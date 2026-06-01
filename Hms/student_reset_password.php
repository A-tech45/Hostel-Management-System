<?php
include 'config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-shell">
        <div class="login-card login-card--center">
            <a class="login-back" href="index.php?role=student" aria-label="Back to login">&larr;</a>
            <div class="login-header">
                <h2>Reset Password</h2>
                <p>Enter your registered student email to receive an OTP.</p>
            </div>

            <div id="resetAlert"></div>

            <form id="resetForm" method="POST" action="student_reset_password_api.php">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" required>
                <button type="submit" id="sendOtpBtn" class="btn btn-primary">Send OTP</button>
            </form>

            <div class="login-actions">
                <a class="login-reset" href="index.php?role=student">Back to login</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
    <script>
        (function () {
            var form = document.getElementById('resetForm');
            var alertBox = document.getElementById('resetAlert');
            var submitBtn = document.getElementById('sendOtpBtn');

            emailjs.init(' '); // Here put ur emailjs public key

            function showAlert(type, message) {
                alertBox.innerHTML = '<div class="alert alert-' + type + '">' + message + '</div>';
            }

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                submitBtn.disabled = true;
                submitBtn.textContent = 'Sending...';

                var formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (!data.ok) {
                            showAlert('danger', data.error || 'Unable to send OTP.');
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Send OTP';
                            return;
                        }

                        var templateParams = {
                            to_email: data.email,
                            to_name: data.name,
                            passcode: data.otp
                        };

                        return emailjs.send('  ', ' ',)  // here service details and template 
                            .then(function () {
                                window.location.href = 'student_verify_otp.php';
                            }, function () {
                                showAlert('danger', 'Email delivery failed. Please try again.');
                                submitBtn.disabled = false;
                                submitBtn.textContent = 'Send OTP';
                            });
                    })
                    .catch(function () {
                        showAlert('danger', 'Something went wrong. Please try again.');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Send OTP';
                    });
            });
        })();
    </script>
</body>
</html>
