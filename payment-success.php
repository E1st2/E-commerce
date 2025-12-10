<?php 
include 'includes/header.php';

$current_user = null;
$order_items = [];
$order_total = 0;
$order_discount = 0;

if (is_logged_in()) {
    $current_user = get_logged_in_user();
    
    // Get the last order from session
    if (isset($_SESSION['lastOrderId'])) {
        $order_id = $_SESSION['lastOrderId'];
        
        // Fetch order details
        $order_query = "SELECT o.*, 
                        (SELECT SUM(oi.price * oi.quantity) FROM order_items oi WHERE oi.order_id = o.id) as subtotal
                        FROM orders o 
                        WHERE o.id = ? AND o.user_id = ?";
        $stmt = $conn->prepare($order_query);
        $stmt->bind_param("ii", $order_id, $current_user['id']);
        $stmt->execute();
        $order_result = $stmt->get_result();
        $order = $order_result->fetch_assoc();
        
        if ($order) {
            $order_total = $order['total_amount'];
            $order_discount = $order['discount_applied'];
            
            // Fetch order items
            $items_query = "SELECT oi.*, p.name, p.image_url 
                           FROM order_items oi 
                           JOIN products p ON oi.product_id = p.id 
                           WHERE oi.order_id = ?";
            $stmt = $conn->prepare($items_query);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $items_result = $stmt->get_result();
            
            while ($item = $items_result->fetch_assoc()) {
                $order_items[] = $item;
            }
        }
        
        // Clear the session variable
        unset($_SESSION['lastOrderId']);
    }
}
?>

<main class="main">
    <div class="container">
        <div class="success-page">
            <div class="success-card">
                <div class="success-icon">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="#10b981" stroke-width="2"/>
                        <path d="M8 12L11 15L16 9" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h2 class="success-title">SUCCESSFUL payment validated</h2>
                <p class="success-message">
                    <?php if (is_logged_in()): ?>
                        Your order has been placed successfully! You can view all your orders in your profile.
                    <?php else: ?>
                        Thank you for your purchase! Your order has been completed.
                    <?php endif; ?>
                </p>
                
                <div id="order-items-container"></div>
                
                <div class="success-actions">
                    <?php if (is_logged_in()): ?>
                        <a href="/profile.php" class="btn btn-primary">View Order History</a>
                    <?php endif; ?>
                    <a href="/index.php" class="btn btn-secondary">Continue Shopping</a>
                </div>
            </div>
        </div>
    </div>
</main>

<script type="text/babel">
const BASE_URL = '<?php echo ''; ?>';
const isLoggedIn = <?php echo is_logged_in() ? 'true' : 'false'; ?>;

const OrderItemsDisplay = () => {
    const [items, setItems] = React.useState([]);
    const [total, setTotal] = React.useState(0);
    const [discount, setDiscount] = React.useState(0);
    
    React.useEffect(() => {
        if (isLoggedIn) {
            // For logged-in users, get items from PHP
            const orderItems = <?php echo json_encode($order_items); ?>;
            const orderTotal = <?php echo $order_total; ?>;
            const orderDiscount = <?php echo $order_discount; ?>;
            
            setItems(orderItems);
            setTotal(orderTotal);
            setDiscount(orderDiscount);
        } else {
            // For guest users, get items from sessionStorage
            const guestOrder = sessionStorage.getItem('guestOrder');
            if (guestOrder) {
                const orderItems = JSON.parse(guestOrder);
                setItems(orderItems);
                
                const subtotal = orderItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                setTotal(subtotal);
                
                // Clear guest order from sessionStorage when component unmounts
                return () => {
                    sessionStorage.removeItem('guestOrder');
                };
            }
        }
    }, []);
    
    if (items.length === 0) {
        return null;
    }
    
    const subtotal = isLoggedIn ? total + discount : total;
    
    return React.createElement('div', { className: 'order-summary-section' }, [
        React.createElement('h3', {}, 'Order Details'),
        React.createElement('div', { className: 'order-items-list' },
            items.map((item, index) =>
                React.createElement('div', { key: index, className: 'order-item-row' }, [
                    React.createElement('img', {
                        src: isLoggedIn ? item.image_url : item.image,
                        alt: item.name,
                        onError: (e) => e.target.src = BASE_URL + '/placeholder.svg?height=60&width=60'
                    }),
                    React.createElement('div', { className: 'item-details' }, [
                        React.createElement('h4', {}, item.name),
                        React.createElement('p', {}, `Quantity: ${item.quantity}`)
                    ]),
                    React.createElement('p', { className: 'item-price' },
                        `${(item.price * item.quantity).toFixed(0)} XAF`
                    )
                ])
            )
        ),
        React.createElement('div', { className: 'order-total-section' }, [
            React.createElement('div', { className: 'total-row' }, [
                React.createElement('span', {}, 'Subtotal:'),
                React.createElement('span', {}, `${subtotal.toFixed(0)} XAF`)
            ]),
            discount > 0 && React.createElement('div', { className: 'total-row discount-row' }, [
                React.createElement('span', {}, 'Discount (5%):'),
                React.createElement('span', {}, `-${discount.toFixed(0)} XAF`)
            ]),
            React.createElement('div', { className: 'total-row final-total' }, [
                React.createElement('strong', {}, 'Total Paid:'),
                React.createElement('strong', {}, `${total.toFixed(0)} XAF`)
            ])
        ])
    ]);
};

const container = document.getElementById('order-items-container');
const root = ReactDOM.createRoot(container);
root.render(React.createElement(OrderItemsDisplay));
</script>

<?php include 'includes/footer.php'; ?>
