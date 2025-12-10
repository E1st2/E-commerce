<?php include 'includes/header.php'; ?>

<main class="main">
    <div class="container">
        <div class="auth-container">
            <h2>Create Account</h2>
            <div id="register-message"></div>
            
            <form id="register-form" class="auth-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" id="is_student" name="is_student">
                        My discount (Get 5% discount on all products!)
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>
            
            <!-- Fixed link to use BASE_URL -->
            <p class="auth-link">Already have an account? <a href="/login.php">Login here</a></p>
        </div>
    </div>
</main>

<script>
const BASE_URL = '<?php echo ''; ?>';

document.getElementById('register-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        showMessage('Passwords do not match!', 'error');
        return;
    }
    
    const formData = new FormData(e.target);
    
    try {
        const response = await fetch(BASE_URL + '/api/register.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage('Registration successful! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = BASE_URL + '/login.php';
            }, 1500);
        } else {
            showMessage(data.message, 'error');
        }
    } catch (error) {
        console.error('Registration error:', error);
        showMessage('An error occurred. Please try again.', 'error');
    }
});

function showMessage(message, type) {
    const messageDiv = document.getElementById('register-message');
    messageDiv.textContent = message;
    messageDiv.className = `message ${type}`;
}
</script>

<?php include 'includes/footer.php'; ?>
