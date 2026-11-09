<?php
include 'db.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role']!='Farmer') die("Farmer only");
$id = $_GET['id'];
// Only delete your own produce - SECURITY
$stmt = $conn->prepare("DELETE FROM Produce WHERE produce_id=? AND farmer_id=?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
header("Location: produce_list.php"); // Go back to list
exit();
?>