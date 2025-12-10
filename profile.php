<?php 
include 'includes/header.php';

if (!is_logged_in()) {
    header('Location: /login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Get user's order history
$orders_query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();
?>

<main class="main">
    <div class="container">
        <div class="profile-container">
            <h2>My Profile</h2>
            
            <div class="profile-info">
                <div class="info-card">
                    <h3>Account Information</h3>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($current_user['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($current_user['email']); ?></p>
                    <p><strong>Full Name:</strong> <?php echo htmlspecialchars($current_user['full_name']); ?></p>
                    <p><strong>Account Type:</strong> 
                        <?php if ($current_user['is_student']): ?>
                            <span class="student-badge"> Account (5% Discount)</span>
                        <?php else: ?>
                            Regular Account
                        <?php endif; ?>
                    </p>
                    <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($current_user['created_at'])); ?></p>
                </div>
            </div>
            
            <div class="order-history">
                <h3>Order History</h3>
                <?php if ($orders_result->num_rows > 0): ?>
                    <div class="orders-list">
                        <?php while ($order = $orders_result->fetch_assoc()): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <span class="order-id">Order #<?php echo $order['id']; ?></span>
                                    <span class="order-date"><?php echo date('M j, Y', strtotime($order['created_at'])); ?></span>
                                    <span class="order-status status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                                <div class="order-details">
                                    <?php
                                    $items_query = "SELECT oi.*, p.name, p.image_url 
                                                   FROM order_items oi 
                                                   JOIN products p ON oi.product_id = p.id 
                                                   WHERE oi.order_id = ?";
                                    $items_stmt = $conn->prepare($items_query);
                                    $items_stmt->bind_param("i", $order['id']);
                                    $items_stmt->execute();
                                    $items_result = $items_stmt->get_result();
                                    ?>
                                    <div class="order-items">
                                        <?php while ($item = $items_result->fetch_assoc()): ?>
                                            <div class="order-item">
                                                <img src="/images/<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                                                <div class="item-info">
                                                    <p class="item-name"><?php echo htmlspecialchars($item['name']); ?></p>
                                                    <p class="item-quantity">Qty: <?php echo $item['quantity']; ?></p>
                                                    <p class="item-price"><?php echo format_currency($item['price']); ?></p>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                    <div class="order-total">
                                        <?php if ($order['discount_applied'] > 0): ?>
                                            <p>Discount: -<?php echo format_currency($order['discount_applied']); ?></p>
                                        <?php endif; ?>
                                        <p class="total"><strong>Total: <?php echo format_currency($order['total_amount']); ?></strong></p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="no-orders">No orders yet. <a href="/index.php">Start shopping!</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
