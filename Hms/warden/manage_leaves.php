<?php
include('../config.php');

if (!isset($_SESSION['warden_id'])) {
    header("Location: ../warden_login.php");
    exit();
}

$warden_id = $_SESSION['warden_id'];
$warden_name = $_SESSION['warden_name'];
$hostel_id = $_SESSION['warden_hostel_id'];

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
                $stmt = $conn->prepare("UPDATE leave_request lr JOIN student s ON lr.Student_id = s.Student_id JOIN room r ON s.Room_id = r.Room_id SET lr.Status=?, lr.Reviewed_by_role=NULL, lr.Reviewed_by_id=NULL, lr.Reviewed_at=NULL WHERE lr.Leave_id=? AND r.Hostel_id=?");
                $stmt->bind_param("sii", $status, $leave_id, $hostel_id);
            } else {
                $stmt = $conn->prepare("UPDATE leave_request lr JOIN student s ON lr.Student_id = s.Student_id JOIN room r ON s.Room_id = r.Room_id SET lr.Status=?, lr.Reviewed_by_role='Warden', lr.Reviewed_by_id=?, lr.Reviewed_at=NOW() WHERE lr.Leave_id=? AND r.Hostel_id=?");
                $stmt->bind_param("siii", $status, $warden_id, $leave_id, $hostel_id);
            }

            if ($stmt->execute()) {
                if ($stmt->affected_rows === 0) {
                    $msg = 'Leave request not found for your hostel.';
                    $msg_type = 'warning';
                } else {
                    $msg = 'Leave request updated.';
                    $msg_type = 'success';
                }
            } else {
                $msg = 'Error: ' . $stmt->error;
                $msg_type = 'danger';
            }
            $stmt->close();
        }
    }
}

$leaves = [];
$stmt = $conn->prepare("SELECT lr.*, s.name, r.Room_name
                        FROM leave_request lr
                        JOIN student s ON lr.Student_id = s.Student_id
                        JOIN room r ON s.Room_id = r.Room_id
                        WHERE r.Hostel_id = ?
                        ORDER BY lr.Requested_at DESC, lr.Leave_id DESC");
$stmt->bind_param("i", $hostel_id);
if ($stmt->execute()) {
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $leaves[] = $row;
    }
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Requests - Warden Panel</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="layout">
    <div class="sidebar">
        <h2> Warden Panel</h2>
        <div class="warden-info">Welcome, <?php echo htmlspecialchars($warden_name); ?></div>
        <a href="dashboard.php"> Dashboard</a>
        <a href="payments.php"> Payments</a>
        <a href="attendance.php"> Attendance</a>
        <a href="manage_leaves.php" class="active"> Leave Approvals</a>
        <a href="room_allocation.php"> Room Allocation</a>
        <a href="../logout.php"> Logout</a>
    </div>
    <div class="main-content">
        <h1> Manage Leave Requests</h1>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-<?php echo $msg_type; ?>"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>

        <h3 class="mt-20">Requests (My Hostel)</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
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
                    <tr><td colspan="9" class="text-center">No leave requests found.</td></tr>
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
                            <td><?php echo htmlspecialchars($l['Room_name']); ?></td>
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
