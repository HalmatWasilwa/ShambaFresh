<?php
include 'db.php';

// DEBUG - Remove after it works. This shows you the real error.
// error_reporting(E_ALL); ini_set('display_errors', 1);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    die("❌ Admin only. You are logged in as: " . ($_SESSION['role'] ?? 'NOT LOGGED IN') . " - <a href='login.php'>Login as Admin</a>");
}

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);

if ($id == 0 || $type == '') {
    die("❌ Missing id or type. Link is broken. Go back to <a href='admin.php'>Admin</a>");
}

if ($type == 'farmer') {
    $stmt = $conn->prepare("UPDATE Users SET status='Approved' WHERE user_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "✅ Farmer Approved! ";
}
elseif ($type == 'produce') {
    $stmt = $conn->prepare("UPDATE Produce SET status='Approved' WHERE produce_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "✅ Produce Approved! ";
}
elseif ($type == 'order') {
    $stmt = $conn->prepare("UPDATE Orders SET status = CASE WHEN status='Placed' THEN 'Confirmed' WHEN status='Confirmed' THEN 'Dispatched' WHEN status='Dispatched' THEN 'Delivered' ELSE status END WHERE order_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "✅ Order Status Updated! ";
}
else {
    die("❌ Unknown type: $type");
}

// Check if it actually changed a row
if ($stmt->affected_rows == 0) {
    echo "<br>⚠️ No row changed. Check: Is ID $id correct? Is status already Approved?";
    echo "<br><a href='admin.php'>Back to Admin</a>";
} else {
    echo "<br><a href='admin.php'>Back to Admin - See it changed</a>";
}
?>