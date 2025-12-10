<?php
// Sanitize input data
function sanitize_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function get_logged_in_user() {
    global $conn;
    if (!is_logged_in()) {
        return null;
    }
    
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Calculate price with account discount
function calculate_price($price, $has_account = false) {
    if ($has_account) {
        return $price * (1 - STUDENT_DISCOUNT);
    }
    return $price;
}

// Format currency
function format_currency($amount) {
    return number_format($amount, 0) . ' XAF';
}

// Get product suggestions based on purchase history
function get_product_suggestions($user_id, $limit = 4) {
    global $conn;
    
    // Get categories from user's purchase history
    $query = "SELECT DISTINCT p.category_id 
              FROM order_items oi 
              JOIN orders o ON oi.order_id = o.id 
              JOIN products p ON oi.product_id = p.id 
              WHERE o.user_id = ? 
              LIMIT 3";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category_id'];
    }
    
    if (empty($categories)) {
        // If no purchase history, return random products
        $query = "SELECT * FROM products ORDER BY RAND() LIMIT ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $limit);
    } else {
        // Get products from same categories
        $placeholders = implode(',', array_fill(0, count($categories), '?'));
        $query = "SELECT * FROM products 
                  WHERE category_id IN ($placeholders) 
                  ORDER BY RAND() 
                  LIMIT ?";
        $stmt = $conn->prepare($query);
        
        $types = str_repeat('i', count($categories)) . 'i';
        $params = array_merge($categories, [$limit]);
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    return $stmt->get_result();
}
?>
