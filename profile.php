<?php
session_start();
include 'connection.php';

// Redirect if not logged in
if (!isset($_SESSION['user_mail'])) {
    header("Location: login.php");
    exit;
}

$user_mail = $_SESSION['user_mail'];

// ✅ Fetch user data
$stmt = $conn->prepare("SELECT id, full_name, email, password FROM users WHERE email = ?");
$stmt->bind_param("s", $user_mail);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// ✅ Handle password update
if (isset($_POST['update_password'])) {
    $current = trim($_POST['current_password']);
    $password = trim($_POST['password']);
    $confirm = trim($_POST['confirm_password']);
    $strongPwRegex = '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/';

    if (!password_verify($current, $user['password'])) {
        echo "<script>alert('❌ Current password is incorrect.');</script>";
    } elseif ($password !== $confirm) {
        echo "<script>alert('❌ New passwords do not match!');</script>";
    } elseif (!preg_match($strongPwRegex, $password)) {
        echo "<script>alert('❌ New password must meet all requirements.');</script>";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->prepare("UPDATE users SET password=? WHERE email=?");
        $update->bind_param("ss", $hashed, $user_mail);

        if ($update->execute()) {
            echo "<script>alert('✅ Password updated successfully!'); window.location='profile.php';</script>";
            exit;
        } else {
            echo "<script>alert('❌ Failed to update password. Try again.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profile - PC Tech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    body { background-color: #f8f9fa; }
    .eye-btn {
      border: 1px solid #ced4da;
      border-left: 0;
    }
    .password-requirements {
      font-size: 0.9em;
      color: #000; /* neutral color */
    }
    .password-requirements li {
      list-style: none;
      margin-left: -20px;
      color: #000; /* start in black */
    }
    .valid { color: green !important; }
    .invalid { color: red !important; }
  </style>
</head>
<body>

<!-- ✅ LOGO AT TOP (same as home.php) -->
<header class="header-logo text-center py-3 bg-white shadow-sm">
  <img src="images/logo.png" alt="PC Tech Logo" style="max-width:200px; height:auto;">
</header>

<!-- ✅ NAVBAR BELOW LOGO (same as home.php) -->
<nav class="navbar d-flex justify-content-center gap-4 py-2 bg-light shadow-sm">
  <a href="home.php"><img src="images/home.png" alt="Home"></a>
  <a href="product.php"><img src="images/product.png" alt="Products"></a>
  <a href="cart.php"><img src="images/cart.png" alt="Cart"></a>
  <a href="order_history.php"><img src="images/history.png" alt="Orders"></a>
  <a href="profile.php"><img src="images/user_profile.png" alt="Profile"></a>
  <a href="logout.php"><img src="images/logout.png" alt="Logout"></a>
</nav>

<!-- ✅ MAIN CONTENT -->
<div class="container py-5">
  <h2 class="text-center mb-4">Your Profile</h2>

  <div class="card shadow-sm p-4 mx-auto" style="max-width:600px;">
    <!-- Read-only user info -->
    <div class="mb-3">
      <label class="form-label">Full Name</label>
      <input type="text" class="form-control" value="<?= htmlspecialchars($user['full_name']); ?>" readonly>
    </div>

    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" readonly>
    </div>

    <hr class="my-4">

    <!-- ✅ Change Password Section -->
    <form method="post" id="passwordForm">
      <h5 class="mb-3 text-primary text-center">Change Password</h5>

      <!-- Current Password -->
      <div class="mb-3">
        <label class="form-label">Current Password</label>
        <div class="input-group">
          <input type="password" name="current_password" id="current_password" class="form-control" required>
          <button type="button" class="btn btn-outline-secondary eye-btn" onclick="toggleVisibility('current_password', this)">👁️</button>
        </div>
      </div>

      <!-- New Password -->
      <div class="mb-3">
        <label class="form-label">New Password</label>
        <div class="input-group">
          <input type="password" name="password" id="password" class="form-control" required>
          <button type="button" class="btn btn-outline-secondary eye-btn" onclick="toggleVisibility('password', this)">👁️</button>
        </div>
        <div class="password-requirements mt-2">
          <strong>Password Requirements:</strong>
          <ul class="mb-0">
            <li id="length">• At least 8 characters long</li>
            <li id="letter">• At least one letter (A–Z or a–z)</li>
            <li id="number">• At least one number (0–9)</li>
            <li id="symbol">• At least one special symbol (!, @, #, $, %, ^, &)</li>
          </ul>
        </div>
      </div>

      <!-- Confirm Password -->
      <div class="mb-3">
        <label class="form-label">Confirm New Password</label>
        <div class="input-group">
          <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
          <button type="button" class="btn btn-outline-secondary eye-btn" onclick="toggleVisibility('confirm_password', this)">👁️</button>
        </div>
      </div>

      <!-- Submit -->
      <div class="text-center mt-4">
        <button type="submit" name="update_password" class="btn btn-primary w-100">Update Password</button>
      </div>
    </form>
  </div>
</div>

<!-- ✅ SCRIPTS -->
<script>
function toggleVisibility(id, btn) {
  const input = document.getElementById(id);
  input.type = input.type === 'password' ? 'text' : 'password';
  btn.textContent = input.type === 'password' ? '👁️' : '🙈';
}

const passwordInput = document.getElementById('password');
const lengthReq = document.getElementById('length');
const letterReq = document.getElementById('letter');
const numberReq = document.getElementById('number');
const symbolReq = document.getElementById('symbol');

passwordInput.addEventListener('input', () => {
  const val = passwordInput.value;
  updateRequirement(lengthReq, val.length >= 8, 'At least 8 characters long');
  updateRequirement(letterReq, /[A-Za-z]/.test(val), 'At least one letter (A–Z or a–z)');
  updateRequirement(numberReq, /\d/.test(val), 'At least one number (0–9)');
  updateRequirement(symbolReq, /[@$!%*#?&]/.test(val), 'At least one special symbol (!, @, #, $, %, ^, &)');
});

function updateRequirement(el, valid, text) {
  if (passwordInput.value.length === 0) {
    el.style.color = "#000"; // stays black when empty
    el.textContent = "• " + text;
  } else {
    el.style.color = valid ? "green" : "red";
    el.textContent = (valid ? "✅ " : "❌ ") + text;
  }
}

document.getElementById('passwordForm').addEventListener('submit', function(e) {
  const pw = passwordInput.value;
  const confirm = document.getElementById('confirm_password').value;
  const regex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/;

  if (!regex.test(pw)) {
    e.preventDefault();
    alert('❌ Password does not meet all requirements.');
  } else if (pw !== confirm) {
    e.preventDefault();
    alert('❌ New passwords do not match.');
  }
});
</script>

</body>
</html>
