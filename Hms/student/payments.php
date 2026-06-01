<?php
include('../config.php');
if (!isset($_SESSION['student_id'])) { header("Location: ../student_login.php"); exit(); }
$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

// Fetch payments
$payments = [];
$res = $conn->query("SELECT * FROM payment WHERE Student_id=$student_id ORDER BY Payment_Date DESC");
if ($res) { while ($row = $res->fetch_assoc()) $payments[] = $row; }

$total_paid = 0; $total_pending = 0;
foreach ($payments as $p) {
    if ($p['status'] === 'Paid') $total_paid += $p['amount'];
    else $total_pending += $p['amount'];
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>My Payments - Student</title><link rel="stylesheet" href="../style.css"></head><body>
<div class="layout">
<div class="sidebar">
<div class="info">Welcome, <?php echo htmlspecialchars($student_name); ?></div>
<a href="dashboard.php"> Dashboard</a>
<a href="payments.php" class="active"> Payments</a>
<a href="attendance.php"> Attendance</a>
<a href="leave_request.php"> Leave Request</a>
<a href="profile.php"> Profile</a>
<a href="../logout.php"> Logout</a>
</div>
<div class="main-content">
<h1> My Payments</h1>
<div class="summary">
<div class="summary-card"><h4>Total Paid</h4><div class="val paid">₹<?php echo number_format($total_paid, 2); ?></div></div>
<div class="summary-card"><h4>Pending Dues</h4><div class="val pending">₹<?php echo number_format($total_pending, 2); ?></div></div>
</div>
<table><thead><tr><th>Payment ID</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead><tbody>
<?php if (count($payments) === 0): ?><tr><td colspan="4" class="text-center">No payments found.</td></tr>
<?php else: foreach ($payments as $p): ?>
<?php
$status_class = 'status-pending';
if ($p['status'] === 'Paid') {
    $status_class = 'status-paid';
} elseif ($p['status'] === 'Overdue') {
    $status_class = 'status-overdue';
}
?>
<tr>
<td><?php echo $p['Payment_id']; ?></td>
<td><?php echo htmlspecialchars($p['Payment_Date']); ?></td>
<td>₹<?php echo number_format($p['amount'], 2); ?></td>
<td><span class="<?php echo $status_class; ?>"><?php echo $p['status']; ?></span></td>
</tr><?php endforeach; endif; ?></tbody></table>
</div></div></body></html>
