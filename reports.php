<?php
session_start();
include 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Set default date range (last 30 days)
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-30 days'));

// Handle date range filter
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
}

// Simulate data for demonstration
// Monthly revenue data (last 12 months)
$months = [];
$revenue_data = [];
$expense_data = [];
$profit_data = [];

for ($i = 11; $i >= 0; $i--) {
    $month = date('M Y', strtotime("-$i months"));
    $months[] = $month;
    
    // Generate random revenue between 100,000 and 500,000
    $revenue = mt_rand(100000, 500000);
    $revenue_data[] = $revenue;
    
    // Generate random expenses between 50,000 and 300,000
    $expense = mt_rand(50000, 300000);
    $expense_data[] = $expense;
    
    // Calculate profit
    $profit_data[] = $revenue - $expense;
}

// Commodity distribution data
$commodity_labels = ['Grains', 'Coal', 'Cement', 'Steel', 'Chemicals', 'Electronics', 'Textiles', 'Other'];
$commodity_data = [];
$commodity_colors = [
    'rgba(255, 99, 132, 0.7)',
    'rgba(54, 162, 235, 0.7)',
    'rgba(255, 206, 86, 0.7)',
    'rgba(75, 192, 192, 0.7)',
    'rgba(153, 102, 255, 0.7)',
    'rgba(255, 159, 64, 0.7)',
    'rgba(199, 199, 199, 0.7)',
    'rgba(83, 102, 255, 0.7)'
];

foreach ($commodity_labels as $commodity) {
    $commodity_data[] = mt_rand(1000, 10000);
}

// Route performance data
$route_labels = [
    'Mumbai to Delhi',
    'Delhi to Chennai',
    'Kolkata to Mumbai',
    'Chennai to Bangalore',
    'Hyderabad to Delhi',
    'Pune to Kolkata',
    'Ahmedabad to Chennai'
];

$route_bookings = [];
$route_revenue = [];

foreach ($route_labels as $route) {
    $route_bookings[] = mt_rand(50, 500);
    $route_revenue[] = mt_rand(50000, 300000);
}

// User registration data
$user_reg_months = $months;
$user_reg_data = [];

foreach ($user_reg_months as $month) {
    $user_reg_data[] = mt_rand(20, 200);
}

// Customer satisfaction data
$satisfaction_labels = ['Excellent', 'Good', 'Average', 'Poor', 'Very Poor'];
$satisfaction_data = [60, 25, 10, 4, 1]; // Percentages

// Delivery performance data
$delivery_labels = ['On Time', 'Delayed (1-2 days)', 'Delayed (3-5 days)', 'Delayed (>5 days)'];
$delivery_data = [75, 15, 7, 3]; // Percentages

// Top commodities table data
$top_commodities = [];
for ($i = 0; $i < count($commodity_labels); $i++) {
    $top_commodities[] = [
        'name' => $commodity_labels[$i],
        'bookings' => mt_rand(100, 1000),
        'weight' => mt_rand(1000, 10000),
        'revenue' => mt_rand(100000, 500000)
    ];
}
// Sort by revenue (highest first)
usort($top_commodities, function($a, $b) {
    return $b['revenue'] - $a['revenue'];
});

// Popular routes table data
$popular_routes = [];
for ($i = 0; $i < count($route_labels); $i++) {
    $popular_routes[] = [
        'route' => $route_labels[$i],
        'bookings' => $route_bookings[$i],
        'weight' => mt_rand(1000, 10000),
        'revenue' => $route_revenue[$i]
    ];
}
// Sort by bookings (highest first)
usort($popular_routes, function($a, $b) {
    return $b['bookings'] - $a['bookings'];
});

