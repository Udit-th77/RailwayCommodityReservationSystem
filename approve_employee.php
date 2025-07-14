<?php
session_start();
include 'db_connect.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$success_message = "";
$error_message = "";

// Handle employee approval/rejection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['approve_employee'])) {
        $employee_id = $_POST['employee_id'];
        
        // Update employee status to approved
        $stmt = $conn->prepare("UPDATE employees SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $employee_id);
        
        if ($stmt->execute()) {
            $success_message = "Employee has been approved successfully.";
        } else {
            $error_message = "Error approving employee. Please try again.";
        }
    } elseif (isset($_POST['reject_employee'])) {
        $employee_id = $_POST['employee_id'];
        
        // Update employee status to rejected
        $stmt = $conn->prepare("UPDATE employees SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $employee_id);
        
        if ($stmt->execute()) {
            $success_message = "Employee has been rejected.";
        } else {
            $error_message = "Error rejecting employee. Please try again.";
        }
    } elseif (isset($_POST['delete_employee'])) {
        $employee_id = $_POST['employee_id'];
        
        // Delete employee record
        $stmt = $conn->prepare("DELETE FROM employees WHERE id = ?");
        $stmt->bind_param("i", $employee_id);
        
        if ($stmt->execute()) {
            $success_message = "Employee record has been deleted.";
        } else {
            $error_message = "Error deleting employee record. Please try again.";
        }
    }
}

// Get all pending employees
$pending_employees = [];
$pending_query = "SELECT id, name, email, phone, position, created_at FROM employees WHERE status = 'pending' ORDER BY created_at DESC";
$pending_result = $conn->query($pending_query);

if ($pending_result && $pending_result->num_rows > 0) {
    while ($row = $pending_result->fetch_assoc()) {
        $pending_employees[] = $row;
    }
}

// Get all approved employees
$approved_employees = [];
$approved_query = "SELECT id, name, email, phone, position, created_at FROM employees WHERE status = 'approved' ORDER BY name ASC";
$approved_result = $conn->query($approved_query);

if ($approved_result && $approved_result->num_rows > 0) {
    while ($row = $approved_result->fetch_assoc()) {
        $approved_employees[] = $row;
    }
}

// Get all rejected employees
$rejected_employees = [];
$rejected_query = "SELECT id, name, email, phone, position, created_at FROM employees WHERE status = 'rejected' ORDER BY created_at DESC";
$rejected_result = $conn->query($rejected_query);

