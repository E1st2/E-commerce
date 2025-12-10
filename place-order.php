<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$current_user = get_logged_in_user();

$input = json_decode(file_get_contents('php://input'), true);
$items = $input['items'] ?? [];

if (empty($items)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

// Calculate total
$total = 0;
foreach ($items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Calculate discount
$discount = $current_user['is_student'] ? $total * STUDENT_DISCOUNT : 0;
$final_total = $total - $discount;

// Start transaction
$conn->begin_transaction();

try {
    // Insert order
    $order_query = "INSERT INTO orders (user_id, total_amount, discount_applied, status) VALUES (?, ?, ?, 'pending')";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("idd", $user_id, $final_total, $discount);
    $stmt->execute();
    $order_id = $conn->insert_id;
    
    // Insert order items
    $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($item_query);
    
    foreach ($items as $item) {
        $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
        $stmt->execute();
        
        // Update stock
        $update_stock = "UPDATE products SET stock = stock - ? WHERE id = ?";
        $stock_stmt = $conn->prepare($update_stock);
        $stock_stmt->bind_param("ii", $item['quantity'], $item['id']);
        $stock_stmt->execute();
    }
    
    $conn->commit();
    echo json_encode(['success' => true, 'order_id' => $order_id]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Failed to place order']);
}
?>
