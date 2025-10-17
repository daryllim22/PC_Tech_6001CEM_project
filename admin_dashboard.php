<?php
session_start();
include 'connection.php';

// Redirect non-admin users to home
if (!isset($_SESSION['user_mail'])) {
    header("Location: login.php");
    exit;
}

// Optional: restrict access to admin only if role column exists
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit;
}

// Fetch users and products
$users_result = $conn->query("SELECT * FROM users");
$products_result = $conn->query("SELECT * FROM products");
$total_users = $users_result->num_rows;
$total_products = $products_result->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - PC Tech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar img {
            height: 50px;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        table img {
            height: 50px;
            width: auto;
            border-radius: 8px;
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar d-flex justify-content-center gap-4 py-2 bg-light">
        <a href="admin_dashboard.php"><img src="images/logo.jpg" alt="PC Tech Logo"></a>
        <a href="admin_dashboard.php"><img src="images/home.png" alt="Dashboard"></a>
        <a href="product.php"><img src="images/product.png" alt="Products"></a>
        <a href="profile.php"><img src="images/user_profile.png" alt="Profile"></a>
        <a href="logout.php"><img src="images/logout.png" alt="Logout"></a>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Admin Dashboard</h2>

        <!-- Dashboard Cards -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card p-3 text-center bg-primary text-white">
                    <h4>Total Users</h4>
                    <h2><?= $total_users ?></h2>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-3 text-center bg-success text-white">
                    <h4>Total Products</h4>
                    <h2><?= $total_products ?></h2>
                </div>
            </div>
        </div>

        <!-- User Management -->
        <div class="card mb-4 p-4">
            <h4 class="mb-3">Registered Users</h4>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <?php if ($users_result && $users_result->fetch_field_direct(3)->name === 'role'): ?>
                                <th>Role</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $users_result->data_seek(0);
                        while ($user = $users_result->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['full_name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <?php if (isset($user['role'])): ?>
                                <td><?= htmlspecialchars($user['role']) ?></td>
                            <?php endif; ?>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Product Management -->
        <div class="card p-4">
            <h4 class="mb-3">Product List</h4>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price (RM)</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($product = $products_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $product['id'] ?></td>
                            <td><img src="uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>"></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= number_format($product['price'], 2) ?></td>
                            <td><?= $product['created_at'] ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>
