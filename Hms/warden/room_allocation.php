<?php
include('../config.php');
if (!isset($_SESSION['warden_id'])) { header("Location: ../warden_login.php"); exit(); }
$warden_name = $_SESSION['warden_name'];
$hostel_id = $_SESSION['warden_hostel_id'];
$msg = ''; $msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'allocate') {
        $student_id = intval($_POST['student_id']);
        $room_id = intval($_POST['room_id']);
        $errors = [];
        if ($student_id <= 0) {
            $errors[] = 'Student is required.';
        }
        if ($room_id <= 0) {
            $errors[] = 'Room is required.';
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT s.Student_id, s.Room_id, r.Hostel_id FROM student s LEFT JOIN room r ON s.Room_id = r.Room_id WHERE s.Student_id = ? AND (r.Hostel_id = ? OR s.Room_id IS NULL)");
            $stmt->bind_param("ii", $student_id, $hostel_id);
            $stmt->execute();
            $student_res = $stmt->get_result();
            $student_row = $student_res ? $student_res->fetch_assoc() : null;
            $stmt->close();

            if (!$student_row) {
                $errors[] = 'Student not found for your hostel.';
            }
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT Room_id, capacity, status FROM room WHERE Room_id = ? AND Hostel_id = ?");
            $stmt->bind_param("ii", $room_id, $hostel_id);
            $stmt->execute();
            $room_res = $stmt->get_result();
            $room_row = $room_res ? $room_res->fetch_assoc() : null;
            $stmt->close();

            if (!$room_row) {
                $errors[] = 'Room not found for your hostel.';
            } elseif ($room_row['status'] === 'Maintenance') {
                $errors[] = 'Room is under maintenance.';
            }
        }

        if (empty($errors)) {
            if (!empty($student_row['Room_id']) && intval($student_row['Room_id']) === $room_id) {
                $errors[] = 'Student is already allocated to this room.';
            }
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM student WHERE Room_id = ?");
            $stmt->bind_param("i", $room_id);
            $stmt->execute();
            $count_res = $stmt->get_result();
            $count_row = $count_res ? $count_res->fetch_assoc() : ['cnt' => 0];
            $stmt->close();
            if (intval($count_row['cnt']) >= intval($room_row['capacity'])) {
                $errors[] = 'Room capacity is already full.';
            }
        }

        if (!empty($errors)) {
            $msg = implode(' ', $errors);
            $msg_type = 'danger';
        } else {
            $stmt = $conn->prepare("UPDATE student SET Room_id=? WHERE Student_id=?");
            $stmt->bind_param("ii", $room_id, $student_id);
            if ($stmt->execute()) { $msg = 'Room allocated successfully.'; $msg_type = 'success'; }
            else { $msg = 'Error: ' . $stmt->error; $msg_type = 'danger'; }
            $stmt->close();
        }
    }
    if (isset($_POST['action']) && $_POST['action'] === 'deallocate') {
        $student_id = intval($_POST['student_id']);
        if ($student_id <= 0) {
            $msg = 'Invalid student selection.';
            $msg_type = 'danger';
        } else {
            $stmt = $conn->prepare("UPDATE student SET Room_id=NULL WHERE Student_id=?");
            $stmt->bind_param("i", $student_id);
            if ($stmt->execute()) { $msg = 'Room deallocated.'; $msg_type = 'success'; }
            else { $msg = 'Error: ' . $stmt->error; $msg_type = 'danger'; }
            $stmt->close();
        }
    }
}

// Rooms in this hostel
$rooms = [];
$res = $conn->query("SELECT Room_id, Room_name, Room_type, capacity, status FROM room WHERE Hostel_id=$hostel_id ORDER BY Room_name");
if ($res) { while ($row = $res->fetch_assoc()) $rooms[] = $row; }

// Students with room info
$students = [];
$res = $conn->query("SELECT s.Student_id, s.name, s.Room_id, r.Room_name FROM student s LEFT JOIN room r ON s.Room_id=r.Room_id LEFT JOIN hostel h ON r.Hostel_id=h.Hostel_id WHERE r.Hostel_id=$hostel_id OR s.Room_id IS NULL ORDER BY s.name");
if ($res) { while ($row = $res->fetch_assoc()) $students[] = $row; }
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Room Allocation - Warden</title><link rel="stylesheet" href="../style.css"></head><body>
<div class="layout">
<div class="sidebar">
<h2> Warden Panel</h2>
<div class="info">Welcome, <?php echo htmlspecialchars($warden_name); ?></div>
<a href="dashboard.php"> Dashboard</a><a href="payments.php"> Payments</a>
<a href="attendance.php"> Attendance</a><a href="manage_leaves.php"> Leave Approvals</a><a href="room_allocation.php" class="active"> Room Allocation</a>
<a href="../logout.php"> Logout</a>
</div>
<div class="main-content">
<h1> Room Allocation</h1>
<?php if (!empty($msg)): ?><div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

<h3 class="mt-20">Allocate Room to Student</h3>
<form method="POST"><input type="hidden" name="action" value="allocate">
<label>Student</label><select name="student_id" required><option value="">-- Select Student --</option>
<?php foreach ($students as $s): ?><option value="<?php echo $s['Student_id']; ?>"><?php echo htmlspecialchars($s['name']); ?> (<?php echo $s['Room_name'] ? $s['Room_name'] : 'No Room'; ?>)</option><?php endforeach; ?></select>
<label>Room</label><select name="room_id" required><option value="">-- Select Room --</option>
<?php foreach ($rooms as $r): ?><option value="<?php echo $r['Room_id']; ?>"><?php echo htmlspecialchars($r['Room_name'].' - '.$r['Room_type'].' (Cap: '.$r['capacity'].', '.$r['status'].')'); ?></option><?php endforeach; ?></select>
<div class="form-actions">
<button type="submit" class="btn btn-primary">Allocate Room</button>
</div></form>

<h3 class="mt-20">Current Allocations</h3>
<table><thead><tr><th>Student ID</th><th>Student Name</th><th>Room</th><th>Actions</th></tr></thead><tbody>
<?php if (count($students) === 0): ?><tr><td colspan="4" class="text-center">No students.</td></tr>
<?php else: foreach ($students as $s): ?><tr>
<td><?php echo $s['Student_id']; ?></td><td><?php echo htmlspecialchars($s['name']); ?></td>
<td><?php echo $s['Room_name'] ? htmlspecialchars($s['Room_name']) : '<em>Not Allocated</em>'; ?></td>
<td><?php if ($s['Room_id']): ?>
<form method="POST" style="display:inline" onsubmit="return confirm('Deallocate room?')"><input type="hidden" name="action" value="deallocate">
<input type="hidden" name="student_id" value="<?php echo $s['Student_id']; ?>"><button type="submit" class="btn btn-danger">Deallocate</button></form>
<?php else: echo '<span style="color:#999">N/A</span>'; endif; ?></td>
</tr><?php endforeach; endif; ?></tbody></table>

<h3 class="mt-20">Rooms Overview</h3>
<table><thead><tr><th>Room ID</th><th>Room Name</th><th>Type</th><th>Capacity</th><th>Status</th></tr></thead><tbody>
<?php foreach ($rooms as $r): ?><tr>
<td><?php echo $r['Room_id']; ?></td><td><?php echo htmlspecialchars($r['Room_name']); ?></td>
<td><?php echo $r['Room_type']; ?></td><td><?php echo $r['capacity']; ?></td>
<td><?php echo $r['status']; ?></td></tr><?php endforeach; ?></tbody></table>
</div></div></body></html>
