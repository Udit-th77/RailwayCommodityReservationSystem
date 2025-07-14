<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Set minimum date to today
$min_date = date('Y-m-d');
// Set maximum date to 3 months from now
$max_date = date('Y-m-d', strtotime('+3 months'));

// Initialize error message
$error_message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commodity_type = $_POST['commodity_type'] ?? '';
    $weight = $_POST['weight'] ?? '';
    $starting_station = $_POST['starting_station'] ?? '';
    $destination_station = $_POST['destination_station'] ?? '';
    $booking_date = $_POST['booking_date'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $delivery_method = $_POST['delivery_method'] ?? '';
    
    // Basic validation
    if (empty($commodity_type) || empty($weight) || empty($starting_station) || 
        empty($destination_station) || empty($booking_date) || empty($delivery_method)) {
        $error_message = "All fields are required except notes.";
    } elseif ($starting_station === $destination_station) {
        $error_message = "Starting and destination stations cannot be the same.";
    } elseif ($weight <= 0 || $weight > 1000) {
        $error_message = "Weight must be between 1 and 1000 tons.";
    } elseif (strtotime($booking_date) < strtotime($min_date)) {
        $error_message = "Booking date cannot be in the past.";
    } elseif (strtotime($booking_date) > strtotime($max_date)) {
        $error_message = "Booking date cannot be more than 3 months in the future.";
    } elseif ($delivery_method === 'train' && $weight < 50) {
        $error_message = "Train delivery requires a minimum of 50 tons.";
    } else {
        // Calculate price based on weight, distance, and delivery method
        $base_price_per_ton = [
            'delivery_partner' => 150,  // Higher for small deliveries
            'truck' => 120,             // Medium price
            'train' => 80               // Lower for bulk transport
        ];
        
        $price_per_ton = $base_price_per_ton[$delivery_method] ?? 100;
        $total_price = $weight * $price_per_ton;
        
        // Store booking details in session for payment page
        $_SESSION['booking_details'] = [
            'commodity_type' => $commodity_type,
            'weight' => $weight,
            'starting_station' => $starting_station,
            'destination_station' => $destination_station,
            'booking_date' => $booking_date,
            'notes' => $notes,
            'delivery_method' => $delivery_method,
            'total_price' => $total_price
        ];
        
        // Redirect to payment page
        header("Location: payment.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Commodity - FreightX</title>
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
        
        .container h2 { 
            margin-bottom: 25px; 
            color: #2c3e50;
            text-align: center;
            font-size: 28px;
        }
        
        .form-group { 
            margin-bottom: 20px; 
            text-align: left; 
        }
        
        label { 
            display: block; 
            font-weight: bold; 
            margin-bottom: 8px;
            color: #2c3e50;
        }
        
        select, input, textarea {
            width: 100%; 
            padding: 12px; 
            border: 1px solid #ddd;
            border-radius: 5px; 
            font-size: 16px;
            transition: border 0.3s;
        }
        
        select:focus, input:focus, textarea:focus {
            border-color: #3498db;
            outline: none;
        }
        
        button {
            background: #3498db; 
            color: white; 
            padding: 12px 20px;
            border: none; 
            border-radius: 5px; 
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
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
        
        .price-estimate {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            color: #2c3e50;
            display: none;
        }
        
        .price-estimate h3 {
            margin-bottom: 5px;
            color: #3498db;
        }
        
        .booking-info {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .booking-info h3 {
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .booking-info p {
            margin-bottom: 5px;
            color: #555;
        }
        
        /* Delivery Method Selection */
        .delivery-options {
            display: flex;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .delivery-option {
            flex: 1;
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .delivery-option:hover {
            border-color: #3498db;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .delivery-option.selected {
            border-color: #3498db;
            background-color: #e8f4fd;
        }
        
        .delivery-option i {
            font-size: 32px;
            margin-bottom: 10px;
            color: #3498db;
        }
        
        .delivery-option h3 {
            margin-bottom: 5px;
            font-size: 16px;
        }
        
        .delivery-option p {
            font-size: 14px;
            color: #777;
        }
        
        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal {
            background: white;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            padding: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-close {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            color: #777;
            cursor: pointer;
            transition: color 0.3s;
        }
        
        .modal-close:hover {
            color: #e74c3c;
        }
        
        .modal h2 {
            margin-bottom: 20px;
            color: #2c3e50;
            text-align: center;
        }
        
        .modal-content {
            margin-bottom: 20px;
        }
        
        .delivery-details {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .delivery-details h3 {
            margin-bottom: 10px;
            color: #3498db;
        }
        
        @media (max-width: 768px) {
            .delivery-options {
                flex-direction: column;
            }
            
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <a href="user_dashboard.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
    
    <div class="container">
        <h2><i class="fas fa-box"></i> Book Your Commodity</h2>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <div class="booking-info">
            <h3>Booking Information</h3>
            <p>• Book your commodity shipment with FreightX for reliable and efficient transportation.</p>
            <p>• Choose from three delivery methods based on your needs.</p>
            <p>• Pricing varies based on weight, distance, and delivery method.</p>
        </div>
        
        <div id="priceEstimate" class="price-estimate">
            <h3>Estimated Price</h3>
            <p id="estimatedPrice">₹0</p>
        </div>
        
        <!-- Delivery Method Selection -->
        <h3 style="margin-bottom: 15px; color: #2c3e50;">Select Delivery Method:</h3>
        <div class="delivery-options">
            <div class="delivery-option" data-method="delivery_partner" onclick="selectDeliveryMethod(this, 'delivery_partner')">
                <i class="fas fa-people-carry"></i>
                <h3>Delivery Partner</h3>
                <p>Best for small shipments up to 10 tons</p>
            </div>
            
            <div class="delivery-option" data-method="truck" onclick="selectDeliveryMethod(this, 'truck')">
                <i class="fas fa-truck"></i>
                <h3>Truck</h3>
                <p>Ideal for medium loads up to 50 tons</p>
            </div>
            
            <div class="delivery-option" data-method="train" onclick="selectDeliveryMethod(this, 'train')">
                <i class="fas fa-train"></i>
                <h3>Train</h3>
                <p>Best for bulk shipments over 50 tons</p>
            </div>
        </div>
    </div>
    
    <!-- Modal for Booking Form -->
    <div class="modal-overlay" id="bookingModal">
        <div class="modal">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Book Your Shipment</h2>
            
            <div class="modal-content">
                <div class="delivery-details" id="deliveryDetails">
                    <h3>Delivery Details</h3>
                    <p id="deliveryMethodDesc"></p>
                </div>
                
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" id="bookingForm">
                    <input type="hidden" name="delivery_method" id="deliveryMethodInput">
                    
                    <div class="form-group">
                        <label for="commodity_type">Commodity Type:</label>
                        <select name="commodity_type" id="commodity_type" required>
                            <option value="">Select Commodity</option>
                            <option value="Grains">Grains</option>
                            <option value="Coal">Coal</option>
                            <option value="Cement">Cement</option>
                            <option value="Steel">Steel</option>
                            <option value="Fertilizers">Fertilizers</option>
                            <option value="Chemicals">Chemicals</option>
                            <option value="Minerals">Minerals</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="weight">Weight (in Tons):</label>
                        <input type="number" name="weight" id="weight" min="1" max="1000" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="starting_station">Starting Station:</label>
                        <select name="starting_station" id="starting_station" required>
                            <option value="">Select Starting Station</option>
                            <option value="New Delhi">New Delhi</option>
                            <option value="Mumbai Central">Mumbai Central</option>
                            <option value="Howrah Junction">Howrah Junction</option>
                            <option value="Chennai Central">Chennai Central</option>
                            <option value="Kolkata">Kolkata</option>
                            <option value="Bangalore City">Bangalore City</option>
                            <option value="Hyderabad Deccan">Hyderabad Deccan</option>
                            <option value="Ahmedabad Junction">Ahmedabad Junction</option>
                            <option value="Pune Junction">Pune Junction</option>
                            <option value="Lucknow Junction">Lucknow Junction</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="destination_station">Destination Station:</label>
                        <select name="destination_station" id="destination_station" required>
                            <option value="">Select Destination Station</option>
                            <option value="New Delhi">New Delhi</option>
                            <option value="Mumbai Central">Mumbai Central</option>
                            <option value="Howrah Junction">Howrah Junction</option>
                            <option value="Chennai Central">Chennai Central</option>
                            <option value="Kolkata">Kolkata</option>
                            <option value="Bangalore City">Bangalore City</option>
                            <option value="Hyderabad Deccan">Hyderabad Deccan</option>
                            <option value="Ahmedabad Junction">Ahmedabad Junction</option>
                            <option value="Pune Junction">Pune Junction</option>
                            <option value="Lucknow Junction">Lucknow Junction</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="booking_date">Booking Date:</label>
                        <input type="date" name="booking_date" id="booking_date" min="<?php echo $min_date; ?>" max="<?php echo $max_date; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="notes">Additional Notes:</label>
                        <textarea name="notes" id="notes" rows="3" placeholder="Special handling instructions or other requirements..."></textarea>
                    </div>
                    
                    <div id="modalPriceEstimate" class="price-estimate" style="display: block;">
                        <h3>Estimated Price</h3>
                        <p id="modalEstimatedPrice">₹0</p>
                    </div>
                    
                    <button type="submit">Proceed to Payment</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Variables to store delivery method details
        const deliveryMethods = {
            'delivery_partner': {
                title: 'Delivery Partner Booking',
                description: 'Our delivery partners provide door-to-door service for smaller shipments up to 10 tons. Ideal for local and regional deliveries with quick turnaround times.',
                pricePerTon: 150,
                maxWeight: 10
            },
            'truck': {
                title: 'Truck Freight Booking',
                description: 'Our truck fleet handles medium-sized shipments up to 50 tons. Perfect for inter-city transport with flexible scheduling and routing options.',
                pricePerTon: 120,
                maxWeight: 50
            },
            'train': {
                title: 'Train Freight Booking',
                description: 'Rail transport is optimal for bulk shipments over 50 tons. Most cost-effective for long distances with fixed schedules and routes.',
                pricePerTon: 80,
                minWeight: 50
            }
        };
        
        // Function to select delivery method and open modal
        function selectDeliveryMethod(element, method) {
            // Remove selected class from all options
            document.querySelectorAll('.delivery-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            element.classList.add('selected');
            
            // Set delivery method in hidden input
            document.getElementById('deliveryMethodInput').value = method;
            
            // Update modal title and description
            document.getElementById('modalTitle').textContent = deliveryMethods[method].title;
            document.getElementById('deliveryMethodDesc').textContent = deliveryMethods[method].description;
            
            // Set min/max weight based on delivery method
            const weightInput = document.getElementById('weight');
            if (method === 'delivery_partner') {
                weightInput.max = 10;
                weightInput.min = 1;
            } else if (method === 'truck') {
                weightInput.max = 50;
                weightInput.min = 1;
            } else if (method === 'train') {
                weightInput.max = 1000;
                weightInput.min = 50;
            }
            
            // Open the modal
            openModal();
        }
        
        // Function to open modal
        function openModal() {
            document.getElementById('bookingModal').style.display = 'flex';
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }
        
        // Function to close modal
        function closeModal() {
            document.getElementById('bookingModal').style.display = 'none';
            document.body.style.overflow = 'auto'; // Enable scrolling
        }
        
        // Update price estimate when form values change
        document.addEventListener('DOMContentLoaded', function() {
            const weightInput = document.getElementById('weight');
            const startingStation = document.getElementById('starting_station');
            const destinationStation = document.getElementById('destination_station');
            const modalEstimatedPrice = document.getElementById('modalEstimatedPrice');
            const deliveryMethodInput = document.getElementById('deliveryMethodInput');
            
            function updatePriceEstimate() {
                const weight = parseFloat(weightInput.value) || 0;
                const start = startingStation.value;
                const destination = destinationStation.value;
                const method = deliveryMethodInput.value;
                
                if (weight > 0 && start && destination && start !== destination && method) {
                    // Get price per ton based on delivery method
                    const pricePerTon = deliveryMethods[method].pricePerTon;
                    
                    // Calculate total price
                    const totalPrice = weight * pricePerTon;
                    
                    modalEstimatedPrice.textContent = '₹' + totalPrice.toLocaleString();
                } else {
                    modalEstimatedPrice.textContent = '₹0';
                }
            }
            
            weightInput.addEventListener('input', updatePriceEstimate);
            startingStation.addEventListener('change', updatePriceEstimate);
            destinationStation.addEventListener('change', updatePriceEstimate);
            
            // Form validation
            const form = document.getElementById('bookingForm');
            form.addEventListener('submit', function(event) {
                const start = startingStation.value;
                const destination = destinationStation.value;
                const weight = parseFloat(weightInput.value) || 0;
                const method = deliveryMethodInput.value;
                
                if (start === destination) {
                    event.preventDefault();
                    alert('Starting and destination stations cannot be the same.');
                    return;
                }
                
                // Validate weight based on delivery method
                if (method === 'delivery_partner' && weight > 10) {
                    event.preventDefault();
                    alert('Delivery partner can only handle shipments up to 10 tons. Please select a different delivery method or reduce the weight.');
                    return;
                }
                
                if (method === 'truck' && weight > 50) {
                    event.preventDefault();
                    alert('Truck delivery can only handle shipments up to 50 tons. Please select train delivery for larger shipments.');
                    return;
                }
                
                if (method === 'train' && weight < 50) {
                    event.preventDefault();
                    alert('Train delivery requires a minimum of 50 tons. Please select a different delivery method for smaller shipments.');
                    return;
                }
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', function(event) {
                const modal = document.getElementById('bookingModal');
                if (event.target === modal) {
                    closeModal();
                }
            });
        });
    </script>
</body>
</html>
        