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
    $stmt = mysqli_prepare($conn, "DELETE FROM hostel WHERE Hostel_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) { $success = "Hostel deleted successfully!"; }
    else { $error = "Error deleting hostel: " . mysqli_error($conn); }
    mysqli_stmt_close($stmt);
}

if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $hostel_name = trim($_POST['Hostel_name']);
    $location = trim($_POST['location']);
    $total_rooms = intval($_POST['Total_rooms']);

    if ($hostel_name === '' || !preg_match("/^[A-Za-z0-9 .,'-]{2,100}$/", $hostel_name)) {
        $error = "Hostel name must be 2-100 characters and valid text.";
    } elseif ($location === '' || strlen($location) < 2 || strlen($location) > 200) {
        $error = "Location must be 2-200 characters.";
    } elseif ($total_rooms <= 0) {
        $error = "Total Rooms must be greater than 0.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT Hostel_id FROM hostel WHERE LOWER(Hostel_name) = LOWER(?) LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $hostel_name);
        mysqli_stmt_execute($stmt);
        $dup = mysqli_stmt_get_result($stmt);
        $has_dup = $dup && mysqli_num_rows($dup) > 0;
        mysqli_stmt_close($stmt);
        if ($has_dup) {
            $error = "Hostel name already exists.";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO hostel (Hostel_name, location, Total_rooms) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssi", $hostel_name, $location, $total_rooms);
            if (mysqli_stmt_execute($stmt)) { $success = "Hostel added successfully!"; }
            else { $error = "Error adding hostel: " . mysqli_error($conn); }
            mysqli_stmt_close($stmt);
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] == 'edit') {
    $hostel_id = intval($_POST['Hostel_id']);
    $hostel_name = trim($_POST['Hostel_name']);
    $location = trim($_POST['location']);
    $total_rooms = intval($_POST['Total_rooms']);

    if ($hostel_id <= 0) {
        $error = "Invalid hostel selection.";
    } elseif ($hostel_name === '' || !preg_match("/^[A-Za-z0-9 .,'-]{2,100}$/", $hostel_name)) {
        $error = "Hostel name must be 2-100 characters and valid text.";
    } elseif ($location === '' || strlen($location) < 2 || strlen($location) > 200) {
        $error = "Location must be 2-200 characters.";
    } elseif ($total_rooms <= 0) {
        $error = "Total Rooms must be greater than 0.";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT Hostel_id FROM hostel WHERE LOWER(Hostel_name) = LOWER(?) AND Hostel_id <> ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "si", $hostel_name, $hostel_id);
        mysqli_stmt_execute($stmt);
        $dup = mysqli_stmt_get_result($stmt);
        $has_dup = $dup && mysqli_num_rows($dup) > 0;
        mysqli_stmt_close($stmt);
        if ($has_dup) {
            $error = "Hostel name already exists.";
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE hostel SET Hostel_name = ?, location = ?, Total_rooms = ? WHERE Hostel_id = ?");
            mysqli_stmt_bind_param($stmt, "ssii", $hostel_name, $location, $total_rooms, $hostel_id);
            if (mysqli_stmt_execute($stmt)) { $success = "Hostel updated successfully!"; }
            else { $error = "Error updating hostel: " . mysqli_error($conn); }
            mysqli_stmt_close($stmt);
        }
    }
}

$edit_hostel = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = mysqli_prepare($conn, "SELECT * FROM hostel WHERE Hostel_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $edit_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $edit_hostel = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}
$hostels = mysqli_query($conn, "SELECT * FROM hostel ORDER BY Hostel_id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Manage Hostels - HMS</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="admin-layout">
        <div class="sidebar">
            <h2 style="color:white;text-align:center;padding:10px;">HMS Admin</h2>
            <a href="dashboard.php"> Dashboard</a>
            <a href="manage_hostels.php" class="active"> Hostels</a>
            <a href="manage_rooms.php"> Rooms</a>
            <a href="manage_students.php"> Students</a>
            <a href="manage_wardens.php"> Wardens</a>
            <a href="manage_payments.php"> Payments</a>
            <a href="manage_attendance.php"> Attendance</a>
            <a href="manage_complaints.php"> Complaints</a>
            <a href="manage_leaves.php"> Leave Requests</a>
            <a href="../logout.php"> Logout</a>
        </div>
        <div class="main-content">
            <h1>Manage Hostels</h1>
            <?php if (!empty($success)): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
            <?php if (!empty($error)): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <div class="form-container">
                <?php if ($edit_hostel): ?>
                    <h2>Edit Hostel</h2>
                    <form method="POST" action="manage_hostels.php">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="Hostel_id" value="<?php echo $edit_hostel['Hostel_id']; ?>">
                        <div class="form-group"><label>Hostel Name</label><input type="text" name="Hostel_name" value="<?php echo htmlspecialchars($edit_hostel['Hostel_name']); ?>" required minlength="2" maxlength="100" pattern="[A-Za-z0-9 .,'-]{2,100}"></div>
                        <div class="form-group"><label>Location</label><input type="text" name="location" value="<?php echo htmlspecialchars($edit_hostel['location']); ?>" required minlength="2" maxlength="200"></div>
                        <div class="form-group"><label>Total Rooms</label><input type="number" name="Total_rooms" value="<?php echo $edit_hostel['Total_rooms']; ?>" min="1" required></div>
                        <button type="submit" class="btn btn-primary">Update Hostel</button>
                        <a href="manage_hostels.php" class="btn btn-secondary">Cancel</a>
                    </form>
                <?php else: ?>
                    <h2>Add New Hostel</h2>
                    <form method="POST" action="manage_hostels.php">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group"><label>Hostel Name</label><input type="text" name="Hostel_name" placeholder="Enter hostel name" required minlength="2" maxlength="100" pattern="[A-Za-z0-9 .,'-]{2,100}"></div>
                        <div class="form-group"><label>Location</label><input type="text" name="location" placeholder="Enter location" required minlength="2" maxlength="200"></div>
                        <div class="form-group"><label>Total Rooms</label><input type="number" name="Total_rooms" placeholder="Enter total rooms" min="1" required></div>
                        <button type="submit" class="btn btn-primary">Add Hostel</button>
                    </form>
                <?php endif; ?>
            </div>
            <table>
                <thead><tr><th>Hostel ID</th><th>Name</th><th>Location</th><th>Total Rooms</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php if (mysqli_num_rows($hostels) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($hostels)): ?>
                            <tr>
                                <td><?php echo $row['Hostel_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['Hostel_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td><?php echo $row['Total_rooms']; ?></td>
                                <td class="actions">
                                    <a href="manage_hostels.php?edit=<?php echo $row['Hostel_id']; ?>" class="btn btn-warning">Edit</a>
                                    <a href="manage_hostels.php?delete=<?php echo $row['Hostel_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center;color:#999;">No hostels found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
