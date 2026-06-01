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
    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $complaint_id = intval($_POST['complaint_id']);
        $status = trim($_POST['status']);

        $stmt = $conn->prepare("UPDATE complaint SET Status=? WHERE Complaint_id=?");
        $stmt->bind_param("si", $status, $complaint_id);
        if ($stmt->execute()) {
            $msg = 'Complaint status updated.';
            $msg_type = 'success';
        } else {
            $msg = 'Error: ' . $stmt->error;
            $msg_type = 'danger';
        }
        $stmt->close();
    }
}

$complaints = [];
$res = $conn->query("SELECT c.*, s.name FROM complaint c JOIN student s ON c.Student_id = s.Student_id ORDER BY c.Date DESC");
if ($res) { while ($row = $res->fetch_assoc()) { $complaints[] = $row; } }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Complaints - HMS Admin</title>
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
        <a href="manage_attendance.php"> Attendance</a>
        <a href="manage_complaints.php" class="active"> Complaints</a>
        <a href="manage_leaves.php"> Leave Requests</a>
        <a href="../logout.php"> Logout</a>
    </div>

    <div class="main-content">
        <h2> Manage Complaints</h2>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <h3 class="mt-20">All Complaints</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student Name</th>
                    <th>Description</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($complaints) === 0): ?>
                    <tr><td colspan="6" style="text-align:center;">No complaints found.</td></tr>
                <?php else: ?>
                    <?php foreach ($complaints as $c): ?>
                        <tr>
                            <td><?php echo $c['Complaint_id']; ?></td>
                            <td><?php echo htmlspecialchars($c['name']); ?></td>
                            <td><?php echo htmlspecialchars($c['Description']); ?></td>
                            <td><?php echo htmlspecialchars($c['Date']); ?></td>
                            <td>
                                <?php 
                                    if ($c['Status'] == 'Resolved') echo '<span class="status-resolved">Resolved</span>';
                                    else echo '<span class="status-pending">Pending</span>';
                                ?>
                            </td>
                            <td>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="complaint_id" value="<?php echo $c['Complaint_id']; ?>">
                                    <select name="status" style="padding:5px;">
                                        <option value="Pending" <?php echo ($c['Status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Resolved" <?php echo ($c['Status'] == 'Resolved') ? 'selected' : ''; ?>>Resolved</option>
                                    </select>
                                    <button type="submit" class="btn btn-primary">Update</button>
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
