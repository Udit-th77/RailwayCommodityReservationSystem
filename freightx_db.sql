-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2025 at 11:54 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `freightx_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `created_at`) VALUES
(1, 'admin', '$2y$10$1G.IJKX/4go2Zmsxk1XIXuk8A3AUfUM88xBC86Fr7GyCOL4i.Xcji', 'admin@gmail.com', '2025-05-07 16:09:08');

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `assigned_date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `commodity_type` varchar(100) NOT NULL,
  `weight` float NOT NULL,
  `source` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `booking_date` date NOT NULL,
  `delivery_method` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','in_transit','delivered','cancelled') DEFAULT 'pending',
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `payment_id` varchar(100) DEFAULT NULL,
  `order_id` varchar(100) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `ticket_number` varchar(20) DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `commodity_type`, `weight`, `source`, `destination`, `booking_date`, `delivery_method`, `notes`, `delivery_date`, `price`, `status`, `payment_status`, `payment_id`, `order_id`, `payment_method`, `ticket_number`, `cancellation_reason`, `cancelled_at`, `created_at`) VALUES
(1, 14, 'Grains', 51, 'Howrah Junction', 'Bangalore City', '2025-05-29', NULL, NULL, NULL, 0.00, 'pending', 'pending', 'COD-FX1746734187826', 'FX1746734187826', 'cod', 'FX202505083465', NULL, NULL, '2025-05-08 19:56:39'),
(3, 14, 'Grains', 50, 'Bangalore City', 'Pune Junction', '2025-05-16', 'truck', '0', NULL, 6000.00, 'pending', 'pending', 'COD-FX1746815730611', 'FX1746815730611', 'cod', 'FX202505097136', NULL, NULL, '2025-05-09 18:36:53');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `position` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `name`, `email`, `password`, `position`, `phone`, `status`, `created_at`) VALUES
(2, 'ishu tiwari', 'ishutiwari@gmail.com', '$2y$10$qPHrhwPwkSnmSPHlRHEqOutu410fFTqpVrPIRU57r4x.OLGu/AD7i', 'Station Manager', '9012437158', 'approved', '2025-05-08 18:02:31'),
(3, 'ashish', 'ashish@gmail.com', '$2y$10$oD2ApK1bp2E70RP5LqX36.Oo47ar3vhXT55yc8pciwiK05T1EFFN.', 'Logistics Coordinator', NULL, 'approved', '2025-05-08 18:29:06'),
(4, 'ishu', 'ishut@gmail.com', '$2y$10$4yqLYFizqkuYm6.TOQJkz.qAOuCOIdmYqinK9Z.9syyToyqYmHqn2', 'Delivery Partner', '9012437158', 'approved', '2025-05-09 21:42:55');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `card_name` varchar(100) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','completed','failed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_gateways`
--

CREATE TABLE `payment_gateways` (
  `id` int(11) NOT NULL,
  `gateway_name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `api_key` varchar(255) DEFAULT NULL,
  `api_secret` varchar(255) DEFAULT NULL,
  `merchant_id` varchar(100) DEFAULT NULL,
  `sandbox_mode` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_gateways`
--

INSERT INTO `payment_gateways` (`id`, `gateway_name`, `display_name`, `is_active`, `api_key`, `api_secret`, `merchant_id`, `sandbox_mode`, `created_at`, `updated_at`) VALUES
(1, 'razorpay', 'Razorpay', 1, NULL, NULL, NULL, 1, '2025-05-08 19:42:09', '2025-05-08 19:42:09'),
(2, 'paytm', 'Paytm', 1, NULL, NULL, NULL, 1, '2025-05-08 19:42:09', '2025-05-08 19:42:09'),
(3, 'paypal', 'PayPal', 0, '', '', '0', 1, '2025-05-08 19:42:09', '2025-05-09 19:39:55'),
(4, 'stripe', 'Stripe', 0, NULL, NULL, NULL, 1, '2025-05-08 19:42:09', '2025-05-08 19:42:09');

-- --------------------------------------------------------

--
-- Table structure for table `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'INR',
  `transaction_id` varchar(100) DEFAULT NULL,
  `order_id` varchar(100) DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `gateway_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_transactions`
--

INSERT INTO `payment_transactions` (`id`, `booking_id`, `user_id`, `payment_method`, `amount`, `currency`, `transaction_id`, `order_id`, `payment_status`, `gateway_response`, `created_at`, `updated_at`) VALUES
(1, 1, 14, 'cod', 4080.00, 'INR', 'COD-FX1746734187826', 'FX1746734187826', 'pending', '{\"name\":\"ishu tiwari\",\"phone\":\"0000000000000\"}', '2025-05-08 19:56:40', '2025-05-08 19:56:40'),
(2, 2, 14, 'cod', 8000.00, 'INR', 'COD-FX1746814679701', 'FX1746814679701', 'pending', '{\"name\":\"ishu \",\"phone\":\"901222212\"}', '2025-05-09 18:19:33', '2025-05-09 18:19:33'),
(3, 3, 14, 'cod', 6000.00, 'INR', 'COD-FX1746815730611', 'FX1746815730611', 'pending', '{\"name\":\"ishu tiwari\",\"phone\":\"852852852\"}', '2025-05-09 18:36:53', '2025-05-09 18:36:53');

-- --------------------------------------------------------

--
-- Table structure for table `refunds`
--

CREATE TABLE `refunds` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','rejected') NOT NULL DEFAULT 'pending',
  `refund_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `gateway_response` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_queries`
