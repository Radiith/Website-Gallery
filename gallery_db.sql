-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 03, 2026 at 04:48 PM
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
-- Database: `gallery_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `color` varchar(7) DEFAULT '#667eea',
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `color`, `user_id`, `created_at`) VALUES
(1, 'Sports Day', 'sports-day', '#ef4444', NULL, '2026-01-22 07:41:56'),
(2, 'Group Study', 'group-study', '#3b82f6', NULL, '2026-01-22 07:41:56'),
(3, 'Art Exhibition', 'art-exhibition', '#8b5cf6', NULL, '2026-01-22 07:41:56'),
(4, 'School Trip', 'school-trip', '#10b981', NULL, '2026-01-22 07:41:56'),
(5, 'Graduation', 'graduation', '#f59e0b', NULL, '2026-01-22 07:41:56'),
(6, 'Festival', 'festival', '#ec4899', NULL, '2026-01-22 07:41:56'),
(13, 'My FineShyt', 'my-fineshyt', '#ec4899', 12, '2026-01-28 05:07:00');

-- --------------------------------------------------------

--
-- Table structure for table `featured_images`
--

CREATE TABLE `featured_images` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `display_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `featured_images`
--

INSERT INTO `featured_images` (`id`, `filename`, `display_order`) VALUES
(1, 'minji.jpg', 1),
(2, 'idol-1.jpg\r\n', 2),
(3, 'idol-2.jpg', 3);

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `upload_date` date NOT NULL,
  `likes` int(11) DEFAULT 0,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`id`, `name`, `filename`, `category_id`, `upload_date`, `likes`, `user_id`, `created_at`) VALUES
(17, 'Minji', '6979998582b20_1769576837.jpg', 13, '2026-01-28', 0, 12, '2026-01-28 05:07:17'),
(18, 'Han So Hee', '6979999ec3b53_1769576862.jpg', 13, '2026-01-28', 0, 12, '2026-01-28 05:07:42'),
(19, 'Go younjung', '697999e852ea0_1769576936.jpg', 13, '2026-01-28', 0, 12, '2026-01-28 05:08:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `role` varchar(20) DEFAULT 'user',
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `fullname`, `created_at`, `last_login`, `role`, `profile_picture`, `bio`) VALUES
(12, 'Arsya', '$2y$10$zNzXYIU6PBNhTufCbX1lpeUHEFSHtnL96awlDZGPqsczTH49ZEbUO', 'ardimas.arsya1@gmail.com', 'Radithya Arsya', '2026-01-28 05:04:48', NULL, 'user', 'avatar_12_1769576759.jpg', 'Past Is The Past, Be A Better Person, Be A Better Human\r\n'),
(13, 'Waki', '$2y$10$DCC5JRkfv12M84b3ZWZbHewB21U7MqsZKFlZhtqpp75spdp7vhFVm', 'asdjahdiua@gmail.com', 'Waki Sange', '2026-02-03 15:46:16', NULL, 'user', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `featured_images`
--
ALTER TABLE `featured_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `featured_images`
--
ALTER TABLE `featured_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `images`
--
ALTER TABLE `images`
  ADD CONSTRAINT `images_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `images_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
