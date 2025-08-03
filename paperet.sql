-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 03, 2025 at 11:11 AM
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
(3, 6, 1, 'accepted', '2025-07-27 13:29:37', '2025-07-27 13:29:55'),
(4, 1, 7, 'pending', '2025-08-02 08:10:13', '2025-08-02 08:10:13');

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
(38, 1, 6, 'Chat Message', 'اوکی', 1, '2025-07-27 16:59:09', '1_6'),
(39, 6, 1, 'vvv', 'vvvvvvvvv', 1, '2025-07-28 14:33:13', '1_6'),
(40, 1, 6, 'Chat Message', 'what ?', 1, '2025-07-28 15:37:36', '1_6'),
(41, 1, 6, 'Chat Message', '000', 1, '2025-07-28 16:03:40', '1_6'),
(42, 7, 1, 'new', 'hiii', 1, '2025-08-02 11:36:21', '1_7'),
(43, 1, 7, 'Chat Message', 'hey', 0, '2025-08-02 11:36:58', '1_7');

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
(6, 1, '11', '111111', '../uploads/pdfs/1/pres_68862bdf7bded2.68989374.pdf', '2025-07-27 17:08:39'),
(7, 1, 'تست 2', 'تستی 2 هست', '../uploads/pdfs/1/pres_688765440f2b79.33440737.pdf', '2025-07-28 15:25:48');

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
(14, 6, 6, '2025-07-28 11:50:55'),
(16, 6, 7, '2025-07-28 11:56:19'),
(26, 7, 7, '2025-08-02 08:06:03');

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
  `reset_code` varchar(255) DEFAULT NULL,
  `reset_code_expires_at` datetime DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `is_google_user` tinyint(1) DEFAULT 0,
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

INSERT INTO `users` (`id`, `name`, `family`, `email`, `password`, `reset_code`, `reset_code_expires_at`, `google_id`, `is_google_user`, `profile_pic`, `university`, `birthdate`, `education`, `workplace`, `meeting_info`, `linkedin_url`, `x_url`, `google_scholar_url`, `github_url`, `website_url`, `custom_profile_link`, `availability_status`, `meeting_link`, `google_calendar`, `resume_pdf_path`, `intro_video_path`, `last_resume_update`, `biography`, `created_at`, `updated_at`) VALUES
(1, 'mahshid', 'khodsiani', 'm@m.com', '123', '$2y$10$AVS5Nck8JUBFZM.GhoDOlupM80j7sJD9iIoNzCwq3gwMM.VL.zfsS', '2025-08-03 10:01:31', NULL, 0, 'uploads/pics/1/profile_pic_687debc66fe75.jpg', 'isfahan', '0000-00-00', 'bachelor', '', 'Mon - Fri: 9:00 AM - 5:00 PM (CST)', '', '', '', 'https://www.linkedin.com/in/mahshid-khodsiani-27626835a', 'https://moonshid.com/', NULL, 'meeting_link', 'https://meet.google.com/cix-bcge-tuq', '', '../uploads/pdfs/1/user_1_687e06dfb3318.pdf', '../uploads/videos/1/user_1_68861bbc8c7a3.mp4', '2025-07-25 12:23:18', 'از htmlspecialchars() برای جلوگیری از XSS استفاده شده است.\r\n\r\nآیکون Font Awesome برای زیبایی بیشتر اضافه شده است.\r\n\r\nمحدودیت 500 کاراکتری برای متن بیوگرافی در نظر گرفته شده است.\r\n\r\n', '2025-07-19 15:03:35', '2025-08-03 07:31:31'),
(6, 'کوروش ', 'فکاری', 'k@m.com', '123', NULL, NULL, NULL, 0, 'uploads/pics/6/profile_pic_68875796932d5.jpg', '', '0000-00-00', '', 'hub', 'Mon - Fri: 9:00 AM - 5:00 PM (CST)', '', '', '', '', '', NULL, 'available', NULL, NULL, NULL, NULL, NULL, '', '2025-07-27 12:54:35', '2025-07-28 12:04:31'),
(7, 'katy', 'khanjani', 'kat@m.com', '123', NULL, NULL, NULL, 0, 'uploads/pics/7/profile_pic_688dbfe3c079d.png', '', '0000-00-00', '', '', 'Mon - Fri: 9:00 AM - 5:00 PM (CST)', '', '', '', '', '', NULL, 'busy', '', '', '../uploads/pdfs/7/user_7_688dbffd06220.pdf', NULL, '2025-08-02 11:06:10', '', '2025-08-02 07:35:41', '2025-08-02 07:36:29'),
(12, 'mahshid', 'khodsiani', 'mahshidkhodsiani2@gmail.com', '$2y$10$7.KCeM8aRb3ieWqAeKrTG.tpsKrHlGkOXhK6EprZlENvlxN4FF.wq', NULL, NULL, NULL, 0, 'uploads/pics/12/profile_pic_688f2442153ed.png', '', '0000-00-00', '', '', 'Mon - Fri: 9:00 AM - 5:00 PM (CST)', '', '', '', '', '', NULL, 'available', NULL, NULL, '../uploads/pdfs/12/user_12_688f270a832be.pdf', '../uploads/videos/12/user_12_688f270e756f7.mp4', NULL, '', '2025-08-03 08:56:01', '2025-08-03 09:08:30');

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
  ADD UNIQUE KEY `custom_profile_link` (`custom_profile_link`),
  ADD UNIQUE KEY `google_id` (`google_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `connections`
--
ALTER TABLE `connections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `presentations`
--
ALTER TABLE `presentations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `saved_presentations`
--
ALTER TABLE `saved_presentations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

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
