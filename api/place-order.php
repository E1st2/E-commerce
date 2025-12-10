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

$discount = $total * 0.05;
$final_total = $total - $discount;

// Start transaction
$conn->begin_transaction();

try {
    // Insert order
    $order_query = "INSERT INTO orders (user_id, total_amount, discount_applied, status) 
    VALUES (?, ?, ?, 'completed')";
    $stmt = $conn->prepare($order_query);
    
    if (!$stmt) {
        throw new Exception("Database error");
    }
    
    $stmt->bind_param("idd", $user_id, $final_total, $discount);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create order");
    }
    
    $order_id = $conn->insert_id;
    $_SESSION['lastOrderId'] = $order_id;
    
    // Insert order items
    $item_query = "INSERT INTO order_items (order_id, product_id, quantity, price)
     VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($item_query);
    
    if (!$stmt) {
        throw new Exception("Database error");
    }
    
    foreach ($items as $item) {
        $stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to add items to order");
        }
        
        // Update stock
        $update_stock = "UPDATE products SET stock = stock - ? WHERE id = ?";
        $stock_stmt = $conn->prepare($update_stock);
        
        if ($stock_stmt) {
            $stock_stmt->bind_param("ii", $item['quantity'], $item['id']);
            $stock_stmt->execute();
        }
    }
    
    $conn->commit();
    
    echo json_encode(['success' => true, 'order_id' => $order_id]);
    
    // Only attempt if function exists to close connection
    if (function_exists('fastcgi_finish_request')) {
        fastcgi_finish_request(); // Close connection to client
        
        // Now try to send email in background (client already got success response)
        try {
            $emailData = json_encode(['order_id' => $order_id]);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://" . $_SERVER['HTTP_HOST'] . "/api/send-order-email.php");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $emailData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_exec($ch);
            curl_close($ch);
        } catch (Exception $e) {
            // Email failed but order succeeded - just log it
            error_log("Email sending failed for order #" . $order_id);
        }
    }
    
} catch (Exception $e) {
    $conn->rollback();
    error_log("Order placement error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to place order. Please try again.'
    ]);
}
?>
