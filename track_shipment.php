<?php
session_start();
include 'db_connect.php';

$error = '';
$tracking_result = null;
$tracking_events = [];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_number'])) {
    $ticket_number = trim($_POST['ticket_number']);
    
    if (empty($ticket_number)) {
        $error = "Please enter a tracking number.";
    } else {
        // Query to get booking details by ticket number
        $stmt = $conn->prepare("SELECT b.*, pt.payment_method, pt.transaction_id, pt.payment_status as payment_transaction_status 
                               FROM bookings b 
                               LEFT JOIN payment_transactions pt ON b.id = pt.booking_id 
                               WHERE b.ticket_number = ?");
        $stmt->bind_param("s", $ticket_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = "No shipment found with this tracking number. Please check and try again.";
        } else {
            $tracking_result = $result->fetch_assoc();
            
            // Generate tracking events based on booking status
            $current_status = $tracking_result['status'];
            
            // Generate tracking events based on booking status
            if ($current_status == 'confirmed' || $current_status == 'in_transit' || $current_status == 'delivered') {
                $tracking_events[] = [
                    'status' => 'Booking Created',
                    'date' => $tracking_result['created_at'],
                    'description' => 'Your booking has been created successfully.',
                    'location' => $tracking_result['source']
                ];
                
                $tracking_events[] = [
                    'status' => 'Payment ' . ucfirst($tracking_result['payment_status']),
                    'date' => date('Y-m-d H:i:s', strtotime($tracking_result['created_at'] . ' +1 hour')),
                    'description' => 'Payment has been ' . $tracking_result['payment_status'] . '.',
                    'location' => '-'
                ];
            }

            if ($current_status == 'confirmed' || $current_status == 'in_transit' || $current_status == 'delivered') {
                $tracking_events[] = [
                    'status' => 'Booking Confirmed',
                    'date' => date('Y-m-d H:i:s', strtotime($tracking_result['created_at'] . ' +2 hours')),
                    'description' => 'Your booking has been confirmed and is being processed.',
                    'location' => $tracking_result['source']
                ];
            }

            if ($current_status == 'in_transit' || $current_status == 'delivered') {
                $tracking_events[] = [
                    'status' => 'Shipment Picked Up',
                    'date' => date('Y-m-d H:i:s', strtotime($tracking_result['created_at'] . ' +1 day')),
                    'description' => 'Your shipment has been picked up and is in transit.',
                    'location' => $tracking_result['source']
                ];
                
                // Add some transit events
                $transit_points = getTransitPoints($tracking_result['source'], $tracking_result['destination']);
                $transit_date = strtotime($tracking_result['created_at'] . ' +2 days');
                
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
                    'date' => date('Y-m-d H:i:s', strtotime($tracking_result['created_at'] . ' +5 days')),
                    'description' => 'Your shipment is out for delivery.',
                    'location' => $tracking_result['destination']
                ];
                
                $tracking_events[] = [
                    'status' => 'Delivered',
                    'date' => date('Y-m-d H:i:s', strtotime($tracking_result['created_at'] . ' +5 days 4 hours')),
                    'description' => 'Your shipment has been delivered successfully.',
                    'location' => $tracking_result['destination']
                ];
            }

            // Sort tracking events by date (newest first)
            usort($tracking_events, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
        }
    }
}

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
    <title>Track Your Shipment - FreightX</title>
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
            padding: 30px 20px;
            text-align: center;
        }
        
        .header h1 {
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .header p {
            color: #bdc3c7;
            font-size: 16px;
        }
        
        .tracking-form {
            padding: 30px;
            background: #f9f9f9;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        
        .tracking-form form {
            display: flex;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .tracking-form input {
            flex: 1;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 5px 0 0 5px;
            font-size: 16px;
        }
        
        .tracking-form button {
            padding: 15px 25px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            transition: background 0.3s;
        }
        
        .tracking-form button:hover {
            background: #2980b9;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px auto;
            max-width: 600px;
            text-align: center;
        }
        
        .tracking-result {
            padding: 30px;
        }
        
        .shipment-info {
            background: #f5f5f5;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .shipment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .shipment-title {
            font-size: 22px;
            color: #2c3e50;
        }
        
        .tracking-number {
            font-size: 16px;
            color: #7f8c8d;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
            color: white;
            font-size: 14px;
        }
        
        .status-pending {
            background: #f39c12;
        }
        
        .status-confirmed {
            background: #2ecc71;
        }
        
        .status-in-transit {
            background: #3498db;
        }
        
        .status-delivered {
            background: #27ae60;
        }
        
        .status-cancelled {
            background: #e74c3c;
        }
        
        .shipment-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .detail-group {
            flex: 1;
            min-width: 200px;
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
        
        .tracking-timeline {
            margin-top: 30px;
            position: relative;
            padding-left: 30px;
        }
        
        .timeline-title {
            font-size: 22px;
            margin-bottom: 20px;
            color: #2c3e50;
        }
        
        .timeline-line {
            position: absolute;
            top: 50px;
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
        
        .no-tracking {
            text-align: center;
            padding: 50px 20px;
            color: #7f8c8d;
        }
        
        .no-tracking i {
            font-size: 50px;
            margin-bottom: 20px;
            color: #bdc3c7;
        }
        
        .no-tracking h3 {
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .home-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-size: 16px;
            display: flex;
            align-items: center;
        }
        
        .home-link i {
            margin-right: 5px;
        }
        
               @media (max-width: 768px) {
            .tracking-form form {
                flex-direction: column;
            }
            
            .tracking-form input {
                border-radius: 5px;
                margin-bottom: 10px;
            }
            
            .tracking-form button {
                border-radius: 5px;
            }
            
            .shipment-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
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
</head>
<body>
    <a href="index.php" class="home-link"><i class="fas fa-home"></i> Back to Home</a>
    
    <div class="container">
        <div class="header">
            <h1>Track Your Shipment</h1>
            <p>Enter your tracking number to get real-time updates on your shipment</p>
        </div>
        
        <div class="tracking-form">
            <form method="POST" action="">
              <input type="text" name="ticket_number" placeholder="Enter your tracking number" value="<?php echo isset($_POST['ticket_number']) ? htmlspecialchars($_POST['ticket_number']) : (isset($_GET['ticket_number']) ? htmlspecialchars($_GET['ticket_number']) : ''); ?>">
<button type="submit"><i class="fas fa-search"></i> Track</button>

            </form>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($tracking_result): ?>
            <div class="tracking-result">
                <div class="shipment-info">
                    <div class="shipment-header">
                        <div>
                            <div class="shipment-title">Shipment Details</div>
                            <div class="tracking-number">Tracking #: <?php echo htmlspecialchars($tracking_result['ticket_number']); ?></div>
                        </div>
                        
                        <?php
                        $status_classes = [
                            'pending' => 'status-pending',
                            'confirmed' => 'status-confirmed',
                            'in_transit' => 'status-in-transit',
                            'delivered' => 'status-delivered',
                            'cancelled' => 'status-cancelled'
                        ];
                        $status_class = $status_classes[$tracking_result['status']] ?? '';
                        ?>
                        
                        <div class="status-badge <?php echo $status_class; ?>">
                            <?php echo ucfirst(htmlspecialchars($tracking_result['status'])); ?>
                        </div>
                    </div>
                    
                    <div class="shipment-details">
                        <div class="detail-group">
                            <div class="detail-label">Commodity Type</div>
                            <div class="detail-value"><?php echo htmlspecialchars($tracking_result['commodity_type']); ?></div>
                        </div>
                        
                        <div class="detail-group">
                            <div class="detail-label">Weight</div>
                            <div class="detail-value"><?php echo htmlspecialchars($tracking_result['weight']); ?> Tons</div>
                        </div>
                        
                        <div class="detail-group">
                            <div class="detail-label">Booking Date</div>
                            <div class="detail-value"><?php echo date('d M Y', strtotime($tracking_result['booking_date'])); ?></div>
                        </div>
                        
                        <div class="detail-group">
                            <div class="detail-label">Payment Status</div>
                            <div class="detail-value"><?php echo ucfirst(htmlspecialchars($tracking_result['payment_status'])); ?></div>
                        </div>
                    </div>
                    
                    <div class="route-info">
                        <div class="route-line"></div>
                        <div class="route-points">
                            <div class="route-point">
                                <div class="point-icon"><i class="fas fa-warehouse"></i></div>
                                <div class="point-label">Origin</div>
                                <div><?php echo htmlspecialchars($tracking_result['source']); ?></div>
                            </div>
                            
                            <div class="route-point">
                                <div class="point-icon"><i class="fas fa-flag-checkered"></i></div>
                                <div class="point-label">Destination</div>
                                <div><?php echo htmlspecialchars($tracking_result['destination']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tracking-timeline">
                    <h2 class="timeline-title">Tracking History</h2>
                    <div class="timeline-line"></div>
                    
                    <?php if (empty($tracking_events)): ?>
                        <div class="no-tracking">
                            <i class="fas fa-truck"></i>
                            <h3>No tracking information available yet</h3>
                            <p>Tracking information will be available once your shipment is processed.</p>
                        </div>
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
        <?php elseif ($_SERVER['REQUEST_METHOD'] !== 'POST'): ?>
            <div class="no-tracking">
                <i class="fas fa-search"></i>
                <h3>Enter your tracking number above</h3>
                <p>Enter your tracking number to get the latest updates on your shipment.</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