--

CREATE TABLE `support_queries` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `status` enum('pending','in_progress','resolved') DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `support_queries`
--

INSERT INTO `support_queries` (`id`, `name`, `email`, `message`, `status`, `submitted_at`) VALUES
(1, 'Ashish', 'ashish@gmail.com', 'jbMBCKF', 'pending', '2025-05-06 19:47:50'),
(2, 'Saurabh', 'Saurabh@gmail.com', 'I am facing issue in booking the ticket.', 'pending', '2025-05-06 20:07:27'),
(3, 'isdjf', 'ishu@gmail.com', 'this is message', 'pending', '2025-05-09 19:41:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `created_at`) VALUES
(1, 'Ashish Gupta', 'akg@gmail.com', '$2y$10$/3codEcV2Yf8Zz1vQm0CSORZjsxvR3EIic9MSd0.2g/kkNn1HAAkG', NULL, NULL, '2025-03-06 05:49:34'),
(3, 'Ashish', 'akg1@gmail.com', '$2y$10$oXwURgVgUpN2ZagCjXz8Xu/79no4A8gruXFVKBGYS/77sXFSHQ5hS', NULL, NULL, '2025-03-29 05:59:45'),
(4, 'Somya Tiwari', 'somya@gmail.com', '$2y$10$jneYLTXmeOqJ/8ueUUIDRuLmkTg8dvXvgQ6REkG22FI9BF/bTNcIy', NULL, NULL, '2025-03-29 09:29:53'),
(5, 'udit kumar', 'udit@gmail.com', '$2y$10$zh8MgOIcU2O1XEIgjdgM4.sXC6ZrdH7DPNwDXOOlZwBlfkwv2S7ZK', NULL, NULL, '2025-03-29 14:49:04'),
(6, 'Arvind', 'Arvind@gmail.com', '$2y$10$oAZYAUgD.JRYLS8xF.2woe45sdOiVpcqzBat7GWIRb0PJsa0srzAO', NULL, NULL, '2025-04-01 05:31:10'),
(7, 'Gautam Tiwari', 'gautam@gmail.com', '$2y$10$AvmpLQb8pGozytBxjd/qoOEq8VqsRAC7p4siDW6XdUB6AVce5V8we', NULL, NULL, '2025-04-01 05:37:35'),
(8, 'ashish', 'ashish12@gmail.com', '$2y$10$TjhcEDZfcX4n/qXD0Scqm.nu9dWAbHtYk4/U8pXWNzS8oiEUo2AzO', NULL, NULL, '2025-04-01 05:54:45'),
(9, 'ashish', 'ashish12@gmaill.com', '$2y$10$6t0x5wYRdIvwsXOco8Tw1.651RBCy1pcHXcVuXAT0wtFVayGOC/w.', NULL, NULL, '2025-04-01 05:57:11'),
(10, 'AK', 'sk@gmail.com', '$2y$10$fhXzROWlx649CAkbM5BLDOg1FWaOmiKpEGDWrMAp0t9C8chpebU1y', NULL, NULL, '2025-04-18 16:22:31'),
(11, 'Akhilesh sir', 'akhileshsir@gmail.com', '$2y$10$8.rqaHICFNeIM7xwI0c7/O6EJArCPHLOHDIZ8dW7WbtQr2x3BkBrK', NULL, NULL, '2025-04-26 09:19:53'),
(12, 'Ashish', 'ashish@gmail.com', '$2y$10$VMqwWOOG96RTXeLu6V2uYutpNu1vUEdhaeoehpRQbJ42CYLGmIwme', NULL, NULL, '2025-05-06 18:07:12'),
(13, 'ishu tiwari', 'ishutiwari9980@gmail.com', '$2y$10$kLJT29W1fnp2HOVBEcQxmewJkzK/PauwwajOcnXejrSX4aFV4fPTW', NULL, NULL, '2025-05-07 11:49:05'),
(14, 'ishu tiwari', 'ishuuser@gmail.com', '$2y$10$TPyfDoAG6D0TWgBcF2SDau10DRBcRUUSFsgiC9u3zPKgUAq.KwHEO', NULL, NULL, '2025-05-08 18:58:07'),
(15, 'isss', 'ishutiwari9999@gmail.com', '$2y$10$BiLwu3wHZmJpbDKbqDPsy.bTwLHWkIa83RqDHnBG9Kekc0HknT6k6', NULL, NULL, '2025-05-09 19:18:39'),
(16, 'asasas', 'sssd@gmdail.com', '$2y$10$4tiiJFE4LDJFgdaSOnlQi.5P6pj9CJmvziFUpT8j9pjjhLNGCVp0S', NULL, NULL, '2025-05-09 19:54:28'),
(17, 'ishu', 'ishuu@gmaill.com', '$2y$10$GbQcMbMUaUHz7Fg2/QBkP.mxyK35xUWK0kQTSaPDgPaRmXICQnbiC', NULL, NULL, '2025-05-09 20:03:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_number` (`ticket_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gateway_name` (`gateway_name`);

--
-- Indexes for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `refunds`
--
ALTER TABLE `refunds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `support_queries`
--
ALTER TABLE `support_queries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_queries`
--
ALTER TABLE `support_queries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `fk_booking_assignment` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_employee_assignment` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `refunds`
--
ALTER TABLE `refunds`
  ADD CONSTRAINT `refunds_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `refunds_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
