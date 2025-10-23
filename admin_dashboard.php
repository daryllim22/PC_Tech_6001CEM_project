<?php
session_start();
include 'connection.php';

// Redirect non-admin users
if (!isset($_SESSION['user_mail'])) {
    header("Location: login.php");
    exit;
}
if (isset($_SESSION['role']) && $_SESSION['role'] !== 'admin') {
    header("Location: home.php");
    exit;
}

// --- ADD PRODUCT ---
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $desc = $_POST['description'];
    $image = $_FILES['image']['name'];
    $target = "uploads/" . basename($image);
    move_uploaded_file($_FILES['image']['tmp_name'], $target);

    $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssds", $name, $desc, $price, $image);
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit;
}

// --- EDIT PRODUCT ---
if (isset($_POST['edit_product'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $desc = $_POST['description'];

    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $target = "uploads/" . basename($image);
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, image=? WHERE id=?");
        $stmt->bind_param("ssdsi", $name, $desc, $price, $image, $id);
    } else {
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=? WHERE id=?");
        $stmt->bind_param("ssdi", $name, $desc, $price, $id);
    }
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit;
}

// --- DELETE PRODUCT ---
if (isset($_GET['delete_product'])) {
    $id = $_GET['delete_product'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit;
}

// --- ADD USER ---
if (isset($_POST['add_user'])) {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'] ?? 'user';
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $pass, $role);
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit;
}

// --- EDIT USER ---
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

// --- DELETE USER ---
if (isset($_GET['delete_user'])) {
    $id = $_GET['delete_user'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit;
}

// Fetch users & products
$users = $conn->query("SELECT * FROM users");
$products = $conn->query("SELECT * FROM products");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - PC Tech</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f8f9fa; }
.navbar img { height: 50px; }
.card { border-radius: 12px; box-shadow: 0 3px 6px rgba(0,0,0,0.1); }
.card:hover { transform: translateY(-3px); }
table img { height: 50px; border-radius: 8px; }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar d-flex justify-content-center gap-4 py-2 bg-light">
  <a href="admin_dashboard.php"><img src="images/logo.jpg" alt="Logo"></a>
  <a href="product.php"><img src="images/product.png" alt="Products"></a>
  <a href="logout.php"><img src="images/logout.png" alt="Logout"></a>
</nav>

<div class="container mt-5">
  <h2 class="text-center mb-4">Admin Dashboard</h2>

  <!-- Users Section -->
  <div class="card mb-4 p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4>Registered Users</h4>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">+ Add User</button>
    </div>
    <table class="table table-striped text-center align-middle">
      <thead class="table-dark"><tr><th>ID</th><th>Full Name</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead>
      <tbody>
        <?php while ($u = $users->fetch_assoc()): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><?= htmlspecialchars($u['full_name']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= htmlspecialchars($u['role']) ?></td>
          <td>
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $u['id'] ?>">Edit</button>
            <a href="?delete_user=<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</a>
          </td>
        </tr>
        <!-- Edit User Modal -->
        <div class="modal fade" id="editUserModal<?= $u['id'] ?>" tabindex="-1">
          <div class="modal-dialog"><div class="modal-content">
            <form method="POST">
              <div class="modal-header"><h5>Edit User</h5></div>
              <div class="modal-body">
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                <div class="mb-3"><label>Full Name</label>
                  <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($u['full_name']) ?>" required>
                </div>
                <div class="mb-3"><label>Email</label>
                  <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($u['email']) ?>" required>
                </div>
                <div class="mb-3"><label>Role</label>
                  <select name="role" class="form-select">
                    <option value="user" <?= $u['role']=='user'?'selected':'' ?>>User</option>
                    <option value="admin" <?= $u['role']=='admin'?'selected':'' ?>>Admin</option>
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" name="edit_user" class="btn btn-success">Save Changes</button>
              </div>
            </form>
          </div></div>
        </div>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Products Section -->
  <div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h4>Product List</h4>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">+ Add Product</button>
    </div>
    <table class="table table-striped text-center align-middle">
      <thead class="table-dark"><tr><th>ID</th><th>Image</th><th>Name</th><th>Price (RM)</th><th>Actions</th></tr></thead>
      <tbody>
        <?php while ($p = $products->fetch_assoc()): ?>
        <tr>
          <td><?= $p['id'] ?></td>
          <td><img src="uploads/<?= htmlspecialchars($p['image']) ?>" width="70"></td>
          <td><?= htmlspecialchars($p['name']) ?></td>
          <td><?= number_format($p['price'],2) ?></td>
          <td>
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editProductModal<?= $p['id'] ?>">Edit</button>
            <a href="?delete_product=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">Delete</a>
          </td>
        </tr>

        <!-- Edit Product Modal -->
        <div class="modal fade" id="editProductModal<?= $p['id'] ?>" tabindex="-1">
          <div class="modal-dialog"><div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
              <div class="modal-header"><h5>Edit Product</h5></div>
              <div class="modal-body">
                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                <div class="mb-3"><label>Name</label>
                  <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($p['name']) ?>" required>
                </div>
                <div class="mb-3"><label>Description</label>
                  <textarea name="description" class="form-control"><?= htmlspecialchars($p['description']) ?></textarea>
                </div>
                <div class="mb-3"><label>Price</label>
                  <input type="number" step="0.01" name="price" class="form-control" value="<?= $p['price'] ?>" required>
                </div>
                <div class="mb-3"><label>Change Image (optional)</label>
                  <input type="file" name="image" class="form-control">
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" name="edit_product" class="btn btn-success">Save Changes</button>
              </div>
            </form>
          </div></div>
        </div>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST">
      <div class="modal-header"><h5>Add New User</h5></div>
      <div class="modal-body">
        <div class="mb-3"><label>Full Name</label><input type="text" name="full_name" class="form-control" required></div>
        <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
        <div class="mb-3"><label>Role</label>
          <select name="role" class="form-select">
            <option value="user">User</option>
            <option value="admin">Admin</option>
          </select>
        </div>
      </div>
      <div class="modal-footer"><button type="submit" name="add_user" class="btn btn-primary">Add User</button></div>
    </form>
  </div></div>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addProductModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content">
    <form method="POST" enctype="multipart/form-data">
      <div class="modal-header"><h5>Add New Product</h5></div>
      <div class="modal-body">
        <div class="mb-3"><label>Name</label><input type="text" name="name" class="form-control" required></div>
        <div class="mb-3"><label>Description</label><textarea name="description" class="form-control"></textarea></div>
        <div class="mb-3"><label>Price (RM)</label><input type="number" step="0.01" name="price" class="form-control" required></div>
        <div class="mb-3"><label>Image</label><input type="file" name="image" class="form-control" required></div>
      </div>
      <div class="modal-footer"><button type="submit" name="add_product" class="btn btn-primary">Add Product</button></div>
    </form>
  </div></div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
