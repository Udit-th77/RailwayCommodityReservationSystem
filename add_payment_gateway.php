<?php
session_start();
include 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gateway_name = trim($_POST['gateway_name']);
    $display_name = trim($_POST['display_name']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $api_key = trim($_POST['api_key']);
    $api_secret = trim($_POST['api_secret']);
    $merchant_id = trim($_POST['merchant_id']);
    $sandbox_mode = isset($_POST['sandbox_mode']) ? 1 : 0;
    
    // Validate input
    if (empty($gateway_name) || empty($display_name)) {
        $error_message = "Gateway name and display name are required.";
    } else {
        // Check if gateway already exists
        $stmt = $conn->prepare("SELECT id FROM payment_gateways WHERE gateway_name = ?");
        $stmt->bind_param("s", $gateway_name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error_message = "A payment gateway with this name already exists.";
        } else {
            // Insert new gateway
            $stmt = $conn->prepare("INSERT INTO payment_gateways (gateway_name, display_name, is_active, api_key, api_secret, merchant_id, sandbox_mode) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisssi", $gateway_name, $display_name, $is_active, $api_key, $api_secret, $merchant_id, $sandbox_mode);
            
            if ($stmt->execute()) {
                $success_message = "Payment gateway added successfully!";
                // Clear form data on success
                $gateway_name = $display_name = $api_key = $api_secret = $merchant_id = '';
                $is_active = $sandbox_mode = 0;
            } else {
                $error_message = "Error adding payment gateway: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Payment Gateway - FreightX Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background: #f4f7fc;
            color: #333;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #3d5166;
        }
        
        .sidebar-header h2 {
            color: #ffcc00;
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .admin-info {
            font-size: 14px;
            color: #ecf0f1;
            margin-bottom: 10px;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 15px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: 0.3s;
            font-size: 16px;
        }
        
        .sidebar-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: #34495e;
            border-left: 4px solid #ffcc00;
        }
        
        .logout-btn {
            margin: 20px;
            padding: 12px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: calc(100% - 40px);
            font-weight: bold;
            transition: 0.3s;
        }
        
        .logout-btn:hover {
            background: #c0392b;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .page-header h1 {
            font-size: 28px;
            color: #2c3e50;
        }
        
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .checkbox-group label {
            margin-left: 10px;
            margin-bottom: 0;
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
            font-size: 16px;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
            flex: 1;
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .password-toggle {
            position: relative;
        }
        
        .password-toggle i {
            position: absolute;
            right: 15px;
            top: 15px;
            cursor: pointer;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-train"></i> FreightX</h2>
                <div class="admin-info">
                    Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
                </div>
            </div>
            
            <div class="sidebar-menu">
                <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
                <a href="manage_employees.php"><i class="fas fa-user-tie"></i> Manage Employees</a>
                <a href="manage_bookings.php"><i class="fas fa-clipboard-list"></i> Manage Bookings</a>
                <a href="view_support_queries.php"><i class="fas fa-headset"></i> Support Queries</a>
                <a href="payment_settings.php" class="active"><i class="fas fa-credit-card"></i> Payment Settings</a>
                <a href="admin_change_password.php"><i class="fas fa-key"></i> Change Password</a>
                <a href="admin_dashboard.php"><i class="fas fa-cog"></i> Settings</a>
            </div>
            
            <form action="admin_logout.php" method="post">
                <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-plus-circle"></i> Add New Payment Gateway</h1>
                <a href="payment_settings.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Payment Settings
                </a>
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
            
            <div class="form-card">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="gateway_name">Gateway Name (system identifier)</label>
                        <input type="text" id="gateway_name" name="gateway_name" value="<?php echo isset($gateway_name) ? htmlspecialchars($gateway_name) : ''; ?>" required placeholder="e.g., stripe, paypal, razorpay">
                    </div>
                    
                                     <div class="form-group">
                        <label for="display_name">Display Name</label>
                        <input type="text" id="display_name" name="display_name" value="<?php echo isset($display_name) ? htmlspecialchars($display_name) : ''; ?>" required placeholder="e.g., Stripe, PayPal, Razorpay">
                    </div>
                    
                    <div class="form-group">
                        <label for="api_key">API Key</label>
                        <div class="password-toggle">
                            <input type="password" id="api_key" name="api_key" value="<?php echo isset($api_key) ? htmlspecialchars($api_key) : ''; ?>" placeholder="Enter API key">
                            <i class="fas fa-eye toggle-password" data-target="api_key"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="api_secret">API Secret</label>
                        <div class="password-toggle">
                            <input type="password" id="api_secret" name="api_secret" value="<?php echo isset($api_secret) ? htmlspecialchars($api_secret) : ''; ?>" placeholder="Enter API secret">
                            <i class="fas fa-eye toggle-password" data-target="api_secret"></i>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="merchant_id">Merchant ID</label>
                        <input type="text" id="merchant_id" name="merchant_id" value="<?php echo isset($merchant_id) ? htmlspecialchars($merchant_id) : ''; ?>" placeholder="Enter merchant ID (if applicable)">
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" <?php echo isset($is_active) && $is_active ? 'checked' : ''; ?>>
                        <label for="is_active">Enable this payment gateway</label>
                    </div>
                    
                    <div class="checkbox-group">
                        <input type="checkbox" id="sandbox_mode" name="sandbox_mode" <?php echo isset($sandbox_mode) && $sandbox_mode ? 'checked' : ''; ?>>
                        <label for="sandbox_mode">Enable sandbox/test mode</label>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">Add Payment Gateway</button>
                        <a href="payment_settings.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Toggle password visibility
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButtons = document.querySelectorAll('.toggle-password');
            
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    const inputField = document.getElementById(targetId);
                    
                    if (inputField.type === 'password') {
                        inputField.type = 'text';
                        this.classList.remove('fa-eye');
                        this.classList.add('fa-eye-slash');
                    } else {
                        inputField.type = 'password';
                        this.classList.remove('fa-eye-slash');
                        this.classList.add('fa-eye');
                    }
                });
            });
        });
    </script>
</body>
</html>
