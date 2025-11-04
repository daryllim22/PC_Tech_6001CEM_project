<?php
session_start();
include 'connection.php';

// ✅ User must be logged in
if (!isset($_SESSION['user_mail'])) {
    header("Location: login.php");
    exit;
}

// ✅ Ensure cart is not empty
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

// ✅ Get user input (you can adjust to match your checkout form)
$customer_name  = $_POST['customer_name'];
$customer_email = $_POST['customer_email'];
$address        = $_POST['address'];
$payment_method = $_POST['payment_method'];
$total          = $_POST['total'];
$order_date     = date("Y-m-d H:i:s");

// ✅ Insert new order with viewed_admin = 0 so admin sees notification
$stmt = $conn->prepare("
    INSERT INTO orders (customer_name, customer_email, address, payment_method, total, delivery_status, order_date, viewed_admin)
    VALUES (?, ?, ?, ?, ?, 'Pending', ?, 0)
");
$stmt->bind_param("ssssds", $customer_name, $customer_email, $address, $payment_method, $total, $order_date);
$stmt->execute();
$order_id = $conn->insert_id; // Get newly created order ID

/* =============================
   INSERT EACH CART ITEM INTO order_items
============================= */
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $quantity = $item['quantity'];
        $price    = $item['price'];

        $stmt_item = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");
        $stmt_item->bind_param("iiid", $order_id, $product_id, $quantity, $price);
        $stmt_item->execute();
    }
}

/* =============================
   CLEAR CART & REDIRECT
============================= */
unset($_SESSION['cart']);
header("Location: order_success.php");
exit;
?>
