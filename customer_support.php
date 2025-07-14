<?php
session_start();
include 'db_connect.php';

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $query = trim($_POST['message']);
    
    if ($name && $email && $query) {
        $stmt = $conn->prepare("INSERT INTO support_queries (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $query);
        
        if ($stmt->execute()) {
            $message = "Your message has been submitted. We'll get back to you soon!";
        } else {
            $message = "Something went wrong. Please try again.";
        }
    } else {
        $message = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Support - FreightX</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #3498db, #2c3e50);
            color: #fff;
            min-height: 100vh;
        }
        
        .navbar {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 15px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #ffcc00;
            text-decoration: none;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-left: 20px;
        }
        
        .nav-links a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s;
        }
        
        .nav-links a:hover, .nav-links a.active {
            color: #ffcc00;
        }
        
        .auth-buttons a {
            margin-left: 15px;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .login-btn {
            background-color: transparent;
            border: 2px solid #ffcc00;
            color: #ffcc00;
        }
        
        .login-btn:hover {
            background-color: #ffcc00;
            color: #000;
        }
        
        .register-btn {
            background-color: #ffcc00;
            color: #000;
        }
        
        .register-btn:hover {
            background-color: #e6b800;
        }
        
        .support-header {
            text-align: center;
            padding: 120px 0 40px;
        }
        
        .support-header h1 {
            font-size: 42px;
            margin-bottom: 15px;
            color: #ffcc00;
        }
        
        .support-header p {
            font-size: 18px;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .support-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 40px;
            padding: 40px 20px 80px;
        }
        
        .contact-info {
            background: rgba(0, 0, 0, 0.5);
            padding: 30px;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .contact-info h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #ffcc00;
            text-align: center;
        }
        
        .contact-method {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .contact-method i {
            font-size: 24px;
            color: #ffcc00;
            margin-right: 15px;
            width: 40px;
            text-align: center;
        }
        
        .contact-method .details {
            flex: 1;
        }
        
        .contact-method h3 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .contact-method p {
            color: #ddd;
        }
        
        .query-form {
            background: rgba(0, 0, 0, 0.5);
            padding: 30px;
            border-radius: 10px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }
        
        .query-form h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #ffcc00;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            font-size: 16px;
        }
        
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        button {
            background: #ffcc00;
            color: #000;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            display: block;
            width: 100%;
        }
        
        button:hover {
            background: #e6b800;
        }
        
        .msg {
            margin-top: 15px;
            padding: 15px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            background: rgba(0, 0, 0, 0.5);
        }
        
        .success-msg {
            color: #32cd32;
        }
        
        .error-msg {
            color: #ff6b6b;
        }
        
        /* Chat Widget Styles */
        .chat-widget {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
        
        .chat-button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #ffcc00;
            display: flex;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            transition: all 0.3s;
        }
        
        .chat-button:hover {
            transform: scale(1.1);
            background: #e6b800;
        }
        
        .chat-button i {
            font-size: 24px;
            color: #000;
        }
        
        .chat-container {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 350px;
            height: 450px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            display: none;
            flex-direction: column;
            z-index: 1001;
        }
        
        .chat-header {
            background: #ffcc00;
            color: #000;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chat-header h3 {
            margin: 0;
            font-size: 18px;
        }
        
        .close-chat {
            cursor: pointer;
            font-size: 20px;
        }
        
        .chat-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            background: #f5f5f5;
        }
        
        .message {
            margin-bottom: 15px;
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 15px;
            position: relative;
            clear: both;
        }
        
        .user-message {
            background: #3498db;
            color: #fff;
            float: right;
            border-bottom-right-radius: 0;
        }
        
        .agent-message {
            background: #e5e5ea;
            color: #000;
            float: left;
            border-bottom-left-radius: 0;
        }
        
        .chat-input {
            padding: 15px;
            background: #fff;
            border-top: 1px solid #ddd;
            display: flex;
        }
        
        .chat-input input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 20px;
            margin-right: 10px;
        }
        
        .chat-input button {
            background: #ffcc00;
            color: #000;
            border: none;
            border-radius: 20px;
            padding: 10px 15px;
            cursor: pointer;
            width: auto;
        }
        
        footer {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 40px 0 20px;
            text-align: center;
        }
        
        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .footer-section {
            flex: 1;
            min-width: 250px;
            margin-bottom: 20px;
            padding: 0 20px;
        }
        
        .footer-section h3 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #ffcc00;
        }
        
        .footer-section p, .footer-section a {
            color: #ddd;
            margin-bottom: 10px;
            display: block;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-section a:hover {
            color: #ffcc00;
        }
        
        .footer-bottom {
            border-top: 1px solid #444;
            padding-top: 20px;
            color: #ddd;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .chat-container {
                width: 300px;
                height: 400px;
                right: 10px;
                bottom: 80px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-content">
                <a href="index.php" class="logo"><i class="fas fa-train"></i> FreightX</a>
                
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="contact_support.php">Contact</a></li>
                    <li><a href="track_shipment.php">Track Shipment</a></li>
                </ul>
                
                <div class="auth-buttons">
                    <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                        <a href="user_dashboard.php" class="login-btn">Dashboard</a>
                        <a href="user_logout.php" class="register-btn">Logout</a>
                    <?php else: ?>
                        <a href="user_login.php" class="login-btn">Login</a>
                        <a href="user_register.php" class="register-btn">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Support Header -->
    <div class="support-header">
        <h1>Customer Support</h1>
        <p>We're here to help! If you have any questions or concerns, please reach out to us using one of the methods below.</p>
    </div>

    <!-- Support Container -->
    <div class="container support-container">
        <!-- Contact Information -->
        <div class="contact-info">
            <h2>Contact Information</h2>
            
            <div class="contact-method">
                <i class="fas fa-phone-alt"></i>
                <div class="details">
                    <h3>Phone Support</h3>
                    <p>+91 1234567890</p>
                    <p>Monday to Saturday, 9am to 6pm</p>
                </div>
            </div>
            
            <div class="contact-method">
                <i class="fas fa-envelope"></i>
                <div class="details">
                    <h3>Email Support</h3>
                    <p>support@freightx.com</p>
                    <p>We'll respond within 24 hours</p>
                </div>
            </div>
            
            <div class="contact-method">
                <i class="fas fa-map-marker-alt"></i>
                <div class="details">
                    <h3>Head Office</h3>
                    <p>123 Transport Plaza, Railway Colony</p>
                    <p>New Delhi, India - 110001</p>
                </div>
            </div>
            
            <div class="contact-method">
                <i class="fas fa-comments"></i>
                <div class="details">
                    <h3>Live Chat</h3>
                    <p>Click the chat icon in the bottom right</p>
                    <p>Available 24/7 for urgent issues</p>
                </div>
            </div>
        </div>
        
        <!-- Query Form -->
        <div class="query-form">
            <h2>Send Us a Message</h2>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Your Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="message">Your Message</label>
                    <textarea id="message" name="message" rows="6" required></textarea>
                </div>
                
                <button type="submit">Submit Message</button>
            </form>
            
            <?php if ($message): ?>
                <div class="msg <?php echo strpos($message, 'submitted') !== false ? 'success-msg' : 'error-msg'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Chat Widget (for future Socket.io implementation) -->
    <div class="chat-widget">
        <div class="chat-button" id="chatButton">
            <i class="fas fa-comments"></i>
        </div>
    </div>
    
    <div class="chat-container" id="chatContainer">
        <div class="chat-header">
            <h3><i class="fas fa-headset"></i> FreightX Support</h3>
            <span class="close-chat" id="closeChat">&times;</span>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <div class="message agent-message">
                Hello! How can I help you today?
            </div>
            <!-- Messages will be dynamically added here with Socket.io -->
        </div>
        
        <div class="chat-input">
            <input type="text" id="messageInput" placeholder="Type your message...">
            <button id="sendMessage">Send</button>
        </div>
    </div>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>FreightX</h3>
                    <p>Your trusted partner in freight transportation and logistics solutions across India.</p>
                    <p><i class="fas fa-phone"></i> +91 1234567890</p>
                    <p><i class="fas fa-envelope"></i> info@freightx.com</p>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <a href="index.php">Home</a>
                    <a href="services.php">Services</a>
                    <a href="about.php">About Us</a>
                    <a href="contact.php">Contact</a>
                    <a href="track.php">Track Shipment</a>
                </div>
                
                <div class="footer-section">
                    <h3>Services</h3>
                    <a href="services.php#rail-freight">Rail Freight</a>
                    <a href="services.php#road-freight">Road Freight</a>
                    <a href="services.php#warehousing">Warehousing</a>
                    <a href="services.php#express-delivery">Express Delivery</a>
                </div>
                
                <div class="footer-section">
                    <h3>Connect With Us</h3>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2023 FreightX. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- JavaScript for Chat Widget (Placeholder for future Socket.io implementation) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatButton = document.getElementById('chatButton');
            const chatContainer = document.getElementById('chatContainer');
            const closeChat = document.getElementById('closeChat');
            const messageInput = document.getElementById('messageInput');
            const sendMessage = document.getElementById('sendMessage');
            const chatMessages = document.getElementById('chatMessages');
            
            // Toggle chat window
            chatButton.addEventListener('click', function() {
                chatContainer.style.display = 'flex';
                chatButton.style.display = 'none';
            });
            
            closeChat.addEventListener('click', function() {
                chatContainer.style.display = 'none';
                chatButton.style.display = 'flex';
            });
            
            // Basic message sending functionality (to be replaced with Socket.io)
            sendMessage.addEventListener('click', sendUserMessage);
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendUserMessage();
                }
            });
            
            function sendUserMessage() {
                const message = messageInput.value.trim();
                if (message) {
                    // Add user message to chat
                    const userMessageElement = document.createElement('div');
                    userMessageElement.className = 'message user-message';
                    userMessageElement.textContent = message;
                    chatMessages.appendChild(userMessageElement);
                    
                    // Clear input
                    messageInput.value = '';
                    
                    // Scroll to bottom
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                    
                    // Simulate agent response (to be replaced with Socket.io)
                    setTimeout(function() {
                        const agentMessageElement = document.createElement('div');
                        agentMessageElement.className = 'message agent-message';
                        agentMessageElement.textContent = "Thank you for your message. This is a placeholder response. Real-time chat will be implemented soon.";
                        chatMessages.appendChild(agentMessageElement);
                        
                        // Scroll to bottom again
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }, 1000);
                }
            }
        });
    </script>
</body>
</html>

