<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_mail']) || empty($_SESSION['cart'])) {
    header("Location: login.php");
    exit;
}

$email = $_SESSION['user_mail'];
$name = $_SESSION['full_name'] ?? 'Customer';

// ✅ Process order on form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = $_POST['address'] ?? 'N/A';
    $payment = $_POST['payment'] ?? 'Cash on Delivery';
    $cart = $_SESSION['cart'];

    // --- Calculate total safely
    $total = 0;
    foreach ($cart as $item) {
        $qty = isset($item['qty']) && is_numeric($item['qty']) ? (int)$item['qty'] : 1;
        $price = isset($item['price']) ? (float)$item['price'] : 0;
        $total += $price * $qty;
    }

    // --- Insert order into database
    $stmt = $conn->prepare("
        INSERT INTO orders (customer_name, customer_email, address, payment_method, total, delivery_status, order_date)
        VALUES (?, ?, ?, ?, ?, 'Pending', NOW())
    ");
    $stmt->bind_param("ssssd", $name, $email, $address, $payment, $total);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // --- Insert order items
    $itemStmt = $conn->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)
    ");
    foreach ($cart as $item) {
        $pid = $item['id'] ?? 0;
        $qty = isset($item['qty']) && $item['qty'] > 0 ? (int)$item['qty'] : 1;
        $price = isset($item['price']) ? (float)$item['price'] : 0;

        if ($pid <= 0 || $price <= 0) continue;

        $itemStmt->bind_param("iiid", $order_id, $pid, $qty, $price);
        $itemStmt->execute();
    }

    unset($_SESSION['cart']); // clear cart

    // ✅ Redirect to success page instead of invoice
    header("Location: order_success.php?id=" . $order_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Checkout - PC Tech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .checkout-card { max-width: 600px; margin: 50px auto; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    .hidden { display: none; }
  </style>
</head>
<body>

<?php include 'navbar.php'; ?>
<div style="margin-top:160px;"></div>

<div class="container">
  <div class="card p-4 checkout-card">
    <h3 class="text-center mb-4 text-primary">Checkout</h3>

    <form method="POST" id="checkoutForm" novalidate>
      <div class="mb-3">
        <label class="form-label">Customer Name</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($name); ?>" readonly>
      </div>

      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" value="<?= htmlspecialchars($email); ?>" readonly>
      </div>

      <div class="mb-3">
        <label class="form-label">Delivery Address</label>
        <textarea name="address" class="form-control" rows="3" required placeholder="Enter your full delivery address"></textarea>
      </div>

      <div class="mb-3">
        <label class="form-label">Payment Method</label>
        <select name="payment" id="payment" class="form-select" required>
          <option value="Cash on Delivery">Cash on Delivery</option>
          <option value="Credit/Debit Card">Credit/Debit Card</option>
          <option value="Online Banking">Online Banking</option>
        </select>
      </div>

      <!-- ✅ Credit/Debit Card Fields -->
      <div id="cardFields" class="hidden">
        <div class="mb-3">
          <label class="form-label">Card Number</label>
          <input type="text" id="cardNumber" name="card_number" class="form-control" 
                 placeholder="XXXX XXXX XXXX XXXX" maxlength="19" inputmode="numeric">
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Expiry Date (MM/YY)</label>
            <input type="text" id="expiryDate" name="expiry_date" class="form-control" 
                   maxlength="5" placeholder="MM/YY" inputmode="numeric">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">CVV</label>
            <input type="text" id="cvv" name="cvv" class="form-control" maxlength="4" placeholder="123" inputmode="numeric">
          </div>
        </div>
      </div>

      <!-- ✅ Online Banking Fields -->
      <div id="bankFields" class="hidden">
        <div class="mb-3">
          <label class="form-label">Select Your Bank</label>
          <select name="bank_name" id="bankName" class="form-select">
            <option value="">-- Select Bank --</option>
            <option value="Maybank">Maybank</option>
            <option value="CIMB">CIMB</option>
            <option value="RHB">RHB</option>
            <option value="Public Bank">Public Bank</option>
            <option value="Hong Leong Bank">Hong Leong Bank</option>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Account Number</label>
          <input type="text" id="accountNumber" name="account_number" class="form-control" placeholder="Enter your bank account number" inputmode="numeric">
        </div>
      </div>

      <div class="text-center mt-4">
        <button type="submit" class="btn btn-success w-100">Confirm Order</button>
      </div>
    </form>
  </div>
</div>

<script>
const paymentSelect = document.getElementById('payment');
const cardFields = document.getElementById('cardFields');
const bankFields = document.getElementById('bankFields');
const form = document.getElementById('checkoutForm');

// ✅ Toggle payment field visibility
paymentSelect.addEventListener('change', () => {
  const method = paymentSelect.value;
  cardFields.classList.add('hidden');
  bankFields.classList.add('hidden');

  if (method === 'Credit/Debit Card') {
    cardFields.classList.remove('hidden');
  } else if (method === 'Online Banking') {
    bankFields.classList.remove('hidden');
  }
});

// ✅ Auto-format card number
document.getElementById('cardNumber').addEventListener('input', e => {
  e.target.value = e.target.value
    .replace(/\D/g, '')
    .replace(/(.{4})/g, '$1 ')
    .trim();
});

// ✅ Format expiry as MM/YY
document.getElementById('expiryDate').addEventListener('input', e => {
  let v = e.target.value.replace(/\D/g, '');
  if (v.length >= 3) v = v.substring(0, 2) + '/' + v.substring(2, 4);
  e.target.value = v.substring(0, 5);
});

// ✅ Validation before submitting
form.addEventListener('submit', e => {
  const method = paymentSelect.value;

  if (method === 'Credit/Debit Card') {
    const cardNum = document.getElementById('cardNumber').value.replace(/\s/g, '');
    const expiry = document.getElementById('expiryDate').value;
    const cvv = document.getElementById('cvv').value;

    if (cardNum.length < 16 || isNaN(cardNum)) {
      alert('Please enter a valid 16-digit card number.');
      e.preventDefault(); return;
    }
    if (!/^(0[1-9]|1[0-2])\/\d{2}$/.test(expiry)) {
      alert('Please enter a valid expiry date (MM/YY).');
      e.preventDefault(); return;
    }
    if (cvv.length < 3 || isNaN(cvv)) {
      alert('Please enter a valid 3-digit CVV.');
      e.preventDefault(); return;
    }
  }

  if (method === 'Online Banking') {
    const bank = document.getElementById('bankName').value;
    const acc = document.getElementById('accountNumber').value;

    if (!bank) {
      alert('Please select your bank.');
      e.preventDefault(); return;
    }
    if (acc.length < 8 || isNaN(acc)) {
      alert('Please enter a valid account number.');
      e.preventDefault(); return;
    }
  }
});
</script>

</body>
</html>
