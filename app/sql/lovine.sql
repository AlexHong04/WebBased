-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 24, 2025 at 04:27 AM
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
('AD0001', 'CU0006', 'Tan Kok Hong', '0176265778', '23333331', '12', '1', '1', 1, 0),
('AD0002', 'CU0006', 'Tan Kok Hong', '0176265778', '23333331', '12', '1', '1', 1, 0),
('AD0003', 'CU0006', 'Tan Kok Hong', '0176265778', '23333331', '12', '1', '1', 1, 0),
('AD0004', 'CU0006', 'Tan Kok Hong', '0176265778', '23333331', '12', '1', '1', 1, 0),
('AD0005', 'CU0006', 'Tan Kok Hong', '0176265778', '23333331', '12', '1', '1', 1, 0),
('AD0006', 'CU0006', 'Tan Kok Hong', '0176265778', '23333331', '12', '1', '1', 1, 0),
('AD0007', 'CU0001', 'Tan Kok Hong', '0123456789', 'abc', 'kuala lumpur', 'selangor', '52000', 1, 0),
('AD0008', 'CU0001', 'Tan Kok Hong', '0176265778', '3333333', 'kuala lumpur', 'selangor', '52000', 0, 0),
('AD0009', 'CU0008', 'Tan Kok Hong', '0176265778', 'afafsdfafa', 'kuala lumpur', 'selangor', '52000', 1, 0),
('AD0010', 'CU0008', 'Tan Kok Hong', '012-3456789', 'Jalan Sierramas Utama', 'Selayang Municipal Council', 'Selangor', '47830', 0, 0);

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
('AD0001', 'kaiying2455@gmail.com', 'John', 'Doe', '$2y$10$v3zlOzIeoOCPKGYtZDSFJuYjRoHr.HU1jrOwX0zRyX9tIQeczdwWW', '0174221173', 'No. 10, Jalan Bukit, Kuala Lumpur1111111333gggg', '2025-12-23 14:53:59', 'Manager'),
('AD002', 'alexhong704@gmail.com', 'alex', 'Hong', '$2y$10$cWBis25stWcz.3lU6/YSJuZZANwLhu9KH6.4veQWLkAtchVH91jpe', '0123334455', 'null', '2025-12-22 06:15:32', 'Staff'),
('AD003', 'peifen@gmail.com', 'Tan', 'Pei Fen', '$2y$10$PfRxHQM7stiiM4f1kuNk1ORTLlJv45NMnOpWasOb3PAru3xgELrmS', '0123456781', '', '2025-12-22 13:41:31', 'Staff'),
('AD004', 'wongweixin116@gmail.com', 'wei xin', 'wong', '$2y$10$nE1BTbEodQwBJeN1ey7RYOQCpuvMi45lWlxkHToLmHKtD3QJ7weZu', '0164549207', '11', '2025-12-24 01:32:26', 'Staff');

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

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `customer_id`, `created_datetime`, `updated_datetime`) VALUES
('CA0001', 'CU0008', '2025-12-23 16:16:22', '2025-12-23 16:16:22');

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

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `cart_id`, `product_variant_id`, `quantity`, `cart_status`) VALUES
('CI0006', 'CA0001', 'PV0013', 2, 0),
('CI0007', 'CA0001', 'PV0003', 3, 0),
('CI0008', 'CA0001', 'PV0001', 1, 0);

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
  `gender` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
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
('CU0001', 'Tan', 'Kok Hong', 'kokhong705@gmail.com', 'Female', '$2y$10$m/Q5GggwlXACZOTD01UqwONRqaQIGAulWstl2Titn67GsApmkd/W2', '0176265771', '2025-12-24 02:59:00', '2025-12-24 02:58:17', 20, 1, 0, NULL),
('CU0002', 'Tan', 'Kok Hong', 'kokhong7041@gmail.com', 'Female', '$2y$10$m/Q5GggwlXACZOTD01UqwONRqaQIGAulWstl2Titn67GsApmkd/W2', '0176265771', '2025-12-24 02:57:26', '2025-12-24 02:54:19', 0, 1, 0, NULL),
('CU0003', 'Tan', 'Kok Hong', 'kokhong701@gmail.com', 'Female', '$2y$10$m/Q5GggwlXACZOTD01UqwONRqaQIGAulWstl2Titn67GsApmkd/W2', '0176265722', '2025-12-24 02:55:05', '2025-12-24 02:54:19', 100, 1, 0, NULL),
('CU0004', 'Tan', 'Kok Hong', 'kokhong70@gmail.com', 'Female', '$2y$10$m/Q5GggwlXACZOTD01UqwONRqaQIGAulWstl2Titn67GsApmkd/W2', '0176265333', '2025-12-24 02:57:34', '2025-12-24 02:54:09', 23, 1, 0, NULL),
('CU0005', 'Tan', 'Kok Hong', 'kokhong7@gmail.com', 'Female', '$2y$10$m/Q5GggwlXACZOTD01UqwONRqaQIGAulWstl2Titn67GsApmkd/W2', '0176265331', '2025-12-24 02:57:38', '2025-12-23 04:56:35', 35, 1, 0, NULL),
('CU0006', 'Tan', 'Kok Hong', 'kokhong@gmail.com', 'Female', '$2y$10$m/Q5GggwlXACZOTD01UqwONRqaQIGAulWstl2Titn67GsApmkd/W2', '0176265322', '2025-12-24 02:57:41', '2025-12-23 04:56:35', 46, 1, 0, NULL),
('CU0007', 'Tan', 'Kok Hong', 'kokhong1@gmail.com', 'Female', '$2y$10$m/Q5GggwlXACZOTD01UqwONRqaQIGAulWstl2Titn67GsApmkd/W2', '0176265321', '2025-12-24 02:57:45', '2025-12-23 04:56:35', 200, 1, 0, NULL),
('CU0008', 'Tan', 'Kok Hong', 'kokhong704@gmail.com', '', '$2y$10$k75Y6Smn3zKrmegXzqqmDOSeKBCk7TWYu11T9Av831iag3ECyW5Si', '0176265778', '2025-12-24 03:19:22', '2025-12-23 16:17:45', 446, 1, 0, NULL),
('CU0009', 'Tan', 'Kok Hong', 'kokhong703@gmail.com', '', '$2y$10$k75Y6Smn3zKrmegXzqqmDOSeKBCk7TWYu11T9Av831iag3ECyW5Si', '0176265778', '2025-12-24 02:57:52', '2025-12-23 16:17:45', 20, 1, 0, NULL),
('CU0010', 'Tan', 'Kok Hong', 'kokhong702@gmail.com', '', '$2y$10$k75Y6Smn3zKrmegXzqqmDOSeKBCk7TWYu11T9Av831iag3ECyW5Si', '0176265778', '2025-12-24 02:57:54', '2025-12-23 16:17:45', 46, 1, 0, NULL),
('CU0011', 'Tan', 'Kok Hong', 'kokhong700@gmail.com', '', '$2y$10$k75Y6Smn3zKrmegXzqqmDOSeKBCk7TWYu11T9Av831iag3ECyW5Si', '0176265778', '2025-12-24 02:57:55', '2025-12-23 16:17:45', 88, 1, 0, NULL),
('CU0012', 'Teh', 'Zhi qin', 'zhiqinteh@gmail.com', NULL, '$2y$10$qfvaehVeoN7kFe3lYPZFiulVujCwm0evZNwuyK1kxjbudQ3uNXloi', '0163428861', '2025-12-24 03:05:51', NULL, 0, 0, 0, NULL);

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
('OS0001', 'Paid', '2025-12-11 02:07:27', 'O0001'),
('OS0002', 'Delivered', '2025-12-11 02:20:27', 'O0001'),
('OS0003', 'Cancelled', '2025-12-11 02:42:42', 'O0001'),
('OS0004', 'Delivered', '2025-12-11 02:49:52', 'O0001'),
('OS0005', 'Cancel Requested', '2025-12-12 23:29:45', 'O0001'),
('OS0008', 'Paid', '2025-12-13 01:30:48', 'O0002'),
('OS0009', 'Paid', '2025-12-13 01:30:48', 'O0003'),
('OS0010', 'Paid', '2025-12-13 01:30:48', 'O0004'),
('OS0012', 'Packing', '2025-12-13 01:32:23', 'O0001'),
('OS0013', 'Packing', '2025-12-13 01:32:23', 'O0002'),
('OS0015', 'Out for Delivery', '2025-12-13 01:46:17', 'O0002'),
('OS0020', 'Packing', '2025-12-17 03:33:38', 'O0003'),
('OS0021', 'Out for Delivery', '2025-12-17 03:33:42', 'O0003'),
('OS0022', 'Cancel Requested', '2025-12-17 03:33:46', 'O0003'),
('OS0024', 'Delivered', '2025-12-17 13:37:58', 'O0002'),
('OS0025', 'Out for Delivery', '2025-12-17 23:10:50', 'O0001'),
('OS0026', 'Delivered', '2025-12-17 23:11:29', 'O0001'),
('OS0027', 'Completed', '2025-12-17 23:17:03', 'O0001'),
('OS0028', 'Cancel Requested', '2025-12-18 02:30:55', 'O0001'),
('OS0031', 'Packing', '2025-12-18 02:39:29', 'O0004'),
('OS0032', 'Cancel Requested', '2025-12-18 02:40:32', 'O0004'),
('OS0033', 'Cancelled', '2025-12-18 03:02:16', 'O0001'),
('OS0034', 'Refunded', '2025-12-21 03:02:16', 'O0001'),
('OS0035', 'Pending', '2025-01-05 10:00:00', 'O0005'),
('OS0036', 'Paid', '2025-01-05 10:05:00', 'O0005'),
('OS0037', 'Packing', '2025-01-05 11:00:00', 'O0005'),
('OS0038', 'Out for Delivery', '2025-01-06 09:00:00', 'O0005'),
('OS0039', 'Delivered', '2025-01-06 15:00:00', 'O0005'),
('OS0040', 'Completed', '2025-01-07 10:00:00', 'O0005'),
('OS0041', 'Pending', '2025-02-03 14:30:00', 'O0006'),
('OS0042', 'Paid', '2025-02-03 14:35:00', 'O0006'),
('OS0043', 'Packing', '2025-02-03 15:30:00', 'O0006'),
('OS0044', 'Out for Delivery', '2025-02-04 09:15:00', 'O0006'),
('OS0045', 'Delivered', '2025-02-04 16:20:00', 'O0006'),
('OS0046', 'Pending', '2025-03-08 11:00:00', 'O0007'),
('OS0047', 'Paid', '2025-03-08 11:05:00', 'O0007'),
('OS0048', 'Cancel Requested', '2025-03-08 11:40:00', 'O0007'),
('OS0049', 'Cancelled', '2025-03-08 12:10:00', 'O0007'),
('OS0050', 'Pending', '2025-04-12 09:20:00', 'O0008'),
('OS0051', 'Paid', '2025-04-12 09:25:00', 'O0008'),
('OS0052', 'Packing', '2025-04-12 10:30:00', 'O0008'),
('OS0053', 'Out for Delivery', '2025-04-13 09:00:00', 'O0008'),
('OS0054', 'Delivered', '2025-04-13 15:30:00', 'O0008'),
('OS0055', 'Completed', '2025-04-14 10:00:00', 'O0008'),
('OS0056', 'Pending', '2025-05-18 13:10:00', 'O0009'),
('OS0057', 'Paid', '2025-05-18 13:15:00', 'O0009'),
('OS0058', 'Cancel Requested', '2025-05-18 14:00:00', 'O0009'),
('OS0059', 'Refunded', '2025-05-19 11:30:00', 'O0009'),
('OS0060', 'Pending', '2025-06-22 16:00:00', 'O0010'),
('OS0061', 'Paid', '2025-06-22 16:05:00', 'O0010'),
('OS0062', 'Packing', '2025-06-22 17:00:00', 'O0010'),
('OS0063', 'Out for Delivery', '2025-06-23 09:30:00', 'O0010'),
('OS0064', 'Delivered', '2025-06-23 15:45:00', 'O0010'),
('OS0065', 'Pending', '2025-07-03 10:15:00', 'O0011'),
('OS0066', 'Paid', '2025-07-03 10:20:00', 'O0011'),
('OS0067', 'Packing', '2025-07-03 11:30:00', 'O0011'),
('OS0068', 'Out for Delivery', '2025-07-04 09:10:00', 'O0011'),
('OS0069', 'Delivered', '2025-07-04 15:40:00', 'O0011'),
('OS0070', 'Completed', '2025-07-05 10:00:00', 'O0011'),
('OS0071', 'Pending', '2025-08-06 14:00:00', 'O0012'),
('OS0072', 'Paid', '2025-08-06 14:05:00', 'O0012'),
('OS0073', 'Packing', '2025-08-06 15:00:00', 'O0012'),
('OS0074', 'Out for Delivery', '2025-08-07 09:00:00', 'O0012'),
('OS0075', 'Delivered', '2025-08-07 16:10:00', 'O0012'),
('OS0076', 'Pending', '2025-09-09 11:30:00', 'O0013'),
('OS0077', 'Paid', '2025-09-09 11:35:00', 'O0013'),
('OS0078', 'Cancel Requested', '2025-09-09 12:15:00', 'O0013'),
('OS0079', 'Cancelled', '2025-09-09 12:45:00', 'O0013'),
('OS0080', 'Pending', '2025-10-14 09:00:00', 'O0014'),
('OS0081', 'Paid', '2025-10-14 09:05:00', 'O0014'),
('OS0082', 'Packing', '2025-10-14 10:15:00', 'O0014'),
('OS0083', 'Out for Delivery', '2025-10-15 09:30:00', 'O0014'),
('OS0084', 'Delivered', '2025-10-15 16:30:00', 'O0014'),
('OS0085', 'Completed', '2025-10-16 10:00:00', 'O0014'),
('OS0086', 'Pending', '2025-11-18 13:20:00', 'O0015'),
('OS0087', 'Paid', '2025-11-18 13:25:00', 'O0015'),
('OS0088', 'Cancel Requested', '2025-11-18 14:10:00', 'O0015'),
('OS0089', 'Refunded', '2025-11-19 11:00:00', 'O0015'),
('OS0090', 'Pending', '2025-12-02 16:00:00', 'O0016'),
('OS0091', 'Paid', '2025-12-02 16:05:00', 'O0016'),
('OS0092', 'Packing', '2025-12-02 17:00:00', 'O0016'),
('OS0093', 'Out for Delivery', '2025-12-03 09:45:00', 'O0016'),
('OS0094', 'Delivered', '2025-12-03 15:50:00', 'O0016'),
('OS0095', 'Pending', '2025-12-08 10:30:00', 'O0017'),
('OS0096', 'Paid', '2025-12-08 10:35:00', 'O0017'),
('OS0097', 'Packing', '2025-12-08 11:30:00', 'O0017'),
('OS0098', 'Out for Delivery', '2025-12-09 09:00:00', 'O0017'),
('OS0099', 'Delivered', '2025-12-09 15:30:00', 'O0017'),
('OS0100', 'Completed', '2025-12-10 10:00:00', 'O0017'),
('OS0101', 'Pending', '2025-12-15 14:10:00', 'O0018'),
('OS0105', 'Pending', '2025-12-20 09:20:00', 'O0019'),
('OS0106', 'Paid', '2025-12-20 09:25:00', 'O0019'),
('OS0107', 'Packing', '2025-12-20 10:30:00', 'O0019'),
('OS0108', 'Out for Delivery', '2025-12-21 09:00:00', 'O0019'),
('OS0109', 'Delivered', '2025-12-21 16:00:00', 'O0019'),
('OS0110', 'Pending', '2025-12-19 11:00:00', 'O0020'),
('OS0111', 'Paid', '2025-12-19 11:05:00', 'O0020'),
('OS0112', 'Packing', '2025-12-21 12:00:00', 'O0020'),
('OS0113', 'Delivered', '2025-12-22 01:34:32', 'O0020'),
('OS0114', 'Pending', '2025-12-22 11:56:13', 'O0021'),
('OS0115', 'Paid', '2025-12-22 11:59:02', 'O0021'),
('OS0116', 'Packing', '2025-12-23 03:06:03', 'O0021'),
('OS0117', 'Pending', '2025-01-10 09:15:00', 'O0022'),
('OS0118', 'Paid', '2025-01-10 09:20:00', 'O0022'),
('OS0119', 'Packing', '2025-01-10 11:00:00', 'O0022'),
('OS0120', 'Out for Delivery', '2025-01-11 09:00:00', 'O0022'),
('OS0121', 'Delivered', '2025-01-11 15:30:00', 'O0022'),
('OS0122', 'Pending', '2025-01-12 10:00:00', 'O0023'),
('OS0123', 'Paid', '2025-01-12 10:10:00', 'O0023'),
('OS0124', 'Packing', '2025-01-12 12:00:00', 'O0023'),
('OS0125', 'Delivered', '2025-01-12 17:00:00', 'O0023'),
('OS0126', 'Pending', '2025-01-13 09:00:00', 'O0024'),
('OS0127', 'Cancelled', '2025-01-13 10:00:00', 'O0024'),
('OS0128', 'Pending', '2025-01-14 09:00:00', 'O0025'),
('OS0129', 'Paid', '2025-01-14 09:30:00', 'O0025'),
('OS0130', 'Pending', '2025-01-15 10:00:00', 'O0026'),
('OS0131', 'Packing', '2025-01-15 11:00:00', 'O0026'),
('OS0132', 'Out for Delivery', '2025-01-16 09:00:00', 'O0026'),
('OS0133', 'Pending', '2025-01-16 10:00:00', 'O0027'),
('OS0134', 'Paid', '2025-01-16 11:00:00', 'O0027'),
('OS0135', 'Pending', '2025-01-17 09:00:00', 'O0028'),
('OS0136', 'Delivered', '2025-01-17 10:00:00', 'O0028'),
('OS0137', 'Pending', '2025-01-18 09:00:00', 'O0029'),
('OS0138', 'Paid', '2025-01-18 10:00:00', 'O0029'),
('OS0139', 'Pending', '2025-01-19 09:00:00', 'O0030'),
('OS0140', 'Out for Delivery', '2025-01-19 10:00:00', 'O0030'),
('OS0141', 'Pending', '2025-01-20 09:00:00', 'O0031'),
('OS0142', 'Paid', '2025-01-20 10:00:00', 'O0031'),
('OS0143', 'Pending', '2025-01-21 09:00:00', 'O0032'),
('OS0144', 'Delivered', '2025-01-21 10:00:00', 'O0032'),
('OS0145', 'Pending', '2025-01-22 09:00:00', 'O0033'),
('OS0146', 'Paid', '2025-01-22 10:00:00', 'O0033'),
('OS0147', 'Pending', '2025-01-23 09:00:00', 'O0034'),
('OS0148', 'Packing', '2025-01-23 10:00:00', 'O0034'),
('OS0149', 'Pending', '2025-01-24 09:00:00', 'O0035'),
('OS0150', 'Cancelled', '2025-01-24 10:00:00', 'O0035'),
('OS0151', 'Pending', '2025-01-25 09:00:00', 'O0036'),
('OS0152', 'Paid', '2025-01-25 10:00:00', 'O0036'),
('OS0153', 'Packing', '2025-01-25 11:00:00', 'O0036'),
('OS0154', 'Pending', '2025-01-26 09:00:00', 'O0037'),
('OS0155', 'Paid', '2025-01-26 10:00:00', 'O0037'),
('OS0156', 'Pending', '2025-01-27 09:00:00', 'O0038'),
('OS0157', 'Delivered', '2025-01-27 10:00:00', 'O0038'),
('OS0158', 'Pending', '2025-01-28 09:00:00', 'O0039'),
('OS0159', 'Paid', '2025-01-28 10:00:00', 'O0039'),
('OS0160', 'Pending', '2025-01-29 09:00:00', 'O0040'),
('OS0161', 'Delivered', '2025-01-29 10:00:00', 'O0040'),
('OS0162', 'Pending', '2025-01-30 09:00:00', 'O0041'),
('OS0163', 'Out for Delivery', '2025-01-30 10:00:00', 'O0041'),
('OS0164', 'Completed', '2025-12-23 17:29:05', 'O0022'),
('OS0165', 'Cancel Requested', '2025-12-24 00:10:30', 'O0021'),
('OS0166', 'Cancelled', '2025-12-24 00:13:28', 'O0003'),
('OS0167', 'Refunded', '2025-12-27 00:13:28', 'O0003'),
('OS0168', 'Cancelled', '2025-12-24 00:14:39', 'O0004'),
('OS0169', 'Refunded', '2025-12-27 00:14:39', 'O0004'),
('OS0170', 'Packing', '2025-12-24 00:15:05', 'O0029'),
('OS0171', 'Packing', '2025-12-24 00:16:19', 'O0025'),
('OS0172', 'Packing', '2025-12-24 00:16:19', 'O0027'),
('OS0173', 'Delivered', '2025-12-24 11:00:13', 'O0026'),
('OS0174', 'Out for Delivery', '2025-12-24 11:00:27', 'O0029'),
('OS0175', 'Delivered', '2025-12-24 11:00:45', 'O0029'),
('OS0176', 'Cancel Requested', '2025-12-24 11:01:45', 'O0027'),
('OS0177', 'Completed', '2025-12-24 11:04:00', 'O0040'),
('OS0178', 'Cancelled', '2025-12-24 11:08:45', 'O0027'),
('OS0179', 'Refunded', '2025-12-27 11:08:45', 'O0027'),
('OS0180', 'Pending', '2025-12-24 11:15:53', 'O0042'),
('OS0181', 'Paid', '2025-12-24 11:16:25', 'O0042'),
('OS0182', 'Packing', '2025-12-24 11:17:43', 'O0042'),
('OS0183', 'Out for Delivery', '2025-12-24 11:17:47', 'O0042'),
('OS0184', 'Delivered', '2025-12-24 11:17:53', 'O0042'),
('OS0185', 'Completed', '2025-12-24 11:19:05', 'O0042');

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
('O0001', 288, 3, 10, 0, 2, 'CU0001', 'AD0001'),
('O0002', 178, 2, 5, 0, 1.5, 'CU0002', 'AD0001'),
('O0003', 799, 1, 20, 0, 5, 'CU0003', 'AD0001'),
('O0004', 49, 1, 49, 0, 1, 'CU0004', 'AD0001'),
('O0005', 159, 1, 10, 0, 1.5, 'CU0005', 'AD0001'),
('O0006', 320, 2, 15, 0, 3, 'CU0002', 'AD0001'),
('O0007', 450, 3, 20, 0, 5, 'CU0003', 'AD0002'),
('O0008', 120, 1, 5, 0, 2, 'CU0001', 'AD0001'),
('O0009', 680, 4, 30, 0, 6, 'CU0005', 'AD0002'),
('O0010', 250, 2, 12, 0, 3, 'CU0006', 'AD0001'),
('O0011', 180, 1, 8, 0, 2, 'CU0004', 'AD0001'),
('O0012', 540, 3, 25, 0, 6, 'CU0002', 'AD0002'),
('O0013', 295, 2, 14, 0, 3, 'CU0001', 'AD0001'),
('O0014', 760, 5, 35, 0, 8, 'CU0006', 'AD0002'),
('O0015', 410, 3, 18, 0, 4, 'CU0003', 'AD0001'),
('O0016', 150, 1, 6, 0, 2, 'CU0005', 'AD0001'),
('O0017', 620, 4, 28, 0, 7, 'CU0004', 'AD0002'),
('O0018', 275, 2, 13, 0, 3, 'CU0002', 'AD0001'),
('O0019', 890, 6, 40, 0, 9, 'CU0006', 'AD0002'),
('O0020', 330, 2, 330, 0, 4, 'CU0001', 'AD0001'),
('O0021', 56.94, 1, 56, 0, 2.94, 'CU0007', 'AD0001'),
('O0022', 125.5, 3, 125, 5, 5.5, 'CU0007', 'AD0002'),
('O0023', 78, 2, 7, 0, 3.5, 'CU0002', 'AD0001'),
('O0024', 189.9, 4, 18, 10, 6.9, 'CU0005', 'AD0003'),
('O0025', 55.5, 1, 5, 0, 2.5, 'CU0010', 'AD0001'),
('O0026', 102, 3, 10, 2, 4, 'CU0001', 'AD0002'),
('O0027', 149.75, 4, 14, 7, 5.75, 'CU0008', 'AD0003'),
('O0028', 64, 2, 6, 0, 3, 'CU0003', 'AD0002'),
('O0029', 88.9, 2, 8, 2, 4.9, 'CU0006', 'AD0001'),
('O0030', 175, 5, 17, 8, 7, 'CU0004', 'AD0003'),
('O0031', 92.5, 3, 9, 3, 4.5, 'CU0009', 'AD0002'),
('O0032', 130.75, 3, 13, 6, 5.75, 'CU0001', 'AD0001'),
('O0033', 47, 1, 4, 0, 2, 'CU0002', 'AD0002'),
('O0034', 115, 3, 11, 5, 5, 'CU0005', 'AD0003'),
('O0035', 67.5, 2, 6, 0, 3.5, 'CU0003', 'AD0001'),
('O0036', 154.2, 4, 15, 7, 6.2, 'CU0007', 'AD0002'),
('O0037', 80, 2, 8, 2, 4, 'CU0011', 'AD0003'),
('O0038', 140.5, 4, 14, 5, 6.5, 'CU0006', 'AD0001'),
('O0039', 98.9, 3, 9, 3, 4.9, 'CU0004', 'AD0002'),
('O0040', 60, 2, 60, 0, 3, 'CU0008', 'AD0003'),
('O0041', 170.75, 5, 17, 8, 7.75, 'CU0005', 'AD0002'),
('O0042', 68.812, 1, 68, 0, 3.612, 'CU0008', 'AD0009');

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
('O0001', 'PV0001', 199, 1),
('O0001', 'PV0003', 89, 2),
('O0002', 'PV0002', 89, 2),
('O0003', 'PV0003', 799, 1),
('O0004', 'PV0003', 120, 1),
('O0004', 'PV0007', 80, 2),
('O0005', 'PV0010', 150, 1),
('O0006', 'PV0005', 160, 1),
('O0006', 'PV0012', 160, 1),
('O0007', 'PV0008', 150, 1),
('O0007', 'PV0015', 200, 1),
('O0007', 'PV0020', 100, 1),
('O0008', 'PV0002', 120, 1),
('O0009', 'PV0018', 200, 2),
('O0009', 'PV0022', 140, 1),
('O0009', 'PV0025', 200, 1),
('O0010', 'PV0009', 125, 2),
('O0011', 'PV0003', 180, 1),
('O0012', 'PV0005', 200, 1),
('O0012', 'PV0012', 170, 1),
('O0012', 'PV0020', 170, 1),
('O0013', 'PV0007', 150, 1),
('O0013', 'PV0010', 145, 1),
('O0014', 'PV0002', 160, 2),
('O0014', 'PV0015', 140, 1),
('O0014', 'PV0018', 160, 2),
('O0015', 'PV0009', 140, 1),
('O0015', 'PV0016', 135, 2),
('O0016', 'PV0004', 150, 1),
('O0017', 'PV0011', 155, 2),
('O0017', 'PV0023', 155, 2),
('O0018', 'PV0006', 140, 1),
('O0018', 'PV0019', 135, 1),
('O0019', 'PV0001', 150, 3),
('O0019', 'PV0014', 145, 3),
('O0020', 'PV0008', 165, 2),
('O0021', 'PV0007', 49, 1),
('O0022', 'PV0026', 45.5, 1),
('O0022', 'PV0030', 40, 2),
('O0023', 'PV0028', 35, 1),
('O0023', 'PV0042', 43, 1),
('O0024', 'PV0034', 50, 1),
('O0024', 'PV0041', 45, 1),
('O0024', 'PV0050', 44.9, 2),
('O0025', 'PV0027', 55.5, 1),
('O0026', 'PV0031', 34, 1),
('O0026', 'PV0044', 34, 2),
('O0026', 'PV0052', 34, 1),
('O0027', 'PV0029', 50, 1),
('O0027', 'PV0033', 49.75, 3),
('O0028', 'PV0035', 32, 2),
('O0029', 'PV0040', 44.45, 1),
('O0029', 'PV0045', 44.45, 1),
('O0030', 'PV0026', 35, 2),
('O0030', 'PV0030', 35, 1),
('O0030', 'PV0045', 35, 2),
('O0031', 'PV0029', 30.5, 1),
('O0031', 'PV0035', 31, 2),
('O0032', 'PV0032', 43.5, 1),
('O0032', 'PV0040', 43.25, 2),
('O0033', 'PV0027', 47, 1),
('O0034', 'PV0041', 38, 2),
('O0034', 'PV0045', 39, 1),
('O0035', 'PV0026', 33.75, 1),
('O0035', 'PV0050', 33.75, 1),
('O0036', 'PV0028', 38.5, 2),
('O0036', 'PV0034', 38.6, 2),
('O0037', 'PV0033', 40, 2),
('O0038', 'PV0044', 35, 1),
('O0038', 'PV0051', 35.5, 3),
('O0039', 'PV0029', 32.9, 1),
('O0039', 'PV0035', 33, 2),
('O0040', 'PV0042', 30, 2),
('O0041', 'PV0026', 34.25, 2),
('O0041', 'PV0040', 34.25, 3),
('O0042', 'PV0047', 60.2, 1);

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
('PM0001', 'O0001', 'TNG', 'Paid', 199, '2025-12-23 09:07:49', NULL),
('PM0002', 'O0002', 'Credit Card', 'Pending', 178, '2025-11-27 16:53:47', NULL),
('PM0003', 'O0003', 'TNG', 'Paid', 799, '2025-12-23 15:59:24', NULL),
('PM0004', 'O0004', 'TNG', 'Refunded', 49, '2025-12-23 15:59:27', NULL),
('PM0005', 'O0005', 'Credit Card', 'Paid', 159, '2025-11-27 16:53:47', NULL),
('PM0006', 'O0006', 'TNG', 'Paid', 320, '2025-12-23 15:59:30', NULL),
('PM0007', 'O0007', 'Credit Card', 'Paid', 450, '2025-11-28 03:41:00', NULL),
('PM0008', 'O0008', 'TNG', 'Paid', 120, '2025-12-23 15:59:18', NULL),
('PM0009', 'O0009', 'TNG', 'Paid', 680, '2025-12-23 15:59:13', '2025-11-29 07:30:00'),
('PM0010', 'O0010', 'Credit Card', 'Paid', 250, '2025-11-30 08:46:00', NULL),
('PM0011', 'O0016', 'TNG', 'Paid', 150, '2025-12-23 15:59:09', NULL),
('PM0012', 'O0017', 'TNG', 'Paid', 620, '2025-12-23 15:59:02', NULL),
('PM0013', 'O0018', 'Credit Card', 'Paid', 275, '2025-11-28 08:00:00', NULL),
('PM0014', 'O0019', 'TNG', 'Paid', 890, '2025-12-23 15:58:59', NULL),
('PM0015', 'O0020', 'TNG', 'Paid', 330, '2025-12-23 15:58:54', NULL),
('PM0016', 'O0011', 'TNG', 'Paid', 320, '2025-12-23 15:58:51', NULL),
('PM0017', 'O0012', 'Credit Card', 'Paid', 450, '2025-12-21 16:43:04', NULL),
('PM0018', 'O0013', 'TNG', 'Paid', 120, '2025-12-23 15:58:48', NULL),
('PM0019', 'O0014', 'TNG', 'Paid', 680, '2025-12-23 15:58:45', NULL),
('PM0020', 'O0015', 'Credit Card', 'Paid', 250, '2025-12-21 16:43:15', NULL),
('PM0021', 'O0021', 'Credit Card', 'Paid', 56.94, '2025-12-22 03:59:02', '2025-12-22 03:59:02'),
('PM0022', 'O0022', 'Credit Card', 'Paid', 125.5, '2025-01-10 01:20:00', '2025-01-10 01:20:00'),
('PM0023', 'O0023', 'TNG', 'Paid', 78, '2025-01-12 02:10:00', '2025-01-12 02:10:00'),
('PM0024', 'O0024', 'Credit Card', 'Cancelled', 189.9, '2025-01-13 01:00:00', '2025-01-13 01:00:00'),
('PM0025', 'O0025', 'TNG', 'Paid', 55.5, '2025-01-14 01:30:00', '2025-01-14 01:30:00'),
('PM0026', 'O0026', 'Credit Card', 'Paid', 102, '2025-01-15 02:00:00', '2025-01-15 02:00:00'),
('PM0027', 'O0027', 'TNG', 'Paid', 149.75, '2025-01-16 03:00:00', '2025-01-16 03:00:00'),
('PM0028', 'O0028', 'Credit Card', 'Delivered', 64, '2025-01-17 02:00:00', '2025-01-17 02:00:00'),
('PM0029', 'O0029', 'TNG', 'Paid', 88.9, '2025-01-18 02:00:00', '2025-01-18 02:00:00'),
('PM0030', 'O0030', 'Credit Card', 'Out for Delivery', 175, '2025-01-19 02:00:00', '2025-01-19 02:00:00'),
('PM0031', 'O0031', 'TNG', 'Paid', 92.5, '2025-01-20 02:00:00', '2025-01-20 02:00:00'),
('PM0032', 'O0032', 'Credit Card', 'Delivered', 130.75, '2025-01-21 02:00:00', '2025-01-21 02:00:00'),
('PM0033', 'O0033', 'TNG', 'Paid', 47, '2025-01-22 02:00:00', '2025-01-22 02:00:00'),
('PM0034', 'O0034', 'Credit Card', 'Packing', 115, '2025-01-23 02:00:00', '2025-01-23 02:00:00'),
('PM0035', 'O0035', 'TNG', 'Cancelled', 67.5, '2025-01-24 02:00:00', '2025-01-24 02:00:00'),
('PM0036', 'O0036', 'Credit Card', 'Paid', 154.2, '2025-01-25 02:00:00', '2025-01-25 02:00:00'),
('PM0037', 'O0037', 'TNG', 'Paid', 80, '2025-01-26 02:00:00', '2025-01-26 02:00:00'),
('PM0038', 'O0038', 'Credit Card', 'Delivered', 140.5, '2025-01-27 02:00:00', '2025-01-27 02:00:00'),
('PM0039', 'O0039', 'TNG', 'Paid', 98.9, '2025-01-28 02:00:00', '2025-01-28 02:00:00'),
('PM0040', 'O0040', 'Credit Card', 'Delivered', 60, '2025-01-29 02:00:00', '2025-01-29 02:00:00'),
('PM0041', 'O0041', 'TNG', 'Out for Delivery', 170.75, '2025-01-30 02:00:00', '2025-01-30 02:00:00'),
('PM0042', 'O0042', 'Credit Card', 'Paid', 68.812, '2025-12-24 03:16:25', '2025-12-24 03:16:25');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `product_name` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_sold` int DEFAULT NULL,
  `cost_price` double NOT NULL,
  `rate` double DEFAULT NULL,
  `img_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `sale_price` double NOT NULL,
  `category_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `youtube_link` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `product_name`, `description`, `created_at`, `updated_at`, `total_sold`, `cost_price`, `rate`, `img_url`, `sale_price`, `category_id`, `is_deleted`, `youtube_link`) VALUES
('PR0001', 'Butterfly Bracelet', 'A stylish accessory perfect for everyday wear.', '2025-12-24 01:40:37', '2025-11-27 08:53:47', 120, 150, 4.5, 'PV0001.jpg,PV0002.jpg\n', 199, 'CA0001', 0, NULL),
('PR0002', 'Diamond Bracelet', 'Designed to add a touch of elegance to your look.', '2025-12-24 01:40:37', '2025-11-27 08:53:47', 80, 60, 4.2, 'PR0002_img1.jpeg, PR0002_img2.jpeg, PR0002_img3.jpeg', 89, 'CA0001', 0, NULL),
('PR0003', 'Fairy Bracelet', 'Lightweight, durable, and comfortable to use.', '2025-12-23 05:48:01', '2025-11-27 08:53:47', 40, 50, 4.9, 'PV0005.jpg, PV0006.jpg\n', 79, 'CA0001', 0, NULL),
('PR0004', 'Lotus Bracelet', 'A timeless piece that matches any outfit.', '2025-12-23 05:48:07', '2025-11-27 08:53:47', 150, 30, 4.3, 'PV0007.jpg, PV0008.jpg\n', 49, 'CA0001', 0, NULL),
('PR0005', 'Winter Jasmine', 'Crafted with quality materials for long-lasting use.', '2025-12-23 05:48:13', '2025-11-27 08:53:47', 60, 120, 4.6, 'PV0009.jpg, PV0010.jpg\n', 159, 'CA0001', 0, NULL),
('PR0006', 'Jeulia Ring', 'A trendy design made for modern fashion lovers.', '2025-12-23 05:48:18', '2025-11-29 08:52:52', 0, 40, 5, 'PV0011.jpg, PV0012.jpg\n', 70, 'CA0002', 0, NULL),
('PR0007', 'Love Ring', 'Adds a subtle sparkle to elevate your style.', '2025-12-14 17:49:11', '2025-11-29 08:52:52', 0, 30, 5, 'PR0007_img1.jpeg, PR0007_img2.jpge, PR0007_img3.jpeg', 60, 'CA0002', 0, NULL),
('PR0008', 'Lyra Ring', 'Simple yet eye-catching for any occasion.', '2025-12-23 05:48:26', '2025-11-29 08:52:52', 0, 50, 5, 'PV0015.jpg, PV0016.jpg\n', 90, 'CA0002', 0, NULL),
('PR0009', 'Manilla Ring', 'A must-have accessory for your collection.', '2025-12-23 05:48:36', '2025-11-29 08:52:52', 0, 20, 5, 'PV0017.jpg, PV0018.jpg\n', 50, 'CA0002', 0, NULL),
('PR0010', 'Soulmate Ring', 'Made to complement both casual and formal outfits.', '2025-12-23 05:48:41', '2025-11-29 08:52:52', 0, 20.5, 5, 'PV0019.jpg, PV0020.jpg\n', 30, 'CA0002', 0, NULL),
('PR0011', 'Clover Necklace', 'Beautifully crafted with attention to detail.', '2025-12-23 05:48:45', '2025-11-29 08:52:52', 0, 40.5, 5, 'PV0021.jpg, PV0022.jpg\n', 70.5, 'CA0003', 0, NULL),
('PR0012', 'Diamond Necklace', 'A perfect gift for friends or loved ones.', '2025-12-14 17:26:56', '2025-11-29 08:52:52', 0, 30.5, 5, 'PR0012_img1.jpeg, PR0012_img2.jpeg, PR0012_img3.jpeg', 60.5, 'CA0003', 0, NULL),
('PR0013', 'Eutosma Necklace', 'Minimalist design that blends with any style.', '2025-12-23 05:48:50', '2025-11-29 08:52:52', 0, 25.5, 5, 'PV0025.jpg, PV0026.jpg\n', 39.5, 'CA0003', 0, NULL),
('PR0014', 'Pear Necklace', 'Features a smooth finish for a comfortable fit.', '2025-12-23 05:48:54', '2025-11-29 08:52:52', 0, 29.2, 5, 'PV0027.jpg, PV0028.jpg\n', 35.5, 'CA0003', 0, NULL),
('PR0015', 'Ribbon Necklace', 'Easy to match with other accessories.', '2025-12-23 05:48:58', '2025-11-29 08:52:52', 0, 21.8, 5, 'PV0029.jpg, PV0030.jpg\n', 38.5, 'CA0003', 0, NULL),
('PR0016', 'Butterfly Dreams Earring', 'A stylish choice for daily wear.', '2025-12-23 05:49:02', '2025-11-29 08:52:52', 0, 40, 5, 'PV0031.jpg, PV0032.jpg\n', 70, 'CA0004', 0, NULL),
('PR0017', 'Calla Ribbon Earring', 'Made to stand out without being too flashy.', '2025-12-14 17:52:47', '2025-11-29 08:52:52', 0, 30, 5, 'PV0033.jpg', 60, 'CA0004', 0, NULL),
('PR0018', 'Hane Pearl Earring', 'Enhances your natural beauty effortlessly.', '2025-12-23 05:49:07', '2025-11-29 08:52:52', 0, 55, 5, 'PV0035.jpg, PV0036.jpg\n', 90, 'CA0004', 0, NULL),
('PR0019', 'Pear Earring', 'Carefully crafted to ensure premium quality.', '2025-12-14 17:29:29', '2025-11-29 08:52:52', 0, 25, 5, 'PR0019_img1.jpeg, PR0019_img2.jepg, PR0019_img3.jpeg', 55, 'CA0004', 0, NULL),
('PR0020', 'Rose Rommance Earring', 'Adds charm and personality to your outfit.', '2025-12-23 05:49:12', '2025-11-29 08:52:52', 0, 15.5, 5, 'PV0039.jpg, PV0040.jpg\n', 35, 'CA0004', 0, NULL),
('PR0021', 'Autumn Hairclaw', 'Designed to stay secure and comfortable.', '2025-12-24 03:04:00', '2025-11-29 08:52:52', 2, 46.2, 5, 'PV0041.jpg, PV0042.jpg,  PV0043.jpg\n \n', 70.2, 'CA0005', 0, NULL),
('PR0022', 'Minimalist Hairclaw', 'A classic look that never goes out of style.', '2025-12-14 17:35:23', '2025-11-29 08:52:52', 0, 53.2, 5, 'PV0044.jpg, PV0045.jpg, PV0046.jpg', 65.2, 'CA0005', 0, NULL),
('PR0023', 'Ribbon Hairclaw', 'Perfect for adding a touch of sophistication.', '2025-12-24 03:19:05', '2025-11-29 08:52:52', 1, 50.2, 5, 'PR0023_img1.jpeg, PR0023_img2.jpeg, PR0023_img3.jpeg', 60.2, 'CA0005', 0, 'https://www.youtube.com/watch?v=TrRduvhBNBo'),
('PR0024', 'Summer Hair Claw', 'Trendy piece that keeps you stylish all day.', '2025-12-14 17:35:23', '2025-11-29 08:52:52', 0, 25.2, 5, 'PV0050.jpg, PV0051.jpg, PV0052.jpg', 55.2, 'CA0005', 0, NULL),
('PR0025', 'Tulip Hairclaw', 'A simple accessory that makes a big difference.', '2025-12-14 17:35:23', '2025-11-29 08:52:52', 0, 15.6, 5, 'PV0053.jpg, PV0054.jpg, PV0055.jpg', 35.2, 'CA0005', 0, NULL),
('PR0026', 'testingeee', '11112222', '2025-12-24 01:40:37', '2025-12-24 01:38:03', NULL, 43, NULL, 'PR0026_img1.png', 45, 'CA0004', 0, NULL),
('PR0027', '2', 'eee', '2025-12-24 01:40:37', '2025-12-24 01:40:08', NULL, 2, NULL, 'PR0027_img1.png,PR0027_img2.png', 3, 'CA0003', 0, NULL);

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
  `img_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variant`
--

INSERT INTO `product_variant` (`product_variant_id`, `min_stock_level`, `stock_qty`, `stock_status`, `variant_id`, `product_id`, `img_url`, `is_deleted`) VALUES
('PV0001', 5, 19, 'In Stock', 'VR0001', 'PR0001', 'PV0001.jpg', 0),
('PV0002', 5, 15, 'In Stock', 'VR0002', 'PR0001', 'PV0002.jpg', 0),
('PV0003', 5, 10, 'In Stock', 'VR0006', 'PR0002', 'PV0003.jpg', 0),
('PV0004', 5, 10, 'In Stock', 'VR0007', 'PR0002', 'PV0004.jpg', 0),
('PV0005', 5, 25, 'In Stock', 'VR0010', 'PR0003', 'PV0005.jpg', 0),
('PV0006', 5, 12, 'Low Stock', 'VR0018', 'PR0003', 'PV0006.jpg', 0),
('PV0007', 5, 18, 'In Stock', 'VR0006', 'PR0004', 'PV0007.jpg', 0),
('PV0008', 5, 7, 'In Stock', 'VR0002', 'PR0004', 'PV0008.jpg', 0),
('PV0009', 5, 30, 'In Stock', 'VR0001', 'PR0005', 'PV0009.jpg', 0),
('PV0010', 5, 5, 'Low Stock', 'VR0002', 'PR0005', 'PV0010.jpg', 0),
('PV0011', 5, 30, 'In Stock', 'VR0002', 'PR0006', 'PV0011.jpg', 0),
('PV0012', 5, 5, 'In Stock', 'VR0001', 'PR0006', 'PV0012.jpg', 0),
('PV0013', 5, 30, 'In Stock', 'VR0001', 'PR0007', 'PV0013.jpg', 0),
('PV0014', 5, 5, 'In Stock', 'VR0002', 'PR0007', 'PV0014.jpg', 0),
('PV0015', 5, 30, 'In Stock', 'VR0001', 'PR0008', 'PV0015.jpg', 0),
('PV0016', 5, 5, 'Low Stock', 'VR0002', 'PR0008', 'PV0016.jpg', 0),
('PV0017', 5, 29, 'In Stock', 'VR0017', 'PR0009', 'PV0017.jpg', 0),
('PV0018', 5, 5, 'In Stock', 'VR0001', 'PR0009', 'PV0018.jpg', 0),
('PV0019', 5, 30, 'In Stock', 'VR0001', 'PR0010', 'PV0019.jpg', 0),
('PV0020', 5, 5, 'In Stock', 'VR0002', 'PR0010', 'PV0020.jpg', 0),
('PV0021', 5, 30, 'In Stock', 'VR0001', 'PR0011', 'PV0021.jpg', 0),
('PV0022', 5, 5, 'In Stock', 'VR0002', 'PR0011', 'PV0022.jpg', 0),
('PV0023', 5, 30, 'In Stock', 'VR0001', 'PR0012', 'PV0023.jpg', 0),
('PV0024', 5, 5, 'In Stock', 'VR0002', 'PR0012', 'PV0024.jpg', 0),
('PV0025', 5, 30, 'In Stock', 'VR0001', 'PR0013', 'PV0025.jpg', 0),
('PV0026', 5, 5, 'Low Stock', 'VR0002', 'PR0013', 'PV0026.jpg', 0),
('PV0027', 5, 30, 'In Stock', 'VR0015', 'PR0014', 'PV0027.jpg', 0),
('PV0028', 5, 5, 'Low Stock', 'VR0016', 'PR0014', 'PV0028.jpg', 0),
('PV0029', 5, 30, 'In Stock', 'VR0020', 'PR0015', 'PV0029.jpg', 0),
('PV0030', 5, 5, 'In Stock', 'VR0002', 'PR0015', 'PV0030.jpg', 0),
('PV0031', 5, 30, 'In Stock', 'VR0001', 'PR0016', 'PV0031.jpg', 0),
('PV0032', 5, 5, 'Low Stock', 'VR0002', 'PR0016', 'PV0032.jpg', 0),
('PV0033', 5, 30, 'In Stock', 'VR0001', 'PR0017', 'PV0033.jpg', 0),
('PV0034', 5, 5, 'Low Stock', 'VR0002', 'PR0017', 'PV0034.jpg', 0),
('PV0035', 5, 30, 'In Stock', 'VR0001', 'PR0018', 'PV0035.jpg', 0),
('PV0036', 5, 5, 'In Stock', 'VR0002', 'PR0018', 'PV0036.jpg', 0),
('PV0037', 5, 30, 'In Stock', 'VR0003', 'PR0019', 'PV0037.jpg', 0),
('PV0038', 5, 5, 'In Stock', 'VR0004', 'PR0019', 'PV0038.jpg', 0),
('PV0039', 5, 30, 'In Stock', 'VR0001', 'PR0020', 'PV0039.jpg', 0),
('PV0040', 5, 5, 'In Stock', 'VR0002', 'PR0020', 'PV0040.jpg', 0),
('PV0041', 5, 30, 'In Stock', 'VR0006', 'PR0021', 'PV0041.jpg', 0),
('PV0042', 5, 5, 'In Stock', 'VR0009', 'PR0021', 'PV0042.jpg', 0),
('PV0043', 5, 5, 'In Stock', 'VR0007', 'PR0021', 'PV0043.jpg', 0),
('PV0044', 5, 30, 'In Stock', 'VR0008', 'PR0022', 'PV0044.jpg', 0),
('PV0045', 5, 5, 'In Stock', 'VR0007', 'PR0022', 'PV0045.jpg', 0),
('PV0046', 5, 5, 'In Stock', 'VR0019', 'PR0022', 'PV0046.jpg', 0),
('PV0047', 5, 29, 'In Stock', 'VR0006', 'PR0023', 'PV0047.jpg', 0),
('PV0048', 5, 5, 'In Stock', 'VR0007', 'PR0023', 'PV0048.jpg', 0),
('PV0049', 5, 0, 'In Stock', 'VR0019', 'PR0023', 'PV0049.jpg', 0),
('PV0050', 5, 30, 'In Stock', 'VR0012', 'PR0024', 'PV0050.jpg', 0),
('PV0051', 5, 5, 'Low Stock', 'VR0007', 'PR0024', 'PV0051.jpg', 0),
('PV0052', 5, 5, 'In Stock', 'VR0013', 'PR0024', 'PV0052.jpg', 0),
('PV0053', 5, 30, 'In Stock', 'VR0006', 'PR0025', 'PV0053.jpg', 0),
('PV0054', 5, 5, 'In Stock', 'VR0007', 'PR0025', 'PV0054.jpg', 0),
('PV0055', 5, 5, 'In Stock', 'VR0014', 'PR0025', 'PV0055.jpg', 0),
('PV0056', 3, 3, 'Low Stock', 'VR0010', 'PR0026', 'PV0056.png', 0),
('PV0057', 3, 3, 'Low Stock', 'VR0012', 'PR0026', 'PV0057.png', 0),
('PV0058', 2, 56, 'In Stock', 'VR0017', 'PR0027', 'PV0058.png', 0);

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

--
-- Dumping data for table `review`
--

INSERT INTO `review` (`review_id`, `description`, `rating`, `createdAt`, `img_url`, `product_variant_id`, `order_id`) VALUES
('R0001', 'abc', 4, '2025-12-24 11:04:42', '694b584a92942_product-qrcode.png', 'PV0042', 'O0040'),
('R0002', '', 4, '2025-12-24 11:19:22', '694b5bba3a65b_product-qrcode.png', 'PV0047', 'O0042');

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

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` (`shipment_id`, `order_id`, `receiver_name`, `receiver_phone`, `receiver_address`, `status`, `created_datetime`, `updated_datetime`) VALUES
('SH0001', 'O0042', 'Tan Kok Hong', '0176265778', 'afafsdfafa, 52000 kuala lumpur, selangor', 'Delivered', '2025-12-24 03:17:53', '2025-12-24 03:17:53');

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

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`wishlist_id`, `customer_id`) VALUES
('WL0001', 'CU0001'),
('WL0002', 'CU0008'),
('WL0003', 'CU0011');

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
-- Dumping data for table `wishlist_items`
--

INSERT INTO `wishlist_items` (`wishlist_item_id`, `wishlist_id`, `product_variant_id`) VALUES
('WI0001', 'WL0002', 'PV0001'),
('WI0003', 'WL0003', 'PV0001');

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
