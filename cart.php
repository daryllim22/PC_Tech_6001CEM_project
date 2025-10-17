<?php
session_start();

// Add item to cart
if (isset($_POST['add_to_cart'])) {
    $item = [
        'id' => $_POST['id'],
        'name' => $_POST['name'],
        'price' => $_POST['price'],
        'image' => $_POST['image']
    ];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Prevent duplicate items
    $ids = array_column($_SESSION['cart'], 'id');
    if (!in_array($item['id'], $ids)) {
        $_SESSION['cart'][] = $item;
    }
}

// Remove item from cart
if (isset($_POST['remove'])) {
    $remove_id = $_POST['remove_id'];
    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $remove_id) {
            unset($_SESSION['cart'][$key]);
        }
    }
    $_SESSION['cart'] = array_values($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Shopping Cart - PC Tech</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .cart-item img { height: 80px; border-radius: 10px; }
    .cart-summary { background: #fff; border-radius: 12px; box-shadow: 0 6px 16px rgba(0,0,0,0.1); padding: 20px; }
    th, td { vertical-align: middle; }
  </style>
</head>
<body>

  <!-- Navigation -->
  <nav class="navbar d-flex justify-content-center gap-4 py-2 bg-light">
    <a href="home.php"><img src="images/logo.jpg" alt="PC Tech Logo" style="height: 60px;"></a>
    <a href="home.php"><img src="images/home.png" alt="Home"></a>
    <a href="product.php"><img src="images/product.png" alt="Products"></a>
    <a href="cart.php"><img src="images/cart.png" alt="Cart"></a>
    <a href="profile.php"><img src="images/user_profile.png" alt="Profile"></a>
    <a href="logout.php"><img src="images/logout.png" alt="Logout"></a>
  </nav>

  <div class="container mt-5">
    <h2 class="text-center mb-4">🛒 Your Shopping Cart</h2>

    <?php if (!empty($_SESSION['cart'])): ?>
      <form method="POST" id="checkoutForm">
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="table-dark text-center">
              <tr>
                <th>Select</th>
                <th>Image</th>
                <th>Name</th>
                <th>Price (RM)</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                foreach ($_SESSION['cart'] as $item):
              ?>
                <tr class="cart-item text-center">
                  <td><input type="checkbox" class="form-check-input select-item" data-price="<?= $item['price'] ?>"></td>
                  <td><img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"></td>
                  <td><?= htmlspecialchars($item['name']) ?></td>
                  <td><?= number_format($item['price'], 2) ?></td>
                  <td>
                    <form method="POST">
                      <input type="hidden" name="remove_id" value="<?= $item['id'] ?>">
                      <button type="submit" name="remove" class="btn btn-danger btn-sm">Remove</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>

        <div class="cart-summary mt-4 text-center">
          <h4>Total Selected: RM <span id="selectedTotal">0.00</span></h4>
          <button type="button" id="checkoutBtn" class="btn btn-success mt-3" disabled>Proceed to Checkout</button>
        </div>
      </form>

    <?php else: ?>
      <p class="text-center text-muted fs-5 mt-5">Your cart is empty 😢</p>
      <div class="text-center">
        <a href="product.php" class="btn btn-primary mt-3">Browse Products</a>
      </div>
    <?php endif; ?>
  </div>

  <script>
    // Calculate total for selected items
    const checkboxes = document.querySelectorAll('.select-item');
    const totalDisplay = document.getElementById('selectedTotal');
    const checkoutBtn = document.getElementById('checkoutBtn');

    checkboxes.forEach(cb => {
      cb.addEventListener('change', () => {
        let total = 0;
        checkboxes.forEach(box => {
          if (box.checked) {
            total += parseFloat(box.getAttribute('data-price'));
          }
        });
        totalDisplay.textContent = total.toFixed(2);
        checkoutBtn.disabled = total === 0;
      });
    });

    // Simulated checkout action
    checkoutBtn.addEventListener('click', () => {
      alert("✅ Proceeding to checkout for selected items!");
    });
  </script>

</body>
</html>
