<?php
session_start();
include 'connection.php'; // make sure this file sets up $conn (mysqli)

// message to show login errors
$message = "";

// On POST attempt to login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // prepare and fetch user by email
    $stmt = $conn->prepare("SELECT id, full_name, email, password, role FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // verify password
        if (password_verify($password, $user['password'])) {
            // success: save session
            $_SESSION['user_mail'] = $user['email'];
            $_SESSION['role'] = $user['role'] ?? 'user';
            $_SESSION['full_name'] = $user['full_name'] ?? 'User';

            // redirect based on role stored in DB
            if (strtolower($_SESSION['role']) === 'admin') {
                header("Location: admin_dashboard.php");
                exit;
            } else {
                header("Location: home.php");
                exit;
            }
        } else {
            $message = "<div class='alert alert-danger text-center'>Invalid password.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger text-center'>Email not found.</div>";
    }

    $stmt->close();
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
    .eye-btn { border: 1px solid #ced4da; border-left: 0; }
  </style>
</head>
<body>
  <div class="login-container">
    <form method="POST" class="card bg-light" style="width: 350px;">
      <h3 class="text-center mb-4">PC Tech Login</h3>

      <?= $message; ?>

      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required autofocus>
      </div>

      <div class="mb-3">
        <label class="form-label">Password</label>
        <div class="input-group">
          <input type="password" name="password" id="password" class="form-control" required>
          <button type="button" class="btn btn-outline-secondary eye-btn" onclick="toggleVisibility('password', this)">👁️</button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100">Login</button>
      <div class="text-center mt-3">
        <a href="sign_up.php" class="text-decoration-none">Create an account</a>
      </div>
    </form>
  </div>

  <script>
    function toggleVisibility(id, btn) {
      const input = document.getElementById(id);
      input.type = input.type === 'password' ? 'text' : 'password';
      btn.textContent = input.type === 'password' ? '👁️' : '🙈';
    }
  </script>
</body>
</html>
