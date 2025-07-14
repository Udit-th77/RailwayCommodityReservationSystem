<?php
session_start();
include 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Fetch pending employee registrations
$pending_employees = $conn->query("SELECT * FROM employees WHERE status = 'pending' ORDER BY created_at DESC");

// Fetch recent bookings
$recent_bookings = $conn->query("SELECT b.*, u.name as user_name FROM bookings b 
                                JOIN users u ON b.user_id = u.id 
                                ORDER BY b.booking_date DESC LIMIT 10");

// Fetch support queries
$support_queries = $conn->query("SELECT * FROM support_queries ORDER BY submitted_at DESC LIMIT 5");

// Count statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_employees = $conn->query("SELECT COUNT(*) as count FROM employees WHERE status = 'approved'")->fetch_assoc()['count'];
$total_bookings = $conn->query("SELECT COUNT(*) as count FROM bookings")->fetch_assoc()['count'];
$pending_approvals = $conn->query("SELECT COUNT(*) as count FROM employees WHERE status = 'pending'")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FreightX</title>
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
        
        .date-time {
            font-size: 14px;
            color: #7f8c8d;
        }
        
        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
        }
        
        .stat-card i {
            font-size: 40px;
            margin-right: 20px;
            color: #3498db;
        }
        
        .stat-card.users i { color: #3498db; }
        .stat-card.employees i { color: #2ecc71; }
        .stat-card.bookings i { color: #f39c12; }
        .stat-card.pending i { color: #e74c3c; }
        
        .stat-info h3 {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        /* Sections */
        .dashboard-section {
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
        
        .view-all {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
        }
        
        .view-all:hover {
            text-decoration: underline;
        }
        
        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        
        table tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Buttons */
        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }
        
        .btn-approve {
            background: #2ecc71;
            color: white;
        }
        
        .btn-reject {
            background: #e74c3c;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.8;
        }
        
        /* Status Badges */
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .status-pending {
            background: #f39c12;
            color: white;
        }
        
        .status-approved {
            background: #2ecc71;
            color: white;
        }
        
        .status-rejected {
            background: #e74c3c;
            color: white;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
        }
        
        .empty-state i {
            font-size: 50px;
            margin-bottom: 10px;
            color: #bdc3c7;
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
    <a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
    <a href="manage_employees.php"><i class="fas fa-user-tie"></i> Manage Employees</a>
    <a href="manage_bookings.php"><i class="fas fa-clipboard-list"></i> Manage Bookings</a>
    <a href="view_support_queries.php"><i class="fas fa-headset"></i> Support Queries</a>
    <a href="payment_settings.php"><i class="fas fa-credit-card"></i> Payment Settings</a>
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
                <h1>Admin Dashboard</h1>
                <div class="date-time">
                    <?php echo date('l, F j, Y'); ?>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card users">
                    <i class="fas fa-users"></i>
                    <div class="stat-info">
                        <h3>Total Users</h3>
                        <p><?php echo $total_users; ?></p>
                    </div>
                </div>
                
                <div class="stat-card employees">
                    <i class="fas fa-user-tie"></i>
                    <div class="stat-info">
                        <h3>Active Employees</h3>
                        <p><?php echo $total_employees; ?></p>
                    </div>
                </div>
                
                <div class="stat-card bookings">
                    <i class="fas fa-clipboard-list"></i>
                    <div class="stat-info">
                        <h3>Total Bookings</h3>
                        <p><?php echo $total_bookings; ?></p>
                    </div>
                </div>
                
                <div class="stat-card pending">
                    <i class="fas fa-user-clock"></i>
                    <div class="stat-info">
                        <h3>Pending Approvals</h3>
                        <p><?php echo $pending_approvals; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Pending Employee Approvals Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-user-clock"></i> Pending Employee Approvals</h2>
                    <a href="manage_employees.php" class="view-all">View All</a>
                </div>
                
                <?php if ($pending_employees->num_rows > 0): ?>
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Position</th>
                            <th>Applied On</th>
                            <th>Actions</th>
                        </tr>
                        
                        <?php while ($employee = $pending_employees->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $employee['id']; ?></td>
                                <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                <td><?php echo htmlspecialchars($employee['position']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($employee['created_at'])); ?></td>
                                <td>
                                    <a href="approve_employee.php?id=<?php echo $employee['id']; ?>" class="btn btn-approve">Approve</a>
                                    <a href="reject_employee.php?id=<?php echo $employee['id']; ?>" class="btn btn-reject">Reject</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-user-check"></i>
                        <p>No pending employee approvals at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Bookings Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-clipboard-list"></i> Recent Bookings</h2>
                    <a href="manage_bookings.php" class="view-all">View All</a>
                </div>
                
                <?php if ($recent_bookings->num_rows > 0): ?>
                    <table>
                        <tr>
                            <th>Booking ID</th>
                            <th>User</th>
                            <th>Commodity</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                        
                        <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $booking['id']; ?></td>
                                <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['commodity_type']); ?></td>
                                <td><?php echo htmlspecialchars($booking['source']); ?></td>
                                <td><?php echo htmlspecialchars($booking['destination']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                                <td>
                                    <span class="status status-<?php echo strtolower($booking['status']); ?>">
                                        <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard"></i>
                        <p>No bookings have been made yet.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Support Queries Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-headset"></i> Recent Support Queries</h2>
                    <a href="view_support_queries.php" class="view-all">View All</a>
                </div>
                
                <?php if ($support_queries->num_rows > 0): ?>
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Submitted</th>
                            <th>Status</th>
                        </tr>
                        
                        <?php while ($query = $support_queries->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $query['id']; ?></td>
                                <td><?php echo htmlspecialchars($query['name']); ?></td>
                                <td><?php echo htmlspecialchars($query['email']); ?></td>
                                <td><?php echo substr(htmlspecialchars($query['message']), 0, 50) . (strlen($query['message']) > 50 ? '...' : ''); ?></td>
                                <td><?php echo date('M j, Y', strtotime($query['submitted_at'])); ?></td>
                                <td>
                                    <?php 
                                    $status = isset($query['status']) ? $query['status'] : 'pending';
                                    $statusClass = $status == 'resolved' ? 'status-approved' : 'status-pending';
                                    ?>
                                    <span class="status <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <p>No support queries have been submitted yet.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Quick Actions Section -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                </div>
                
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <a href="add_user.php" style="text-decoration: none;">
                        <div style="background: #3498db; color: white; padding: 15px; border-radius: 8px; width: 200px; text-align: center;">
                            <i class="fas fa-user-plus" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p>Add New User</p>
                        </div>
                    </a>
                    
                    <a href="add_employee.php" style="text-decoration: none;">
                        <div style="background: #2ecc71; color: white; padding: 15px; border-radius: 8px; width: 200px; text-align: center;">
                            <i class="fas fa-user-tie" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p>Add New Employee</p>
                        </div>
                    </a>
                    
                    <a href="create_booking.php" style="text-decoration: none;">
                        <div style="background: #f39c12; color: white; padding: 15px; border-radius: 8px; width: 200px; text-align: center;">
                            <i class="fas fa-plus-circle" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p>Create New Booking</p>
                        </div>
                    </a>
                    
                    <a href="reports.php" style="text-decoration: none;">
                        <div style="background: #9b59b6; color: white; padding: 15px; border-radius: 8px; width: 200px; text-align: center;">
                            <i class="fas fa-chart-bar" style="font-size: 24px; margin-bottom: 10px;"></i>
                            <p>Generate Reports</p>
                        </div>
                    </a>
                    <a href="payment_settings.php" style="text-decoration: none;">
    <div style="background: #e74c3c; color: white; padding: 15px; border-radius: 8px; width: 200px; text-align: center;">
        <i class="fas fa-credit-card" style="font-size: 24px; margin-bottom: 10px;"></i>
        <p>Payment Settings</p>
    </div>
</a>

                </div>
            </div>
        </div>
    </div>

    <script>
        // Display current date and time
        function updateDateTime() {
            const now = new Date();
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            document.querySelector('.date-time').textContent = now.toLocaleDateString('en-US', options);
        }
        
        updateDateTime();
        setInterval(updateDateTime, 60000); // Update every minute
    </script>
</body>
</html>
