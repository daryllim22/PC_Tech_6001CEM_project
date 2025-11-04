<?php
session_start();
include 'connection.php';

// ✅ Admin-only access
if (!isset($_SESSION['user_mail'])) {
    header("Location: login.php");
    exit;
}
if ($_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit;
}

/* =============================
   USERS CRUD
============================= */
if (isset($_POST['add_user'])) {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit;
}

if (isset($_POST['edit_user'])) {
    $id = $_POST['id'];
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $email, $role, $id);
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit;
}

if (isset($_GET['delete_user'])) {
    $id = $_GET['delete_user'];
    $conn->query("DELETE FROM users WHERE id=$id");
    header("Location: admin_dashboard.php");
    exit;
}

/* =============================
   PRODUCTS CRUD
============================= */
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $image = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];
    if ($image) move_uploaded_file($tmp, "images/" . $image);
    $stmt = $conn->prepare("INSERT INTO products (name, category, price, description, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdss", $name, $category, $price, $description, $image);
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit;
}

if (isset($_POST['edit_product'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $image = $_FILES['image']['name'];
    $tmp = $_FILES['image']['tmp_name'];
    if ($image) {
        move_uploaded_file($tmp, "images/" . $image);
        $stmt = $conn->prepare("UPDATE products SET name=?, category=?, price=?, description=?, image=? WHERE id=?");
        $stmt->bind_param("ssdssi", $name, $category, $price, $description, $image, $id);
    } else {
        $stmt = $conn->prepare("UPDATE products SET name=?, category=?, price=?, description=? WHERE id=?");
        $stmt->bind_param("ssdsi", $name, $category, $price, $description, $id);
    }
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit;
}

if (isset($_GET['delete_product'])) {
    $id = $_GET['delete_product'];
    $conn->query("DELETE FROM products WHERE id=$id");
    header("Location: admin_dashboard.php");
    exit;
}

/* =============================
   ORDERS & STATUS CHANGE
============================= */
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['delivery_status'];
    $stmt = $conn->prepare("UPDATE orders SET delivery_status=? WHERE id=?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit;
}

if (isset($_GET['mark_read'])) {
    $conn->query("UPDATE orders SET viewed_admin=1");
    header("Location: admin_dashboard.php");
    exit;
}

/* =============================
   FETCH DATA
============================= */
$users = $conn->query("SELECT * FROM users ORDER BY id ASC");
$products = $conn->query("SELECT * FROM products ORDER BY id ASC");
$orders = $conn->query("SELECT * FROM orders ORDER BY id ASC");
$totalSales = $conn->query("SELECT SUM(total) AS total_sales FROM orders WHERE delivery_status != 'Cancelled'")
                  ->fetch_assoc()['total_sales'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - PC Tech</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<style>
body { background-color: #f8f9fa; }
.navbar img { height: 50px; }
.card { border-radius: 12px; box-shadow: 0 3px 6px rgba(0,0,0,0.1); }
.status-badge { display:inline-block;padding:6px 12px;border-radius:20px;font-weight:500;color:#fff;}
.status-pending{background-color:#ffc107;}
.status-shipped{background-color:#17a2b8;}
.status-delivered{background-color:#28a745;}
.status-cancelled{background-color:#dc3545;}
</style>
</head>
<body>

<!-- ✅ HEADER NAVBAR -->
<nav class="navbar d-flex justify-content-center align-items-center gap-4 py-2 bg-light shadow-sm fixed-top">
  <a href="admin_dashboard.php"><img src="images/logo.png" alt="Logo"></a>
  <button id="notifIcon" data-bs-toggle="modal" data-bs-target="#notificationsModal" class="btn position-relative bg-transparent border-0">
    <i class="bi bi-bell-fill fs-3 text-dark"></i>
    <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
          style="display:none; font-size:0.75rem;">0</span>
  </button>
  <a href="logout.php" class="btn bg-transparent border-0">
    <i class="bi bi-box-arrow-right fs-3 text-dark"></i>
  </a>
</nav>

<div class="container mt-5 pt-5">
  <h2 class="text-center mb-4">Admin Dashboard</h2>

  <!-- Notifications Modal -->
  <div class="modal fade" id="notificationsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Recent Orders</h5>
          <a href="?mark_read=1" class="btn btn-sm btn-light">Mark All as Read</a>
        </div>
        <div class="modal-body">
          <ul id="notifList" class="list-group"></ul>
        </div>
      </div>
    </div>
  </div>

  <!-- ✅ USERS SECTION -->
  <div class="card mb-5 p-4">
    <div class="d-flex justify-content-between align-items-center">
      <h4>User List</h4>
      <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal"><i class="bi bi-person-plus-fill"></i></button>
    </div>
    <table class="table table-striped text-center align-middle mt-3">
      <thead class="table-dark">
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php while ($u = $users->fetch_assoc()): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><?= htmlspecialchars($u['full_name']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= htmlspecialchars($u['role']) ?></td>
          <td>
            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editUser<?= $u['id'] ?>"><i class="bi bi-pencil-fill"></i></button>
            <a href="?delete_user=<?= $u['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this user?')"><i class="bi bi-trash-fill"></i></a>
          </td>
        </tr>
        <!-- Edit User Modal -->
        <div class="modal fade" id="editUser<?= $u['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="post">
                <div class="modal-header bg-warning">
                  <h5 class="modal-title">Edit User</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <input type="text" name="full_name" class="form-control mb-3" value="<?= htmlspecialchars($u['full_name']) ?>" required>
                  <input type="email" name="email" class="form-control mb-3" value="<?= htmlspecialchars($u['email']) ?>" required>
                  <select name="role" class="form-select" required>
                    <option value="user" <?= $u['role']=='user'?'selected':'' ?>>User</option>
                    <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                  </select>
                </div>
                <div class="modal-footer">
                  <button type="submit" name="edit_user" class="btn btn-warning">Update</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Add User Modal -->
  <div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="post">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title">Add User</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="text" name="full_name" class="form-control mb-3" placeholder="Full Name" required>
            <input type="email" name="email" class="form-control mb-3" placeholder="Email" required>
            <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
            <select name="role" class="form-select" required>
              <option value="user">User</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="modal-footer">
            <button type="submit" name="add_user" class="btn btn-success">Add</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ✅ PRODUCTS SECTION -->
  <div class="card mb-5 p-4">
    <div class="d-flex justify-content-between align-items-center">
      <h4>Product List</h4>
      <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addProductModal"><i class="bi bi-plus-circle-fill"></i></button>
    </div>
    <table class="table table-striped text-center align-middle mt-3">
      <thead class="table-dark">
        <tr><th>ID</th><th>Image</th><th>Name</th><th>Category</th><th>Price (RM)</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php while ($p = $products->fetch_assoc()): ?>
        <tr>
          <td><?= $p['id'] ?></td>
          <td><img src="images/<?= htmlspecialchars($p['image']) ?>" height="60"></td>
          <td><?= htmlspecialchars($p['name']) ?></td>
          <td><?= htmlspecialchars($p['category']) ?></td>
          <td><?= number_format($p['price'],2) ?></td>
          <td>
            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editProduct<?= $p['id'] ?>"><i class="bi bi-pencil-fill"></i></button>
            <a href="?delete_product=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')"><i class="bi bi-trash-fill"></i></a>
          </td>
        </tr>
        <!-- Edit Product Modal -->
        <div class="modal fade" id="editProduct<?= $p['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="post" enctype="multipart/form-data">
                <div class="modal-header bg-warning">
                  <h5 class="modal-title">Edit Product</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <input type="text" name="name" class="form-control mb-3" value="<?= htmlspecialchars($p['name']) ?>" required>
                  <select name="category" class="form-select mb-3" required>
                    <option value="Laptop" <?= $p['category']=='Laptop'?'selected':'' ?>>Laptop</option>
                    <option value="Headset" <?= $p['category']=='Headset'?'selected':'' ?>>Headset</option>
                    <option value="Mouse" <?= $p['category']=='Mouse'?'selected':'' ?>>Mouse</option>
                    <option value="Monitor" <?= $p['category']=='Monitor'?'selected':'' ?>>Monitor</option>
                  </select>
                  <input type="number" step="0.01" name="price" class="form-control mb-3" value="<?= $p['price'] ?>" required>
                  <textarea name="description" class="form-control mb-3"><?= htmlspecialchars($p['description']) ?></textarea>
                  <div class="text-center mb-3">
                    <img src="images/<?= htmlspecialchars($p['image']) ?>" height="100" class="rounded border mb-2">
                    <p class="text-muted small">Current Image</p>
                  </div>
                  <input type="file" name="image" class="form-control mb-3">
                </div>
                <div class="modal-footer">
                  <button type="submit" name="edit_product" class="btn btn-warning">Update</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Add Product Modal -->
  <div class="modal fade" id="addProductModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="post" enctype="multipart/form-data">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title">Add Product</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="text" name="name" class="form-control mb-3" placeholder="Product Name" required>
            <select name="category" class="form-select mb-3" required>
              <option value="Laptop">Laptop</option>
              <option value="Headset">Headset</option>
              <option value="Mouse">Mouse</option>
              <option value="Monitor">Monitor</option>
            </select>
            <input type="number" step="0.01" name="price" class="form-control mb-3" placeholder="Price" required>
            <textarea name="description" class="form-control mb-3" placeholder="Description"></textarea>
            <input type="file" name="image" class="form-control mb-3">
          </div>
          <div class="modal-footer">
            <button type="submit" name="add_product" class="btn btn-success">Add</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ✅ ORDERS SECTION -->
  <div class="card mb-5 p-4">
    <h4>Order List</h4>
    <table class="table table-striped text-center align-middle mt-3">
      <thead class="table-dark">
        <tr>
          <th>ID</th><th>Customer</th><th>Email</th><th>Address</th><th>Payment</th><th>Total (RM)</th><th>Status</th><th>Update</th><th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($o = $orders->fetch_assoc()): ?>
        <?php
          $status = strtolower($o['delivery_status'] ?? 'pending');
          switch ($status) {
              case 'pending':   $badge = 'status-pending'; break;
              case 'shipped':   $badge = 'status-shipped'; break;
              case 'delivered': $badge = 'status-delivered'; break;
              case 'cancelled': $badge = 'status-cancelled'; break;
              default:          $badge = 'status-pending';
          }
        ?>
        <tr>
          <td><?= $o['id'] ?></td>
          <td><?= htmlspecialchars($o['customer_name']) ?></td>
          <td><?= htmlspecialchars($o['customer_email']) ?></td>
          <td><?= htmlspecialchars($o['address']) ?></td>
          <td><?= htmlspecialchars($o['payment_method']) ?></td>
          <td><?= number_format($o['total'],2) ?></td>
          <td><span class="status-badge <?= $badge ?>"><?= ucfirst($status) ?></span></td>
          <td>
            <form method="post" class="d-flex">
              <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
              <select name="delivery_status" class="form-select form-select-sm me-2">
                <option value="Pending" <?= $o['delivery_status']=='Pending'?'selected':'' ?>>Pending</option>
                <option value="Shipped" <?= $o['delivery_status']=='Shipped'?'selected':'' ?>>Shipped</option>
                <option value="Delivered" <?= $o['delivery_status']=='Delivered'?'selected':'' ?>>Delivered</option>
                <option value="Cancelled" <?= $o['delivery_status']=='Cancelled'?'selected':'' ?>>Cancelled</option>
              </select>
              <button type="submit" name="update_status" class="btn btn-primary btn-sm">Update</button>
            </form>
          </td>
          <td><?= $o['order_date'] ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- ✅ TOTAL SALES -->
  <div class="card mb-5 p-4 text-center">
    <h4>Total Sales: RM <?= number_format($totalSales, 2) ?></h4>
    <a href="generate_report.php" target="_blank" class="btn btn-primary mt-3">
      <i class="bi bi-file-earmark-pdf-fill"></i> Generate PDF Report
    </a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
async function loadNotifications() {
  const res = await fetch('notifications_api.php');
  if (!res.ok) return;
  const data = await res.json();
  const badge = document.getElementById('notifBadge');
  badge.style.display = data.unread > 0 ? 'inline-block' : 'none';
  badge.textContent = data.unread;
  const list = document.getElementById('notifList');
  list.innerHTML = '';
  if (data.orders && data.orders.length > 0) {
    data.orders.forEach(o => {
      const li = document.createElement('li');
      li.className = 'list-group-item d-flex justify-content-between align-items-center';
      li.innerHTML = `<span><strong>${o.customer_name}</strong> placed order #${o.id} - RM ${o.total.toFixed(2)}</span>
                      <small class="text-muted">${o.order_date}</small>`;
      list.appendChild(li);
    });
  } else {
    list.innerHTML = '<p class="text-center text-muted">No recent orders.</p>';
  }
}
setInterval(loadNotifications, 10000);
window.onload = loadNotifications;
</script>
</body>
</html>
