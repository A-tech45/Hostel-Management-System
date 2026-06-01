<?php
include('../config.php');
if (!isset($_SESSION['student_id'])) { header("Location: ../student_login.php"); exit(); }
$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

// Fetch student info
$student = null;
$res = $conn->query("SELECT s.*, r.Room_name, r.Room_type, h.Hostel_name FROM student s LEFT JOIN room r ON s.Room_id=r.Room_id LEFT JOIN hostel h ON r.Hostel_id=h.Hostel_id WHERE s.Student_id=$student_id");
if ($res) $student = $res->fetch_assoc();

// Payment stats
$total_paid = 0; $total_pending = 0;
$res = $conn->query("SELECT SUM(CASE WHEN status='Paid' THEN amount ELSE 0 END) as paid, SUM(CASE WHEN status!='Paid' THEN amount ELSE 0 END) as pending FROM payment WHERE Student_id=$student_id");
if ($res && $row = $res->fetch_assoc()) { $total_paid = $row['paid'] ?? 0; $total_pending = $row['pending'] ?? 0; }

// Attendance count
$att_count = 0;
$res = $conn->query("SELECT COUNT(*) as total FROM attendance WHERE Student_id=$student_id");
if ($res && $row = $res->fetch_assoc()) $att_count = $row['total'];
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Student Dashboard - HMS</title>
<link rel="stylesheet" href="../style.css"></head><body>
<div class="layout">
<div class="sidebar">
<div class="info">Welcome, <?php echo htmlspecialchars($student_name); ?></div>
<a href="dashboard.php" class="active"> Dashboard</a>
<a href="payments.php">Payments</a>
<a href="attendance.php"> Attendance</a>
<a href="leave_request.php"> Leave Request</a>
<a href="profile.php"> Profile</a>
<a href="../logout.php"> Logout</a>
</div>
<div class="main-content">
<h1>Student Dashboard</h1>
<div class="cards">
<div class="card"><h3>Room</h3><div class="val"><?php echo htmlspecialchars($student['Room_name'] ?? 'N/A'); ?></div></div>
<div class="card"><h3>Total Paid</h3><div class="val">₹<?php echo number_format($total_paid, 2); ?></div></div>
<div class="card"><h3>Pending Dues</h3><div class="val">₹<?php echo number_format($total_pending, 2); ?></div></div>
<div class="card"><h3>Attendance Records</h3><div class="val"><?php echo $att_count; ?></div></div>
</div>
</div></div></body></html>
