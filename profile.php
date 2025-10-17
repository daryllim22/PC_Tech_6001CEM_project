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
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch current user data
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $_SESSION['user_mail']);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        // Verify current password
        if (password_verify($current_password, $hashed_password)) {

            // Password validation regex (server-side)
            $pattern = '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/';

            if (!preg_match($pattern, $new_password)) {
                $message .= "<div class='alert alert-danger'>
                    Password must be at least 8 characters long and contain letters, numbers, and special symbols.
                </div>";
            } elseif ($new_password !== $confirm_password) {
                $message .= "<div class='alert alert-danger'>New passwords do not match.</div>";
            } else {
                $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $update_pass = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_pass->bind_param("si", $new_hashed, $user_id);
                $update_pass->execute();
                $message .= "<div class='alert alert-success'>Password updated successfully.</div>";
            }
        } else {
            $message .= "<div class='alert alert-danger'>Current password is incorrect.</div>";
        }
    } else {
        $message .= "<div class='alert alert-danger'>User not found.</div>";
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
  <link rel="stylesheet" href="style.css">
  <style>
    .valid { color: green; }
    .invalid { color: red; }
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
      <!-- Read-only email field -->
      <input type="email" class="form-control" id="email" name="email"
             value="<?= htmlspecialchars($_SESSION['user_mail']); ?>" readonly>
      <small class="text-muted">Your email cannot be changed.</small>
    </div>

    <hr>
    <h5 class="mb-3">Change Password</h5>

    <div class="mb-3">
      <label for="current_password" class="form-label">Current Password *</label>
      <input type="password" class="form-control" id="current_password" name="current_password" required>
    </div>

    <div class="mb-3">
      <label for="new_password" class="form-label">New Password *</label>
      <input type="password" class="form-control" id="new_password" name="new_password" required>

      <!-- Live Password Requirements -->
      <small class="text-muted d-block mt-2">
        <strong>Password Requirements:</strong>
        <ul class="text-muted mt-1 mb-0" style="font-size: 0.9rem;">
          <li id="length" class="invalid">❌ At least 8 characters long</li>
          <li id="letter" class="invalid">❌ At least one letter (A–Z or a–z)</li>
          <li id="number" class="invalid">❌ At least one number (0–9)</li>
          <li id="symbol" class="invalid">❌ At least one special symbol (e.g. !, @, #, $, %, ^, &amp;)</li>
        </ul>
      </small>
    </div>

    <div class="mb-3">
      <label for="confirm_password" class="form-label">Confirm New Password *</label>
      <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
    </div>

    <button type="submit" class="btn btn-primary w-100">Update Password</button>
  </form>
</div>

<script>
  const passwordInput = document.getElementById("new_password");
  const lengthReq = document.getElementById("length");
  const letterReq = document.getElementById("letter");
  const numberReq = document.getElementById("number");
  const symbolReq = document.getElementById("symbol");

  passwordInput.addEventListener("input", () => {
    const value = passwordInput.value;

    // Length
    if (value.length >= 8) {
      lengthReq.classList.add("valid");
      lengthReq.classList.remove("invalid");
      lengthReq.textContent = "✅ At least 8 characters long";
    } else {
      lengthReq.classList.add("invalid");
      lengthReq.classList.remove("valid");
      lengthReq.textContent = "❌ At least 8 characters long";
    }

    // Letter
    if (/[A-Za-z]/.test(value)) {
      letterReq.classList.add("valid");
      letterReq.classList.remove("invalid");
      letterReq.textContent = "✅ At least one letter (A–Z or a–z)";
    } else {
      letterReq.classList.add("invalid");
      letterReq.classList.remove("valid");
      letterReq.textContent = "❌ At least one letter (A–Z or a–z)";
    }

    // Number
    if (/\d/.test(value)) {
      numberReq.classList.add("valid");
      numberReq.classList.remove("invalid");
      numberReq.textContent = "✅ At least one number (0–9)";
    } else {
      numberReq.classList.add("invalid");
      numberReq.classList.remove("valid");
      numberReq.textContent = "❌ At least one number (0–9)";
    }

    // Symbol
    if (/[!@#$%^&*(),.?":{}|<>]/.test(value)) {
      symbolReq.classList.add("valid");
      symbolReq.classList.remove("invalid");
      symbolReq.textContent = "✅ At least one special symbol (e.g. !, @, #, $, %, ^, &)";
    } else {
      symbolReq.classList.add("invalid");
      symbolReq.classList.remove("valid");
      symbolReq.textContent = "❌ At least one special symbol (e.g. !, @, #, $, %, ^, &)";
    }
  });
</script>

</body>
</html>
