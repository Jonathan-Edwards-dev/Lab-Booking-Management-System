<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: login.php");
    exit;
}
require_once "config.php";

$user_id = $_SESSION['user_id'];

/* ---------- Fetch filter parameters ---------- */
$filterStatus = $_GET['status'] ?? '';
$filterDate = $_GET['date'] ?? '';

/* ---------- Build SQL with filters ---------- */
$sql = "
    SELECT b.*, e.name AS equipment_name
    FROM bookings b
    JOIN equipment e ON b.equipment_id = e.id
    WHERE b.user_id = ?
";
$params = [$user_id];
$types = "i";

if ($filterStatus !== '') { $sql .= " AND b.status=?"; $params[] = $filterStatus; $types .= "s"; }
if ($filterDate !== '') { $sql .= " AND b.date=?"; $params[] = $filterDate; $types .= "s"; }

$sql .= " ORDER BY b.date ASC, b.time_slot ASC"; // upcoming first

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Bookings - Lab Booking</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="dashboard-container">
    <h2>My Bookings</h2>

    <!-- Filters Form -->
    <form method="get" class="filter-form">
        <input type="date" name="date" value="<?= htmlspecialchars($filterDate) ?>">
        <select name="status">
            <option value="">All Status</option>
            <option value="pending" <?= $filterStatus=='pending'?'selected':'' ?>>Pending</option>
            <option value="approved" <?= $filterStatus=='approved'?'selected':'' ?>>Approved</option>
            <option value="rejected" <?= $filterStatus=='rejected'?'selected':'' ?>>Rejected</option>
        </select>
        <button type="submit">Apply</button>
        <a href="student_dashboard.php">Reset</a>
    </form>

    <table>
        <tr>
            <th>Equipment</th>
            <th>Date</th>
            <th>Time Slot</th>
            <th>Status</th>
        </tr>
        <?php if ($res && $res->num_rows > 0): ?>
            <?php while($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['equipment_name']) ?></td>
                <td><?= htmlspecialchars($row['date']) ?></td>
                <td><?= htmlspecialchars($row['time_slot']) ?></td>
                <?php
                    $st = $row['status'];
                    $class = ($st=='approved')?'status-approved':(($st=='rejected')?'status-rejected':'status-pending');
                ?>
                <td><span class="<?= $class ?>"><?= ucfirst($st) ?></span></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">No bookings found.</td></tr>
        <?php endif; ?>
    </table>

    <p><a href="index.php">Book More</a> | <a href="logout.php">Logout</a></p>
</div>
</body>
</html>
