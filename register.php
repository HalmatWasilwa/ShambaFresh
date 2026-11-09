<?php
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $location = $_POST['location'];
    $pass = $_POST['password'];

    // SERVER-SIDE VALIDATION (Task 6 + Non-functional requirement)
    if (strlen($name) < 3) die("Name too short");
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) die("Invalid email");
    if (!preg_match("/^07\d{8}$/", $phone)) die("Invalid phone 0712345678");
    if (strlen($pass) < 6) die("Password min 6 chars");
    if (!in_array($role, ['Farmer','Buyer','Admin'])) die("Invalid role");

    $hash = password_hash($pass, PASSWORD_DEFAULT); // NEVER store plain password
// FIX: Clean the role first - remove spaces, make first letter capital
$role = ucfirst(strtolower(trim($_POST['role'])));
$status = ($role == 'Farmer') ? 'Pending' : 'Approved';

    // PREPARED STATEMENT - protects against SQL Injection
    $stmt = $conn->prepare("INSERT INTO Users (full_name,email,phone,password_hash,role,location,status) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("sssssss", $name, $email, $phone, $hash, $role, $location, $status);
    if ($stmt->execute()) {
        echo "✅ Registered! ";
        echo ($role == 'Farmer') ? "Wait for Admin approval." : "<a href='login.php'>Login now</a>";
    } else {
        echo "Email already exists";
    }
}
?>
<form method="POST">
  <input name="full_name" placeholder="Full Name" required>
  <input name="email" type="email" placeholder="Email" required>
  <input name="phone" placeholder="0712345678" required>
  <select name="role"><option>Farmer</option><option>Buyer</option><option>Admin</option></select>
  <select name="location"><option>Kiambu</option><option>Nakuru</option><option>Eldoret</option><option>Nairobi</option></select>
  <input name="password" type="password" placeholder="Password" required>
  <button>Register</button>
</form>