<?php
session_start();
include 'connection.php';

// Initialize cart
if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

// --- Handle AJAX for removing item ---
if (isset($_POST['action']) && $_POST['action'] === 'remove') {
    $remove_id = $_POST['id'];
    $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], fn($item) => $item['id'] != $remove_id));
    echo json_encode(['status' => 'success']);
    exit;
}

// --- Handle AJAX for updating quantity ---
if (isset($_POST['action']) && $_POST['action'] === 'update_qty') {
    $id = $_POST['id'];
    $qty = max(1, (int)$_POST['qty']);
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] == $id) $item['qty'] = $qty;
    }
    echo json_encode(['status' => 'success']);
    exit;
}

// --- Calculate total ---
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $price = (float)$item['price'];
    $qty = isset($item['qty']) ? (int)$item['qty'] : 1;
    $total += $price * $qty;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cart - PC Tech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    .cart-img { width: 80px; height: auto; border-radius: 6px; }
  </style>
</head>
<body>

<?php include 'navbar.php'; ?>



<div style="margin-top:160px;"></div>

<!-- ✅ CART CONTENT -->
<div class="container py-5">
  <h2 class="text-center mb-4">Your Shopping Cart</h2>

  <?php if (!empty($_SESSION['cart'])): ?>
    <table class="table table-bordered text-center align-middle shadow-sm" id="cartTable">
      <thead class="table-dark">
        <tr>
          <th>Image</th>
          <th>Item</th>
          <th>Price (RM)</th>
          <th>Quantity</th>
          <th>Subtotal (RM)</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($_SESSION['cart'] as $item): 
          $qty = isset($item['qty']) ? (int)$item['qty'] : 1;
          $subtotal = $qty * $item['price'];
        ?>
        <tr data-id="<?= $item['id']; ?>">
          <td><img src="images/<?= htmlspecialchars($item['image']); ?>" class="cart-img"></td>
          <td><?= htmlspecialchars($item['name']); ?></td>
          <td><?= number_format($item['price'], 2); ?></td>
          <td>
            <input type="number" class="form-control text-center qty-input" value="<?= $qty; ?>" min="1" style="width:80px;">
          </td>
          <td class="subtotal"><?= number_format($subtotal, 2); ?></td>
          <td>
            <button class="btn btn-danger btn-sm remove-btn">Remove</button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="d-flex justify-content-between align-items-center mt-4">
      <h4>Total: <span class="text-primary" id="totalAmount">RM <?= number_format($total, 2); ?></span></h4>
      <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
    </div>
  <?php else: ?>
    <p class="text-center text-muted fs-5">Your cart is empty.</p>
  <?php endif; ?>
</div>

<!-- ✅ JS: Handle cart updates without refresh -->
<script>
document.querySelectorAll('.qty-input').forEach(input => {
  input.addEventListener('change', function() {
    const row = this.closest('tr');
    const id = row.dataset.id;
    const qty = this.value;

    fetch('cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ action: 'update_qty', id, qty })
    }).then(() => {
      const price = parseFloat(row.children[2].textContent);
      const subtotal = price * qty;
      row.querySelector('.subtotal').textContent = subtotal.toFixed(2);
      updateTotal();
    });
  });
});

document.querySelectorAll('.remove-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    const row = this.closest('tr');
    const id = row.dataset.id;

    fetch('cart.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ action: 'remove', id })
    }).then(() => {
      row.remove();
      updateTotal();
      if (document.querySelectorAll('#cartTable tbody tr').length === 0)
        location.reload(); // Show "empty" message
    });
  });
});

function updateTotal() {
  let total = 0;
  document.querySelectorAll('#cartTable tbody tr').forEach(row => {
    total += parseFloat(row.querySelector('.subtotal').textContent);
  });
  document.getElementById('totalAmount').textContent = "RM " + total.toFixed(2);
}
</script>

</body>
</html>
