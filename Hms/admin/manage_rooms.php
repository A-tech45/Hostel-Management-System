<?php
session_start();
include('../config.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

$success = "";
$error = "";

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = mysqli_prepare($conn, "DELETE FROM room WHERE Room_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) { $success = "Room deleted successfully!"; }
    else { $error = "Error deleting room: " . mysqli_error($conn); }
    mysqli_stmt_close($stmt);
}

if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $room_name = trim($_POST['Room_name']);
    $room_type = trim($_POST['Room_type']);
    $capacity = intval($_POST['capacity']);
    $status = trim($_POST['status']);
    $hostel_id = intval($_POST['Hostel_id']);
    $allowed_types = ['Single', 'Double', 'Triple'];
    $allowed_status = ['Available', 'Occupied', 'Maintenance'];

    if ($room_name === '' || !preg_match("/^[A-Za-z0-9 -]{1,50}$/", $room_name)) {
        $error = "Room name must be 1-50 characters and alphanumeric.";
    } elseif (!in_array($room_type, $allowed_types, true)) {
        $error = "Room type is invalid.";
    } elseif ($capacity <= 0) {
        $error = "Capacity must be greater than 0.";
    } elseif (!in_array($status, $allowed_status, true)) {
        $error = "Status is invalid.";
    } elseif ($hostel_id <= 0) {
        $error = "Hostel is required.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT Room_id FROM room WHERE LOWER(Room_name) = LOWER(?) AND Hostel_id = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "si", $room_name, $hostel_id);
        mysqli_stmt_execute($stmt);
        $dup = mysqli_stmt_get_result($stmt);
        $has_dup = $dup && mysqli_num_rows($dup) > 0;
        mysqli_stmt_close($stmt);
        if ($has_dup) {
            $error = "Room name already exists for this hostel.";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO room (Room_name, Room_type, capacity, status, Hostel_id) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssisi", $room_name, $room_type, $capacity, $status, $hostel_id);
            if (mysqli_stmt_execute($stmt)) { $success = "Room added successfully!"; }
            else { $error = "Error adding room: " . mysqli_error($conn); }
            mysqli_stmt_close($stmt);
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] == 'edit') {
    $room_id = intval($_POST['Room_id']);
    $room_name = trim($_POST['Room_name']);
    $room_type = trim($_POST['Room_type']);
    $capacity = intval($_POST['capacity']);
    $status = trim($_POST['status']);
    $hostel_id = intval($_POST['Hostel_id']);
    $allowed_types = ['Single', 'Double', 'Triple'];
    $allowed_status = ['Available', 'Occupied', 'Maintenance'];

    if ($room_id <= 0) {
        $error = "Invalid room selection.";
    } elseif ($room_name === '' || !preg_match("/^[A-Za-z0-9 -]{1,50}$/", $room_name)) {
        $error = "Room name must be 1-50 characters and alphanumeric.";
    } elseif (!in_array($room_type, $allowed_types, true)) {
        $error = "Room type is invalid.";
    } elseif ($capacity <= 0) {
        $error = "Capacity must be greater than 0.";
    } elseif (!in_array($status, $allowed_status, true)) {
        $error = "Status is invalid.";
    } elseif ($hostel_id <= 0) {
        $error = "Hostel is required.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT Room_id FROM room WHERE LOWER(Room_name) = LOWER(?) AND Hostel_id = ? AND Room_id <> ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "sii", $room_name, $hostel_id, $room_id);
        mysqli_stmt_execute($stmt);
        $dup = mysqli_stmt_get_result($stmt);
        $has_dup = $dup && mysqli_num_rows($dup) > 0;
        mysqli_stmt_close($stmt);
        if ($has_dup) {
            $error = "Room name already exists for this hostel.";
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE room SET Room_name = ?, Room_type = ?, capacity = ?, status = ?, Hostel_id = ? WHERE Room_id = ?");
            mysqli_stmt_bind_param($stmt, "ssisii", $room_name, $room_type, $capacity, $status, $hostel_id, $room_id);
            if (mysqli_stmt_execute($stmt)) { $success = "Room updated successfully!"; }
            else { $error = "Error updating room: " . mysqli_error($conn); }
            mysqli_stmt_close($stmt);
        }
    }
}

$edit_room = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = mysqli_prepare($conn, "SELECT * FROM room WHERE Room_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $edit_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $edit_room = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

$hostels = mysqli_query($conn, "SELECT Hostel_id, Hostel_name FROM hostel ORDER BY Hostel_name ASC");
$rooms = mysqli_query($conn, "SELECT r.*, h.Hostel_name FROM room r LEFT JOIN hostel h ON r.Hostel_id = h.Hostel_id ORDER BY r.Room_id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Manage Rooms - HMS</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="admin-layout">
        <div class="sidebar">
            <h2 style="color:white;text-align:center;padding:10px;">HMS Admin</h2>
            <a href="dashboard.php"> Dashboard</a>
            <a href="manage_hostels.php"> Hostels</a>
            <a href="manage_rooms.php" class="active"> Rooms</a>
            <a href="manage_students.php"> Students</a>
            <a href="manage_wardens.php">Wardens</a>
            <a href="manage_payments.php"> Payments</a>
            <a href="manage_attendance.php"> Attendance</a>
            <a href="manage_complaints.php"> Complaints</a>
            <a href="manage_leaves.php"> Leave Requests</a>
            <a href="../logout.php"> Logout</a>
        </div>
        <div class="main-content">
            <h1>Manage Rooms</h1>
            <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
            <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            
            <div class="form-container">
                <?php if ($edit_room): ?>
                    <h2>Edit Room</h2>
                    <form method="POST" action="manage_rooms.php">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="Room_id" value="<?php echo $edit_room['Room_id']; ?>">
                        <div class="form-row">
                            <div class="form-group"><label>Room Name</label><input type="text" name="Room_name" value="<?php echo htmlspecialchars($edit_room['Room_name']); ?>" required minlength="1" maxlength="50" pattern="[A-Za-z0-9 -]{1,50}"></div>
                            <div class="form-group"><label>Room Type</label><select name="Room_type" required>
                                <option value="Single" <?php echo ($edit_room['Room_type'] == 'Single') ? 'selected' : ''; ?>>Single</option>
                                <option value="Double" <?php echo ($edit_room['Room_type'] == 'Double') ? 'selected' : ''; ?>>Double</option>
                                <option value="Triple" <?php echo ($edit_room['Room_type'] == 'Triple') ? 'selected' : ''; ?>>Triple</option>
                            </select></div>
                            <div class="form-group"><label>Capacity</label><input type="number" name="capacity" value="<?php echo $edit_room['capacity']; ?>" min="1" required></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>Status</label><select name="status" required>
                                <option value="Available" <?php echo ($edit_room['status'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                                <option value="Occupied" <?php echo ($edit_room['status'] == 'Occupied') ? 'selected' : ''; ?>>Occupied</option>
                                <option value="Maintenance" <?php echo ($edit_room['status'] == 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                            </select></div>
                            <div class="form-group"><label>Hostel</label><select name="Hostel_id" required>
                                <?php mysqli_data_seek($hostels, 0); while ($h = mysqli_fetch_assoc($hostels)): ?>
                                    <option value="<?php echo $h['Hostel_id']; ?>" <?php echo ($edit_room['Hostel_id'] == $h['Hostel_id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($h['Hostel_name']); ?></option>
                                <?php endwhile; ?>
                            </select></div>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Room</button>
                        <a href="manage_rooms.php" class="btn btn-secondary">Cancel</a>
                    </form>
                <?php else: ?>
                    <h2>Add New Room</h2>
                    <form method="POST" action="manage_rooms.php">
                        <input type="hidden" name="action" value="add">
                        <div class="form-row">
                            <div class="form-group"><label>Room Name</label><input type="text" name="Room_name" placeholder="Enter room name" required minlength="1" maxlength="50" pattern="[A-Za-z0-9 -]{1,50}"></div>
                            <div class="form-group"><label>Room Type</label><select name="Room_type" required>
                                <option value="Single">Single</option>
                                <option value="Double">Double</option>
                                <option value="Triple">Triple</option>
                            </select></div>
                            <div class="form-group"><label>Capacity</label><input type="number" name="capacity" placeholder="Enter capacity" min="1" required></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>Status</label><select name="status" required>
                                <option value="Available">Available</option>
                                <option value="Occupied">Occupied</option>
                                <option value="Maintenance">Maintenance</option>
                            </select></div>
                            <div class="form-group"><label>Hostel</label><select name="Hostel_id" required>
                                <?php mysqli_data_seek($hostels, 0); while ($h = mysqli_fetch_assoc($hostels)): ?>
                                    <option value="<?php echo $h['Hostel_id']; ?>"><?php echo htmlspecialchars($h['Hostel_name']); ?></option>
                                <?php endwhile; ?>
                            </select></div>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Room</button>
                    </form>
                <?php endif; ?>
            </div>

            <table>
                <thead><tr><th>Room ID</th><th>Room Name</th><th>Type</th><th>Capacity</th><th>Status</th><th>Hostel</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php if (mysqli_num_rows($rooms) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($rooms)): ?>
                            <tr>
                                <td><?php echo $row['Room_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['Room_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['Room_type']); ?></td>
                                <td><?php echo $row['capacity']; ?></td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    if ($row['status'] == 'Available') $status_class = 'status-available';
                                    elseif ($row['status'] == 'Occupied') $status_class = 'status-occupied';
                                    elseif ($row['status'] == 'Maintenance') $status_class = 'status-maintenance';
                                    ?>
                                    <span class="<?php echo $status_class; ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($row['Hostel_name'] ?? 'N/A'); ?></td>
                                <td class="actions">
                                    <a href="manage_rooms.php?edit=<?php echo $row['Room_id']; ?>" class="btn btn-warning">Edit</a>
                                    <a href="manage_rooms.php?delete=<?php echo $row['Room_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center;color:#999;">No rooms found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