if ($rejected_result && $rejected_result->num_rows > 0) {
    while ($row = $rejected_result->fetch_assoc()) {
        $rejected_employees[] = $row;
    }
}
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
            padding-bottom: 50px;
        }
        
        .container {
            width: 95%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .header h1 {
            color: #2c3e50;
            font-size: 28px;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .btn-secondary {
            background: #2c3e50;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #1a252f;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-success {
            background: #2ecc71;
            color: white;
        }
        
        .btn-success:hover {
            background: #27ae60;
        }
        
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
        
        .tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .tab {
            padding: 12px 20px;
            cursor: pointer;
            font-weight: bold;
            color: #7f8c8d;
            position: relative;
            transition: all 0.3s;
        }
        
        .tab.active {
            color: #3498db;
        }
        
        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background: #3498db;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .employee-count {
            background: #f1f1f1;
            color: #333;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
            margin-left: 5px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .card-body {
            padding: 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th, table td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #2c3e50;
        }
        
        table tr:last-child td {
            border-bottom: none;
        }
        
        table tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .badge-pending {
            background: #f39c12;
            color: white;
        }
        
        .badge-approved {
            background: #2ecc71;
            color: white;
        }
        
        .badge-rejected {
            background: #e74c3c;
            color: white;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .action-buttons form {
            margin: 0;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #7f8c8d;
        }
        
        .empty-state i {
            font-size: 50px;
            margin-bottom: 20px;
            color: #bdc3c7;
        }
        
        .empty-state p {
            font-size: 18px;
            margin-bottom: 20px;
        }
        
        /* Modal styles */
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
        
        .modal-header {
            margin-bottom: 20px;
        }
        
        .modal-footer {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .header-actions {
                width: 100%;
            }
            
            .tabs {
                overflow-x: auto;
                white-space: nowrap;
                padding-bottom: 5px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-users"></i> Manage Employees</h1>
            <div class="header-actions">
                <a href="admin_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <div class="tabs">
            <div class="tab active" data-tab="pending">
                Pending Approvals <span class="employee-count"><?php echo count($pending_employees); ?></span>
            </div>
            <div class="tab" data-tab="approved">
                Approved Employees <span class="employee-count"><?php echo count($approved_employees); ?></span>
            </div>
            <div class="tab" data-tab="rejected">
                Rejected Applications <span class="employee-count"><?php echo count($rejected_employees); ?></span>
            </div>
        </div>
        
        <!-- Pending Employees Tab -->
        <div class="tab-content active" id="pending-tab">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-clock"></i> Pending Employee Approvals
                </div>
                <div class="card-body">
                    <?php if (count($pending_employees) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Position</th>
                                    <th>Applied On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending_employees as $employee): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['position']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($employee['created_at'])); ?></td>
                                        <td class="action-buttons">
                                            <form method="POST" action="">
                                                <input type="hidden" name="employee_id" value="<?php echo $employee['id']; ?>">
                                                <button type="submit" name="approve_employee" class="btn btn-success" title="Approve">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                            <form method="POST" action="">
                                            <input type="hidden" name="employee_id" value="<?php echo $employee['id']; ?>">
                                                <button type="submit" name="reject_employee" class="btn btn-danger" title="Reject">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </form>
                                            <button class="btn btn-primary" onclick="viewEmployeeDetails(<?php echo $employee['id']; ?>)" title="View Details">
                                                <i class="fas fa-eye"></i> Details
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <p>No pending employee approvals.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Approved Employees Tab -->
        <div class="tab-content" id="approved-tab">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-check"></i> Approved Employees
                </div>
                <div class="card-body">
                    <?php if (count($approved_employees) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Position</th>
                                    <th>Joined On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($approved_employees as $employee): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['position']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($employee['created_at'])); ?></td>
                                        <td class="action-buttons">
                                            <button class="btn btn-primary" onclick="viewEmployeeDetails(<?php echo $employee['id']; ?>)" title="View Details">
                                                <i class="fas fa-eye"></i> Details
                                            </button>
                                            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to reject this employee?');">
                                                <input type="hidden" name="employee_id" value="<?php echo $employee['id']; ?>">
                                                <button type="submit" name="reject_employee" class="btn btn-danger" title="Reject">
                                                    <i class="fas fa-user-times"></i> Revoke
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <p>No approved employees yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Rejected Employees Tab -->
        <div class="tab-content" id="rejected-tab">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-user-times"></i> Rejected Applications
                </div>
                <div class="card-body">
                    <?php if (count($rejected_employees) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Position</th>
                                    <th>Applied On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rejected_employees as $employee): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['phone']); ?></td>
                                        <td><?php echo htmlspecialchars($employee['position']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($employee['created_at'])); ?></td>
                                        <td class="action-buttons">
                                            <form method="POST" action="">
                                                <input type="hidden" name="employee_id" value="<?php echo $employee['id']; ?>">
                                                <button type="submit" name="approve_employee" class="btn btn-success" title="Approve">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                            <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this employee record?');">
                                                <input type="hidden" name="employee_id" value="<?php echo $employee['id']; ?>">
                                                <button type="submit" name="delete_employee" class="btn btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-check-circle"></i>
                            <p>No rejected applications.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Employee Details Modal -->
        <div id="employeeDetailsModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <div class="modal-header">
                    <h2><i class="fas fa-user"></i> Employee Details</h2>
                </div>
                <div id="employeeDetailsContent">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeModal()">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Tab switching functionality
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all tab contents
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                // Show the corresponding tab content
                document.getElementById(this.dataset.tab + '-tab').classList.add('active');
            });
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 1s';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 1000);
            });
        }, 5000);
        
        // Modal functionality
        function viewEmployeeDetails(employeeId) {
            // In a real application, you would fetch employee details via AJAX
            // For this example, we'll just show a placeholder
            document.getElementById('employeeDetailsContent').innerHTML = `
                <div style="padding: 20px;">
                    <p>Loading details for employee ID: ${employeeId}...</p>
                    <p>In a real application, this would show detailed information about the employee.</p>
                </div>
            `;
            
            document.getElementById('employeeDetailsModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('employeeDetailsModal').style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target == document.getElementById('employeeDetailsModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>
