<?php
session_start();
include 'connection.php';

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// ✅ Calculate total
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $qty = $item['qty'] ?? 1;
    $total += $item['price'] * $qty;
}

// ✅ Handle form submission
if (isset($_POST['place_order'])) {
    $customer_name = trim($_POST['full_name']);
    $customer_email = $_SESSION['user_mail'] ?? 'guest@example.com';
    $address = trim($_POST['address']);
    $payment = $_POST['payment_method'];
    $order_date = date('Y-m-d H:i:s');
    $order_items = json_encode($_SESSION['cart'], JSON_UNESCAPED_UNICODE);

    $stmt = $conn->prepare("INSERT INTO orders 
        (customer_name, customer_email, address, payment_method, order_items, total, order_date, delivery_status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("ssssdds", 
        $customer_name, 
        $customer_email, 
        $address, 
        $payment, 
        $order_items, 
        $total, 
        $order_date
    );

    if ($stmt->execute()) {
        unset($_SESSION['cart']);
        header("Location: order_success.php");
        exit;
    } else {
        echo "<script>alert('❌ Order failed to save. Please try again.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Checkout - PC Tech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    .payment-details { display: none; }
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


<div style="margin-top:160px;"></div>

<div class="container py-5">
  <h2 class="text-center mb-4">Checkout</h2>

  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow-sm p-4">
        <form method="post" id="checkoutForm" onsubmit="return validatePayment();">
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="3" required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Phone Number</label>
            <input type="text" name="phone" class="form-control" pattern="[0-9]{8,}" title="Enter at least 8 digits" required>
          </div>

          <!-- ✅ Payment Method -->
          <div class="mb-3">
            <label class="form-label">Payment Method</label>
            <select name="payment_method" id="payment_method" class="form-select" required>
              <option value="">Select Payment Method</option>
              <option value="Credit Card">Credit Card</option>
              <option value="Online Banking">Online Banking</option>
              <option value="Cash on Delivery">Cash on Delivery</option>
            </select>
          </div>

          <!-- ✅ Credit Card Details -->
          <div id="creditCardDetails" class="payment-details">
            <div class="mb-3">
              <label class="form-label">Card Number</label>
              <input type="text" name="card_number" id="card_number" class="form-control" 
                     placeholder="16-digit Card Number" pattern="[0-9]{16}" 
                     title="Card number must be exactly 16 digits">
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Expiry Date (MM/YY)</label>
                <input type="text" name="expiry" id="expiry" class="form-control" 
                       placeholder="MM/YY" maxlength="5" 
                       pattern="^(0[1-9]|1[0-2])\/\d{2}$" 
                       title="Enter expiry date in MM/YY format (e.g. 09/29)">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">CVV</label>
                <input type="text" name="cvv" id="cvv" class="form-control" 
                       placeholder="123" pattern="[0-9]{3}" 
                       title="CVV must be exactly 3 digits">
              </div>
            </div>
          </div>

          <!-- ✅ Online Banking Details -->
          <div id="onlineBankDetails" class="payment-details">
            <div class="mb-3">
              <label class="form-label">Select Bank</label>
              <select name="bank" id="bank" class="form-select">
                <option value="">Choose Bank</option>
                <option value="Maybank">Maybank</option>
                <option value="CIMB">CIMB</option>
                <option value="RHB">RHB</option>
                <option value="Public Bank">Public Bank</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Account Number</label>
              <input type="text" name="bank_acc" id="bank_acc" class="form-control" pattern="[0-9]{8,}" 
                     title="Enter a valid bank account number (min 8 digits)">
            </div>
          </div>

          <!-- ✅ COD Note -->
          <div id="codNote" class="payment-details">
            <p class="text-muted mt-3">💵 Please prepare the exact cash amount upon delivery.</p>
          </div>

          <h5 class="text-end text-primary mb-3">
            Total: RM <?= number_format($total, 2); ?>
          </h5>

          <div class="text-center">
            <button type="submit" name="place_order" class="btn btn-success px-5">Place Order</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ✅ SCRIPT: Show/hide + Validate -->
<script>
document.getElementById('payment_method').addEventListener('change', function() {
  document.querySelectorAll('.payment-details').forEach(el => el.style.display = 'none');
  if (this.value === 'Credit Card') document.getElementById('creditCardDetails').style.display = 'block';
  if (this.value === 'Online Banking') document.getElementById('onlineBankDetails').style.display = 'block';
  if (this.value === 'Cash on Delivery') document.getElementById('codNote').style.display = 'block';
});

function validatePayment() {
  const method = document.getElementById('payment_method').value;

  if (method === 'Credit Card') {
    const card = document.getElementById('card_number').value.trim();
    const cvv = document.getElementById('cvv').value.trim();
    const expiry = document.getElementById('expiry').value.trim();

    if (!/^\d{16}$/.test(card)) {
      alert("Card number must be exactly 16 digits.");
      return false;
    }

    if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiry)) {
      alert("Expiry date must be in MM/YY format (e.g. 09/29).");
      return false;
    }

    const [mm, yy] = expiry.split('/');
    const expDate = new Date(`20${yy}`, mm);
    const now = new Date();
    if (expDate <= now) {
      alert("Expiry date must be in the future.");
      return false;
    }

    if (!/^\d{3}$/.test(cvv)) {
      alert("CVV must be exactly 3 digits.");
      return false;
    }
  }

  if (method === 'Online Banking') {
    const bank = document.getElementById('bank').value;
    const acc = document.getElementById('bank_acc').value.trim();

    if (!bank) {
      alert("Please select your bank.");
      return false;
    }
    if (!/^\d{8,}$/.test(acc)) {
      alert("Bank account number must have at least 8 digits.");
      return false;
    }
  }

  return true;
}
</script>

</body>
</html>
