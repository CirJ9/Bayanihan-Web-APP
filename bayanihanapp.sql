-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 16, 2026 at 05:14 PM
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
-- Database: `bayanihanapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `admin_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `admin_id`, `created_at`) VALUES
(1, 'System Update', 'We have updated the rewards system!', 7, '2026-01-19 18:47:31');

-- --------------------------------------------------------

--
-- Table structure for table `communities`
--

CREATE TABLE `communities` (
  `community_id` int(11) NOT NULL,
  `municipality_name` varchar(120) NOT NULL,
  `region` varchar(100) DEFAULT NULL,
  `points_total` bigint(20) DEFAULT 0,
  `hours_total` bigint(20) DEFAULT 0,
  `members_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `communities`
--

INSERT INTO `communities` (`community_id`, `municipality_name`, `region`, `points_total`, `hours_total`, `members_count`, `created_at`) VALUES
(1, 'Naic, Cavite', 'Region IV-A', 0, 0, 0, '2026-01-19 14:47:13'),
(2, 'Trece Martires', 'Region IV-A', 0, 0, 0, '2026-01-19 14:47:13'),
(3, 'Indang, Cavite', 'Region IV-A', 0, 0, 0, '2026-01-19 14:47:13'),
(4, 'Maragondon, Cavite', 'Region IV-A', 0, 0, 0, '2026-01-19 14:47:13');

-- --------------------------------------------------------

--
-- Table structure for table `points`
--

CREATE TABLE `points` (
  `point_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `points_total` int(11) DEFAULT 0,
  `LEVEL` int(11) DEFAULT 1,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rewards`
--

CREATE TABLE `rewards` (
  `reward_id` int(11) NOT NULL,
  `reward_name` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `reward_image` varchar(255) DEFAULT 'gift.png',
  `points_required` int(11) DEFAULT 0,
  `stock` int(11) DEFAULT 999,
  `community_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rewards`
--

INSERT INTO `rewards` (`reward_id`, `reward_name`, `description`, `reward_image`, `points_required`, `stock`, `community_id`) VALUES
(1, '1kg Rice', 'Premium white rice, 1kg pack.', 'rice_bowl', 100, 49, NULL),
(2, 'Canned Goods Pack', 'Assorted canned goods (Sardines, Corned Beef).', 'inventory_2', 250, 30, NULL),
(3, 'School Supplies Kit', 'Notebooks, pens, and pencils for students.', 'school', 500, 20, NULL),
(4, 'Free Delivery Voucher', 'Free delivery for your next request.', 'local_shipping', 150, 99, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reward_redemption`
--

CREATE TABLE `reward_redemption` (
  `redeem_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reward_id` int(11) NOT NULL,
  `points_used` int(11) NOT NULL,
  `redeemed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_volunteers`
--

CREATE TABLE `task_volunteers` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'joined',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_volunteers`
--

INSERT INTO `task_volunteers` (`id`, `request_id`, `user_id`, `status`, `joined_at`) VALUES
(1, 7, 8, 'joined', '2026-01-19 19:41:20'),
(2, 6, 8, 'joined', '2026-01-19 19:41:50'),
(3, 5, 8, 'joined', '2026-01-19 19:41:56'),
(4, 4, 8, 'joined', '2026-01-19 19:41:59'),
(5, 3, 8, 'joined', '2026-01-19 19:42:01'),
(6, 8, 5, 'cancelled', '2026-01-19 19:52:03'),
(7, 9, 8, 'cancelled', '2026-01-19 19:53:22'),
(8, 10, 8, 'completed', '2026-01-19 20:09:03'),
(9, 11, 5, 'completed', '2026-01-19 20:21:57'),
(10, 12, 8, 'completed', '2026-01-19 20:23:25'),
(11, 13, 5, 'completed', '2026-01-19 20:25:20'),
(12, 14, 8, 'completed', '2026-01-19 20:26:04'),
(13, 15, 8, 'cancelled', '2026-01-19 20:28:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `community_id` int(11) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `role_title` varchar(100) DEFAULT 'Community Member',
  `bio` text DEFAULT NULL,
  `profile_img` varchar(255) DEFAULT 'user.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password_hash`, `phone`, `is_verified`, `community_id`, `role`, `role_title`, `bio`, `profile_img`, `created_at`) VALUES
(7, 'Cir', 'rictheraccoon@gmail.com', '$2y$10$Fm5yihw/tcEQZYIo6BLt5ecCUJjr8wlDeLWcMFif7mjusywCdupym', NULL, 1, 3, 'admin', 'Community Member', NULL, 'user.png', '2026-01-19 18:45:59'),
(9, 'test test', 'test@gmail.com', '$2y$10$wJ5sMIIb5hnQZmd5rYEfpuGMBtk8iMsa6YM6O6TT.GPCYM2mEnwLu', NULL, 0, 4, 'user', 'Community Member', NULL, 'user.png', '2026-06-16 15:13:57');

-- --------------------------------------------------------

--
-- Table structure for table `volunteer_requests`
--

CREATE TABLE `volunteer_requests` (
  `request_id` int(11) NOT NULL,
  `poster_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `reward_points` int(11) DEFAULT 0,
  `volunteers_needed` int(11) DEFAULT 1,
  `volunteers_accepted` int(11) DEFAULT 0,
  `status` varchar(50) DEFAULT 'open',
  `community_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `volunteer_request_reports`
--

CREATE TABLE `volunteer_request_reports` (
  `report_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `STATUS` enum('pending','reviewed','dismissed','action_taken') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `communities`
--
ALTER TABLE `communities`
  ADD PRIMARY KEY (`community_id`),
  ADD UNIQUE KEY `municipality_name` (`municipality_name`);

--
-- Indexes for table `points`
--
ALTER TABLE `points`
  ADD PRIMARY KEY (`point_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `rewards`
--
ALTER TABLE `rewards`
  ADD PRIMARY KEY (`reward_id`),
  ADD KEY `community_id` (`community_id`);

--
-- Indexes for table `reward_redemption`
--
ALTER TABLE `reward_redemption`
  ADD PRIMARY KEY (`redeem_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reward_id` (`reward_id`);

--
-- Indexes for table `task_volunteers`
--
ALTER TABLE `task_volunteers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `community_id` (`community_id`);

--
-- Indexes for table `volunteer_requests`
--
ALTER TABLE `volunteer_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `poster_id` (`poster_id`),
  ADD KEY `community_id` (`community_id`);

--
-- Indexes for table `volunteer_request_reports`
--
ALTER TABLE `volunteer_request_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `communities`
--
ALTER TABLE `communities`
  MODIFY `community_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `points`
--
ALTER TABLE `points`
  MODIFY `point_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `rewards`
--
ALTER TABLE `rewards`
  MODIFY `reward_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reward_redemption`
--
ALTER TABLE `reward_redemption`
  MODIFY `redeem_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `task_volunteers`
--
ALTER TABLE `task_volunteers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `volunteer_requests`
--
ALTER TABLE `volunteer_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `volunteer_request_reports`
--
ALTER TABLE `volunteer_request_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `points`
--
ALTER TABLE `points`
  ADD CONSTRAINT `points_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `rewards`
--
ALTER TABLE `rewards`
  ADD CONSTRAINT `rewards_ibfk_1` FOREIGN KEY (`community_id`) REFERENCES `communities` (`community_id`) ON DELETE SET NULL;

--
-- Constraints for table `reward_redemption`
--
ALTER TABLE `reward_redemption`
  ADD CONSTRAINT `reward_redemption_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reward_redemption_ibfk_2` FOREIGN KEY (`reward_id`) REFERENCES `rewards` (`reward_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`community_id`) REFERENCES `communities` (`community_id`) ON DELETE SET NULL;

--
-- Constraints for table `volunteer_requests`
--
ALTER TABLE `volunteer_requests`
  ADD CONSTRAINT `volunteer_requests_ibfk_1` FOREIGN KEY (`poster_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `volunteer_requests_ibfk_2` FOREIGN KEY (`community_id`) REFERENCES `communities` (`community_id`) ON DELETE CASCADE;

--
-- Constraints for table `volunteer_request_reports`
--
ALTER TABLE `volunteer_request_reports`
  ADD CONSTRAINT `volunteer_request_reports_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `volunteer_requests` (`request_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `volunteer_request_reports_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
