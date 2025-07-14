<?php
session_start();
include 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Initialize variables for statistics
$total_bookings = 0;
$active_bookings = 0;
$completed_bookings = 0;

// Fetch user's bookings - using the table structure from view_bookings.php
try {
    $sql = "SELECT * FROM bookings WHERE user_id = ? ORDER BY booking_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $bookings = $stmt->get_result();
    
    // Count total bookings
    $total_bookings = $bookings->num_rows;
    // echo "<pre>";
    // print_r($bookings);
    // echo "</pre>";
    // For simplicity, we'll set these to 0 since we don't have status field in the original code
    $active_bookings = 0;
    $completed_bookings = 0;
} catch (Exception $e) {
    // Handle error silently
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - FreightX</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background: #f5f5f5;
            color: #333;
        }
        
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: linear-gradient(to bottom, #3498db, #2c3e50);
            color: #fff;
            padding: 20px 0;
            position: fixed;
            height: 100%;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .sidebar-header h2 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #ffcc00;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            margin-top: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: #ffcc00;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 10px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .user-name {
            font-size: 16px;
            font-weight: bold;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .menu-item {
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: all 0.3s;
            text-decoration: none;
            color: #fff;
        }
        
        .menu-item:hover, .menu-item.active {
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid #ffcc00;
        }
        
        .menu-item i {
            margin-right: 10px;
            font-size: 18px;
            width: 25px;
            text-align: center;
        }
        
        .logout-btn {
            margin-top: 20px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: all 0.3s;
            text-decoration: none;
            color: #fff;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .logout-btn:hover {
            background: rgba(255, 0, 0, 0.1);
        }
        
        .logout-btn i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .dashboard-title h1 {
            font-size: 28px;
            color: #2c3e50;
        }
        
        .dashboard-actions a {
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .dashboard-actions a:hover {
            background: #2980b9;
        }
        
        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-right: 15px;
            font-size: 24px;
        }
        
        .stat-icon.blue {
            background: rgba(52, 152, 219, 0.2);
            color: #3498db;
        }
        
        .stat-icon.green {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
        }
        
        .stat-icon.orange {
            background: rgba(230, 126, 34, 0.2);
            color: #e67e22;
        }
        
        .stat-info h3 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        /* Bookings Table */
        .bookings-container {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .bookings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .bookings-header h2 {
            color: #2c3e50;
            font-size: 20px;
        }
        
        .bookings-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .bookings-table th, .bookings-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .bookings-table th {
            background: #f9f9f9;
            color: #2c3e50;
            font-weight: bold;
        }
        
        .bookings-table tr:hover {
            background: #f5f5f5;
        }
        
        .action-btn {
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            font-weight: bold;
            margin-right: 5px;
        }
        
        .view-btn {
            background: #3498db;
            color: #fff;
        }
        
        .view-btn:hover {
            background: #2980b9;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
        }
        
        .empty-state i {
            font-size: 50px;
            margin-bottom: 15px;
            color: #bdc3c7;
        }
        
        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        
        .empty-state p {
            margin-bottom: 20px;
        }
        
        .empty-state a {
            display: inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .empty-state a:hover {
            background: #2980b9;
        }
        
        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .bookings-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-train"></i> FreightX</h2>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                    </div>
                    <div class="user-name">
                        <?php echo htmlspecialchars($user_name); ?>
                    </div>
                </div>
            </div>
            
            <div class="sidebar-menu">
                <a href="user_dashboard.php" class="menu-item active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="book_commodity.php" class="menu-item">
                    <i class="fas fa-box"></i> Book Commodity
                </a>
                <a href="view_bookings.php" class="menu-item">
                    <i class="fas fa-list"></i> View Bookings
                </a>
                <a href="customer_support.php" class="menu-item">
                    <i class="fas fa-headset"></i> Customer Support
                </a>
            </div>
            
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="dashboard-header">
                <div class="dashboard-title">
                    <h1>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h1>
                </div>
                <div class="dashboard-actions">
                    <a href="book_commodity.php"><i class="fas fa-plus"></i> Book New Commodity</a>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_bookings; ?></h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $active_bookings; ?></h3>
                        <p>Active Bookings</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $completed_bookings; ?></h3>
                        <p>Completed Bookings</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Bookings -->
            <div class="bookings-container">
                <div class="bookings-header">
                    <h2>Recent Bookings</h2>
                </div>
                
                <?php if (isset($bookings) && $bookings->num_rows > 0): ?>
                    <table class="bookings-table">
                        <thead>
                            <tr>
                                <th>Commodity</th>
                                <th>Weight (Tons)</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Booking Date</th>
                                <th>Tracking Number</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($booking = $bookings->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($booking['commodity_name'] ?? $booking['commodity_type'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($booking['weight'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($booking['start_station'] ?? $booking['source'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($booking['destination'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['booking_date'] ?? 'now')); ?></td>
                                    <td><?php echo htmlspecialchars($booking['ticket_number'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="view_booking_details.php?id=<?php echo $booking['id'] ?? ''; ?>" class="action-btn view-btn">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h3>No bookings yet</h3>
                        <p>You haven't booked any commodities yet. Start shipping with FreightX today!</p>
                        <a href="book_commodity.php">Book Your First Commodity</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Add active class to current menu item
        document.addEventListener('DOMContentLoaded', function() {
            const currentLocation = window.location.pathname;
            const menuItems = document.querySelectorAll('.menu-item');
            
            menuItems.forEach(item => {
                const href = item.getAttribute('href');
                if (currentLocation.includes(href)) {
                    item.classList.add('active');
                } else if (currentLocation.endsWith('user_dashboard.php') && href.endsWith('user_dashboard.php')) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
        });
    </script>
</body>
</html>
