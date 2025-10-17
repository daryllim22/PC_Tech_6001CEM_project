<?php
session_start();
include 'connection.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_mail'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home Page - PC Tech</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .carousel-wrapper {
            max-width: 800px;
            margin: auto;
        }
        #carouselImage {
            height: 400px;
            object-fit: contain;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar d-flex justify-content-center gap-4 py-2 bg-light">
        <a href="home.php"><img src="images/logo.jpg" alt="PC Tech Logo" style="height: 60px; margin-bottom: 10px;"></a>
        <a href="home.php"><img src="images/home.png" alt="Home"></a>
        <a href="product.php"><img src="images/product.png" alt="Products"></a>
        <a href="profile.php"><img src="images/user_profile.png" alt="Profile"></a>
        <a href="logout.php"><img src="images/logout.png" alt="Logout"></a>
    </nav>

    <!-- Main Content -->
    <div class="content">
        <div class="announcement-banner text-center p-5">
            <h1>Welcome, <strong><?= htmlspecialchars($_SESSION['full_name']); ?></strong> 👋</h1>
            <p class="mt-3 fs-5">
                Welcome to PC Tech, your trusted destination for all things computing. We provide a 
                wide range of high-quality PCs, laptops, and accessories designed to meet the needs 
                of students, professionals, and everyday users. Our mission is to make technology 
                accessible, reliable, and efficient so you can focus on what matters most—whether 
                it’s work, study, or play.
            </p>
            <p class="fs-5">
                At PC Tech, we believe technology should enhance your lifestyle, not complicate it. 
                That’s why we offer products that combine performance, durability, and value. 
                From powerful desktops and portable laptops to essential PC accessories, we deliver 
                solutions that keep you connected, productive, and entertained every day.
            </p>

            <!-- Custom JavaScript Carousel -->
            <div class="carousel-wrapper my-4">
                <div id="jsCarousel" class="position-relative text-center">
                    <img id="carouselImage" src="" class="img-fluid rounded shadow" alt="Promotion Image">
                    <div class="mt-3">
                        <button onclick="prevImage()" class="btn btn-outline-primary me-2">⟨ Prev</button>
                        <button onclick="nextImage()" class="btn btn-outline-primary">Next ⟩</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Carousel Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const images = [
            'images/black-friday-sale.jpg',
            'images/cyber-monday-discount-sale.jpg'
        ];
        let currentIndex = 0;

        function showImage(index) {
            const img = document.getElementById('carouselImage');
            img.src = images[index];
        }

        function nextImage() {
            currentIndex = (currentIndex + 1) % images.length;
            showImage(currentIndex);
        }

        function prevImage() {
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            showImage(currentIndex);
        }

        // ✅ Automatically show the first image on page load
        window.onload = function() {
            showImage(currentIndex);
        };
    </script>
</body>
</html>
