<?php 
include 'includes/header.php';

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header('Location: ' . '/index.php');
    exit;
}

$display_price = $current_user 
    ? calculate_price($product['price'], true) 
    : $product['price'];

$image_path = $product['image_url'];
if (!str_starts_with($image_path, 'http')) {
    $image_path = '' . $image_path;
}
?>

<main class="main">
    <div class="container">
        <div class="product-detail">
            <div class="product-image-large">
                <img src="images/<?php echo htmlspecialchars($image_path); ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                     onerror="this.onerror=null"; onerror="this.src='/images/placeholder.jpg'">
            </div>
            
            <div class="product-detail-info">
                <h2><?php echo htmlspecialchars($product['name']); ?></h2>
                <p class="category-tag"><?php echo htmlspecialchars($product['category_name']); ?></p>
                
                <div class="price-section-large">
                    <?php if ($current_user): ?>
                        <span class="original-price-large"><?php echo format_currency($product['price']); ?></span>
                        <span class="discounted-price-large"><?php echo format_currency($display_price); ?></span>
                        <span class="discount-badge">Account Discount Applied!</span>
                    <?php else: ?>
                        <span class="price-large"><?php echo format_currency($product['price']); ?></span>
                    <?php endif; ?>
                </div>
                
                <p class="product-description-full"><?php echo htmlspecialchars($product['description']); ?></p>
                
                <p class="stock-info">
                    <?php if ($product['stock'] > 0): ?>
                        <span class="in-stock">In Stock: <?php echo $product['stock']; ?> available</span>
                    <?php else: ?>
                        <span class="out-of-stock">Out of Stock</span>
                    <?php endif; ?>
                </p>
                
                <?php if ($product['stock'] > 0): ?>
                    <!-- Pass image path with BASE_URL to addToCart -->
                    <button 
                        class="btn btn-primary btn-large" 
                        onclick='addToCart(<?php echo json_encode([
                            "id" => $product["id"],
                            "name" => $product["name"],
                            "price" => (float)$product["price"],
                            "image" => $image_path
                        ]); ?>)'>
                        Add to Cart
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <section class="related-products">
            <h3>Related Products</h3>
            <div class="products-grid">
                <?php
                $related_query = "SELECT * FROM products 
                                 WHERE category_id = ? AND id != ? AND stock > 0 
                                 LIMIT 4";
                $stmt = $conn->prepare($related_query);
                $stmt->bind_param("ii", $product['category_id'], $product_id);
                $stmt->execute();
                $related_result = $stmt->get_result();
                
                while ($related = $related_result->fetch_assoc()):
                    $related_price = $current_user 
                        ? calculate_price($related['price'], true) 
                        : $related['price'];
                    $related_image = $related['image_url'];
                    if (!str_starts_with($related_image, 'http')) {
                        $related_image = '' . $related_image;
                    }
                ?>
                    <div class="product-card">
                        <img src="images/<?php echo htmlspecialchars($related_image); ?>" 
                             alt="<?php echo htmlspecialchars($related['name']); ?>"
                             onerror="this.onerror=null; onerror="this.src='/images/placeholder.jpg'">
                        <div class="product-info">
                            <h4><?php echo htmlspecialchars($related['name']); ?></h4>
                            <p class="price"><?php echo format_currency($related_price); ?></p>
                            <div class="product-actions">
                                <a href="/product.php?id=<?php echo $related['id']; ?>" class="btn btn-secondary">
                                    View Details
                                </a>
                                <!-- Pass image path with BASE_URL to addToCart -->
                                <button 
                                    class="btn btn-primary" 
                                    onclick='addToCart(<?php echo json_encode([
                                        "id" => $related["id"],
                                        "name" => $related["name"],
                                        "price" => (float)$related["price"],
                                        "image" => $related_image
                                    ]); ?>)'>
                                    Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
