<?php
session_start();
include '../config.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$msg = '';
$msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $leave_id = intval($_POST['leave_id']);
        $status = trim($_POST['status']);
        $allowed = ['Pending', 'Approved', 'Rejected'];

        if (!in_array($status, $allowed, true)) {
            $msg = 'Invalid status.';
            $msg_type = 'danger';
        } else {
            if ($status === 'Pending') {
                $stmt = $conn->prepare("UPDATE leave_request SET Status=?, Reviewed_by_role=NULL, Reviewed_by_id=NULL, Reviewed_at=NULL WHERE Leave_id=?");
                $stmt->bind_param("si", $status, $leave_id);
            } else {
                $stmt = $conn->prepare("UPDATE leave_request SET Status=?, Reviewed_by_role='Admin', Reviewed_by_id=?, Reviewed_at=NOW() WHERE Leave_id=?");
                $stmt->bind_param("sii", $status, $admin_id, $leave_id);
            }

            if ($stmt->execute()) {
                $msg = 'Leave request updated.';
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
$res = $conn->query("SELECT lr.*, s.name, r.Room_name, h.Hostel_name
                      FROM leave_request lr
                      JOIN student s ON lr.Student_id = s.Student_id
                      LEFT JOIN room r ON s.Room_id = r.Room_id
                      LEFT JOIN hostel h ON r.Hostel_id = h.Hostel_id
                      ORDER BY lr.Requested_at DESC, lr.Leave_id DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $leaves[] = $row;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Leave Requests - HMS Admin</title>
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
        <a href="manage_complaints.php"> Complaints</a>
        <a href="manage_leaves.php" class="active"> Leave Requests</a>
        <a href="../logout.php"> Logout</a>
    </div>

    <div class="main-content">
        <h2> Manage Leave Requests</h2>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <h3 class="mt-20">All Requests</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Hostel</th>
                    <th>Room</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Reviewed</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($leaves) === 0): ?>
                    <tr><td colspan="10" style="text-align:center;">No leave requests found.</td></tr>
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
                            <td><?php echo htmlspecialchars($l['name']); ?></td>
                            <td><?php echo htmlspecialchars($l['Hostel_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($l['Room_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($l['Start_date']); ?></td>
                            <td><?php echo htmlspecialchars($l['End_date']); ?></td>
                            <td><?php echo htmlspecialchars($l['Reason']); ?></td>
                            <td><span class="<?php echo $status_class; ?>"><?php echo htmlspecialchars($l['Status']); ?></span></td>
                            <td><?php echo htmlspecialchars($reviewed); ?></td>
                            <td>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="leave_id" value="<?php echo $l['Leave_id']; ?>">
                                    <select name="status" style="padding:5px;">
                                        <option value="Pending" <?php echo ($l['Status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Approved" <?php echo ($l['Status'] === 'Approved') ? 'selected' : ''; ?>>Approved</option>
                                        <option value="Rejected" <?php echo ($l['Status'] === 'Rejected') ? 'selected' : ''; ?>>Rejected</option>
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
