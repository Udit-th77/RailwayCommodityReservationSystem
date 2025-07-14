<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['commodity_type'] = $_POST['commodity_type'];
    $_SESSION['weight'] = $_POST['weight'];
    $_SESSION['starting_station'] = $_POST['starting_station'];
    $_SESSION['destination_station'] = $_POST['destination_station'];
    $_SESSION['total_price'] = $_POST['total_price'];
} else {
    header("Location: book_commodity.php");
    exit();
}

// Replace this with your actual PhonePe QR image link
$qr_code_url = "YOUR_QR_IMAGE_URL";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Payment</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, sans-serif; }
        body { background: linear-gradient(135deg, #2c3e50, #3498db); color: white; text-align: center; padding: 20px; }
        
        .container {
            background: rgba(255, 255, 255, 0.2);
            padding: 20px; border-radius: 10px;
            width: 50%; margin: auto; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        .container h2 { margin-bottom: 20px; }
        .info { margin-bottom: 15px; font-size: 18px; }
        .price { font-size: 22px; font-weight: bold; color: #FFD700; }
        .qr-code { margin-top: 20px; }
        button {
            background: #FF4500; color: white; padding: 10px 15px;
            border: none; border-radius: 5px; cursor: pointer;
            margin-top: 20px;
        }
        button:hover { background: #e63900; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Complete Your Payment</h2>
        <div class="info">Commodity: <b><?php echo $_SESSION['commodity_type']; ?></b></div>
        <div class="info">Weight: <b><?php echo $_SESSION['weight']; ?> Tons</b></div>
        <div class="info">From: <b><?php echo $_SESSION['starting_station']; ?></b></div>
        <div class="info">To: <b><?php echo $_SESSION['destination_station']; ?></b></div>
        <div class="price">Total Amount: ₹<?php echo number_format($_SESSION['total_price'], 2); ?></div>

        <h3>Scan to Pay via PhonePe</h3>
        <div class="qr-code">
            <img src="<?php echo $qr_code_url; ?>" alt="PhonePe QR Code" width="250">
        </div>

        <form action="commodity_bookings.php" method="POST">
            <input type="hidden" name="commodity_type" value="<?php echo $_SESSION['commodity_type']; ?>">
            <input type="hidden" name="weight" value="<?php echo $_SESSION['weight']; ?>">
            <input type="hidden" name="starting_station" value="<?php echo $_SESSION['starting_station']; ?>">
            <input type="hidden" name="destination_station" value="<?php echo $_SESSION['destination_station']; ?>">
            <input type="hidden" name="total_price" value="<?php echo $_SESSION['total_price']; ?>">
            <input type="hidden" name="payment_method" value="PhonePe">
            <button type="submit">Confirm Payment</button>
        </form>
    </div>
</body>
</html>
