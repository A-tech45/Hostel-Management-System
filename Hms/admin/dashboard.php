<?php
session_start();
include('../config.php');

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php");
    exit();
}

// Fetch counts
$students_count = 0;
$rooms_count = 0;
$hostels_count = 0;
$pending_complaints = 0;
$available_rooms = 0;
$occupied_rooms = 0;
$total_complaints = 0;

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM student");
if ($row = mysqli_fetch_assoc($result)) {
    $students_count = $row['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM room");
if ($row = mysqli_fetch_assoc($result)) {
    $rooms_count = $row['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM hostel");
if ($row = mysqli_fetch_assoc($result)) {
    $hostels_count = $row['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM complaint WHERE Status='Pending'");
if ($row = mysqli_fetch_assoc($result)) {
    $pending_complaints = $row['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM room WHERE status='Available'");
if ($row = mysqli_fetch_assoc($result)) {
    $available_rooms = $row['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM room WHERE status='Occupied'");
if ($row = mysqli_fetch_assoc($result)) {
    $occupied_rooms = $row['total'];
}

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM complaint");
if ($row = mysqli_fetch_assoc($result)) {
    $total_complaints = $row['total'];
}

$students_in_hostel = [];
$students_query = "SELECT s.Student_id, s.name, s.course, s.semester, r.Room_name, h.Hostel_name
                   FROM student s
                   LEFT JOIN room r ON s.Room_id = r.Room_id
                   LEFT JOIN hostel h ON r.Hostel_id = h.Hostel_id
                   WHERE s.Room_id IS NOT NULL
                   ORDER BY s.name";
$result = mysqli_query($conn, $students_query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $students_in_hostel[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HMS</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="admin-layout">
        <div class="sidebar">
            <h2 style="color:white;text-align:center;padding:10px;">HMS Admin</h2>
            <a href="dashboard.php" class="active"> Dashboard</a>
            <a href="manage_hostels.php"> Hostels</a>
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
            <h1>Admin Dashboard</h1>
            <div class="cards-container">
                <div class="card">
                    <h3>Total Students</h3>
                    <div class="count"><?php echo $students_count; ?></div>
                </div>
                <div class="card">
                    <h3>Total Rooms</h3>
                    <div class="count"><?php echo $rooms_count; ?></div>
                </div>
                <div class="card">
                    <h3>Total Hostels</h3>
                    <div class="count"><?php echo $hostels_count; ?></div>
                </div>
                <div class="card">
                    <h3>Pending Complaints</h3>
                    <div class="count"><?php echo $pending_complaints; ?></div>
                </div>
            </div>

            <h2>Students in Hostel</h2>
            <table class="display-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Semester</th>
                        <th>Hostel</th>
                        <th>Room</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($students_in_hostel) > 0): ?>
                        <?php foreach ($students_in_hostel as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['course']); ?></td>
                                <td><?php echo htmlspecialchars($student['semester']); ?></td>
                                <td><?php echo htmlspecialchars($student['Hostel_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['Room_name']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No students are currently allocated to rooms.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="cards-container" style="margin-top: 20px;">
                <div class="card">
                    <h3>Rooms Available</h3>
                    <div class="count"><?php echo $available_rooms; ?></div>
                </div>
                <div class="card">
                    <h3>Rooms Occupied</h3>
                    <div class="count"><?php echo $occupied_rooms; ?></div>
                </div>
                <div class="card">
                    <h3>Total Hostels</h3>
                    <div class="count"><?php echo $hostels_count; ?></div>
                </div>
                <div class="card">
                    <h3>Total Complaints</h3>
                    <div class="count"><?php echo $total_complaints; ?></div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
