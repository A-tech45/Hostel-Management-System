<?php
include('../config.php');

if (!isset($_SESSION['warden_id'])) {
    header("Location: ../warden_login.php");
    exit();
}

$warden_name = $_SESSION['warden_name'];
$hostel_id = $_SESSION['warden_hostel_id'];

$msg = '';
$msg_type = '';

// Handle Add Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $student_id   = intval($_POST['student_id']);
        $payment_date = trim($_POST['payment_date']);
        $amount       = floatval($_POST['amount']);
        $status       = trim($_POST['status']);
        $allowed_status = ['Paid', 'Pending', 'Overdue'];
        $errors = [];
        if ($student_id <= 0) {
            $errors[] = 'Student is required.';
        }
        if ($payment_date === '' || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $payment_date)) {
            $errors[] = 'Payment date must be valid.';
        }
        if ($amount <= 0) {
            $errors[] = 'Amount must be greater than 0.';
        }
        if (!in_array($status, $allowed_status, true)) {
            $errors[] = 'Status is invalid.';
        }

        if (!empty($errors)) {
            $msg = implode(' ', $errors);
            $msg_type = 'danger';
        } else {
            $stmt = $conn->prepare("SELECT Payment_id FROM payment WHERE Student_id=? AND Payment_Date=? AND amount=? AND status=? LIMIT 1");
            $stmt->bind_param("isds", $student_id, $payment_date, $amount, $status);
            $stmt->execute();
            $dup = $stmt->get_result();
            $has_dup = $dup && $dup->num_rows > 0;
            $stmt->close();
            if ($has_dup) {
                $msg = 'Duplicate payment entry detected.';
                $msg_type = 'danger';
            } else {
                $stmt = $conn->prepare("INSERT INTO payment (Student_id, Payment_Date, amount, status) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isds", $student_id, $payment_date, $amount, $status);
                if ($stmt->execute()) {
                    $msg = 'Payment added successfully.';
                    $msg_type = 'success';
                } else {
                    $msg = 'Error adding payment: ' . $stmt->error;
                    $msg_type = 'danger';
                }
                $stmt->close();
            }
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $payment_id = intval($_POST['payment_id']);
        $status     = trim($_POST['status']);
        $allowed_status = ['Paid', 'Pending', 'Overdue'];
        if ($payment_id <= 0 || !in_array($status, $allowed_status, true)) {
            $msg = 'Invalid payment update.';
            $msg_type = 'danger';
        } else {
            $stmt = $conn->prepare("UPDATE payment SET status=? WHERE Payment_id=?");
            $stmt->bind_param("si", $status, $payment_id);
            if ($stmt->execute()) {
                $msg = 'Payment status updated.';
                $msg_type = 'success';
            } else {
                $msg = 'Error updating payment: ' . $stmt->error;
                $msg_type = 'danger';
            }
            $stmt->close();
        }
    }
}

// Fetch students in this hostel
$students = [];
$res = $conn->query("SELECT s.Student_id, s.name FROM student s JOIN room r ON s.Room_id = r.Room_id WHERE r.Hostel_id = $hostel_id ORDER BY s.name");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $students[] = $row;
    }
}

// Fetch payments for students in this hostel
$payments = [];
$res = $conn->query("SELECT p.Payment_id, p.Student_id, p.Payment_Date, p.amount, p.status, s.name AS Student_Name
                      FROM payment p
                      JOIN student s ON p.Student_id = s.Student_id
                      JOIN room r ON s.Room_id = r.Room_id
                      WHERE r.Hostel_id = $hostel_id
                      ORDER BY p.Payment_id DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $payments[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Warden Panel</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="layout">
    <div class="sidebar">
        <h2> Warden Panel</h2>
        <div class="warden-info">Welcome, <?php echo htmlspecialchars($warden_name); ?></div>
        <a href="dashboard.php"> Dashboard</a>
        <a href="payments.php" class="active"> Payments</a>
        <a href="attendance.php"> Attendance</a>
        <a href="manage_leaves.php"> Leave Approvals</a>
        <a href="room_allocation.php"> Room Allocation</a>
        <a href="../logout.php"> Logout</a>
    </div>
    <div class="main-content">
        <h1> Manage Payments</h1>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <!-- Add Payment Form -->
        <h3 class="mt-20">Add New Payment</h3>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">

            <label for="student_id">Student</label>
            <select id="student_id" name="student_id" required>
                <option value="">-- Select Student --</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?php echo $s['Student_id']; ?>">
                        <?php echo htmlspecialchars($s['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="payment_date">Payment Date</label>
            <input type="date" id="payment_date" name="payment_date" required>

            <label for="amount">Amount (₹)</label>
            <input type="number" id="amount" name="amount" step="0.01" min="1" placeholder="Enter amount" required>

            <label for="status">Status</label>
            <select id="status" name="status" required>
                <option value="">-- Select Status --</option>
                <option value="Paid">Paid</option>
                <option value="Pending">Pending</option>
                <option value="Overdue">Overdue</option>
            </select>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Add Payment</button>
            </div>
        </form>

        <!-- Payments Table -->
        <h3 class="mt-20">All Payments (My Hostel)</h3>
        <table>
            <thead>
                <tr>
                    <th>Payment ID</th>
                    <th>Student Name</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($payments) === 0): ?>
                    <tr><td colspan="6" class="text-center">No payments found.</td></tr>
                <?php else: ?>
                    <?php foreach ($payments as $p): ?>
                        <tr>
                            <td><?php echo $p['Payment_id']; ?></td>
                            <td><?php echo htmlspecialchars($p['Student_Name']); ?></td>
                            <td><?php echo htmlspecialchars($p['Payment_Date']); ?></td>
                            <td>₹<?php echo number_format($p['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($p['status']); ?></td>
                            <td>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="payment_id" value="<?php echo $p['Payment_id']; ?>">
                                    <select name="status" required style="width:100px;">
                                        <option value="Paid" <?php echo ($p['status'] === 'Paid') ? 'selected' : ''; ?>>Paid</option>
                                        <option value="Pending" <?php echo ($p['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Overdue" <?php echo ($p['status'] === 'Overdue') ? 'selected' : ''; ?>>Overdue</option>
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
