<?php
include 'connection.php';
header('Content-Type: application/json');

// ✅ Fetch recent (unread) orders — limit to the latest 10
$query = "
SELECT id, customer_name, total, order_date 
FROM orders 
WHERE viewed_admin = 0 
ORDER BY order_date DESC 
LIMIT 10
";
$result = $conn->query($query);

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = [
        'id' => $row['id'],
        'customer_name' => $row['customer_name'],
        'total' => (float)$row['total'],
        'order_date' => $row['order_date']
    ];
}

// ✅ Count unread orders
$unreadCount = $conn->query("SELECT COUNT(*) AS unread FROM orders WHERE viewed_admin = 0")
                    ->fetch_assoc()['unread'] ?? 0;

// ✅ Return as JSON
echo json_encode([
    'unread' => $unreadCount,
    'orders' => $orders
]);
?>
