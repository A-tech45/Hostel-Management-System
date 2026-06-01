<?php
include '../config.php';
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../admin_login.php');
    exit;
}

$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name      = trim($_POST['name']);
        $username  = trim($_POST['username']);
        $password  = trim($_POST['password']);
        $phone     = trim($_POST['phone']);
        $email     = trim($_POST['email']);
        $hostel_id = intval($_POST['hostel_id']);

        $errors = [];
        if ($name === '' || !preg_match("/^[A-Za-z .'-]{2,100}$/", $name)) {
            $errors[] = 'Name must be 2-100 letters and valid characters.';
        }
        if ($username === '' || !preg_match("/^[A-Za-z0-9_.-]{4,20}$/", $username)) {
            $errors[] = 'Username must be 4-20 characters and alphanumeric.';
        }
        if ($password === '' || strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        if ($phone === '' || !preg_match("/^[0-9]{10,15}$/", $phone)) {
            $errors[] = 'Phone must be 10-15 digits.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email must be valid.';
        }
        if ($hostel_id <= 0) {
            $errors[] = 'Hostel is required.';
        }

        if (!empty($errors)) {
            $msg = implode(' ', $errors);
            $msg_type = 'danger';
        } else {
            $stmt = $conn->prepare("SELECT Warden_id, username, Email, Phone FROM warden WHERE username = ? OR Email = ? OR Phone = ? LIMIT 1");
            $stmt->bind_param("sss", $username, $email, $phone);
            $stmt->execute();
            $dup = $stmt->get_result();
            $has_dup = $dup && $dup->num_rows > 0;
            $stmt->close();
            if ($has_dup) {
                $msg = 'Duplicate username, email, or phone found.';
                $msg_type = 'danger';
            } else {
                $stmt = $conn->prepare("INSERT INTO warden (Name, username, password, Phone, Email, Hostel_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssi", $name, $username, $password, $phone, $email, $hostel_id);
                if ($stmt->execute()) {
                    $msg = 'Warden added successfully.';
                    $msg_type = 'success';
                } else {
                    $msg = 'Error adding warden: ' . $stmt->error;
                    $msg_type = 'danger';
                }
                $stmt->close();
            }
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $warden_id = intval($_POST['warden_id']);
        $name      = trim($_POST['name']);
        $username  = trim($_POST['username']);
        $password  = trim($_POST['password']);
        $phone     = trim($_POST['phone']);
        $email     = trim($_POST['email']);
        $hostel_id = intval($_POST['hostel_id']);

        $errors = [];
        if ($warden_id <= 0) {
            $errors[] = 'Invalid warden selection.';
        }
        if ($name === '' || !preg_match("/^[A-Za-z .'-]{2,100}$/", $name)) {
            $errors[] = 'Name must be 2-100 letters and valid characters.';
        }
        if ($username === '' || !preg_match("/^[A-Za-z0-9_.-]{4,20}$/", $username)) {
            $errors[] = 'Username must be 4-20 characters and alphanumeric.';
        }
        if ($password === '' || strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }
        if ($phone === '' || !preg_match("/^[0-9]{10,15}$/", $phone)) {
            $errors[] = 'Phone must be 10-15 digits.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email must be valid.';
        }
        if ($hostel_id <= 0) {
            $errors[] = 'Hostel is required.';
        }

        if (!empty($errors)) {
            $msg = implode(' ', $errors);
            $msg_type = 'danger';
        } else {
            $stmt = $conn->prepare("SELECT Warden_id FROM warden WHERE (username = ? OR Email = ? OR Phone = ?) AND Warden_id <> ? LIMIT 1");
            $stmt->bind_param("sssi", $username, $email, $phone, $warden_id);
            $stmt->execute();
            $dup = $stmt->get_result();
            $has_dup = $dup && $dup->num_rows > 0;
            $stmt->close();
            if ($has_dup) {
                $msg = 'Duplicate username, email, or phone found.';
                $msg_type = 'danger';
            } else {
                $stmt = $conn->prepare("UPDATE warden SET Name=?, username=?, password=?, Phone=?, Email=?, Hostel_id=? WHERE Warden_id=?");
                $stmt->bind_param("sssssii", $name, $username, $password, $phone, $email, $hostel_id, $warden_id);
                if ($stmt->execute()) {
                    $msg = 'Warden updated successfully.';
                    $msg_type = 'success';
                } else {
                    $msg = 'Error updating warden: ' . $stmt->error;
                    $msg_type = 'danger';
                }
                $stmt->close();
            }
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $warden_id = intval($_POST['warden_id']);
        $stmt = $conn->prepare("DELETE FROM warden WHERE Warden_id=?");
        $stmt->bind_param("i", $warden_id);
        if ($stmt->execute()) {
            $msg = 'Warden deleted successfully.';
            $msg_type = 'success';
        } else {
            $msg = 'Error deleting warden: ' . $stmt->error;
            $msg_type = 'danger';
        }
        $stmt->close();
    }
}

$hostels = [];
$res = $conn->query("SELECT Hostel_id, Hostel_Name FROM hostel ORDER BY Hostel_Name");
if ($res) { while ($row = $res->fetch_assoc()) { $hostels[] = $row; } }

$wardens = [];
$res = $conn->query("SELECT w.Warden_id, w.Name, w.username, w.password, w.Phone, w.Email, w.Hostel_id, h.Hostel_Name
                      FROM warden w
                      LEFT JOIN hostel h ON w.Hostel_id = h.Hostel_id
                      ORDER BY w.Warden_id DESC");
if ($res) { while ($row = $res->fetch_assoc()) { $wardens[] = $row; } }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Wardens - Hostel Management System</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="admin-layout">
    <div class="sidebar">
        <h2> HMS Admin</h2>
        <a href="dashboard.php"> Dashboard</a>
        <a href="manage_hostels.php"> Hostels</a>
        <a href="manage_rooms.php"> rooms</a>
        <a href="manage_students.php"> Students</a>
        <a href="manage_wardens.php" class="active"> Wardens</a>
        <a href="manage_payments.php"> Payments</a>
        <a href="manage_attendance.php"> Attendance</a>
        <a href="manage_complaints.php"> Complaints</a>
        <a href="manage_leaves.php"> Leave Requests</a>
        <a href="../logout.php"> Logout</a>
    </div>

    <div class="main-content">
        <h2> Manage Wardens</h2>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <h3 class="mt-20">Add New Warden</h3>
        <form method="POST" action="">
            <input type="hidden" name="action" value="add">
            <label for="name">Name</label>
            <input type="text" id="name" name="name" placeholder="Enter warden name" required minlength="2" maxlength="100" pattern="[A-Za-z .'-]{2,100}">

            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Enter username" required minlength="4" maxlength="20" pattern="[A-Za-z0-9_.-]{4,20}">

            <label for="password">Password</label>
            <input type="text" id="password" name="password" placeholder="Enter password" required minlength="6">

            <label for="phone">Phone</label>
            <input type="tel" id="phone" name="phone" placeholder="Enter phone number" required pattern="[0-9]{10,15}">

            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Enter email address" required>

            <label for="hostel_id">Hostel</label>
            <select id="hostel_id" name="hostel_id" required>
                <option value="">-- Select Hostel --</option>
                <?php foreach ($hostels as $h): ?>
                    <option value="<?php echo $h['Hostel_id']; ?>"><?php echo htmlspecialchars($h['Hostel_Name']); ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <button type="submit" class="btn btn-primary">Add Warden</button>
        </form>

        <h3 class="mt-20">All Wardens</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Hostel</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($wardens) === 0): ?>
                    <tr><td colspan="7" style="text-align:center;">No wardens found.</td></tr>
                <?php else: ?>
                    <?php foreach ($wardens as $w): ?>
                        <tr>
                            <td><?php echo $w['Warden_id']; ?></td>
                            <td><?php echo htmlspecialchars($w['Name']); ?></td>
                            <td><?php echo htmlspecialchars($w['username']); ?></td>
                            <td><?php echo htmlspecialchars($w['Phone']); ?></td>
                            <td><?php echo htmlspecialchars($w['Email']); ?></td>
                            <td><?php echo htmlspecialchars($w['Hostel_Name'] ?? 'N/A'); ?></td>
                            <td>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" name="warden_id" value="<?php echo $w['Warden_id']; ?>">
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($w['Name']); ?>" required minlength="2" maxlength="100" pattern="[A-Za-z .'-]{2,100}" style="width:100px; padding:4px;">
                                    <input type="text" name="username" value="<?php echo htmlspecialchars($w['username']); ?>" required minlength="4" maxlength="20" pattern="[A-Za-z0-9_.-]{4,20}" style="width:80px; padding:4px;">
                                    <input type="text" name="password" value="<?php echo htmlspecialchars($w['password']); ?>" required minlength="6" style="width:80px; padding:4px;">
                                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($w['Phone']); ?>" required pattern="[0-9]{10,15}" style="width:100px; padding:4px;">
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($w['Email']); ?>" required style="width:120px; padding:4px;">
                                    <select name="hostel_id" required style="width:100px; padding:4px;">
                                        <?php foreach ($hostels as $h): ?>
                                            <option value="<?php echo $h['Hostel_id']; ?>" <?php echo ($h['Hostel_id'] == $w['Hostel_id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($h['Hostel_Name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-warning" style="margin-left:5px;">Update</button>
                                </form>
                                <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this warden?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="warden_id" value="<?php echo $w['Warden_id']; ?>">
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
