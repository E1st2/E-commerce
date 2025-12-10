<?php 
include 'includes/header.php';

$current_user = null;

if (is_logged_in()) {
    $current_user = get_logged_in_user();
}
?>

<main class="main">
    <div class="container">
        <div class="cart-page">
            
            <h2>Shopping Cart</h2>

            <!-- Message selon l'utilisateur connecté ou pas -->
            <?php if (!is_logged_in()): ?>
                <div class="guest-notice">
                    <p>
                        ⚠️ Create an account and get 5% discount on all your purchases! 
                        <a href="/register.php">Register now</a> or
                        <a href="/login.php?redirect=/cart.php">Login</a> to save!
                    </p>
                </div>
            <?php else: ?>
                <div class="guest-notice" style="background-color: #d1fae5; border-color: #10b981;">
                    <p style="color: #065f46;">
                        ✓ You're getting 5% discount on all items as an account holder!
                    </p>
                </div>
            <?php endif; ?>

            <!-- Clear Cart Button -->
            <div style="margin-bottom: 1rem;">
                <button onclick="clearCart()" 
                        class="btn btn-secondary" 
                        style="background-color: #ef4444;">
                    Clear Cart
                </button>
            </div>

            <!-- Cart Content -->
            <div id="cart-items-container"></div>
            <div id="cart-summary"></div>

        </div>
    </div>
</main>

<script>
    // Base URL automatique (évite les bugs)
    const siteBaseUrl = window.location.origin;

    window.cartPageConfig = {
        baseUrl: siteBaseUrl,
        baseUrli: siteBaseUrl + "/images/",
        isLoggedIn: <?php echo is_logged_in() ? 'true' : 'false'; ?>
    };

    // Charger le panier quand la page est prête
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", () => {
            if (typeof window.loadCartPage === "function") {
                window.loadCartPage();
            }
        });
    } else {
        if (typeof window.loadCartPage === "function") {
            window.loadCartPage();
        }
    }
</script>

<?php include 'includes/footer.php'; ?>
