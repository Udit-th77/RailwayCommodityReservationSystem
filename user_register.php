<?php
session_start();
include 'db_connect.php';

$error = "";
$success = "";
$email_warning = "";

// Check if user is already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    header("Location: user_dashboard.php");
    exit();
}

// Function to validate email domain
function validateEmailDomain($email) {
    // Extract domain from email
    $domain = substr(strrchr($email, "@"), 1);
    
    // List of common email providers and their correct spellings
    $commonDomains = [
        // Gmail variations
        'gmail.com' => 'gmail.com',
        'gmail.co' => 'gmail.com',
        'gamil.com' => 'gmail.com',
        'gmial.com' => 'gmail.com',
        'gmal.com' => 'gmail.com',
        'gmai.com' => 'gmail.com',
        'gmail.con' => 'gmail.com',
        'gmail.cm' => 'gmail.com',
        
        // Yahoo variations
        'yahoo.com' => 'yahoo.com',
        'yaho.com' => 'yahoo.com',
        'yahooo.com' => 'yahoo.com',
        'yahoo.co' => 'yahoo.com',
        'yahoo.con' => 'yahoo.com',
        
        // Hotmail variations
        'hotmail.com' => 'hotmail.com',
        'hotmal.com' => 'hotmail.com',
        'hotmai.com' => 'hotmail.com',
        'hotmail.co' => 'hotmail.com',
        
        // Outlook variations
        'outlook.com' => 'outlook.com',
        'outlook.co' => 'outlook.com',
        'outlok.com' => 'outlook.com',
        
        // Other common providers
        'icloud.com' => 'icloud.com',
        'protonmail.com' => 'protonmail.com',
        'aol.com' => 'aol.com',
        'zoho.com' => 'zoho.com',
        'yandex.com' => 'yandex.com',
        'mail.com' => 'mail.com',
        'rediffmail.com' => 'rediffmail.com'
    ];
    
    // Check if domain is in our list of common domains with typos
    if (array_key_exists(strtolower($domain), $commonDomains)) {
        $correctDomain = $commonDomains[strtolower($domain)];
        
        // If the domain is already correct, return null (no warning)
        if (strtolower($domain) === $correctDomain) {
            return null;
        }
        
        // Otherwise, return the correct domain as a suggestion
        return $correctDomain;
    }
    
    // Check for educational or organizational domains
    if (preg_match('/\.(edu|ac\.(in|uk|us)|edu\.(in|au)|org|gov|mil)$/', $domain)) {
        return null; // These are likely legitimate
    }
    
    // For all other domains, perform a basic check
    // Ensure the domain has at least one dot and valid TLD
    if (strpos($domain, '.') !== false) {
        $tld = substr($domain, strrpos($domain, '.') + 1);
        $validTLDs = ['com', 'net', 'org', 'edu', 'gov', 'mil', 'int', 'io', 'co', 'in', 'us', 'uk', 'ca', 'au', 'de', 'jp', 'fr', 'it', 'es', 'nl', 'ru', 'br', 'mx', 'ch', 'at', 'be', 'dk', 'fi', 'no', 'se', 'pl', 'pt', 'tr', 'nz', 'sg', 'ae', 'kr', 'cn', 'tw', 'hk', 'info', 'biz', 'name', 'pro', 'museum', 'coop', 'aero', 'xxx', 'idv', 'mobi', 'asia', 'tel', 'travel', 'dev', 'app', 'tech'];
        
        if (!in_array($tld, $validTLDs)) {
            return "unknown TLD"; // Unknown TLD
        }
    } else {
        return "invalid format"; // No dot in domain
    }
    
    return null; // Domain seems valid
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Basic validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Validate email domain
        $domainCheck = validateEmailDomain($email);
        
        if ($domainCheck === "invalid format") {
            $error = "Your email domain appears to be invalid. Please check your email address.";
        } elseif ($domainCheck === "unknown TLD") {
            $error = "Your email has an unknown top-level domain. Please check your email address.";
        } elseif ($domainCheck !== null) {
            // This is a suggestion, not an error that stops submission
            $email_warning = "Did you mean " . substr($email, 0, strpos($email, '@') + 1) . $domainCheck . "?";
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $error = "Email already registered. Please use a different email or login.";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $name, $email, $hashed_password);
                
                if ($stmt->execute()) {
                    $success = "Registration successful! Please login.";
                } else {
                    $error = "Registration failed. Please try again later.";
                }
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
    <title>User Registration - FreightX</title>
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
        
        .register-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            width: 90%;
            max-width: 500px;
            padding: 40px;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .register-header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .register-header p {
            color: #7f8c8d;
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 20px;
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
            transition: background 0.3s;
        }
        
        button:hover {
            background: #2980b9;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .warning-message {
            background: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #2c3e50;
        }
        
        .login-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .home-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-size: 16px;
        }
        
        .home-link:hover {
            text-decoration: underline;
        }
        
        .password-requirements {
            margin-top: 5px;
            font-size: 12px;
            color: #7f8c8d;
        }
        
        .suggestion-link {
            cursor: pointer;
            color: #3498db;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <a href="index.php" class="home-link"><i class="fas fa-home"></i> Back to Home</a>
    
    <div class="register-container">
        <div class="register-header">
            <h1>Create an Account</h1>
            <p>Join FreightX for seamless freight transportation services</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($email_warning)): ?>
            <div class="warning-message">
                <?php echo $email_warning; ?> 
                <span class="suggestion-link" id="use-suggestion">Click to use this email</span>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success-message">
                <?php echo $success; ?>
                <script>
                    setTimeout(function() {
                        window.location.href = 'user_login.php';
                    }, 3000);
                </script>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="registration-form">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <div class="password-requirements">Password must be at least 6 characters long</div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit">Register</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="user_login.php">Login here</a>
        </div>
    </div>

    <script>
        // Handle email suggestion click
        document.addEventListener('DOMContentLoaded', function() {
            const suggestionLink = document.getElementById('use-suggestion');
            if (suggestionLink) {
                suggestionLink.addEventListener('click', function() {
                    const warningText = document.querySelector('.warning-message').textContent;
                    const suggestedEmail = warningText.match(/Did you mean (.*?)\?/)[1];
                    document.getElementById('email').value = suggestedEmail;
                    document.querySelector('.warning-message').style.display = 'none';
                });
            }
            
            // Real-time email validation
            const emailInput = document.getElementById('email');
            if (emailInput) {
                emailInput.addEventListener('blur', function() {
                    const email = this.value.trim();
                    if (email && !validateEmail(email)) {
                        this.style.borderColor = '#dc3545';
                    } else {
                        this.style.borderColor = '#ddd';
                    }
                });
            }
            
            // Simple email validation function for client-side
                       function validateEmail(email) {
                const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(email);
            }
            
            // Password matching validation
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            
            if (confirmPasswordInput) {
                confirmPasswordInput.addEventListener('input', function() {
                    if (this.value !== passwordInput.value) {
                        this.style.borderColor = '#dc3545';
                    } else {
                        this.style.borderColor = '#28a745';
                    }
                });
            }
            
            // Form submission validation
            const form = document.getElementById('registration-form');
            form.addEventListener('submit', function(e) {
                const email = emailInput.value.trim();
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                let hasError = false;
                
                // Clear previous error styles
                emailInput.style.borderColor = '#ddd';
                passwordInput.style.borderColor = '#ddd';
                confirmPasswordInput.style.borderColor = '#ddd';
                
                // Validate email
                if (!validateEmail(email)) {
                    emailInput.style.borderColor = '#dc3545';
                    hasError = true;
                }
                
                // Validate password length
                if (password.length < 6) {
                    passwordInput.style.borderColor = '#dc3545';
                    hasError = true;
                }
                
                // Validate password match
                if (password !== confirmPassword) {
                    confirmPasswordInput.style.borderColor = '#dc3545';
                    hasError = true;
                }
                
                if (hasError) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
