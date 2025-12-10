<?php
require_once 'config.php';
require_once 'functions.php';

$current_user = get_logged_in_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Store</title>
    <link rel="stylesheet" href="/css/style.css">
    <script src="/js/cart.js"></script>
    <script src="/js/main.js" defer></script>
</head>
<body>
    <div id="chat-widget">
    <style>
        .chat-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            font-family: sans-serif;
            z-index: 999;
        }

        .chat-toggle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #3b82f6;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s;
        }

        .chat-toggle:hover {
            background-color: #2563eb;
        }

        .chat-box {
            position: absolute;
            bottom: 80px;
            right: 0;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            display: none;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            background-color: #3b82f6;
            color: white;
            padding: 1rem;
            text-align: center;
            font-weight: bold;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            background-color: #f9fafb;
        }

        .chat-message {
            margin-bottom: 1rem;
            padding: 0.75rem;
            border-radius: 8px;
            max-width: 80%;
        }

        .chat-message.user {
            background-color: #dbeafe;
            color: #1e40af;
            margin-left: auto;
            border-bottom-right-radius: 0;
        }

        .chat-message.bot {
            background-color: #e5e7eb;
            color: #374151;
            margin-right: auto;
            border-bottom-left-radius: 0;
        }

        .chat-message p {
            margin: 0;
            font-size: 0.95rem;
        }

        .chat-message small {
            display: block;
            font-size: 0.75rem;
            margin-top: 0.25rem;
            opacity: 0.7;
        }

        .chat-input-box {
            display: flex;
            gap: 0.5rem;
            padding: 1rem;
            background-color: white;
            border-top: 1px solid #e5e7eb;
        }

        .chat-input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 0.95rem;
        }

        .chat-send-btn {
            padding: 0.75rem 1rem;
            background-color: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .chat-send-btn:hover {
            background-color: #2563eb;
        }
    </style>

    <div class="chat-widget">
        <button class="chat-toggle">💬</button>
        <div class="chat-box">
            <div class="chat-header">Customer Support</div>
            <div class="chat-messages"></div>
            <div class="chat-input-box">
                <input type="text" class="chat-input" placeholder="Type a message...">
                <button class="chat-send-btn">Send</button>
            </div>
        </div>
    </div>
</div>

    <header class="header">
        <div class="container">
            <div class="header-content">
                <h1 class="logo"><a href="/index.php">X-Women Wide World</a></h1>
                <nav class="nav">
                    <a href="/index.php">Home</a>
                    <?php if ($current_user): ?>
                        <a href="profile.php">Profile</a>
                        <a href="/cart.php">Cart</a>
                        <a href="/api/logout.php">Logout</a>
                        <span class="user-badge">
                            <?php echo htmlspecialchars($current_user['username']); ?>
                        </span>
                    <?php else: ?>
                        <a href="/login.php">Login</a>
                        <a href="/register.php">Register</a>
                    <?php endif; ?>
                    <a href="/cart.php" class="cart-btn">
                        Cart (<span id="cart-count">0</span>)
                    </a>
                </nav>
            </div>
        </div>
    </header>
