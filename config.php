<?php
// config.php — Database connection

$host = "localhost";
$user = "root";
$password = "";
$dbname = "labbooking";

// Create connection
$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");
?>
