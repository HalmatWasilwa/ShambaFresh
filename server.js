const express = require('express');
const mysql = require('mysql2/promise');
const bcrypt = require('bcryptjs');
const session = require('express-session');
const path = require('path');

const app = express();
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(__dirname)); // serves your index.html
app.use(session({ secret: 'shamba-secret-123', resave: false, saveUninitialized: false }));

// --- DATABASE - We will change this to Clever Cloud later ---
let db;
async function connectDB(){
  db = await mysql.createConnection({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASS || '',
    database: process.env.DB_NAME || 'shambafresh'
  });
  console.log("✅ DB Connected");
}
connectDB();

// --- REGISTER ---
app.post('/api/register', async (req,res)=>{
  const {full_name, email, password, role} = req.body;
  const hash = await bcrypt.hash(password, 10);
  try{
    await db.execute("INSERT INTO Users (full_name, email, password, role, status) VALUES (?, ?, ?, ?, 'Pending')", [full_name, email, hash, role]);
    res.json({success:true, msg:"Registered! Wait for Admin Approval"});
  }catch(e){ res.json({success:false, msg:"Email already exists"}); }
});

// --- LOGIN ---
app.post('/api/login', async (req,res)=>{
  const {email, password} = req.body;
  const [rows] = await db.execute("SELECT * FROM Users WHERE email=?", [email]);
  if(rows.length===0) return res.json({success:false, msg:"User not found"});
  const user = rows[0];
  if(user.status!=='Approved') return res.json({success:false, msg:"Account Pending Approval"});
  const ok = await bcrypt.compare(password, user.password);
  if(!ok) return res.json({success:false, msg:"Wrong password"});
  req.session.user_id = user.user_id;
  req.session.name = user.full_name;
  req.session.role = user.role;
  res.json({success:true, role:user.role, name:user.full_name});
});

// --- GET PRODUCE (for homepage) ---
app.get('/api/produce', async (req,res)=>{
  const [rows] = await db.execute("SELECT * FROM Produce WHERE status='Approved'");
  res.json(rows);
});

// --- ADD PRODUCE (Farmer) ---
app.post('/api/produce', async (req,res)=>{
  const {name, category, price, stock, farmer_id} = req.body;
  await db.execute("INSERT INTO Produce (farmer_id, name, category, price, stock, status) VALUES (?, ?, ?, ?, ?, 'Pending')", [farmer_id, name, category, price, stock]);
  res.json({success:true});
});

// --- CHECKOUT / PLACE ORDER ---
app.post('/api/checkout', async (req,res)=>{
  const {buyer_id, total, location, payment, items} = req.body;
  const [result] = await db.execute("INSERT INTO Orders (buyer_id, total_amount, status, delivery_location, payment_method) VALUES (?, ?, 'Pending', ?, ?)", [buyer_id || 1, total, location, payment]);
  res.json({success:true, order_id: result.insertId, msg:"🎉 Order Placed Successfully! Order ID #"+result.insertId});
});

// --- GET ORDERS (Admin) ---
app.get('/api/orders', async (req,res)=>{
  const [rows] = await db.execute("SELECT * FROM Orders ORDER BY order_id DESC");
  res.json(rows);
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, ()=> console.log("🚀 ShambaFresh running on port "+PORT));