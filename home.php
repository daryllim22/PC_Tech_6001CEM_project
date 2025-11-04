<?php
session_start();
include 'connection.php';
if (!isset($_SESSION['user_mail'])) {
    header("Location: login.php");
    exit;
}
$full_name = $_SESSION['full_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Home - PC Tech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- ✅ LOGO AT TOP -->
<header class="header-logo text-center py-3 bg-white shadow-sm">
  <img src="images/logo.png" alt="PC Tech Logo" style="max-width:200px; height:auto;">
</header>

<!-- ✅ NAVBAR BELOW LOGO -->
<nav class="navbar d-flex justify-content-center gap-4 py-2 bg-light shadow-sm">
  <a href="home.php"><img src="images/home.png" alt="Home"></a>
  <a href="product.php"><img src="images/product.png" alt="Products"></a>
  <a href="cart.php"><img src="images/cart.png" alt="Cart"></a>
  <a href="order_history.php"><img src="images/history.png" alt="Orders"></a>
  <a href="profile.php"><img src="images/user_profile.png" alt="Profile"></a>
  <a href="logout.php"><img src="images/logout.png" alt="Logout"></a>
</nav>

<!-- ✅ MAIN CONTENT -->
<div class="text-center container py-5">
  <h1>Welcome, <strong><?= htmlspecialchars($full_name); ?></strong> 👋</h1>
  <p class="mt-3 fs-5">
    Welcome to PC Tech — your trusted destination for all things computing.  
    We provide a wide range of high-quality PCs, laptops, and accessories 
    designed to meet the needs of students, professionals, and everyday users.
  </p>
  <p class="fs-5">
    At PC Tech, we believe technology should enhance your lifestyle, not complicate it.  
    That’s why we offer products that combine performance, durability, and value.
  </p>

  <!-- ✅ CAROUSEL -->
  <div class="carousel-wrapper my-4">
    <div id="jsCarousel" class="text-center">
      <img id="carouselImage" src="" class="img-fluid" alt="Promotion">
      <div class="mt-3">
        <button onclick="prevImage()" class="btn btn-outline-primary me-2">⟨ Prev</button>
        <button onclick="nextImage()" class="btn btn-outline-primary">Next ⟩</button>
      </div>
    </div>
  </div>

  <!-- ✅ SHOP NOW BUTTON -->
  <div class="mt-4">
    <a href="product.php" class="shop-now-btn">🛒 Shop Now</a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const images = [
    'images/black-friday-sale.jpg',
    'images/cyber-monday-discount-sale.jpg',
    'images/new-arrivals.jpg'
  ];
  let currentIndex = 0;
  function showImage(i){ document.getElementById('carouselImage').src = images[i]; }
  function nextImage(){ currentIndex=(currentIndex+1)%images.length; showImage(currentIndex); }
  function prevImage(){ currentIndex=(currentIndex-1+images.length)%images.length; showImage(currentIndex); }
  window.onload = () => showImage(currentIndex);
</script>

</body>
</html>
