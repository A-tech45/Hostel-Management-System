<?php
include('../config.php');

if (!isset($_SESSION['warden_id'])) {
    header("Location: ../warden_login.php");
    exit();
}

$warden_id = $_SESSION['warden_id'];
$warden_name = $_SESSION['warden_name'];
$hostel_id = $_SESSION['warden_hostel_id'];

// Fetch hostel info
$hostel_name = 'N/A';
$res = $conn->query("SELECT Hostel_name FROM hostel WHERE Hostel_id = $hostel_id");
if ($res && $row = $res->fetch_assoc()) {
    $hostel_name = $row['Hostel_name'];
}

// Count students in this hostel
$students_count = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM student s JOIN room r ON s.Room_id = r.Room_id WHERE r.Hostel_id = $hostel_id");
if ($res && $row = $res->fetch_assoc()) {
    $students_count = $row['total'];
}

// Count rooms in this hostel
$rooms_count = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM room WHERE Hostel_id = $hostel_id");
if ($res && $row = $res->fetch_assoc()) {
    $rooms_count = $row['total'];
}

// Pending payments count
$pending_payments = 0;
$res = $conn->query("SELECT COUNT(*) AS total FROM payment p JOIN student s ON p.Student_id = s.Student_id JOIN room r ON s.Room_id = r.Room_id WHERE r.Hostel_id = $hostel_id AND p.status != 'Paid'");
if ($res && $row = $res->fetch_assoc()) {
    $pending_payments = $row['total'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warden Dashboard - HMS</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="layout">
        <div class="sidebar">
            
            <div class="warden-info">
                Welcome, <?php echo htmlspecialchars($warden_name); ?><br>
                <?php echo htmlspecialchars($hostel_name); ?>
            </div>
            <a href="dashboard.php" class="active"> Dashboard</a>
            <a href="payments.php"> Payments</a>
            <a href="attendance.php"> Attendance</a>
            <a href="manage_leaves.php"> Leave Approvals</a>
            <a href="room_allocation.php"> Room Allocation</a>
            <a href="../logout.php"> Logout</a>
        </div>
        <div class="main-content">
            <h1>Warden Dashboard</h1>
            <div class="cards-container">
                <div class="card">
                    <h3>My Hostel</h3>
                    <div class="count"><?php echo htmlspecialchars($hostel_name); ?></div>
                </div>
                <div class="card">
                    <h3>Total Students</h3>
                    <div class="count"><?php echo $students_count; ?></div>
                </div>
                <div class="card">
                    <h3>Total Rooms</h3>
                    <div class="count"><?php echo $rooms_count; ?></div>
                </div>
                <div class="card">
                    <h3>Pending Payments</h3>
                    <div class="count"><?php echo $pending_payments; ?></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
