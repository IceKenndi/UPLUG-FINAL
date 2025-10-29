-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 30, 2025 at 12:19 AM
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
-- Database: `db_uplug`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `admin_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `verified` tinyint(1) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expiry` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`admin_id`, `email`, `password_hash`, `verified`, `reset_token`, `reset_expiry`) VALUES
(1, 'uplug.noreply@gmail.com', '$2y$10$m8WD5SOwvxf6sCmc6UqTZ..NPWi1e4VWLn9fEP6oseOzh/CWiqZqO', 1, '1dad0f45e7967318c54d16d63148d95d', 1761780097);

-- --------------------------------------------------------

--
-- Table structure for table `faculty_users`
--

CREATE TABLE `faculty_users` (
  `seq_id` int(11) NOT NULL,
  `faculty_id` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `first_name` varchar(128) NOT NULL,
  `last_name` varchar(128) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `department` enum('SHS','CITE','CCJE','CAHS','CAS','CEA','CELA','CMA','COL') NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `verified` tinyint(1) DEFAULT 0,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expiry` int(11) DEFAULT NULL,
  `create_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` varchar(255) NOT NULL,
  `sender_type` enum('student','faculty') NOT NULL,
  `receiver_id` varchar(255) NOT NULL,
  `receiver_type` enum('student','faculty') NOT NULL,
  `content` text NOT NULL,
  `sent_at` datetime DEFAULT current_timestamp(),
  `seen` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `sender_id`, `sender_type`, `receiver_id`, `receiver_type`, `content`, `sent_at`, `seen`) VALUES
(113, 'STU-25-CITE', 'student', 'STU-27-CAHS', 'student', 'hi bbgirl', '2025-10-26 17:38:33', 1),
(114, 'STU-27-CAHS', 'student', 'STU-25-CITE', 'student', 'wsg', '2025-10-26 17:38:40', 1),
(115, 'STU-27-CAHS', 'student', 'STU-25-CITE', 'student', 'wsg', '2025-10-26 17:38:43', 1),
(116, 'STU-27-CAHS', 'student', 'STU-25-CITE', 'student', 'wsg', '2025-10-26 17:38:44', 1),
(117, 'STU-27-CAHS', 'student', 'STU-25-CITE', 'student', 'wsg', '2025-10-26 17:38:46', 1),
(118, 'STU-27-CAHS', 'student', 'STU-25-CITE', 'student', 'wsg', '2025-10-26 17:38:46', 1),
(119, 'STU-28-CITE', 'student', 'STU-25-CITE', 'student', 'Yo', '2025-10-26 22:40:29', 1),
(120, 'STU-28-CITE', 'student', 'STU-25-CITE', 'student', 'Tite', '2025-10-26 22:40:31', 1),
(121, 'STU-28-CITE', 'student', 'STU-25-CITE', 'student', 'eut', '2025-10-26 22:40:33', 1),
(122, 'STU-28-CITE', 'student', 'STU-25-CITE', 'student', '😁', '2025-10-26 23:04:46', 1),
(123, 'STU-29-CITE', 'student', 'STU-26-CITE', 'student', 'low hey!', '2025-10-28 11:27:45', 0),
(124, 'STU-31-CITE', 'student', 'STU-25-CITE', 'student', 'lllalallaal', '2025-10-28 11:31:49', 1),
(125, 'STU-25-CITE', 'student', 'STU-31-CITE', 'student', 'lalaallalalala', '2025-10-28 11:32:04', 1),
(126, 'STU-28-COL', 'student', 'STU-34-CITE', 'student', 'yo', '2025-10-30 02:04:47', 0),
(127, 'STU-35-CITE', 'student', 'STU-36-CITE', 'student', 'yo', '2025-10-30 06:59:13', 1),
(128, 'STU-36-CITE', 'student', 'STU-35-CITE', 'student', 'wsg', '2025-10-30 06:59:32', 1),
(129, 'STU-35-CITE', 'student', 'STU-36-CITE', 'student', 'cs?', '2025-10-30 06:59:43', 1),
(130, 'STU-36-CITE', 'student', 'STU-35-CITE', 'student', 'this nigga bro', '2025-10-30 07:03:14', 1);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author_id` varchar(255) NOT NULL,
  `post_type` varchar(255) NOT NULL,
  `author_department` enum('SHS','CITE','CCJE','CAHS','CAS','CEA','CELA','CMA','COL') NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `edited_at` datetime DEFAULT NULL,
  `toast_status` tinyint(1) DEFAULT 0,
  `toast_message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`post_id`, `title`, `content`, `author_id`, `post_type`, `author_department`, `create_date`, `edited_at`, `toast_status`, `toast_message`) VALUES
(90, 'test', 'asd', 'STU-34-CITE', 'official', 'CITE', '2025-10-29 22:29:01', NULL, 0, 'CITE Student - Kenneth Dela Cruz posted a new official post!'),
(93, 'asd', 'asd', 'STU-35-CITE', 'official', 'CITE', '2025-10-29 22:58:08', NULL, 1, 'CITE Student - Kenneth Dela Cruz posted a new official post!'),
(95, 'test', 'test', 'STU-35-COL', 'official', 'COL', '2025-10-29 23:08:19', NULL, 1, 'COL Student - Kenneth Dela Cruz posted a new official post!'),
(97, 'test', 's', 'STU-36-CITE', 'official', 'CITE', '2025-10-29 23:18:43', NULL, 1, 'CITE Student - Zhyll Aguinaldo Aguinaldo posted a new official post!');

-- --------------------------------------------------------

--
-- Table structure for table `student_users`
--

CREATE TABLE `student_users` (
  `seq_id` int(11) NOT NULL,
  `student_id` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `first_name` varchar(128) NOT NULL,
  `last_name` varchar(128) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `department` enum('SHS','CITE','CCJE','CAHS','CAS','CEA','CELA','CMA','COL') NOT NULL,
  `profile_picture` varchar(255) DEFAULT 'assets/images/client/default/profile.png',
  `verified` tinyint(1) DEFAULT 0,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expiry` int(11) DEFAULT NULL,
  `create_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_users`
--

INSERT INTO `student_users` (`seq_id`, `student_id`, `full_name`, `first_name`, `last_name`, `email`, `password_hash`, `department`, `profile_picture`, `verified`, `reset_token`, `reset_expiry`, `create_date`) VALUES
(27, 'STU-27-CAHS', 'Janel Romero', 'Janel', 'Romero', 'jado.romero.up@phinmaed.com', '$2y$10$99NLhqooVZSHSHt9BeCW0up2YUQBVMErrBRXFeIX2uXxAl0lHm2u6', 'CAHS', 'assets/images/student-profiles/CAHS/STU-27-CAHS.png', 1, NULL, NULL, '2025-10-30 03:25:18'),
(35, 'STU-35-COL', 'Kenneth Dela Cruz', 'Kenneth', 'Dela Cruz', 'keli.delacruz.up@phinmaed.com', '$2y$10$Msi7FP/Bosh00yCJRQG7QusD8g9l3eYQywrc6VWIgIBecRGQHxpBu', 'COL', 'assets/images/client/default/profile.png', 1, NULL, NULL, '2025-10-30 05:42:09'),
(36, 'STU-36-CITE', 'Zhyll Aguinaldo Aguinaldo', 'Zhyll Aguinaldo', 'Aguinaldo', 'zhra.aguinaldo.up@phinmaed.com', '$2y$10$.N2GkpnZT9Y7Zag.TZGo9uf6rPtGQ8InLI.ibW8YBPbjxYO52JJNe', 'CITE', 'assets/images/student-profiles/CITE/STU-36-CITE.png', 1, '082b1e43b7148e6a3a0e6777c91c84e39e4cadb73ba5c6ae4d8054f5a54ed389', 1761780455, '2025-10-30 06:09:14');

-- --------------------------------------------------------

--
-- Table structure for table `toast_acknowledgments`
--

CREATE TABLE `toast_acknowledgments` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `post_id` varchar(50) DEFAULT NULL,
  `acknowledged_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `toast_acknowledgments`
--

INSERT INTO `toast_acknowledgments` (`id`, `user_id`, `post_id`, `acknowledged_at`) VALUES
(30, 'STU-25-CITE', '46', '2025-10-26 01:53:12'),
(31, 'STU-25-CITE', '47', '2025-10-26 02:12:38'),
(32, 'STU-25-CITE', '54', '2025-10-26 02:18:49'),
(33, 'STU-25-CITE', '<br />\n<b>Warning</b>:  Undefined array key ', '2025-10-26 02:19:18'),
(34, 'STU-25-CITE', '56', '2025-10-26 02:20:32'),
(35, 'STU-25-CITE', 'reset_68fd1545b61504.53059541', '2025-10-26 02:22:04'),
(36, 'STU-25-CITE', 'upload_68fd1551151848.99933733', '2025-10-26 02:22:12'),
(37, 'STU-25-CITE', '58', '2025-10-26 02:26:09'),
(38, 'STU-27-CAHS', '56', '2025-10-26 17:36:27'),
(39, 'STU-27-CAHS', '58', '2025-10-26 17:36:27'),
(40, 'STU-27-CAHS', 'upload_68fdebc30f8820.72328721', '2025-10-26 17:37:09'),
(41, 'STU-27-CAHS', '<br />\n<b>Warning</b>:  Undefined array key ', '2025-10-26 17:37:12'),
(42, 'STU-27-CAHS', '61', '2025-10-26 17:39:24'),
(43, 'STU-28-CITE', '56', '2025-10-26 22:35:42'),
(44, 'STU-28-CITE', '58', '2025-10-26 22:35:42'),
(45, 'STU-28-CITE', '61', '2025-10-26 22:35:43'),
(46, 'STU-28-CITE', 'reset_68fe320a7c7ea9.07503976', '2025-10-26 22:37:12'),
(49, 'STU-25-CITE', '63', '2025-10-26 23:09:12'),
(50, 'STU-31-CITE', '56', '2025-10-28 11:32:08'),
(51, 'STU-31-CITE', '58', '2025-10-28 11:32:09'),
(52, 'STU-31-CITE', '61', '2025-10-28 11:32:10'),
(54, 'STU-31-CITE', '63', '2025-10-28 11:32:11'),
(55, 'STU-28-CITE', '66', '2025-10-28 11:39:21'),
(56, 'STU-25-CITE', '62', '2025-10-28 13:39:49'),
(57, 'STU-25-CITE', '66', '2025-10-28 13:39:50'),
(58, 'STU-25-CITE', '56', '2025-10-28 13:40:45'),
(59, 'STU-25-CITE', '58', '2025-10-28 13:40:46'),
(60, 'STU-25-CITE', '62', '2025-10-28 13:40:48'),
(61, 'STU-25-CITE', '63', '2025-10-28 13:40:49'),
(62, 'STU-25-CITE', '66', '2025-10-28 13:40:50'),
(63, 'STU-25-CITE', '56', '2025-10-28 13:42:19'),
(64, 'STU-25-CITE', '58', '2025-10-28 13:42:20'),
(65, 'STU-25-CITE', '62', '2025-10-28 13:42:21'),
(66, 'STU-25-CITE', '63', '2025-10-28 13:42:21'),
(67, 'STU-25-CITE', '66', '2025-10-28 13:42:21'),
(68, 'STU-25-CITE', '56', '2025-10-28 13:44:59'),
(69, 'STU-25-CITE', '58', '2025-10-28 13:45:00'),
(70, 'STU-25-CITE', '62', '2025-10-28 13:45:00'),
(71, 'STU-25-CITE', '63', '2025-10-28 13:45:00'),
(72, 'STU-25-CITE', '66', '2025-10-28 13:45:01'),
(73, 'STU-28-COL', 'reset_6901280a3c1b05.96023922', '2025-10-29 04:31:09'),
(74, 'STU-33-CITE', '68', '2025-10-29 11:13:03'),
(75, 'STU-33-CITE', 'upload_6901951dcc3c77.68528270', '2025-10-29 12:20:40'),
(76, 'STU-33-CITE', 'upload_690196078dab86.64662355', '2025-10-29 12:20:42'),
(77, 'STU-33-CITE', 'upload_690197aba4ae57.91317504', '2025-10-29 12:27:38'),
(78, 'STU-33-CITE', '<br />\n<b>Warning</b>:  Undefined array key ', '2025-10-29 12:27:38'),
(79, 'STU-33-CITE', 'upload_690197b1a90979.26325790', '2025-10-29 12:27:39'),
(80, 'STU-33-CITE', '<br />\n<b>Warning</b>:  Undefined array key ', '2025-10-29 12:27:39'),
(81, 'STU-33-CITE', 'upload_690197bf59d130.79142590', '2025-10-29 12:27:47'),
(82, 'STU-33-CITE', '<br />\n<b>Warning</b>:  Undefined array key ', '2025-10-29 12:27:48'),
(83, 'STU-33-CITE', '<br />\n<b>Warning</b>:  Undefined array key ', '2025-10-29 20:10:42'),
(84, 'STU-33-CITE', 'upload_69019a15a4b6f3.39518522', '2025-10-29 20:10:43'),
(85, 'STU-33-CITE', 'upload_69019ca419edd0.47551291', '2025-10-29 20:10:43'),
(86, 'STU-33-CITE', 'upload_69019ca5d231c0.59562777', '2025-10-29 20:10:43'),
(87, 'STU-33-CITE', 'upload_6901a01e88b7a3.29543254', '2025-10-29 20:10:44'),
(88, 'STU-33-CITE', 'upload_6901a039347309.59499433', '2025-10-29 20:10:44'),
(89, 'STU-33-CITE', 'upload_6901a0589b4940.53755676', '2025-10-29 20:10:45'),
(90, 'STU-34-CITE', 'upload_6902067f56ee53.32589690', '2025-10-29 20:20:18'),
(91, 'STU-34-CITE', 'upload_69021ea65af121.59592960', '2025-10-29 23:16:24'),
(92, 'STU-34-CITE', 'upload_69021f01e7f024.35140381', '2025-10-29 23:16:24'),
(93, 'STU-34-CITE', 'upload_690229c75b74f0.78419021', '2025-10-29 23:16:25'),
(94, 'STU-34-CITE', '<br />\n<b>Warning</b>:  Undefined array key ', '2025-10-29 23:21:51'),
(95, 'STU-28-COL', '90', '2025-10-30 02:04:49'),
(96, 'FAC-6-CITE', '90', '2025-10-30 02:11:08'),
(97, 'STU-35-CITE', '90', '2025-10-30 05:42:59'),
(98, 'STU-35-CITE', '90', '2025-10-30 05:44:42'),
(99, 'STU-35-CITE', '90', '2025-10-30 05:47:01'),
(100, 'STU-35-CITE', '90', '2025-10-30 05:47:18'),
(101, 'STU-35-CITE', '90', '2025-10-30 05:49:25'),
(102, 'STU-35-CITE', '90', '2025-10-30 05:52:02'),
(103, 'STU-36-CITE', '93', '2025-10-30 06:58:17'),
(104, 'STU-35-COL', '93', '2025-10-30 07:04:26'),
(105, 'STU-36-CITE', '94', '2025-10-30 07:04:53'),
(106, 'STU-36-CITE', '95', '2025-10-30 07:08:26'),
(108, 'STU-35-COL', 'upload_69029d916da5b0.78010729', '2025-10-30 07:12:12'),
(109, 'STU-35-COL', '<br />\n<b>Warning</b>:  Undefined array key ', '2025-10-30 07:12:12'),
(110, 'STU-35-COL', 'upload_69029e631fa164.07414069', '2025-10-30 07:12:13'),
(111, 'STU-36-CITE', '93', '2025-10-30 07:14:07'),
(112, 'STU-36-CITE', '93', '2025-10-30 07:14:13'),
(113, 'STU-36-CITE', '93', '2025-10-30 07:14:16'),
(114, 'STU-36-CITE', '93', '2025-10-30 07:14:18'),
(115, 'STU-36-CITE', '95', '2025-10-30 07:14:18'),
(116, 'STU-36-CITE', 'upload_69029f25d69236.79431536', '2025-10-30 07:14:25'),
(117, 'STU-36-CITE', '93', '2025-10-30 07:14:29'),
(118, 'STU-36-CITE', '95', '2025-10-30 07:14:29'),
(119, 'STU-36-CITE', '93', '2025-10-30 07:15:56'),
(120, 'STU-36-CITE', '95', '2025-10-30 07:15:56'),
(121, 'STU-36-CITE', '93', '2025-10-30 07:15:58'),
(122, 'STU-36-CITE', '93', '2025-10-30 07:16:09'),
(123, 'STU-36-CITE', '95', '2025-10-30 07:16:11'),
(124, 'STU-36-CITE', '93', '2025-10-30 07:16:15'),
(125, 'STU-36-CITE', '95', '2025-10-30 07:16:15'),
(126, 'STU-36-CITE', '93', '2025-10-30 07:16:31'),
(127, 'STU-36-CITE', '95', '2025-10-30 07:16:32'),
(128, 'STU-36-CITE', '93', '2025-10-30 07:16:43'),
(129, 'STU-36-CITE', '95', '2025-10-30 07:16:43');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `faculty_users`
--
ALTER TABLE `faculty_users`
  ADD PRIMARY KEY (`faculty_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `seq_id` (`seq_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`);

--
-- Indexes for table `student_users`
--
ALTER TABLE `student_users`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `seq_id` (`seq_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `toast_acknowledgments`
--
ALTER TABLE `toast_acknowledgments`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `faculty_users`
--
ALTER TABLE `faculty_users`
  MODIFY `seq_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=98;

--
-- AUTO_INCREMENT for table `student_users`
--
ALTER TABLE `student_users`
  MODIFY `seq_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `toast_acknowledgments`
--
ALTER TABLE `toast_acknowledgments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `expire_posts` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-10-30 06:02:30' ON COMPLETION NOT PRESERVE ENABLE DO UPDATE posts
  SET toast_status = 0
  WHERE toast_status != 0
    AND create_date <= NOW() - INTERVAL 24 HOUR$$

CREATE DEFINER=`root`@`localhost` EVENT `expire_reset_tokens` ON SCHEDULE EVERY 1 MINUTE STARTS '2025-10-30 06:42:34' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
  UPDATE student_users
  SET reset_token = NULL,
      reset_expiry = NULL
  WHERE reset_token IS NOT NULL
    AND reset_expiry IS NOT NULL
    AND reset_expiry <= UNIX_TIMESTAMP(NOW());

  UPDATE admin_users
  SET reset_token = NULL,
      reset_expiry = NULL
  WHERE reset_token IS NOT NULL
    AND reset_expiry IS NOT NULL
    AND reset_expiry <= UNIX_TIMESTAMP(NOW());

  UPDATE faculty_users
  SET reset_token = NULL,
      reset_expiry = NULL
  WHERE reset_token IS NOT NULL
    AND reset_expiry IS NOT NULL
    AND reset_expiry <= UNIX_TIMESTAMP(NOW());
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
