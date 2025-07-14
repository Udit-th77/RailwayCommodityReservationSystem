<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FreightX - Railway Commodity Reservation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* General Page Styling */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(to right, #ff9966, #ff5e62, #1e90ff, #32cd32);
            color: white;
            text-align: center;
        }

        /* Navigation Bar */
        .navbar {
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 30px;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar .logo {
            font-size: 24px;
            font-weight: bold;
            color: white;
            display: flex;
            align-items: center;
        }
        .navbar .logo i {
            margin-right: 10px;
            color: #ffcc00;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 14px 20px;
            margin: 0 12px;
            font-size: 18px;
            font-weight: bold;
            transition: 0.3s;
            border-radius: 8px;
        }
        .navbar a:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
        }

        /* Welcome Section */
        .hero {
            padding: 80px 20px;
            background: rgba(0, 0, 0, 0.4);
            margin: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        .hero h1 {
            font-size: 45px;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
        }
        .hero p {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 30px;
        }
        
        /* Features Section */
        .features {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 30px;
            margin: 50px 20px;
        }
        .feature-card {
            background: rgba(0, 0, 0, 0.7);
            width: 300px;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            transition: transform 0.3s;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .feature-card:hover {
            transform: translateY(-10px);
        }
        .feature-card i {
            font-size: 40px;
            color: #ffcc00;
            margin-bottom: 15px;
        }
        .feature-card h3 {
            color: #00ffcc;
            margin-bottom: 15px;
        }

        /* Login & Register Box */
        .box-container {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
            margin: 50px 0;
        }
        .box {
            width: 280px;
            background: rgba(0, 0, 0, 0.7);
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 4px 4px 20px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s;
        }
        .box:hover {
            transform: scale(1.05);
        }
        .box h2 {
            margin-bottom: 20px;
            color: #ffcc00;
            font-size: 24px;
        }
        .box p {
            margin-bottom: 20px;
            color: #e0e0e0;
        }
        .box a {
            display: block;
            text-decoration: none;
            color: white;
            padding: 14px;
            margin-top: 15px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 8px;
            transition: 0.3s;
        }
        .box .login {
            background: #1e90ff;
        }
        .box .register {
            background: #ff4500;
        }
        .box a:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        /* CTA Section */
        .cta {
            background: rgba(0, 0, 0, 0.7);
            padding: 40px;
            margin: 50px 40px;
            border-radius: 15px;
        }
        .cta h2 {
            font-size: 32px;
            margin-bottom: 20px;
            color: #00ffcc;
        }
        .cta-button {
            display: inline-block;
            background: #ff4500;
            color: white;
            padding: 15px 30px;
            font-size: 20px;
            font-weight: bold;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 20px;
            transition: 0.3s;
        }
        .cta-button:hover {
            background: #ff6a33;
            transform: scale(1.05);
        }

        /* Footer */
        .footer {
            background: rgba(0, 0, 0, 0.9);
            padding: 40px 20px;
            text-align: center;
            margin-top: 50px;
        }
        .footer-content {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            max-width: 1200px;
            margin: 0 auto;
        }
        .footer-section {
            flex: 1;
            min-width: 250px;
            margin-bottom: 20px;
        }
        .footer-section h3 {
            color: #ffcc00;
            margin-bottom: 15px;
        }
        .footer-section p, .footer-section a {
            color: #e0e0e0;
            margin: 8px 0;
            display: block;
            text-decoration: none;
        }
        .footer-section a:hover {
            color: #00ffcc;
        }
        .social-icons {
            margin-top: 20px;
        }
        .social-icons a {
            display: inline-block;
            margin: 0 10px;
            color: white;
            font-size: 24px;
            transition: 0.3s;
        }
        .social-icons a:hover {
            color: #ffcc00;
            transform: scale(1.2);
        }
        .footer-bottom {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #444;
        }
        .admin-link {
            margin-top: 15px;
        }
        .admin-link a {
            color: #ffcc00;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }
        .admin-link a:hover {
            color: #00ffcc;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <div class="navbar">
        <div class="logo">
            <i class="fas fa-train"></i> FreightX
        </div>
        <div>
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="services.php"><i class="fas fa-box"></i> Services</a>
            <a href="about.php"><i class="fas fa-info-circle"></i> About</a>
            <a href="customer_support.php"><i class="fas fa-headset"></i> Support</a>
        </div>
    </div>

    <!-- Hero Section -->
    <div class="hero">
        <h1>Welcome to FreightX</h1>
        <p>Your trusted Railway Commodity Reservation System</p>
        <p>Efficient, Reliable, and Secure Transportation Solutions</p>
    </div>

    <!-- Features Section -->
    <div class="features">
        <div class="feature-card">
            <i class="fas fa-shipping-fast"></i>
            <h3>Fast Delivery</h3>
            <p>Our railway network ensures your commodities reach their destination on time, every time.</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-shield-alt"></i>
            <h3>Secure Transport</h3>
            <p>Advanced tracking and security measures to keep your goods safe throughout transit.</p>
        </div>
        <div class="feature-card">
            <i class="fas fa-chart-line"></i>
            <h3>Real-time Tracking</h3>
            <p>Monitor your shipments in real-time with our advanced tracking system.</p>
        </div>
    </div>

    <!-- Login & Register Boxes -->
    <div class="box-container">
        <div class="box">
            <h2><i class="fas fa-user"></i> User Portal</h2>
            <p>Book shipments, track deliveries, and manage your account.</p>
            <a href="user_login.php" class="login">User Login</a>
            <a href="user_register.php" class="register">User Register</a>
        </div>
        
        <div class="box">
            <h2><i class="fas fa-user-tie"></i> Admin Portal</h2>
            <p>System management, approvals, and administrative controls.</p>
            <a href="admin_login.php" class="login">Admin Login</a>
        </div>
        
        <div class="box">
            <h2><i class="fas fa-user-hard-hat"></i> Employee Portal</h2>
            <p>For train drivers, station staff, and other railway employees.</p>
            <a href="employee_login.php" class="login">Employee Login</a>
            <a href="employee_register.php" class="register">Join Our Team</a>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="cta">
        <h2>Ready to Ship Your Commodities?</h2>
        <p>Register now and experience the most efficient railway commodity reservation system.</p>
        <a href="user_register.php" class="cta-button">Get Started</a>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About FreightX</h3>
                <p>Leading railway commodity reservation system providing efficient and secure transportation solutions.</p>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <a href="services.php">Our Services</a>
                <a href="pricing.php">Pricing</a>
                <a href="faq.php">FAQs</a>
                <a href="terms.php">Terms & Conditions</a>
            </div>
            
            <div class="footer-section">
                <h3>Contact Us</h3>
                <p><i class="fas fa-map-marker-alt"></i> 123 Railway Avenue, Transport City</p>
                <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                <p><i class="fas fa-envelope"></i> info@freightx.com</p>
            </div>
        </div>
        
        <div class="social-icons">
            <a href="#"><i class="fab fa-facebook"></i></a>
            <a href="#"><i class="fab fa-twitter"></i></a>
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-linkedin"></i></a>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 FreightX - All Rights Reserved</p>
            <div class="admin-link">
                <a href="view_support_queries.php"><i class="fas fa-lock"></i> Admin: View Support Messages</a>
            </div>
        </div>
    </div>

</body>
</html>
