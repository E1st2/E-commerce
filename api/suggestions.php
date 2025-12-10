<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : $_SESSION['user_id'];
$current_user = get_logged_in_user();

$suggestions = get_product_suggestions($user_id, 4);

$products = [];
while ($product = $suggestions->fetch_assoc()) {
    $price = $current_user['is_student'] 
        ? calculate_price($product['price'], true) 
        : $product['price'];
    
    $products[] = [
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => $price,
        'image_url' => $product['image_url']
    ];
}

echo json_encode(['success' => true, 'products' => $products]);
?>
