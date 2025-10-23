<?php
session_start();
include 'connection.php';

// Handle AJAX add-to-cart request
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
        echo json_encode(['status' => 'success', 'message' => 'Item added to cart!']);
    } else {
        echo json_encode(['status' => 'info', 'message' => 'Item already in cart.']);
    }
    exit;
}

// Fetch products
$sql = "SELECT * FROM products ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Products - PC Tech</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <style>
    body { background: #f8f9fa; }
    .content { max-width: 1100px; margin: 0 auto; }
    .announcement-banner { background: #fff; border-radius: 12px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); }
    .card {
      border: 0;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.08);
      transition: transform .2s ease, box-shadow .2s ease;
      background: #ffffff;
      cursor: pointer;
    }
    .card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,0.12); }
    .card-img-top { height: 200px; object-fit: cover; border-radius: 12px 12px 0 0; }
    .price-badge { font-weight: 700; font-size: 1rem; }
    .filter-btn.active { background-color: #0d6efd; color: white; }
    #searchInput { max-width: 350px; display: inline-block; margin-left: 10px; }
    .toast-container { position: fixed; top: 20px; right: 20px; z-index: 2000; }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar d-flex justify-content-center gap-4 py-2 bg-light">
  <a href="home.php"><img src="images/logo.jpg" alt="PC Tech Logo" style="height: 60px; margin-bottom: 10px;"></a>
  <a href="home.php"><img src="images/home.png" alt="Home"></a>
  <a href="product.php"><img src="images/product.png" alt="Products"></a>
  <a href="cart.php"><img src="images/cart.png" alt="Cart"></a>
  <a href="profile.php"><img src="images/user_profile.png" alt="Profile"></a>
  <a href="logout.php"><img src="images/logout.png" alt="Logout"></a>
</nav>

<div class="content">
  <div class="announcement-banner p-5 my-4 text-center">
    <h1 class="mb-2">Our Products</h1>
    <p class="fs-5 mb-0">Explore the latest PCs, laptops, and accessories curated by PC Tech. Quality, performance, and value—built for study, work, and play.</p>
  </div>

  <!-- Filters, Search & Sort -->
  <div class="text-center mb-4">
    <div class="btn-group mb-3">
      <button class="btn btn-outline-primary filter-btn active" data-category="all">All</button>
      <button class="btn btn-outline-primary filter-btn" data-category="laptop">Laptops</button>
      <button class="btn btn-outline-primary filter-btn" data-category="mouse">Mouses</button>
      <button class="btn btn-outline-primary filter-btn" data-category="keyboard">Keyboards</button>
      <button class="btn btn-outline-primary filter-btn" data-category="monitor">Monitors</button>
      <button class="btn btn-outline-primary filter-btn" data-category="headset">Headsets</button>
    </div>
    <div class="d-flex justify-content-center flex-wrap gap-2">
      <input type="text" id="searchInput" class="form-control w-auto" placeholder="Search products...">
      <select id="sortSelect" class="form-select w-auto">
        <option value="newest">Sort: Newest</option>
        <option value="low">Price: Low → High</option>
        <option value="high">Price: High → Low</option>
        <option value="az">Name (A–Z)</option>
      </select>
    </div>
  </div>

  <!-- Cart Button -->
  <div class="text-center mb-4">
    <a href="cart.php" class="btn btn-primary">
      <img src="images/cart.png" alt="Cart" style="height:24px; margin-right:6px;">View Cart
    </a>
  </div>

  <!-- Product Grid -->
  <div class="row g-4 mb-4" id="productGrid">
    <?php
    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $name = htmlspecialchars($row['name']);
        $desc = htmlspecialchars($row['description']);
        $category = strtolower($row['category'] ?? 'other');
        $short = mb_strimwidth($desc, 0, 80, '...');
        $price = number_format($row['price'], 2);
        $imgFile = htmlspecialchars($row['image']);
        $imgSrc = $imgFile ? 'uploads/' . $imgFile : 'images/placeholder.png';
        echo '
        <div class="col-12 col-sm-6 col-lg-4 col-xl-3 product-card" 
             data-category="'.$category.'" 
             data-name="'.strtolower($name).'" 
             data-desc="'.strtolower($desc).'" 
             data-price="'.$row['price'].'" 
             data-created="'.$row['created_at'].'">
          <div class="card h-100" data-bs-toggle="modal" data-bs-target="#productModal'.$id.'">
            <img src="'.$imgSrc.'" class="card-img-top" alt="'.$name.'">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title">'.$name.'</h5>
              <p class="card-text text-muted flex-grow-1">'.$short.'</p>
            </div>
            <div class="card-footer bg-white text-center">
              <span class="price-badge">RM '.$price.'</span>
            </div>
          </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="productModal'.$id.'" tabindex="-1" aria-labelledby="productModalLabel'.$id.'" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="productModalLabel'.$id.'">'.$name.'</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body text-center">
                <img src="'.$imgSrc.'" class="img-fluid mb-3 rounded" style="max-height:300px; object-fit:contain;">
                <p class="fs-5 text-muted">'.$desc.'</p>
                <h4 class="text-primary">RM '.$price.'</h4>
              </div>
              <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success addToCartBtn"
                  data-id="'.$id.'" 
                  data-name="'.$name.'" 
                  data-price="'.$row['price'].'" 
                  data-image="'.$imgSrc.'">🛒 Add to Cart</button>
              </div>
            </div>
          </div>
        </div>';
      }
    } else {
      echo "<p class='text-center text-muted'>No products available.</p>";
    }
    ?>
  </div>
