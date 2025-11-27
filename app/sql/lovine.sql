-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 27, 2025 at 05:56 PM
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
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `firstName` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `lastName` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `position` varchar(20) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `email`, `firstName`, `lastName`, `password`, `phone`, `address`, `created_at`, `position`) VALUES
('AD001', 'admin1@shop.com', 'Alice', 'Tan', 'admin123', '0121112222', 'KL', '2025-11-27 16:53:47', 'Manager'),
('AD002', 'admin2@shop.com', 'Brian', 'Lim', 'admin123', '0123334444', 'Penang', '2025-11-27 16:53:47', 'Staff'),
('AD003', 'admin3@shop.com', 'Cindy', 'Lee', 'admin123', '0129998888', 'Selangor', '2025-11-27 16:53:47', 'Supervisor'),
('AD004', 'admin4@shop.com', 'Danny', 'Wong', 'admin123', '0135556666', 'Johor', '2025-11-27 16:53:47', 'Staff'),
('AD005', 'admin5@shop.com', 'Eva', 'Tan', 'admin123', '0178889999', 'KL', '2025-11-27 16:53:47', 'Manager');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `created_datetime` timestamp NOT NULL,
  `updated_datetime` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`cart_id`, `customer_id`, `created_datetime`, `updated_datetime`) VALUES
('CA0001', 'CU0001', '2025-11-27 16:53:47', NULL),
('CA0002', 'CU0002', '2025-11-27 16:53:47', NULL),
('CA0003', 'CU0003', '2025-11-27 16:53:47', NULL),
('CA0004', 'CU0004', '2025-11-27 16:53:47', NULL),
('CA0005', 'CU0005', '2025-11-27 16:53:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `cart_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `product_variant_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `quantity` int NOT NULL,
  `cart_status` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `cart_id`, `product_variant_id`, `quantity`, `cart_status`) VALUES
('CI0001', 'CA0001', 'PV0001', 1, 1),
('CI0002', 'CA0002', 'PV0002', 2, 1),
('CI0003', 'CA0003', 'PV0003', 1, 1),
('CI0004', 'CA0004', 'PV0004', 1, 0),
('CI0005', 'CA0005', 'PV0005', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `category_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
('C00001', 'Rings'),
('C00002', 'Necklaces'),
('C00003', 'Bracelets'),
('C00004', 'Earrings'),
('C00005', 'Watches');

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `firstname` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `lastname` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `rewardPoint` int DEFAULT NULL,
  `isActive` tinyint(1) NOT NULL,
  `isBlocked` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`customer_id`, `firstname`, `lastname`, `email`, `password`, `phone`, `address`, `created_at`, `updated_at`, `rewardPoint`, `isActive`, `isBlocked`) VALUES
('CU0001', 'Adam', 'Lim', 'adam@gmail.com', 'pass123', '0123456789', 'KL', '2025-11-27 16:53:47', NULL, 100, 1, 0),
('CU0002', 'Bella', 'Tan', 'bella@gmail.com', 'pass123', '0112223333', 'Penang', '2025-11-27 16:53:47', NULL, 50, 1, 0),
('CU0003', 'Chris', 'Lee', 'chris@gmail.com', 'pass123', '0198887777', 'Johor', '2025-11-27 16:53:47', NULL, 20, 1, 0),
('CU0004', 'Diana', ' Wong', 'diana@gmail.com', 'pass123', '0135558888', 'Sabah', '2025-11-27 16:53:47', NULL, 10, 1, 1),
('CU0005', 'Edwin', 'Kong', 'edwin@gmail.com', 'pass123', '0166677777', 'Sarawak', '2025-11-27 16:53:47', NULL, 0, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `ordertable`
--

CREATE TABLE `ordertable` (
  `order_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `total_amount` double NOT NULL,
  `order_status` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `created_datetime` timestamp NOT NULL,
  `total_order_qty` int NOT NULL,
  `reward` double DEFAULT NULL,
  `tax_fee` double NOT NULL,
  `customer_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ordertable`
--

INSERT INTO `ordertable` (`order_id`, `total_amount`, `order_status`, `created_datetime`, `total_order_qty`, `reward`, `tax_fee`, `customer_id`) VALUES
('O00001', 199, 'Completed', '2025-11-27 16:53:47', 1, 10, 2, 'CU0001'),
('O00002', 178, 'Pending', '2025-11-27 16:53:47', 2, 5, 1.5, 'CU0002'),
('O00003', 799, 'Completed', '2025-11-27 16:53:47', 1, 20, 5, 'CU0003'),
('O00004', 49, 'Cancelled', '2025-11-27 16:53:47', 1, NULL, 1, 'CU0004'),
('O00005', 159, 'Completed', '2025-11-27 16:53:47', 1, 10, 1.5, 'CU0005');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `product_variant_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `price` double NOT NULL,
  `order_qty` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_id`, `product_variant_id`, `price`, `order_qty`) VALUES
