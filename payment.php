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
$user_phone = $_SESSION['user_phone'] ?? '';
$booking_details = $_SESSION['booking_details'];

// Generate a unique order ID
$order_id = 'FX' . time() . rand(100, 999);
$_SESSION['order_id'] = $order_id;

// Get active payment gateways
$stmt = $conn->prepare("SELECT * FROM payment_gateways WHERE is_active = 1 ORDER BY display_name");
$stmt->execute();
$payment_gateways = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get Razorpay configuration if available
$razorpay_config = null;
foreach ($payment_gateways as $gateway) {
    if ($gateway['gateway_name'] === 'razorpay') {
        $razorpay_config = $gateway;
        break;
    }
}

// Amount in paise (Razorpay expects amount in smallest currency unit)
$amount_in_paise = $booking_details['total_price'] * 100;

// Check for payment errors
$payment_error = $_SESSION['payment_error'] ?? '';
unset($_SESSION['payment_error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - FreightX</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Include Razorpay JS SDK -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
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
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
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
        
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            margin-top: 20px;
        }
        
        h2 {
            margin-bottom: 25px;
            color: #2c3e50;
            text-align: center;
            font-size: 28px;
        }
        
        .booking-summary {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .booking-summary h3 {
            color: #3498db;
            margin-bottom: 15px;
            font-size: 20px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .summary-item:last-child {
            border-bottom: none;
        }
        
        .summary-label {
            font-weight: bold;
            color: #555;
        }
        
        .summary-value {
            color: #333;
        }
        
        .total-price {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            text-align: center;
            margin: 20px 0;
        }
        
        .payment-methods {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 15px;
        }
        
        .payment-method {
            flex: 1;
            min-width: calc(50% - 15px);
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-method:hover {
            border-color: #3498db;
            transform: translateY(-5px);
        }
        
        .payment-method.selected {
            border-color: #3498db;
            background-color: #e8f4fd;
        }
        
        .payment-method i {
            font-size: 30px;
            margin-bottom: 10px;
            color: #3498db;
        }
        
        .payment-method p {
            margin: 0;
            font-weight: bold;
        }
        
        .payment-method .description {
            font-size: 12px;
            color: #777;
            margin-top: 5px;
        }
        
        button {
            background: #3498db;
            color: white;
            padding: 15px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
        }
        
        button:hover {
            background: #2980b9;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .payment-form {
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #2c3e50;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .payment-info {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #2c3e50;
        }
        
        .payment-info p {
            margin-bottom: 10px;
        }
        
        .payment-info i {
            color: #3498db;
            margin-right: 5px;
        }
        
        @media (max-width: 768px) {
            .payment-method {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <a href="book_commodity.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Booking
    </a>
    
    <div class="container">
        <h2><i class="fas fa-credit-card"></i> Payment</h2>
        
        <?php if (!empty($payment_error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($payment_error); ?></div>
        <?php endif; ?>
        
        <div class="booking-summary">
            <h3>Booking Summary</h3>
            <div class="summary-item">
                <span class="summary-label">Commodity:</span>
                <span class="summary-value"><?php echo htmlspecialchars($booking_details['commodity_type']); ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Weight:</span>
                <span class="summary-value"><?php echo htmlspecialchars($booking_details['weight']); ?> tons</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">From:</span>
                <span class="summary-value"><?php echo htmlspecialchars($booking_details['starting_station']); ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">To:</span>
                <span class="summary-value"><?php echo htmlspecialchars($booking_details['destination_station']); ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Booking Date:</span>
                <span class="summary-value"><?php echo date('F d, Y', strtotime($booking_details['booking_date'])); ?></span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Delivery Method:</span>
                <span class="summary-value"><?php echo ucwords(str_replace('_', ' ', $booking_details['delivery_method'])); ?></span>
            </div>
        </div>
        
        <div class="total-price">
            Total: ₹<?php echo number_format($booking_details['total_price'], 2); ?>
        </div>
        
        <div class="payment-info">
            <p><i class="fas fa-info-circle"></i> Select your preferred payment method below. Your booking will be confirmed once payment is completed.</p>
            <p><i class="fas fa-shield-alt"></i> All payments are secure and encrypted.</p>
        </div>
        
        <h3 style="margin-bottom: 15px; text-align: center;">Select Payment Method</h3>
        
        <div class="payment-methods">
            <?php if ($razorpay_config): ?>
            <div class="payment-method" onclick="selectPaymentMethod('razorpay', this)" data-gateway="razorpay">
                <i class="fas fa-credit-card"></i>
                <p>Razorpay</p>
                <div class="description">Credit/Debit Card, UPI, NetBanking, Wallet</div>
            </div>
            <?php endif; ?>
            
            <div class="payment-method" onclick="selectPaymentMethod('cod', this)" data-gateway="cod">
                <i class="fas fa-money-bill-wave"></i>
                <p>Cash on Delivery</p>
                <div class="description">Pay when your shipment is picked up</div>
            </div>
            
            <div class="payment-method" onclick="selectPaymentMethod('bank_transfer', this)" data-gateway="bank_transfer">
                <i class="fas fa-university"></i>
                <p>Bank Transfer</p>
                <div class="description">Direct bank transfer/NEFT/RTGS</div>
            </div>
            
            <?php foreach ($payment_gateways as $gateway): ?>
                <?php if ($gateway['gateway_name'] !== 'razorpay'): ?>
                <div class="payment-method" onclick="selectPaymentMethod('<?php echo $gateway['gateway_name']; ?>', this)" data-gateway="<?php echo $gateway['gateway_name']; ?>">
                    <i class="fas fa-<?php echo getGatewayIcon($gateway['gateway_name']); ?>"></i>
                    <p><?php echo htmlspecialchars($gateway['display_name']); ?></p>
                    <div class="description">Online Payment</div>
                </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
        
        <!-- COD Form -->      
         
        <form id="codForm" action="process_payment.php" method="POST" class="payment-form" style="display: none;">
            <input type="hidden" name="payment_method" value="cod">
            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
            
            <div class="payment-info">
                <p><i class="fas fa-exclamation-triangle"></i> By selecting Cash on Delivery, you agree to pay the full amount when your shipment is picked up.</p>
                <p><i class="fas fa-info-circle"></i> A confirmation call may be made before pickup.</p>
            </div>
            
            <div class="form-group">
                <label for="cod_name">Full Name</label>
                <input type="text" id="cod_name" name="cod_name" value="<?php echo htmlspecialchars($user_name); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="cod_phone">Contact Number</label>
                <input type="tel" id="cod_phone" name="cod_phone" value="<?php echo htmlspecialchars($user_phone); ?>" required>
            </div>
            
            <button type="submit">Confirm Booking</button>
        </form>
        
        <!-- Bank Transfer Form -->
        <form id="bankTransferForm" action="process_payment.php" method="POST" class="payment-form" style="display: none;">
            <input type="hidden" name="payment_method" value="bank_transfer">
            <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
            
            <div class="payment-info">
                <p><i class="fas fa-university"></i> <strong>Bank Account Details:</strong></p>
                <p>Account Name: FreightX Logistics Pvt Ltd</p>
                <p>Account Number: 1234567890</p>
                <p>IFSC Code: ABCD0001234</p>
                <p>Bank: State Bank of India</p>
                <p><i class="fas fa-info-circle"></i> Please include your Order ID: <strong><?php echo $order_id; ?></strong> in the payment reference.</p>
            </div>
            
            <div class="form-group">
                <label for="transaction_ref">Transaction Reference Number</label>
                <input type="text" id="transaction_ref" name="transaction_ref" placeholder="Enter your bank transaction reference" required>
            </div>
            
            <div class="form-group">
                <label for="transaction_date">Transaction Date</label>
                <input type="date" id="transaction_date" name="transaction_date" required>
            </div>
            
            <button type="submit">Confirm Payment</button>
        </form>
        
        <!-- Razorpay Button (Hidden) -->
        <button id="razorpayButton" style="display: none;">Pay with Razorpay</button>
    </div>
    
    <script>
        // Function to select payment method
        function selectPaymentMethod(method, element) {
            // Remove selected class from all payment methods
            document.querySelectorAll('.payment-method').forEach(item => {
                item.classList.remove('selected');
            });
            
            // Add selected class to clicked method
            element.classList.add('selected');
            
            // Hide all payment forms
            document.getElementById('codForm').style.display = 'none';
            document.getElementById('bankTransferForm').style.display = 'none';
            document.getElementById('razorpayButton').style.display = 'none';
            
            // Show the selected payment form
            if (method === 'cod') {
                document.getElementById('codForm').style.display = 'block';
            } else if (method === 'bank_transfer') {
                document.getElementById('bankTransferForm').style.display = 'block';
            } else if (method === 'razorpay') {
                document.getElementById('razorpayButton').style.display = 'block';
            }
            
            // Store selected payment method
            selectedPaymentMethod = method;
        }
        
        // Initialize variables
        let selectedPaymentMethod = '';
        
        <?php if ($razorpay_config): ?>
        // Razorpay configuration
        const razorpayOptions = {
            key: "<?php echo $razorpay_config['api_key']; ?>",
            amount: "<?php echo $amount_in_paise; ?>",
            currency: "INR",
            name: "FreightX",
            description: "Commodity Booking Payment",
            image: "https://your-website.com/logo.png", // Replace with your logo URL
            order_id: "", // Will be filled when we create an order
            handler: function (response) {
                // This function will be called when payment is successful
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('razorpay_order_id').value = response.razorpay_order_id;
                document.getElementById('razorpay_signature').value = response.razorpay_signature;
                
                // Submit the form to complete the payment process
                document.getElementById('razorpayForm').submit();
            },
            prefill: {
                name: "<?php echo htmlspecialchars($user_name); ?>",
                email: "<?php echo htmlspecialchars($user_email); ?>",
                contact: "<?php echo htmlspecialchars($user_phone); ?>"
            },
            notes: {
                order_id: "<?php echo $order_id; ?>",
                user_id: "<?php echo $user_id; ?>"
            },
            theme: {
                color: "#3498db"
            }
        };
        
        // Initialize Razorpay instance
        let razorpay = new Razorpay(razorpayOptions);
        
        // Handle Razorpay button click
        document.getElementById('razorpayButton').addEventListener('click', function() {
            // Create an order on your server first
            fetch('create_razorpay_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    amount: <?php echo $amount_in_paise; ?>,
                    order_id: "<?php echo $order_id; ?>"
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.id) {
                    // Set the order ID in options
                    razorpayOptions.order_id = data.id;
                    razorpay = new Razorpay(razorpayOptions);
                    razorpay.open();
                } else {
                    alert('Failed to create order. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
        <?php endif; ?>
        
        // Set default payment method on page load
        window.onload = function() {
            const defaultMethod = document.querySelector('.payment-method');
            if (defaultMethod) {
                selectPaymentMethod(defaultMethod.dataset.gateway, defaultMethod);
            }
        };
    </script>
    
    <!-- Hidden form for Razorpay -->
    <form id="razorpayForm" action="process_payment.php" method="POST" style="display: none;">
        <input type="hidden" name="payment_method" value="razorpay">
        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
        <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
        <input type="hidden" name="razorpay_signature" id="razorpay_signature">
    </form>
</body>
</html>

<?php
// Helper function to get icon for payment gateway
function getGatewayIcon($gateway) {
    $icons = [
        'razorpay' => 'credit-card',
        'paytm' => 'mobile-alt',
        'paypal' => 'paypal',
        'stripe' => 'credit-card',
        'cod' => 'money-bill-wave',
        'bank_transfer' => 'university'
    ];
    
    return $icons[$gateway] ?? 'credit-card';
}
?>

     