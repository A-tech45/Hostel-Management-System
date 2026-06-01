<?php
include 'config.php';

if (empty($_SESSION['reset_student_id']) || empty($_SESSION['reset_verified']) || empty($_SESSION['reset_token_id'])) {
    header('Location: student_reset_password.php');
    exit;
}

$error = '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm'] ?? '');

    if ($password === '' || strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $student_id = (int) $_SESSION['reset_student_id'];
        $token_id = (int) $_SESSION['reset_token_id'];

        $stmt = $conn->prepare("UPDATE student SET password=? WHERE Student_id=?");
        $stmt->bind_param("si", $password, $student_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE student_password_reset SET used_at = NOW() WHERE Reset_id = ?");
        $stmt->bind_param("i", $token_id);
        $stmt->execute();
        $stmt->close();

        unset($_SESSION['reset_student_id'], $_SESSION['reset_email'], $_SESSION['reset_verified'], $_SESSION['reset_token_id']);

        $msg = 'Password updated successfully. You can log in now.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Set New Password - Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-shell">
        <div class="login-card login-card--center">
            <div class="login-header">
                <h2>Set New Password</h2>
                <p>Create a new password for your student account.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php elseif (!empty($msg)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
            <?php endif; ?>

            <?php if (empty($msg)): ?>
                <form method="POST" action="student_set_password.php">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" placeholder="New password" required minlength="6">

                    <label for="confirm">Confirm Password</label>
                    <input type="password" id="confirm" name="confirm" placeholder="Confirm password" required minlength="6">

                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            <?php else: ?>
                <div class="login-actions">
                    <a class="btn btn-primary" href="index.php?role=student">Back to login</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
