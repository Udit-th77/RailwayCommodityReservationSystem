<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';

// Check if booking ID is provided
$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

// Validate if booking exists and belongs to the user
if ($booking_id > 0) {
    $stmt = $conn->prepare("SELECT id, ticket_number, commodity_type FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Booking not found or doesn't belong to this user
        header("Location: user_dashboard.php");
        exit();
    }
    
    $booking = $result->fetch_assoc();
}

// Initialize message variable
$message = '';

// Handle form submission (this will be replaced with Socket.io later)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $user_message = trim($_POST['message']);
    
    if (!empty($user_message)) {
        // For now, just show a confirmation message
        // Later, this will be replaced with Socket.io implementation
        $message = "Your message has been sent to our support team. We'll respond shortly.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Support - FreightX</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: #333;
            min-height: 100vh;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .chat-container {
            width: 100%;
            max-width: 800px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 80vh;
        }
        
        .chat-header {
            background: #2c3e50;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chat-header h2 {
            font-size: 20px;
            display: flex;
            align-items: center;
        }
        
        .chat-header h2 i {
            margin-right: 10px;
        }
        
        .booking-info {
            background: rgba(44, 62, 80, 0.1);
            padding: 10px 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .booking-info p {
            margin: 5px 0;
            font-size: 14px;
        }
        
        .booking-info strong {
            color: #2c3e50;
        }
        
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f9f9f9;
        }
        
        .message {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }
        
        .message-content {
            max-width: 80%;
            padding: 10px 15px;
            border-radius: 10px;
            position: relative;
        }
        
        .user-message {
            align-items: flex-end;
        }
        
        .user-message .message-content {
            background: #3498db;
            color: white;
            border-bottom-right-radius: 0;
        }
        
        .support-message {
            align-items: flex-start;
        }
        
        .support-message .message-content {
            background: #e9e9e9;
            color: #333;
            border-bottom-left-radius: 0;
        }
        
        .message-time {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }
        
        .user-message .message-time {
            text-align: right;
        }
        
        .chat-input {
            padding: 15px;
            background: white;
            border-top: 1px solid #ddd;
            display: flex;
        }
        
        .chat-input textarea {
            flex: 1;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 30px;
            resize: none;
            height: 50px;
            font-size: 16px;
        }
        
        .chat-input button {
            background: #3498db;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            margin-left: 10px;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            transition: background 0.3s;
        }
        
        .chat-input button:hover {
            background: #2980b9;
        }
        
        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            font-size: 16px;
        }
        
        .back-link i {
            margin-right: 5px;
        }
        
        .status-indicator {
            display: flex;
            align-items: center;
            font-size: 14px;
        }
        
        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #2ecc71;
            margin-right: 5px;
        }
        
        .notification {
            background: #f8d7da;
            color: #721c24;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .success-notification {
            background: #d4edda;
            color: #155724;
        }
        
        .placeholder-text {
            text-align: center;
            color: #888;
            margin-top: 50px;
        }
        
        .placeholder-text i {
            font-size: 50px;
            margin-bottom: 20px;
            color: #ddd;
        }
    </style>
</head>
<body>
    <a href="view_booking_details.php?id=<?php echo $booking_id; ?>" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Booking
    </a>
    
    <div class="chat-container">
        <div class="chat-header">
            <h2><i class="fas fa-headset"></i> Customer Support</h2>
            <div class="status-indicator">
                <div class="status-dot"></div>
                <span>Support Online</span>
            </div>
        </div>
        
        <?php if ($booking_id > 0): ?>
        <div class="booking-info">
            <p><strong>Booking:</strong> #<?php echo htmlspecialchars($booking['ticket_number']); ?></p>
            <p><strong>Commodity:</strong> <?php echo htmlspecialchars($booking['commodity_type']); ?></p>
        </div>
        <?php endif; ?>
        
        <div class="chat-messages">
            <?php if (!empty($message)): ?>
                <div class="notification success-notification"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <!-- Sample messages for UI demonstration - will be replaced with real messages later -->
            <div class="message support-message">
                <div class="message-content">
                    Hello <?php echo htmlspecialchars($user_name); ?>! Welcome to FreightX support. How can we help you today?
                </div>
                <div class="message-time">Today, <?php echo date('h:i A'); ?></div>
            </div>
            
            <!-- Placeholder for empty chat -->
            <div class="placeholder-text">
                <i class="fas fa-comments"></i>
                <p>Your conversation with our support team will appear here.</p>
                <p>Type a message below to get started.</p>
            </div>
        </div>
        
        <form class="chat-input" method="POST" action="">
            <textarea name="message" placeholder="Type your message here..." required></textarea>
            <button type="submit"><i class="fas fa-paper-plane"></i></button>
        </form>
    </div>
    
    <!-- This section will be replaced with Socket.io implementation later -->
    <script>
        // Placeholder for future Socket.io implementation
        document.addEventListener('DOMContentLoaded', function() {
            const chatMessages = document.querySelector('.chat-messages');
            const placeholderText = document.querySelector('.placeholder-text');
            const form = document.querySelector('.chat-input');
            
            form.addEventListener('submit', function(e) {
                // This will be replaced with Socket.io emit event
                const messageInput = this.querySelector('textarea');
                if (messageInput.value.trim()) {
                    // Show the message in the UI
                    placeholderText.style.display = 'none';
                    
                    // This is just for UI demonstration
                    // In the real implementation, this will be handled by Socket.io
                    setTimeout(() => {
                        const now = new Date();
                        const timeStr = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        
                        // Create user message element
                        const userMessage = document.createElement('div');
                        userMessage.className = 'message user-message';
                        userMessage.innerHTML = `
                            <div class="message-content">${messageInput.value}</div>
                            <div class="message-time">Today, ${timeStr}</div>
                        `;
                        
                        // Add to chat
                        chatMessages.appendChild(userMessage);
                        
                        // Auto scroll to bottom
                        chatMessages.scrollTop = chatMessages.scrollHeight;
                    }, 100);
                }
            });
        });
    </script>
</body>
</html>
