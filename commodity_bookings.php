<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FreightX - Payment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #ff9966, #ff5e62, #1e90ff, #32cd32);
            color: white;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        .payment-container {
            background: rgba(0, 0, 0, 0.8);
            width: 400px;
            margin: 80px auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.4);
        }

        h2 {
            margin-bottom: 20px;
            color: #ffcc00;
        }

        input[type="text"], input[type="number"], input[type="submit"] {
            width: 90%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 6px;
            font-size: 16px;
        }

        input[type="submit"] {
            background-color: #1e90ff;
            color: white;
            cursor: pointer;
            font-weight: bold;
        }

        input[type="submit"]:hover {
            background-color: #005f73;
        }

        label {
            display: block;
            text-align: left;
            margin-top: 10px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="payment-container">
    <h2>Payment Information</h2>
    <form action="process_payment.php" method="POST">
        <label for="card_name">Name on Card</label>
        <input type="text" name="card_name" id="card_name" required>

        <label for="card_number">Card Number</label>
        <input type="text" name="card_number" id="card_number" pattern="\d{16}" maxlength="16" required>

        <label for="expiry">Expiry Date (MM/YY)</label>
        <input type="text" name="expiry" id="expiry" pattern="\d{2}/\d{2}" required>

        <label for="cvv">CVV</label>
        <input type="number" name="cvv" id="cvv" maxlength="3" required>

        <input type="submit" value="Pay Now">
    </form>
</div>

</body>
</html>
