<?php
include('../config.php');
if (!isset($_SESSION['warden_id'])) { header("Location: ../warden_login.php"); exit(); }
$warden_name = $_SESSION['warden_name'];
$hostel_id = $_SESSION['warden_hostel_id'];
$msg = ''; $msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $student_id = intval($_POST['student_id']);
        $date = trim($_POST['date']);
        $in_time = trim($_POST['in_time']);
        $out_time = trim($_POST['out_time']);
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
        if ($out_time === '' || !preg_match("/^\d{2}:\d{2}$/", $out_time)) {
            $errors[] = 'Out time must be valid.';
        }
        if ($out_time !== '' && $in_time !== '' && $out_time < $in_time) {
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
                if ($stmt->execute()) { $msg = 'Attendance added.'; $msg_type = 'success'; }
                else { $msg = 'Error: ' . $stmt->error; $msg_type = 'danger'; }
                $stmt->close();
            }
        }
    }
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $aid = intval($_POST['attendance_id']);
        $stmt = $conn->prepare("DELETE FROM attendance WHERE Attendance_id=?");
        $stmt->bind_param("i", $aid);
        $stmt->execute(); $stmt->close();
        $msg = 'Record deleted.'; $msg_type = 'success';
    }
}

$students = [];
$res = $conn->query("SELECT s.Student_id, s.name FROM student s JOIN room r ON s.Room_id=r.Room_id WHERE r.Hostel_id=$hostel_id ORDER BY s.name");
if ($res) { while ($row = $res->fetch_assoc()) $students[] = $row; }

$attendance = [];
$res = $conn->query("SELECT a.*, s.name AS Student_Name FROM attendance a JOIN student s ON a.Student_id=s.Student_id JOIN room r ON s.Room_id=r.Room_id WHERE r.Hostel_id=$hostel_id ORDER BY a.Date DESC");
if ($res) { while ($row = $res->fetch_assoc()) $attendance[] = $row; }
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Attendance - Warden</title><link rel="stylesheet" href="../style.css"></head><body>
<div class="layout">
<div class="sidebar">
<h2> Warden Panel</h2>
<div class="info">Welcome, <?php echo htmlspecialchars($warden_name); ?></div>
<a href="dashboard.php"> Dashboard</a><a href="payments.php"> Payments</a>
<a href="attendance.php" class="active"> Attendance</a><a href="manage_leaves.php"> Leave Approvals</a><a href="room_allocation.php"> Room Allocation</a>
<a href="../logout.php"> Logout</a>
</div>
<div class="main-content">
<h1> Manage Attendance</h1>
<?php if (!empty($msg)): ?><div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

<h3 class="mt-20">Add Attendance Record</h3>
<form method="POST"><input type="hidden" name="action" value="add">
<label>Student</label><select name="student_id" required><option value="">-- Select --</option>
<?php foreach ($students as $s): ?><option value="<?php echo $s['Student_id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option><?php endforeach; ?></select>
<label>Date</label><input type="date" name="date" required>
<label>In Time</label><input type="time" name="in_time" required>
<label>Out Time</label><input type="time" name="out_time" required>
<div class="form-actions">
<button type="submit" class="btn btn-primary">Add Record</button>
</div></form>

<h3 class="mt-20">Attendance Records</h3>
<table><thead><tr><th>ID</th><th>Student</th><th>Date</th><th>In</th><th>Out</th><th>Actions</th></tr></thead><tbody>
<?php if (count($attendance) === 0): ?><tr><td colspan="6" class="text-center">No records.</td></tr>
<?php else: foreach ($attendance as $a): ?><tr>
<td><?php echo $a['Attendance_id']; ?></td><td><?php echo htmlspecialchars($a['Student_Name']); ?></td>
<td><?php echo $a['Date']; ?></td><td><?php echo $a['In_time']; ?></td><td><?php echo $a['Out_time']; ?></td>
<td><form method="POST" style="display:inline" onsubmit="return confirm('Delete?')"><input type="hidden" name="action" value="delete">
<input type="hidden" name="attendance_id" value="<?php echo $a['Attendance_id']; ?>"><button type="submit" class="btn btn-danger">Delete</button></form></td>
</tr><?php endforeach; endif; ?></tbody></table>
</div></div></body></html>
