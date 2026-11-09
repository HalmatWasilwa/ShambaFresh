<?php
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Farmer') die("Login as Farmer first");
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name']; $cat = $_POST['category_id'];
    $price = $_POST['price']; $qty = $_POST['quantity'];
    if ($price <= 0) die("Price must be > 0");
    if ($qty < 0) die("Quantity cannot be negative");
    // Photo upload
    $photo = "";
    if (!empty($_FILES['photo']['name'])) {
        $photo = "uploads/" . time() . "_" . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    }
    $stmt = $conn->prepare("INSERT INTO Produce (farmer_id, category_id, name, price, quantity, unit, photo, location) VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param("iisdssss", $_SESSION['user_id'], $cat, $name, $price, $qty, $_POST['unit'], $photo, $_POST['location']);
    $stmt->execute();
    echo "✅ Produce added! Waiting for Admin approval.";
}
?>
<form method="POST" enctype="multipart/form-data">
  <input name="name" placeholder="Tomatoes" required>
  <select name="category_id"><option value="1">Vegetables</option><option value="2">Fruits</option><option value="3">Grains</option><option value="4">Dairy</option></select>
  <input name="price" type="number" step="0.01" placeholder="Price KES" required>
  <input name="quantity" type="number" placeholder="Quantity" required>
  <input name="unit" placeholder="kg / L / bunch">
  <input name="location" placeholder="Kiambu">
  <input name="photo" type="file">
  <button>Add Produce</button>
</form>