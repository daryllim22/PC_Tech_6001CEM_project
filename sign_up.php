<?php
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password_plain = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate password complexity (server-side)
    $pattern = '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/';
    if (!preg_match($pattern, $password_plain)) {
        $error = "Password must be at least 8 characters long and contain letters, numbers, and special symbols.";
    } elseif ($password_plain !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $password = password_hash($password_plain, PASSWORD_DEFAULT);

        // DB connection
        $conn = new mysqli("localhost", "root", "", "pc_shop");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if email already exists
        $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            $error = "Email already exists.";
        } else {
            // Insert user
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $full_name, $email, $password);

            if ($stmt->execute()) {
                $success = "Account created successfully!";
            } else {
                $error = "Error: " . $stmt->error;
            }

            $stmt->close();
        }

        $check->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
      .valid { color: green; }
      .invalid { color: red; }
      .neutral { color: black; } /* Default color before typing */
      .eye-btn { border: 1px solid #ced4da; border-left: 0; }
    </style>
</head>
<body>
<div class="create-account-container d-flex align-items-center justify-content-center w-100" style="min-height:100vh;">
    <form action="" method="POST" class="bg-light p-4 rounded shadow" style="min-width: 340px;">
        <h2 class="text-center mb-4">Create Account</h2>

        <div class="mb-3">
            <label for="full_name" class="form-label">Full Name</label>
            <input type="text" name="full_name" id="full_name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="input-group">
              <input type="password" name="password" id="password" class="form-control" required>
              <button type="button" class="btn btn-outline-secondary eye-btn" onclick="toggleVisibility('password', this)">👁️</button>
            </div>

            <!-- Password Requirements -->
            <small class="text-muted d-block mt-2">
              <strong>Password Requirements:</strong>
              <ul class="mt-1 mb-0" style="font-size: 0.9rem;">
                <li id="length" class="neutral">• At least 8 characters long</li>
                <li id="letter" class="neutral">• At least one letter (A–Z or a–z)</li>
                <li id="number" class="neutral">• At least one number (0–9)</li>
                <li id="symbol" class="neutral">• At least one special symbol (e.g. !, @, #, $, %, ^, &amp;)</li>
              </ul>
            </small>
        </div>

        <!-- Confirm Password -->
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <div class="input-group">
              <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
              <button type="button" class="btn btn-outline-secondary eye-btn" onclick="toggleVisibility('confirm_password', this)">👁️</button>
            </div>
            <small id="matchMessage" class="fw-semibold d-block mt-2"></small>
        </div>

        <button type="submit" class="btn btn-primary w-100">Create Account</button>

        <div class="text-center mt-3">
            <a href="login.php" class="text-decoration-none">Already have an account? Login</a>
        </div>

        <?php if ($success): ?>
            <p class="text-success text-center mt-3"><?= htmlspecialchars($success) ?></p>
        <?php elseif ($error): ?>
            <p class="text-danger text-center mt-3"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </form>
</div>

<script>
  // Show/Hide Password Function
  function toggleVisibility(id, btn) {
    const input = document.getElementById(id);
    input.type = input.type === 'password' ? 'text' : 'password';
    btn.textContent = input.type === 'password' ? '👁️' : '🙈';
  }

  // Password live validation
  const passwordInput = document.getElementById("password");
  const confirmInput = document.getElementById("confirm_password");
  const matchMsg = document.getElementById("matchMessage");

  const lengthReq = document.getElementById("length");
  const letterReq = document.getElementById("letter");
  const numberReq = document.getElementById("number");
  const symbolReq = document.getElementById("symbol");

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

  function updateReq(el, condition, text) {
    el.classList.remove("neutral");
    el.classList.toggle("valid", condition);
    el.classList.toggle("invalid", !condition);
    el.textContent = (condition ? "✅ " : "❌ ") + text;
  }

  function resetToNeutral() {
    [lengthReq, letterReq, numberReq, symbolReq].forEach(req => {
      req.className = "neutral";
      req.textContent = "• " + req.textContent.replace(/✅ |❌ /g, "");
    });
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
