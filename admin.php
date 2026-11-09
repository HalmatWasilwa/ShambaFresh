<?php
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    die("❌ Admin only. <a href='login.php'>Login as Admin</a>");
}
echo "Logged in as: " . $_SESSION['name'] . " | Role: " . $_SESSION['role'] . " | <a href='logout.php'>Logout</a><hr>";
echo "<h2>Admin Dashboard</h2>";

echo "<h3>1. Pending Farmers</h3>";
$res = $conn->query("SELECT user_id, full_name, email, phone FROM Users WHERE role='Farmer' AND status='Pending'");
if ($res->num_rows == 0) echo "No pending farmers.<br>";
while($r = $res->fetch_assoc()){
  echo $r['full_name'] . " - " . $r['email'] . " ";
  echo "<a href='admin_approve.php?type=farmer&id=" . $r['user_id'] . "'>[Approve]</a><br>";
}

echo "<h3>2. Pending Produce</h3>";
$res = $conn->query("SELECT p.produce_id, p.name, p.price, u.full_name FROM Produce p JOIN Users u ON p.farmer_id=u.user_id WHERE p.status='Pending'");
if ($res->num_rows == 0) echo "No pending produce.<br>";
while($r = $res->fetch_assoc()){
  echo $r['name'] . " - KES " . $r['price'] . " by " . $r['full_name'] . " ";
  echo "<a href='admin_approve.php?type=produce&id=" . $r['produce_id'] . "'>[Approve]</a><br>";
}

echo "<h3>3. All Orders</h3>";
$res = $conn->query("SELECT o.order_id, o.total_amount, o.status, u.full_name FROM Orders o JOIN Users u ON o.buyer_id=u.user_id");
if ($res->num_rows == 0) echo "No orders yet.<br>";
while($r = $res->fetch_assoc()){
  echo "Order #" . $r['order_id'] . " - " . $r['full_name'] . " - KES " . $r['total_amount'] . " - " . $r['status'] . " ";
  echo "<a href='admin_approve.php?type=order&id=" . $r['order_id'] . "'>[Next Status]</a><br>";
}
$id = $_GET['id'];
$stmt = $conn->prepare("DELETE FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
?>