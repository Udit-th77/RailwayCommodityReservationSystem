<?php
session_start();
include 'db_connect.php';

// Redirect if already logged in
if (isset($_SESSION['employee_logged_in']) && $_SESSION['employee_logged_in'] === true) {
    header("Location: employee_dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);
    
    if (!empty($email) && !empty($password)) {
        // Query to check employee credentials
        $stmt = $conn->prepare("SELECT id, name, email, password, position, status FROM employees WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $employee = $result->fetch_assoc();
            
            // Check if employee is approved
            if ($employee['status'] !== 'approved') {
                $error = "Your account is not yet approved. Please contact admin.";
            } else {
                // Verify password
                if (password_verify($password, $employee['password'])) {
                    // Set session variables
                    $_SESSION['employee_id'] = $employee['id'];
                    $_SESSION['employee_name'] = $employee['name'];
                    $_SESSION['employee_email'] = $employee['email'];
                    $_SESSION['employee_position'] = $employee['position'];
                    $_SESSION['employee_logged_in'] = true;
                    $_SESSION['last_activity'] = time(); // For session timeout
                    
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    // Redirect to employee dashboard
                    header("Location: employee_dashboard.php");
                    exit();
                } else {
                    $error = "Invalid password. Please try again.";
                    // Add delay to prevent brute force attacks
                    sleep(1);
                }
            }
        } else {
            $error = "Account not found. Please check your email.";
            // Add delay to prevent brute force attacks
            sleep(1);
        }
    } else {
        $error = "Please enter both email and password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Login - FreightX</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #3498db, #2c3e50);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 400px;
            padding: 40px;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #7f8c8d;
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        
        input:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.5);
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 38px;
            cursor: pointer;
            color: #7f8c8d;
        }
        
        button {
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s, transform 0.1s;
        }
        
        button:hover {
            background: #2980b9;
        }
        
        button:active {
            transform: scale(0.98);
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            animation: shake 0.5s ease-in-out;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #2c3e50;
        }
        
        .register-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        
        .register-link a:hover {
            color: #2980b9;
            text-decoration: underline;
        }
        
        .forgot-password {
            text-align: right;
            margin-top: -15px;
            margin-bottom: 15px;
        }
        
        .forgot-password a {
            color: #7f8c8d;
            font-size: 14px;
            text-decoration: none;
        }
        
        .forgot-password a:hover {
            color: #3498db;
            text-decoration: underline;
        }
        
        .home-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: transform 0.3s;
        }
        
        .home-link:hover {
            transform: translateX(-3px);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo img {
            max-width: 150px;
            height: auto;
        }
    </style>
</head>
<body>
    <a href="index.php" class="home-link"><i class="fas fa-home"></i> Back to Home</a>
    
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <!-- Add your logo here if available -->
                <!-- <img src="images/logo.png" alt="FreightX Logo"> -->
            </div>
            <h1>Employee Login</h1>
            <p>Access your FreightX employee account</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" autocomplete="off">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <span class="password-toggle" onclick="togglePassword()">
                    <i class="fas fa-eye" id="toggleIcon"></i>
                </span>
            </div>
            
            <div class="forgot-password">
                <a href="employee_register.php">Forgot Password?</a>
            </div>
            
            <button type="submit">Login <i class="fas fa-sign-in-alt"></i></button>
        </form>
        
        <div class="register-link">
            Don't have an account? <a href="employee_register.php">Register here</a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Auto-hide error message after 5 seconds
        const errorMessage = document.querySelector('.error-message');
        if (errorMessage) {
            setTimeout(() => {
                errorMessage.style.opacity = '0';
                errorMessage.style.transition = 'opacity 1s';
                setTimeout(() => {
                    errorMessage.style.display = 'none';
                }, 1000);
            }, 5000);
        }
    </script>
</body>
</html>
