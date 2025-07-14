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
                        pt.gateway_response, pt.created_at as payment_date, u.name as customer_name, u.email as customer_email
                        FROM bookings b
                        LEFT JOIN payment_transactions pt ON b.id = pt.booking_id
                        LEFT JOIN users u ON b.user_id = u.id
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

// Create company information
$company = [
    'name' => 'FreightX Logistics',
    'address' => '123 Transport Avenue, Railway District',
    'city' => 'Mumbai, Maharashtra 400001',
    'phone' => '+91 9876543210',
    'email' => 'support@freightx.com',
    'website' => 'www.freightx.com',
    'gst' => 'GSTIN: 27AABCF1234A1Z5'
];

// Calculate GST (assuming 18% GST)
$subtotal = $booking['price'] / 1.18; // Remove GST from total to get subtotal
$gst_amount = $booking['price'] - $subtotal;

// Set the content type to HTML
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #INV-<?php echo $booking['ticket_number']; ?> - FreightX</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #ddd;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .company-details {
            text-align: left;
        }
        
        .invoice-title {
            text-align: right;
            font-size: 24px;
            color: #3498db;
        }
        
        .customer-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .invoice-meta {
            text-align: right;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
            text-align: left;
            padding: 10px;
        }
        
        .items-table td {
            border-bottom: 1px solid #ddd;
            padding: 10px;
        }
        
        .totals-table {
            width: 40%;
            margin-left: auto;
            margin-bottom: 30px;
        }
        
        .totals-table td {
            padding: 5px;
        }
        
        .grand-total {
            font-weight: bold;
            font-size: 16px;
            background-color: #f8f9fa;
        }
         .notes {
            margin-top: 30px;
            font-size: 12px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #777;
            padding-top: 20px;
            border-top: 1px solid #ddd;
        }
        
        .print-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .print-button:hover {
            background-color: #2980b9;
        }
        
        @media print {
            .print-button, .back-button {
                display: none;
            }
            
            body {
                padding: 0;
            }
            
            .invoice-container {
                box-shadow: none;
                border: none;
            }
        }
        
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 8px 15px;
            background-color: #7f8c8d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .back-button:hover {
            background-color: #6c7a7d;
        }
    </style>
</head>
<body>
    <a href="view_booking_details.php?id=<?php echo $booking_id; ?>" class="back-button">← Back to Booking Details</a>
    
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="company-details">
                <h2><?php echo $company['name']; ?></h2>
                <p>
                    <?php echo $company['address']; ?><br>
                    <?php echo $company['city']; ?><br>
                    Phone: <?php echo $company['phone']; ?><br>
                    Email: <?php echo $company['email']; ?><br>
                    <?php echo $company['gst']; ?>
                </p>
            </div>
            <div class="invoice-title">
                <h1>INVOICE</h1>
                <p>#INV-<?php echo $booking['ticket_number']; ?></p>
            </div>
        </div>

        <div class="customer-details">
            <div>
                <strong>Bill To:</strong><br>
                <?php echo htmlspecialchars($booking['customer_name']); ?><br>
                Email: <?php echo htmlspecialchars($booking['customer_email']); ?><br>
                Customer ID: <?php echo htmlspecialchars($booking['user_id']); ?>
            </div>
            <div class="invoice-meta">
                <p>
                    <strong>Invoice Date:</strong> <?php echo date('d M Y'); ?><br>
                    <strong>Booking Date:</strong> <?php echo date('d M Y', strtotime($booking['booking_date'])); ?><br>
                    <strong>Ticket Number:</strong> <?php echo htmlspecialchars($booking['ticket_number']); ?><br>
                    <strong>Payment Method:</strong> <?php echo htmlspecialchars($payment_method_display); ?>
                </p>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="10%">Item #</th>
                    <th width="40%">Description</th>
                    <th width="15%">Weight</th>
                    <th width="15%">Route</th>
                    <th width="20%">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td><?php echo htmlspecialchars($booking['commodity_type']); ?> Transportation</td>
                    <td><?php echo htmlspecialchars($booking['weight']); ?> Tons</td>
                    <td><?php echo htmlspecialchars($booking['source']); ?> to <?php echo htmlspecialchars($booking['destination']); ?></td>
                    <td>₹<?php echo number_format($subtotal, 2); ?></td>
                </tr>
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td width="60%">Subtotal:</td>
                <td width="40%" align="right">₹<?php echo number_format($subtotal, 2); ?></td>
            </tr>
            <tr>
                <td>GST (18%):</td>
                <td align="right">₹<?php echo number_format($gst_amount, 2); ?></td>
            </tr>
            <tr class="grand-total">
                <td>Total:</td>
                <td align="right">₹<?php echo number_format($booking['price'], 2); ?></td>
            </tr>
        </table>

        <div class="notes">
            <strong>Notes:</strong><br>
            1. This is a computer-generated invoice and does not require a signature.<br>
            2. Payment Status: <?php echo ucfirst(htmlspecialchars($booking['payment_status'])); ?><br>
            3. For any queries regarding this invoice, please contact our customer support.<br>
            4. Terms & Conditions Apply.
        </div>

        <div class="footer">
            Thank you for choosing FreightX Logistics for your freight transportation needs!<br>
            <?php echo $company['website']; ?> | <?php echo $company['phone']; ?> | <?php echo $company['email']; ?>
        </div>
    </div>
    
    <button class="print-button" onclick="window.print()">Print Invoice</button>
    
    <script>
        // Auto-print when the page loads (optional)
        /*
        window.onload = function() {
            window.print();
        }
        */
    </script>
</body>
</html>
