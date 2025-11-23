-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 23, 2025 at 01:42 AM
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
-- Database: `lee_sneakers`
--

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `created_at`) VALUES
(1, 'Nike', '2025-11-21 05:20:18'),
(2, 'Adidas', '2025-11-21 05:20:18'),
(3, 'Puma', '2025-11-21 05:20:18'),
(4, 'New Balance', '2025-11-21 05:20:18'),
(5, 'Asics', '2025-11-21 05:20:18'),
(7, 'Vans', '2025-11-21 05:20:18'),
(8, 'Hoka', '2025-11-21 05:20:18'),
(10, 'Under Armour', '2025-11-21 05:20:18'),
(11, 'Onitsuka Tiger', '2025-11-21 05:20:18');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) NOT NULL,
  `size` varchar(20) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','processing','completed','cancelled') DEFAULT 'pending',
  `shipping_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `gcash_screenshot` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `shipping_address`, `payment_method`, `gcash_screenshot`, `created_at`, `updated_at`) VALUES
(6, 2, 1900.00, 'cancelled', '{\"full_name\":\"fitz\",\"email\":\"kristiamaeorbe@gmail.com\",\"phone\":\"09928716021\",\"street\":\"1583\",\"barangay\":\"Lapu Lapu\",\"city\":\"Cebu\",\"province\":\"Visayas\",\"postal_code\":\"1234\"}', 'GCash', 'gcash_2_order_691d9d504124a_1763548496.png', '2025-11-19 10:34:56', '2025-11-22 08:32:27'),
(7, 2, 1800.00, 'cancelled', '{\"full_name\":\"fitz\",\"email\":\"kristiamaeorbe@gmail.com\",\"phone\":\"12345678912\",\"street\":\"1583\",\"barangay\":\"carmona\",\"city\":\"makati\",\"province\":\"metro manila\",\"postal_code\":\"1207\"}', 'COD', NULL, '2025-11-19 10:40:55', '2025-11-22 08:33:02'),
(8, 2, 1800.00, 'cancelled', '{\"full_name\":\"fitz\",\"email\":\"kristiamaeorbe@gmail.com\",\"phone\":\"12345678911\",\"street\":\"1583\",\"barangay\":\"carmona\",\"city\":\"makati\",\"province\":\"metro manila\",\"postal_code\":\"1207\"}', 'COD', NULL, '2025-11-19 10:52:51', '2025-11-22 08:33:05'),
(9, 2, 1800.00, 'cancelled', '{\"full_name\":\"fitz\",\"email\":\"kristiamaeorbe@gmail.com\",\"phone\":\"12345678912\",\"street\":\"1583\",\"barangay\":\"carmona\",\"city\":\"makati\",\"province\":\"metro manila\",\"postal_code\":\"1207\"}', 'COD', NULL, '2025-11-21 11:23:38', '2025-11-22 08:33:09'),
(10, 2, 1600.00, 'cancelled', '{\"full_name\":\"fitz\",\"email\":\"kristiamaeorbe@gmail.com\",\"phone\":\"09303060298\",\"street\":\"1583\",\"barangay\":\"carmona\",\"city\":\"makati\",\"province\":\"metro manila\",\"postal_code\":\"1207\"}', 'COD', NULL, '2025-11-22 06:25:10', '2025-11-22 08:33:19'),
(11, 2, 5100.00, 'cancelled', '{\"full_name\":\"fitz\",\"email\":\"kristiamaeorbe@gmail.com\",\"phone\":\"09303060298\",\"street\":\"1583\",\"barangay\":\"carmona\",\"city\":\"makati\",\"province\":\"metro manila\",\"postal_code\":\"1207\"}', 'COD', NULL, '2025-11-22 08:23:46', '2025-11-22 08:34:19'),
(12, 2, 1800.00, 'pending', '{\"full_name\":\"fitz\",\"email\":\"kristiamaeorbe@gmail.com\",\"phone\":\"09303060298\",\"street\":\"1583\",\"barangay\":\"carmona\",\"city\":\"makati\",\"province\":\"metro manila\",\"postal_code\":\"1207\"}', 'COD', NULL, '2025-11-22 12:17:38', '2025-11-22 12:17:38');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `size` varchar(20) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `size`, `quantity`, `price`, `created_at`) VALUES
(6, 6, 39, 103, '6', 1, 1800.00, '2025-11-19 10:34:56'),
(7, 7, 39, 103, '6', 1, 1800.00, '2025-11-19 10:40:55'),
(8, 8, 39, 103, '6', 1, 1800.00, '2025-11-19 10:52:51'),
(9, 9, 38, 102, '6', 1, 1800.00, '2025-11-21 11:23:38'),
(10, 10, 9, 25, '7', 1, 1600.00, '2025-11-22 06:25:10'),
(11, 11, 39, 103, '6', 1, 1800.00, '2025-11-22 08:23:46'),
(12, 11, 38, 102, '7', 1, 1800.00, '2025-11-22 08:23:46'),
(13, 11, 36, 96, '42', 1, 1500.00, '2025-11-22 08:23:46'),
(14, 12, 39, 103, '6', 1, 1800.00, '2025-11-22 12:17:38');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `category` text NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT 0.0,
  `sale` tinyint(1) DEFAULT 0,
  `stock` int(11) DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `image`, `price`, `original_price`, `category`, `brand`, `rating`, `sale`, `stock`, `description`, `created_at`, `updated_at`) VALUES
(2, 'Nike A One Leo Lights EP A ja Wilson', '690d431f91deb_1762476831_0.png', 1700.00, 2000.00, 'Men,Basketball Shoes', 'Nike', 4.0, 0, 30, 'Lightweight and durable basketball shoes built for comfort and control.', '2025-11-07 00:53:51', '2025-11-07 00:53:51'),
(3, 'Nike Book 1', '690d4381d5e14_1762476929_0.png', 1600.00, 1800.00, 'New Arrivals,Men,Basketball Shoes', 'Nike', 4.0, 0, 20, 'Stylish and comfortable basketball shoes made for speed and stability.', '2025-11-07 00:55:29', '2025-11-07 00:55:29'),
(4, 'Nike Book Chapter 1', '690d44035861c_1762477059_0.png', 1700.00, 2000.00, 'New Arrivals,Best Seller,Men,Basketball Shoes', 'Nike', 4.0, 0, 40, 'Durable basketball shoes designed for power and agility', '2025-11-07 00:57:39', '2025-11-07 00:57:39'),
(5, 'Nike KD 18', '690d447ab78d8_1762477178_0.png', 1800.00, 2000.00, 'Best Seller,Men,Basketball Shoes', 'Nike', 4.0, 0, 40, 'High-performance basketball shoes built for comfort and precision.', '2025-11-07 00:59:38', '2025-11-07 00:59:38'),
(6, 'Nike Kobe 6 Protro', '690d44df57bec_1762477279_0.png', 1800.00, 2000.00, 'New Arrivals,Best Seller,Men,Basketball Shoes', 'Nike', 4.0, 0, 20, 'Built for champions, these basketball shoes offer comfort, grip, and support.', '2025-11-07 01:01:19', '2025-11-07 01:01:19'),
(7, 'Puma', '690d45953be18_1762477461_0.png', 1600.00, 1800.00, 'Men,Basketball Shoes', 'Puma', 4.0, 0, 50, 'These basketball shoes combine style and performance.', '2025-11-07 01:04:21', '2025-11-07 01:04:21'),
(8, 'Under Armour Curry 12', '690d45f149112_1762477553_0.png', 1700.00, 1900.00, 'New Arrivals,Men,Basketball Shoes', 'Under Armour', 4.0, 0, 30, 'Play hard with these reliable basketball shoes designed for agility and support.', '2025-11-07 01:05:53', '2025-11-07 01:05:53'),
(9, 'Adidas Spezial', '690d4683c5056_1762477699_0.png', 1600.00, 1800.00, 'New Arrivals,Men,Running Shoes Men,Lifestyle Men,Running Shoes Women,Lifestyle Women', 'Adidas', 4.0, 0, 40, 'Step out in comfort with these versatile lifestyle shoes.', '2025-11-07 01:08:19', '2025-11-22 08:33:19'),
(10, 'Adidas Yeezy Boost 350', '690d46f8111c8_1762477816_0.png', 1600.00, 1800.00, 'Best Seller,Men,Running Shoes Men,Lifestyle Men,Lifestyle Women', 'Adidas', 4.0, 0, 50, 'Perfect for any occasion, these lifestyle shoes offer lasting comfort and timeless design.', '2025-11-07 01:10:16', '2025-11-07 01:10:16'),
(11, 'AIR JORDAN', '690d63e89615b_1762485224_0.png', 1900.00, 2000.00, 'Men,Lifestyle Men', 'Nike', 4.0, 0, 40, 'Comfort meets style with these lifestyle shoes.', '2025-11-07 03:13:44', '2025-11-21 06:02:51'),
(12, 'Air Jordan 1 Low', '690d644f2317b_1762485327_0.png', 1500.00, 2000.00, 'Men,Lifestyle Men', 'Nike', 4.0, 0, 50, 'These lifestyle shoes redefine everyday comfort.', '2025-11-07 03:15:27', '2025-11-21 06:02:33'),
(13, 'Jordan Luka 4', '690d64c075b64_1762485440_0.png', 1500.00, 17000.00, 'Basketball Shoes,Lifestyle Men', 'Nike', 4.0, 0, 40, 'Durable basketball shoes designed for power and agility.', '2025-11-07 03:17:20', '2025-11-21 06:01:52'),
(14, 'NIke SB Dunk', '690d6547964f7_1762485575_0.png', 1500.00, 1700.00, 'Men,Women,Lifestyle Men,Lifestyle Women', 'Nike', 4.0, 0, 50, 'Stay comfortable and stylish wherever you go.', '2025-11-07 03:19:35', '2025-11-07 03:19:35'),
(15, 'Nike Shiox TL Sneaker', '690d65a50676d_1762485669_0.png', 1500.00, 1500.00, 'Men,Women,Lifestyle Men,Lifestyle Women', 'Nike', 4.0, 0, 40, 'Everyday comfort meets effortless style.', '2025-11-07 03:21:09', '2025-11-07 03:21:09'),
(16, 'Puma SpeedCat', '690d66379f958_1762485815_0.png', 1500.00, 1500.00, 'Men,Women,Lifestyle Men,Lifestyle Women', 'Puma', 4.0, 0, 30, 'Step in style with these lifestyle shoes made for comfort and confidence.', '2025-11-07 03:23:35', '2025-11-07 03:23:35'),
(17, 'Vans Checkered Slip Ons', '690d668419dc7_1762485892_0.png', 1500.00, 1500.00, 'Men,Women,Lifestyle Men,Lifestyle Women', 'Vans', 4.0, 0, 10, 'Designed for your daily hustle, these lifestyle shoes combine comfort, durability, and fashion.', '2025-11-07 03:24:52', '2025-11-07 03:24:52'),
(18, 'Vans Checkered with Lace', '690d66bb2cebc_1762485947_0.png', 1500.00, 1500.00, 'Men,Lifestyle Men,Lifestyle Women', 'Vans', 4.0, 0, 10, 'Walk freely with these lifestyle shoes that blend function and fashion.', '2025-11-07 03:25:47', '2025-11-07 03:25:47'),
(19, 'Vans OldSchool Jumbo', '690d66ebc143f_1762485995_0.png', 1500.00, 1500.00, 'Men,Women,Lifestyle Men,Lifestyle Women', 'Vans', 4.0, 0, 10, 'Simple yet stylish, these lifestyle shoes deliver all-day comfort.', '2025-11-07 03:26:35', '2025-11-07 03:26:35'),
(20, 'Vans OldSchool Banda', '690d672bbd802_1762486059_0.png', 1500.00, 1500.00, 'Men,Women,Lifestyle Men,Lifestyle Women', 'Vans', 4.0, 0, 10, 'Stay casual and confident with these lifestyle shoes.', '2025-11-07 03:27:39', '2025-11-07 03:27:39'),
(21, 'Vans Black with Lace', '690d6764a2aed_1762486116_0.png', 1500.00, 1500.00, 'Men,Women,Lifestyle Men,Lifestyle Women', 'Vans', 4.0, 0, 10, 'Experience effortless comfort with these lifestyle shoes.', '2025-11-07 03:28:36', '2025-11-07 03:28:36'),
(22, 'Jordan Titum 1', '690d67dcca9c6_1762486236_0.png', 1700.00, 1700.00, 'Men,Basketball Shoes,Running Shoes Men', 'Nike', 4.0, 0, 10, 'Designed for comfort and versatility, these lifestyle shoes fit any mood or outfit.', '2025-11-07 03:30:36', '2025-11-21 06:01:21'),
(23, 'KD 18 Slim Reaper', '690d681641e04_1762486294_0.png', 2000.00, 2000.00, 'New Arrivals,Men,Basketball Shoes,Running Shoes Men', 'Nike', 4.0, 0, 8, 'Step up your everyday style with these shoes.', '2025-11-07 03:31:34', '2025-11-10 06:16:43'),
(24, 'Nike GT Cut', '690d68405fbef_1762486336_0.png', 1500.00, 1500.00, 'New Arrivals,Basketball Shoes,Running Shoes Men', 'Nike', 4.0, 0, 10, 'These shoes bring comfort and class together.', '2025-11-07 03:32:16', '2025-11-07 03:32:16'),
(25, 'Nike Shox R4', '690d688d78f73_1762486413_0.png', 1500.00, 1500.00, 'New Arrivals,Men,Basketball Shoes,Running Shoes Men', 'Nike', 4.0, 0, 9, 'Play your best game with these basketball shoes designed for speed and balance', '2025-11-07 03:33:33', '2025-11-14 04:19:26'),
(26, 'adidas adizero', '690d69322e44c_1762486578_0.png', 1500.00, 1500.00, 'Men,Women,Lifestyle Men,Lifestyle Women', 'Adidas', 4.0, 0, 40, 'Walk in comfort and confidence with these lifestyle shoes.', '2025-11-07 03:36:18', '2025-11-07 03:36:18'),
(27, 'Adidas Running Shoes', '690d697c1cd8a_1762486652_0.png', 1500.00, 1500.00, 'Men,Women,Running Shoes Men,Running Shoes Women', 'Adidas', 4.0, 0, 40, 'Durable and lightweight, they pair perfectly with jeans, shorts, or any casual outfit.', '2025-11-07 03:37:32', '2025-11-07 03:37:32'),
(28, 'ASICS Gel-Kayano 31 Black', '690d69bcec937_1762486716_0.png', 1700.00, 1700.00, 'Men,Women,Running Shoes Men,Lifestyle Men,Running Shoes Women,Lifestyle Women', 'Asics', 4.0, 0, 10, 'Designed for all-day comfort, these lifestyle shoes feature a minimalist design, breathable build, and soft support. Perfect for work, walks, or simply relaxing in effortless style.', '2025-11-07 03:38:36', '2025-11-07 03:38:36'),
(29, 'ASICS Netburner Ballistic FF Black', '690d69f8385ce_1762486776_0.png', 1700.00, 1700.00, 'Men,Women,Running Shoes Men,Lifestyle Men,Running Shoes Women,Lifestyle Women', 'Asics', 4.0, 0, 20, 'These lifestyle shoes combine versatility and comfort.', '2025-11-07 03:39:36', '2025-11-07 03:39:36'),
(30, 'ASICS Novablast 4 Black', '690d6a576b35f_1762486871_0.png', 1700.00, 1700.00, 'Men,Women,Running Shoes Men,Lifestyle Men,Running Shoes Women,Lifestyle Women', 'Asics', 4.0, 0, 40, 'Step into style with these lifestyle shoes made for your daily routine.', '2025-11-07 03:41:11', '2025-11-07 03:41:11'),
(31, 'Asics GC4', '690d6afd8be32_1762487037_0.png', 1700.00, 1700.00, 'Men,Women,Running Shoes Men,Lifestyle Men,Running Shoes Women,Lifestyle Women', 'Asics', 4.0, 0, 30, 'Effortless style meets everyday comfort.', '2025-11-07 03:43:57', '2025-11-07 03:43:57'),
(32, 'Hoka Bondi 8', '690d6b6cddea4_1762487148_0.png', 1800.00, 1800.00, 'Men,Women,Running Shoes Men,Lifestyle Men,Running Shoes Women,Lifestyle Women', 'Hoka', 4.0, 0, 40, 'Live in comfort and style with these lifestyle shoes.', '2025-11-07 03:45:48', '2025-11-07 03:45:48'),
(33, 'HOKA Clifton 9', '690d6bd16e268_1762487249_0.png', 1800.00, 1800.00, 'Men,Women,Running Shoes Men,Lifestyle Men,Running Shoes Women,Lifestyle Women', 'Hoka', 4.0, 0, 30, 'Move freely with these lifestyle shoes designed for all-day wear.', '2025-11-07 03:47:29', '2025-11-07 03:47:29'),
(34, 'Hoka One One Clifton 8', '690d6c2fd085d_1762487343_0.png', 1800.00, 1800.00, 'Men,Women,Running Shoes Men,Lifestyle Men,Running Shoes Women,Lifestyle Women', 'Hoka', 4.0, 0, 20, 'Step up your casual game with these lifestyle shoes.', '2025-11-07 03:49:03', '2025-11-07 03:49:03'),
(35, 'New Balance 530', '690d6c921ae56_1762487442_0.png', 1500.00, 1500.00, 'Best Seller,Men,Women,Running Shoes Men,Lifestyle Men,Running Shoes Women,Lifestyle Women', 'New Balance', 4.0, 0, 10, 'These lifestyle shoes bring modern comfort to classic design.', '2025-11-07 03:50:42', '2025-11-08 07:45:10'),
(36, 'New Balance 550', '691c320e6e39a_1763455502_0.png', 1500.00, 1500.00, 'New Arrivals,Men,Women,Lifestyle Men,Lifestyle Women', 'New Balance', 4.0, 0, 24, 'The New Balance 550 has a firm, well-built quality with durable leather, solid stitching, and a stable feel that makes it reliable for everyday wear.', '2025-11-18 08:45:02', '2025-11-22 08:34:19'),
(38, 'PUMA x ROSÉ Speedcat', '691c3514146dd_1763456276_0.png', 1800.00, 2000.00, 'New Arrivals,Women,Lifestyle Women', 'Puma', 4.0, 0, 5, 'The Puma x Rose shoes have a smooth, premium build with soft materials, clean stitching, and a comfortable, well-crafted feel overall.', '2025-11-18 08:57:56', '2025-11-22 08:34:19'),
(39, 'Puma Speedcat Ballet', '691c37d42d592_1763456980_0.png', 1800.00, 2000.00, 'New Arrivals,Women,Lifestyle Women', 'Puma', 4.0, 0, 18, 'The Puma Speedcat Ballet has a sleek, soft, and flexible quality with smooth materials and a lightweight, well-crafted feel.', '2025-11-18 09:09:40', '2025-11-22 12:17:38'),
(40, 'Puma Balenciaga', '691c3b7889249_1763457912_0.png', 1800.00, 2000.00, 'Men,Women,Lifestyle Men,Lifestyle Women', 'Puma', 4.0, 0, 35, 'The Puma Balenciaga collaboration has a bold, high-end quality with premium materials, strong construction, and a solid, designer-level feel.', '2025-11-18 09:25:12', '2025-11-18 09:25:12'),
(41, 'Nike Shox Ride 2 SP x Supreme', '691c3d8fbfe14_1763458447_0.png', 1600.00, 1800.00, 'Men,Lifestyle Men', 'Nike', 4.0, 0, 10, 'The Nike Shox Ride 2 SP x Supreme has a sturdy, premium quality with durable materials, sharp detailing, and a solid, high-performance feel.', '2025-11-18 09:34:07', '2025-11-18 09:34:30'),
(42, 'Nike x Supreme Shox Ride 2 SP', '691c4004bb847_1763459076_0.png', 1700.00, 2000.00, 'Men,Running Shoes Men,Lifestyle Men', 'Nike', 4.0, 0, 20, 'The Nike x Supreme Shox Ride 2 SP has a premium, solid quality with durable materials, sharp detailing, and a comfortable, high-performance feel.', '2025-11-18 09:44:36', '2025-11-18 09:44:36'),
(43, 'Nike Air More Uptempo Slides', '691c425fee71d_1763459679_0.png', 1200.00, 1200.00, 'Men,Lifestyle Men', 'Nike', 4.0, 0, 20, 'The Nike Air More Uptempo Slides have a sturdy, comfortable quality with soft cushioning, durable materials, and a smooth, supportive feel.', '2025-11-18 09:54:39', '2025-11-18 09:54:39'),
(44, 'Nike Sabrina 2', '691c46457b9c2_1763460677_0.png', 1500.00, 1500.00, 'Men,Basketball Shoes', 'Nike', 4.0, 0, 10, 'The Nike Sabrina 2 features a sleek, well-crafted quality with durable materials, smooth finishes, and a comfortable, stylish feel.', '2025-11-18 10:11:17', '2025-11-18 10:11:17'),
(45, 'Adidas Harden Volume 8', '691c47d3eb5fd_1763461075_0.png', 1700.00, 1700.00, 'Men,Basketball Shoes', 'Adidas', 4.0, 0, 25, 'The shoes are known for their distinct design and performance features, including a textile upper, a full-length JETBOOST midsole, and a rubber outsole.', '2025-11-18 10:17:55', '2025-11-18 10:17:55'),
(46, 'Jordan Slides', '691c497b3d34f_1763461499_0.png', 1200.00, 1200.00, 'Men,Lifestyle Men', 'Nike', 4.0, 0, 30, 'The Jordan Slides have a soft, durable quality with cushioned soles, sturdy materials, and a comfortable, supportive feel.', '2025-11-18 10:24:59', '2025-11-18 10:26:41'),
(47, 'Nike Air Max Plus', '691d7cdc57252_1763540188_0.png', 1500.00, 1500.00, 'Men,Lifestyle Men', 'Nike', 4.0, 0, 20, 'The Nike Air Max Plus has a durable, well-crafted quality with sturdy materials, smooth detailing, and responsive cushioning that gives it a solid, supportive feel.', '2025-11-19 08:16:28', '2025-11-19 08:16:28'),
(48, 'Nike Air Force 1 Low Kobe Bryant \\\'Lakers Away\\\'', '691d7e0adc147_1763540490_0.png', 1600.00, 1600.00, 'Men,Lifestyle Men', 'Nike', 4.0, 0, 10, 'The Nike Air Force 1 Low Kobe Bryant “Lakers Away” has a premium, well-built quality with rich materials, clean detailing, and a solid, refined feel that reflects its tribute design.', '2025-11-19 08:21:30', '2025-11-19 08:21:30'),
(49, 'Nike LeBron 22 \\\'Space Jam\\\'', '691d7f1b529ab_1763540763_0.png', 1700.00, 1700.00, 'Men,Basketball Shoes', 'Nike', 4.0, 0, 10, 'The Nike LeBron 22 “Space Jam” has a high-performance, premium quality with durable materials, detailed construction, and a solid, responsive feel.', '2025-11-19 08:26:03', '2025-11-19 08:26:03'),
(50, 'Nike GT Cut 4', '691d8325e6cc4_1763541797_0.png', 1500.00, 1500.00, 'Men,Lifestyle Men', 'Nike', 4.0, 0, 10, 'The Nike G.T. Cut 4 has a lightweight yet durable quality with breathable materials, sharp detailing, and a responsive, high-performance feel.', '2025-11-19 08:43:17', '2025-11-19 08:43:41'),
(51, 'Nike Sabrina 3', '691d863605180_1763542582_0.png', 1500.00, 1500.00, 'Men,Basketball Shoes', 'Nike', 4.0, 0, 25, 'The Nike Sabrina 3 has a sleek, premium quality with durable materials, clean craftsmanship, and a responsive, comfortable feel.', '2025-11-19 08:56:21', '2025-11-19 08:56:22'),
(52, 'Nike Ja 3', '691d88669a4a3_1763543142_0.png', 1500.00, 1500.00, 'Men,Basketball Shoes,Lifestyle Men', 'Nike', 4.0, 0, 20, 'The Nike Ja 3 has a durable, high-performance quality with sturdy materials, sharp detailing, and a responsive, supportive feel.', '2025-11-19 09:05:42', '2025-11-19 09:05:42');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `size` varchar(20) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `variant_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `image`, `size`, `stock`, `variant_order`, `created_at`) VALUES
(2, 2, '690d431f91deb_1762476831_0.png', '7,8,9,10', 10, 0, '2025-11-07 00:53:51'),
(3, 2, '690d431f92e28_1762476831_1.png', '7,8,9,10', 10, 1, '2025-11-07 00:53:51'),
(4, 2, '690d431f94381_1762476831_2.png', '7,8,9,10', 10, 2, '2025-11-07 00:53:51'),
(5, 3, '690d4381d5e14_1762476929_0.png', '7,8,9,10', 10, 0, '2025-11-07 00:55:29'),
(6, 3, '690d4381d82c0_1762476929_1.png', '7,8,9,10', 10, 1, '2025-11-07 00:55:29'),
(7, 4, '690d44035861c_1762477059_0.png', '7,8,9,10', 10, 0, '2025-11-07 00:57:39'),
(8, 4, '690d44035920b_1762477059_1.png', '7,8,9,10', 10, 1, '2025-11-07 00:57:39'),
(9, 4, '690d44035aaff_1762477059_2.png', '7,8,9,10', 10, 2, '2025-11-07 00:57:39'),
(10, 4, '690d44035b658_1762477059_3.png', '7,8,9,10', 10, 3, '2025-11-07 00:57:39'),
(11, 5, '690d447ab78d8_1762477178_0.png', '7,8,9,10', 10, 0, '2025-11-07 00:59:38'),
(12, 5, '690d447ab8549_1762477178_1.png', '7,8,9,10', 10, 1, '2025-11-07 00:59:38'),
(13, 5, '690d447aba20d_1762477178_2.png', '7,8,9,10', 10, 2, '2025-11-07 00:59:38'),
(14, 5, '690d447abac71_1762477178_3.png', '7,8,9,10', 10, 3, '2025-11-07 00:59:38'),
(15, 6, '690d44df57bec_1762477279_0.png', '7,8,9,10', 10, 0, '2025-11-07 01:01:19'),
(16, 6, '690d44df5a3e8_1762477279_1.png', '7,8,9,10', 10, 1, '2025-11-07 01:01:19'),
(17, 7, '690d45953be18_1762477461_0.png', '7,8,9,10', 10, 0, '2025-11-07 01:04:21'),
(18, 7, '690d45953c921_1762477461_1.png', '7,8,9,10', 10, 1, '2025-11-07 01:04:21'),
(19, 7, '690d45953ceb1_1762477461_2.png', '7,8,9,10', 10, 2, '2025-11-07 01:04:21'),
(20, 7, '690d45953d37f_1762477461_3.png', '7,8,9,10', 10, 3, '2025-11-07 01:04:21'),
(21, 7, '690d45953dda2_1762477461_4.png', '7,8,9,10', 10, 4, '2025-11-07 01:04:21'),
(22, 8, '690d45f149112_1762477553_0.png', '7,8,9,10', 10, 0, '2025-11-07 01:05:53'),
(23, 8, '690d45f14a1aa_1762477553_1.png', '7,8,9,10', 10, 1, '2025-11-07 01:05:53'),
(24, 8, '690d45f14a8b3_1762477553_2.png', '7,8,9,10', 10, 2, '2025-11-07 01:05:53'),
(25, 9, '690d4683c5056_1762477699_0.png', '7,8,9,10', 20, 0, '2025-11-07 01:08:19'),
(26, 9, '690d4683c5b79_1762477699_1.png', '7,8,9,10', 10, 1, '2025-11-07 01:08:19'),
(27, 10, '690d46f8111c8_1762477816_0.png', '7,8,9,10', 10, 0, '2025-11-07 01:10:16'),
(28, 10, '690d46f811b56_1762477816_1.png', '7,8,9,10', 10, 1, '2025-11-07 01:10:16'),
(29, 10, '690d46f812e12_1762477816_2.png', '7,8,9,10', 10, 2, '2025-11-07 01:10:16'),
(30, 10, '690d46f813e45_1762477816_3.png', '7,8,9,10', 10, 3, '2025-11-07 01:10:16'),
(31, 10, '690d46f814575_1762477816_4.png', '7,8,9,10', 10, 4, '2025-11-07 01:10:16'),
(32, 11, '690d63e89615b_1762485224_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:13:44'),
(33, 11, '690d63e897479_1762485224_1.png', '7,8,9,10', 10, 1, '2025-11-07 03:13:44'),
(34, 11, '690d63e897ec6_1762485224_2.png', '7,8,9,10', 10, 2, '2025-11-07 03:13:44'),
(35, 11, '690d63e89c1e9_1762485224_3.png', '7,8,9,10', 10, 3, '2025-11-07 03:13:44'),
(36, 12, '690d644f2317b_1762485327_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:15:27'),
(37, 12, '690d644f2491d_1762485327_1.png', '7,8,9,10', 10, 1, '2025-11-07 03:15:27'),
(38, 12, '690d644f256c5_1762485327_2.png', '7,8,9,10', 10, 2, '2025-11-07 03:15:27'),
(39, 12, '690d644f25bb6_1762485327_3.png', '7,8,9,10', 10, 3, '2025-11-07 03:15:27'),
(40, 12, '690d644f265f8_1762485327_4.png', '7,8,9,10', 10, 4, '2025-11-07 03:15:27'),
(41, 13, '690d64c075b64_1762485440_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:17:20'),
(42, 13, '690d64c076833_1762485440_1.png', '7,8,9,10', 10, 1, '2025-11-07 03:17:20'),
(43, 13, '690d64c077bcc_1762485440_2.png', '7,8,9,10', 10, 2, '2025-11-07 03:17:20'),
(44, 13, '690d64c078360_1762485440_3.png', '7,8,9,10', 10, 3, '2025-11-07 03:17:20'),
(45, 14, '690d6547964f7_1762485575_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:19:35'),
(46, 14, '690d654796f6d_1762485575_1.png', '7,8,9,10', 10, 1, '2025-11-07 03:19:35'),
(47, 14, '690d65479804e_1762485575_2.png', '7,8,9,10', 10, 2, '2025-11-07 03:19:35'),
(48, 14, '690d654798611_1762485575_3.png', '7,8,9,10', 10, 3, '2025-11-07 03:19:35'),
(49, 14, '690d654799104_1762485575_4.png', '7,8,9,10', 10, 4, '2025-11-07 03:19:35'),
(50, 15, '690d65a50676d_1762485669_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:21:09'),
(51, 15, '690d65a506f4e_1762485669_1.png', '7,8,9,10', 10, 1, '2025-11-07 03:21:09'),
(52, 15, '690d65a5080ca_1762485669_2.png', '7,8,9,10', 10, 2, '2025-11-07 03:21:09'),
(53, 15, '690d65a50955a_1762485669_3.png', '7,8,9,10', 10, 3, '2025-11-07 03:21:09'),
(54, 16, '690d66379f958_1762485815_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:23:35'),
(55, 16, '690d6637a113d_1762485815_1.png', '7,8,9,10', 10, 1, '2025-11-07 03:23:35'),
(56, 16, '690d6637a1ca0_1762485815_2.png', '7,8,9,10', 10, 2, '2025-11-07 03:23:35'),
(57, 17, '690d668419dc7_1762485892_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:24:52'),
(58, 18, '690d66bb2cebc_1762485947_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:25:47'),
(59, 19, '690d66ebc143f_1762485995_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:26:35'),
(60, 20, '690d672bbd802_1762486059_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:27:39'),
(61, 21, '690d6764a2aed_1762486116_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:28:36'),
(62, 9, '690d67ad01750_1762486189_0.png', '7,8,9,10', 10, 2, '2025-11-07 03:29:49'),
(63, 22, '690d67dcca9c6_1762486236_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:30:36'),
(64, 23, '690d681641e04_1762486294_0.png', '7,8,9,10', 8, 0, '2025-11-07 03:31:34'),
(65, 24, '690d68405fbef_1762486336_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:32:16'),
(66, 25, '690d688d78f73_1762486413_0.png', '7,8,9,10', 9, 0, '2025-11-07 03:33:33'),
(67, 26, '690d69322e44c_1762486578_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:36:18'),
(68, 26, '690d69322efb4_1762486578_1.png', '7,8,9,10', 10, 1, '2025-11-07 03:36:18'),
(69, 26, '690d69322f544_1762486578_2.png', '7,8,9,10', 10, 2, '2025-11-07 03:36:18'),
(70, 26, '690d69322fa4d_1762486578_3.png', '7,8,9,10', 10, 3, '2025-11-07 03:36:18'),
(71, 27, '690d697c1cd8a_1762486652_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:37:32'),
(72, 27, '690d697c1e42e_1762486652_1.png', '7,8,9,10', 10, 1, '2025-11-07 03:37:32'),
(73, 27, '690d697c1eb67_1762486652_2.png', '7,8,9,10', 10, 2, '2025-11-07 03:37:32'),
(74, 27, '690d697c1f07b_1762486652_3.png', '7,8,9,10', 10, 3, '2025-11-07 03:37:32'),
(75, 28, '690d69bcec937_1762486716_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:38:36'),
(76, 29, '690d69f8385ce_1762486776_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:39:36'),
(77, 29, '690d69f838c2a_1762486776_1.png', '7,8,9,10', 10, 1, '2025-11-07 03:39:36'),
(78, 30, '690d6a576b35f_1762486871_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:41:11'),
(79, 30, '690d6a576c239_1762486871_1.png', '7,8,9,10', 10, 1, '2025-11-07 03:41:11'),
(80, 30, '690d6a576c74c_1762486871_2.png', '7,8,9,10', 10, 2, '2025-11-07 03:41:11'),
(81, 30, '690d6a576cc23_1762486871_3.png', '7,8,9,10', 10, 3, '2025-11-07 03:41:11'),
(82, 31, '690d6afd8be32_1762487037_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:43:57'),
(83, 31, '690d6afd8c986_1762487037_1.png', '7,8,9,10', 10, 1, '2025-11-07 03:43:57'),
(84, 31, '690d6afd8d283_1762487037_2.png', '7,8,9,10', 10, 2, '2025-11-07 03:43:57'),
(85, 32, '690d6b6cddea4_1762487148_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:45:48'),
(86, 32, '690d6b6cde46f_1762487148_1.png', '7,8,9,10', 10, 1, '2025-11-07 03:45:48'),
(87, 32, '690d6b6cde958_1762487148_2.png', '7,8,9,10', 10, 2, '2025-11-07 03:45:48'),
(88, 32, '690d6b6cdf333_1762487148_3.png', '7,8,9,10', 10, 3, '2025-11-07 03:45:48'),
(89, 33, '690d6bd16e268_1762487249_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:47:29'),
(90, 33, '690d6bd16fd6e_1762487249_1.png', '7,8,9,10', 10, 1, '2025-11-07 03:47:29'),
(91, 33, '690d6bd1705d2_1762487249_2.png', '7,8,9,10', 10, 2, '2025-11-07 03:47:29'),
(92, 34, '690d6c2fd085d_1762487343_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:49:03'),
(93, 34, '690d6c2fd0f53_1762487343_1.png', '7,8,9,10', 10, 1, '2025-11-07 03:49:03'),
(94, 35, '690d6c921ae56_1762487442_0.png', '7,8,9,10', 10, 0, '2025-11-07 03:50:42'),
(95, 35, '690d6c921b3e8_1762487442_1.png', '7,8,9,10', 0, 1, '2025-11-07 03:50:42'),
(96, 36, '691c320e6e39a_1763455502_0.png', '40,41,42,43,44,45', 5, 0, '2025-11-18 08:45:02'),
(98, 36, '691c33c4ceab7_1763455940_0.png', '40,41,42,43,44,45', 5, 1, '2025-11-18 08:52:20'),
(99, 36, '691c33c4cfe12_1763455940_1.png', '40,41,42,43,44,45', 4, 2, '2025-11-18 08:52:20'),
(100, 36, '691c33c4d1483_1763455940_2.png', '40,41,42,43,44,45', 5, 3, '2025-11-18 08:52:20'),
(101, 36, '691c33c4d1c9d_1763455940_3.png', '40,41,42,43,44,45', 5, 4, '2025-11-18 08:52:20'),
(102, 38, '691c3514146dd_1763456276_0.png', '6,7,8,9,10', 5, 0, '2025-11-18 08:57:56'),
(103, 39, '691c37d42d592_1763456980_0.png', '6,7,8,9,10', 3, 0, '2025-11-18 09:09:40'),
(104, 39, '691c37d42e0f1_1763456980_1.png', '6,7,8,9,10', 5, 1, '2025-11-18 09:09:40'),
(105, 39, '691c37d42e80a_1763456980_2.jpg', '6,7,8,9,10', 5, 2, '2025-11-18 09:09:40'),
(106, 39, '691c37d42ee4b_1763456980_3.png', '6,7,8,9,10', 5, 3, '2025-11-18 09:09:40'),
(107, 40, '691c3b7889249_1763457912_0.png', '40,41,42,43,44,45', 5, 0, '2025-11-18 09:25:12'),
(108, 40, '691c3b788c989_1763457912_1.png', '40,41,42,43,44,45', 5, 1, '2025-11-18 09:25:12'),
(109, 40, '691c3b788e182_1763457912_2.png', '40,41,42,43,44,45', 5, 2, '2025-11-18 09:25:12'),
(110, 40, '691c3b788efd1_1763457912_3.png', '40,41,42,43,44,45', 5, 3, '2025-11-18 09:25:12'),
(111, 40, '691c3b788faef_1763457912_4.png', '40,41,42,43,44,45', 5, 4, '2025-11-18 09:25:12'),
(112, 40, '691c3b7890d1a_1763457912_5.png', '40,41,42,43,44,45', 5, 5, '2025-11-18 09:25:12'),
(113, 40, '691c3b78929b8_1763457912_6.png', '40,41,42,43,44,45', 5, 6, '2025-11-18 09:25:12'),
(114, 41, '691c3d8fbfe14_1763458447_0.png', '40,41,42,43,44,45', 5, 0, '2025-11-18 09:34:07'),
(115, 41, '691c3da6a884f_1763458470_0.png', '40,41,42,43,44,45', 5, 1, '2025-11-18 09:34:30'),
(116, 42, '691c4004bb847_1763459076_0.png', '40,41,42,43,44,45', 5, 0, '2025-11-18 09:44:36'),
(117, 42, '691c4004bddb5_1763459076_1.png', '40,41,42,43,44,45', 5, 1, '2025-11-18 09:44:36'),
(118, 42, '691c4004bebd6_1763459076_2.png', '40,41,42,43,44,45', 5, 2, '2025-11-18 09:44:36'),
(119, 42, '691c4004bf3af_1763459076_3.png', '40,41,42,43,44,45', 5, 3, '2025-11-18 09:44:36'),
(120, 43, '691c425fee71d_1763459679_0.png', '40,41,42,43,44,45', 5, 0, '2025-11-18 09:54:39'),
(121, 43, '691c425ff05ef_1763459679_1.png', '40,41,42,43,44,45', 5, 1, '2025-11-18 09:54:39'),
(122, 43, '691c425ff0f6d_1763459679_2.png', '40,41,42,43,44,45', 5, 2, '2025-11-18 09:54:39'),
(123, 43, '691c425ff18b5_1763459679_3.png', '40,41,42,43,44,45', 5, 3, '2025-11-18 09:54:39'),
(124, 44, '691c46457b9c2_1763460677_0.png', '40,41,42,43,44,45', 5, 0, '2025-11-18 10:11:17'),
(125, 44, '691c46457ce34_1763460677_1.png', '40,41,42,43,44,45', 5, 1, '2025-11-18 10:11:17'),
(126, 45, '691c47d3eb5fd_1763461075_0.png', '40,41,42,43,44,45', 5, 0, '2025-11-18 10:17:55'),
(127, 45, '691c47d3ed304_1763461075_1.png', '40,41,42,43,44,45', 5, 1, '2025-11-18 10:17:55'),
(128, 45, '691c47d3ed918_1763461075_2.png', '40,41,42,43,44,45', 5, 2, '2025-11-18 10:17:55'),
(129, 45, '691c47d3edf69_1763461075_3.png', '40,41,42,43,44,45', 5, 3, '2025-11-18 10:17:55'),
(130, 45, '691c47d3ee423_1763461075_4.png', '40,41,42,43,44,45', 5, 4, '2025-11-18 10:17:55'),
(131, 46, '691c497b3d34f_1763461499_0.png', '40,41,42,43,44,45', 5, 0, '2025-11-18 10:24:59'),
(132, 46, '691c49e1dd578_1763461601_0.png', '40,41,42,43,44,45', 5, 1, '2025-11-18 10:26:41'),
(133, 46, '691c49e1de4e6_1763461601_1.png', '40,41,42,43,44,45', 5, 2, '2025-11-18 10:26:41'),
(134, 46, '691c49e1deadd_1763461601_2.png', '40,41,42,43,44,45', 5, 3, '2025-11-18 10:26:41'),
(135, 46, '691c49e1df88e_1763461601_3.png', '40,41,42,43,44,45', 5, 4, '2025-11-18 10:26:41'),
(136, 46, '691c49e1e04ab_1763461601_4.png', '40,41,42,43,44,45', 5, 5, '2025-11-18 10:26:41'),
(137, 47, '691d7cdc57252_1763540188_0.png', '40,41,42,43,44,45', 5, 0, '2025-11-19 08:16:28'),
(138, 47, '691d7cdc5a0c3_1763540188_1.png', '40,41,42,43,44,45', 5, 1, '2025-11-19 08:16:28'),
(139, 47, '691d7cdc5b13a_1763540188_2.png', '40,41,42,43,44,45', 5, 2, '2025-11-19 08:16:28'),
(140, 47, '691d7cdc5c596_1763540188_3.png', '40,41,42,43,44,45', 5, 3, '2025-11-19 08:16:28'),
(141, 48, '691d7e0adc147_1763540490_0.png', '40,41,42,43,44,45', 5, 0, '2025-11-19 08:21:30'),
(142, 48, '691d7e0ae0300_1763540490_1.png', '40,41,42,43,44,45', 5, 1, '2025-11-19 08:21:30'),
(143, 49, '691d7f1b529ab_1763540763_0.png', '40,41,42,43,44,45', 5, 0, '2025-11-19 08:26:03'),
(144, 49, '691d7f1b55c57_1763540763_1.png', '40,41,42,43,44,45', 5, 1, '2025-11-19 08:26:03'),
(145, 50, '691d8325e6cc4_1763541797_0.png', '40,41,42,43,44,45', 5, 0, '2025-11-19 08:43:17'),
(146, 50, '691d833d17242_1763541821_0.png', '40,41,42,43,44,45', 5, 1, '2025-11-19 08:43:41'),
(147, 51, '691d863605180_1763542582_0.png', '40,41,42,43,44,45', 5, 0, '2025-11-19 08:56:22'),
(148, 51, '691d863606fb3_1763542582_1.png', '40,41,42,43,44,45', 5, 1, '2025-11-19 08:56:22'),
(149, 51, '691d863608a12_1763542582_2.png', '40,41,42,43,44,45', 5, 2, '2025-11-19 08:56:22'),
(150, 51, '691d86360976d_1763542582_3.png', '40,41,42,43,44,45', 5, 3, '2025-11-19 08:56:22'),
(151, 51, '691d86360a428_1763542582_4.png', '40,41,42,43,44,45', 5, 4, '2025-11-19 08:56:22'),
(152, 52, '691d88669a4a3_1763543142_0.png', '40,41,42,43,44,45', 5, 0, '2025-11-19 09:05:42'),
(153, 52, '691d88669cd62_1763543142_1.png', '40,41,42,43,44,45', 5, 1, '2025-11-19 09:05:42'),
(154, 52, '691d8866a109f_1763543142_2.png', '40,41,42,43,44,45', 5, 2, '2025-11-19 09:05:42'),
(155, 52, '691d8866a2663_1763543142_3.png', '40,41,42,43,44,45', 5, 3, '2025-11-19 09:05:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@lee.com', 'admin123', 'Administrator', 'admin', '2025-11-06 05:40:01', '2025-11-06 05:40:01'),
(2, 'BATO', 'kristiamaeorbe@gmail.com', 'batobato', 'fitz', 'user', '2025-11-06 05:41:02', '2025-11-06 05:41:02');

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_data` text NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `address_data`, `is_default`, `created_at`, `updated_at`) VALUES
(1, 2, '{\"first_name\":\"fitzgerald\",\"last_name\":\"aclan\",\"address\":\"1583 A. Mendoza St. Brgy. Carmona Makati City\",\"apartment\":\"4D\",\"postal_code\":\"207\",\"city\":\"makati\",\"region\":\"Metro Manila\",\"phone\":\"09303060298\",\"is_default\":1}', 1, '2025-11-18 11:03:09', '2025-11-18 11:03:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`,`variant_id`,`size`),
  ADD KEY `variant_id` (`variant_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_brand` (`brand`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_default` (`is_default`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=156;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
