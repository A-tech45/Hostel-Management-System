<?php
include 'config.php';

$allowed_roles = ['admin', 'warden', 'student'];
$role = $_POST['role'] ?? $_GET['role'] ?? 'admin';
if (!in_array($role, $allowed_roles, true)) {
    $role = 'admin';
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        if ($role === 'admin') {
            $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                if ($password === $admin['password']) {
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    header('Location: admin/dashboard.php');
                    exit;
                }
            }
            $error = 'Invalid username or password.';
            $stmt->close();
        } elseif ($role === 'warden') {
            $stmt = $conn->prepare("SELECT Warden_id, Name, username, password, Hostel_id FROM warden WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $warden = $result->fetch_assoc();
                if ($password === $warden['password']) {
                    $_SESSION['warden_id'] = $warden['Warden_id'];
                    $_SESSION['warden_name'] = $warden['Name'];
                    $_SESSION['warden_hostel_id'] = $warden['Hostel_id'];
                    header('Location: warden/dashboard.php');
                    exit;
                }
            }
            $error = 'Invalid username or password.';
            $stmt->close();
        } else {
            $stmt = $conn->prepare("SELECT Student_id, name, username, password, Room_id FROM student WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 1) {
                $student = $result->fetch_assoc();
                if ($password === $student['password']) {
                    $_SESSION['student_id'] = $student['Student_id'];
                    $_SESSION['student_name'] = $student['name'];
                    $_SESSION['student_room_id'] = $student['Room_id'];
                    header('Location: student/dashboard.php');
                    exit;
                }
            }
            $error = 'Invalid username or password.';
            $stmt->close();
        }
    }
}

$role_labels = [
    'admin' => 'Admin',
    'warden' => 'Warden',
    'student' => 'Student',
];
$active_label = $role_labels[$role] ?? 'Admin';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hostel Management System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-shell">
        <div class="login-card login-card--center">
            <div class="login-header">
                <h2>Welcome back!</h2>
                <p>Log in to your Hostel Management account</p>
            </div>

            <div class="role-switch">
                <button type="button" class="role-btn" data-role="admin" data-btn="btn-primary">Admin</button>
                <button type="button" class="role-btn" data-role="warden" data-btn="btn-warning">Warden</button>
                <button type="button" class="role-btn" data-role="student" data-btn="btn-student">Student</button>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="index.php">
                <input type="hidden" name="role" id="roleInput" value="<?php echo htmlspecialchars($role); ?>">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter username" required
                       value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter password" required>

                <div class="login-actions">
                    <a id="studentResetLink" class="login-reset" href="student_reset_password.php">Forgot your password? Reset</a>
                </div>

                <button type="submit" id="loginButton" class="btn btn-primary">Log in</button>
            </form>
        </div>
    </div>

    <script>
        (function () {
            var role = "<?php echo htmlspecialchars($role); ?>";
            var toggles = document.querySelectorAll('.role-btn');
            var roleInput = document.getElementById('roleInput');
            var loginButton = document.getElementById('loginButton');
            var resetLink = document.getElementById('studentResetLink');

            function setActiveRole(nextRole) {
                toggles.forEach(function (btn) {
                    var isActive = btn.getAttribute('data-role') === nextRole;
                    btn.classList.toggle('active', isActive);
                });

                roleInput.value = nextRole;

                var btnClass = 'btn-primary';
                toggles.forEach(function (btn) {
                    if (btn.getAttribute('data-role') === nextRole) {
                        btnClass = btn.getAttribute('data-btn');
                    }
                });

                loginButton.className = 'btn ' + btnClass;
                resetLink.style.display = nextRole === 'student' ? 'inline-flex' : 'none';
            }

            toggles.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    setActiveRole(btn.getAttribute('data-role'));
                });
            });

            setActiveRole(role);
        })();
    </script>
</body>
</html>
