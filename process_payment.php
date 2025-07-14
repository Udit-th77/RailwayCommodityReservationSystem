<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

// Check if booking details exist in session
if (!isset($_SESSION['booking_details'])) {
    header("Location: book_commodity.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? '';
$user_email = $_SESSION['user_email'] ?? '';
$booking_details = $_SESSION['booking_details'];

// Get payment details from POST
$payment_method = $_POST['payment_method'] ?? '';
$order_id = $_POST['order_id'] ?? '';

if (empty($payment_method) || empty($order_id)) {
    $_SESSION['payment_error'] = "Invalid payment request. Please try again.";
    header("Location: payment.php");
    exit();
}

// Generate a unique ticket number
$ticket_number = "FX" . date('Ymd') . rand(1000, 9999);

// Process payment based on method
$payment_status = 'pending';
$transaction_id = '';
$gateway_response = '';

try {
    // Start transaction
    $conn->begin_transaction();
    
    switch ($payment_method) {
        case 'razorpay':
            // Get Razorpay payment details
            $razorpay_payment_id = $_POST['razorpay_payment_id'] ?? '';
            $razorpay_order_id = $_POST['razorpay_order_id'] ?? '';
            $razorpay_signature = $_POST['razorpay_signature'] ?? '';
            
            if (empty($razorpay_payment_id) || empty($razorpay_order_id) || empty($razorpay_signature)) {
                throw new Exception("Incomplete Razorpay payment details");
            }
            
            // Get Razorpay API key and secret
            $stmt = $conn->prepare("SELECT api_key, api_secret FROM payment_gateways WHERE gateway_name = 'razorpay' AND is_active = 1");
            $stmt->execute();
            $razorpay_config = $stmt->get_result()->fetch_assoc();
            
            if (!$razorpay_config) {
                throw new Exception("Razorpay configuration not found");
            }
            
            // Verify Razorpay signature
            $generated_signature = hash_hmac('sha256', $razorpay_order_id . '|' . $razorpay_payment_id, $razorpay_config['api_secret']);
            
            if ($generated_signature !== $razorpay_signature) {
                throw new Exception("Invalid Razorpay signature");
            }
            
            // Payment is verified
            $payment_status = 'completed';
            $transaction_id = $razorpay_payment_id;
            $gateway_response = json_encode([
                'payment_id' => $razorpay_payment_id,
                'order_id' => $razorpay_order_id,
                'signature' => $razorpay_signature
            ]);
            break;
            
        case 'cod':
            // Cash on Delivery - just mark as pending
            $payment_status = 'pending';
            $transaction_id = 'COD-' . $order_id;
            $gateway_response = json_encode([
                'name' => $_POST['cod_name'] ?? '',
                'phone' => $_POST['cod_phone'] ?? ''
            ]);
            break;
            
        case 'bank_transfer':
            // Bank Transfer - mark as pending until verified
            $payment_status = 'pending';
            $transaction_id = $_POST['transaction_ref'] ?? '';
            $gateway_response = json_encode([
                'transaction_ref' => $_POST['transaction_ref'] ?? '',
                'transaction_date' => $_POST['transaction_date'] ?? ''
            ]);
            break;
            
        default:
            throw new Exception("Unsupported payment method");
    }
    
    // Insert booking into database - Adjusted column names to match your schema
// Insert booking into database
// Insert booking into database
$stmt = $conn->prepare("INSERT INTO bookings (user_id, commodity_type, weight, source, destination, 
                        booking_date, delivery_method, notes, price, status, payment_status, 
                        payment_id, order_id, payment_method, ticket_number) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

// Set booking status based on payment status
$booking_status = ($payment_status === 'completed') ? 'confirmed' : 'pending';

// Get delivery method and notes from booking details or set defaults
$delivery_method = $booking_details['delivery_method'] ?? null;
$notes = $booking_details['notes'] ?? null;

// Bind parameters - Adjusted to match your complete schema
// Insert booking into database
$stmt = $conn->prepare("INSERT INTO bookings (user_id, commodity_type, weight, source, destination, 
                        booking_date, delivery_method, notes, price, status, payment_status, 
                        payment_id, order_id, payment_method, ticket_number) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

// Set booking status based on payment status
$booking_status = ($payment_status === 'completed') ? 'confirmed' : 'pending';

// Get delivery method and notes from booking details or set defaults
$delivery_method = $booking_details['delivery_method'] ?? null;
$notes = $booking_details['notes'] ?? null;
$price = $booking_details['price'] ?? $booking_details['total_price'] ?? 0;

// Bind parameters - Corrected type definition string to match 15 parameters
$stmt->bind_param("isdssssdsssssss", 
    $user_id,
    $booking_details['commodity_type'],
    $booking_details['weight'],
    $booking_details['starting_station'], // This maps to 'source' in your DB
    $booking_details['destination_station'], // This maps to 'destination' in your DB
    $booking_details['booking_date'],
    $delivery_method,
    $notes,
    $price, // Price parameter
    $booking_status, // Status column
    $payment_status,
    $transaction_id,
    $order_id,
    $payment_method,
    $ticket_number
);



    
    // Execute statement
    $stmt->execute();
    
    // Get the booking ID
    $booking_id = $conn->insert_id;
    
    // Insert payment transaction record
    $stmt = $conn->prepare("INSERT INTO payment_transactions (booking_id, user_id, payment_method, amount, transaction_id, 
                            order_id, payment_status, gateway_response) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Use 'price' instead of 'total_price' to match your session data
    $price = $booking_details['total_price'] ?? $booking_details['price'] ?? 0;
    
    $stmt->bind_param("iisdssss", 
        $booking_id,
        $user_id,
        $payment_method,
        $price,
        $transaction_id,
        $order_id,
        $payment_status,
        $gateway_response
    );
    
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Store booking ID and ticket number in session
    $_SESSION['last_booking_id'] = $booking_id;
    $_SESSION['ticket_number'] = $ticket_number;
    
    // Clear booking details from session
    unset($_SESSION['booking_details']);
    unset($_SESSION['order_id']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log the error
    error_log("Payment processing error: " . $e->getMessage());
    
    // Set error message
    $_SESSION['payment_error'] = "An error occurred while processing your payment: " . $e->getMessage();
    
    // Redirect back to payment page
    header("Location: payment.php");
    exit();
}

// Determine success page based on payment method
if ($payment_method === 'cod' || $payment_method === 'bank_transfer') {
    header("Location: payment_pending.php");
    exit();
} else {
    header("Location: payment_success.php");
    exit();
}
?>
