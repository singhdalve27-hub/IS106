-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 18, 2026 at 03:25 PM
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
-- Database: `carenderia_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'admin', '$2y$10$7R9Gv9v9gKk8g8f7f7f7fe3m8H8m8J8k8L8o8M8N8O8P8Q8R8S8Tu', '2026-05-18 12:24:54');

-- --------------------------------------------------------

--
-- Table structure for table `daily_menu`
--

CREATE TABLE `daily_menu` (
  `id` int(11) NOT NULL,
  `menu_item_id` int(11) NOT NULL,
  `available_date` date NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_menu`
--

INSERT INTO `daily_menu` (`id`, `menu_item_id`, `available_date`, `is_available`) VALUES
(1, 1, '2026-05-18', 1),
(2, 2, '2026-05-18', 1),
(3, 3, '2026-05-18', 1),
(4, 7, '2026-05-18', 1);

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `gallery`
--

INSERT INTO `gallery` (`id`, `title`, `image_path`, `menu_item_id`, `uploaded_at`) VALUES
(1, 'Malinis na Kusina', 'assets/uploads/kitchen.jpg', NULL, '2026-05-18 12:24:54'),
(2, 'Kuha ng Aming Masarap na Adobo', 'assets/uploads/pork-adobo.jpg', 1, '2026-05-18 12:24:54'),
(3, 'Mainit-init na Sinigang', 'assets/uploads/sinigang-baboy.jpg', 2, '2026-05-18 12:24:54');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('Main Dish','Side Dish','Dessert','Drinks','Soup') NOT NULL DEFAULT 'Main Dish',
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `description`, `category`, `price`, `image_path`, `created_at`) VALUES
(1, 'Pork Adobo', 'Classic at malinamnam na pork adobo na may sarsa ng toyo, suka, at bawang.', 'Main Dish', 70.00, 'assets/uploads/pork-adobo.png', '2026-05-18 12:24:54'),
(2, 'Sinigang na Baboy', 'Mainit at maasim-asim na sabaw na may sariwang kangkong, gabi, at sitaw.', 'Soup', 85.00, 'assets/uploads/sinigang-baboy.png', '2026-05-18 12:24:54'),
(3, 'Pinakbet', 'Masustansyang ginisang kalabasa, ampalaya, talong, at okra na may bagoong.', 'Side Dish', 45.00, 'assets/uploads/pinakbet.png', '2026-05-18 12:24:54'),
(4, 'Lechon Kawali', 'Lutong-crispy na tiyan ng baboy, perpekto sa mang tomas o suka.', 'Main Dish', 90.00, 'assets/uploads/lechon-kawali.png', '2026-05-18 12:24:54'),
(5, 'Leche Flan', 'Matamis at creamy na panghimagas na gawa sa gatas at pula ng itlog.', 'Dessert', 50.00, 'assets/uploads/leche-flan.png', '2026-05-18 12:24:54'),
(6, 'Ice Candy (Mango)', 'Pampalamig pagkatapos kumain na gawa sa totoong mangga.', 'Dessert', 10.00, 'assets/uploads/ice-candy.png', '2026-05-18 12:24:54'),
(7, 'Sago\'t Gulaman', 'Malamig na inumin na may matamis na arnibal, sago, at gulaman.', 'Drinks', 15.00, 'assets/uploads/sagat-gulaman.png', '2026-05-18 12:24:54');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `store_name` varchar(100) NOT NULL DEFAULT 'Aking Carenderia',
  `operating_hours` varchar(100) NOT NULL DEFAULT '6:00 AM - 8:00 PM',
  `contact_number` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `store_name`, `operating_hours`, `contact_number`, `address`, `updated_at`) VALUES
(1, 'Kusina de Carenderia', '6:00 AM - 8:00 PM', '09123456789', 'Talibon, Bohol', '2026-05-18 12:24:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `daily_menu`
--
ALTER TABLE `daily_menu`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_item_per_day` (`menu_item_id`,`available_date`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menu_item_id` (`menu_item_id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `daily_menu`
--
ALTER TABLE `daily_menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `daily_menu`
--
ALTER TABLE `daily_menu`
  ADD CONSTRAINT `daily_menu_ibfk_1` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `gallery`
--
ALTER TABLE `gallery`
  ADD CONSTRAINT `gallery_ibfk_1` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