('O00001', 'PV0001', 199, 1),
('O00002', 'PV0002', 89, 2),
('O00003', 'PV0003', 799, 1),
('O00004', 'PV0004', 49, 1),
('O00005', 'PV0005', 159, 1);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `order_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `payment_status` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `amount` double NOT NULL,
  `created_datetime` timestamp NOT NULL,
  `updated_datetime` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `order_id`, `payment_method`, `payment_status`, `amount`, `created_datetime`, `updated_datetime`) VALUES
('PM001', 'O00001', 'Online Banking', 'Paid', 199, '2025-11-27 16:53:47', NULL),
('PM002', 'O00002', 'Credit Card', 'Pending', 178, '2025-11-27 16:53:47', NULL),
('PM003', 'O00003', 'Debit Card', 'Paid', 799, '2025-11-27 16:53:47', NULL),
('PM004', 'O00004', 'E-wallet', 'Refunded', 49, '2025-11-27 16:53:47', NULL),
('PM005', 'O00005', 'Credit Card', 'Paid', 159, '2025-11-27 16:53:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `photo`
--

CREATE TABLE `photo` (
  `photo_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `img_url` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `product_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `review_id` varchar(6) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_variant_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `photo`
--

INSERT INTO `photo` (`photo_id`, `img_url`, `product_id`, `review_id`, `product_variant_id`) VALUES
('PH001', 'img/p1.jpg', 'P00001', 'R00001', 'PV0001'),
('PH002', 'img/p2.jpg', 'P00002', 'R00002', 'PV0002'),
('PH003', 'img/p3.jpg', 'P00003', 'R00003', 'PV0003'),
('PH004', 'img/p4.jpg', 'P00004', 'R00004', 'PV0004'),
('PH005', 'img/p5.jpg', 'P00005', 'R00005', 'PV0005');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `product_name` varchar(25) COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NOT NULL,
  `total_sold` int DEFAULT NULL,
  `cost_price` double NOT NULL,
  `rate` double DEFAULT NULL,
  `img_url` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `sale_price` double NOT NULL,
  `category_id` varchar(20) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `product_name`, `description`, `created_at`, `updated_at`, `total_sold`, `cost_price`, `rate`, `img_url`, `sale_price`, `category_id`) VALUES
('P00001', 'Gold Ring', '24K gold ring', '2025-11-27 16:53:47', '2025-11-27 16:53:47', 120, 150, 4.5, 'img/ring1.jpg', 199, 'C00001'),
('P00002', 'Silver Necklace', 'Pure silver chain', '2025-11-27 16:53:47', '2025-11-27 16:53:47', 80, 60, 4.2, 'img/necklace1.jpg', 89, 'C00002'),
('P00003', 'Diamond Bracelet', 'High quality diamond bracelet', '2025-11-27 16:53:47', '2025-11-27 16:53:47', 40, 500, 4.9, 'img/bracelet1.jpg', 799, 'C00003'),
('P00004', 'Pearl Earrings', 'Elegant pearl earrings', '2025-11-27 16:53:47', '2025-11-27 16:53:47', 150, 30, 4.3, 'img/earring1.jpg', 49, 'C00004'),
('P00005', 'Classic Watch', 'Vintage leather strap watch', '2025-11-27 16:53:47', '2025-11-27 16:53:47', 60, 120, 4.6, 'img/watch1.jpg', 159, 'C00005');

-- --------------------------------------------------------

--
-- Table structure for table `product_variant`
--

CREATE TABLE `product_variant` (
  `product_variant_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `min_stock_level` int NOT NULL,
  `stock_qty` int NOT NULL,
  `stock_status` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `variant_id` varchar(6) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `product_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variant`
--

