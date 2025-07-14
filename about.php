<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - FreightX</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        
        body {
            background: linear-gradient(to right, #3498db, #2c3e50);
            color: #fff;
        }
        
        .navbar {
            background-color: rgba(0, 0, 0, 0.7);
            padding: 15px 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .nav-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #ffcc00;
            text-decoration: none;
        }
        
        .nav-links {
            display: flex;
            list-style: none;
        }
        
        .nav-links li {
            margin-left: 20px;
        }
        
        .nav-links a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s;
        }
        
        .nav-links a:hover {
            color: #ffcc00;
        }
        
        .auth-buttons a {
            margin-left: 15px;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        
        .login-btn {
            background-color: transparent;
            border: 2px solid #ffcc00;
            color: #ffcc00;
        }
        
        .login-btn:hover {
            background-color: #ffcc00;
            color: #000;
        }
        
        .register-btn {
            background-color: #ffcc00;
            color: #000;
        }
        
        .register-btn:hover {
            background-color: #e6b800;
        }
        
        .about-section {
            padding: 120px 0 60px;
        }
        
        .about-header {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .about-header h1 {
            font-size: 42px;
            margin-bottom: 15px;
            color: #ffcc00;
        }
        
        .about-header p {
            font-size: 18px;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .about-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 60px;
        }
        
        .about-text {
            flex: 1;
            min-width: 300px;
            padding: 0 20px;
        }
        
        .about-text h2 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #ffcc00;
        }
        
        .about-text p {
            margin-bottom: 20px;
            line-height: 1.6;
            font-size: 16px;
        }
        
        .about-image {
            flex: 1;
            min-width: 300px;
            padding: 0 20px;
        }
        
        .about-image img {
            width: 100%;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }
        
        .mission-vision {
            background-color: rgba(0, 0, 0, 0.5);
            padding: 60px 0;
            margin-bottom: 60px;
        }
        
        .mission-vision-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        
        .mission, .vision {
            flex: 1;
            min-width: 300px;
            padding: 30px;
            margin: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .mission h2, .vision h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #ffcc00;
            text-align: center;
        }
        
        .mission p, .vision p {
            line-height: 1.6;
            font-size: 16px;
        }
        
        .team-section {
            padding: 60px 0;
        }
        
        .team-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .team-header h2 {
            font-size: 32px;
            color: #ffcc00;
            margin-bottom: 15px;
        }
        
        .team-header p {
            font-size: 18px;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .team-members {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .team-member {
            width: 250px;
            margin: 20px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }
        
        .team-member:hover {
            transform: translateY(-10px);
        }
        
        .member-image {
            height: 250px;
            overflow: hidden;
        }
        
        .member-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .member-info {
            padding: 20px;
            text-align: center;
        }
        
        .member-info h3 {
            font-size: 20px;
            margin-bottom: 5px;
            color: #ffcc00;
        }
        
        .member-info p {
            font-size: 14px;
            color: #ddd;
            margin-bottom: 15px;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
        }
        
        .social-links a {
            color: #fff;
            margin: 0 8px;
            font-size: 18px;
            transition: color 0.3s;
        }
        
        .social-links a:hover {
            color: #ffcc00;
        }
        
        footer {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 40px 0 20px;
            text-align: center;
        }
        
        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        
        .footer-section {
            flex: 1;
            min-width: 250px;
            margin-bottom: 20px;
            padding: 0 20px;
        }
        
        .footer-section h3 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #ffcc00;
        }
        
        .footer-section p, .footer-section a {
            color: #ddd;
            margin-bottom: 10px;
            display: block;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .footer-section a:hover {
            color: #ffcc00;
        }
        
        .footer-bottom {
            border-top: 1px solid #444;
            padding-top: 20px;
            color: #ddd;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            .nav-links {
                display: none;
            }
            
            .about-content, .mission-vision-content {
                flex-direction: column;
            }
            
            .about-image {
                margin-top: 30px;
            }
            
            .mission, .vision {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-content">
                <a href="index.php" class="logo"><i class="fas fa-train"></i> FreightX</a>
                
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="services.php">Services</a></li>
                    <li><a href="about.php" class="active">About Us</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="track.php">Track Shipment</a></li>
                </ul>
                
                <div class="auth-buttons">
                    <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
                        <a href="user_dashboard.php" class="login-btn">Dashboard</a>
                        <a href="user_logout.php" class="register-btn">Logout</a>
                    <?php else: ?>
                        <a href="user_login.php" class="login-btn">Login</a>
                        <a href="user_register.php" class="register-btn">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- About Section -->
    <section class="about-section">
        <div class="container">
            <div class="about-header">
                <h1>About FreightX</h1>
                <p>Your trusted partner in freight transportation and logistics solutions across India.</p>
            </div>
            
            <div class="about-content">
                <div class="about-text">
                    <h2>Our Story</h2>
                    <p>Founded in 2025
                          freightX has grown from a small regional carrier to one of India's leading freight transportation companies. Our journey began with a simple mission: to provide reliable, efficient, and cost-effective freight solutions to businesses of all sizes.</p>
                    <p>Over the years, we have expanded our network to cover all major cities and industrial hubs across India. Our commitment to innovation, customer service, and operational excellence has earned us the trust of thousands of clients ranging from small businesses to large corporations.</p>
                    <p>Today, FreightX operates a modern fleet of vehicles and utilizes cutting-edge technology to ensure timely delivery and complete visibility of shipments. We continue to invest in our infrastructure and people to deliver the highest quality of service to our customers.</p>
                </div>
                
                <div class="about-image">
                    <img src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Freight Transportation">
                </div>
            </div>
        </div>
    </section>
    
    <!-- Mission & Vision Section -->
    <section class="mission-vision">
        <div class="container">
            <div class="mission-vision-content">
                <div class="mission">
                    <h2><i class="fas fa-bullseye"></i> Our Mission</h2>
                    <p>To provide innovative and sustainable freight transportation solutions that exceed customer expectations while maintaining the highest standards of safety, reliability, and environmental responsibility.</p>
                    <p>We strive to be the most trusted partner for businesses by delivering their goods safely and on time, every time. Our mission is to simplify logistics for our customers, allowing them to focus on their core business while we handle their transportation needs with utmost care and efficiency.</p>
                </div>
                
                <div class="vision">
                    <h2><i class="fas fa-eye"></i> Our Vision</h2>
                    <p>To be India's premier freight transportation company, recognized for our commitment to excellence, innovation, and customer satisfaction.</p>
                    <p>We envision a future where logistics is seamless, sustainable, and accessible to all businesses regardless of their size. By leveraging technology and our extensive network, we aim to revolutionize the freight industry in India and set new standards for service quality and operational efficiency.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Team Section -->
    <section class="team-section">
        <div class="container">
            <div class="team-header">
                <h2>Our Leadership Team</h2>
                <p>Meet the dedicated professionals who drive our success and ensure we deliver on our promises.</p>
            </div>
            
            <div class="team-members">
                <div class="team-member">
                    <div class="member-image">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Team Member">
                    </div>
                    <div class="member-info">
                        <h3>Rajesh Kumar</h3>
                        <p>Chief Executive Officer</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="team-member">
                    <div class="member-image">
                        <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Team Member">
                    </div>
                    <div class="member-info">
                        <h3>Priya Sharma</h3>
                        <p>Chief Operations Officer</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="team-member">
                    <div class="member-image">
                        <img src="https://randomuser.me/api/portraits/men/65.jpg" alt="Team Member">
                    </div>
                    <div class="member-info">
                        <h3>Vikram Singh</h3>
                        <p>Chief Technology Officer</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="team-member">
                    <div class="member-image">
                        <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Team Member">
                    </div>
                    <div class="member-info">
                        <h3>Ananya Patel</h3>
                        <p>Chief Financial Officer</p>
                        <div class="social-links">
                            <a href="#"><i class="fab fa-linkedin"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>FreightX</h3>
                    <p>Your trusted partner in freight transportation and logistics solutions across India.</p>
                    <p><i class="fas fa-phone"></i> +91 1234567890</p>
                    <p><i class="fas fa-envelope"></i> info@freightx.com</p>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <a href="index.php">Home</a>
                    <a href="services.php">Services</a>
                    <a href="about.php">About Us</a>
                    <a href="contact.php">Contact</a>
                    <a href="track.php">Track Shipment</a>
                </div>
                
                <div class="footer-section">
                    <h3>Services</h3>
                    <a href="services.php#rail-freight">Rail Freight</a>
                    <a href="services.php#road-freight">Road Freight</a>
                    <a href="services.php#warehousing">Warehousing</a>
                    <a href="services.php#express-delivery">Express Delivery</a>
                </div>
                
                <div class="footer-section">
                    <h3>Connect With Us</h3>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2023 FreightX. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
