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

// Handle form submission for updating gateway settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_gateway'])) {
        $gateway_id = $_POST['gateway_id'];
        $display_name = $_POST['display_name'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $api_key = $_POST['api_key'];
        $api_secret = $_POST['api_secret'];
        $merchant_id = $_POST['merchant_id'];
        $sandbox_mode = isset($_POST['sandbox_mode']) ? 1 : 0;
        
        // Update gateway settings
        $stmt = $conn->prepare("UPDATE payment_gateways SET 
                               display_name = ?, 
                               is_active = ?, 
                               api_key = ?, 
                               api_secret = ?, 
                               merchant_id = ?, 
                               sandbox_mode = ? 
                               WHERE id = ?");
        
        $stmt->bind_param("sissiii", $display_name, $is_active, $api_key, $api_secret, $merchant_id, $sandbox_mode, $gateway_id);
        
        if ($stmt->execute()) {
            $success_message = "Payment gateway settings updated successfully!";
        } else {
            $error_message = "Error updating payment gateway settings: " . $conn->error;
        }
    }
}

// Fetch all payment gateways
$gateways = $conn->query("SELECT * FROM payment_gateways ORDER BY id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Settings - FreightX Admin</title>
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
        
        /* Gateway Cards */
        .gateway-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .gateway-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .gateway-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .gateway-header h2 {
            font-size: 20px;
            color: #2c3e50;
            display: flex;
            align-items: center;
        }
        
        .gateway-header h2 i {
            margin-right: 10px;
            color: #3498db;
        }
        
        .gateway-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-active {
            background: #2ecc71;
            color: white;
        }
        
        .status-inactive {
            background: #e74c3c;
            color: white;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .checkbox-group label {
            margin-left: 10px;
            margin-bottom: 0;
        }
        
        .btn-update {
            padding: 10px 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }
        
        .btn-update:hover {
            background: #2980b9;
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
            right: 10px;
            top: 12px;
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
                <h1><i class="fas fa-credit-card"></i> Payment Gateway Settings</h1>
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
            
            <div class="gateway-container">
                <?php if ($gateways->num_rows > 0): ?>
                    <?php while ($gateway = $gateways->fetch_assoc()): ?>
                        <div class="gateway-card">
                            <div class="gateway-header">
                                <h2>
                                    <?php if ($gateway['gateway_name'] == 'stripe'): ?>
                                        <i class="fab fa-stripe"></i>
                                    <?php elseif ($gateway['gateway_name'] == 'paypal'): ?>
                                        <i class="fab fa-paypal"></i>
                                    <?php elseif ($gateway['gateway_name'] == 'razorpay'): ?>
                                        <i class="fas fa-rupee-sign"></i>
                                    <?php else: ?>
                                        <i class="fas fa-credit-card"></i>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($gateway['gateway_name']); ?>
                                </h2>
                                <span class="gateway-status <?php echo $gateway['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $gateway['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </div>
                            
                            <form method="POST" action="">
                                <input type="hidden" name="gateway_id" value="<?php echo $gateway['id']; ?>">
                                
                                <div class="form-group">
                                    <label for="display_name_<?php echo $gateway['id']; ?>">Display Name</label>
                                    <input type="text" id="display_name_<?php echo $gateway['id']; ?>" name="display_name" value="<?php echo htmlspecialchars($gateway['display_name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="api_key_<?php echo $gateway['id']; ?>">API Key</label>
                                    <div class="password-toggle">
                                        <input type="password" id="api_key_<?php echo $gateway['id']; ?>" name="api_key" value="<?php echo htmlspecialchars($gateway['api_key'] ?? ''); ?>">
                                        <i class="fas fa-eye toggle-password" data-target="api_key_<?php echo $gateway['id']; ?>"></i>
                                    </div>
                                </div>
                                
                                                            <div class="form-group">
                                    <label for="api_secret_<?php echo $gateway['id']; ?>">API Secret</label>
                                    <div class="password-toggle">
                                        <input type="password" id="api_secret_<?php echo $gateway['id']; ?>" name="api_secret" value="<?php echo htmlspecialchars($gateway['api_secret'] ?? ''); ?>">
                                        <i class="fas fa-eye toggle-password" data-target="api_secret_<?php echo $gateway['id']; ?>"></i>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="merchant_id_<?php echo $gateway['id']; ?>">Merchant ID</label>
                                    <input type="text" id="merchant_id_<?php echo $gateway['id']; ?>" name="merchant_id" value="<?php echo htmlspecialchars($gateway['merchant_id'] ?? ''); ?>">
                                </div>
                                
                                <div class="checkbox-group">
                                    <input type="checkbox" id="is_active_<?php echo $gateway['id']; ?>" name="is_active" <?php echo $gateway['is_active'] ? 'checked' : ''; ?>>
                                    <label for="is_active_<?php echo $gateway['id']; ?>">Enable this payment gateway</label>
                                </div>
                                
                                <div class="checkbox-group">
                                    <input type="checkbox" id="sandbox_mode_<?php echo $gateway['id']; ?>" name="sandbox_mode" <?php echo $gateway['sandbox_mode'] ? 'checked' : ''; ?>>
                                    <label for="sandbox_mode_<?php echo $gateway['id']; ?>">Enable sandbox/test mode</label>
                                </div>
                                
                                <button type="submit" name="update_gateway" class="btn-update">Update Settings</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="grid-column: 1 / -1; text-align: center; padding: 30px; color: #7f8c8d;">
                        <i class="fas fa-exclamation-circle" style="font-size: 48px; margin-bottom: 15px;"></i>
                        <p>No payment gateways found in the database. Please add payment gateways to the system.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Add New Gateway Button -->
            <div style="text-align: center; margin-top: 20px;">
                <a href="add_payment_gateway.php" style="display: inline-block; padding: 12px 20px; background: #2ecc71; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                    <i class="fas fa-plus"></i> Add New Payment Gateway
                </a>
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
