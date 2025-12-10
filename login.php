<?php include 'includes/header.php'; ?>

<main class="main">
    <div class="container">
        <div class="auth-container">
            <h2>Login</h2>
            <div id="login-message"></div>
            
            <form id="login-form" class="auth-form">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            
            <!-- Fixed link to use BASE_URL -->
            <p class="auth-link">Don't have an account? <a href="/register.php">Register here</a></p>
        </div>
    </div>
</main>

<script>
const BASE_URL = '';

document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch(BASE_URL + '/api/login.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage('Login successful! Redirecting...', 'success');
            const urlParams = new URLSearchParams(window.location.search);
            const redirect = urlParams.get('redirect') || '/index.php';
            setTimeout(() => {
                window.location.href = BASE_URL + redirect;
            }, 1000);
        } else {
            showMessage(data.message, 'error');
        }
    } catch (error) {
        console.error('Login error:', error);
        showMessage('An error occurred. Please try again.', 'error');
    }
});

function showMessage(message, type) {
    const messageDiv = document.getElementById('login-message');
    messageDiv.textContent = message;
    messageDiv.className = `message ${type}`;
}
</script>

<?php include 'includes/footer.php'; ?>
