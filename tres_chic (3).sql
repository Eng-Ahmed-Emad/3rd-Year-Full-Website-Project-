-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2025 at 08:40 PM
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
-- Database: `tres_chic`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `phone` int(15) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `product_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `first_name`, `last_name`, `phone`, `email`, `total_amount`, `shipping_address`, `created_at`, `updated_at`, `product_id`) VALUES
(35, 1, 'Ahmed', 'Sherif', 1030680370, 'ahmedgamer250@gmail.com', 4875.00, '18 hakim ali hassan', '2025-05-03 00:44:10', '2025-05-03 00:44:10', 6),
(41, 1, 'Ahmed', 'Sherif', 1030680370, 'ahmedgamer250@gmail.com', 975.00, '18 hakim ali hassan', '2025-05-03 00:56:20', '2025-05-03 00:56:20', 6),
(47, 1, 'Ahmed', 'Sherif', 1030680370, 'ahmedgamer250@gmail.com', 4250.00, '18 hakim ali hassan', '2025-05-03 01:37:57', '2025-05-03 01:37:57', 5),
(48, 1, 'Ahmed', 'Sherif', 1030680370, 'ahmedgamer250@gmail.com', 1485.00, '18 hakim ali hassan', '2025-05-03 02:51:20', '2025-05-03 02:51:20', 5),
(49, 1, 'Ahmed', 'Sherif', 1030680370, 'ahmedgamer250@gmail.com', 150.00, '18 hakim ali hassan', '2025-05-03 02:51:41', '2025-05-03 02:51:41', 7),
(50, 1, 'Ahmed', 'Sherif', 1030680370, 'ahmedgamer250@gmail.com', 150.00, '18 hakim ali hassan', '2025-05-03 02:51:55', '2025-05-03 02:51:55', 7),
(51, 1, 'Ahmed', 'Sherif', 1030680370, 'ahmedgamer250@gmail.com', 4875.00, '18 hakim ali hassan', '2025-05-03 02:57:22', '2025-05-03 02:57:22', 6),
(52, 1, 'Ahmed', 'Sherif', 1030680370, 'ahmedgamer250@gmail.com', 212500.00, '18 hakim ali hassan', '2025-05-03 03:03:12', '2025-05-03 03:03:12', 5),
(53, 1, 'mostafa', 'khater', 1030680370, 'ahmedgamer250@gmail.com', 27295.00, '18 hakim ali hassan', '2025-05-03 03:11:00', '2025-05-03 03:11:00', 2),
(54, 1, 'Marwan', 'Vosper', 111, 'manomero1975@gmail.com', 3135.00, '&ls', '2025-05-03 13:08:48', '2025-05-03 13:08:48', 5),
(63, 2, 'Vitoria', 'Vosper', 111, 'maro@gmail.com', 2220.00, 'test', '2025-05-04 15:39:16', '2025-05-04 15:39:16', 8),
(64, 1, 'marcel ', 'coler', 111, 'maro@gmail.com', 15229.00, 'test', '2025-05-04 16:15:33', '2025-05-04 16:15:33', 7),
(65, 1, 'maro', 'sherif', 111, 'maro@gmail.com', 975.00, 'test', '2025-05-04 16:16:48', '2025-05-04 16:16:48', 2),
(66, 1, 'Vitoria', 'Vosper', 1115573567, 'test@test.com', 499.00, 'test', '2025-05-04 16:18:27', '2025-05-04 16:18:27', 17);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_time` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `quantity`, `price_at_time`) VALUES
(26, 31, 6, 5, 975.00),
(27, 32, 5, 5, 4250.00),
(28, 33, 2, 5, 975.00),
(29, 34, 2, 5, 975.00),
(30, 35, 2, 5, 975.00),
(32, 41, 2, 1, 975.00),
(36, 47, 5, 1, 4250.00),
(37, 48, 9, 3, 345.00),
(38, 48, 3, 3, 150.00),
(39, 49, 3, 1, 150.00),
(40, 50, 3, 1, 150.00),
(41, 51, 2, 5, 975.00),
(42, 52, 5, 50, 4250.00),
(43, 53, 6, 2, 910.00),
(44, 53, 5, 1, 4250.00),
(45, 53, 4, 20, 330.00),
(46, 53, 2, 15, 975.00),
(47, 54, 9, 1, 345.00),
(48, 54, 7, 1, 2790.00),
(49, 63, 8, 2, 1110.00),
(50, 64, 7, 2, 2790.00),
(51, 64, 5, 1, 4250.00),
(52, 64, 9, 1, 345.00),
(53, 64, 4, 1, 330.00),
(54, 64, 2, 1, 975.00),
(55, 64, 3, 1, 330.00),
(56, 64, 6, 1, 910.00),
(57, 64, 14, 1, 900.00),
(58, 64, 17, 1, 499.00),
(59, 64, 8, 1, 1110.00),
(60, 65, 2, 1, 975.00),
(61, 66, 17, 1, 499.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `path` varchar(255) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `category`, `image_url`, `path`, `stock_quantity`, `created_at`, `updated_at`) VALUES
(2, 'Pont Neuf 35mm Belt', 'Classic 35mm belt with premium leather', 975.00, 'Accessories', './Images/belt.png', 'belt.php', 13, '2025-05-02 11:30:47', '2025-05-04 16:16:48'),
(3, 'Legacy Pilot Square Sunglasses', 'Stylish square sunglasses with UV protection', 330.00, 'Accessories', './Images/glasses.png', 'glasses.php', 14, '2025-05-02 11:30:47', '2025-05-04 16:15:33'),
(4, 'Imagination Perfume', 'Luxury fragrance for men', 330.00, 'Fragrance', './Images/perfume.png', 'perfume.php', 23, '2025-05-02 11:30:47', '2025-05-04 16:15:33'),
(5, 'Costume Élégant', 'Ce costume noir offre un style intemporel essentiel pour le bureau. Il est confectionné dans un jacquard de laine ton sur ton orné d\'un motif LV Blason qui s\'étend jusqu\'à la doublure.', 4250.00, 'suit', './Images/Black_suit .png', 'Custome.php', 19, '2025-05-02 13:22:31', '2025-05-04 16:15:33'),
(6, 'Chemise Blanche', 'Cette chemise blanche classique incarne l\'élégance intemporelle. Confectionnée en popeline de coton de la plus haute qualité, elle offre une coupe ajustée moderne avec des détails raffinés comme les boutons en nacre véritable.', 910.00, 'shirt', './Images/white shirt inner.png', 'shirt.php', 24, '2025-05-02 13:23:04', '2025-05-04 16:15:33'),
(7, 'Gilet Matelassé Réversible', 'Ce gilet matelassé réversible allie style et fonctionnalité. D\'un côté, un motif signature élégant, de l\'autre, une finition unie sophistiquée. La doublure en soie et le rembourrage en duvet offrent un confort optimal.', 2790.00, 'vest', './Images/vest outter.png', 'vest.php', 5, '2025-05-02 13:23:45', '2025-05-04 16:20:07'),
(8, 'Mocassins Classiques', 'Ces mocassins classiques incarnent l\'élégance italienne. Confectionnés en cuir de veau souple avec une semelle en cuir, ils offrent un confort exceptionnel et une allure sophistiquée.', 1110.00, 'shoes', './Images/shoes.png', 'shoes.php', 50, '2025-05-02 13:24:23', '2025-05-04 16:15:33'),
(9, 'Monogram Wildflowers Tie', 'Cette cravate en soie jacquard présente un motif Monogram Wildflowers exclusif. Sa finition mate et sa doublure en soie offrent une élégance raffinée pour toutes les occasions formelles.', 345.00, 'tie', './Images/tie.png', 'tie.php', 26, '2025-05-02 15:05:12', '2025-05-04 16:15:33'),
(14, 'Off-White Chemise', 'Cette chemise blanche classique incarne l\'élégance intemporelle. Confectionnée en popeline de coton de la plus haute qualité, elle offre une coupe ajustée moderne avec des détails raffinés comme les boutons en nacre véritable.', 900.00, 'shirt', './Images/White shirt inner1.png', 'shirt.php', 5, '2025-05-03 02:56:33', '2025-05-04 16:15:33'),
(15, 'Tuxedo', 'Ce costume noir offre un style intemporel essentiel pour le bureau. Il est confectionné dans un jacquard de laine ton sur ton orné d\'un motif LV Blason qui s\'étend jusqu\'à la doublure.', 9000.00, 'suit', './Images/Black suit opened.png', 'Custome.php', 20, '2025-05-03 03:08:34', '2025-05-03 13:20:16'),
(17, 'Beige Shirt', 'This easy pale beige cotton t-shirt in comfortable French terry is a must-have piece for summer. The softly textured jacquard features a tonal allover Damier with a discreet embroidered Marque L.Vuitton Déposée on the chest. This effortless piece is perfect for relaxing by the pool and can be worn as a set with matching shorts.\n\nRegular Fit\nLight Beige\n85% Cotton, 15% Polyamide\nFrench Terry with allover signature\nRibbed collar\nSignature embroidery on chest\nMade in Italy', 499.00, 'shirt', './Images/beige shirt.png', 'product.php', 20, '2025-05-03 21:29:41', '2025-05-04 16:20:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `role` varchar(10) NOT NULL DEFAULT 'guest'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `phone_number`, `address`, `created_at`, `updated_at`, `role`) VALUES
(1, '3omdatraholat', '3omda@gmail.com', '$2y$10$vm/hctybj2DIqpX8AnNONuT36kUu9wwY4X3B/8dOSvbUKgEFzRkb6', '3omda', 'traholat', '011111111111', 'test', '2025-05-02 11:58:26', '2025-05-02 13:08:57', 'admin'),
(2, 'marwansherif721', 'maro@gmail.com', '$2y$10$opkUBq5iiDtHVyUqfYrgDOSVehtSBilH.CjXBkFPR5aXqQyf8p1Wu', 'Marwan', 'Sherif', '011111111111', 'test', '2025-05-02 13:29:18', '2025-05-02 13:29:18', 'user'),
(3, 'sherfa', 'sherfa@mail.com', '$2y$10$xI4J9FvbLcILYBtOihWydebJbep3R2Io.vrlKX0KYGOLDf7jcmtku', 'Ahmed', 'Sherif', '01030680370', 'cairo', '2025-05-02 21:47:01', '2025-05-02 21:47:01', 'user'),
(4, '7amada69', 'lol@mail.com', '$2y$10$KCNrnZcVMf/cQU92tSVyweF.tnWMC/XBOa.ha8Q1XjPITyE3cHtYC', '7amada', 'ahmed', '01030680370', '18 hakim ali hassan', '2025-05-03 00:59:59', '2025-05-03 00:59:59', 'user'),
(5, 'sherfa70', 'test@test.com', '$2y$10$rDCpCXVzADTB7Ethp5XL6uKfW9c2jjFlsaOcQPnqkwFT.ALU00eQO', 'ahmed', 'sherif', '011111111111', 'test', '2025-05-04 15:46:29', '2025-05-04 15:46:29', 'user'),
(6, 'mokhat', 'test1@test.com', '$2y$10$sEc6rz/5W.GPclbl/KR1QetHzLdFRTk5R4wvS8UoMqT/S9giT.0Rq', 'abdo', 'shweal', '011111111111', 'test', '2025-05-04 15:49:20', '2025-05-04 15:49:20', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_orders_product` (`product_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