INSERT INTO `product_variant` (`product_variant_id`, `min_stock_level`, `stock_qty`, `stock_status`, `variant_id`, `product_id`) VALUES
('PV0001', 10, 50, 'In Stock', 'V00001', 'P00001'),
('PV0002', 5, 20, 'Low Stock', 'V00004', 'P00002'),
('PV0003', 3, 5, 'Low Stock', 'V00002', 'P00003'),
('PV0004', 8, 30, 'In Stock', 'V00003', 'P00004'),
('PV0005', 12, 100, 'In Stock', 'V00005', 'P00005');

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `review_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `rating` double NOT NULL,
  `created_at` timestamp NOT NULL,
  `order_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review`
--

INSERT INTO `review` (`review_id`, `description`, `rating`, `created_at`, `order_id`) VALUES
('R00001', 'Amazing quality!', 5, '2025-11-27 16:53:47', 'O00001'),
('R00002', 'Good but slow delivery', 4, '2025-11-27 16:53:47', 'O00002'),
('R00003', 'Excellent craftsmanship', 5, '2025-11-27 16:53:47', 'O00003'),
('R00004', 'Not as expected', 2.5, '2025-11-27 16:53:47', 'O00004'),
('R00005', 'Love it!', 4.8, '2025-11-27 16:53:47', 'O00005');

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `shipment_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `order_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `receiver_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `receiver_phone` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `receiver_address` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `shipment_date` date DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `created_datetime` timestamp NOT NULL,
  `updated_datetime` timestamp NULL DEFAULT NULL,
  `update_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` (`shipment_id`, `order_id`, `receiver_name`, `receiver_phone`, `receiver_address`, `shipment_date`, `status`, `created_datetime`, `updated_datetime`, `update_by`) VALUES
('SH001', 'O00001', 'Adam Lim', '0123456789', 'KL', '2025-11-28', 'Delivered', '2025-11-27 16:53:47', NULL, 'AD001'),
('SH002', 'O00002', 'Bella Tan', '0112223333', 'Penang', '2025-11-28', 'Processing', '2025-11-27 16:53:47', NULL, 'AD002'),
('SH003', 'O00003', 'Chris Lee', '0198887777', 'Johor', '2025-11-28', 'Shipped', '2025-11-27 16:53:47', NULL, 'AD003'),
('SH004', 'O00004', 'Diana Wong', '0135558888', 'Sabah', '2025-11-28', 'Cancelled', '2025-11-27 16:53:47', NULL, 'AD004'),
('SH005', 'O00005', 'Edwin Kong', '0166677777', 'Sarawak', '2025-11-28', 'Delivered', '2025-11-27 16:53:47', NULL, 'AD005');

-- --------------------------------------------------------

--
-- Table structure for table `variant`
--

CREATE TABLE `variant` (
  `variant_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `variant_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `variant`
--

INSERT INTO `variant` (`variant_id`, `variant_name`) VALUES
('V00001', 'Small'),
('V00002', 'Medium'),
('V00003', 'Large'),
('V00004', 'Color – Gold'),
('V00005', 'Color – Silver');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `wishlist_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`wishlist_id`, `customer_id`) VALUES
('WL001', 'CU0001'),
('WL002', 'CU0002'),
('WL003', 'CU0003'),
('WL004', 'CU0004'),
('WL005', 'CU0005');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist_items`
--

CREATE TABLE `wishlist_items` (
  `wishlist_item_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `wishlist_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL,
  `product_variant_id` varchar(6) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist_items`
--

INSERT INTO `wishlist_items` (`wishlist_item_id`, `wishlist_id`, `product_variant_id`) VALUES
('WI001', 'WL001', 'PV0001'),
('WI002', 'WL002', 'PV0002'),
('WI003', 'WL003', 'PV0003'),
('WI004', 'WL004', 'PV0004'),
('WI005', 'WL005', 'PV0005');

--
-- Indexes for dumped tables
--

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
-- Indexes for table `ordertable`
--
ALTER TABLE `ordertable`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

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
-- Indexes for table `photo`
--
ALTER TABLE `photo`
  ADD PRIMARY KEY (`photo_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `review_id` (`review_id`),
  ADD KEY `product_variant_id` (`product_variant_id`);

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
  ADD KEY `order_id` (`order_id`);

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
-- Constraints for table `ordertable`
--
ALTER TABLE `ordertable`
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
-- Constraints for table `photo`
--
ALTER TABLE `photo`
  ADD CONSTRAINT `photo_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`),
  ADD CONSTRAINT `photo_ibfk_2` FOREIGN KEY (`review_id`) REFERENCES `review` (`review_id`),
  ADD CONSTRAINT `photo_ibfk_3` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variant` (`product_variant_id`);

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
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `ordertable` (`order_id`);

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
