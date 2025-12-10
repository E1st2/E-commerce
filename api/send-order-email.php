<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// This script sends order confirmation emails to logged-in users
// Note: In production, use a proper email service like SendGrid, Mailgun, or AWS SES

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$order_id = $input['order_id'] ?? null;

if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'Order ID required']);
    exit;
}

// Get order details
$order_query = "SELECT o.*, u.email, u.username 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

// Get order items
$items_query = "SELECT oi.*, p.name, p.image_url 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Build email content
$email_subject = "Order Confirmation #" . $order_id . " - ShopHub";

$email_body = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .order-details { background-color: white; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .item { border-bottom: 1px solid #eee; padding: 10px 0; }
        .item:last-child { border-bottom: none; }
        .total { font-size: 18px; font-weight: bold; margin-top: 20px; padding-top: 20px; border-top: 2px solid #007bff; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>Order Confirmation</h1>
        </div>
        <div class='content'>
            <p>Hello " . htmlspecialchars($order['username']) . ",</p>
            <p>Thank you for your order! Your payment has been successfully validated.</p>
            
            <div class='order-details'>
                <h2>Order #" . $order_id . "</h2>
                <p><strong>Order Date:</strong> " . date('F j, Y, g:i a', strtotime($order['created_at'])) . "</p>
                
                <h3>Items Ordered:</h3>";

foreach ($items as $item) {
    $item_total = $item['price'] * $item['quantity'];
    $email_body .= "
                <div class='item'>
                    <strong>" . htmlspecialchars($item['name']) . "</strong><br>
                    Quantity: " . $item['quantity'] . " × " . format_currency($item['price']) . " = " . format_currency($item_total) . "
                </div>";
}

$subtotal = $order['total_amount'] + $order['discount_applied'];
$email_body .= "
                <div class='total'>
                    <p>Subtotal: " . format_currency($subtotal) . "</p>
                    <p style='color: #28a745;'>Account Discount (5%): -" . format_currency($order['discount_applied']) . "</p>
                    <p style='font-size: 20px;'>Total Paid: " . format_currency($order['total_amount']) . "</p>
                </div>
            </div>
            
            <p>Your order is being processed and will be shipped soon.</p>
            <p>If you have any questions, please don't hesitate to contact us.</p>
        </div>
        <div class='footer'>
            <p>© 2025 ShopHub. All rights reserved.</p>
            <p>This is an automated email. Please do not reply to this message.</p>
        </div>
    </div>
</body>
</html>
";

// Send email using PHP mail() function
// Note: For production, replace this with a proper email service
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: ShopHub <noreply@shophub.com>" . "\r\n";

$email_sent = mail($order['email'], $email_subject, $email_body, $headers);

if ($email_sent) {
    echo json_encode(['success' => true, 'message' => 'Email sent successfully']);
} else {
    // Log error but don't fail the order
    error_log("Failed to send order confirmation email for order #" . $order_id);
    echo json_encode(['success' => false, 'message' => 'Failed to send email']);
}
?>
