<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Services - FreightX</title>
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
        
        .nav-links a:hover, .nav-links a.active {
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
        
        .services-header {
            padding: 150px 0 80px;
            text-align: center;
            background: rgba(0, 0, 0, 0.3);
        }
        
        .services-header h1 {
            font-size: 42px;
            margin-bottom: 20px;
            color: #ffcc00;
        }
        
        .services-header p {
            font-size: 18px;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }
        
        .services-grid {
            padding: 60px 0;
        }
        
        .service-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 40px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }
        
        .service-card:hover {
            transform: translateY(-10px);
        }
        
        .service-image {
            height: 250px;
            overflow: hidden;
        }
        
        .service-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .service-card:hover .service-image img {
            transform: scale(1.1);
        }
        
        .service-content {
            padding: 30px;
        }
        
        .service-content h2 {
            font-size: 28px;
            margin-bottom: 15px;
            color: #ffcc00;
        }
        
        .service-content p {
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .service-features {
            margin-bottom: 20px;
        }
        
        .feature {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .feature i {
            color: #ffcc00;
            margin-right: 10px;
            font-size: 18px;
        }
        
        .learn-more {
            display: inline-block;
            padding: 10px 20px;
            background: #ffcc00;
            color: #000;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: background 0.3s;
        }
        
        .learn-more:hover {
            background: #e6b800;
        }
        
        .why-choose-us {
            background: rgba(0, 0, 0, 0.5);
            padding: 80px 0;
            text-align: center;
        }
        
        .why-choose-us h2 {
            font-size: 36px;
            margin-bottom: 20px;
            color: #ffcc00;
        }
        
        .why-choose-us p {
            max-width: 800px;
            margin: 0 auto 40px;
            line-height: 1.6;
        }
        
        .benefits {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
        }
        
        .benefit {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 30px;
            width: 250px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .benefit i {
            font-size: 40px;
            color: #ffcc00;
            margin-bottom: 20px;
        }
        
        .benefit h3 {
            font-size: 20px;
            margin-bottom: 15px;
            color: #ffcc00;
        }
        
        .benefit p {
            font-size: 14px;
            line-height: 1.5;
        }
        
        .cta-section {
            padding: 80px 0;
            text-align: center;
        }
        
        .cta-section h2 {
            font-size: 36px;
            margin-bottom: 20px;
            color: #ffcc00;
        }
        
        .cta-section p {
            max-width: 700px;
            margin: 0 auto 30px;
            line-height: 1.6;
            font-size: 18px;
        }
        
        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .cta-btn {
            padding: 12px 25px;
            border-radius: 5px;
            font-weight: bold;
            text-decoration: none;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        .primary-btn {
            background: #ffcc00;
            color: #000;
        }
        
        .primary-btn:hover {
            background: #e6b800;
        }
        
        .secondary-btn {
            background: transparent;
            border: 2px solid #ffcc00;
            color: #ffcc00;
        }
        
        .secondary-btn:hover {
            background: #ffcc00;
            color: #000;
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
            
            .benefits {
                flex-direction: column;
                align-items: center;
            }
            
            .benefit {
                width: 90%;
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
                    <li><a href="services.php" class="active">Services</a></li>
                    <li><a href="about.php">About Us</a></li>
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
    
    <!-- Services Header -->
    <section class="services-header">
        <div class="container">
            <h1>Our Services</h1>
            <p>FreightX offers comprehensive freight transportation and logistics solutions tailored to meet your specific needs. Explore our range of services designed to ensure your goods reach their destination safely and on time.</p>
        </div>
    </section>
    
    <!-- Services Grid -->
    <section class="services-grid">
        <div class="container">
            <!-- Rail Freight Service -->
            <div class="service-card" id="rail-freight">
                <div class="service-image">
                    <img src="https://images.unsplash.com/photo-1474487548417-781cb71495f3?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1484&q=80" alt="Rail Freight">
                </div>
                <div class="service-content">
                    <h2>Rail Freight</h2>
                    <p>Our rail freight service offers a cost-effective and environmentally friendly solution for transporting large volumes of goods across long distances. With access to an extensive rail network covering all major cities and industrial hubs in India, we ensure your cargo reaches its destination efficiently.</p>
                    
                    <div class="service-features">
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Nationwide coverage with access to all major rail routes</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Suitable for bulk commodities like coal, grains, cement, and steel</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Reduced carbon footprint compared to road transportation</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Real-time tracking and status updates</span>
                        </div>
                    </div>
                    
                    <a href="book_commodity.php" class="learn-more">Book Now</a>
                </div>
            </div>
            
            <!-- Road Freight Service -->
            <div class="service-card" id="road-freight">
                <div class="service-image">
                    <img src="https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Road Freight">
                </div>
                <div class="service-content">
                    <h2>Road Freight</h2>
                    <p>Our road freight service provides flexible and reliable transportation solutions for your cargo. With a modern fleet of vehicles and experienced drivers, we offer door-to-door delivery services that cater to your specific requirements and timelines.</p>
                    
                    <div class="service-features">
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Door-to-door delivery service</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Flexible scheduling and route options</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Various vehicle types to suit different cargo needs</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>GPS tracking for real-time location updates</span>
                        </div>
                    </div>
                    
                    <a href="book_commodity.php" class="learn-more">Book Now</a>
                </div>
            </div>
            
            <!-- Warehousing Service -->
            <div class="service-card" id="warehousing">
                <div class="service-image">
                    <img src="https://images.unsplash.com/photo-1553413077-190dd305871c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Warehousing">
                </div>
                <div class="service-content">
                    <h2>Warehousing</h2>
                    <p>Our warehousing services offer secure and efficient storage solutions for your goods. With strategically located facilities across India, we provide short-term and long-term storage options along with value-added services to streamline your supply chain.</p>
                    
                    <div class="service-features">
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Modern facilities with advanced security systems</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Inventory management and order fulfillment services</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Temperature-controlled storage for sensitive goods</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Seamless integration with transportation services</span>
                        </div>
                    </div>
                    
                    <a href="contact.php" class="learn-more">Inquire Now</a>
                </div>
            </div>
            
            <!-- Express Delivery Service -->
            <div class="service-card" id="express-delivery">
                <div class="service-image">
                    <img src="https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="Express Delivery">
                </div>
                <div class="service-content">
                    <h2>Express Delivery</h2>
                    <p>When time is of the essence, our express delivery service ensures your shipments reach their destination quickly and reliably. Ideal for urgent deliveries, our express service operates on priority schedules to meet your tight deadlines.</p>
                    
                    <div class="service-features">
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Guaranteed delivery within specified timeframes</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Priority handling and expedited transportation</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Real-time tracking and status notifications</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Available for both small packages and larger shipments</span>
                        </div>
                    </div>
                    
                    <a href="contact.php" class="learn-more">Inquire Now</a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Why Choose Us Section -->
    <section class="why-choose-us">
        <div class="container">
            <h2>Why Choose FreightX?</h2>
            <p>With years of experience in the freight industry, we have built a reputation for reliability, efficiency, and customer satisfaction. Here's why our clients trust us with their valuable cargo:</p>
            
            <div class="benefits">
                <div class="benefit">
                    <i class="fas fa-network-wired"></i>
                    <h3>Extensive Network</h3>
                    <p>Our nationwide network ensures we can deliver your goods to any destination in India efficiently and on time.</p>
                </div>
                
                <div class="benefit">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Safety & Security</h3>
                    <p>We prioritize the safety of your cargo with robust security measures and careful handling throughout transit.</p>
                </div>
                
                <div class="benefit">
                    <i class="fas fa-headset"></i>
                    <h3>24/7 Support</h3>
                    <p>Our dedicated customer support team is available round the clock to address your queries and concerns.</p>
                </div>
                
                <div class="benefit">
                    <i class="fas fa-chart-line"></i>
                    <h3>Real-time Tracking</h3>
                    <p>Track your shipments in real-time through our advanced tracking system for complete visibility.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Call to Action Section -->
    <section class="cta-section">
        <div class="container">
            <h2>Ready to Ship with FreightX?</h2>
            <p>Experience hassle-free freight transportation with our comprehensive logistics solutions. Get started today and let us handle your shipping needs with care and efficiency.</p>
            
            <div class="cta-buttons">
                <a href="book_commodity.php" class="cta-btn primary-btn">Book a Shipment</a>
                <a href="contact.php" class="cta-btn secondary-btn">Contact Our Team</a>
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
                    <a href="#rail-freight">Rail Freight</a>
                    <a href="#road-freight">Road Freight</a>
                    <a href="#warehousing">Warehousing</a>
                    <a href="#express-delivery">Express Delivery</a>
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
