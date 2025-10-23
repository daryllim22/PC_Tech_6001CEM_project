<?php
session_start();
include 'connection.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Admin login check (hardcoded)
    if ($email === "admin@pcmail.com" && $password === "Admin1!") {
        $_SESSION['user_mail'] = $email;
        $_SESSION['role'] = 'admin';
        header("Location: admin_dashboard.php");
        exit;
    }

    // Normal user login
    $stmt = $conn->prepare("SELECT id, email, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_mail'] = $user['email'];
            $_SESSION['role'] = 'user';
            header("Location: home.php");
            exit;
        } else {
            $message = "<div class='alert alert-danger text-center'>Invalid password.</div>";
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
  <title>Login - PC Tech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; }
    .login-container {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .card {
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      padding: 30px;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <form method="POST" class="card bg-light" style="width: 350px;">
      <h3 class="text-center mb-4">PC Tech Login</h3>

      <?= $message; ?>

      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>

      <button type="submit" class="btn btn-primary w-100">Login</button>
      <div class="text-center mt-3">
        <a href="sign_up.php" class="text-decoration-none">Create an account</a>
      </div>
    </form>
  </div>
</body>
</html>
