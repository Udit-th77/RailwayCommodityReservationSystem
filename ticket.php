<?php
session_start();
include 'db_connect.php'; // Ensure database connection is included

// Check if booking_id is set and valid
if (!isset($_GET['booking_id']) || empty($_GET['booking_id'])) {
    die("Invalid Request!");
}

$booking_id = $_GET['booking_id'];

// Check if database connection exists
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Use prepared statements to prevent SQL injection
$stmt = $conn->prepare("SELECT * FROM commodity_bookings WHERE id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Booking not found!");
}

$booking = $result->fetch_assoc();
$stmt->close();
$conn->close(); // Close connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            text-align: center;
            padding: 20px;
        }
        .ticket {
            background: white;
            width: 60%;
            margin: auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        h2 {
            color: #008CBA;
        }
        .ticket-details {
            text-align: left;
            margin-top: 20px;
        }
        .ticket-details p {
            font-size: 18px;
            margin: 8px 0;
        }
        .print-button {
            margin-top: 20px;
            padding: 10px 15px;
            background: #008CBA;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .print-button:hover {
            background: #005f73;
        }
    </style>
</head>
<body>

<div class="ticket">
    <h2>Booking Confirmation</h2>
    <hr>
    <div class="ticket-details">
        <p><strong>Booking ID:</strong> <?php echo htmlspecialchars($booking['id']); ?></p>
        <p><strong>Commodity Type:</strong> <?php echo htmlspecialchars($booking['commodity_type']); ?></p>
        <p><strong>Weight:</strong> <?php echo htmlspecialchars($booking['weight']); ?> Tons</p>
        <p><strong>Starting Station:</strong> <?php echo htmlspecialchars($booking['start_station']); ?></p>
        <p><strong>Destination:</strong> <?php echo htmlspecialchars($booking['destination']); ?></p>
        <p><strong>Booking Date:</strong> <?php echo htmlspecialchars($booking['booking_date']); ?></p>
        <p><strong>Notes:</strong> <?php echo !empty($booking['notes']) ? htmlspecialchars($booking['notes']) : "N/A"; ?></p>
    </div>
    <button class="print-button" onclick="window.print()">Print Ticket</button>
</div>

</body>
</html>
