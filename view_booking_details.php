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

// Get booking details with join to payment_transactions
$stmt = $conn->prepare("SELECT b.*, pt.payment_method, pt.transaction_id, pt.payment_status as payment_transaction_status, 
                        pt.gateway_response, pt.created_at as payment_date
                        FROM bookings b 
                        LEFT JOIN payment_transactions pt ON b.id = pt.booking_id 
                        WHERE b.id = ? AND b.user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Booking not found or doesn't belong to this user
    header("Location: user_dashboard.php");
    exit();
}

$booking = $result->fetch_assoc();

// Format payment method for display
$payment_methods = [
    'razorpay' => 'Razorpay',
    'paytm' => 'Paytm',
    'paypal' => 'PayPal',
    'stripe' => 'Stripe',
    'cod' => 'Cash on Delivery',
    'bank_transfer' => 'Bank Transfer'
];

$payment_method_display = $payment_methods[$booking['payment_method']] ?? ucfirst($booking['payment_method'] ?? 'Unknown');

// Define status colors and descriptions
$status_colors = [
    'pending' => '#f39c12',
    'confirmed' => '#2ecc71',
    'in_transit' => '#3498db',
    'delivered' => '#27ae60',
    'cancelled' => '#e74c3c'
];

$status_descriptions = [
    'pending' => 'Your booking is pending confirmation.',
    'confirmed' => 'Your booking has been confirmed and is being processed.',
    'in_transit' => 'Your shipment is in transit.',
    'delivered' => 'Your shipment has been delivered successfully.',
    'cancelled' => 'This booking has been cancelled.'
];

$payment_status_colors = [
    'pending' => '#f39c12',
    'completed' => '#2ecc71',
    'failed' => '#e74c3c',
    'refunded' => '#3498db'
];

// Get tracking information (simulated for this example)
// In a real application, you would fetch this from a tracking table
$tracking_events = [];
$current_status = $booking['status'];

// Generate tracking events based on booking status
if ($current_status == 'confirmed' || $current_status == 'in_transit' || $current_status == 'delivered') {
    $tracking_events[] = [
        'status' => 'Booking Created',
        'date' => $booking['created_at'],
        'description' => 'Your booking has been created successfully.',
        'location' => $booking['source']
    ];
    
    $tracking_events[] = [
        'status' => 'Payment ' . ucfirst($booking['payment_status']),
        'date' => $booking['payment_date'] ?? $booking['created_at'],
        'description' => 'Payment has been ' . $booking['payment_status'] . '.',
        'location' => '-'
    ];
}

if ($current_status == 'confirmed' || $current_status == 'in_transit' || $current_status == 'delivered') {
    $tracking_events[] = [
        'status' => 'Booking Confirmed',
        'date' => date('Y-m-d H:i:s', strtotime($booking['created_at'] . ' +1 hour')),
        'description' => 'Your booking has been confirmed and is being processed.',
        'location' => $booking['source']
    ];
}

if ($current_status == 'in_transit' || $current_status == 'delivered') {
    $tracking_events[] = [
        'status' => 'Shipment Picked Up',
        'date' => date('Y-m-d H:i:s', strtotime($booking['created_at'] . ' +1 day')),
        'description' => 'Your shipment has been picked up and is in transit.',
        'location' => $booking['source']
    ];
    
    // Add some transit events
    $transit_points = getTransitPoints($booking['source'], $booking['destination']);
    $transit_date = strtotime($booking['created_at'] . ' +2 days');
    
    foreach ($transit_points as $point) {
        $tracking_events[] = [
            'status' => 'In Transit',
            'date' => date('Y-m-d H:i:s', $transit_date),
            'description' => 'Your shipment is passing through ' . $point . '.',
            'location' => $point
        ];
        $transit_date += 86400; // Add one day
    }
}

if ($current_status == 'delivered') {
    $tracking_events[] = [
        'status' => 'Out for Delivery',
        'date' => date('Y-m-d H:i:s', strtotime($booking['created_at'] . ' +5 days')),
        'description' => 'Your shipment is out for delivery.',
        'location' => $booking['destination']
    ];
    
    $tracking_events[] = [
        'status' => 'Delivered',
        'date' => date('Y-m-d H:i:s', strtotime($booking['created_at'] . ' +5 days 4 hours')),
        'description' => 'Your shipment has been delivered successfully.',
        'location' => $booking['destination']
    ];
}

