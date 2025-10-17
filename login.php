<?php
session_start();
include("connection.php");

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT full_name, email, role, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Store user session
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_mail'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            // ✅ Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: home.php');
            }
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - PC Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="login-container d-flex align-items-center justify-content-center vh-100">
    <form action="" method="POST" class="bg-light p-4 rounded shadow" style="min-width: 300px;">
        <h2 class="text-center mb-4">Login</h2>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>

        <div class="text-center mt-3">
            <a href="sign_up.php">Sign Up</a>
        </div>

        <?php if (!empty($error)): ?>
            <p class="text-danger text-center mt-3"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
    </form>
</div>
</body>
</html>