</div>

<!-- Toast Notification -->
<div class="toast-container position-fixed top-0 end-0 p-3">
  <div id="cartToast" class="toast align-items-center text-white bg-success border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="toastMsg">Item added to cart!</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const filterButtons = document.querySelectorAll('.filter-btn');
const productCards = Array.from(document.querySelectorAll('.product-card'));
const searchInput = document.getElementById('searchInput');
const sortSelect = document.getElementById('sortSelect');

// Filter + Search
filterButtons.forEach(btn => {
  btn.addEventListener('click', () => {
    filterButtons.forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    filterProducts();
  });
});
searchInput.addEventListener('input', filterProducts);
sortSelect.addEventListener('change', sortProducts);

function filterProducts() {
  const activeCategory = document.querySelector('.filter-btn.active').dataset.category;
  const query = searchInput.value.toLowerCase();

  productCards.forEach(card => {
    const matchesCategory = (activeCategory === 'all' || card.dataset.category === activeCategory);
    const matchesSearch = (card.dataset.name.includes(query) || card.dataset.desc.includes(query));
    card.style.display = (matchesCategory && matchesSearch) ? 'block' : 'none';
  });
}

// Sorting
function sortProducts() {
  const sortBy = sortSelect.value;
  const grid = document.getElementById('productGrid');
  const visibleCards = productCards.filter(card => card.style.display !== 'none');

  visibleCards.sort((a, b) => {
    const priceA = parseFloat(a.dataset.price);
    const priceB = parseFloat(b.dataset.price);
    const nameA = a.dataset.name;
    const nameB = b.dataset.name;
    const dateA = new Date(a.dataset.created);
    const dateB = new Date(b.dataset.created);

    if (sortBy === 'low') return priceA - priceB;
    if (sortBy === 'high') return priceB - priceA;
    if (sortBy === 'az') return nameA.localeCompare(nameB);
    if (sortBy === 'newest') return dateB - dateA;
    return 0;
  });

  grid.innerHTML = '';
  visibleCards.forEach(card => grid.appendChild(card));
}

// Add to cart (AJAX)
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
      .then(data => {
        const toastMsg = document.getElementById('toastMsg');
        const cartToast = new bootstrap.Toast(document.getElementById('cartToast'));
        toastMsg.textContent = data.message;
        const toastEl = document.getElementById('cartToast');
        toastEl.classList.remove('bg-success', 'bg-info');
        toastEl.classList.add(data.status === 'success' ? 'bg-success' : 'bg-info');
        cartToast.show();
      })
      .catch(err => console.error(err));
  });
});
</script>
</body>
</html>
