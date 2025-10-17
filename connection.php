<?php
// Database connection settings
$servername = "localhost";   // XAMPP default
$username   = "root";        // XAMPP default user
$password   = "";            // XAMPP default has no password
$dbname     = "pc_shop";     // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
