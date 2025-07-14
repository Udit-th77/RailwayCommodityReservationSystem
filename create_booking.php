<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// Get user details
$stmt = $conn->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();

// Define available stations
$stations = [
    "New Delhi",
    "Mumbai Central",
    "Howrah Junction",
    "Chennai Central",
    "Kolkata",
    "Bangalore City",
    "Hyderabad Deccan",
    "Ahmedabad Junction",
    "Pune Junction",
    "Lucknow Junction"
];

// Define commodity types
$commodity_types = [
    "Grains" => 50,
    "Coal" => 45,
    "Cement" => 60,
    "Steel" => 75,
    "Chemicals" => 85,
    "Electronics" => 100,
    "Textiles" => 65,
    "Furniture" => 80,
    "Machinery" => 90,
    "Other" => 70
];

// Define delivery methods
$delivery_methods = [
    "Standard" => 1.0,
    "Express" => 1.5,
    "Premium" => 2.0
];

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $commodity_type = trim($_POST['commodity_type']);
    $weight = floatval($_POST['weight']);
    $source = trim($_POST['source']);
    $destination = trim($_POST['destination']);
    $booking_date = trim($_POST['booking_date']);
    $delivery_method = isset($_POST['delivery_method']) ? trim($_POST['delivery_method']) : null;
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : null;
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : null;
    
    // Validate input
    if (empty($commodity_type) || $weight <= 0 || empty($source) || empty($destination) || empty($booking_date)) {
        $error_message = "Please fill all required fields.";
    } elseif ($source === $destination) {
        $error_message = "Source and destination cannot be the same.";
    } elseif (strtotime($booking_date) < strtotime(date('Y-m-d'))) {
        $error_message = "Booking date cannot be in the past.";
    } else {
        // Calculate price based on commodity type, weight, and delivery method
        $base_price_per_ton = $commodity_types[$commodity_type] ?? 70; // Default to 70 if not found
        $delivery_multiplier = $delivery_methods[$delivery_method] ?? 1.0; // Default to 1.0 if not found
        
        // Calculate distance factor (simplified for demo)
        $distance_factor = 1.0;
        if (($source == "Mumbai Central" && $destination == "Delhi") || 
            ($source == "Delhi" && $destination == "Mumbai Central")) {
            $distance_factor = 1.4;
        } elseif (($source == "Chennai Central" && $destination == "Kolkata") || 
                 ($source == "Kolkata" && $destination == "Chennai Central")) {
            $distance_factor = 1.3;
        } elseif (($source == "Bangalore City" && $destination == "Hyderabad Deccan") || 
                 ($source == "Hyderabad Deccan" && $destination == "Bangalore City")) {
            $distance_factor = 0.8;
        }
        
        // Final price calculation
        $price = $base_price_per_ton * $weight * $delivery_multiplier * $distance_factor;
        $price = round($price, 2); // Round to 2 decimal places
        
        // Generate unique ticket number
        $ticket_number = "FX" . date('Ymd') . rand(1000, 9999);
        
        // Generate order ID
        $order_id = "ORD" . time() . rand(100, 999);
        
        // Set initial status
        $status = "pending";
        $payment_status = "pending";
        
        // Insert booking into database
        $stmt = $conn->prepare("INSERT INTO bookings (user_id, commodity_type, weight, source, destination, booking_date, delivery_method, notes, price, status, payment_status, ticket_number, order_id, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isdssssdssssss", $user_id, $commodity_type, $weight, $source, $destination, $booking_date, $delivery_method, $notes, $price, $status, $payment_status, $ticket_number, $order_id, $payment_method);
        
        if ($stmt->execute()) {
            $booking_id = $conn->insert_id;
            $success_message = "Booking created successfully! Your ticket number is: " . $ticket_number;
            
            // Redirect to payment page
            $_SESSION['booking_id'] = $booking_id;
            $_SESSION['price'] = $price;
            $_SESSION['order_id'] = $order_id;
            $_SESSION['ticket_number'] = $ticket_number;
            
            header("Location: payment.php");
            exit();
        } else {
            $error_message = "Error: " . $stmt->error;
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Booking - FreightX</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background: linear-gradient(to right, #3498db, #2c3e50);
            color: #333;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .booking-form {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            padding: 30px;
            margin-top: 20px;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-header h1 {
            color: #2c3e50;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .form-header p {
            color: #7f8c8d;
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        input[type="text"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            border-color: #3498db;
            outline: none;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .price-estimate {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
        
        .price-estimate h3 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .price-breakdown {
            margin-top: 10px;
        }
        
        .price-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .total-price {
            font-weight: bold;
            font-size: 18px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: 0.3s;
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
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
        
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="home-link"><i class="fas fa-home"></i> Back to Home</a>
    
    <div class="container">
        <div class="booking-form">
            <div class="form-header">
                <h1><i class="fas fa-shipping-fast"></i> Create New Booking</h1>
                <p>Fill in the details below to book your commodity shipment</p>
            </div>
            
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="bookingForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="commodity_type">Commodity Type *</label>
                        <select id="commodity_type" name="commodity_type" required onchange="updatePriceEstimate()">
                            <option value="">Select Commodity Type</option>
                            <?php foreach ($commodity_types as $type => $price): ?>
                                <option value="<?php echo $type; ?>" data-price="<?php echo $price; ?>"><?php echo $type; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="weight">Weight (in Tons) *</label>
                        <input type="number" id="weight" name="weight" min="0.1" step="0.1" required onchange="updatePriceEstimate()" onkeyup="updatePriceEstimate()">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="source">Source Station *</label>
                        <select id="source" name="source" required>
                            <option value="">Select Source Station</option>
                            <?php foreach ($stations as $station): ?>
                                <option value="<?php echo $station; ?>"><?php echo $station; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="destination">Destination Station *</label>
                        <select id="destination" name="destination" required onchange="updatePriceEstimate()">
                            <option value="">Select Destination Station</option>
                            <?php foreach ($stations as $station): ?>
                                <option value="<?php echo $station; ?>"><?php echo $station; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="booking_date">Booking Date *</label>
                        <input type="date" id="booking_date" name="booking_date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="delivery_method">Delivery Method</label>
                        <select id="delivery_method" name="delivery_method" onchange="updatePriceEstimate()">
                            <option value="">Select Delivery Method</option>
                            <?php foreach ($delivery_methods as $method => $multiplier): ?>
                                <option value="<?php echo $method; ?>" data-multiplier="<?php echo $multiplier; ?>"><?php echo $method; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">

                                    <label for="notes">Additional Notes</label>
                    <textarea id="notes" name="notes" rows="3" placeholder="Any special instructions or requirements..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="payment_method">Payment Method *</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="">Select Payment Method</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="debit_card">Debit Card</option>
                        <option value="net_banking">Net Banking</option>
                        <option value="upi">UPI</option>
                        <option value="wallet">Digital Wallet</option>
                    </select>
                </div>
                
                <div class="price-estimate" id="priceEstimate">
                    <h3>Price Estimate</h3>
                    <p>Select commodity type, weight, and delivery method to see the estimated price.</p>
                    <div class="price-breakdown" id="priceBreakdown" style="display: none;">
                        <div class="price-item">
                            <span>Base Price:</span>
                            <span id="basePrice">₹0.00</span>
                        </div>
                        <div class="price-item">
                            <span>Weight Factor:</span>
                            <span id="weightFactor">0 tons</span>
                        </div>
                        <div class="price-item">
                            <span>Delivery Method:</span>
                            <span id="deliveryFactor">Standard (x1.0)</span>
                        </div>
                        <div class="price-item">
                            <span>Distance Factor:</span>
                            <span id="distanceFactor">x1.0</span>
                        </div>
                        <div class="total-price">
                            <span>Total Estimated Price:</span>
                            <span id="totalPrice">₹0.00</span>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="admin_dashboard.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Proceed to Payment</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Set minimum date for booking to today
        document.getElementById('booking_date').min = new Date().toISOString().split('T')[0];
        
        // Function to update price estimate
        function updatePriceEstimate() {
            const commoditySelect = document.getElementById('commodity_type');
            const weightInput = document.getElementById('weight');
            const deliveryMethodSelect = document.getElementById('delivery_method');
            const sourceSelect = document.getElementById('source');
            const destinationSelect = document.getElementById('destination');
            
            const priceBreakdown = document.getElementById('priceBreakdown');
            const basePriceElement = document.getElementById('basePrice');
            const weightFactorElement = document.getElementById('weightFactor');
            const deliveryFactorElement = document.getElementById('deliveryFactor');
            const distanceFactorElement = document.getElementById('distanceFactor');
            const totalPriceElement = document.getElementById('totalPrice');
            
            // Get selected values
            const selectedCommodity = commoditySelect.options[commoditySelect.selectedIndex];
            const weight = parseFloat(weightInput.value) || 0;
            const selectedDelivery = deliveryMethodSelect.options[deliveryMethodSelect.selectedIndex];
            const source = sourceSelect.value;
            const destination = destinationSelect.value;
            
            // If required fields are not filled, hide price breakdown
            if (!selectedCommodity.value || weight <= 0) {
                priceBreakdown.style.display = 'none';
                return;
            }
            
            // Get base price per ton
            const basePricePerTon = parseFloat(selectedCommodity.dataset.price) || 0;
            
            // Get delivery multiplier
            const deliveryMultiplier = selectedDelivery.value ? 
                parseFloat(selectedDelivery.dataset.multiplier) || 1.0 : 1.0;
            
            // Calculate distance factor (simplified for demo)
            let distanceFactor = 1.0;
            if ((source === "Mumbai Central" && destination === "New Delhi") || 
                (source === "New Delhi" && destination === "Mumbai Central")) {
                distanceFactor = 1.4;
            } else if ((source === "Chennai Central" && destination === "Kolkata") || 
                      (source === "Kolkata" && destination === "Chennai Central")) {
                distanceFactor = 1.3;
            } else if ((source === "Bangalore City" && destination === "Hyderabad Deccan") || 
                      (source === "Hyderabad Deccan" && destination === "Bangalore City")) {
                distanceFactor = 0.8;
            }
            
            // Calculate total price
            const totalPrice = basePricePerTon * weight * deliveryMultiplier * distanceFactor;
            
            // Update price breakdown elements
            basePriceElement.textContent = '₹' + basePricePerTon.toFixed(2) + ' per ton';
            weightFactorElement.textContent = weight.toFixed(1) + ' tons';
            
            const deliveryMethod = selectedDelivery.value || 'Standard';
            deliveryFactorElement.textContent = deliveryMethod + ' (x' + deliveryMultiplier.toFixed(1) + ')';
            
            distanceFactorElement.textContent = 'x' + distanceFactor.toFixed(1);
            totalPriceElement.textContent = '₹' + totalPrice.toFixed(2);
            
            // Show price breakdown
            priceBreakdown.style.display = 'block';
        }
        
        // Validate source and destination are not the same
        document.getElementById('bookingForm').addEventListener('submit', function(event) {
            const source = document.getElementById('source').value;
            const destination = document.getElementById('destination').value;
            
            if (source === destination && source !== '') {
                alert('Source and destination stations cannot be the same.');
                event.preventDefault();
            }
        });
    </script>
</body>
</html>

