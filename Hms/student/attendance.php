<?php
include('../config.php');
if (!isset($_SESSION['student_id'])) { header("Location: ../student_login.php"); exit(); }
$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

$attendance = [];
$res = $conn->query("SELECT * FROM attendance WHERE Student_id=$student_id ORDER BY Date DESC");
if ($res) { while ($row = $res->fetch_assoc()) $attendance[] = $row; }
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Attendance - Student</title><link rel="stylesheet" href="../style.css"></head><body>
<div class="layout">
<div class="sidebar">
<div class="info">Welcome, <?php echo htmlspecialchars($student_name); ?></div>
<a href="dashboard.php"> Dashboard</a>
<a href="payments.php"> Payments</a>
<a href="attendance.php" class="active"> Attendance</a>
<a href="leave_request.php"> Leave Request</a>
<a href="profile.php"> Profile</a>
<a href="../logout.php"> Logout</a>
</div>
<div class="main-content">
<h1> My Attendance Records</h1>
<p style="margin-bottom:15px;color:#666">Total records: <strong><?php echo count($attendance); ?></strong></p>
<table><thead><tr><th>ID</th><th>Date</th><th>In Time</th><th>Out Time</th></tr></thead><tbody>
<?php if (count($attendance) === 0): ?><tr><td colspan="4" class="text-center">No attendance records found.</td></tr>
<?php else: foreach ($attendance as $a): ?><tr>
<td><?php echo $a['Attendance_id']; ?></td>
<td><?php echo $a['Date']; ?></td>
<td><?php echo $a['In_time']; ?></td>
<td><?php echo $a['Out_time']; ?></td>
</tr><?php endforeach; endif; ?></tbody></table>
</div></div></body></html>
