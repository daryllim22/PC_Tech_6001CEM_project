<?php
session_start();
include 'connection.php';

// Redirect if accessed directly without checkout
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['total'])) {
    header("Location: cart.php");
    exit;
}

// Collect posted data
$name = $_POST['name'];
$email = $_POST['email'];
$address = $_POST['address'];
$payment = $_POST['payment_method'];
$total = $_POST['total'];
$items = json_decode($_POST['items'], true);

// Convert items to text for order summary
$orderSummary = "";
foreach ($items as $item) {
    $orderSummary .= $item['name'] . " (RM " . number_format($item['price'], 2) . ")\n";
}

// Insert order into database
$stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_email, address, payment_method, order_items, total) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssd", $name, $email, $address, $payment, $orderSummary, $total);
$stmt->execute();

// Clear cart after successful order
unset($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order Confirmation - PC Tech</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body { background-color: #f8f9fa; text-align: center; padding-top: 50px; }
    .card { max-width: 600px; margin: auto; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 30px; }
    .order-details { text-align: left; margin-top: 20px; }
  </style>
</head>
<body>

  <div class="card">
    <h2 class="text-success mb-3">✅ Order Confirmed!</h2>
    <p class="text-muted">Thank you for shopping with <strong>PC Tech</strong>.</p>
    <p>Below are your order details:</p>

    <div class="order-details">
      <h5>🧍 Customer Information</h5>
      <p><strong>Name:</strong> <?= htmlspecialchars($name) ?><br>
         <strong>Email:</strong> <?= htmlspecialchars($email) ?><br>
         <strong>Address:</strong> <?= nl2br(htmlspecialchars($address)) ?></p>

      <h5>💳 Payment Method</h5>
      <p><?= htmlspecialchars($payment) ?></p>

      <h5>🛒 Order Summary</h5>
      <ul class="list-group mb-3">
        <?php foreach ($items as $item): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <?= htmlspecialchars($item['name']) ?>
            <span>RM <?= number_format($item['price'], 2) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>

      <h5 class="text-end fw-bold">Total: RM <?= number_format($total, 2) ?></h5>
      <p class="text-center text-success mt-3 fw-semibold">Your order has been recorded successfully!</p>
    </div>

    <a href="home.php" class="btn btn-primary mt-4">Return to Home</a>
  </div>

</body>
</html>
