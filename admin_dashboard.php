<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

require_once "config.php";

/* ---------- Auto-move past bookings into booking_history ---------- */
function movePastBookings($conn) {
    $today = date('Y-m-d');
    $now = new DateTime();

    $stmt = $conn->prepare("SELECT * FROM bookings WHERE date <= ?");
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $bookingDate = $row['date'];
        $timeSlot = trim($row['time_slot']);
        $shouldMove = false;

        if ($bookingDate < $today) {
            $shouldMove = true;
        } elseif ($bookingDate === $today) {
            $parts = preg_split('/\s*-\s*/', $timeSlot);
            $endTime = isset($parts[1]) ? trim($parts[1]) : null;
            if ($endTime) {
                $endDT = DateTime::createFromFormat('Y-m-d H:i', $bookingDate . ' ' . $endTime)
                       ?: DateTime::createFromFormat('Y-m-d H:i:s', $bookingDate . ' ' . $endTime);
                if ($endDT && $endDT <= $now) $shouldMove = true;
            } else $shouldMove = true;
        }

        if ($shouldMove) {
            $conn->begin_transaction();
            try {
                $insert = $conn->prepare("
                    INSERT INTO booking_history
                    (original_booking_id, user_id, equipment_id, date, time_slot, status, created_at, moved_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $movedAt = date('Y-m-d H:i:s');
                $createdAt = $row['created_at'] ?? date('Y-m-d H:i:s');
                $insert->bind_param(
                    "iiisssss",
                    $row['id'],
                    $row['user_id'],
                    $row['equipment_id'],
                    $row['date'],
                    $row['time_slot'],
                    $row['status'],
                    $createdAt,
                    $movedAt
                );
                $insert->execute();

                $delete = $conn->prepare("DELETE FROM bookings WHERE id = ?");
                $delete->bind_param("i", $row['id']);
                $delete->execute();

                $conn->commit();
                $insert->close();
                $delete->close();
            } catch (Exception $e) {
                $conn->rollback();
            }
        }
    }
    $stmt->close();
}
movePastBookings($conn);

/* ---------- Approve / Reject ---------- */
if (isset($_GET['action'], $_GET['id'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    if ($action === 'approve' || $action === 'reject') {
        $newStatus = ($action === 'approve') ? 'approved' : 'rejected';
        $stmtUpd = $conn->prepare("UPDATE bookings SET status=? WHERE id=?");
        $stmtUpd->bind_param("si", $newStatus, $id);
        $stmtUpd->execute();
        $stmtUpd->close();
        header("Location: admin_dashboard.php");
        exit;
    }
}

/* ---------- Fetch stats ---------- */
$totalBookings = $conn->query("SELECT COUNT(*) AS c FROM bookings")->fetch_assoc()['c'] ?? 0;
$pendingBookings = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE status='pending'")->fetch_assoc()['c'] ?? 0;
$approvedBookings = $conn->query("SELECT COUNT(*) AS c FROM bookings WHERE status='approved'")->fetch_assoc()['c'] ?? 0;
$historyCount = $conn->query("SELECT COUNT(*) AS c FROM booking_history")->fetch_assoc()['c'] ?? 0;

/* ---------- Fetch active & history tables ---------- */
$activeRes = $conn->query("
    SELECT b.id,b.date,b.time_slot,b.status,e.name AS equipment_name,u.name AS student_name,u.email AS student_email
    FROM bookings b
    JOIN equipment e ON b.equipment_id=e.id
    JOIN users u ON b.user_id=u.id
    ORDER BY b.date DESC,b.time_slot DESC
");
$historyRes = $conn->query("
    SELECT h.id,h.date,h.time_slot,h.status,h.created_at,h.moved_at,e.name AS equipment_name,
           u.name AS student_name,u.email AS student_email
    FROM booking_history h
    LEFT JOIN equipment e ON h.equipment_id=e.id
    LEFT JOIN users u ON h.user_id=u.id
    ORDER BY h.moved_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="container">
    <h1>Admin Dashboard</h1>
    <p class="small">Logged in as: <?= htmlspecialchars($_SESSION['name']) ?> — <a href="logout.php">Logout</a></p>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card blue"><h3>Total Bookings</h3><p><?= $totalBookings ?></p></div>
        <div class="stat-card orange"><h3>Pending Approvals</h3><p><?= $pendingBookings ?></p></div>
        <div class="stat-card green"><h3>Approved Bookings</h3><p><?= $approvedBookings ?></p></div>
        <div class="stat-card gray"><h3>Completed (History)</h3><p><?= $historyCount ?></p></div>
    </div>

    <!-- Active Bookings -->
    <div class="section">
        <h2>Active Bookings</h2>
        <?php if ($activeRes && $activeRes->num_rows > 0): ?>
        <table class="table">
            <tr><th>Student</th><th>Equipment</th><th>Date</th><th>Time Slot</th><th>Status</th><th>Action</th></tr>
            <?php while($row=$activeRes->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['student_name']) ?><br><span class="small"><?= htmlspecialchars($row['student_email']) ?></span></td>
                <td><?= htmlspecialchars($row['equipment_name']) ?></td>
                <td><?= htmlspecialchars($row['date']) ?></td>
                <td><?= htmlspecialchars($row['time_slot']) ?></td>
                <?php
                    $st=$row['status'];
                    $class=($st=='approved')?'status-approved':(($st=='rejected')?'status-rejected':'status-pending');
                ?>
                <td><span class="<?= $class ?>"><?= ucfirst($st) ?></span></td>
                <td class="actions">
                    <?php if ($row['status']=='pending'): ?>
                        <a href="?action=approve&id=<?= $row['id'] ?>">Approve</a>
                        <a href="?action=reject&id=<?= $row['id'] ?>">Reject</a>
                    <?php else: ?><span class="small">No actions</span><?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?><p>No active bookings.</p><?php endif; ?>
    </div>

    <!-- History Bookings -->
    <div class="section">
        <h2>History (Completed / Past Bookings)</h2>
        <?php if ($historyRes && $historyRes->num_rows > 0): ?>
        <table class="table">
            <tr><th>Student</th><th>Equipment</th><th>Date</th><th>Time Slot</th><th>Status</th><th>Created At</th><th>Moved At</th></tr>
            <?php while($h=$historyRes->fetch_assoc()): ?>
            <?php $st2=$h['status'];$class2=($st2=='approved')?'status-approved':(($st2=='rejected')?'status-rejected':'status-pending'); ?>
            <tr>
                <td><?= htmlspecialchars($h['student_name'] ?? '—') ?><br><span class="small"><?= htmlspecialchars($h['student_email'] ?? '') ?></span></td>
                <td><?= htmlspecialchars($h['equipment_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($h['date']) ?></td>
                <td><?= htmlspecialchars($h['time_slot']) ?></td>
                <td><span class="<?= $class2 ?>"><?= ucfirst($st2) ?></span></td>
                <td><?= htmlspecialchars($h['created_at']) ?></td>
                <td><?= htmlspecialchars($h['moved_at']) ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php else: ?><p>No historical bookings yet.</p><?php endif; ?>
    </div>
</div>
</body>
</html>