// Sort tracking events by date (newest first)
usort($tracking_events, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Function to get transit points between source and destination
function getTransitPoints($source, $destination) {
    // This is a simplified example. In a real application, you would have a more sophisticated
    // way to determine transit points based on routes, etc.
    $all_stations = [
        'New Delhi', 'Mumbai Central', 'Howrah Junction', 'Chennai Central', 
        'Kolkata', 'Bangalore City', 'Hyderabad Deccan', 'Ahmedabad Junction', 
        'Pune Junction', 'Lucknow Junction'
    ];
    
    // Get random transit points
    $transit_points = [];
    $num_points = rand(1, 3); // Random number of transit points
    
    for ($i = 0; $i < $num_points; $i++) {
        $random_station = $all_stations[array_rand($all_stations)];
        if ($random_station != $source && $random_station != $destination && !in_array($random_station, $transit_points)) {
            $transit_points[] = $random_station;
        }
    }
    
    return $transit_points;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details - FreightX</title>
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
        }
        
        .container {
            max-width: 1000px;
            margin: 40px auto;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
            position: relative;
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
        
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        
        .booking-id {
            font-size: 16px;
            margin-top: 5px;
            color: #bdc3c7;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 10px;
            background: <?php echo $status_colors[$booking['status']] ?? '#7f8c8d'; ?>;
            color: white;
        }
        
        .content {
            display: flex;
            flex-wrap: wrap;
        }
        
        .booking-details, .payment-details {
            flex: 1;
            min-width: 300px;
            padding: 20px;
        }
        
        .section-title {
            font-size: 20px;
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        
        .detail-item {
            margin-bottom: 15px;
        }
        
        .detail-label {
            font-weight: bold;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .detail-value {
            color: #2c3e50;
            font-size: 16px;
        }
        
        .route-info {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            position: relative;
        }
        
        .route-line {
            position: absolute;
            top: 60px;
            left: 30px;
            bottom: 60px;
            width: 4px;
            background: #3498db;
        }
        
        .route-points {
            display: flex;
            justify-content: space-between;
            position: relative;
            z-index: 1;
        }
        
        .route-point {
            text-align: center;
            flex: 1;
        }
        
        .point-icon {
            width: 40px;
            height: 40px;
            background: #3498db;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 10px;
            color: white;
            font-size: 18px;
        }
        
        .point-label {
            font-weight: bold;
            color: #2c3e50;
        }
        
        .tracking-section {
            padding: 20px;
            background: #f9f9f9;
        }
        
        .tracking-title {
            font-size: 20px;
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        
        .tracking-timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline-line {
            position: absolute;
            top: 0;
            left: 10px;
            bottom: 0;
            width: 2px;
            background: #bdc3c7;
        }
        
        .timeline-event {
            position: relative;
            margin-bottom: 30px;
        }
        
        .timeline-event:last-child {
            margin-bottom: 0;
        }
        
        .event-dot {
            position: absolute;
            left: -30px;
            top: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #3498db;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #3498db;
        }
        
        .event-content {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .event-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .event-status {
            font-weight: bold;
            color: #3498db;
        }
        
        .event-date {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .event-description {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .event-location {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .event-location i {
            margin-right: 5px;
        }
        
        .payment-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: bold;
            background: <?php echo $payment_status_colors[$booking['payment_status']] ?? '#7f8c8d'; ?>;
            color: white;
        }
        
        .actions {
            padding: 20px;
            background: #f5f5f5;
            text-align: center;
            border-top: 1px solid #ddd;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 0 10px;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #2980b9;
        }
        
        .btn-secondary {
            background: #7f8c8d;
        }
        
        .btn-secondary:hover {
            background: #6c7a7d;
        }
        
              .btn-danger {
            background: #e74c3c;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn i {
            margin-right: 5px;
        }
        
        @media (max-width: 768px) {
            .content {
                flex-direction: column;
            }
            
            .booking-details, .payment-details {
                width: 100%;
            }
            
            .route-points {
                flex-direction: column;
                gap: 20px;
            }
            
            .route-line {
                left: 50%;
                width: 2px;
                top: 40px;
                bottom: 40px;
            }
        }
    </style>
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&libraries=places"></script>
<script>
    // Initialize the map when the page loads
    function initMap() {
        // Check if we have source and destination
        const source = "<?php echo htmlspecialchars($booking['source']); ?>";
        const destination = "<?php echo htmlspecialchars($booking['destination']); ?>";
        
        // Create a map centered on the source location
        const geocoder = new google.maps.Geocoder();
        
        geocoder.geocode({ 'address': source + ', India' }, function(results, status) {
            if (status === 'OK') {
                const mapOptions = {
                    zoom: 12,
                    center: results[0].geometry.location,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };
                
                const map = new google.maps.Map(document.getElementById('map'), mapOptions);
                
                // Add a marker for the source location
                const sourceMarker = new google.maps.Marker({
                    position: results[0].geometry.location,
                    map: map,
                    title: source,
                    icon: {
                        url: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
                    }
                });
                
                // Add an info window for the source marker
                const sourceInfoWindow = new google.maps.InfoWindow({
                    content: '<div><strong>Source:</strong> ' + source + '</div>'
                });
                
                sourceMarker.addListener('click', function() {
                    sourceInfoWindow.open(map, sourceMarker);
                });
                
                // If we have a destination, try to show the route
                if (destination && destination !== source) {
                    geocoder.geocode({ 'address': destination + ', India' }, function(results, status) {
                        if (status === 'OK') {
                            // Add a marker for the destination
                            const destMarker = new google.maps.Marker({
                                position: results[0].geometry.location,
                                map: map,
                                title: destination,
                                icon: {
                                    url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
                                }
                            });
                            
                            // Add an info window for the destination marker
                            const destInfoWindow = new google.maps.InfoWindow({
                                content: '<div><strong>Destination:</strong> ' + destination + '</div>'
                            });
                            
                            destMarker.addListener('click', function() {
                                destInfoWindow.open(map, destMarker);
                            });
                            
                            // Try to show the route
                            const directionsService = new google.maps.DirectionsService();
                            const directionsRenderer = new google.maps.DirectionsRenderer({
                                map: map,
                                suppressMarkers: true // We already have our own markers
                            });
                            
                            const request = {
                                origin: source + ', India',
                                destination: destination + ', India',
                                travelMode: google.maps.TravelMode.DRIVING
                            };
                            
                            directionsService.route(request, function(result, status) {
                                if (status === 'OK') {
                                    directionsRenderer.setDirections(result);
                                    
                                    // Adjust the map bounds to show both markers
                                    const bounds = new google.maps.LatLngBounds();
                                    bounds.extend(sourceMarker.getPosition());
                                    bounds.extend(destMarker.getPosition());
                                    map.fitBounds(bounds);
                                }
                            });
                        }
                    });
                }
            }
        });
    }
    
    // Load the map when the page is ready
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('map')) {
            initMap();
        }
    });
</script>

</head>
<body>
    <div class="container">
        <div class="header">
            <a href="user_dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            <h1>Booking Details</h1>
            <div class="booking-id">Ticket #<?php echo htmlspecialchars($booking['ticket_number']); ?></div>
            <div class="status-badge"><?php echo ucfirst(htmlspecialchars($booking['status'])); ?></div>
        </div>
        
        <div class="content">
            <div class="booking-details">
                <h2 class="section-title">Shipment Information</h2>
                
                <div class="detail-item">
                    <div class="detail-label">Commodity Type</div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking['commodity_type']); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Weight</div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking['weight']); ?> Tons</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Booking Date</div>
                    <div class="detail-value"><?php echo date('d M Y', strtotime($booking['booking_date'])); ?></div>
                </div>
                
                <?php if (!empty($booking['delivery_date'])): ?>
                <div class="detail-item">
                    <div class="detail-label">Expected Delivery Date</div>
                    <div class="detail-value"><?php echo date('d M Y', strtotime($booking['delivery_date'])); ?></div>
                </div>
                <?php endif; ?>
                
                <div class="route-info">
                    <div class="route-line"></div>
                    <div class="route-points">
                        <div class="route-point">
                            <div class="point-icon"><i class="fas fa-warehouse"></i></div>
                            <div class="point-label">Origin</div>
                            <div><?php echo htmlspecialchars($booking['source']); ?></div>
                        </div>
                        
                        <div class="route-point">
                            <div class="point-icon"><i class="fas fa-flag-checkered"></i></div>
                            <div class="point-label">Destination</div>
                            <div><?php echo htmlspecialchars($booking['destination']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="payment-details">
                <h2 class="section-title">Payment Information</h2>
                
                <div class="detail-item">
                    <div class="detail-label">Payment Method</div>
                    <div class="detail-value"><?php echo htmlspecialchars($payment_method_display); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Amount</div>
                    <div class="detail-value">₹<?php echo number_format($booking['price'], 2); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Payment Status</div>
                    <div class="detail-value">
                        <span class="payment-badge"><?php echo ucfirst(htmlspecialchars($booking['payment_status'])); ?></span>
                    </div>
                </div>
                
                <?php if (!empty($booking['transaction_id'])): ?>
                <div class="detail-item">
                    <div class="detail-label">Transaction ID</div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking['transaction_id']); ?></div>
                </div>
                <?php endif; ?>
                
                <div class="detail-item">
                    <div class="detail-label">Order ID</div>
                    <div class="detail-value"><?php echo htmlspecialchars($booking['order_id']); ?></div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Booking Status</div>
                    <div class="detail-value"><?php echo ucfirst(htmlspecialchars($booking['status'])); ?></div>
                </div>
                
                <?php if (!empty($status_descriptions[$booking['status']])): ?>
                <div class="detail-item">
                    <div class="detail-label">Status Description</div>
                    <div class="detail-value"><?php echo htmlspecialchars($status_descriptions[$booking['status']]); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
       <div class="tracking-section">
    <h2 class="tracking-title">Tracking Information</h2>
    
    <div class="tracking-timeline">
        <div class="timeline-line"></div>
        
        <?php if (empty($tracking_events)): ?>
            <p>No tracking information available yet.</p>
            
            <!-- Add the map container -->
            <div id="map" style="width: 100%; height: 400px; margin-top: 20px; border-radius: 8px;"></div>
            
            <p style="margin-top: 15px; color: #7f8c8d; font-style: italic;">
                <i class="fas fa-info-circle"></i> The map shows the source location and planned route for your shipment.
            </p>
        <?php else: ?>
            <?php foreach ($tracking_events as $event): ?>
                <div class="timeline-event">
                    <div class="event-dot"></div>
                    <div class="event-content">
                        <div class="event-header">
                            <div class="event-status"><?php echo htmlspecialchars($event['status']); ?></div>
                            <div class="event-date"><?php echo date('d M Y, h:i A', strtotime($event['date'])); ?></div>
                        </div>
                        <div class="event-description"><?php echo htmlspecialchars($event['description']); ?></div>
                        <div class="event-location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

        <div class="actions">
    <?php if ($booking['status'] == 'pending'): ?>
        <a href="cancel_booking.php?id=<?php echo $booking_id; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this booking?');">
            <i class="fas fa-times"></i> Cancel Booking
        </a>
    <?php endif; ?>
    
    <a href="track_shipment.php?ticket_number=<?php echo urlencode($booking['ticket_number']); ?>" class="btn">
        <i class="fas fa-truck-moving"></i> Track Shipment
    </a>
    
    <a href="download_invoice.php?id=<?php echo $booking_id; ?>" class="btn">
        <i class="fas fa-file-invoice"></i> Download Invoice
    </a>
    
    <a href="contact_support.php?booking_id=<?php echo $booking_id; ?>" class="btn btn-secondary">
        <i class="fas fa-headset"></i> Contact Support
    </a>
</div>

    </div>
</body>
</html>
