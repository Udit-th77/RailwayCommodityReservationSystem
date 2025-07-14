<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

// Check if booking ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: user_dashboard.php");
    exit();
}

$booking_id = $_GET['id'];
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';
$booking = null;

// Get booking details
$stmt = $conn->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Booking not found or doesn't belong to this user
    header("Location: user_dashboard.php");
    exit();
}

$booking = $result->fetch_assoc();

// Check if booking is already cancelled
if ($booking['status'] === 'cancelled') {
    $error = "This booking has already been cancelled.";
}

// Check if booking is in a state that can be cancelled
// For example, you might not allow cancellation of completed bookings
if ($booking['status'] === 'completed' || $booking['status'] === 'in_transit') {
    $error = "This booking cannot be cancelled because it is already " . 
             ($booking['status'] === 'completed' ? 'completed' : 'in transit') . ".";
}

// Process cancellation if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($error)) {
    $cancellation_reason = trim($_POST['cancellation_reason']);
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update booking status
        $update_stmt = $conn->prepare("UPDATE bookings SET status = 'cancelled', 
                                       cancellation_reason = ?, 
                                       cancelled_at = NOW() 
                                       WHERE id = ? AND user_id = ?");
        $update_stmt->bind_param("sii", $cancellation_reason, $booking_id, $user_id);
        $update_stmt->execute();
        
        // Check if we need to process a refund
        if ($booking['payment_status'] === 'paid') {
            // Insert refund record
            $refund_stmt = $conn->prepare("INSERT INTO refunds 
                                          (booking_id, user_id, amount, status, notes) 
                                          VALUES (?, ?, ?, 'pending', 'Cancellation refund')");
            $refund_stmt->bind_param("iid", $booking_id, $user_id, $booking['price']);
            $refund_stmt->execute();
            
            // You might want to call a payment gateway API here to process the actual refund
            // This is just a placeholder for the refund logic
        }
        
        // Commit transaction
        $conn->commit();
        
        $message = "Your booking has been successfully cancelled.";
        if ($booking['payment_status'] === 'paid') {
            $message .= " A refund request has been initiated and will be processed within 5-7 business days.";
        }
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error = "An error occurred while cancelling your booking. Please try again later.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Booking - FreightX</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #3498db, #2c3e50);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }
        
        .container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 600px;
            padding: 30px;
        }
        
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
        }
        
        .booking-details {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .booking-details h2 {
            margin-top: 0;
            color: #3498db;
            font-size: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 16px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #555;
        }
        
        .cancellation-form {
            margin-top: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
            margin-bottom: 20px;
            min-height: 100px;
            resize: vertical;
        }
        
        .button-group {
            display: flex;
            justify-content: space-between;
        }
        
        .btn {
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            border: none;
            text-align: center;
            text-decoration: none;
        }
        
        .btn-cancel {
            background: #e74c3c;
            color: white;
            flex: 1;
            margin-right: 10px;
        }
        
        .btn-cancel:hover {
            background: #c0392b;
        }
        
        .btn-back {
            background: #7f8c8d;
            color: white;
            flex: 1;
            margin-left: 10px;
        }
        
        .btn-back:hover {
            background: #6c7a7d;
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .cancellation-policy {
            margin-top: 30px;
            font-size: 14px;
            color: #6c757d;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .home-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-size: 16px;
        }
        
        .home-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <a href="user_dashboard.php" class="home-link"><i class="fas fa-home"></i> Back to Dashboard</a>
    
    <div class="container">
        <h1>Cancel Booking</h1>
        
        <?php if (!empty($message)): ?>
            <div class="message success">
                <?php echo $message; ?>
                <script>
                    // Redirect to dashboard after 5 seconds
                    setTimeout(function() {
                        window.location.href = 'user_dashboard.php';
                    }, 5000);
                </script>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($booking && empty($message)): ?>
            <div class="booking-details">
                <h2>Booking Information</h2>
                <div class="detail-row">
                    <span class="detail-label">Ticket Number:</span>
                    <span><?php echo htmlspecialchars($booking['ticket_number']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Commodity:</span>
                    <span><?php echo htmlspecialchars($booking['commodity_type']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Weight:</span>
                    <span><?php echo htmlspecialchars($booking['weight']); ?> Tons</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Route:</span>
                    <span><?php echo htmlspecialchars($booking['source']); ?> to <?php echo htmlspecialchars($booking['destination']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Booking Date:</span>
                    <span><?php echo date('d M Y', strtotime($booking['booking_date'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Amount:</span>
                    <span>₹<?php echo number_format($booking['price'], 2); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span><?php echo ucfirst(htmlspecialchars($booking['status'])); ?></span>
                </div>
            </div>
            
            <?php if (empty($error)): ?>
                <form class="cancellation-form" method="POST" action="">
                    <label for="cancellation_reason">Please tell us why you're cancelling this booking:</label>
                    <textarea id="cancellation_reason" name="cancellation_reason" required></textarea>
                    
                    <div class="button-group">
                        <button type="submit" class="btn btn-cancel">Confirm Cancellation</button>
                        <a href="booking_details.php?id=<?php echo $booking_id; ?>" class="btn btn-back">Go Back</a>
                    </div>
                </form>
                
                <div class="cancellation-policy">
                    <strong>Cancellation Policy:</strong>
                    <ul>
                        <li>Cancellations made more than 48 hours before the booking date are eligible for a full refund.</li>
                        <li>Cancellations made between 24-48 hours before the booking date are eligible for a 50% refund.</li>
                        <li>Cancellations made less than 24 hours before the booking date are not eligible for a refund.</li>
                        <li>Refunds will be processed within 5-7 business days to the original payment method.</li>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>

