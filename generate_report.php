<?php
include 'connection.php';
session_start();

// ✅ Only admin access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$currentMonth = date('m');
$currentYear = date('Y');

// ✅ Correct query using order_items
$sql = "
SELECT 
    p.name AS product_name,
    p.category,
    SUM(oi.quantity) AS total_quantity,
    SUM(oi.quantity * oi.price) AS total_amount
FROM order_items oi
JOIN products p ON oi.product_id = p.id
JOIN orders o ON oi.order_id = o.id
WHERE MONTH(o.order_date) = ? AND YEAR(o.order_date) = ? 
  AND o.delivery_status != 'Cancelled'
GROUP BY p.id, p.name, p.category
ORDER BY total_amount DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $currentMonth, $currentYear);
$stmt->execute();
$result = $stmt->get_result();

$totalSalesQuery = $conn->prepare("
SELECT SUM(total) AS total_sales 
FROM orders 
WHERE MONTH(order_date)=? AND YEAR(order_date)=? AND delivery_status != 'Cancelled'
");
$totalSalesQuery->bind_param("ii", $currentMonth, $currentYear);
$totalSalesQuery->execute();
$totalSales = $totalSalesQuery->get_result()->fetch_assoc()['total_sales'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Monthly Sales Report - PC Tech</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #fff; font-family: Arial, sans-serif; }
.container { margin-top: 40px; }
.table th, .table td { vertical-align: middle !important; }
.table thead { background-color: #212529; color: #fff; }
@media print {.no-print { display: none; }}
</style>
</head>
<body onload="window.print()">

<div class="container">
  <h2 class="text-center">📊 PC Tech - Monthly Sales Report</h2>
  <h5 class="text-center text-muted"><?= date("F Y") ?></h5>
  <table class="table table-bordered text-center mt-4">
    <thead>
      <tr><th>Product Name</th><th>Category</th><th>Quantity Sold</th><th>Total Amount (RM)</th></tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['product_name']) ?></td>
          <td><?= htmlspecialchars($row['category']) ?></td>
          <td><?= $row['total_quantity'] ?></td>
          <td><?= number_format($row['total_amount'], 2) ?></td>
        </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="4" class="text-muted">No sales recorded this month.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  <h4 class="text-end mt-4">Total Monthly Sales: RM <?= number_format($totalSales, 2) ?></h4>
  <div class="text-center mt-4 no-print">
    <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
  </div>
</div>

</body>
</html>