// Key performance indicators
$kpi_data = [
    'total_bookings' => mt_rand(5000, 10000),
    'completed_bookings' => mt_rand(3000, 5000),
    'pending_bookings' => mt_rand(500, 1000),
    'in_transit_bookings' => mt_rand(500, 1000),
    'cancelled_bookings' => mt_rand(100, 500),
    'total_revenue' => mt_rand(5000000, 10000000),
    'avg_booking_value' => mt_rand(5000, 15000),
    'new_customers' => mt_rand(500, 2000)
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - FreightX Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
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
            z-index: 100;
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
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .page-header h1 {
            font-size: 28px;
            color: #2c3e50;
        }
        
        .page-actions {
            display: flex;
            gap: 10px;
        }
        
        /* Filter and Date Range */
        .filter-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .filter-form {
            display: flex;
            align-items: flex-end;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            margin-bottom: 10px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .filter-group input {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 14px;
            transition: 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .btn-secondary {
            background: #7f8c8d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #6c7a7d;
        }
        
        .btn-success {
            background: #2ecc71;
            color: white;
        }
        
        .btn-success:hover {
            background: #27ae60;
        }
        
        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        
        .stat-card i {
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        .stat-card h3 {
            font-size: 16px;
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        
        .stat-card .value {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .stat-card .trend {
            margin-top: 10px;
            font-size: 14px;
        }
        
        .trend-up {
            color: #2ecc71;
        }
        
        .trend-down {
            color: #e74c3c;
        }
        
        /* Charts */
        .chart-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .chart-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .chart-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        .chart-header h2 {
            font-size: 20px;
            color: #2c3e50;
            margin: 0;
        }
        
        .chart-header .chart-actions {
            display: flex;
            gap: 10px;
        }
        
        .chart-wrapper {
            position: relative;
            height: 300px;
        }
        
        /* Tables */
        .table-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .table-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        
        .table-header {
            padding: 15px 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .table-header h2 {
                      font-size: 20px;
            color: #2c3e50;
            margin: 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        /* Dashboard Tabs */
        .dashboard-tabs {
            display: flex;
            margin-bottom: 20px;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .tab-button {
            flex: 1;
            padding: 15px;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: bold;
            color: #7f8c8d;
            transition: 0.3s;
            border-bottom: 3px solid transparent;
        }
        
        .tab-button.active {
            color: #3498db;
            border-bottom: 3px solid #3498db;
            background: #f8f9fa;
        }
        
        .tab-button:hover:not(.active) {
            background: #f8f9fa;
            color: #2c3e50;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* KPI Cards with Icons */
        .kpi-card {
            display: flex;
            align-items: center;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        
        .kpi-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            flex-shrink: 0;
        }
        
        .kpi-icon i {
            font-size: 24px;
            color: white;
        }
        
        .kpi-details {
            flex: 1;
        }
        
        .kpi-title {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 5px;
        }
        
        .kpi-value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .kpi-trend {
            font-size: 12px;
        }
        
        /* Colors for KPI icons */
        .kpi-blue {
            background: linear-gradient(135deg, #3498db, #2980b9);
        }
        
        .kpi-green {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
        }
        
        .kpi-orange {
            background: linear-gradient(135deg, #f39c12, #d35400);
        }
        
        .kpi-red {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }
        
        .kpi-purple {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
        }
        
        .kpi-teal {
            background: linear-gradient(135deg, #1abc9c, #16a085);
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .chart-grid {
                grid-template-columns: 1fr;
            }
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
            
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .dashboard-tabs {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>FreightX</h2>
                <div class="admin-info">
                    <p>Admin Panel</p>
                </div>
            </div>
            
            <div class="sidebar-menu">
                <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="manage_users.php"><i class="fas fa-users"></i> Manage Users</a>
                <a href="manage_employees.php"><i class="fas fa-user-tie"></i> Manage Employees</a>
                <a href="manage_bookings.php"><i class="fas fa-shipping-fast"></i> Manage Bookings</a>
                <a href="manage_commodities.php"><i class="fas fa-boxes"></i> Manage Commodities</a>
                <a href="reports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="admin_dashboard.php"><i class="fas fa-cog"></i> Settings</a>
            </div>
            
            <form action="admin_logout.php" method="post">
                <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-chart-bar"></i> Reports & Analytics</h1>
                <div class="page-actions">
                    <button class="btn btn-success" onclick="window.print()"><i class="fas fa-print"></i> Print Report</button>
                    <button class="btn btn-primary" onclick="exportToPDF()"><i class="fas fa-file-pdf"></i> Export PDF</button>
                    <button class="btn btn-secondary" onclick="exportToExcel()"><i class="fas fa-file-excel"></i> Export Excel</button>
                </div>
            </div>
            
            <!-- Date Range Filter -->
            <div class="filter-container">
                <form action="" method="GET" class="filter-form">
                    <div class="filter-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="end_date">End Date</label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="report_type">Report Type</label>
                        <select id="report_type" name="report_type">
                            <option value="all">All Reports</option>
                            <option value="revenue">Revenue Reports</option>
                            <option value="bookings">Booking Reports</option>
                            <option value="commodities">Commodity Reports</option>
                            <option value="customers">Customer Reports</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply Filter</button>
                </form>
            </div>
            
            <!-- Dashboard Tabs -->
            <div class="dashboard-tabs">
                <button class="tab-button active" onclick="openTab('overview')"><i class="fas fa-home"></i> Overview</button>
                <button class="tab-button" onclick="openTab('revenue')"><i class="fas fa-rupee-sign"></i> Revenue</button>
                <button class="tab-button" onclick="openTab('bookings')"><i class="fas fa-shipping-fast"></i> Bookings</button>
                <button class="tab-button" onclick="openTab('commodities')"><i class="fas fa-boxes"></i> Commodities</button>
                <button class="tab-button" onclick="openTab('customers')"><i class="fas fa-users"></i> Customers</button>
            </div>
            
            <!-- Overview Tab -->
            <div id="overview" class="tab-content active">
                <!-- KPI Cards -->
                <div class="stats-container">
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-blue">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <div class="kpi-details">
                            <div class="kpi-title">Total Bookings</div>
                            <div class="kpi-value"><?php echo number_format($kpi_data['total_bookings']); ?></div>
                            <div class="kpi-trend trend-up"><i class="fas fa-arrow-up"></i> 12% from last month</div>
                        </div>
                    </div>
                    
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-green">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="kpi-details">
                            <div class="kpi-title">Completed Bookings</div>
                            <div class="kpi-value"><?php echo number_format($kpi_data['completed_bookings']); ?></div>
                            <div class="kpi-trend trend-up"><i class="fas fa-arrow-up"></i> 8% from last month</div>
                        </div>
                    </div>
                    
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-orange">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="kpi-details">
                            <div class="kpi-title">Pending Bookings</div>
                            <div class="kpi-value"><?php echo number_format($kpi_data['pending_bookings']); ?></div>
                            <div class="kpi-trend trend-down"><i class="fas fa-arrow-down"></i> 5% from last month</div>
                        </div>
                    </div>
                    
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-purple">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="kpi-details">
                            <div class="kpi-title">In Transit</div>
                            <div class="kpi-value"><?php echo number_format($kpi_data['in_transit_bookings']); ?></div>
                            <div class="kpi-trend trend-up"><i class="fas fa-arrow-up"></i> 15% from last month</div>
                        </div>
                    </div>
                    
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-teal">
                            <i class="fas fa-rupee-sign"></i>
                        </div>
                        <div class="kpi-details">
                            <div class="kpi-title">Total Revenue</div>
                            <div class="kpi-value">₹<?php echo number_format($kpi_data['total_revenue']); ?></div>
                            <div class="kpi-trend trend-up"><i class="fas fa-arrow-up"></i> 18% from last month</div>
                        </div>
                    </div>
                    
                    <div class="kpi-card">
                        <div class="kpi-icon kpi-red">
                            <i class="fas fa-ban"></i>
                        </div>
                        <div class="kpi-details">
                            <div class="kpi-title">Cancelled Bookings</div>
                            <div class="kpi-value"><?php echo number_format($kpi_data['cancelled_bookings']); ?></div>
                            <div class="kpi-trend trend-down"><i class="fas fa-arrow-down"></i> 3% from last month</div>
                        </div>
                    </div>
                </div>
                
                <!-- Revenue Chart -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h2><i class="fas fa-chart-line"></i> Revenue vs Expenses</h2>
                        <div class="chart-actions">
                            <button class="btn btn-secondary btn-sm" onclick="toggleChartType('revenueChart')">
                                <i class="fas fa-sync"></i> Change Chart Type
                            </button>
                        </div>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
                
                <!-- Commodity Distribution Chart -->
                <div class="chart-grid">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h2><i class="fas fa-boxes"></i> Commodity Distribution</h2>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="commodityChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Route Performance Chart -->
                    <div class="chart-container">
                        <div class="chart-header">
                            <h2><i class="fas fa-route"></i> Top Routes by Bookings</h2>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="routeChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Top Commodities Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2><i class="fas fa-boxes"></i> Top Commodities by Revenue</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Commodity</th>
                                <th>Bookings</th>
                                <th>Total Weight (Tons)</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                                                       <?php foreach ($top_commodities as $commodity): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($commodity['name']); ?></td>
                                    <td><?php echo number_format($commodity['bookings']); ?></td>
                                    <td><?php echo number_format($commodity['weight']); ?> tons</td>
                                    <td>₹<?php echo number_format($commodity['revenue']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Revenue Tab -->
            <div id="revenue" class="tab-content">
                <!-- Revenue KPIs -->
                <div class="stats-container">
                    <div class="stat-card">
                        <i class="fas fa-rupee-sign" style="color: #2ecc71;"></i>
                        <h3>Total Revenue</h3>
                        <div class="value">₹<?php echo number_format($kpi_data['total_revenue']); ?></div>
                        <div class="trend trend-up"><i class="fas fa-arrow-up"></i> 18% from last month</div>
                    </div>
                    
                    <div class="stat-card">
                        <i class="fas fa-calculator" style="color: #3498db;"></i>
                        <h3>Average Booking Value</h3>
                        <div class="value">₹<?php echo number_format($kpi_data['avg_booking_value']); ?></div>
                        <div class="trend trend-up"><i class="fas fa-arrow-up"></i> 5% from last month</div>
                    </div>
                    
                    <div class="stat-card">
                        <i class="fas fa-percentage" style="color: #9b59b6;"></i>
                        <h3>Profit Margin</h3>
                        <div class="value">32%</div>
                        <div class="trend trend-up"><i class="fas fa-arrow-up"></i> 3% from last month</div>
                    </div>
                    
                    <div class="stat-card">
                        <i class="fas fa-chart-pie" style="color: #e74c3c;"></i>
                        <h3>Operational Costs</h3>
                        <div class="value">₹<?php echo number_format($kpi_data['total_revenue'] * 0.68); ?></div>
                        <div class="trend trend-down"><i class="fas fa-arrow-down"></i> 2% from last month</div>
                    </div>
                </div>
                
                <!-- Monthly Revenue Chart -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h2><i class="fas fa-chart-line"></i> Monthly Revenue & Profit</h2>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="monthlyRevenueChart"></canvas>
                    </div>
                </div>
                
                <!-- Revenue by Commodity Chart -->
                <div class="chart-grid">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h2><i class="fas fa-boxes"></i> Revenue by Commodity</h2>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="revenueByCommodityChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Revenue by Route Chart -->
                    <div class="chart-container">
                        <div class="chart-header">
                            <h2><i class="fas fa-route"></i> Revenue by Route</h2>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="revenueByRouteChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bookings Tab -->
            <div id="bookings" class="tab-content">
                <!-- Booking KPIs -->
                <div class="stats-container">
                    <div class="stat-card">
                        <i class="fas fa-shipping-fast" style="color: #3498db;"></i>
                        <h3>Total Bookings</h3>
                        <div class="value"><?php echo number_format($kpi_data['total_bookings']); ?></div>
                        <div class="trend trend-up"><i class="fas fa-arrow-up"></i> 12% from last month</div>
                    </div>
                    
                    <div class="stat-card">
                        <i class="fas fa-check-circle" style="color: #2ecc71;"></i>
                        <h3>Completed Bookings</h3>
                        <div class="value"><?php echo number_format($kpi_data['completed_bookings']); ?></div>
                        <div class="trend trend-up"><i class="fas fa-arrow-up"></i> 8% from last month</div>
                    </div>
                    
                    <div class="stat-card">
                        <i class="fas fa-clock" style="color: #f39c12;"></i>
                        <h3>Pending Bookings</h3>
                        <div class="value"><?php echo number_format($kpi_data['pending_bookings']); ?></div>
                        <div class="trend trend-down"><i class="fas fa-arrow-down"></i> 5% from last month</div>
                    </div>
                    
                    <div class="stat-card">
                        <i class="fas fa-ban" style="color: #e74c3c;"></i>
                        <h3>Cancelled Bookings</h3>
                        <div class="value"><?php echo number_format($kpi_data['cancelled_bookings']); ?></div>
                        <div class="trend trend-down"><i class="fas fa-arrow-down"></i> 3% from last month</div>
                    </div>
                </div>
                
                <!-- Booking Status Chart -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h2><i class="fas fa-chart-pie"></i> Booking Status Distribution</h2>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="bookingStatusChart"></canvas>
                    </div>
                </div>
                
                <!-- Delivery Performance Chart -->
                <div class="chart-grid">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h2><i class="fas fa-truck"></i> Delivery Performance</h2>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="deliveryPerformanceChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Monthly Bookings Chart -->
                    <div class="chart-container">
                        <div class="chart-header">
                            <h2><i class="fas fa-calendar-alt"></i> Monthly Bookings</h2>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="monthlyBookingsChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Popular Routes Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2><i class="fas fa-route"></i> Popular Routes</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Route</th>
                                <th>Bookings</th>
                                <th>Total Weight (Tons)</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($popular_routes as $route): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($route['route']); ?></td>
                                    <td><?php echo number_format($route['bookings']); ?></td>
                                    <td><?php echo number_format($route['weight']); ?> tons</td>
                                    <td>₹<?php echo number_format($route['revenue']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Commodities Tab -->
            <div id="commodities" class="tab-content">
                <!-- Commodity Distribution Chart -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h2><i class="fas fa-boxes"></i> Commodity Distribution</h2>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="commodityDistributionChart"></canvas>
                    </div>
                </div>
                
                <!-- Commodity Weight vs Revenue Chart -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h2><i class="fas fa-balance-scale"></i> Commodity Weight vs Revenue</h2>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="commodityWeightRevenueChart"></canvas>
                    </div>
                </div>
                
                <!-- Top Commodities Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2><i class="fas fa-boxes"></i> Commodity Performance</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Commodity</th>
                                <th>Bookings</th>
                                <th>Total Weight (Tons)</th>
                                <th>Revenue</th>
                                <th>Avg. Price/Ton</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_commodities as $commodity): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($commodity['name']); ?></td>
                                    <td><?php echo number_format($commodity['bookings']); ?></td>
                                    <td><?php echo number_format($commodity['weight']); ?> tons</td>
                                    <td>₹<?php echo number_format($commodity['revenue']); ?></td>
                                    <td>₹<?php echo number_format($commodity['revenue'] / $commodity['weight'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Customers Tab -->
            <div id="customers" class="tab-content">
                <!-- Customer KPIs -->
                <div class="stats-container">
                    <div class="stat-card">
                        <i class="fas fa-user-plus" style="color: #3498db;"></i>
                        <h3>New Customers</h3>
                        <div class="value"><?php echo number_format($kpi_data['new_customers']); ?></div>
                        <div class="trend trend-up"><i class="fas fa-arrow-up"></i> 15% from last month</div>
                    </div>
                    
                    <div class="stat-card">
                        <i class="fas fa-sync" style="color: #2ecc71;"></i>
                        <h3>Repeat Booking Rate</h3>
                        <div class="value">68%</div>
                        <div class="trend trend-up"><i class="fas fa-arrow-up"></i> 4% from last month</div>
                    </div>
                    
                    <div class="stat-card">
                        <i class="fas fa-smile" style="color: #f39c12;"></i>
                        <h3>Customer Satisfaction</h3>
                        <div class="value">4.7/5</div>
                        <div class="trend trend-up"><i class="fas fa-arrow-up"></i> 0.2 from last month</div>
                    </div>
                    
                    <div class="stat-card">
                        <i class="fas fa-rupee-sign" style="color: #9b59b6;"></i>
                        <h3>Avg. Customer Value</h3>
                        <div class="value">₹<?php echo number_format($kpi_data['total_revenue'] / ($kpi_data['total_bookings'] * 0.7)); ?></div>
                        <div class="trend trend-up"><i class="fas fa-arrow-up"></i> 8% from last month</div>
                    </div>
                </div>
                
                <!-- User Registration Chart -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h2><i class="fas fa-user-plus"></i> New User Registrations</h2>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="userRegistrationChart"></canvas>
                    </div>
                </div>
                
                <!-- Customer Satisfaction Chart -->
                <div class="chart-grid">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h2><i class="fas fa-smile"></i> Customer Satisfaction</h2>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="customerSatisfactionChart"></canvas>
                        </div>
                    </div>
                    
                    <!-- Customer Retention Chart -->
                    <div class="chart-container">
                        <div class="chart-header">
                            <h2><i class="fas fa-users"></i> Customer Retention</h2>
                        </div>
                        <div class="chart-wrapper">
                            <canvas id="customerRetentionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Tab functionality
        function openTab(tabName) {
            // Hide all tab contents
            var tabContents = document.getElementsByClassName('tab-content');
            for (var i = 0; i < tabContents.length; i++) {
                tabContents[i].classList.remove('active');
            }
            
            // Remove active class from all tab buttons
            var tabButtons = document.getElementsByClassName('tab-button');
            for (var i = 0; i < tabButtons.length; i++) {
                tabButtons[i].classList.remove('active');
            }
            
            // Show the selected tab content and mark button as active
            document.getElementById(tabName).classList.add('active');
            event.currentTarget.classList.add('active');
        }
        
        // Revenue vs Expenses Chart
        var revenueCtx = document.getElementById('revenueChart').getContext('2d');
        var revenueChart = new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode($revenue_data); ?>,
                    backgroundColor: 'rgba(46, 204, 113, 0.5)',
                                      borderColor: 'rgba(46, 204, 113, 1)',
                    borderWidth: 2
                }, {
                    label: 'Expenses',
                    data: <?php echo json_encode($expense_data); ?>,
                    backgroundColor: 'rgba(231, 76, 60, 0.5)',
                    borderColor: 'rgba(231, 76, 60, 1)',
                    borderWidth: 2
                }, {
                    label: 'Profit',
                    data: <?php echo json_encode($profit_data); ?>,
                    backgroundColor: 'rgba(52, 152, 219, 0.5)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ₹' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        
        // Commodity Distribution Chart
        var commodityCtx = document.getElementById('commodityChart').getContext('2d');
        var commodityChart = new Chart(commodityCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($commodity_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($commodity_data); ?>,
                    backgroundColor: <?php echo json_encode($commodity_colors); ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.parsed || 0;
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = Math.round((value / total) * 100);
                                return label + ': ' + value.toLocaleString() + ' tons (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
        
        // Route Performance Chart
        var routeCtx = document.getElementById('routeChart').getContext('2d');
        var routeChart = new Chart(routeCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($route_labels); ?>,
                datasets: [{
                    label: 'Number of Bookings',
                    data: <?php echo json_encode($route_bookings); ?>,
                    backgroundColor: 'rgba(52, 152, 219, 0.7)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Monthly Revenue Chart
        var monthlyRevenueCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
        var monthlyRevenueChart = new Chart(monthlyRevenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode($revenue_data); ?>,
                    backgroundColor: 'rgba(46, 204, 113, 0.2)',
                    borderColor: 'rgba(46, 204, 113, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Profit',
                    data: <?php echo json_encode($profit_data); ?>,
                    backgroundColor: 'rgba(52, 152, 219, 0.2)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ₹' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        
        // Revenue by Commodity Chart
        var revenueByCommodityCtx = document.getElementById('revenueByCommodityChart').getContext('2d');
        var revenueByCommodityChart = new Chart(revenueByCommodityCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($commodity_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_map(function() { return mt_rand(100000, 500000); }, $commodity_labels)); ?>,
                    backgroundColor: <?php echo json_encode($commodity_colors); ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.parsed || 0;
                                return label + ': ₹' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        
        // Revenue by Route Chart
        var revenueByRouteCtx = document.getElementById('revenueByRouteChart').getContext('2d');
        var revenueByRouteChart = new Chart(revenueByRouteCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($route_labels); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode($route_revenue); ?>,
                    backgroundColor: 'rgba(155, 89, 182, 0.7)',
                    borderColor: 'rgba(155, 89, 182, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Revenue: ₹' + context.parsed.x.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        
        // Booking Status Chart
        var bookingStatusCtx = document.getElementById('bookingStatusChart').getContext('2d');
        var bookingStatusChart = new Chart(bookingStatusCtx, {
            type: 'pie',
            data: {
                labels: ['Completed', 'Pending', 'In Transit', 'Cancelled'],
                datasets: [{
                    data: [
                        <?php echo $kpi_data['completed_bookings']; ?>,
                        <?php echo $kpi_data['pending_bookings']; ?>,
                        <?php echo $kpi_data['in_transit_bookings']; ?>,
                        <?php echo $kpi_data['cancelled_bookings']; ?>
                    ],
                    backgroundColor: [
                        'rgba(46, 204, 113, 0.7)',
                        'rgba(243, 156, 18, 0.7)',
                        'rgba(52, 152, 219, 0.7)',
                        'rgba(231, 76, 60, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.parsed || 0;
                                var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                var percentage = Math.round((value / total) * 100);
                                return label + ': ' + value.toLocaleString() + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
        
        // Delivery Performance Chart
        var deliveryPerformanceCtx = document.getElementById('deliveryPerformanceChart').getContext('2d');
        var deliveryPerformanceChart = new Chart(deliveryPerformanceCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($delivery_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($delivery_data); ?>,
                    backgroundColor: [
                        'rgba(46, 204, 113, 0.7)',
                        'rgba(243, 156, 18, 0.7)',
                        'rgba(231, 76, 60, 0.5)',
                        'rgba(231, 76, 60, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.parsed || 0;
                                return label + ': ' + value + '%';
                            }
                        }
                    }
                }
            }
        });
        
        // Monthly Bookings Chart
        var monthlyBookingsCtx = document.getElementById('monthlyBookingsChart').getContext('2d');
        var monthlyBookingsChart = new Chart(monthlyBookingsCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Number of Bookings',
                    data: <?php echo json_encode(array_map(function() { return mt_rand(100, 500); }, $months)); ?>,
                    backgroundColor: 'rgba(52, 152, 219, 0.7)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Commodity Distribution Chart (for Commodities tab)
        var commodityDistributionCtx = document.getElementById('commodityDistributionChart').getContext('2d');
        var commodityDistributionChart = new Chart(commodityDistributionCtx, {
            type: 'polarArea',
            data: {
                labels: <?php echo json_encode($commodity_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($commodity_data); ?>,
                    backgroundColor: <?php echo json_encode($commodity_colors); ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.raw || 0;
                                return label + ': ' + value.toLocaleString() + ' tons';
                            }
                        }
                    }
                }
            }
        });
        
        // Commodity Weight vs Revenue Chart
        var commodityWeightRevenueCtx = document.getElementById('commodityWeightRevenueChart').getContext('2d');
        var commodityWeightRevenueChart = new Chart(commodityWeightRevenueCtx, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: 'Commodities',
                    data: <?php 
                        $scatter_data = [];
                        foreach ($top_commodities as $commodity) {
                            $scatter_data[] = [
                                'x' => $commodity['weight'],
                                'y' => $commodity['revenue'],
                                'commodity' => $commodity['name']
                            ];
                        }
                        echo json_encode($scatter_data);
                    ?>,
                    backgroundColor: 'rgba(52, 152, 219, 0.7)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 1,
                    pointRadius: 8,
                    pointHoverRadius: 12
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Total Weight (Tons)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString();
                            }
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Total Revenue (₹)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var commodity = context.raw.commodity;
                                return commodity + ' - Weight: ' + context.parsed.x.toLocaleString() + ' tons, Revenue: ₹' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        
        // User Registration Chart
        var userRegistrationCtx = document.getElementById('userRegistrationChart').getContext('2d');
        var userRegistrationChart = new Chart(userRegistrationCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($user_reg_months); ?>,
                datasets: [{
                    label: 'New Users',
                    data: <?php echo json_encode($user_reg_data); ?>,
                    backgroundColor: 'rgba(52, 152, 219, 0.2)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Customer Satisfaction Chart
        var customerSatisfactionCtx = document.getElementById('customerSatisfactionChart').getContext('2d');
        var customerSatisfactionChart = new Chart(customerSatisfactionCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($satisfaction_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($satisfaction_data); ?>,
                    backgroundColor: [
                        'rgba(46, 204, 113, 0.7)',
                        'rgba(52, 152, 219, 0.7)',
                        'rgba(243, 156, 18, 0.7)',
                        'rgba(231, 76, 60, 0.5)',
                        'rgba(231, 76, 60, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label || '';
                                var value = context.parsed || 0;
                                return label + ': ' + value + '%';
                            }
                        }
                    }
                }
            }
        });
        
        // Customer Retention Chart
        var customerRetentionCtx = document.getElementById('customerRetentionChart').getContext('2d');
        var customerRetentionChart = new Chart(customerRetentionCtx, {
            type: 'bar',
            data: {
                labels: ['First Time', '2-3 Bookings', '4-10 Bookings', '10+ Bookings'],
                datasets: [{
                    label: 'Number of Customers',
                    data: [1200, 850, 450, 300],
                    backgroundColor: [
                        'rgba(52, 152, 219, 0.7)',
                        'rgba(46, 204, 113, 0.7)',
                        'rgba(155, 89, 182, 0.7)',
                        'rgba(241, 196, 15, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        
        // Function to toggle chart type
        function toggleChartType(chartId) {
            if (chartId === 'revenueChart') {
                if (revenueChart.config.type === 'bar') {
                    revenueChart.config.type = 'line';
                } else {
                    revenueChart.config.type = 'bar';
                }
                revenueChart.update();
            }
        }
        
        // Export functions (placeholders)
        function exportToPDF() {
            alert('PDF export functionality would be implemented here.');
            // In a real implementation, you would use a library like jsPDF
        }
        
        function exportToExcel() {
            alert('Excel export functionality would be implemented here.');
            // In a real implementation, you would use a library like SheetJS
        }
    </script>
</body>
</html>
