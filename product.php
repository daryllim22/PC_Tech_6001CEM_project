<?php
session_start();
include 'connection.php';

// --- Handle AJAX Add to Cart ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_add_to_cart'])) {
    $item = [
        'id' => $_POST['id'],
        'name' => $_POST['name'],
        'price' => $_POST['price'],
        'image' => $_POST['image']
    ];

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $ids = array_column($_SESSION['cart'], 'id');
    if (!in_array($item['id'], $ids)) {
        $_SESSION['cart'][] = $item;
        echo json_encode(['status' => 'success', 'message' => '✅ Item added to cart!']);
    } else {
        echo json_encode(['status' => 'info', 'message' => 'ℹ️ Item already in cart.']);
    }
    exit;
}

// --- Handle Filters ---
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? '';

$query = "SELECT * FROM products WHERE 1=1";

if (!empty($search)) {
    $query .= " AND name LIKE '%" . $conn->real_escape_string($search) . "%'";
}
if (!empty($category)) {
    $query .= " AND category = '" . $conn->real_escape_string($category) . "'";
}
if ($sort == 'low_high') {
    $query .= " ORDER BY price ASC";
} elseif ($sort == 'high_low') {
    $query .= " ORDER BY price DESC";
} else {
    $query .= " ORDER BY created_at DESC";
}

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Products - PC Tech</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
  <style>
    #alertBox {
      position: fixed;
      top: 100px;
      right: 20px;
      display: none;
      z-index: 9999;
      padding: 15px 20px;
      border-radius: 8px;
      color: white;
      font-weight: 500;
      box-shadow: 0 4px 10px rgba(0,0,0,0.2);
      transition: opacity 0.4s ease;
    }
    #alertBox.success { background-color: #28a745; }
    #alertBox.info { background-color: #17a2b8; }
  </style>
</head>
<body>

<!-- ✅ LOGO HEADER -->
<header class="header-logo text-center py-3 bg-white shadow-sm">
  <img src="images/logo.png" alt="PC Tech Logo" style="max-width:200px; height:auto;">
</header>

<!-- ✅ NAVBAR -->
<nav class="navbar d-flex justify-content-center gap-4 py-2 bg-light shadow-sm">
  <a href="home.php"><img src="images/home.png" alt="Home"></a>
  <a href="product.php"><img src="images/product.png" alt="Products"></a>
  <a href="cart.php"><img src="images/cart.png" alt="Cart"></a>
  <a href="order_history.php"><img src="images/history.png" alt="Orders"></a>
  <a href="profile.php"><img src="images/user_profile.png" alt="Profile"></a>
  <a href="logout.php"><img src="images/logout.png" alt="Logout"></a>
</nav>

<div style="margin-top:160px;"></div>

<!-- ✅ FILTER SECTION -->
<div class="container mt-4">
  <h2 class="text-center mb-4">Our Products</h2>

  <form method="GET" class="row g-3 mb-4 justify-content-center">
    <div class="col-md-3">
      <input type="text" name="search" class="form-control" placeholder="Search by name" value="<?= htmlspecialchars($search); ?>">
    </div>

    <div class="col-md-3">
      <select name="category" class="form-select">
        <option value="">All Categories</option>
        <option value="Laptop" <?= ($category == 'Laptop') ? 'selected' : ''; ?>>Laptop</option>
        <option value="Headset" <?= ($category == 'Headset') ? 'selected' : ''; ?>>Headset</option>
        <option value="Mouse" <?= ($category == 'Mouse') ? 'selected' : ''; ?>>Mouse</option>
        <option value="Monitor" <?= ($category == 'Monitor') ? 'selected' : ''; ?>>Monitor</option>
      </select>
    </div>

    <div class="col-md-3">
      <select name="sort" class="form-select">
        <option value="">Sort by</option>
        <option value="low_high" <?= ($sort == 'low_high') ? 'selected' : ''; ?>>Price: Low to High</option>
        <option value="high_low" <?= ($sort == 'high_low') ? 'selected' : ''; ?>>Price: High to Low</option>
      </select>
    </div>

    <div class="col-md-2">
      <button type="submit" class="btn btn-primary w-100">Filter</button>
    </div>
  </form>

  <!-- ✅ PRODUCT GRID -->
  <div class="row g-4">
    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <?php
          $id = $row['id'];
          $name = htmlspecialchars($row['name']);
          $desc = $row['description']; // keep original text
          $price = number_format($row['price'], 2);
          $img = 'images/' . htmlspecialchars($row['image']);
          $cat = htmlspecialchars($row['category']);
        ?>
        <div class="col-md-4 text-center">
          <div class="card p-3 shadow-sm h-100" data-bs-toggle="modal" data-bs-target="#productModal<?= $id; ?>" style="cursor:pointer;">
            <img src="<?= $img; ?>" class="card-img-top mb-3" style="height:220px; object-fit:contain;">
            <div class="card-body">
              <h5><?= $name; ?></h5>
              <p class="text-muted"><?= $cat; ?></p>
              <p class="text-secondary small">
                <?= strlen($desc) > 100 ? htmlspecialchars(substr($desc, 0, 100)) . '...' : htmlspecialchars($desc); ?>
              </p>
              <p class="fw-bold text-primary">RM <?= $price; ?></p>
            </div>
          </div>
        </div>

        <!-- ✅ POP-UP MODAL -->
        <div class="modal fade" id="productModal<?= $id; ?>" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"><?= $name; ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body text-center">
                <img src="<?= $img; ?>" alt="<?= $name; ?>" class="img-fluid mb-3 rounded" style="max-height:300px; object-fit:contain;">
                <p class="text-muted fs-5"><?= nl2br($row['description']); ?></p>
                <h4 class="text-primary mb-3">RM <?= $price; ?></h4>
              </div>
              <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button 
                  type="button" 
                  class="btn btn-success addToCartBtn"
                  data-id="<?= $id; ?>"
                  data-name="<?= $name; ?>"
                  data-price="<?= $row['price']; ?>"
                  data-image="<?= htmlspecialchars($row['image']); ?>">
                  🛒 Add to Cart
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <p class="text-center text-muted">No products found.</p>
    <?php endif; ?>
  </div>
</div>

<!-- ✅ ALERT BOX -->
<div id="alertBox"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Show alert message
function showAlert(message, type='success') {
  const alertBox = document.getElementById('alertBox');
  alertBox.textContent = message;
  alertBox.className = type;
  alertBox.style.display = 'block';
  alertBox.style.opacity = '1';
  setTimeout(() => {
    alertBox.style.opacity = '0';
    setTimeout(() => alertBox.style.display = 'none', 400);
  }, 2000);
}

// AJAX Add to Cart
document.querySelectorAll('.addToCartBtn').forEach(btn => {
  btn.addEventListener('click', function() {
    const formData = new FormData();
    formData.append('ajax_add_to_cart', '1');
    formData.append('id', this.dataset.id);
    formData.append('name', this.dataset.name);
    formData.append('price', this.dataset.price);
    formData.append('image', this.dataset.image);

    fetch('product.php', { method: 'POST', body: formData })
      .then(res => res.json())
      .then(data => showAlert(data.message, data.status))
      .catch(err => console.error(err));
  });
});
</script>
</body>
</html>
