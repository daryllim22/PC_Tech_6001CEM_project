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

    // Avoid duplicates
    $ids = array_column($_SESSION['cart'], 'id');
    if (!in_array($item['id'], $ids)) {
        $_SESSION['cart'][] = $item;
        echo json_encode(['status' => 'success', 'message' => 'Item added to cart!']);
    } else {
        echo json_encode(['status' => 'info', 'message' => 'Item already in cart.']);
    }
    exit;
}

// Fetch products from DB
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
    .content { max-width: 1000px; margin: 0 auto; }
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

    <div class="text-center mb-4">
      <a href="cart.php" class="btn btn-primary">
        <img src="images/cart.png" alt="Cart" style="height:24px; margin-right:6px;">View Cart
      </a>
    </div>

    <div class="row g-4 mb-4">
      <?php
      if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $id = $row['id'];
          $name = htmlspecialchars($row['name']);
          $desc = htmlspecialchars($row['description']);
          $short = mb_strimwidth($desc, 0, 80, '...');
          $price = number_format($row['price'], 2);
          $imgFile = htmlspecialchars($row['image']);
          $imgSrc = $imgFile ? 'uploads/' . $imgFile : 'images/placeholder.png';
          echo '
          <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
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

  <!-- Toast Container -->
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
            document.getElementById('cartToast').classList.toggle('bg-success', data.status === 'success');
            document.getElementById('cartToast').classList.toggle('bg-info', data.status === 'info');
            cartToast.show();
          })
          .catch(err => console.error(err));
      });
    });
  </script>
</body>
</html>
