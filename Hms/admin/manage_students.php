<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM student WHERE Student_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_students.php");
    exit();
}

$form_errors = [];
$form_old = [];
$edit_student = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $name    = trim($_POST['name'] ?? '');
    $username= trim($_POST['username'] ?? '');
    $password= trim($_POST['password'] ?? '');
    $gender  = $_POST['gender'] ?? '';
    $course  = trim($_POST['course'] ?? '');
    $semester = intval($_POST['semester'] ?? 0);
    $phone   = trim($_POST['phone'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $room_id = intval($_POST['Room_id'] ?? 0);

    $errors = [];
    if ($name === '' || !preg_match("/^[A-Za-z .'-]{2,50}$/", $name)) {
        $errors[] = 'Name must be 2-50 letters and valid characters.';
    }
    if ($username === '' || !preg_match("/^[A-Za-z0-9_.-]{4,20}$/", $username)) {
        $errors[] = 'Username must be 4-20 characters and alphanumeric.';
    }
    if ($password === '' || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if (!in_array($gender, ['Male', 'Female', 'Other'], true)) {
        $errors[] = 'Gender must be a valid option.';
    }
    if ($course === '' || strlen($course) < 2 || strlen($course) > 50) {
        $errors[] = 'Course must be 2-50 characters.';
    }
    if ($semester < 1 || $semester > 10) {
        $errors[] = 'Semester must be between 1 and 10.';
    }
    if ($phone === '' || !preg_match("/^[0-9]{10,15}$/", $phone)) {
        $errors[] = 'Phone must be 10-15 digits.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email must be valid.';
    }
    if ($room_id <= 0) {
        $errors[] = 'Room is required.';
    }

    if (!empty($errors)) {
        $form_errors = $errors;
        $form_old = [
            'name' => $name,
            'username' => $username,
            'password' => $password,
            'gender' => $gender,
            'course' => $course,
            'semester' => $semester,
            'phone' => $phone,
            'email' => $email,
            'Room_id' => $room_id,
            'Student_id' => intval($_POST['Student_id'] ?? 0),
        ];
        if ($action === 'edit') {
            $edit_student = $form_old;
        }
    } else {
        $student_id = intval($_POST['Student_id'] ?? 0);
        if ($action === 'edit' && $student_id <= 0) {
            $form_errors = ['Invalid student selection.'];
        } else {
            $dup_sql = "SELECT Student_id, username, email, phone FROM student WHERE (username = ? OR email = ? OR phone = ?)";
            if ($action === 'edit') {
                $dup_sql .= " AND Student_id <> ?";
            }
            $stmt = $conn->prepare($dup_sql);
            if ($action === 'edit') {
                $stmt->bind_param("sssi", $username, $email, $phone, $student_id);
            } else {
                $stmt->bind_param("sss", $username, $email, $phone);
            }
            $stmt->execute();
            $dup_res = $stmt->get_result();
            $dup_errors = [];
            if ($dup_res && $dup_res->num_rows > 0) {
                while ($dup_row = $dup_res->fetch_assoc()) {
                    if ($dup_row['username'] === $username) { $dup_errors[] = 'Username already exists.'; }
                    if ($dup_row['email'] === $email) { $dup_errors[] = 'Email already exists.'; }
                    if ($dup_row['phone'] === $phone) { $dup_errors[] = 'Phone already exists.'; }
                }
            }
            $stmt->close();

            if (!empty($dup_errors)) {
                $form_errors = array_values(array_unique($dup_errors));
                $form_old = [
                    'name' => $name,
                    'username' => $username,
                    'password' => $password,
                    'gender' => $gender,
                    'course' => $course,
                    'semester' => $semester,
                    'phone' => $phone,
                    'email' => $email,
                    'Room_id' => $room_id,
                    'Student_id' => $student_id,
                ];
                if ($action === 'edit') {
                    $edit_student = $form_old;
                }
            } else {
                if ($action === 'add') {
                    $stmt = $conn->prepare("INSERT INTO student (name, username, password, gender, course, semester, phone, email, Room_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssissi", $name, $username, $password, $gender, $course, $semester, $phone, $email, $room_id);
                    $stmt->execute();
                    $stmt->close();
                } elseif ($action === 'edit') {
                    $stmt = $conn->prepare("UPDATE student SET name=?, username=?, password=?, gender=?, course=?, semester=?, phone=?, email=?, Room_id=? WHERE Student_id=?");
                    $stmt->bind_param("sssssissii", $name, $username, $password, $gender, $course, $semester, $phone, $email, $room_id, $student_id);
                    $stmt->execute();
                    $stmt->close();
                }
                header("Location: manage_students.php");
                exit();
            }
        }
    }
}

if ($edit_student === null && isset($_GET['edit'])) {
    $eid = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM student WHERE Student_id = ?");
    $stmt->bind_param("i", $eid);
    $stmt->execute();
    $edit_student = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$rooms = $conn->query("SELECT r.Room_id, r.Room_name, h.Hostel_name FROM room r JOIN hostel h ON r.Hostel_id = h.Hostel_id ORDER BY h.Hostel_name, r.Room_name");
$students = $conn->query("SELECT s.*, r.Room_name, h.Hostel_name FROM student s LEFT JOIN room r ON s.Room_id = r.Room_id LEFT JOIN hostel h ON r.Hostel_id = h.Hostel_id ORDER BY s.Student_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Manage Students - HMS</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="admin-layout">
        <div class="sidebar">
            <h2 style="color:white;text-align:center;padding:10px;">HMS Admin</h2>
            <a href="dashboard.php"> Dashboard</a>
            <a href="manage_hostels.php"> Hostels</a>
            <a href="manage_rooms.php" > Rooms</a>
            <a href="manage_students.php" class="active"> Students</a>
            <a href="manage_wardens.php">Wardens</a>
            <a href="manage_payments.php"> Payments</a>
            <a href="manage_attendance.php"> Attendance</a>
            <a href="manage_complaints.php"> Complaints</a>
            <a href="manage_leaves.php"> Leave Requests</a>
            <a href="../logout.php"> Logout</a>
        </div>
        <div class="main">
            <h2>Manage Students</h2>
            <div class="form-card">
                <h3><?php echo $edit_student ? ' Edit Student' : ' Add New Student'; ?></h3>
                <?php if (!empty($form_errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($form_errors as $error): ?>
                            <div><?php echo htmlspecialchars($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <form method="POST" action="manage_students.php">
                    <input type="hidden" name="action" value="<?php echo $edit_student ? 'edit' : 'add'; ?>">
                    <?php if ($edit_student): ?><input type="hidden" name="Student_id" value="<?php echo $edit_student['Student_id']; ?>"><?php endif; ?>
                    <div class="form-grid">
                        <div class="form-group"><label>Name</label><input type="text" name="name" value="<?php echo htmlspecialchars($edit_student['name'] ?? ($form_old['name'] ?? '')); ?>" required pattern="[A-Za-z .'-]{2,50}" title="2-50 letters and valid characters"></div>
                        <div class="form-group"><label>Username</label><input type="text" name="username" value="<?php echo htmlspecialchars($edit_student['username'] ?? ($form_old['username'] ?? '')); ?>" required pattern="[A-Za-z0-9_.-]{4,20}" title="4-20 letters/numbers/._-"></div>
                        <div class="form-group"><label>Password</label><input type="text" name="password" value="<?php echo htmlspecialchars($edit_student['password'] ?? ($form_old['password'] ?? '')); ?>" required minlength="6" title="At least 6 characters"></div>
                        <div class="form-group"><label>Gender</label><select name="gender" required>
                            <option value="Male" <?php echo (($edit_student['gender'] ?? ($form_old['gender'] ?? '')) === 'Male') ? 'selected' : ''; ?>>Male</option>
                            <option value="Female" <?php echo (($edit_student['gender'] ?? ($form_old['gender'] ?? '')) === 'Female') ? 'selected' : ''; ?>>Female</option>
                            <option value="Other" <?php echo (($edit_student['gender'] ?? ($form_old['gender'] ?? '')) === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select></div>
                        <div class="form-group"><label>Course</label><input type="text" name="course" value="<?php echo htmlspecialchars($edit_student['course'] ?? ($form_old['course'] ?? '')); ?>" required minlength="2" maxlength="50"></div>
                        <div class="form-group"><label>Semester</label><input type="number" name="semester" min="1" max="10" value="<?php echo htmlspecialchars($edit_student['semester'] ?? ($form_old['semester'] ?? '')); ?>" required></div>
                        <div class="form-group"><label>Phone</label><input type="text" name="phone" value="<?php echo htmlspecialchars($edit_student['phone'] ?? ($form_old['phone'] ?? '')); ?>" required pattern="[0-9]{10,15}" title="10-15 digits"></div>
                        <div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($edit_student['email'] ?? ($form_old['email'] ?? '')); ?>" required></div>
                        <div class="form-group"><label>Room</label><select name="Room_id" required>
                            <option value="">-- Select Room --</option>
                            <?php if ($rooms) { $rooms->data_seek(0); while ($room = $rooms->fetch_assoc()): ?>
                                <option value="<?php echo $room['Room_id']; ?>" <?php echo (($edit_student['Room_id'] ?? '') == $room['Room_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($room['Room_name'] . ' (' . $room['Hostel_name'] . ')'); ?>
                                </option>
                            <?php endwhile; } ?>
                        </select></div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><?php echo $edit_student ? '💾 Update Student' : '➕ Add Student'; ?></button>
                        <?php if ($edit_student): ?><a href="manage_students.php" class="btn btn-secondary">✖ Cancel</a><?php endif; ?>
                    </div>
                </form>
            </div>
            <div class="table-card">
                <table>
                    <thead><tr><th>#</th><th>Name</th><th>Username</th><th>Gender</th><th>Course</th><th>Semester</th><th>Phone</th><th>Email</th><th>Room</th><th>Hostel</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php if ($students && $students->num_rows > 0): while ($row = $students->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['Student_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                <td><?php echo htmlspecialchars($row['course']); ?></td>
                                <td><?php echo $row['semester']; ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['Room_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['Hostel_name'] ?? 'N/A'); ?></td>
                                <td class="actions">
                                    <a href="manage_students.php?edit=<?php echo $row['Student_id']; ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
                                    <a href="manage_students.php?delete=<?php echo $row['Student_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?');">🗑️ Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="11" style="text-align:center; color:#999;">No students found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
