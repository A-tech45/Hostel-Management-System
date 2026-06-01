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
        $date = trim($_POST['date']);
        $in_time = trim($_POST['in_time']);
        $out_time = trim($_POST['out_time']) ?: NULL;
        $errors = [];
        if ($student_id <= 0) {
            $errors[] = 'Student is required.';
        }
        if ($date === '' || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
            $errors[] = 'Date must be valid.';
        }
        if ($in_time === '' || !preg_match("/^\d{2}:\d{2}$/", $in_time)) {
            $errors[] = 'In time must be valid.';
        }
        if ($out_time !== NULL && $out_time !== '' && !preg_match("/^\d{2}:\d{2}$/", $out_time)) {
            $errors[] = 'Out time must be valid.';
        }
        if ($out_time !== NULL && $out_time !== '' && $in_time !== '' && $out_time < $in_time) {
            $errors[] = 'Out time cannot be earlier than in time.';
        }

        if (!empty($errors)) {
            $msg = implode(' ', $errors);
            $msg_type = 'danger';
        } else {
            $stmt = $conn->prepare("SELECT Attendance_id FROM attendance WHERE Student_id=? AND Date=? AND In_time=? LIMIT 1");
            $stmt->bind_param("iss", $student_id, $date, $in_time);
            $stmt->execute();
            $dup = $stmt->get_result();
            $has_dup = $dup && $dup->num_rows > 0;
            $stmt->close();
            if ($has_dup) {
                $msg = 'Duplicate attendance entry detected.';
                $msg_type = 'danger';
            } else {
                $stmt = $conn->prepare("INSERT INTO attendance (Student_id, Date, In_time, Out_time) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $student_id, $date, $in_time, $out_time);
                if ($stmt->execute()) {
                    $msg = 'Attendance recorded successfully.';
                    $msg_type = 'success';
                } else {
                    $msg = 'Error: ' . $stmt->error;
                    $msg_type = 'danger';
                }
                $stmt->close();
            }
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $attendance_id = intval($_POST['attendance_id']);
        $stmt = $conn->prepare("DELETE FROM attendance WHERE Attendance_id=?");
        $stmt->bind_param("i", $attendance_id);
        if ($stmt->execute()) {
            $msg = 'Record deleted successfully.';
            $msg_type = 'success';
        } else {
            $msg = 'Error: ' . $stmt->error;
            $msg_type = 'danger';
        }
        $stmt->close();
    }
}

$students = [];
$res = $conn->query("SELECT Student_id, name FROM student ORDER BY name");
if ($res) { while ($row = $res->fetch_assoc()) { $students[] = $row; } }

$attendance = [];
$res = $conn->query("SELECT a.*, s.name FROM attendance a JOIN student s ON a.Student_id = s.Student_id ORDER BY a.Date DESC, a.In_time DESC");
if ($res) { while ($row = $res->fetch_assoc()) { $attendance[] = $row; } }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Attendance - HMS Admin</title>
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
        <a href="manage_payments.php"> Payments</a>
        <a href="manage_attendance.php" class="active"> Attendance</a>
        <a href="manage_complaints.php"> Complaints</a>
        <a href="manage_leaves.php"> Leave Requests</a>
        <a href="../logout.php"> Logout</a>
    </div>

    <div class="main-content">
        <h2> Manage Attendance</h2>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <h3 class="mt-20">Record Attendance</h3>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <label for="student_id">Student</label>
            <select name="student_id" required>
                <option value="">-- Select Student --</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?php echo $s['Student_id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                <?php endforeach; ?>
            </select>
            <label for="date">Date</label>
            <input type="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
            <label for="in_time">In Time</label>
            <input type="time" name="in_time" required>
            <label for="out_time">Out Time (Optional)</label>
            <input type="time" name="out_time">
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Record</button>
            </div>
        </form>

        <h3 class="mt-20">Recent Records</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student Name</th>
                    <th>Date</th>
                    <th>In Time</th>
                    <th>Out Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($attendance) === 0): ?>
                    <tr><td colspan="6" style="text-align:center;">No records found.</td></tr>
                <?php else: ?>
                    <?php foreach ($attendance as $a): ?>
                        <tr>
                            <td><?php echo $a['Attendance_id']; ?></td>
                            <td><?php echo htmlspecialchars($a['name']); ?></td>
                            <td><?php echo htmlspecialchars($a['Date']); ?></td>
                            <td><?php echo htmlspecialchars($a['In_time']); ?></td>
                            <td><?php echo htmlspecialchars($a['Out_time'] ?? '-'); ?></td>
                            <td>
                                <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Delete record?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="attendance_id" value="<?php echo $a['Attendance_id']; ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
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
