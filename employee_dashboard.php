<?php
session_start();
include 'db_connect.php';

// Check if employee is logged in
if (!isset($_SESSION['employee_logged_in']) || $_SESSION['employee_logged_in'] !== true) {
    header("Location: employee_login.php");
    exit();
}

$employee_id = $_SESSION['employee_id'];
$employee_name = $_SESSION['employee_name'];
$employee_position = $_SESSION['employee_position'];

// Get assigned deliveries for drivers and delivery partners
$assigned_deliveries = [];
if (strpos($employee_position, 'Driver') !== false || $employee_position == 'Delivery Partner') {
    $stmt = $conn->prepare("SELECT a.id, a.booking_id, a.status, a.assigned_date, a.notes,
                           b.commodity_type, b.weight, b.source, b.destination, b.booking_date
                           FROM assignments a
                           JOIN bookings b ON a.booking_id = b.id
                           WHERE a.employee_id = ?
                           ORDER BY a.assigned_date DESC");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $assigned_deliveries[] = $row;
    }
}

// Get all bookings for logistics coordinators and station managers
$all_bookings = [];
$unassigned_bookings = [];
if ($employee_position == 'Logistics Coordinator' || $employee_position == 'Station Manager') {
    // Get all bookings
    $all_bookings_result = $conn->query("SELECT b.*,
                                        CASE WHEN a.id IS NULL THEN 'Unassigned' ELSE 'Assigned' END as assignment_status,
                                        e.name as assigned_to
                                        FROM bookings b
                                        LEFT JOIN assignments a ON b.id = a.booking_id
                                        LEFT JOIN employees e ON a.employee_id = e.id
                                        ORDER BY b.booking_date DESC");
    
    while ($row = $all_bookings_result->fetch_assoc()) {
        $all_bookings[] = $row;
        
        // Collect unassigned bookings
        if ($row['assignment_status'] == 'Unassigned') {
            $unassigned_bookings[] = $row;
        }
    }
    
    // Get available delivery personnel
    $delivery_personnel = $conn->query("SELECT id, name, position FROM employees
                                      WHERE (position LIKE '%Driver%' OR position = 'Delivery Partner')
                                      AND status = 'approved'");
}

// Update assignment status if requested
if (isset($_POST['update_status']) && isset($_POST['assignment_id'])) {
    $assignment_id = $_POST['assignment_id'];
    $new_status = $_POST['new_status'];
    $notes = isset($_POST['notes']) ? $_POST['notes'] : '';
    
    $update_stmt = $conn->prepare("UPDATE assignments SET status = ?, notes = ? WHERE id = ? AND employee_id = ?");
    $update_stmt->bind_param("ssii", $new_status, $notes, $assignment_id, $employee_id);
    
    if ($update_stmt->execute()) {
        // Also update the booking status
        $booking_id = $_POST['booking_id'];
        $booking_status = ($new_status == 'completed') ? 'delivered' : 'in_transit';
        
        $update_booking = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $update_booking->bind_param("si", $booking_status, $booking_id);
        $update_booking->execute();
        
        // Redirect to refresh the page
        header("Location: employee_dashboard.php?status_updated=1");
        exit();
    }
}

// Assign booking to employee if requested
if (isset($_POST['assign_booking'])) {
    // Make sure all required fields are present
    if (isset($_POST['booking_id']) && !empty($_POST['booking_id']) && 
        isset($_POST['assigned_employee']) && !empty($_POST['assigned_employee'])) {
        
        $booking_id = $_POST['booking_id'];
        $assigned_employee = $_POST['assigned_employee'];
        $notes = isset($_POST['assignment_notes']) ? $_POST['assignment_notes'] : '';
        
        // Check if booking is already assigned
        $check = $conn->prepare("SELECT id FROM assignments WHERE booking_id = ?");
        $check->bind_param("i", $booking_id);
        $check->execute();
        $check_result = $check->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing assignment
            $assignment = $check_result->fetch_assoc();
            $update = $conn->prepare("UPDATE assignments SET employee_id = ?, notes = ?, status = 'pending', assigned_date = NOW() WHERE id = ?");
            $update->bind_param("isi", $assigned_employee, $notes, $assignment['id']);
            $update->execute();
        } else {
            // Create new assignment
            $insert = $conn->prepare("INSERT INTO assignments (booking_id, employee_id, status, notes, assigned_date) VALUES (?, ?, 'pending', ?, NOW())");
            $insert->bind_param("iis", $booking_id, $assigned_employee, $notes);
            $insert->execute();
        }
        
        // Update booking status
        $update_booking = $conn->prepare("UPDATE bookings SET status = 'confirmed' WHERE id = ?");
        $update_booking->bind_param("i", $booking_id);
        $update_booking->execute();
        
        // Redirect to refresh the page
        header("Location: employee_dashboard.php?assigned=1");
        exit();
    } else {
        // Handle error - missing required fields
        header("Location: employee_dashboard.php?error=missing_fields");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - FreightX</title>
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
        
        .date-time {
            font-size: 14px;
            color: #7f8c8d;
        }
        
        /* Dashboard Sections */
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
        
        /* Status Badges */
        .status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        
        .status-pending {
            background: #f39c12;
            color: white;
        }
        
        .status-in-progress {
            background: #3498db;
            color: white;
        }
        
        .status-completed {
            background: #2ecc71;
            color: white;
        }
        
        .status-cancelled {
            background: #e74c3c;
            color: white;
        }
        
        /* Buttons */
        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
            display: inline-block;
            text-decoration: none;
            text-align: center;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-success {
            background: #2ecc71;
            color: white;
        }
        
        .btn-warning {
            background: #f39c12;
            color: white;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn:hover {
            opacity: 0.8;
        }
        
        /* Forms */
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        select, textarea, input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #333;
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
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
                <div class="employee-info">
                    Welcome, <?php echo htmlspecialchars($employee_name); ?>
                </div>
                <div class="employee-position">
                    <?php echo htmlspecialchars($employee_position); ?>
                </div>
            </div>
            
            <div class="sidebar-menu">
                <a href="employee_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                
                <?php if (strpos($employee_position, 'Driver') !== false || $employee_position == 'Delivery Partner'): ?>
                    <a href="#deliveries"><i class="fas fa-truck"></i> My Deliveries</a>
                    <a href="#completed"><i class="fas fa-check-circle"></i> Completed Tasks</a>
                <?php endif; ?>
                
                <?php if ($employee_position == 'Logistics Coordinator' || $employee_position == 'Station Manager'): ?>
                    <a href="#all-bookings"><i class="fas fa-clipboard-list"></i> All Bookings</a>
                    <a href="#unassigned"><i class="fas fa-tasks"></i> Unassigned Bookings</a>
                    <a href="#employees"><i class="fas fa-users"></i> Delivery Personnel</a>
                <?php endif; ?>
                
                <a href="employee_profile.php"><i class="fas fa-user"></i> My Profile</a>
            </div>
            
            <form action="employee_logout.php" method="post">
                <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1>Employee Dashboard</h1>
                <div class="date-time">Loading...</div>
            </div>
            
            <?php if (isset($_GET['status_updated'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Delivery status updated successfully!
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['assigned'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Booking assigned successfully!
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error']) && $_GET['error'] == 'missing_fields'): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> Error: Please fill in all required fields.
                </div>
            <?php endif; ?>
            
            <!-- Driver/Delivery Partner View -->
            <?php if (strpos($employee_position, 'Driver') !== false || $employee_position == 'Delivery Partner'): ?>
                
                <!-- Current Deliveries Section -->
                <div class="dashboard-section" id="deliveries">
                    <div class="section-header">
                        <h2><i class="fas fa-truck"></i> My Current Deliveries</h2>
                    </div>
                    
                    <?php if (count($assigned_deliveries) > 0): ?>
                        <table>
                            <tr>
                                <th>ID</th>
                                <th>Commodity</th>
                                <th>Weight</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Assigned Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            
                            <?php foreach ($assigned_deliveries as $delivery): ?>
                                <?php if ($delivery['status'] != 'completed'): ?>
                                <tr>
                                    <td>#<?php echo $delivery['booking_id']; ?></td>
                                    <td><?php echo htmlspecialchars($delivery['commodity_type']); ?></td>
                                    <td><?php echo $delivery['weight']; ?> kg</td>
                                    <td><?php echo htmlspecialchars($delivery['source']); ?></td>
                                    <td><?php echo htmlspecialchars($delivery['destination']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($delivery['assigned_date'])); ?></td>
                                    <td>
                                        <span class="status status-<?php echo $delivery['status'] == 'pending' ? 'pending' : 'in-progress'; ?>">
                                            <?php echo ucfirst($delivery['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary" onclick="openUpdateModal(<?php echo $delivery['id']; ?>, <?php echo $delivery['booking_id']; ?>)">
                                            Update Status
                                        </button>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-truck"></i>
                            <p>No deliveries assigned to you yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Completed Deliveries Section -->
                <div class="dashboard-section" id="completed">
                    <div class="section-header">
                        <h2><i class="fas fa-check-circle"></i> Completed Deliveries</h2>
                    </div>
                    
                    <?php
                    $completed_deliveries = array_filter($assigned_deliveries, function($delivery) {
                        return $delivery['status'] == 'completed';
                    });
                    
                    if (count($completed_deliveries) > 0):
                    ?>
                        <table>
                            <tr>
                                <th>ID</th>
                                <th>Commodity</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Completed Date</th>
                                <th>Notes</th>
                            </tr>
                            
                            <?php foreach ($completed_deliveries as $delivery): ?>
                                <tr>
                                    <td>#<?php echo $delivery['booking_id']; ?></td>
                                    <td><?php echo htmlspecialchars($delivery['commodity_type']); ?></td>
                                    <td><?php echo htmlspecialchars($delivery['source']); ?></td>
                                    <td><?php echo htmlspecialchars($delivery['destination']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($delivery['assigned_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($delivery['notes']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-clipboard-check"></i>
                            <p>You haven't completed any deliveries yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
            <?php endif; ?>
            
            <!-- Logistics Coordinator/Station Manager View -->
            <?php if ($employee_position == 'Logistics Coordinator' || $employee_position == 'Station Manager'): ?>
                
                <!-- All Bookings Section -->
                <div class="dashboard-section" id="all-bookings">
                    <div class="section-header">
                        <h2><i class="fas fa-clipboard-list"></i> All Bookings</h2>
                    </div>
                    
                    <?php if (count($all_bookings) > 0): ?>
                        <table>
                            <tr>
                                <th>ID</th>
                                <th>Commodity</th>
                                <th>Weight</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Booking Date</th>
                                <th>Status</th>
                                <th>Assignment</th>
                                <th>Action</th>
                            </tr>
                            
                            <?php foreach ($all_bookings as $booking): ?>
                                <tr>
                                    <td>#<?php echo $booking['id']; ?></td>
                                    <td><?php echo htmlspecialchars($booking['commodity_type']); ?></td>
                                    <td><?php echo $booking['weight']; ?> kg</td>
                                    <td><?php echo htmlspecialchars($booking['source']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['destination']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                                    <td>
                                        <span class="status status-<?php echo strtolower($booking['status']); ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($booking['assignment_status'] == 'Assigned'): ?>
                                            <span class="status status-completed">
                                                Assigned to <?php echo htmlspecialchars($booking['assigned_to']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="status status-pending">
                                                Unassigned
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($booking['assignment_status'] == 'Unassigned'): ?>
                                            <button class="btn btn-primary" onclick="openAssignModal(<?php echo $booking['id']; ?>)">
                                                Assign
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-warning" onclick="openReassignModal(<?php echo $booking['id']; ?>)">
                                                Reassign
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-clipboard"></i>
                            <p>No bookings have been made yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Unassigned Bookings Section -->
                <div class="dashboard-section" id="unassigned">
                    <div class="section-header">
                        <h2><i class="fas fa-tasks"></i> Unassigned Bookings</h2>
                    </div>
                    
                    <?php if (count($unassigned_bookings) > 0): ?>
                        <table>
                            <tr>
                                <th>ID</th>
                                <th>Commodity</th>
                                <th>Weight</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Booking Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            
                            <?php foreach ($unassigned_bookings as $booking): ?>
                                <tr>
                                    <td>#<?php echo $booking['id']; ?></td>
                                    <td><?php echo htmlspecialchars($booking['commodity_type']); ?></td>
                                    <td><?php echo $booking['weight']; ?> kg</td>
                                    <td><?php echo htmlspecialchars($booking['source']); ?></td>
                                    <td><?php echo htmlspecialchars($booking['destination']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></td>
                                    <td>
                                        <span class="status status-<?php echo strtolower($booking['status']); ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary" onclick="openAssignModal(<?php echo $booking['id']; ?>)">
                                            Assign
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <p>All bookings have been assigned. Great job!</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Delivery Personnel Section -->
                <div class="dashboard-section" id="employees">
                    <div class="section-header">
                        <h2><i class="fas fa-users"></i> Delivery Personnel</h2>
                    </div>
                    
                    <?php if ($delivery_personnel && $delivery_personnel->num_rows > 0): ?>
                        <table>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Action</th>
                            </tr>
                            
                            <?php while ($employee = $delivery_personnel->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $employee['id']; ?></td>
                                    <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                    <td><?php echo htmlspecialchars($employee['position']); ?></td>
                                    <td>
                                        <a href="view_employee_assignments.php?id=<?php echo $employee['id']; ?>" class="btn btn-primary">
                                            View Assignments
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <p>No delivery personnel available.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Update Status Modal -->
    <div id="updateStatusModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('updateStatusModal')">&times;</span>
            <h2>Update Delivery Status</h2>
            <form method="POST" action="">
                <input type="hidden" id="assignment_id" name="assignment_id">
                <input type="hidden" id="booking_id" name="booking_id">
                
                <div class="form-group">
                    <label for="new_status">Status:</label>
                    <select id="new_status" name="new_status" required>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="notes">Notes:</label>
                    <textarea id="notes" name="notes" rows="4" placeholder="Add any delivery notes here..."></textarea>
                </div>
                
                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
            </form>
        </div>
    </div>
    
    <!-- Assign Booking Modal -->
    <div id="assignModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('assignModal')">&times;</span>
            <h2>Assign Booking</h2>
            <form method="POST" action="">
                <input type="hidden" id="assign_booking_id" name="booking_id">
                
                <div class="form-group">
                    <label for="assigned_employee">Assign to:</label>
                    <select id="assigned_employee" name="assigned_employee" required>
                        <option value="">Select Employee</option>
                        <?php if ($delivery_personnel && $delivery_personnel->num_rows > 0): ?>
                            <?php
                            // Reset the result pointer to the beginning
                            $delivery_personnel->data_seek(0);
                            while ($employee = $delivery_personnel->fetch_assoc()):
                            ?>
                                <option value="<?php echo $employee['id']; ?>">
                                    <?php echo htmlspecialchars($employee['name']); ?> (<?php echo htmlspecialchars($employee['position']); ?>)
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="assignment_notes">Assignment Notes:</label>
                    <textarea id="assignment_notes" name="assignment_notes" rows="4" placeholder="Add any special instructions here..."></textarea>
                </div>
                
                <button type="submit" name="assign_booking" class="btn btn-primary">Assign Booking</button>
            </form>
        </div>
    </div>
    
    <!-- Reassign Booking Modal -->
    <div id="reassignModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('reassignModal')">&times;</span>
            <h2>Reassign Booking</h2>
            <form method="POST" action="">
                <input type="hidden" id="reassign_booking_id" name="booking_id">
                
                <div class="form-group">
                    <label for="reassign_employee">Reassign to:</label>
                    <select id="reassign_employee" name="assigned_employee" required>
                        <option value="">Select Employee</option>
                        <?php if ($delivery_personnel && $delivery_personnel->num_rows > 0): ?>
                            <?php
                            // Reset the result pointer to the beginning
                            $delivery_personnel->data_seek(0);
                            while ($employee = $delivery_personnel->fetch_assoc()):
                            ?>
                                <option value="<?php echo $employee['id']; ?>">
                                    <?php echo htmlspecialchars($employee['name']); ?> (<?php echo htmlspecialchars($employee['position']); ?>)
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="reassignment_notes">Reassignment Notes:</label>
                    <textarea id="reassignment_notes" name="assignment_notes" rows="4" placeholder="Add reason for reassignment here..."></textarea>
                </div>
                
                <button type="submit" name="assign_booking" class="btn btn-warning">Reassign Booking</button>
            </form>
        </div>
    </div>
    
    <script>
        // Update date and time
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
        
        // Initial call and set interval
        updateDateTime();
        setInterval(updateDateTime, 60000);
        
        // Modal functions
        function openUpdateModal(assignmentId, bookingId) {
            document.getElementById('assignment_id').value = assignmentId;
            document.getElementById('booking_id').value = bookingId;
            document.getElementById('updateStatusModal').style.display = 'block';
        }
        
        function openAssignModal(bookingId) {
            document.getElementById('assign_booking_id').value = bookingId;
            document.getElementById('assignModal').style.display = 'block';
        }
        
        function openReassignModal(bookingId) {
            document.getElementById('reassign_booking_id').value = bookingId;
            document.getElementById('reassignModal').style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target.className === 'modal') {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html>

