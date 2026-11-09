const mysql = require('mysql2/promise');
async function init(){
  const db = await mysql.createConnection({
    host: 'b6stl5b49eetbf2iqa2f-mysql.services.clever-cloud.com',
    user: 'umpic2rwevzar3qh',
    password: 'GfJMBTf7LByFNNyIaIS6', // paste after you click 🔓
    database: 'b6stl5b49eetbf2iqa2f'
  });
  await db.execute(`CREATE TABLE IF NOT EXISTS Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100), email VARCHAR(100) UNIQUE,
    password VARCHAR(255), role VARCHAR(20), status VARCHAR(20)
  )`);
  await db.execute(`CREATE TABLE IF NOT EXISTS Produce (
    produce_id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT, name VARCHAR(100), category VARCHAR(50),
    price DECIMAL(10,2), stock INT, status VARCHAR(20)
  )`);
  await db.execute(`CREATE TABLE IF NOT EXISTS Orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT, total_amount DECIMAL(10,2),
    status VARCHAR(20), delivery_location VARCHAR(255),
    payment_method VARCHAR(50)
  )`);
  console.log("✅ Tables created!");
  process.exit();
}
init();