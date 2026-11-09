<?php
include 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $stmt = $conn->prepare("SELECT user_id, full_name, password_hash, role, status FROM Users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if ($user && password_verify($pass, $user['password_hash'])) {
        if ($user['status'] == 'Pending') die("Account pending Admin approval");
        // SAVE TO SESSION
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        // REDIRECT BY ROLE
        if ($user['role'] == 'Admin') header("Location: admin.php");
        elseif ($user['role'] == 'Farmer') header("Location: produce_list.php");
        else header("Location: index.html");
        exit();
    } else {
        echo "Wrong email or password";
    }
}
?>
<form method="POST">
  <input name="email" type="email" placeholder="Email" required>
  <input name="password" type="password" placeholder="Password" required>
  <button>Login</button>
</form>