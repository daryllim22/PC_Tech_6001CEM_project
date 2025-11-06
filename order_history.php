<?php
session_start();
include 'connection.php';

$user_email = $_SESSION['user_mail'] ?? 'guest@example.com';

// Fetch orders for the logged-in user
$stmt = $conn->prepare("SELECT * FROM orders WHERE customer_email = ? ORDER BY order_date DESC");
$stmt->bind_param("s", $user_email);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order History - PC Tech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    .order-card { 
      border-left: 5px solid #0d6efd; 
      transition: transform 0.2s ease; 
      position: relative;
    }
    .order-card:hover { transform: scale(1.01); }
    .order-items-box { 
      background-color: #f8f9fa; 
      border-radius: 8px; 
      padding: 15px; 
    }
    .invoice-btn {
      position: absolute;
      bottom: 15px;
      right: 20px;
    }
  </style>
</head>
<body>

<?php include 'navbar.php'; ?>



<div style="margin-top:160px;"></div>

<div class="container py-5">
  <h2 class="text-center mb-4">Your Order History</h2>

  <?php if ($result->num_rows > 0): ?>
    <?php while($order = $result->fetch_assoc()): ?>
      <div class="card shadow-sm p-4 mb-4 order-card">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-1 text-primary">Order #<?= $order['id']; ?></h5>
            <p class="text-muted mb-0">Placed on <?= date("d M Y, h:i A", strtotime($order['order_date'])); ?></p>
          </div>
          <span class="badge bg-<?= strtolower($order['delivery_status']) == 'delivered' ? 'success' : (strtolower($order['delivery_status']) == 'pending' ? 'warning' : 'secondary'); ?> fs-6">
            <?= htmlspecialchars($order['delivery_status']); ?>
          </span>
        </div>

        <hr>
        <p><strong>Customer Name:</strong> <?= htmlspecialchars($order['customer_name']); ?></p>
        <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']); ?></p>
        <p><strong>Total:</strong> RM <?= number_format($order['total'], 2); ?></p>
        <p><strong>Delivery Address:</strong> <?= nl2br(htmlspecialchars($order['address'])); ?></p>

        <?php $items = json_decode($order['order_items'], true); ?>
        <?php if (!empty($items)): ?>
          <button class="btn btn-outline-primary btn-sm mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#details<?= $order['id']; ?>">
            View Items
          </button>

          <div class="collapse" id="details<?= $order['id']; ?>">
            <div class="order-items-box mt-3">
              <?php foreach ($items as $item): ?>
                <div class="d-flex align-items-center mb-3">
                  <img src="images/<?= htmlspecialchars($item['image']); ?>" alt="<?= htmlspecialchars($item['name']); ?>" width="70" class="me-3 rounded">
                  <div>
                    <p class="mb-0"><strong><?= htmlspecialchars($item['name']); ?></strong></p>
                    <small>RM <?= number_format($item['price'], 2); ?></small>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- ✅ Moved Invoice Button to Bottom-Right -->
        <a href="generate_invoice.php?id=<?= $order['id']; ?>" target="_blank" class="btn btn-outline-danger btn-sm invoice-btn">
          🧾 Download Invoice (PDF)
        </a>

      </div>
    <?php endwhile; ?>
  <?php else: ?>
    <p class="text-center text-muted fs-5">You have not placed any orders yet.</p>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
