<?php
session_start();

// Redirect if no checkout data
if (!isset($_POST['selected_items']) || empty($_POST['selected_items'])) {
    header("Location: cart.php");
    exit;
}

$selectedItems = json_decode($_POST['selected_items'], true);

// Calculate total from session cart
$total = 0;
$cartItems = [];
foreach ($_SESSION['cart'] as $item) {
    if (in_array($item['name'], $selectedItems)) {
        $cartItems[] = $item;
        $total += $item['price'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Checkout - PC Tech</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body { background-color: #f8f9fa; }
    .checkout-container { max-width: 750px; margin: 50px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 6px 16px rgba(0,0,0,0.1); }
    .item-img { height: 70px; border-radius: 10px; }
    .payment-section { display: none; margin-top: 15px; }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav class="navbar d-flex justify-content-center gap-4 py-2 bg-light">
    <a href="home.php"><img src="images/logo.jpg" alt="PC Tech Logo" style="height: 60px;"></a>
    <a href="home.php"><img src="images/home.png" alt="Home"></a>
    <a href="product.php"><img src="images/product.png" alt="Products"></a>
    <a href="cart.php"><img src="images/cart.png" alt="Cart"></a>
    <a href="profile.php"><img src="images/user_profile.png" alt="Profile"></a>
    <a href="logout.php"><img src="images/logout.png" alt="Logout"></a>
  </nav>

  <div class="checkout-container">
    <h2 class="text-center mb-4">Checkout Summary</h2>

    <table class="table table-bordered text-center align-middle">
      <thead class="table-dark">
        <tr>
          <th>Image</th>
          <th>Item</th>
          <th>Price (RM)</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($cartItems as $item): ?>
        <tr>
          <td><img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="item-img"></td>
          <td><?= htmlspecialchars($item['name']) ?></td>
          <td><?= number_format($item['price'], 2) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="text-end fs-5 fw-bold mt-3">Total: RM <?= number_format($total, 2) ?></div>
    <hr>

    <h5 class="mt-4 mb-3">Customer Information</h5>
    <form method="POST" action="checkout_success.php">
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" placeholder="Enter your name" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Shipping Address</label>
        <textarea name="address" class="form-control" rows="3" placeholder="Enter your full address" required></textarea>
      </div>

      <h5 class="mt-4">Payment Method</h5>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="payment_method" id="creditCardOption" value="Credit Card" required>
        <label class="form-check-label" for="creditCardOption">Credit Card</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="payment_method" id="onlineBankingOption" value="Online Banking" required>
        <label class="form-check-label" for="onlineBankingOption">Online Banking</label>
      </div>
      <div class="form-check">
        <input class="form-check-input" type="radio" name="payment_method" id="codOption" value="Cash on Delivery" required>
        <label class="form-check-label" for="codOption">Cash on Delivery</label>
      </div>

      <!-- Credit Card Section -->
      <div id="creditCardSection" class="payment-section">
        <div class="mb-3 mt-3">
          <label class="form-label">Card Number</label>
          <input type="text" class="form-control" name="card_number" placeholder="xxxx-xxxx-xxxx-xxxx" maxlength="19">
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Expiry Date</label>
            <input type="text" class="form-control" name="expiry_date" placeholder="MM/YY" maxlength="5">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">CVV</label>
            <input type="text" class="form-control" name="cvv" placeholder="123" maxlength="3">
          </div>
        </div>
      </div>

      <!-- Online Banking Section -->
      <div id="onlineBankingSection" class="payment-section">
        <div class="mb-3 mt-3">
          <label class="form-label">Select Bank</label>
          <select name="bank" class="form-select">
            <option value="">-- Select Your Bank --</option>
            <option value="Maybank">Maybank</option>
            <option value="CIMB">CIMB Bank</option>
            <option value="Public Bank">Public Bank</option>
            <option value="RHB">RHB Bank</option>
            <option value="Hong Leong">Hong Leong Bank</option>
          </select>
        </div>
      </div>

      <!-- Cash on Delivery Section -->
      <div id="codSection" class="payment-section">
        <p class="mt-3 text-muted">💵 Please prepare exact cash upon delivery. No advance payment required.</p>
      </div>

      <input type="hidden" name="total" value="<?= $total ?>">
      <input type="hidden" name="items" value='<?= json_encode($cartItems) ?>'>

      <button type="submit" class="btn btn-success w-100 mt-4">Confirm Purchase</button>
    </form>
  </div>

  <script>
    // Toggle payment fields
    const credit = document.getElementById("creditCardOption");
    const bank = document.getElementById("onlineBankingOption");
    const cod = document.getElementById("codOption");

    const creditSection = document.getElementById("creditCardSection");
    const bankSection = document.getElementById("onlineBankingSection");
    const codSection = document.getElementById("codSection");

    const sections = [creditSection, bankSection, codSection];

    function hideAll() {
      sections.forEach(sec => sec.style.display = "none");
    }

    document.querySelectorAll("input[name='payment_method']").forEach(option => {
      option.addEventListener("change", () => {
        hideAll();
        if (credit.checked) creditSection.style.display = "block";
        else if (bank.checked) bankSection.style.display = "block";
        else if (cod.checked) codSection.style.display = "block";
      });
    });
  </script>

</body>
</html>
