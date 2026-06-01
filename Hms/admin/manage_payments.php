<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $student_id = intval($_POST['student_id']);
        $amount = floatval($_POST['amount']);
        $date = trim($_POST['date']);
        $status = trim($_POST['status']);
        $allowed_status = ['Paid', 'Pending', 'Overdue'];
        $errors = [];
        if ($student_id <= 0) {
            $errors[] = 'Student is required.';
        }
        if ($amount <= 0) {
            $errors[] = 'Amount must be greater than 0.';
        }
        if ($date === '' || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
            $errors[] = 'Payment date must be valid.';
        }
        if (!in_array($status, $allowed_status, true)) {
            $errors[] = 'Status is invalid.';
        }

        if (!empty($errors)) {
            $msg = implode(' ', $errors);
            $msg_type = 'danger';
        } else {
            $stmt = $conn->prepare("SELECT Payment_id FROM payment WHERE Student_id=? AND Payment_Date=? AND amount=? AND status=? LIMIT 1");
            $stmt->bind_param("isds", $student_id, $date, $amount, $status);
            $stmt->execute();
            $dup = $stmt->get_result();
            $has_dup = $dup && $dup->num_rows > 0;
            $stmt->close();
            if ($has_dup) {
                $msg = 'Duplicate payment entry detected.';
                $msg_type = 'danger';
            } else {
                $stmt = $conn->prepare("INSERT INTO payment (Student_id, amount, Payment_Date, status) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("idss", $student_id, $amount, $date, $status);
                if ($stmt->execute()) {
                    $msg = 'Payment added successfully.';
                    $msg_type = 'success';
                } else {
                    $msg = 'Error: ' . $stmt->error;
                    $msg_type = 'danger';
                }
                $stmt->close();
            }
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $payment_id = intval($_POST['payment_id']);
        $status = trim($_POST['status']);
        $allowed_status = ['Paid', 'Pending', 'Overdue'];
        if ($payment_id <= 0 || !in_array($status, $allowed_status, true)) {
            $msg = 'Invalid payment update.';
            $msg_type = 'danger';
        } else {
            $stmt = $conn->prepare("UPDATE payment SET status=? WHERE Payment_id=?");
            $stmt->bind_param("si", $status, $payment_id);
            if ($stmt->execute()) {
                $msg = 'Payment updated successfully.';
                $msg_type = 'success';
            } else {
                $msg = 'Error: ' . $stmt->error;
                $msg_type = 'danger';
            }
            $stmt->close();
        }
    }
}

$students = [];
$res = $conn->query("SELECT Student_id, name FROM student ORDER BY name");
if ($res) { while ($row = $res->fetch_assoc()) { $students[] = $row; } }

$payments = [];
$res = $conn->query("SELECT p.*, s.name FROM payment p JOIN student s ON p.Student_id = s.Student_id ORDER BY p.Payment_Date DESC");
if ($res) { while ($row = $res->fetch_assoc()) { $payments[] = $row; } }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Payments - HMS Admin</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="admin-layout">
    <div class="sidebar">
        <h2> HMS Admin</h2>
        <a href="dashboard.php"> Dashboard</a>
        <a href="manage_hostels.php"> Hostels</a>
        <a href="manage_rooms.php"> Rooms</a>
        <a href="manage_students.php"> Students</a>
        <a href="manage_wardens.php"> Wardens</a>
        <a href="manage_payments.php" class="active"> Payments</a>
        <a href="manage_attendance.php"> Attendance</a>
        <a href="manage_complaints.php"> Complaints</a>
        <a href="manage_leaves.php"> Leave Requests</a>
        <a href="../logout.php"> Logout</a>
    </div>

    <div class="main-content">
        <h2> Manage Payments</h2>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <h3 class="mt-20">Add New Payment</h3>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <label for="student_id">Student</label>
            <select name="student_id" required>
                <option value="">-- Select Student --</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?php echo $s['Student_id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <label for="amount">Amount (₹)</label>
            <input type="number" name="amount" step="0.01" min="1" required>
            <label for="date">Payment Date</label>
            <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
            <label for="status">Status</label>
            <select name="status" required>
                <option value="Paid">Paid</option>
                <option value="Pending">Pending</option>
                <option value="Overdue">Overdue</option>
            </select>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Payment</button>
            </div>
        </form>

        <h3 class="mt-20">Payment Records</h3>
        <table>
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Student Name</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Update Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($payments) === 0): ?>
                    <tr><td colspan="6" style="text-align:center;">No payments found.</td></tr>
                <?php else: ?>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><?php echo $p['Payment_id']; ?></td>
                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                            <td>₹<?php echo number_format($p['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($p['Payment_Date']); ?></td>
                            <td><?php echo htmlspecialchars($p['status']); ?></td>
                            <td>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="payment_id" value="<?php echo $p['Payment_id']; ?>">
                                    <select name="status" required style="width:100px; padding:4px; margin:0;">
                                        <option value="Paid" <?php echo ($p['status'] == 'Paid') ? 'selected' : ''; ?>>Paid</option>
                                        <option value="Pending" <?php echo ($p['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Overdue" <?php echo ($p['status'] == 'Overdue') ? 'selected' : ''; ?>>Overdue</option>
                                    </select>
                                    <button type="submit" class="btn btn-warning">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
