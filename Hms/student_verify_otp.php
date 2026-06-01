<?php
include 'config.php';

if (empty($_SESSION['reset_student_id'])) {
    header('Location: student_reset_password.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');

    if (!preg_match('/^[0-9]{6}$/', $otp)) {
        $error = 'Please enter the 6-digit OTP.';
    } else {
        $student_id = (int) $_SESSION['reset_student_id'];
        $stmt = $conn->prepare("SELECT Reset_id, otp_hash, expires_at FROM student_password_reset WHERE Student_id = ? AND used_at IS NULL ORDER BY Reset_id DESC LIMIT 1");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $reset = $result->num_rows === 1 ? $result->fetch_assoc() : null;
        $stmt->close();

        if (!$reset) {
            $error = 'OTP not found. Please request a new one.';
        } elseif (strtotime($reset['expires_at']) < time()) {
            $error = 'OTP expired. Please request a new one.';
        } elseif (!password_verify($otp, $reset['otp_hash'])) {
            $error = 'Invalid OTP. Please try again.';
        } else {
            $_SESSION['reset_verified'] = true;
            $_SESSION['reset_token_id'] = (int) $reset['Reset_id'];
            header('Location: student_set_password.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify OTP - Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-shell">
        <div class="login-card login-card--center">
            <div class="login-header">
                <h2>Verify OTP</h2>
                <p>Enter the 6-digit OTP sent to your email.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="student_verify_otp.php">
                <label for="otp">OTP</label>
                <input type="text" id="otp" name="otp" placeholder="123456" required pattern="[0-9]{6}" maxlength="6">
                <button type="submit" class="btn btn-primary">Verify</button>
            </form>

            <div class="login-actions">
                <a class="login-reset" href="student_reset_password.php">Resend OTP</a>
            </div>
        </div>
    </div>
</body>
</html>
