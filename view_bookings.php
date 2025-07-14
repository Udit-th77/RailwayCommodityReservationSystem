<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to view bookings.";
    exit();
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM bookings WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Bookings - FreightX</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #1e90ff, #00bfff);
            color: white;
            margin: 0;
            padding: 20px;
            text-align: center;
        }

        h2 {
            margin-top: 30px;
            font-size: 32px;
            color: #ffcc00;
        }

        table {
            width: 90%;
            margin: 30px auto;
            border-collapse: collapse;
            background: rgba(0, 0, 0, 0.85);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 14px 18px;
            border-bottom: 1px solid #444;
            color: white;
        }

        th {
            background-color: #222;
            font-size: 18px;
        }

        tr:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .no-bookings {
            font-size: 20px;
            margin-top: 50px;
            color: #ff8080;
        }
    </style>
</head>
<body>

<h2>Your Bookings</h2>

<?php if ($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>Commodity Name</th>
            <th>Weight (Tons)</th>
            <th>From</th>
            <th>To</th>
            <th>Booking Date</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['commodity_type']); ?></td>
            <td><?php echo htmlspecialchars($row['weight']); ?></td>
            <td><?php echo htmlspecialchars($row['source']); ?></td>
            <td><?php echo htmlspecialchars($row['destination']); ?></td>
            <td><?php echo htmlspecialchars($row['booking_date']); ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p class="no-bookings">No bookings found.</p>
<?php endif; ?>

</body>
</html>
