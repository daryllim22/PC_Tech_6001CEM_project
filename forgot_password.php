<?php
session_start();
include 'connection.php';
require 'vendor/autoload.php'; // load PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        $otp = rand(100000, 999999);
        $_SESSION['reset_email'] = $email;
        $_SESSION['otp'] = $otp;

        // ✅ Send OTP via email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'YOUR_EMAIL@gmail.com'; // your Gmail
            $mail->Password = 'YOUR_APP_PASSWORD';    // Google App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('YOUR_EMAIL@gmail.com', 'PC Tech');
            $mail->addAddress($email, $user['full_name']);

            $mail->isHTML(true);
            $mail->Subject = 'PC Tech Password Reset OTP';
            $mail->Body = "
              <h3>Your OTP Code</h3>
              <p>Hello <strong>{$user['full_name']}</strong>,</p>
              <p>Your OTP for password reset is: <b style='font-size:18px;'>$otp</b></p>
              <p>This code is valid for 10 minutes.</p>
              <br><em>PC Tech Support</em>
            ";

            $mail->send();
            header("Location: verify_otp.php");
            exit;
        } catch (Exception $e) {
            $message = "<div class='alert alert-danger text-center'>Error sending OTP. {$mail->ErrorInfo}</div>";
        }
    } else {
        $message = "<div class='alert alert-danger text-center'>Email not found.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forgot Password - PC Tech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="d-flex justify-content-center align-items-center" style="height:100vh;">
  <form method="POST" class="card p-4 shadow-sm" style="width:350px;">
    <h4 class="text-center mb-3">Forgot Password</h4>
    <?= $message; ?>
    <p class="text-muted small text-center">Enter your registered email to receive a 6-digit OTP.</p>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Send OTP</button>
    <div class="text-center mt-3">
      <a href="login.php" class="text-decoration-none">Back to Login</a>
    </div>
  </form>
</div>
</body>
</html>
