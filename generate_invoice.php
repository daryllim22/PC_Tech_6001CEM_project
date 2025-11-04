<?php
include 'connection.php';

if (!isset($_GET['id'])) {
    die("Order ID missing.");
}
$order_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found.");
}

$items = json_decode($order['order_items'], true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Invoice #<?= $order_id ?> - PC Tech</title>
  <style>
    @media print {
      .no-print { display: none; }
    }
    body {
      font-family: Arial, sans-serif;
      padding: 40px;
      background-color: #f8f9fa;
    }
    .invoice-container {
      background: white;
      border: 1px solid #ccc;
      border-radius: 10px;
      padding: 30px;
      max-width: 750px;
      margin: auto;
    }
    .header {
      text-align: center;
      border-bottom: 2px solid #007bff;
      padding-bottom: 15px;
      margin-bottom: 25px;
    }
    .header img {
      width: 120px;
    }
    .header h2 {
      margin-top: 10px;
      color: #007bff;
    }
    .info p { margin: 5px 0; }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    th, td {
      border: 1px solid #999;
      padding: 10px;
      text-align: left;
    }
    th { background: #007bff; color: white; }
    tr:nth-child(even) { background: #f9f9f9; }
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
      margin-top: 20px;
      padding: 10px 20px;
      background: #007bff;
      color: white;
      border-radius: 5px;
      text-decoration: none;
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

  <div class="info">
    <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']); ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']); ?></p>
    <p><strong>Payment Method:</strong> <?= htmlspecialchars($order['payment_method']); ?></p>
    <p><strong>Date:</strong> <?= date("d M Y, h:i A", strtotime($order['order_date'])); ?></p>
    <p><strong>Address:</strong> <?= nl2br(htmlspecialchars($order['address'])); ?></p>
  </div>

  <table>
    <tr>
      <th>Product</th>
      <th>Price (RM)</th>
      <th>Qty</th>
      <th>Subtotal (RM)</th>
    </tr>
    <?php 
    $total = 0;
    foreach ($items as $item): 
        $qty = $item['qty'] ?? 1;
        $subtotal = $item['price'] * $qty;
        $total += $subtotal;
    ?>
    <tr>
      <td><?= htmlspecialchars($item['name']); ?></td>
      <td><?= number_format($item['price'], 2); ?></td>
      <td><?= $qty; ?></td>
      <td><?= number_format($subtotal, 2); ?></td>
    </tr>
    <?php endforeach; ?>
  </table>

  <p class="total">Total Amount: RM <?= number_format($total, 2); ?></p>

  <div class="footer">
    <p>Thank you for shopping with PC Tech!</p>
    <p><em>This is a system-generated invoice.</em></p>
  </div>

  <center class="no-print">
    <a href="#" class="btn-download" onclick="window.print()">📄 Download / Print PDF</a>
  </center>
</div>

</body>
</html>
