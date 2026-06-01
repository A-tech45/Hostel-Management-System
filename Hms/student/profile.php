<?php
include('../config.php');
if (!isset($_SESSION['student_id'])) { header("Location: ../student_login.php"); exit(); }
$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

$msg = ''; $msg_type = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $errors = [];
    if ($phone === '' || !preg_match("/^[0-9]{10,15}$/", $phone)) {
        $errors[] = 'Phone must be 10-15 digits.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email must be valid.';
    }

    if (!empty($errors)) {
        $msg = implode(' ', $errors);
        $msg_type = 'danger';
    } else {
        $stmt = $conn->prepare("SELECT Student_id FROM student WHERE (phone=? OR email=?) AND Student_id <> ? LIMIT 1");
        $stmt->bind_param("ssi", $phone, $email, $student_id);
        $stmt->execute();
        $dup = $stmt->get_result();
        $has_dup = $dup && $dup->num_rows > 0;
        $stmt->close();

        if ($has_dup) {
            $msg = 'Phone or email already exists.';
            $msg_type = 'danger';
        } else {
            $stmt = $conn->prepare("UPDATE student SET phone=?, email=? WHERE Student_id=?");
            $stmt->bind_param("ssi", $phone, $email, $student_id);
            if ($stmt->execute()) { $msg = 'Profile updated successfully.'; $msg_type = 'success'; }
            else { $msg = 'Error: ' . $stmt->error; $msg_type = 'danger'; }
            $stmt->close();
        }
    }
}

// Fetch student info
$student = null;
$res = $conn->query("SELECT s.*, r.Room_name, r.Room_type, h.Hostel_name FROM student s LEFT JOIN room r ON s.Room_id=r.Room_id LEFT JOIN hostel h ON r.Hostel_id=h.Hostel_id WHERE s.Student_id=$student_id");
if ($res) $student = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Profile - Student</title><link rel="stylesheet" href="../style.css"></head><body>
<div class="layout">
<div class="sidebar">
<div class="info">Welcome, <?php echo htmlspecialchars($student_name); ?></div>
<a href="dashboard.php"> Dashboard</a>
<a href="payments.php"> Payments</a>
<a href="attendance.php"> Attendance</a>
<a href="leave_request.php"> Leave Request</a>
<a href="profile.php" class="active"> Profile</a>
<a href="../logout.php"> Logout</a>
</div>
<div class="main-content">
<h1> My Profile</h1>
<?php if (!empty($msg)): ?><div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

<div class="profile-card">
<table>
<tr><td>Student ID</td><td><?php echo $student['Student_id']; ?></td></tr>
<tr><td>Name</td><td><?php echo htmlspecialchars($student['name']); ?></td></tr>
<tr><td>Username</td><td><?php echo htmlspecialchars($student['username']); ?></td></tr>
<tr><td>Gender</td><td><?php echo htmlspecialchars($student['gender']); ?></td></tr>
<tr><td>Course</td><td><?php echo htmlspecialchars($student['course']); ?></td></tr>
<tr><td>Semester</td><td><?php echo $student['semester']; ?></td></tr>
<tr><td>Phone</td><td><?php echo htmlspecialchars($student['phone']); ?></td></tr>
<tr><td>Email</td><td><?php echo htmlspecialchars($student['email']); ?></td></tr>
<tr><td>Room</td><td><?php echo htmlspecialchars($student['Room_name'] ?? 'Not Allocated'); ?></td></tr>
<tr><td>Room Type</td><td><?php echo htmlspecialchars($student['Room_type'] ?? 'N/A'); ?></td></tr>
<tr><td>Hostel</td><td><?php echo htmlspecialchars($student['Hostel_name'] ?? 'N/A'); ?></td></tr>
</table>
</div>

<div class="edit-form">
<h3>Update Contact Info</h3>
<form method="POST"><input type="hidden" name="action" value="update">
<label>Phone</label><input type="tel" name="phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required pattern="[0-9]{10,15}">
<label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
<button type="submit" class="btn btn-success">Update Profile</button>
</form>
</div>
</div></div></body></html>
