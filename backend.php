<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit;
}

require_once "config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $equipment_id = intval($_POST['equipment_id']);
    $date = $_POST['date'];
    $time_slot = $_POST['time_slot'];

    // Validation: prevent past booking
    if ($date < date('Y-m-d')) {
        die("Cannot book past dates.");
    }

    // Prevent double booking for same equipment & slot
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE equipment_id=? AND date=? AND time_slot=? AND status IN ('pending','approved')");
    $stmt->bind_param("iss", $equipment_id, $date, $time_slot);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        die("This equipment is already booked for selected date/time.");
    }

    // Optional: limit 3 pending bookings per student
    $stmt2 = $conn->prepare("SELECT COUNT(*) AS cnt FROM bookings WHERE user_id=? AND status='pending'");
    $stmt2->bind_param("i", $user_id);
    $stmt2->execute();
    $cnt = $stmt2->get_result()->fetch_assoc()['cnt'] ?? 0;
    if ($cnt >= 3) {
        die("You already have 3 pending bookings.");
    }

    // Insert booking
    $stmt3 = $conn->prepare("INSERT INTO bookings (user_id, equipment_id, date, time_slot, status, created_at) VALUES (?,?,?,?, 'pending',NOW())");
    $stmt3->bind_param("iiss", $user_id, $equipment_id, $date, $time_slot);
    if ($stmt3->execute()) {
        header("Location: student_dashboard.php");
        exit;
    } else {
        die("Booking failed: " . $stmt3->error);
    }
}
?>
