-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 22, 2025 at 03:02 PM
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
-- Database: `lovine`
--

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `customer_id`, `created_datetime`, `updated_datetime`) VALUES
('CA0001', 'CU0001', '2025-12-22 07:58:27', '2025-12-22 07:58:27');

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `cart_id`, `product_variant_id`, `quantity`, `cart_status`) VALUES
('CI0006', 'CA0001', 'PV0013', 2, 0),
('CI0007', 'CA0001', 'PV0003', 3, 0);

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `order_id`, `payment_method`, `payment_status`, `amount`, `created_datetime`, `updated_datetime`) VALUES
('PM0001', 'O0001', 'Credit Card', 'Cancelled', 212.336, '2025-12-20 16:16:29', '2025-12-20 16:16:29'),
('PM0002', 'O0002', 'Credit Card', 'Cancelled', 143.224, '2025-12-20 16:16:29', '2025-12-20 16:16:29'),
('PM0003', 'O0003', 'Credit Card', 'Cancelled', 847.7, '2025-12-20 16:16:29', '2025-12-20 16:16:29'),
('PM0004', 'O0004', 'Credit Card', 'Cancelled', 264.7, '2025-12-20 16:16:29', '2025-12-20 16:16:29'),
('PM0005', 'O0005', 'Credit Card', 'Cancelled', 45.81, '2025-12-20 16:16:29', '2025-12-20 16:16:29'),
('PM0006', 'O0006', 'Credit Card', 'Cancelled', 86.62, '2025-12-20 16:16:29', '2025-12-20 16:16:29'),
('PM0007', 'O0007', 'Credit Card', 'Cancelled', 215.94, '2025-12-20 16:16:29', '2025-12-20 16:16:29'),
('PM0008', 'O0008', 'Credit Card', 'Cancelled', 215.94, '2025-12-20 16:16:29', '2025-12-20 16:16:29'),
('PM0009', 'O0009', 'Credit Card', 'Cancelled', 74.112, '2025-12-20 16:16:29', '2025-12-20 16:16:29'),
('PM0010', 'O0010', 'Credit Card', 'Paid', 506.16, '2025-12-17 10:12:30', '2025-12-17 10:12:30'),
('PM0011', 'O0011', 'TNG', 'Cancelled', 92.17, '2025-12-20 16:16:29', '2025-12-20 16:16:29'),
('PM0012', 'O0012', 'Credit Card', 'Paid', 193.68, '2025-12-17 10:48:08', '2025-12-17 10:48:08'),
('PM0013', 'O0013', 'Credit Card', 'Paid', 205.94, '2025-12-17 10:51:27', '2025-12-17 10:51:27'),
('PM0014', 'O0014', 'Credit Card', 'Paid', 44.86, '2025-12-18 04:59:10', '2025-12-18 04:59:10'),
('PM0015', 'O0015', 'Credit Card', 'Cancelled', 68.812, '2025-12-20 16:16:29', '2025-12-20 16:16:29'),
('PM0016', 'O0016', 'Credit Card', 'Paid', 1987.2, '2025-12-18 16:40:15', '2025-12-18 16:40:15'),
('PM0017', 'O0017', 'TNG', 'Paid', 36.8, '2025-12-18 16:50:09', '2025-12-18 16:50:09'),
('PM0018', 'O0018', 'TNG', 'Paid', 36.8, '2025-12-19 14:20:11', '2025-12-19 14:20:11'),
('PM0019', 'O0019', 'Credit Card', 'Cancelled', 26.8, '2025-12-20 16:16:29', '2025-12-20 16:16:29'),
('PM0020', 'O0020', 'Credit Card', 'Cancelled', 58, '2025-12-20 16:16:29', '2025-12-20 16:16:29'),
('PM0021', 'O0021', 'Credit Card', 'Paid', 101, '2025-12-19 17:38:01', '2025-12-19 17:38:01'),
('PM0022', 'O0022', 'Credit Card', 'Paid', 79.2, '2025-12-19 17:39:46', '2025-12-19 17:39:46'),
('PM0023', 'O0023', 'TNG', 'Paid', 79.2, '2025-12-19 17:40:37', '2025-12-19 17:40:37'),
('PM0024', 'O0024', 'TNG', 'Cancelled', 173.54, '2025-12-20 16:16:29', '2025-12-20 16:16:29'),
('PM0025', 'O0025', 'Credit Card', 'Paid', 173.54, '2025-12-19 18:07:58', '2025-12-19 18:07:58'),
('PM0026', 'O0026', 'TNG', 'Paid', 1049.7, '2025-12-19 19:03:55', '2025-12-19 19:03:55'),
('PM0027', 'O0027', 'Credit Card', 'Paid', 99.34, '2025-12-19 19:05:18', '2025-12-19 19:05:18'),
('PM0028', 'O0028', 'Credit Card', 'Paid', 99.34, '2025-12-19 19:22:14', '2025-12-19 19:22:14'),
('PM0029', 'O0029', 'Credit Card', 'Paid', 106.3, '2025-12-20 04:47:24', '2025-12-20 04:47:24'),
('PM0030', 'O0030', 'Credit Card', 'Paid', 79.2, '2025-12-20 07:18:29', '2025-12-20 07:18:29'),
('PM0031', 'O0031', 'Credit Card', 'Paid', 627.82, '2025-12-20 15:28:23', '2025-12-20 15:28:23'),
('PM0032', 'O0032', 'TNG', 'Paid', 48, '2025-12-20 16:23:21', '2025-12-20 16:23:21'),
('PM0033', 'O0033', 'TNG', 'Cancelled', 376.6, '2025-12-20 17:13:17', '2025-12-20 17:13:17'),
('PM0034', 'O0034', 'Credit Card', 'Cancelled', 193.68, '2025-12-20 17:14:22', '2025-12-20 17:14:22'),
('PM0035', 'O0035', 'Credit Card', 'Cancelled', 173.54, '2025-12-20 12:15:44', '2025-12-20 17:17:44'),
('PM0036', 'O0036', 'Credit Card', 'Cancelled', 4239.7, '2025-12-20 13:20:47', '2025-12-20 17:21:14'),
('PM0037', 'O0037', 'Credit Card', 'Cancelled', 679.16, '2025-12-20 13:27:15', '2025-12-20 17:27:27'),
('PM0038', 'O0038', 'TNG', 'Cancelled', 89.34, '2025-12-21 12:47:54', '2025-12-21 16:47:56'),
('PM0039', 'O0039', 'Credit Card', 'Cancelled', 215.94, '2025-12-21 12:51:24', '2025-12-21 16:55:50'),
('PM0040', 'O0040', 'TNG', 'Cancelled', 215.94, '2025-12-21 13:42:44', '2025-12-21 17:43:32'),
('PM0041', 'O0041', 'TNG', 'Cancelled', 510.62, '2025-12-21 16:48:36', '2025-12-22 02:40:26'),
('PM0042', 'O0042', 'TNG', 'Cancelled', 841.94, '2025-12-21 17:53:53', '2025-12-22 02:40:26'),
('PM0043', 'O0043', 'Credit Card', 'Cancelled', 205.94, '2025-12-21 18:05:52', '2025-12-22 02:40:26'),
('PM0044', 'O0044', 'Credit Card', 'Cancelled', 215.94, '2025-12-22 03:30:42', '2025-12-22 07:30:43'),
('PM0045', 'O0045', 'Credit Card', 'Cancelled', 185.8, '2025-12-22 08:14:18', '2025-12-22 12:54:10'),
('PM0046', 'O0046', 'TNG', 'Paid', 426.88, '2025-12-22 08:17:26', '2025-12-22 08:24:03');

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`wishlist_id`, `customer_id`) VALUES
('WL0001', 'CU0001');

--
-- Dumping data for table `wishlist_items`
--

INSERT INTO `wishlist_items` (`wishlist_item_id`, `wishlist_id`, `product_variant_id`) VALUES
('WI0002', 'WL0001', 'PV0004'),
('WI0003', 'WL0001', 'PV0005'),
('WI0004', 'WL0001', 'PV0007'),
('WI0005', 'WL0001', 'PV0056'),
('WI0006', 'WL0001', 'PV0030'),
('WI0007', 'WL0001', 'PV0013'),
('WI0008', 'WL0001', 'PV0016'),
('WI0009', 'WL0001', 'PV0029'),
('WI0011', 'WL0001', 'PV0010'),
('WI0012', 'WL0001', 'PV0017'),
('WI0013', 'WL0001', 'PV0009'),
('WI0016', 'WL0001', 'PV0001');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
