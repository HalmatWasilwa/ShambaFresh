<?php
include 'db.php';
if (!isset($_SESSION['user_id'])) die("Login first");
$stmt = $conn->prepare("SELECT * FROM Produce WHERE farmer_id=?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute(); $result = $stmt->get_result();
echo "<h2>My Produce</h2><a href='produce_add.php'>+ Add New</a><table border=1 cellpadding=10>";
echo "<tr><th>Name</th><th>Price</th><th>Qty</th><th>Status</th><th>Action</th></tr>";
while ($row = $result->fetch_assoc()) {
  echo "<tr><td>{$row['name']}</td><td>KES {$row['price']}</td><td>{$row['quantity']}</td><td>{$row['status']}</td>";
  echo "<td><a href='produce_edit.php?id={$row['produce_id']}'>Edit</a> | <a href='produce_delete.php?id={$row['produce_id']}' onclick=\"return confirm('Delete?')\">Delete</a></td></tr>";
}
echo "</table>";
$id = $_GET['id'];
$stmt = $conn->prepare("DELETE FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
?>