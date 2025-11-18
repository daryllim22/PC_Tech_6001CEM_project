<?php
include 'connection.php';

if (!isset($_GET['id'])) {
    die("Order ID missing.");
}
$order_id = intval($_GET['id']);

// ✅ Fetch order
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

// ✅ Fetch order items with product info
$itemQuery = $conn->prepare("
    SELECT p.name, p.category, p.image, oi.price, oi.quantity
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$itemQuery->bind_param("i", $order_id);
$itemQuery->execute();
$items = $itemQuery->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Invoice #<?= $order_id ?> - PC Tech</title>
  <style>
    @media print {.no-print { display: none; }}
    body {
      font-family: Arial, sans-serif;
      background-color: #f3f6f9;
      margin: 0;
      padding: 40px;
    }
    .invoice-container {
      background: white;
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 30px 40px;
      max-width: 850px;
      margin: auto;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .header {
      text-align: center;
      margin-bottom: 25px;
    }
    .header img {
      width: 120px;
    }
    .header h2 {
      margin: 10px 0;
      color: #0066cc;
      letter-spacing: 1px;
    }
    hr {
      border: none;
      border-top: 2px solid #0066cc;
      margin: 20px 0;
    }
    .info p { margin: 6px 0; line-height: 1.4; }
    .info strong { color: #000; }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 10px;
    }
    th {
      background: #0066cc;
      color: white;
      text-align: left;
    }
    td {
      vertical-align: middle;
      background: #fff;
    }
    td.price, td.qty, td.subtotal {
      text-align: right;
      font-family: monospace;
    }
    .product-img {
      width: 60px;
      height: auto;
      border-radius: 6px;
    }
    .total {
      text-align: right;
      font-weight: bold;
      margin-top: 20px;
      font-size: 1.1rem;
    }
    .footer {
      text-align: center;
      font-size: 0.9em;
      margin-top: 40px;
      color: #555;
    }
    .btn-download {
      display: inline-block;
      margin-top: 25px;
      padding: 10px 25px;
      background: #0066cc;
      color: white;
      border-radius: 6px;
      text-decoration: none;
      font-size: 16px;
      font-weight: 500;
    }
    .btn-download:hover {
      background: #004d99;
    }
  </style>
</head>
<body>

<div class="invoice-container">
  <div class="header">
    <img src="images/logo.png" alt="PC Tech Logo">
    <h2>INVOICE</h2>
    <p><strong>Invoice #<?= $order_id ?></strong></p>
  </div>

  <hr>

  <div class="info">
    <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']); ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']); ?></p>
    <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']); ?></p>
    <p><strong>Date:</strong> <?= date("d M Y, h:i A", strtotime($order['order_date'])); ?></p>
    <p><strong>Address:</strong> <?= nl2br(htmlspecialchars($order['address'])); ?></p>
  </div>

  <table>
    <thead>
      <tr>
        <th>Image</th>
        <th>Product</th>
        <th>Category</th>
        <th class="price">Price (RM)</th>
        <th class="qty">Qty</th>
        <th class="subtotal">Subtotal (RM)</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      $total = 0;
      if ($items->num_rows > 0):
        while ($item = $items->fetch_assoc()):
          $subtotal = $item['price'] * $item['quantity'];
          $total += $subtotal;
      ?>
      <tr>
        <td><img src="images/<?= htmlspecialchars($item['image']); ?>" alt="Product Image" class="product-img"></td>
        <td><?= htmlspecialchars($item['name']); ?></td>
        <td><?= htmlspecialchars($item['category']); ?></td>
        <td class="price"><?= number_format($item['price'], 2); ?></td>
        <td class="qty"><?= $item['quantity']; ?></td>
        <td class="subtotal"><?= number_format($subtotal, 2); ?></td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="6" style="text-align:center;color:#888;">No product details available.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <p class="total">Total Amount: RM <?= number_format($total, 2); ?></p>

  <div class="footer">
    <p>Thank you for shopping with <strong>PC Tech</strong>!</p>
    <p><em>This is a system-generated invoice.</em></p>
  </div>

  <center class="no-print">
    <a href="#" class="btn-download" onclick="window.print()">📄 Download / Print PDF</a>
  </center>
</div>

</body>
</html>
