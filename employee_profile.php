<?php
session_start();
include 'db_connect.php';

// Check if employee is logged in
if (!isset($_SESSION['employee_logged_in']) || $_SESSION['employee_logged_in'] !== true) {
    header("Location: employee_login.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];
$message = "";
$error = "";

// Fetch employee details
$stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    
    if (!empty($name) && !empty($phone)) {
        $update = $conn->prepare("UPDATE employees SET name = ?, phone = ? WHERE id = ?");
        $update->bind_param("ssi", $name, $phone, $employee_id);
        
        if ($update->execute()) {
            $_SESSION['employee_name'] = $name;
            $message = "Profile updated successfully!";
            
            // Refresh employee data
            $stmt->execute();
            $result = $stmt->get_result();
            $employee = $result->fetch_assoc();
        } else {
            $error = "Failed to update profile: " . $conn->error;
        }
    } else {
        $error = "Name and phone number are required.";
    }
}

// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All password fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        // Verify current password
        if (password_verify($current_password, $employee['password'])) {
            // Hash new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update password
            $update = $conn->prepare("UPDATE employees SET password = ? WHERE id = ?");
            $update->bind_param("si", $hashed_password, $employee_id);
            
            if ($update->execute()) {
                $message = "Password changed successfully!";
            } else {
                $error = "Failed to change password: " . $conn->error;
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Profile - FreightX</title>
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
        
        .employee-info {
            font-size: 14px;
            color: #ecf0f1;
            margin-bottom: 10px;
        }
        
        .employee-position {
            background: #3498db;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
            margin-top: 5px;
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
        
        /* Profile Sections */
        .profile-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .section-header h2 {
            font-size: 20px;
            color: #2c3e50;
        }
        
        /* Forms */
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        button {
            padding: 10px 15px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }
        
        button:hover {
            background: #2980b9;
        }
        
        /* Alerts */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Profile Info */
        .profile-info {
            display: flex;
            margin-bottom: 20px;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: #3498db;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
            font-size: 40px;
            margin-right: 20px;
        }
        
        .profile-details {
            flex: 1;
        }
        
        .profile-details h3 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .profile-details p {
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .profile-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            background: #2ecc71;
            color: white;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
        <div class="sidebar-header">
                <h2><i class="fas fa-train"></i> FreightX</h2>
                <div class="employee-info">
                    Welcome, <?php echo htmlspecialchars($_SESSION['employee_name']); ?>
                </div>
                <div class="employee-position">
                    <?php echo htmlspecialchars($_SESSION['employee_position']); ?>
                </div>
            </div>
            
            <div class="sidebar-menu">
                <a href="employee_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                
                <?php if (strpos($_SESSION['employee_position'], 'Driver') !== false || $_SESSION['employee_position'] == 'Delivery Partner'): ?>
                    <a href="employee_dashboard.php#deliveries"><i class="fas fa-truck"></i> My Deliveries</a>
                    <a href="employee_dashboard.php#completed"><i class="fas fa-check-circle"></i> Completed Tasks</a>
                <?php endif; ?>
                
                <?php if ($_SESSION['employee_position'] == 'Logistics Coordinator' || $_SESSION['employee_position'] == 'Station Manager'): ?>
                    <a href="employee_dashboard.php#all-bookings"><i class="fas fa-clipboard-list"></i> All Bookings</a>
                    <a href="employee_dashboard.php#unassigned"><i class="fas fa-tasks"></i> Unassigned Bookings</a>
                    <a href="employee_dashboard.php#employees"><i class="fas fa-users"></i> Delivery Personnel</a>
                <?php endif; ?>
                
                <a href="employee_profile.php" class="active"><i class="fas fa-user"></i> My Profile</a>
            </div>
            
            <form action="employee_logout.php" method="post">
                <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1>My Profile</h1>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <!-- Profile Overview -->
            <div class="profile-section">
                <div class="section-header">
                    <h2><i class="fas fa-user-circle"></i> Profile Overview</h2>
                </div>
                
                <div class="profile-info">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    
                    <div class="profile-details">
                        <h3><?php echo htmlspecialchars($employee['name']); ?></h3>
                        <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($employee['email']); ?></p>
                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($employee['phone']); ?></p>
                        <p><i class="fas fa-briefcase"></i> <?php echo htmlspecialchars($employee['position']); ?></p>
                        <div class="profile-status">
                            <?php echo ucfirst($employee['status']); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Edit Profile -->
            <div class="profile-section">
                <div class="section-header">
                    <h2><i class="fas fa-edit"></i> Edit Profile</h2>
                </div>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($employee['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" value="<?php echo htmlspecialchars($employee['email']); ?>" disabled>
                        <small style="color: #7f8c8d;">Email cannot be changed. Contact admin for assistance.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($employee['phone']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="position">Position</label>
                        <input type="text" value="<?php echo htmlspecialchars($employee['position']); ?>" disabled>
                        <small style="color: #7f8c8d;">Position cannot be changed. Contact admin for assistance.</small>
                    </div>
                    
                    <button type="submit" name="update_profile">Update Profile</button>
                </form>
            </div>
            
            <!-- Change Password -->
            <div class="profile-section">
                <div class="section-header">
                    <h2><i class="fas fa-key"></i> Change Password</h2>
                </div>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                        <small style="color: #7f8c8d;">Password must be at least 6 characters long.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" name="change_password">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
