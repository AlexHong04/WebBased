-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 22, 2025 at 06:11 AM
-- Server version: 8.0.43
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
CREATE DATABASE IF NOT EXISTS `lovine` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `lovine`;

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE `address` (
  `address_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `customer_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `recipient_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `recipient_phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `street_line` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `state` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `postcode` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `address`
--

INSERT INTO `address` (`address_id`, `customer_id`, `recipient_name`, `recipient_phone`, `street_line`, `city`, `state`, `postcode`, `is_default`, `is_deleted`) VALUES
('AD0001', 'CU0006', 'Tan Kok Hong', '012-3333445', '2333333', '12', '1', '1', 1, 0),
('AD0002', 'CU0006', 'Tan Kok Hong', '012-3333445', '2333333', '12', '1', '1', 1, 0),
('AD0003', 'CU0006', 'Tan Kok Hong', '012-3333445', '2333333', '12', '1', '1', 1, 0),
('AD0004', 'CU0006', 'Tan Kok Hong', '012-3333445', '2333333', '12', '1', '1', 1, 0),
('AD0005', 'CU0006', 'Tan Kok Hong', '012-3333445', '2333333', '12', '1', '1', 1, 0),
('AD0006', 'CU0006', 'Tan Kok Hong', '012-3333445', '2333333', '12', '1', '1', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `firstName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lastName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `position` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `email`, `firstName`, `lastName`, `password`, `phone`, `address`, `created_at`, `position`) VALUES
('AD0001', 'kaiying2455@gmail.com', 'John', 'Doe', '$2y$10$v3zlOzIeoOCPKGYtZDSFJuYjRoHr.HU1jrOwX0zRyX9tIQeczdwWW', '0123456789', 'No. 10, Jalan Bukit, Kuala Lumpur', '2025-12-17 15:43:36', 'Manager');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `customer_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_datetime` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `cart_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `product_variant_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` int NOT NULL,
  `cart_status` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `category_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
('CA0001', 'Bracelet'),
('CA0002', 'Ring'),
('CA0003', 'Necklace'),
('CA0004', 'Earring'),
('CA0005', 'Hairclaw');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `firstName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `lastName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `gender` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `rewardPoint` int DEFAULT NULL,
  `isActive` tinyint(1) NOT NULL,
  `isBlocked` tinyint(1) DEFAULT NULL,
  `img_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customer_id`, `firstName`, `lastName`, `email`, `gender`, `password`, `phone`, `created_at`, `updated_at`, `rewardPoint`, `isActive`, `isBlocked`, `img_url`) VALUES
('CU0001', '', '', '', '', 'pass123', '', '2025-12-14 08:18:46', '2025-12-14 08:18:46', 95, 1, 1, ''),
('CU0002', 'Bella', 'Tan', 'bella@gmail.com', '', 'pass123', '0112223333', '2025-12-07 08:45:32', '2025-12-07 08:45:32', 50, 1, 0, ''),
('CU0003', 'Chris', 'Lee', 'chris@gmail.com', '', 'pass123', '0198887777', '2025-12-07 08:01:54', NULL, 20, 1, 0, ''),
('CU0004', 'Diana', ' Wong', 'diana@gmail.com', '', 'pass123', '0135558888', '2025-12-07 08:01:54', NULL, 10, 1, 1, ''),
('CU0005', 'Edwin', 'Kong', 'edwin@gmail.com', '', 'pass123', '016-6677777', '2025-12-19 15:56:52', NULL, 0, 1, 0, ''),
('CU0006', 'Tan', 'Kok Hong', 'kokhong704@gmail.com', 'Male', '$2y$10$Q/4WFFq/vRXpiKB2WBsIG.qZMT8pxdPO3diw4DjrROYAheZMf5kfS', '0176265778', '2025-12-21 08:12:06', '2025-12-21 06:32:41', NULL, 1, NULL, ''),
('CU0007', 'Alex', 'Hong', 'alexhong704@gmail.com', 'U', '$2y$10$y.v0GBg2JlTXb1EQU6VwNeP53CJ.UM6dVCdCpheZmrirIfDtjG9QK', NULL, '2025-12-21 11:31:13', NULL, NULL, 1, 0, 'https://lh3.googleusercontent.com/a/ACg8ocK93DKwx4m2tajKGfq0evKXV6UbW6eX2pIIC4eDsL7K4PysjQ=s96-c');

-- --------------------------------------------------------

--
-- Table structure for table `orderstatus`
--

CREATE TABLE `orderstatus` (
  `order_status_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `order_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_datetime` datetime DEFAULT CURRENT_TIMESTAMP,
  `order_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderstatus`
--

INSERT INTO `orderstatus` (`order_status_id`, `order_status`, `created_datetime`, `order_id`) VALUES
('OS0001', 'Paid', '2025-12-11 02:07:27', 'O00001'),
('OS0002', 'Delivered', '2025-12-11 02:20:27', 'O00001'),
('OS0003', 'Cancelled', '2025-12-11 02:42:42', 'O00001'),
('OS0004', 'Delivered', '2025-12-11 02:49:52', 'O00001'),
('OS0005', 'Cancel Requested', '2025-12-12 23:29:45', 'O00001'),
('OS0008', 'Paid', '2025-12-13 01:30:48', 'O00002'),
('OS0009', 'Paid', '2025-12-13 01:30:48', 'O00003'),
('OS0010', 'Paid', '2025-12-13 01:30:48', 'O00004'),
('OS0012', 'Packing', '2025-12-13 01:32:23', 'O00001'),
('OS0013', 'Packing', '2025-12-13 01:32:23', 'O00002'),
('OS0015', 'Out for Delivery', '2025-12-13 01:46:17', 'O00002'),
('OS0020', 'Packing', '2025-12-17 03:33:38', 'O00003'),
('OS0021', 'Out for Delivery', '2025-12-17 03:33:42', 'O00003'),
('OS0022', 'Cancel Requested', '2025-12-17 03:33:46', 'O00003'),
('OS0023', 'Cancelled', '2025-12-17 03:34:48', 'O00003'),
('OS0024', 'Delivered', '2025-12-17 13:37:58', 'O00002'),
('OS0025', 'Out for Delivery', '2025-12-17 23:10:50', 'O00001'),
('OS0026', 'Delivered', '2025-12-17 23:11:29', 'O00001'),
('OS0027', 'Completed', '2025-12-17 23:17:03', 'O00001'),
('OS0028', 'Cancel Requested', '2025-12-18 02:30:55', 'O00001'),
('OS0031', 'Packing', '2025-12-18 02:39:29', 'O00004'),
('OS0032', 'Cancel Requested', '2025-12-18 02:40:32', 'O00004'),
('OS0033', 'Cancelled', '2025-12-18 03:02:16', 'O00001'),
('OS0034', 'Refunded', '2025-12-21 03:02:16', 'O00001'),
('OS0035', 'Pending', '2025-01-05 10:00:00', 'O00005'),
('OS0036', 'Paid', '2025-01-05 10:05:00', 'O00005'),
('OS0037', 'Packing', '2025-01-05 11:00:00', 'O00005'),
('OS0038', 'Out for Delivery', '2025-01-06 09:00:00', 'O00005'),
('OS0039', 'Delivered', '2025-01-06 15:00:00', 'O00005'),
('OS0040', 'Completed', '2025-01-07 10:00:00', 'O00005'),
('OS0041', 'Pending', '2025-02-03 14:30:00', 'O00006'),
('OS0042', 'Paid', '2025-02-03 14:35:00', 'O00006'),
('OS0043', 'Packing', '2025-02-03 15:30:00', 'O00006'),
('OS0044', 'Out for Delivery', '2025-02-04 09:15:00', 'O00006'),
('OS0045', 'Delivered', '2025-02-04 16:20:00', 'O00006'),
('OS0046', 'Pending', '2025-03-08 11:00:00', 'O00007'),
('OS0047', 'Paid', '2025-03-08 11:05:00', 'O00007'),
('OS0048', 'Cancel Requested', '2025-03-08 11:40:00', 'O00007'),
('OS0049', 'Cancelled', '2025-03-08 12:10:00', 'O00007'),
('OS0050', 'Pending', '2025-04-12 09:20:00', 'O00008'),
('OS0051', 'Paid', '2025-04-12 09:25:00', 'O00008'),
('OS0052', 'Packing', '2025-04-12 10:30:00', 'O00008'),
('OS0053', 'Out for Delivery', '2025-04-13 09:00:00', 'O00008'),
('OS0054', 'Delivered', '2025-04-13 15:30:00', 'O00008'),
('OS0055', 'Completed', '2025-04-14 10:00:00', 'O00008'),
('OS0056', 'Pending', '2025-05-18 13:10:00', 'O00009'),
('OS0057', 'Paid', '2025-05-18 13:15:00', 'O00009'),
('OS0058', 'Cancel Requested', '2025-05-18 14:00:00', 'O00009'),
('OS0059', 'Refunded', '2025-05-19 11:30:00', 'O00009'),
('OS0060', 'Pending', '2025-06-22 16:00:00', 'O00010'),
('OS0061', 'Paid', '2025-06-22 16:05:00', 'O00010'),
('OS0062', 'Packing', '2025-06-22 17:00:00', 'O00010'),
('OS0063', 'Out for Delivery', '2025-06-23 09:30:00', 'O00010'),
('OS0064', 'Delivered', '2025-06-23 15:45:00', 'O00010'),
('OS0065', 'Pending', '2025-07-03 10:15:00', 'O00011'),
('OS0066', 'Paid', '2025-07-03 10:20:00', 'O00011'),
('OS0067', 'Packing', '2025-07-03 11:30:00', 'O00011'),
('OS0068', 'Out for Delivery', '2025-07-04 09:10:00', 'O00011'),
('OS0069', 'Delivered', '2025-07-04 15:40:00', 'O00011'),
('OS0070', 'Completed', '2025-07-05 10:00:00', 'O00011'),
('OS0071', 'Pending', '2025-08-06 14:00:00', 'O00012'),
('OS0072', 'Paid', '2025-08-06 14:05:00', 'O00012'),
('OS0073', 'Packing', '2025-08-06 15:00:00', 'O00012'),
('OS0074', 'Out for Delivery', '2025-08-07 09:00:00', 'O00012'),
('OS0075', 'Delivered', '2025-08-07 16:10:00', 'O00012'),
('OS0076', 'Pending', '2025-09-09 11:30:00', 'O00013'),
('OS0077', 'Paid', '2025-09-09 11:35:00', 'O00013'),
('OS0078', 'Cancel Requested', '2025-09-09 12:15:00', 'O00013'),
('OS0079', 'Cancelled', '2025-09-09 12:45:00', 'O00013'),
('OS0080', 'Pending', '2025-10-14 09:00:00', 'O00014'),
('OS0081', 'Paid', '2025-10-14 09:05:00', 'O00014'),
('OS0082', 'Packing', '2025-10-14 10:15:00', 'O00014'),
('OS0083', 'Out for Delivery', '2025-10-15 09:30:00', 'O00014'),
('OS0084', 'Delivered', '2025-10-15 16:30:00', 'O00014'),
('OS0085', 'Completed', '2025-10-16 10:00:00', 'O00014'),
('OS0086', 'Pending', '2025-11-18 13:20:00', 'O00015'),
('OS0087', 'Paid', '2025-11-18 13:25:00', 'O00015'),
('OS0088', 'Cancel Requested', '2025-11-18 14:10:00', 'O00015'),
('OS0089', 'Refunded', '2025-11-19 11:00:00', 'O00015'),
('OS0090', 'Pending', '2025-12-02 16:00:00', 'O00016'),
('OS0091', 'Paid', '2025-12-02 16:05:00', 'O00016'),
('OS0092', 'Packing', '2025-12-02 17:00:00', 'O00016'),
('OS0093', 'Out for Delivery', '2025-12-03 09:45:00', 'O00016'),
('OS0094', 'Delivered', '2025-12-03 15:50:00', 'O00016'),
('OS0095', 'Pending', '2025-12-08 10:30:00', 'O00017'),
('OS0096', 'Paid', '2025-12-08 10:35:00', 'O00017'),
('OS0097', 'Packing', '2025-12-08 11:30:00', 'O00017'),
('OS0098', 'Out for Delivery', '2025-12-09 09:00:00', 'O00017'),
('OS0099', 'Delivered', '2025-12-09 15:30:00', 'O00017'),
('OS0100', 'Completed', '2025-12-10 10:00:00', 'O00017'),
('OS0101', 'Pending', '2025-12-15 14:10:00', 'O00018'),
('OS0105', 'Pending', '2025-12-20 09:20:00', 'O00019'),
('OS0106', 'Paid', '2025-12-20 09:25:00', 'O00019'),
('OS0107', 'Packing', '2025-12-20 10:30:00', 'O00019'),
('OS0108', 'Out for Delivery', '2025-12-21 09:00:00', 'O00019'),
('OS0109', 'Delivered', '2025-12-21 16:00:00', 'O00019'),
('OS0110', 'Pending', '2025-12-19 11:00:00', 'O00020'),
('OS0111', 'Paid', '2025-12-19 11:05:00', 'O00020'),
('OS0112', 'Packing', '2025-12-21 12:00:00', 'O00020'),
('OS0113', 'Delivered', '2025-12-22 01:34:32', 'O00020'),
('OS0114', 'Pending', '2025-12-22 11:56:13', 'O0021'),
('OS0115', 'Paid', '2025-12-22 11:59:02', 'O0021'),
('OS0116', 'Packing', '2025-12-22 11:59:54', 'O0021'),
('OS0117', 'Out for Delivery', '2025-12-22 12:01:40', 'O0021'),
('OS0118', 'Delivered', '2025-12-22 12:01:49', 'O0021'),
('OS0119', 'Completed', '2025-12-22 12:04:02', 'O0021');

-- --------------------------------------------------------

--
-- Table structure for table `ordertable`
--

CREATE TABLE `ordertable` (
  `order_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `total_amount` double NOT NULL,
  `total_order_qty` int NOT NULL,
  `reward` double DEFAULT NULL,
  `redeemed_point` int NOT NULL DEFAULT '0',
  `tax_fee` double NOT NULL,
  `customer_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `address_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ordertable`
--

INSERT INTO `ordertable` (`order_id`, `total_amount`, `total_order_qty`, `reward`, `redeemed_point`, `tax_fee`, `customer_id`, `address_id`) VALUES
('O00001', 288, 3, 10, 0, 2, 'CU0001', 'AD0001'),
('O00002', 178, 2, 5, 0, 1.5, 'CU0002', 'AD0001'),
('O00003', 799, 1, 20, 0, 5, 'CU0003', 'AD0001'),
('O00004', 49, 1, 49, 0, 1, 'CU0004', 'AD0001'),
('O00005', 159, 1, 10, 0, 1.5, 'CU0005', 'AD0001'),
('O00006', 320, 2, 15, 0, 3, 'CU0002', 'AD0001'),
('O00007', 450, 3, 20, 0, 5, 'CU0003', 'AD0002'),
('O00008', 120, 1, 5, 0, 2, 'CU0001', 'AD0001'),
('O00009', 680, 4, 30, 0, 6, 'CU0005', 'AD0002'),
('O00010', 250, 2, 12, 0, 3, 'CU0006', 'AD0001'),
('O00011', 180, 1, 8, 0, 2, 'CU0004', 'AD0001'),
('O00012', 540, 3, 25, 0, 6, 'CU0002', 'AD0002'),
('O00013', 295, 2, 14, 0, 3, 'CU0001', 'AD0001'),
('O00014', 760, 5, 35, 0, 8, 'CU0006', 'AD0002'),
('O00015', 410, 3, 18, 0, 4, 'CU0003', 'AD0001'),
('O00016', 150, 1, 6, 0, 2, 'CU0005', 'AD0001'),
('O00017', 620, 4, 28, 0, 7, 'CU0004', 'AD0002'),
('O00018', 275, 2, 13, 0, 3, 'CU0002', 'AD0001'),
('O00019', 890, 6, 40, 0, 9, 'CU0006', 'AD0002'),
('O00020', 330, 2, 330, 0, 4, 'CU0001', 'AD0001'),
('O0021', 56.94, 1, 56, 0, 2.94, 'CU0007', 'AD0001');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `product_variant_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `price` double NOT NULL,
  `order_qty` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_id`, `product_variant_id`, `price`, `order_qty`) VALUES
('O00001', 'PV0001', 199, 1),
('O00001', 'PV0003', 89, 2),
('O00002', 'PV0002', 89, 2),
('O00003', 'PV0003', 799, 1),
('O00004', 'PV0003', 120, 1),
('O00004', 'PV0007', 80, 2),
('O00005', 'PV0010', 150, 1),
('O00006', 'PV0005', 160, 1),
('O00006', 'PV0012', 160, 1),
('O00007', 'PV0008', 150, 1),
('O00007', 'PV0015', 200, 1),
('O00007', 'PV0020', 100, 1),
('O00008', 'PV0002', 120, 1),
('O00009', 'PV0018', 200, 2),
('O00009', 'PV0022', 140, 1),
('O00009', 'PV0025', 200, 1),
('O00010', 'PV0009', 125, 2),
('O00011', 'PV0003', 180, 1),
('O00012', 'PV0005', 200, 1),
('O00012', 'PV0012', 170, 1),
('O00012', 'PV0020', 170, 1),
('O00013', 'PV0007', 150, 1),
('O00013', 'PV0010', 145, 1),
('O00014', 'PV0002', 160, 2),
('O00014', 'PV0015', 140, 1),
('O00014', 'PV0018', 160, 2),
('O00015', 'PV0009', 140, 1),
('O00015', 'PV0016', 135, 2),
('O00016', 'PV0004', 150, 1),
('O00017', 'PV0011', 155, 2),
('O00017', 'PV0023', 155, 2),
('O00018', 'PV0006', 140, 1),
('O00018', 'PV0019', 135, 1),
('O00019', 'PV0001', 150, 3),
('O00019', 'PV0014', 145, 3),
('O00020', 'PV0008', 165, 2),
('O0021', 'PV0007', 49, 1);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `order_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `payment_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `amount` double NOT NULL,
  `created_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_datetime` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `order_id`, `payment_method`, `payment_status`, `amount`, `created_datetime`, `updated_datetime`) VALUES
('PM001', 'O00001', 'Online Banking', 'Paid', 199, '2025-11-27 16:53:47', NULL),
('PM002', 'O00002', 'Credit Card', 'Pending', 178, '2025-11-27 16:53:47', NULL),
('PM0021', 'O0021', 'Credit Card', 'Paid', 56.94, '2025-12-22 03:59:02', '2025-12-22 03:59:02'),
('PM003', 'O00003', 'Debit Card', 'Paid', 799, '2025-11-27 16:53:47', NULL),
('PM004', 'O00004', 'E-wallet', 'Refunded', 49, '2025-11-27 16:53:47', NULL),
('PM005', 'O00005', 'Credit Card', 'Paid', 159, '2025-11-27 16:53:47', NULL),
('PM006', 'O00006', 'Online Banking', 'Paid', 320, '2025-11-28 02:16:00', NULL),
('PM007', 'O00007', 'Credit Card', 'Paid', 450, '2025-11-28 03:41:00', NULL),
('PM008', 'O00008', 'E-Wallet', 'Paid', 120, '2025-11-29 01:21:00', NULL),
('PM009', 'O00009', 'Online Banking', 'Paid', 680, '2025-11-29 06:11:00', '2025-11-29 07:30:00'),
('PM010', 'O00010', 'Credit Card', 'Paid', 250, '2025-11-30 08:46:00', NULL),
('PM011', 'O00016', 'E-Wallet', 'Paid', 150, '2025-11-28 06:00:00', NULL),
('PM012', 'O00017', 'Online Banking', 'Paid', 620, '2025-11-28 07:10:00', NULL),
('PM013', 'O00018', 'Credit Card', 'Paid', 275, '2025-11-28 08:00:00', NULL),
('PM014', 'O00019', 'Online Banking', 'Paid', 890, '2025-11-28 09:30:00', NULL),
('PM015', 'O00020', 'E-Wallet', 'Paid', 330, '2025-11-28 10:10:00', NULL),
('PM016', 'O00011', 'Online Banking', 'Paid', 320, '2025-12-21 16:42:58', NULL),
('PM017', 'O00012', 'Credit Card', 'Paid', 450, '2025-12-21 16:43:04', NULL),
('PM018', 'O00013', 'E-Wallet', 'Paid', 120, '2025-12-21 16:43:08', NULL),
('PM019', 'O00014', 'Online Banking', 'Paid', 680, '2025-12-21 16:43:12', NULL),
('PM020', 'O00015', 'Credit Card', 'Paid', 250, '2025-12-21 16:43:15', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `product_name` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `total_sold` int DEFAULT NULL,
  `cost_price` double NOT NULL,
  `rate` double DEFAULT NULL,
  `img_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sale_price` double NOT NULL,
  `category_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `product_name`, `description`, `created_at`, `updated_at`, `total_sold`, `cost_price`, `rate`, `img_url`, `sale_price`, `category_id`) VALUES
('PR0001', 'Butterfly Bracelet', 'A stylish accessory perfect for everyday wear.', '2025-12-14 17:34:48', '2025-11-27 08:53:47', 120, 150, 4.5, 'PV0001.jpg，PV0002.jpg\n', 199, 'CA0001'),
('PR0002', 'Diamond Bracelet', 'Designed to add a touch of elegance to your look.', '2025-12-14 17:30:09', '2025-11-27 08:53:47', 80, 60, 4.2, 'PR0002_img1.jpeg, PR0002_img2.jpeg, PR0002_img3.jpeg', 89, 'CA0001'),
('PR0003', 'Fairy Bracelet', 'Lightweight, durable, and comfortable to use.', '2025-12-18 04:38:37', '2025-11-27 08:53:47', 40, 50, 4.9, 'PV0005.jpg，PV0006.jpg\n', 79, 'CA0001'),
('PR0004', 'Lotus Bracelet', 'A timeless piece that matches any outfit.', '2025-12-14 17:34:48', '2025-11-27 08:53:47', 150, 30, 4.3, 'PV0007.jpg，PV0008.jpg\n', 49, 'CA0001'),
('PR0005', 'Winter Jasmine', 'Crafted with quality materials for long-lasting use.', '2025-12-14 17:34:48', '2025-11-27 08:53:47', 60, 120, 4.6, 'PV0009.jpg，PV0010.jpg\n', 159, 'CA0001'),
('PR0006', 'Jeulia Ring', 'A trendy design made for modern fashion lovers.', '2025-12-14 17:35:10', '2025-11-29 08:52:52', 0, 40, 5, 'PV0011.jpg，PV0012.jpg\n', 70, 'CA0002'),
('PR0007', 'Love Ring', 'Adds a subtle sparkle to elevate your style.', '2025-12-14 17:49:11', '2025-11-29 08:52:52', 0, 30, 5, 'PR0007_img1.jpeg, PR0007_img2.jpge, PR0007_img3.jpeg', 60, 'CA0002'),
('PR0008', 'Lyra Ring', 'Simple yet eye-catching for any occasion.', '2025-12-14 17:35:10', '2025-11-29 08:52:52', 0, 50, 5, 'PV0015.jpg，PV0016.jpg\n', 90, 'CA0002'),
('PR0009', 'Manilla Ring', 'A must-have accessory for your collection.', '2025-12-14 17:35:10', '2025-11-29 08:52:52', 0, 20, 5, 'PV0017.jpg，PV0018.jpg\n', 50, 'CA0002'),
('PR0010', 'Soulmate Ring', 'Made to complement both casual and formal outfits.', '2025-12-14 17:35:10', '2025-11-29 08:52:52', 0, 20.5, 5, 'PV0019.jpg，PV0020.jpg\n', 30, 'CA0002'),
('PR0011', 'Clover Necklace', 'Beautifully crafted with attention to detail.', '2025-12-14 17:34:56', '2025-11-29 08:52:52', 0, 40.5, 5, 'PV0021.jpg，PV0022.jpg\n', 70.5, 'CA0003'),
('PR0012', 'Diamond Necklace', 'A perfect gift for friends or loved ones.', '2025-12-14 17:26:56', '2025-11-29 08:52:52', 0, 30.5, 5, 'PR0012_img1.jpeg, PR0012_img2.jpeg, PR0012_img3.jpeg', 60.5, 'CA0003'),
('PR0013', 'Eutosma Necklace', 'Minimalist design that blends with any style.', '2025-12-14 17:34:56', '2025-11-29 08:52:52', 0, 25.5, 5, 'PV0025.jpg，PV0026.jpg\n', 39.5, 'CA0003'),
('PR0014', 'Pear Necklace', 'Features a smooth finish for a comfortable fit.', '2025-12-14 17:34:56', '2025-11-29 08:52:52', 0, 29.2, 5, 'PV0027.jpg，PV0028.jpg\n', 35.5, 'CA0003'),
('PR0015', 'Ribbon Necklace', 'Easy to match with other accessories.', '2025-12-14 17:34:56', '2025-11-29 08:52:52', 0, 21.8, 5, 'PV0029.jpg，PV0030.jpg\n', 38.5, 'CA0003'),
('PR0016', 'Butterfly Dreams Earring', 'A stylish choice for daily wear.', '2025-12-14 17:35:02', '2025-11-29 08:52:52', 0, 40, 5, 'PV0031.jpg，PV0032.jpg\n', 70, 'CA0004'),
('PR0017', 'Calla Ribbon Earring', 'Made to stand out without being too flashy.', '2025-12-14 17:52:47', '2025-11-29 08:52:52', 0, 30, 5, 'PV0033.jpg', 60, 'CA0004'),
('PR0018', 'Hane Pearl Earring', 'Enhances your natural beauty effortlessly.', '2025-12-14 17:35:02', '2025-11-29 08:52:52', 0, 55, 5, 'PV0035.jpg，PV0036.jpg\n', 90, 'CA0004'),
('PR0019', 'Pear Earring', 'Carefully crafted to ensure premium quality.', '2025-12-14 17:29:29', '2025-11-29 08:52:52', 0, 25, 5, 'PR0019_img1.jpeg, PR0019_img2.jepg, PR0019_img3.jpeg', 55, 'CA0004'),
('PR0020', 'Rose Rommance Earring', 'Adds charm and personality to your outfit.', '2025-12-14 17:35:02', '2025-11-29 08:52:52', 0, 15.5, 5, 'PV0039.jpg，PV0040.jpg\n', 35, 'CA0004'),
('PR0021', 'Autumn Hairclaw', 'Designed to stay secure and comfortable.', '2025-12-14 17:35:23', '2025-11-29 08:52:52', 0, 46.2, 5, 'PV0041.jpg, PV0042.jpg,  PV0043.jpg\n \n', 70.2, 'CA0005'),
('PR0022', 'Minimalist Hairclaw', 'A classic look that never goes out of style.', '2025-12-14 17:35:23', '2025-11-29 08:52:52', 0, 53.2, 5, 'PV0044.jpg, PV0045.jpg, PV0046.jpg', 65.2, 'CA0005'),
('PR0023', 'Ribbon Hairclaw', 'Perfect for adding a touch of sophistication.', '2025-12-14 17:28:12', '2025-11-29 08:52:52', 0, 50.2, 5, 'PR0023_img1.jpeg, PR0023_img2.jpeg, PR0023_img3.jpeg', 60.2, 'CA0005'),
('PR0024', 'Summer Hair Claw', 'Trendy piece that keeps you stylish all day.', '2025-12-14 17:35:23', '2025-11-29 08:52:52', 0, 25.2, 5, 'PV0050.jpg, PV0051.jpg, PV0052.jpg', 55.2, 'CA0005'),
('PR0025', 'Tulip Hairclaw', 'A simple accessory that makes a big difference.', '2025-12-14 17:35:23', '2025-11-29 08:52:52', 0, 15.6, 5, 'PV0053.jpg, PV0054.jpg, PV0055.jpg', 35.2, 'CA0005');

-- --------------------------------------------------------

--
-- Table structure for table `product_variant`
--

CREATE TABLE `product_variant` (
  `product_variant_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `min_stock_level` int NOT NULL,
  `stock_qty` int NOT NULL,
  `stock_status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `variant_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `img_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variant`
--

INSERT INTO `product_variant` (`product_variant_id`, `min_stock_level`, `stock_qty`, `stock_status`, `variant_id`, `product_id`, `img_url`) VALUES
('PV0001', 5, 20, 'In Stock', 'VR0001', 'PR0001', 'PV0001.jpg'),
('PV0002', 5, 15, 'In Stock', 'VR0002', 'PR0001', 'PV0002.jpg'),
('PV0003', 5, 10, 'In Stock', 'VR0006', 'PR0002', 'PV0003.jpg'),
('PV0004', 5, 10, 'In Stock', 'VR0007', 'PR0002', 'PV0004.jpg'),
('PV0005', 5, 25, 'In Stock', 'VR0010', 'PR0003', 'PV0005.jpg'),
('PV0006', 5, 12, 'Low Stock', 'VR0018', 'PR0003', 'PV0006.jpg'),
('PV0007', 5, 18, 'In Stock', 'VR0006', 'PR0004', 'PV0007.jpg'),
('PV0008', 5, 7, 'In Stock', 'VR0002', 'PR0004', 'PV0008.jpg'),
('PV0009', 5, 30, 'In Stock', 'VR0001', 'PR0005', 'PV0009.jpg'),
('PV0010', 5, 5, 'Low Stock', 'VR0002', 'PR0005', 'PV0010.jpg'),
('PV0011', 5, 30, 'In Stock', 'VR0002', 'PR0006', 'PV0011.jpg'),
('PV0012', 5, 5, 'In Stock', 'VR0001', 'PR0006', 'PV0012.jpg'),
('PV0013', 5, 30, 'In Stock', 'VR0001', 'PR0007', 'PV0013.jpg'),
('PV0014', 5, 5, 'In Stock', 'VR0002', 'PR0007', 'PV0014.jpg'),
('PV0015', 5, 30, 'In Stock', 'VR0001', 'PR0008', 'PV0015.jpg'),
('PV0016', 5, 5, 'Low Stock', 'VR0002', 'PR0008', 'PV0016.jpg'),
('PV0017', 5, 30, 'In Stock', 'VR0017', 'PR0009', 'PV0017.jpg'),
('PV0018', 5, 5, 'In Stock', 'VR0001', 'PR0009', 'PV0018.jpg'),
('PV0019', 5, 30, 'In Stock', 'VR0001', 'PR0010', 'PV0019.jpg'),
('PV0020', 5, 5, 'In Stock', 'VR0002', 'PR0010', 'PV0020.jpg'),
('PV0021', 5, 30, 'In Stock', 'VR0001', 'PR0011', 'PV0021.jpg'),
('PV0022', 5, 5, 'In Stock', 'VR0002', 'PR0011', 'PV0022.jpg'),
('PV0023', 5, 30, 'In Stock', 'VR0001', 'PR0012', 'PV0023.jpg'),
('PV0024', 5, 5, 'In Stock', 'VR0002', 'PR0012', 'PV0024.jpg'),
('PV0025', 5, 30, 'In Stock', 'VR0001', 'PR0013', 'PV0025.jpg'),
('PV0026', 5, 5, 'Low Stock', 'VR0002', 'PR0013', 'PV0026.jpg'),
('PV0027', 5, 30, 'In Stock', 'VR0015', 'PR0014', 'PV0027.jpg'),
('PV0028', 5, 5, 'Low Stock', 'VR0016', 'PR0014', 'PV0028.jpg'),
('PV0029', 5, 30, 'In Stock', 'VR0020', 'PR0015', 'PV0029.jpg'),
('PV0030', 5, 5, 'In Stock', 'VR0002', 'PR0015', 'PV0030.jpg'),
('PV0031', 5, 30, 'In Stock', 'VR0001', 'PR0016', 'PV0031.jpg'),
('PV0032', 5, 5, 'Low Stock', 'VR0002', 'PR0016', 'PV0032.jpg'),
('PV0033', 5, 30, 'In Stock', 'VR0001', 'PR0017', 'PV0033.jpg'),
('PV0034', 5, 5, 'Low Stock', 'VR0002', 'PR0017', 'PV0034.jpg'),
('PV0035', 5, 30, 'In Stock', 'VR0001', 'PR0018', 'PV0035.jpg'),
('PV0036', 5, 5, 'In Stock', 'VR0002', 'PR0018', 'PV0036.jpg'),
('PV0037', 5, 30, 'In Stock', 'VR0003', 'PR0019', 'PV0037.jpg'),
('PV0038', 5, 5, 'In Stock', 'VR0004', 'PR0019', 'PV0038.jpg'),
('PV0039', 5, 30, 'In Stock', 'VR0001', 'PR0020', 'PV0039.jpg'),
('PV0040', 5, 5, 'In Stock', 'VR0002', 'PR0020', 'PV0040.jpg'),
('PV0041', 5, 30, 'In Stock', 'VR0006', 'PR0021', 'PV0041.jpg'),
('PV0042', 5, 5, 'In Stock', 'VR0009', 'PR0021', 'PV0042.jpg'),
('PV0043', 5, 5, 'In Stock', 'VR0007', 'PR0021', 'PV0043.jpg'),
('PV0044', 5, 30, 'In Stock', 'VR0019', 'PR0022', 'PV0044.jpg'),
('PV0045', 5, 5, 'In Stock', 'VR0007', 'PR0022', 'PV0045.jpg'),
('PV0046', 5, 5, 'In Stock', 'VR0008', 'PR0022', 'PV0046.jpg'),
('PV0047', 5, 30, 'In Stock', 'VR0006', 'PR0023', 'PV0047.jpg'),
('PV0048', 5, 5, 'In Stock', 'VR0007', 'PR0023', 'PV0048.jpg'),
('PV0049', 5, 0, 'In Stock', 'VR0019', 'PR0023', 'PV0049.jpg'),
('PV0050', 5, 30, 'In Stock', 'VR0012', 'PR0024', 'PV0050.jpg'),
('PV0051', 5, 5, 'Low Stock', 'VR0007', 'PR0024', 'PV0051.jpg'),
('PV0052', 5, 5, 'In Stock', 'VR0013', 'PR0024', 'PV0052.jpg'),
('PV0053', 5, 30, 'In Stock', 'VR0006', 'PR0025', 'PV0053.jpg'),
('PV0054', 5, 5, 'In Stock', 'VR0007', 'PR0025', 'PV0054.jpg'),
('PV0055', 5, 5, 'In Stock', 'VR0014', 'PR0025', 'PV0055.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `review_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `rating` double NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `img_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_variant_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `order_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `shipment_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `order_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `receiver_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `receiver_phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `receiver_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_datetime` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `variant`
--

CREATE TABLE `variant` (
  `variant_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `variant_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `variant`
--

INSERT INTO `variant` (`variant_id`, `variant_name`) VALUES
('VR0001', 'Silver\r\n'),
('VR0002', 'Gold\r\n'),
('VR0003', 'Big'),
('VR0004', 'Small\r\n'),
('VR0005', 'Blue'),
('VR0006', 'Blue'),
('VR0007', 'Pink'),
('VR0008', 'Black'),
('VR0009', 'Brown'),
('VR0010', 'Ribbon'),
('VR0011', 'Love'),
('VR0012', 'Orange'),
('VR0013', 'Yellow'),
('VR0014', 'Purple'),
('VR0015', 'Basic'),
('VR0016', 'Butterfly'),
('VR0017', 'Green'),
('VR0018', 'Flower'),
('VR0019', 'White'),
('VR0020', 'Rosegold');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `customer_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist_items`
--

CREATE TABLE `wishlist_items` (
  `wishlist_item_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `wishlist_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `product_variant_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `fk_address_customer` (`customer_id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_variant_id` (`product_variant_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `orderstatus`
--
ALTER TABLE `orderstatus`
  ADD PRIMARY KEY (`order_status_id`),
  ADD KEY `fk_order_status_order` (`order_id`);

--
-- Indexes for table `ordertable`
--
ALTER TABLE `ordertable`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `fk_order_address` (`address_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_id`,`product_variant_id`),
  ADD KEY `product_variant_id` (`product_variant_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_variant`
--
ALTER TABLE `product_variant`
  ADD PRIMARY KEY (`product_variant_id`),
  ADD KEY `variant_id` (`variant_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `fk_review_product_variant_id` (`product_variant_id`),
  ADD KEY `fk_review_order_id` (`order_id`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`shipment_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `variant`
--
ALTER TABLE `variant`
  ADD PRIMARY KEY (`variant_id`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`wishlist_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD PRIMARY KEY (`wishlist_item_id`),
  ADD KEY `wishlist_id` (`wishlist_id`),
  ADD KEY `product_variant_id` (`product_variant_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `address`
--
ALTER TABLE `address`
  ADD CONSTRAINT `fk_address_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`);

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`cart_id`),
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variant` (`product_variant_id`);

--
-- Constraints for table `orderstatus`
--
ALTER TABLE `orderstatus`
  ADD CONSTRAINT `fk_order_status_order` FOREIGN KEY (`order_id`) REFERENCES `ordertable` (`order_id`);

--
-- Constraints for table `ordertable`
--
ALTER TABLE `ordertable`
  ADD CONSTRAINT `fk_order_address` FOREIGN KEY (`address_id`) REFERENCES `address` (`address_id`),
  ADD CONSTRAINT `ordertable_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `ordertable` (`order_id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variant` (`product_variant_id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `ordertable` (`order_id`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);

--
-- Constraints for table `product_variant`
--
ALTER TABLE `product_variant`
  ADD CONSTRAINT `product_variant_ibfk_1` FOREIGN KEY (`variant_id`) REFERENCES `variant` (`variant_id`),
  ADD CONSTRAINT `product_variant_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`);

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `fk_review_order_id` FOREIGN KEY (`order_id`) REFERENCES `order_items` (`order_id`),
  ADD CONSTRAINT `fk_review_product_variant_id` FOREIGN KEY (`product_variant_id`) REFERENCES `order_items` (`product_variant_id`);

--
-- Constraints for table `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `ordertable` (`order_id`);

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`);

--
-- Constraints for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD CONSTRAINT `wishlist_items_ibfk_1` FOREIGN KEY (`wishlist_id`) REFERENCES `wishlist` (`wishlist_id`),
  ADD CONSTRAINT `wishlist_items_ibfk_2` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variant` (`product_variant_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
