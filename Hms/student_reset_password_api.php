<?php
include 'config.php';

header('Content-Type: application/json');

$email = trim($_POST['email'] ?? '');
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'error' => 'Please enter a valid email address.']);
    exit;
}

$stmt = $conn->prepare("SELECT Student_id, name, email FROM student WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->num_rows === 1 ? $result->fetch_assoc() : null;
$stmt->close();

if (!$student) {
    echo json_encode(['ok' => false, 'error' => 'Email not found.']);
    exit;
}

$student_id = (int) $student['Student_id'];
$otp = (string) random_int(100000, 999999);
$otp_hash = password_hash($otp, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE student_password_reset SET used_at = NOW() WHERE Student_id = ? AND used_at IS NULL");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("INSERT INTO student_password_reset (Student_id, otp_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
$stmt->bind_param("is", $student_id, $otp_hash);
$stmt->execute();
$stmt->close();

$_SESSION['reset_student_id'] = $student_id;
$_SESSION['reset_email'] = $email;

echo json_encode([
    'ok' => true,
    'email' => $student['email'],
    'name' => $student['name'],
    'otp' => $otp
]);
