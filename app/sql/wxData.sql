-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 18, 2025 at 05:39 AM
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

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
('CA0001', 'Bracelet'),
('CA0002', 'Ring'),
('CA0003', 'Necklace'),
('CA0004', 'Earring'),
('CA0005', 'Hairclaw');

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
