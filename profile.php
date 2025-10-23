<?php
session_start();
include 'connection.php';

// Redirect if not logged in
if (!isset($_SESSION['user_mail'])) {
    header("Location: login.php");
    exit;
}

$message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $_SESSION['user_mail']);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        if (password_verify($current_password, $hashed_password)) {
            $pattern = '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/';

            if (!preg_match($pattern, $new_password)) {
                $message .= "<div class='alert alert-danger fade-alert'>
                    Password must be at least 8 characters long and contain letters, numbers, and special symbols.
                </div>";
            } elseif ($new_password !== $confirm_password) {
                $message .= "<div class='alert alert-danger fade-alert'>New passwords do not match.</div>";
            } else {
                $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $update_pass = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_pass->bind_param("si", $new_hashed, $user_id);
                $update_pass->execute();
                $message .= "<div class='alert alert-success fade-alert'>Password updated successfully.</div>";
            }
        } else {
            $message .= "<div class='alert alert-danger fade-alert'>Current password is incorrect.</div>";
        }
    } else {
        $message .= "<div class='alert alert-danger fade-alert'>User not found.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profile - PC Tech</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .valid { color: green; }
    .invalid { color: red; }
    .neutral { color: black; }
    .eye-btn { border: 1px solid #ced4da; border-left: 0; }
    .fade-alert {
      transition: opacity 0.5s ease-in-out;
      opacity: 1;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar d-flex justify-content-center gap-4 py-2 bg-light">
  <a href="home.php"><img src="images/logo.jpg" alt="PC Tech Logo" style="height: 60px; margin-bottom: 10px;"></a>
  <a href="home.php"><img src="images/home.png" alt="Home"></a>
  <a href="product.php"><img src="images/product.png" alt="Products"></a>
  <a href="profile.php"><img src="images/user_profile.png" alt="Profile"></a>
  <a href="logout.php"><img src="images/logout.png" alt="Logout"></a>
</nav>

<div class="container mt-5" style="max-width: 600px;">
  <h2 class="mb-4 text-center">Update Profile</h2>

  <?= $message; ?>

  <form method="POST" action="">
    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" class="form-control" id="email" name="email"
             value="<?= htmlspecialchars($_SESSION['user_mail']); ?>" readonly>
      <small class="text-muted">Your email cannot be changed.</small>
    </div>

    <hr>
    <h5 class="mb-3">Change Password</h5>

    <!-- Current Password -->
    <div class="mb-3">
      <label for="current_password" class="form-label">Current Password *</label>
      <div class="input-group">
        <input type="password" class="form-control" id="current_password" name="current_password" required>
        <button type="button" class="btn btn-outline-secondary eye-btn" onclick="toggleVisibility('current_password', this)">👁️</button>
      </div>
    </div>

    <!-- New Password -->
    <div class="mb-3">
      <label for="new_password" class="form-label">New Password *</label>
      <div class="input-group">
        <input type="password" class="form-control" id="new_password" name="new_password" required>
        <button type="button" class="btn btn-outline-secondary eye-btn" onclick="toggleVisibility('new_password', this)">👁️</button>
      </div>
      <small class="text-muted d-block mt-2">
        <strong>Password Requirements:</strong>
        <ul class="text-muted mt-1 mb-0" style="font-size: 0.9rem;">
          <li id="length" class="neutral">At least 8 characters long</li>
          <li id="letter" class="neutral">At least one letter (A–Z or a–z)</li>
          <li id="number" class="neutral">At least one number (0–9)</li>
          <li id="symbol" class="neutral">At least one special symbol (e.g. !, @, #, $, %, ^, &amp;)</li>
        </ul>
      </small>
    </div>

    <!-- Confirm Password -->
    <div class="mb-3">
      <label for="confirm_password" class="form-label">Confirm New Password *</label>
      <div class="input-group">
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        <button type="button" class="btn btn-outline-secondary eye-btn" onclick="toggleVisibility('confirm_password', this)">👁️</button>
      </div>
      <small id="matchMessage" class="d-block mt-2 fw-semibold"></small>
    </div>

    <button type="submit" class="btn btn-primary w-100">Update Password</button>
  </form>
</div>

<script>
  // Auto-fade alerts after 3 seconds
  setTimeout(() => {
    document.querySelectorAll('.fade-alert').forEach(alert => {
      alert.style.opacity = '0';
      setTimeout(() => alert.remove(), 500);
    });
  }, 3000);

  // Toggle password visibility
  function toggleVisibility(id, btn) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
    btn.textContent = input.type === 'password' ? '👁️' : '🙈';
  }

  // Password validation logic
  const passwordInput = document.getElementById("new_password");
  const confirmInput = document.getElementById("confirm_password");
  const lengthReq = document.getElementById("length");
  const letterReq = document.getElementById("letter");
  const numberReq = document.getElementById("number");
  const symbolReq = document.getElementById("symbol");
  const matchMsg = document.getElementById("matchMessage");

  passwordInput.addEventListener("input", validatePassword);
  confirmInput.addEventListener("input", checkMatch);

  function validatePassword() {
    const value = passwordInput.value;

    if (value === "") {
      resetToNeutral();
      matchMsg.textContent = "";
      return;
    }

    updateReq(lengthReq, value.length >= 8, "At least 8 characters long");
    updateReq(letterReq, /[A-Za-z]/.test(value), "At least one letter (A–Z or a–z)");
    updateReq(numberReq, /\d/.test(value), "At least one number (0–9)");
    updateReq(symbolReq, /[!@#$%^&*(),.?\":{}|<>]/.test(value), "At least one special symbol");
    checkMatch();
  }

  function resetToNeutral() {
    [lengthReq, letterReq, numberReq, symbolReq].forEach(el => {
      el.className = "neutral";
      el.textContent = el.textContent.replace(/^✅ |^❌ /, "");
    });
  }

  function updateReq(el, condition, text) {
    el.classList.remove("neutral");
    el.classList.toggle("valid", condition);
    el.classList.toggle("invalid", !condition);
    el.textContent = (condition ? "✅ " : "❌ ") + text;
  }

  function checkMatch() {
    if (!confirmInput.value) {
      matchMsg.textContent = "";
      return;
    }
    if (confirmInput.value === passwordInput.value) {
      matchMsg.textContent = "✅ Passwords match";
      matchMsg.style.color = "green";
    } else {
      matchMsg.textContent = "❌ Passwords do not match";
      matchMsg.style.color = "red";
    }
  }
</script>
</body>
</html>
