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
  <style>
    body {
      background-color: #f8f9fa;
      margin: 0;
      padding: 0;
    }

    /* ✅ Navbar styling with centered logo */
    .navbar-icons {
      background-color: #ffffff;
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 30px;
      padding: 10px 0;
      border-bottom: 2px solid #eaeaea;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .navbar-icons a img {
      width: 40px;
      height: 40px;
      transition: transform 0.2s;
    }
    .navbar-icons a:hover img {
      transform: scale(1.1);
    }
    .navbar-logo {
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .navbar-logo img {
      width: 130px;
      height: auto;
      margin: 0 15px;
    }

    /* ✅ Main content */
    .container h1 {
      font-weight: 600;
      color: #333;
    }
    .shop-now-btn {
      display: inline-block;
      padding: 12px 25px;
      background-color: #007bff;
      color: white;
      border-radius: 6px;
      text-decoration: none;
      font-size: 18px;
      font-weight: 500;
      transition: background-color 0.3s;
    }
    .shop-now-btn:hover {
      background-color: #0056b3;
    }
  </style>
</head>
<body>

<!-- ✅ NAVBAR (with centered logo) -->
<nav class="navbar-icons">
  <a href="home.php"><img src="images/home.png" alt="Home"></a>
  <a href="product.php"><img src="images/product.png" alt="Products"></a>
  <a href="cart.php"><img src="images/cart.png" alt="Cart"></a>

  <!-- ✅ Centered Logo -->
  <div class="navbar-logo">
    <a href="home.php"><img src="images/logo.png" alt="PC Tech Logo"></a>
  </div>

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

<?php include 'footer.php'; ?>


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
