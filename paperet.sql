-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 27, 2025 at 04:30 PM
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
-- Database: `paperet`
--

-- --------------------------------------------------------

--
-- Table structure for table `connections`
--

CREATE TABLE `connections` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','accepted','declined') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `connections`
--

INSERT INTO `connections` (`id`, `sender_id`, `receiver_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 5, 'accepted', '2025-07-27 12:38:30', '2025-07-27 12:45:16'),
(2, 6, 5, 'pending', '2025-07-27 12:54:56', '2025-07-27 12:54:56'),
(3, 6, 1, 'accepted', '2025-07-27 13:29:37', '2025-07-27 13:29:55');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_at` datetime NOT NULL,
  `conversation_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `subject`, `content`, `is_read`, `sent_at`, `conversation_id`) VALUES
(34, 6, 1, 'سلام', 'سلام من مهشیدم', 1, '2025-07-27 16:58:03', '1_6'),
(35, 1, 6, 'Chat Message', 'سلام منم مهشیدم اخه', 1, '2025-07-27 16:58:18', '1_6'),
(36, 1, 6, 'Chat Message', 'عه یعنی درست شد ؟', 1, '2025-07-27 16:58:31', '1_6'),
(37, 6, 1, 'Chat Message', 'اره انگار درست شد', 1, '2025-07-27 16:58:59', '1_6'),
(38, 1, 6, 'Chat Message', 'اوکی', 1, '2025-07-27 16:59:09', '1_6');

-- --------------------------------------------------------

--
-- Table structure for table `presentations`
--

CREATE TABLE `presentations` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `presentations`
--

INSERT INTO `presentations` (`id`, `user_id`, `title`, `description`, `file_path`, `created_at`) VALUES
(5, 5, 'زززززززززز', 'ززززززززززز', '../uploads/pdfs/5/pres_6880a3c0c47458.46519137.pdf', '2025-07-23 12:26:32'),
(6, 1, '11', '111111', '../uploads/pdfs/1/pres_68862bdf7bded2.68989374.pdf', '2025-07-27 17:08:39');

-- --------------------------------------------------------

--
-- Table structure for table `saved_presentations`
--

CREATE TABLE `saved_presentations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `presentation_id` int(10) UNSIGNED NOT NULL,
  `saved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `saved_presentations`
--

INSERT INTO `saved_presentations` (`id`, `user_id`, `presentation_id`, `saved_at`) VALUES
(13, 1, 6, '2025-07-27 14:30:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(12) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `family` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `university` varchar(255) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `education` varchar(255) DEFAULT NULL,
  `workplace` varchar(255) DEFAULT NULL,
  `meeting_info` text DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `x_url` varchar(255) DEFAULT NULL,
  `google_scholar_url` varchar(255) DEFAULT NULL,
  `github_url` varchar(255) DEFAULT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `custom_profile_link` varchar(255) DEFAULT NULL,
  `availability_status` enum('available','busy','meeting_link','google_calendar_embed') DEFAULT 'available',
  `meeting_link` varchar(255) DEFAULT NULL,
  `google_calendar` varchar(255) DEFAULT NULL,
  `resume_pdf_path` varchar(255) DEFAULT NULL,
  `intro_video_path` varchar(255) DEFAULT NULL,
  `last_resume_update` varchar(255) DEFAULT NULL,
  `biography` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `family`, `email`, `password`, `profile_pic`, `university`, `birthdate`, `education`, `workplace`, `meeting_info`, `linkedin_url`, `x_url`, `google_scholar_url`, `github_url`, `website_url`, `custom_profile_link`, `availability_status`, `meeting_link`, `google_calendar`, `resume_pdf_path`, `intro_video_path`, `last_resume_update`, `biography`, `created_at`, `updated_at`) VALUES
(1, 'mahshid', 'khodsiani', 'm@m.com', '123', 'uploads/pics/1/profile_pic_687debc66fe75.jpg', 'isfahan', '0000-00-00', 'bachelor', '', 'Mon - Fri: 9:00 AM - 5:00 PM (CST)', '', '', '', 'https://www.linkedin.com/in/mahshid-khodsiani-27626835a', 'https://moonshid.com/', NULL, 'meeting_link', 'https://meet.google.com/cix-bcge-tuq', '', '../uploads/pdfs/1/user_1_687e06dfb3318.pdf', '../uploads/videos/1/user_1_68861bbc8c7a3.mp4', '2025-07-25 12:23:18', 'از htmlspecialchars() برای جلوگیری از XSS استفاده شده است.\r\n\r\nآیکون Font Awesome برای زیبایی بیشتر اضافه شده است.\r\n\r\nمحدودیت 500 کاراکتری برای متن بیوگرافی در نظر گرفته شده است.\r\n\r\n', '2025-07-19 15:03:35', '2025-07-27 12:29:48'),
(2, 'برنامه نویسی', 'اسپادانا', 's.nader@artamoz.com', '123', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-19 15:03:35', '2025-07-19 15:03:35'),
(3, 'برنامه نویسی', 'اسپادانا', 'a@artamoz.com', '123', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-19 15:03:35', '2025-07-19 15:03:35'),
(4, 'برنامه نویسی', 'اسپادانا', 's@artamoz.com', '123', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-19 15:03:35', '2025-07-19 15:03:35'),
(5, 'مهنوش', 'خودسیانی', 'meh@m.com', '123', 'uploads/pics/5/profile_pic_68834c584ce1a.png', '', '0000-00-00', 'master', '', 'Mon - Fri: 9:00 AM - 5:00 PM (CST)', '', '', '', '', '', NULL, 'busy', '', '', NULL, NULL, '2025-07-25 12:51:49', '', '2025-07-25 09:17:32', '2025-07-25 09:22:26'),
(6, 'کوروش ', 'فکاری', 'k@m.com', '123', 'images/2.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-27 12:54:35', '2025-07-27 12:54:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `connections`
--
ALTER TABLE `connections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sender_id` (`sender_id`,`receiver_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `presentations`
--
ALTER TABLE `presentations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `saved_presentations`
--
ALTER TABLE `saved_presentations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`presentation_id`),
  ADD KEY `presentation_id` (`presentation_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `custom_profile_link` (`custom_profile_link`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `connections`
--
ALTER TABLE `connections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `presentations`
--
ALTER TABLE `presentations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `saved_presentations`
--
ALTER TABLE `saved_presentations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `connections`
--
ALTER TABLE `connections`
  ADD CONSTRAINT `connections_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `connections_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `saved_presentations`
--
ALTER TABLE `saved_presentations`
  ADD CONSTRAINT `saved_presentations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_presentations_ibfk_2` FOREIGN KEY (`presentation_id`) REFERENCES `presentations` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
