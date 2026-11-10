const express=require('express');
const mysql=require('mysql2/promise');
const bcrypt=require('bcryptjs');
const session=require('express-session');
const app=express();
app.use(express.json());
app.use(express.urlencoded({extended:true}));
app.use(express.static(__dirname));
app.use(session({secret:'shamba-secret-123',resave:false,saveUninitialized:false}));
let db;
async function connectDB(){
  db=await mysql.createConnection({host:process.env.DB_HOST||'localhost',user:process.env.DB_USER||'root',password:process.env.DB_PASS||'',database:process.env.DB_NAME||'shambafresh'});
  console.log("✅ DB Connected");
  await db.execute(`CREATE TABLE IF NOT EXISTS Users(user_id INT AUTO_INCREMENT PRIMARY KEY, full_name VARCHAR(100), email VARCHAR(100) UNIQUE, phone VARCHAR(20), location VARCHAR(100), password VARCHAR(255), role VARCHAR(20), status VARCHAR(20) DEFAULT 'Pending')`);
  await db.execute(`CREATE TABLE IF NOT EXISTS Produce(produce_id INT AUTO_INCREMENT PRIMARY KEY, farmer_id INT, name VARCHAR(100), category VARCHAR(50), price DECIMAL(10,2), stock INT, location VARCHAR(100), status VARCHAR(20) DEFAULT 'Pending')`);
  await db.execute(`CREATE TABLE IF NOT EXISTS Orders(order_id INT AUTO_INCREMENT PRIMARY KEY, buyer_id INT, total_amount DECIMAL(10,2), status VARCHAR(20) DEFAULT 'Pending', delivery_location VARCHAR(255), payment_method VARCHAR(50))`);
  const [a]=await db.execute("SELECT * FROM Users WHERE email='admin@shamba.com'");
  if(!a.length){const h=await bcrypt.hash('admin123',10);await db.execute("INSERT INTO Users(full_name,email,password,role,status) VALUES(?,?,?,?, 'Pending')",[full_name,email,hash,role||'Buyer']);
}
async function connectDB(){
  db=await mysql.createConnection({host:process.env.DB_HOST||'localhost',user:process.env.DB_USER||'root',password:process.env.DB_PASS||'',database:process.env.DB_NAME||'shambafresh'});
  console.log("✅ DB Connected");
  await db.execute(`CREATE TABLE IF NOT EXISTS Users(user_id INT AUTO_INCREMENT PRIMARY KEY, full_name VARCHAR(100), email VARCHAR(100) UNIQUE, phone VARCHAR(20), location VARCHAR(100), password VARCHAR(255), role VARCHAR(20), status VARCHAR(20) DEFAULT 'Pending')`);
  await db.execute(`CREATE TABLE IF NOT EXISTS Produce(produce_id INT AUTO_INCREMENT PRIMARY KEY, farmer_id INT, name VARCHAR(100), category VARCHAR(50), price DECIMAL(10,2), stock INT, location VARCHAR(100), status VARCHAR(20) DEFAULT 'Pending')`);
  await db.execute(`CREATE TABLE IF NOT EXISTS Orders(order_id INT AUTO_INCREMENT PRIMARY KEY, buyer_id INT, total_amount DECIMAL(10,2), status VARCHAR(20) DEFAULT 'Pending', delivery_location VARCHAR(255), payment_method VARCHAR(50))`);

  // --- FIX OLD TABLE: Add missing columns if they don't exist ---
  try{ await db.execute("ALTER TABLE Users ADD COLUMN phone VARCHAR(20)"); }catch(e){}
  try{ await db.execute("ALTER TABLE Users ADD COLUMN location VARCHAR(100)"); }catch(e){}
  try{ await db.execute("ALTER TABLE Produce ADD COLUMN location VARCHAR(100)"); }catch(e){}
  console.log("✅ Tables Fixed");

  const [a]=await db.execute("SELECT * FROM Users WHERE email='admin@shamba.com'");
  if(!a.length){const h=await bcrypt.hash('admin123',10);await db.execute("INSERT INTO Users(full_name,email,password,role,status) VALUES(?,?,?,?,?)",['Admin','admin@shamba.com',h,'Admin','Approved']);}
}f
async function doLogin(req,res){
  try{const {email,password}=req.body;const [rows]=await db.execute("SELECT * FROM Users WHERE email=?",[email]);if(!rows.length)return res.json({success:false,msg:'User not found'});
  const u=rows[0];if(u.status!=='Approved')return res.json({success:false,msg:'⏳ Account Pending Admin Approval. Ask Admin to approve.'});
  if(!(await bcrypt.compare(password,u.password)))return res.json({success:false,msg:'Wrong password'});
  req.session.user_id=u.user_id;req.session.name=u.full_name;req.session.role=u.role;
  res.json({success:true,role:u.role,name:u.full_name,msg:'Welcome '+u.full_name});
  }catch(e){res.json({success:false,msg:'Login error'});}
}
app.post('/api/register',doRegister);app.post('/register',doRegister);
app.post('/api/login',doLogin);app.post('/login',doLogin);
app.get('/api/produce',async(req,res)=>{try{const [r]=await db.execute("SELECT * FROM Produce WHERE status='Approved'");res.json(r);}catch(e){res.json([]);}});
app.post('/api/produce',async(req,res)=>{try{const {name,category,price,stock,farmer_id}=req.body;await db.execute("INSERT INTO Produce(farmer_id,name,category,price,stock,status) VALUES(?,?,?,?,?,'Pending')",[farmer_id||1,name,category,price,stock]);res.json({success:true,msg:'✅ Produce sent for Admin approval!'});}catch(e){res.json({success:false,msg:'Failed'});}});
app.post('/api/checkout',async(req,res)=>{const {buyer_id,total,location,payment}=req.body;const [r]=await db.execute("INSERT INTO Orders(buyer_id,total_amount,status,delivery_location,payment_method) VALUES(?,?,'Pending',?,?)",[buyer_id||1,total,location,payment]);res.json({success:true,order_id:r.insertId,msg:'🎉 Order Placed! Order ID #'+r.insertId+' - Pay via '+payment});});
app.get('/api/admin/pending',async(req,res)=>{const [users]=await db.execute("SELECT * FROM Users WHERE status='Pending'");const [produce]=await db.execute("SELECT * FROM Produce WHERE status='Pending'");res.json({users,produce});});
app.post('/api/admin/approve',async(req,res)=>{const {type,id}=req.body;if(type==='user')await db.execute("UPDATE Users SET status='Approved' WHERE user_id=?",[id]);else await db.execute("UPDATE Produce SET status='Approved' WHERE produce_id=?",[id]);res.json({success:true});});
app.get('/api/orders',async(req,res)=>{const [r]=await db.execute("SELECT * FROM Orders ORDER BY order_id DESC");res.json(r);});
const PORT=process.env.PORT||3000;app.listen(PORT,()=>console.log("🚀 Running on "+PORT));