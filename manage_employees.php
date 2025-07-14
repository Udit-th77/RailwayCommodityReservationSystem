<?php
session_start();
include 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$message = "";
$error = "";

// Approve employee
if (isset($_GET['approve']) && !empty($_GET['approve'])) {
    $employee_id = $_GET['approve'];
    
    $stmt = $conn->prepare("UPDATE employees SET status = 'approved' WHERE id = ?");
    $stmt->bind_param("i", $employee_id);
    
    if ($stmt->execute()) {
        $message = "Employee approved successfully!";
    } else {
        $error = "Failed to approve employee: " . $conn->error;
    }
}

// Suspend employee
if (isset($_GET['suspend']) && !empty($_GET['suspend'])) {
    $employee_id = $_GET['suspend'];
    
    $stmt = $conn->prepare("UPDATE employees SET status = 'suspended' WHERE id = ?");
    $stmt->bind_param("i", $employee_id);
    
    if ($stmt->execute()) {
        $message = "Employee suspended successfully!";
    } else {
        $error = "Failed to suspend employee: " . $conn->error;
    }
}

// Delete employee
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $employee_id = $_GET['delete'];
    
    // Check if employee has assignments
    $check = $conn->prepare("SELECT id FROM assignments WHERE employee_id = ?");
    $check->bind_param("i", $employee_id);
    $check->execute();
    $result = $check->get_result();
    
    if ($result->num_rows > 0) {
        $error = "Cannot delete employee with active assignments. Reassign or complete their tasks first.";
    } else {
        $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->bind_param("i", $employee_id);
        
        if ($stmt->execute()) {
            $message = "Employee deleted successfully!";
        } else {
            $error = "Failed to delete employee: " . $conn->error;
        }
    }
}

// Get all employees
$employees = $conn->query("SELECT * FROM employees ORDER BY status, position, name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Employees - FreightX Admin</title>
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
        
        /* Employee Section */
        .employee-section {
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
        
        .status-approved {
            background: #2ecc71;
            color: white;
        }
        
        .status-pending {
            background: #f39c12;
            color: white;
        }
        
        .status-suspended {
            background: #e74c3c;
            color: white;
        }
        
        /* Action Buttons */
        .action-btn {
            padding: 6px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            margin-right: 5px;
            transition: 0.3s;
        }
        
        .btn-approve {
            background: #2ecc71;
            color: white;
        }
        
        .btn-suspend {
            background: #f39c12;
            color: white;
        }
        
        .btn-delete {
            background: #e74c3c;
            color: white;
        }
        
        .btn-view {
            background: #3498db;
            color: white;
        }
        
        .action-btn:hover {
            opacity: 0.8;
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
        
        /* Add Employee Button */
        .add-btn {
            background: #3498db;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: 0.3s;
        }
        
        .add-btn:hover {
            background: #2980b9;
        }
        
        /* Confirmation Modal */
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
            margin: 15% auto;
            padding: 20px;
            border-radius: 10px;
            width: 50%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        
        .modal-content h3 {
            margin-bottom: 20px;
            color: #2c3e50;
        }
        
        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .modal-btn {
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            border: none;
        }
        
        .modal-btn-cancel {
            background: #7f8c8d;
            color: white;
        }
        
        .modal-btn-confirm {
            background: #e74c3c;
            color: white;
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
                    Admin Panel
                </div>
            </div>
            
            <div class="sidebar-menu">
                <a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="manage_bookings.php"><i class="fas fa-clipboard-list"></i> Bookings</a>
                <a href="manage_employees.php" class="active"><i class="fas fa-users"></i> Employees</a>
                <a href="manage_users.php"><i class="fas fa-user-friends"></i> Users</a>
                <a href="view_support_queries.php"><i class="fas fa-question-circle"></i> Support Queries</a>
                <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
            </div>
            
            <form action="admin_logout.php" method="post">
                <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="page-header">
                <h1>Manage Employees</h1>
                <a href="add_employee.php" class="add-btn"><i class="fas fa-plus"></i> Add New Employee</a>
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
            
            <!-- Employees Section -->
            <div class="employee-section">
                <div class="section-header">
                    <h2><i class="fas fa-users"></i> All Employees</h2>
                </div>
                
                <?php if ($employees && $employees->num_rows > 0): ?>
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th>Joined Date</th>
                            <th>Actions</th>
                        </tr>
                        
                        <?php while ($employee = $employees->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $employee['id']; ?></td>
                                <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                <td><?php echo htmlspecialchars($employee['phone']); ?></td>
                                <td><?php echo htmlspecialchars($employee['position']); ?></td>
                                <td>
                                    <span class="status status-<?php echo $employee['status']; ?>">
                                        <?php echo ucfirst($employee['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($employee['created_at'])); ?></td>
                                <td>
                                    <a href="view_employee.php?id=<?php echo $employee['id']; ?>" class="action-btn btn-view">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    
                                    <?php if ($employee['status'] == 'pending'): ?>
                                        <a href="manage_employees.php?approve=<?php echo $employee['id']; ?>" class="action-btn btn-approve">
                                            <i class="fas fa-check"></i> Approve
                                        </a>
                                    <?php elseif ($employee['status'] == 'approved'): ?>
                                        <a href="manage_employees.php?suspend=<?php echo $employee['id']; ?>" class="action-btn btn-suspend">
                                            <i class="fas fa-pause"></i> Suspend
                                        </a>
                                    <?php elseif ($employee['status'] == 'suspended'): ?>
                                        <a href="manage_employees.php?approve=<?php echo $employee['id']; ?>" class="action-btn btn-approve">
                                            <i class="fas fa-play"></i> Reactivate
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="#" class="action-btn btn-delete" onclick="confirmDelete(<?php echo $employee['id']; ?>)">
                                        <i class="fas fa-trash"></i> Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 30px; color: #7f8c8d;">
                        <i class="fas fa-users" style="font-size: 50px; margin-bottom: 10px;"></i>
                        <p>No employees found. Add your first employee to get started.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Deletion</h3>
            <p>Are you sure you want to delete this employee? This action cannot be undone.</p>
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-cancel" onclick="closeModal()">Cancel</button>
                <a href="#" id="confirmDeleteBtn" class="modal-btn modal-btn-confirm">Delete</a>
            </div>
        </div>
    </div>
    
    <script>
        // Delete confirmation
        function confirmDelete(employeeId) {
            const modal = document.getElementById('deleteModal');
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            
            modal.style.display = 'block';
            confirmBtn.href = 'manage_employees.php?delete=' + employeeId;
        }
        
        function closeModal() {
            const modal = document.getElementById('deleteModal');
            modal.style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
