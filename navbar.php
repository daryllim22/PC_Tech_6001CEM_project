<!-- ✅ Universal Navbar for PC Tech -->
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

<style>
  /* ✅ Identical navbar styling */
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
</style>
