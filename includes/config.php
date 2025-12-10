<?php
// Database configuration
define('DB_HOST', 'sql311.infinityfree.com');
define('DB_USER', 'if0_39667864');
define('DB_PASS', 'E1st2evabcde');
define('DB_NAME', 'if0_39667864_acommerce_db');

// Student discount percentage
define('STUDENT_DISCOUNT', 0.05); // 5%

// Set this to your folder name if app is in a subdirectory
// Example: If your app is at localhost/ecommerce/, set to '/ecommerce'
// If at root (localhost/), leave as ''
define('/ecommerce', '');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
