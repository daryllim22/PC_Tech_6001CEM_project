<?php
session_start();
include 'connection.php';
if (!isset($_SESSION['reset_email'])) {
    header("Location: login.php");
    exit;
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];
    $regex = '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&]).{8,}$/';

    if ($password !== $confirm) {
        $message = "<div class='alert alert-danger text-center'>Passwords do not match!</div>";
    } elseif (!preg_match($regex, $password)) {
        $message = "<div class='alert alert-warning text-center'>Password must have at least 8 chars, 1 letter, 1 number & 1 symbol.</div>";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $email = $_SESSION['reset_email'];
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE email=?");
        $stmt->bind_param("ss", $hashed, $email);
        $stmt->execute();

        unset($_SESSION['reset_email'], $_SESSION['otp']);
        echo "<script>alert('✅ Password reset successful! Please login again.');window.location='login.php';</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password - PC Tech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="d-flex justify-content-center align-items-center" style="height:100vh;">
  <form method="POST" class="card p-4 shadow-sm" style="width:350px;">
    <h4 class="text-center mb-3">Reset Password</h4>
    <?= $message; ?>
    <div class="mb-3">
      <label class="form-label">New Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Confirm Password</label>
      <input type="password" name="confirm" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Update Password</button>
  </form>
</div>
</body>
</html>
