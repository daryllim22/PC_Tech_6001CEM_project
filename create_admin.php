<?php
// create_admin.php (run this once, then delete it)
include 'connection.php'; // provides $conn

$admin_email = 'admin@pcmail.com';
$admin_name  = 'Administrator';
$admin_pass_plain = 'Admin1!'; // change if you want another initial password
$admin_role = 'admin';

// check if exists
$stmt = $conn->prepare("SELECT id FROM users WHERE LOWER(email) = LOWER(?) LIMIT 1");
$stmt->bind_param("s", $admin_email);
$stmt->execute();
$res = $stmt->get_result();

if ($res && $res->num_rows > 0) {
    echo "Admin user already exists. No changes made.";
    exit;
}

// insert
$pass_hash = password_hash($admin_pass_plain, PASSWORD_DEFAULT);
$ins = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
$ins->bind_param("ssss", $admin_name, $admin_email, $pass_hash, $admin_role);
if ($ins->execute()) {
    echo "Admin account created: {$admin_email} (password: {$admin_pass_plain}). Please delete this file now.";
} else {
    echo "Insert failed: " . $ins->error;
}
$ins->close();
$stmt->close();
$conn->close();
