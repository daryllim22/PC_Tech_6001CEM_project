<?php
session_start();
$message = "";

if (!isset($_SESSION['reset_email'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered = trim($_POST['otp']);
    if ($entered == $_SESSION['otp']) {
        header("Location: reset_password.php");
        exit;
    } else {
        $message = "<div class='alert alert-danger text-center'>Invalid OTP. Try again.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Verify OTP - PC Tech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="d-flex justify-content-center align-items-center" style="height:100vh;">
  <form method="POST" class="card p-4 shadow-sm" style="width:350px;">
    <h4 class="text-center mb-3">Verify OTP</h4>
    <?= $message; ?>
    <div class="mb-3">
      <label class="form-label">Enter OTP</label>
      <input type="text" name="otp" class="form-control" maxlength="6" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Verify</button>
  </form>
</div>
</body>
</html>
