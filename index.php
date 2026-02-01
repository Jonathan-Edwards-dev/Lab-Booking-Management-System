<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit;
}
require_once "config.php";

// Fetch available equipment
$equipments = $conn->query("SELECT * FROM equipment ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book Lab Equipment</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <h1>Book Lab Equipment</h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION['name']) ?> — <a href="logout.php">Logout</a> | <a href="student_dashboard.php">My Bookings</a></p>

    <form action="backend.php" method="post">
        <label for="equipment">Select Equipment:</label>
        <select name="equipment_id" id="equipment" required>
            <option value="">--Choose--</option>
            <?php while($e = $equipments->fetch_assoc()): ?>
                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
            <?php endwhile; ?>
        </select>

        <label for="date">Select Date:</label>
        <input type="date" name="date" id="date" required min="<?= date('Y-m-d') ?>">

        <label for="time_slot">Time Slot:</label>
        <select name="time_slot" id="time_slot" required>
            <option value="09:00-10:00">09:00-10:00</option>
            <option value="10:00-11:00">10:00-11:00</option>
            <option value="11:00-12:00">11:00-12:00</option>
            <option value="12:00-13:00">12:00-13:00</option>
            <option value="14:00-15:00">14:00-15:00</option>
            <option value="15:00-16:00">15:00-16:00</option>
        </select>

        <button type="submit">Book Now</button>
    </form>
</div>
</body>
</html>
