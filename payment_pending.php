<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

// Check if we have a booking ID
if (!isset($_SESSION['last_booking_id'])) {
    header("Location: user_dashboard.php");
    exit();
}

$booking_id = $_SESSION['last_booking_id'];
$ticket_number = $_SESSION['ticket_number'] ?? '';

// Get booking details
$stmt = $conn->prepare("SELECT b.*, pt.payment_method, pt.transaction_id, pt.payment_status 
                        FROM bookings b 
                        LEFT JOIN payment_transactions pt ON b.id = pt.booking_id 
                        WHERE b.id = ? AND b.user_id = ?");
$stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: user_dashboard.php");
    exit();
}

$booking = $result->fetch_assoc();

// Format payment method for display
$payment_methods = [
    'cod' => 'Cash on Delivery',
    'bank_transfer' => 'Bank Transfer'
];

$payment_method_display = $payment_methods[$booking['payment_method']] ?? ucfirst($booking['payment_method']);

// Clear session variables
unset($_SESSION['last_booking_id']);
unset($_SESSION['ticket_number']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed - FreightX</title>
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
            color: #fff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .success-container {
            background: rgba(0, 0, 0, 0.7);
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .success-icon {
            font-size: 80px;
            color: #f39c12;
            margin-bottom: 20px;
        }
        
        h2 {
            color: #f39c12;
            margin-bottom: 20px;
            font-size: 32px;
        }
        
        p {
            font-size: 18px;
            margin: 10px 0;
            line-height: 1.6;
        }
        
        .ticket {
            font-size: 24px;
            font-weight: bold;
            color: #f1c40f;
            margin: 30px 0;
            padding: 15px;
            border: 2px dashed #f1c40f;
            border-radius: 8px;
            display: inline-block;
        }
        
        .payment-details {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }
        
        .payment-details h3 {
            color: #3498db;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }
        
        .payment-instructions {
            background: rgba(243, 156, 18, 0.2);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
            border-left: 4px solid #f39c12;
        }
        
        .buttons {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        
        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .btn-secondary {
            background: #7f8c8d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #6c7a7d;
        }
        
        @media (max-width: 768px) {
            .success-container {
                padding: 30px 20px;
            }
            
            .buttons {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <i class="fas fa-clipboard-check success-icon"></i>
        <h2>Booking Confirmed</h2>
        <p>Thank you for booking with FreightX!</p>
        <p>Your booking has been confirmed and is pending payment.</p>
        
        <div class="ticket">
            <i class="fas fa-ticket-alt"></i> Ticket Number: <?php echo htmlspecialchars($ticket_number); ?>
        </div>
        
        <div class="payment-details">
            <h3>Booking Details</h3>
            <div class="detail-item">
                <span>
