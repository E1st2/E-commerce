# E-Commerce Application

A complete e-commerce web application built with PHP, MySQL, HTML, CSS, and JavaScript with React components.

## Features

- 🛍️ Product catalog with search and category filtering
- 🛒 Shopping cart functionality
- 👤 User authentication (registration and login)
- 🎓 5% student discount for student accounts
- 📦 Order history and tracking
- 💡 Personalized product suggestions
- 🔗 Related products on detail pages

## Prerequisites

Before you begin, ensure you have the following installed:

- **XAMPP** (includes Apache, MySQL, and PHP)
  - Download from: https://www.apachefriends.org/
  - Version 7.4 or higher recommended

## Installation Steps

### 1. Install XAMPP

1. Download and install XAMPP from the official website
2. Install it in the default location (usually `C:\xampp` on Windows)

### 2. Set Up the Database

1. Start XAMPP Control Panel
2. Start **Apache** and **MySQL** services
3. Open your browser and go to: `http://localhost/phpmyadmin`
4. Click on "New" in the left sidebar to create a new database
5. Name it: `ecommerce_db`
6. Click "Create"
7. Select the `ecommerce_db` database
8. Click on the "SQL" tab
9. Open the file `scripts/database-schema.sql` from this project
10. Copy all the SQL code and paste it into the SQL tab
11. Click "Go" to execute the script

### 3. Configure Database Connection

1. Open the file `includes/config.php`
2. Update the database credentials if needed (default values should work):
   \`\`\`php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'ecommerce_db');
   \`\`\`

### 4. Install the Application Files

1. Copy all project files to your XAMPP `htdocs` folder
   - Windows: `C:\xampp\htdocs\ecommerce`
   - Mac/Linux: `/Applications/XAMPP/htdocs/ecommerce`
2. Make sure the folder structure looks like this:
   \`\`\`
   htdocs/ecommerce/
   ├── api/
   ├── css/
   ├── images/
   ├── includes/
   ├── js/
   ├── scripts/
   ├── index.php
   ├── product.php
   ├── cart.php
   ├── login.php
   ├── register.php
   ├── profile.php
   └── README.md
   \`\`\`

### 5. Launch the Application

1. Make sure Apache and MySQL are running in XAMPP Control Panel
2. Open your web browser
3. Go to: `http://localhost/ecommerce`
4. You should see the home page with products!

## Using the Application

### Creating an Account

1. Click "Register" in the navigation menu
2. Fill in your details:
   - Name
   - Email
   - Password
   - Check "Student Account" for 5% discount on all purchases
3. Click "Register"

### Logging In

1. Click "Login" in the navigation menu
2. Enter your email and password
3. Click "Login"

### Shopping

1. Browse products on the home page
2. Use the search bar to find specific products
3. Filter by category using the dropdown
4. Click on a product to see details
5. Click "Add to Cart" to add items
6. Click the "Cart" button to view your cart
7. Click "Place Order" to complete your purchase

### Viewing Orders

1. Log in to your account
2. Click "Profile" in the navigation menu
3. Scroll down to see your order history
4. View personalized product suggestions based on your purchases

## Troubleshooting

### "Cannot connect to database" error
- Make sure MySQL is running in XAMPP Control Panel
- Check that the database name is `ecommerce_db`
- Verify credentials in `includes/config.php`

### "Cannot redeclare function" error
- This has been fixed - the function is now named `get_logged_in_user()`
- If you still see this, make sure you have the latest version of the files

### Pages not loading or showing blank
- Check that Apache is running in XAMPP Control Panel
- Make sure you're accessing via `http://localhost/ecommerce` not just opening the file
- Check Apache error logs in XAMPP Control Panel

### Images not showing
- Make sure the `images/` folder exists in your project
- Add product images to the `images/` folder
- Images should be referenced in the database with paths like `images/product1.jpg`

### Student discount not applying
- Make sure you checked "Student Account" during registration
- The discount is automatically applied at checkout
- You'll see the discounted price in your cart

## Default Test Data

The database schema includes sample products. You can:
- Add more products via phpMyAdmin in the `products` table
- Upload product images to the `images/` folder
- Create test user accounts through the registration page

## File Structure

- **api/** - API endpoints for cart, orders, and suggestions
- **css/** - Stylesheets
- **images/** - Product images
- **includes/** - PHP configuration and shared components (header, footer, functions)
- **js/** - JavaScript files for cart and interactivity
- **scripts/** - Database schema and setup scripts
- **index.php** - Home page with product listing
- **product.php** - Product detail page
- **cart.php** - Shopping cart page
- **login.php** - User login page
- **register.php** - User registration page
- **profile.php** - User profile and order history

## Technologies Used

- **Backend:** PHP 7.4+ with mysqli
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript
- **Framework:** React.js (for cart components)
- **Server:** Apache (via XAMPP)

## Support

If you encounter any issues:
1. Check the Troubleshooting section above
2. Verify all installation steps were completed
3. Check XAMPP error logs for detailed error messages
4. Ensure all files are in the correct locations

## License

This project is for educational purposes.
