<?php
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role']!='Farmer') die("Farmer only");
$id = $_GET['id'];
// Get current data
$stmt = $conn->prepare("SELECT * FROM Produce WHERE produce_id=? AND farmer_id=?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute(); $p = $stmt->get_result()->fetch_assoc();
if (!$p) die("Not found or not yours");

if ($_SERVER['REQUEST_METHOD']=='POST') {
  $price = $_POST['price']; $qty = $_POST['quantity'];
  if ($price <=0) die("Price must be > 0");
  if ($qty <0) die("Qty cannot be negative");
  $stmt = $conn->prepare("UPDATE Produce SET name=?, price=?, quantity=?, unit=? WHERE produce_id=?");
  $stmt->bind_param("sdisi", $_POST['name'], $price, $qty, $_POST['unit'], $id);
  $stmt->execute();
  echo "✅ Updated! <a href='produce_list.php'>Back to List</a>"; exit();
}
?>
<form method="POST">
  Name: <input name="name" value="<?php echo $p['name']; ?>" required><br><br>
  Price: <input name="price" type="number" step="0.01" value="<?php echo $p['price']; ?>" required><br><br>
  Quantity: <input name="quantity" type="number" value="<?php echo $p['quantity']; ?>" required><br><br>
  Unit: <input name="unit" value="<?php echo $p['unit']; ?>"><br><br>
  <button>Update Produce</button>
</form>