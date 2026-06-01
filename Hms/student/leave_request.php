<?php
include('../config.php');
if (!isset($_SESSION['student_id'])) { header("Location: ../student_login.php"); exit(); }
$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

$msg = '';
$msg_type = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'apply') {
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $reason = trim($_POST['reason'] ?? '');

    if ($start_date === '' || $end_date === '') {
        $errors[] = 'Start and end dates are required.';
    } elseif (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $start_date) || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $end_date)) {
        $errors[] = 'Dates must be valid.';
    } elseif (strtotime($end_date) < strtotime($start_date)) {
        $errors[] = 'End date must be on or after start date.';
    }

    if ($reason === '' || strlen($reason) < 5) {
        $errors[] = 'Reason must be at least 5 characters.';
    } elseif (strlen($reason) > 500) {
        $errors[] = 'Reason must be 500 characters or less.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT Leave_id FROM leave_request WHERE Student_id=? AND Start_date=? AND End_date=? AND Reason=? LIMIT 1");
        $stmt->bind_param("isss", $student_id, $start_date, $end_date, $reason);
        $stmt->execute();
        $dup = $stmt->get_result();
        $has_dup = $dup && $dup->num_rows > 0;
        $stmt->close();

        if ($has_dup) {
            $errors[] = 'Duplicate leave request detected.';
        } else {
            $stmt = $conn->prepare("INSERT INTO leave_request (Student_id, Start_date, End_date, Reason) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $student_id, $start_date, $end_date, $reason);
            if ($stmt->execute()) {
                $msg = 'Leave request submitted.';
                $msg_type = 'success';
            } else {
                $msg = 'Error: ' . $stmt->error;
                $msg_type = 'danger';
            }
            $stmt->close();
        }
    }
}

$leaves = [];
$stmt = $conn->prepare("SELECT Leave_id, Start_date, End_date, Reason, Status, Reviewed_by_role, Reviewed_at, Requested_at FROM leave_request WHERE Student_id=? ORDER BY Requested_at DESC, Leave_id DESC");
$stmt->bind_param("i", $student_id);
if ($stmt->execute()) {
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $leaves[] = $row;
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Leave Requests - Student</title><link rel="stylesheet" href="../style.css"></head><body>
<div class="layout">
<div class="sidebar">
<div class="info">Welcome, <?php echo htmlspecialchars($student_name); ?></div>
<a href="dashboard.php"> Dashboard</a>
<a href="payments.php"> Payments</a>
<a href="attendance.php"> Attendance</a>
<a href="leave_request.php" class="active"> Leave Request</a>
<a href="profile.php"> Profile</a>
<a href="../logout.php"> Logout</a>
</div>
<div class="main-content">
<h1> Leave Requests</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <div><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($msg)): ?>
    <div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<h3 class="mt-20">Apply for Leave</h3>
<form method="POST" action="">
    <input type="hidden" name="action" value="apply">
    <label for="start_date">Start Date</label>
    <input type="date" id="start_date" name="start_date" required>

    <label for="end_date">End Date</label>
    <input type="date" id="end_date" name="end_date" required>

    <label for="reason">Reason</label>
    <textarea id="reason" name="reason" rows="4" required minlength="5" maxlength="500" placeholder="Enter reason for leave"></textarea>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Submit Request</button>
    </div>
</form>

<h3 class="mt-20">My Requests</h3>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>From</th>
            <th>To</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Reviewed</th>
            <th>Requested</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($leaves) === 0): ?>
            <tr><td colspan="7" class="text-center">No leave requests yet.</td></tr>
        <?php else: ?>
            <?php foreach ($leaves as $l): ?>
                <?php
                $status_class = 'status-pending';
                if ($l['Status'] === 'Approved') {
                    $status_class = 'status-approved';
                } elseif ($l['Status'] === 'Rejected') {
                    $status_class = 'status-rejected';
                }
                $reviewed = '-';
                if (!empty($l['Reviewed_at']) && !empty($l['Reviewed_by_role'])) {
                    $reviewed = $l['Reviewed_by_role'] . ' on ' . $l['Reviewed_at'];
                }
                ?>
                <tr>
                    <td><?php echo $l['Leave_id']; ?></td>
                    <td><?php echo htmlspecialchars($l['Start_date']); ?></td>
                    <td><?php echo htmlspecialchars($l['End_date']); ?></td>
                    <td><?php echo htmlspecialchars($l['Reason']); ?></td>
                    <td><span class="<?php echo $status_class; ?>"><?php echo htmlspecialchars($l['Status']); ?></span></td>
                    <td><?php echo htmlspecialchars($reviewed); ?></td>
                    <td><?php echo htmlspecialchars($l['Requested_at']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>
</div></div></body></html>
