<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db   = "shambafresh";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "<br>Check XAMPP MySQL is ON and database 'shambafresh' exists.");
}
?>