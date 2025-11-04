<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Successful - PC Tech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    .success-container {
      margin-top: 150px;
      text-align: center;
    }
    .success-icon {
      font-size: 80px;
      color: #28a745;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

<!-- ✅ LOGO HEADER -->
<header class="header-logo text-center py-3 bg-white shadow-sm">
  <img src="images/logo.png" alt="PC Tech Logo" style="max-width:200px; height:auto;">
</header>

<!-- ✅ NAVBAR -->
<nav class="navbar d-flex justify-content-center gap-4 py-2 bg-light shadow-sm">
  <a href="home.php"><img src="images/home.png" alt="Home"></a>
  <a href="product.php"><img src="images/product.png" alt="Products"></a>
  <a href="cart.php"><img src="images/cart.png" alt="Cart"></a>
  <a href="order_history.php"><img src="images/history.png" alt="Orders"></a>
  <a href="profile.php"><img src="images/user_profile.png" alt="Profile"></a>
  <a href="logout.php"><img src="images/logout.png" alt="Logout"></a>
</nav>

<!-- ✅ SUCCESS CONTENT -->
<div class="container success-container py-5">
  <div class="card p-5 shadow-lg">
    <div class="success-icon">✅</div>
    <h2 class="text-success mb-3">Order Placed Successfully!</h2>
    <p class="fs-5 text-muted mb-4">
      Thank you for shopping with <strong>PC Tech</strong>!  
      Your order has been placed and is currently being processed.
    </p>
    <p class="text-muted">
      You can track your order status in the <strong>Order History</strong> section.
    </p>
    <div class="mt-4">
      <a href="order_history.php" class="btn btn-primary me-2">View Orders</a>
      <a href="home.php" class="btn btn-outline-secondary">Back to Home</a>
    </div>
  </div>
</div>

</body>
</html>
