<?php
session_start();
require_once "config.php";

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role']; // 'student' or 'admin'
    $secret_key_input = trim($_POST['secret_key'] ?? '');

    if ($role === 'admin') {
        // Verify secret key from table
        $stmt = $conn->prepare("SELECT * FROM secret_keys WHERE id=1 AND key_value=?");
        $stmt->bind_param("s", $secret_key_input);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows === 0) {
            $message = "❌ Invalid secret key for admin registration!";
        }
        $stmt->close();
    }

    if (empty($message)) {
        // Check if email exists
        $stmtCheck = $conn->prepare("SELECT id FROM users WHERE email=?");
        $stmtCheck->bind_param("s", $email);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();
        if ($resCheck->num_rows > 0) {
            $message = "❌ Email already registered!";
        } else {
            // Insert user
            $hashedPwd = password_hash($password, PASSWORD_DEFAULT);
            $stmtIns = $conn->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)");
            $stmtIns->bind_param("ssss", $name, $email, $hashedPwd, $role);
            if ($stmtIns->execute()) {
                $message = "✅ Registration successful! You can now <a href='login.php'>login</a>.";
            } else {
                $message = "❌ Registration failed. Please try again.";
            }
            $stmtIns->close();
        }
        $stmtCheck->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Lab Booking</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="form-container">
    <h2>Register</h2>
    <?php if(!empty($message)) echo "<p class='message'>{$message}</p>"; ?>
    <form method="POST" action="">
        <label>Name:</label>
        <input type="text" name="name" required>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <label>Role:</label>
        <select name="role" required>
            <option value="student">Student</option>
            <option value="admin">Admin</option>
        </select>

        <label>Admin Secret Key (only if registering as Admin):</label>
        <input type="text" name="secret_key" placeholder="Enter secret key if Admin">

        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a>.</p>
</div>
</body>
</html>
