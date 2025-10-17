<?php
$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password_plain = $_POST['password'];

    // Validate password complexity (server-side)
    $pattern = '/^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/';
    if (!preg_match($pattern, $password_plain)) {
        $error = "Password must be at least 8 characters long and contain letters, numbers, and special symbols.";
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
    <link rel="stylesheet" href="style.css">
    <style>
      .valid { color: green; }
      .invalid { color: red; }
    </style>
</head>
<body>
<div class="create-account-container d-flex align-items-center justify-content-center w-100">
    <form action="" method="POST" class="bg-light p-4 rounded shadow" style="min-width: 320px;">
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
            <input type="password" name="password" id="password" class="form-control" required>

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
  const passwordInput = document.getElementById("password");
  const lengthReq = document.getElementById("length");
  const letterReq = document.getElementById("letter");
  const numberReq = document.getElementById("number");
  const symbolReq = document.getElementById("symbol");

  passwordInput.addEventListener("input", () => {
    const value = passwordInput.value;

    // Check length
    if (value.length >= 8) {
      lengthReq.classList.add("valid");
      lengthReq.classList.remove("invalid");
      lengthReq.textContent = "✅ At least 8 characters long";
    } else {
      lengthReq.classList.add("invalid");
      lengthReq.classList.remove("valid");
      lengthReq.textContent = "❌ At least 8 characters long";
    }

    // Check letters
    if (/[A-Za-z]/.test(value)) {
      letterReq.classList.add("valid");
      letterReq.classList.remove("invalid");
      letterReq.textContent = "✅ At least one letter (A–Z or a–z)";
    } else {
      letterReq.classList.add("invalid");
      letterReq.classList.remove("valid");
      letterReq.textContent = "❌ At least one letter (A–Z or a–z)";
    }

    // Check numbers
    if (/\d/.test(value)) {
      numberReq.classList.add("valid");
      numberReq.classList.remove("invalid");
      numberReq.textContent = "✅ At least one number (0–9)";
    } else {
      numberReq.classList.add("invalid");
      numberReq.classList.remove("valid");
      numberReq.textContent = "❌ At least one number (0–9)";
    }

    // Check special symbols
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
