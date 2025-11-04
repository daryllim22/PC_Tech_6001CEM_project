<?php
session_start();
include 'connection.php';
$id = $_GET['id'];
$order = $conn->query("SELECT * FROM orders WHERE id='$id'")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Invoice - PC Tech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<header class="header-logo text-center py-3 bg-white shadow-sm">
  <img src="images/logo.png" alt="PC Tech Logo" style="max-width:200px; height:auto;">
</header>

<nav class="navbar d-flex justify-content-center gap-4 py-2 bg-light shadow-sm">
  <a href="home.php"><img src="images/home.png" alt="Home"></a>
  <a href="product.php"><img src="images/product.png" alt="Products"></a>
  <a href="cart.php"><img src="images/cart.png" alt="Cart"></a>
  <a href="order_history.php"><img src="images/history.png" alt="Orders"></a>
  <a href="profile.php"><img src="images/user_profile.png" alt="Profile"></a>
  <a href="logout.php"><img src="images/logout.png" alt="Logout"></a>
</nav>

<div style="margin-top:160px;"></div>

<div class="container py-5">
  <h2 class="text-center mb-4">Invoice #<?= $id; ?></h2>
  <div class="card p-4 shadow-sm mx-auto" style="max-width:600px;">
    <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']); ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']); ?></p>
    <p><strong>Address:</strong> <?= htmlspecialchars($order['address']); ?></p>
    <p><strong>Total:</strong> RM <?= number_format($order['total'], 2); ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($order['delivery_status']); ?></p>
    <p><strong>Date:</strong> <?= htmlspecialchars($order['order_date']); ?></p>
  </div>
</div>
</body>
</html>
