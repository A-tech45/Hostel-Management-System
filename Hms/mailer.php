<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_student_reset_otp($to_email, $to_name, $otp)
{
    $autoload = __DIR__ . '/vendor/autoload.php';
    if (!file_exists($autoload)) {
        return [false, 'PHPMailer is not installed. Run composer install.'];
    }

    require_once $autoload;

    $smtp_host = 'smtp.example.com';
    $smtp_port = 587;
    $smtp_user = 'no-reply@example.com';
    $smtp_pass = 'change-me';
    $smtp_secure = 'tls';
    $from_email = 'no-reply@example.com';
    $from_name = 'Hostel Management System';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $smtp_host;
        $mail->SMTPAuth = true;
        $mail->Username = $smtp_user;
        $mail->Password = $smtp_pass;
        $mail->SMTPSecure = $smtp_secure;
        $mail->Port = $smtp_port;

        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($to_email, $to_name);

        $mail->isHTML(true);
        $mail->Subject = 'Your password reset OTP';
        $mail->Body = '<p>Your OTP is <strong>' . htmlspecialchars($otp) . '</strong>. It expires in 10 minutes.</p>';
        $mail->AltBody = 'Your OTP is ' . $otp . '. It expires in 10 minutes.';

        $mail->send();
        return [true, null];
    } catch (Exception $e) {
        return [false, $mail->ErrorInfo];
    }
}
