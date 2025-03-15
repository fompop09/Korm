-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 03, 2025 at 02:53 PM
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
-- Database: `school_personnel_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `personnel`
--

CREATE TABLE `personnel` (
  `personnel_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `personnel`
--

INSERT INTO `personnel` (`personnel_id`, `user_id`, `first_name`, `last_name`, `position`, `email`, `phone`, `address`, `position_id`) VALUES
(1, 4, 'นายวิรัตน์', 'หาดคำ', '', 'wirathadkham@gmail.com', '0956029737 ', '91 บ้านขี้นาคน้อย ม.7 ต.ตูม  อ.ปรางค์กู่  จ.ศรีสะเกษ 33170', 1),
(3, 5, 'member', 'member', '', 'wirathadkham@gmail.com', '0956029737', '91 บ้านขี้นาคน้อย ม.7', 1),
(4, 6, 'member1', 'member1', '', 'wirathadkham@gmail.com', '0956029737', '91 บ้านขี้นาคน้อย ม.7', 3),
(5, 1, 'admin', 'admin', '', 'admin@gmail.com', '0956029737', '91 บ้านขี้นาคน้อย ม.7', NULL),
(6, 5, 'member', 'member', '', 'wirathadkham@gmail.com', '0956029737', '91 บ้านขี้นาคน้อย ม.7', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `position_id` int(11) NOT NULL,
  `position_name` varchar(100) NOT NULL,
  `position_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`position_id`, `position_name`, `position_code`, `created_at`) VALUES
(1, 'ครู', '100', '2025-01-03 11:35:24'),
(2, 'ผู้บริหาร', '200', '2025-01-03 11:35:34'),
(3, 'บุคลากรทางการศึกษา', '300', '2025-01-03 11:35:54');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','teacher','staff') NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `image_path`, `created_at`, `last_login`) VALUES
(1, 'admin', '$2y$10$01g9rs9nnuTZflLfTJ0y8.mpA/gvqTzSRhwAjyO2FHNhIAGKloE6u', 'admin', 'uploads/6777c050838cf.png', '2025-01-03 10:34:33', '2025-01-03 20:47:42'),
(2, 'teacher1', '$2y$10$Alqf.NAw.wbDFGGvcUPRIuE.879vd4G5fQp3Vh5IbRwPb.Q/Z0sbW', 'teacher', 'uploads/6777c5adcbbb2.png', '2025-01-03 10:34:33', '2025-01-03 18:10:19'),
(3, 'staff1', '$2y$10$1v1GbQCtbvUSrq8B.W9ukOcokOkFVPPNURQ1KbLVA68vcdE.zA/O2', 'staff', 'uploads/6777c5e1df3ea.png', '2025-01-03 10:34:33', '2025-01-03 18:11:18'),
(4, 'wirat', '$2y$10$Yscl/v8LKk7eZgsb/ajocekzY96iSNseZ393m.RmO9K06DEIl/Iya', 'teacher', 'uploads/6777cc919f1b9.jpg', '2025-01-03 11:37:43', '2025-01-03 18:46:05'),
(5, 'member', '$2y$10$Z8m38pyYpbPrRV7EgF8Reef9ntizNQfhW9by6khHtV6ptS9yuKRcK', 'teacher', 'uploads/6777e9c110486.png', '2025-01-03 13:09:15', '2025-01-03 20:44:42'),
(6, 'member1', '$2y$10$/01nrB9z8Q4AObSmJFGwTOCmzrOoHdZUN.G12.db1JMqaFhf.FH4W', 'teacher', 'uploads/6777e1a8e75b7.png', '2025-01-03 13:10:00', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `personnel`
--
ALTER TABLE `personnel`
  ADD PRIMARY KEY (`personnel_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `position_id` (`position_id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`position_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `personnel`
--
ALTER TABLE `personnel`
  MODIFY `personnel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `personnel`
--
ALTER TABLE `personnel`
  ADD CONSTRAINT `personnel_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `personnel_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
