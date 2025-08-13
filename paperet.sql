-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 13, 2025 at 01:35 PM
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
(9, 12, 7, 'pending', '2025-08-08 11:49:06', '2025-08-08 11:49:06'),
(11, 12, 13, 'accepted', '2025-08-08 11:52:21', '2025-08-08 11:55:13'),
(12, 12, 6, 'pending', '2025-08-08 11:52:31', '2025-08-08 11:52:31'),
(13, 12, 1, 'pending', '2025-08-08 11:53:43', '2025-08-08 11:53:43'),
(14, 13, 6, 'pending', '2025-08-08 11:55:28', '2025-08-08 11:55:28'),
(15, 13, 7, 'pending', '2025-08-08 11:55:32', '2025-08-08 11:55:32'),
(18, 13, 14, 'declined', '2025-08-13 07:27:29', '2025-08-13 07:27:37'),
(19, 14, 12, 'pending', '2025-08-13 07:29:45', '2025-08-13 07:29:45'),
(20, 14, 7, 'pending', '2025-08-13 07:29:59', '2025-08-13 07:29:59');

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
(43, 1, 7, 'Chat Message', 'hey', 0, '2025-08-02 11:36:58', '1_7'),
(44, 13, 14, '', 'ddd', 1, '2025-08-13 10:33:17', '13_14'),
(45, 14, 13, 'Chat Message', 'heeey', 1, '2025-08-13 10:57:50', '13_14'),
(46, 14, 7, '', 'ddd', 0, '2025-08-13 11:00:05', '7_14');

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
  `keywords` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `presentations`
--

INSERT INTO `presentations` (`id`, `user_id`, `title`, `description`, `file_path`, `keywords`, `created_at`) VALUES
(5, 5, 'زززززززززز', 'ززززززززززز', '../uploads/pdfs/5/pres_6880a3c0c47458.46519137.pdf', NULL, '2025-07-23 12:26:32'),
(6, 1, '11', '111111', '../uploads/pdfs/1/pres_68862bdf7bded2.68989374.pdf', NULL, '2025-07-27 17:08:39'),
(7, 1, 'تست 2', 'تستی 2 هست', '../uploads/pdfs/1/pres_688765440f2b79.33440737.pdf', NULL, '2025-07-28 15:25:48'),
(8, 13, 'جدید', 'جدید جدید ', '../uploads/pdfs/13/pres_6891b412c0a6e4.08854401.pdf', NULL, '2025-08-05 11:04:42'),
(9, 13, 'تستی 3', 'تستی 3 هست', '../uploads/pdfs/13/pres_6891b97fdaf942.06475873.pdf', NULL, '2025-08-05 11:27:51'),
(10, 14, '111', '11111111', '../uploads/pdfs/14/pres_689c3e0adfefd5.57185269.pdf', NULL, '2025-08-13 10:56:02'),
(11, 14, 'e', 'e', '../uploads/pdfs/14/pres_689c3e1560ad14.22268135.mp4', NULL, '2025-08-13 10:56:13');

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
(26, 7, 7, '2025-08-02 08:06:03'),
(29, 13, 7, '2025-08-05 09:30:28'),
(31, 13, 6, '2025-08-08 10:55:16'),
(32, 12, 9, '2025-08-08 11:26:07');

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
  `cover_photo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `email_verification_token` varchar(255) DEFAULT NULL,
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
  `hide_resume` int(1) NOT NULL DEFAULT 0,
  `biography` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `family`, `email`, `password`, `reset_code`, `reset_code_expires_at`, `google_id`, `is_google_user`, `profile_pic`, `cover_photo`, `is_active`, `email_verification_token`, `university`, `birthdate`, `education`, `workplace`, `meeting_info`, `linkedin_url`, `x_url`, `google_scholar_url`, `github_url`, `website_url`, `custom_profile_link`, `availability_status`, `meeting_link`, `google_calendar`, `resume_pdf_path`, `intro_video_path`, `last_resume_update`, `hide_resume`, `biography`, `created_at`, `updated_at`) VALUES
(1, 'mahshid', 'khodsiani', 'm@m.com', '123', '$2y$10$AVS5Nck8JUBFZM.GhoDOlupM80j7sJD9iIoNzCwq3gwMM.VL.zfsS', '2025-08-03 10:01:31', NULL, 0, 'uploads/pics/1/profile_pic_687debc66fe75.jpg', NULL, 0, NULL, 'isfahan', '0000-00-00', 'bachelor', '', 'Mon - Fri: 9:00 AM - 5:00 PM (CST)', '', '', '', 'https://www.linkedin.com/in/mahshid-khodsiani-27626835a', 'https://moonshid.com/', NULL, 'meeting_link', 'https://meet.google.com/cix-bcge-tuq', '', '../uploads/pdfs/1/user_1_687e06dfb3318.pdf', '../uploads/videos/1/user_1_68861bbc8c7a3.mp4', '2025-07-25 12:23:18', 0, 'از htmlspecialchars() برای جلوگیری از XSS استفاده شده است.\r\n\r\nآیکون Font Awesome برای زیبایی بیشتر اضافه شده است.\r\n\r\nمحدودیت 500 کاراکتری برای متن بیوگرافی در نظر گرفته شده است.\r\n\r\n', '2025-07-19 15:03:35', '2025-08-03 07:31:31'),
(6, 'کوروش ', 'فکاری', 'k@m.com', '123', NULL, NULL, NULL, 0, 'uploads/pics/6/profile_pic_68875796932d5.jpg', NULL, 0, NULL, '', '0000-00-00', '', 'hub', 'Mon - Fri: 9:00 AM - 5:00 PM (CST)', '', '', '', '', '', NULL, 'available', NULL, NULL, NULL, NULL, NULL, 0, '', '2025-07-27 12:54:35', '2025-07-28 12:04:31'),
(7, 'katy', 'khanjani', 'kat@m.com', '123', NULL, NULL, NULL, 0, 'uploads/pics/7/profile_pic_688dbfe3c079d.png', NULL, 0, NULL, '', '0000-00-00', '', '', 'Mon - Fri: 9:00 AM - 5:00 PM (CST)', '', '', '', '', '', NULL, 'busy', '', '', '../uploads/pdfs/7/user_7_688dbffd06220.pdf', NULL, '2025-08-02 11:06:10', 0, '', '2025-08-02 07:35:41', '2025-08-02 07:36:29'),
(12, 'mahshid', 'khodsiani', 'mahshidkhodsiani2@gmail.com', '$2y$10$7.KCeM8aRb3ieWqAeKrTG.tpsKrHlGkOXhK6EprZlENvlxN4FF.wq', NULL, NULL, NULL, 0, 'uploads/pics/12/profile_pic_688f2442153ed.png', NULL, 0, NULL, '', '0000-00-00', '', '', 'Mon - Fri: 9:00 AM - 5:00 PM (CST)', '', '', '', '', '', NULL, 'available', NULL, NULL, '../uploads/pdfs/12/user_12_688f270a832be.pdf', '../uploads/videos/12/user_12_688f270e756f7.mp4', NULL, 0, '', '2025-08-03 08:56:01', '2025-08-03 09:08:30'),
(13, 'mahsa', 'aghadad', 'mah@m.com', '$2y$10$ImbQTDVa02qF1ksUl27aeeZPASWH/gmpNPgDvdNUCrKV/GarF/WrC', NULL, NULL, NULL, 0, 'uploads/pics/13/profile_pic_689c32a946bc0.png', 'uploads/covers/13/cover_photo_6895da884c6be.png', 0, NULL, '', '2014-02-13', 'master', '', 'Mon - Fri: 9:00 AM - 5:00 PM (CST)', '', '', '', '', '', NULL, 'available', NULL, NULL, '../uploads/pdfs/13/resume_13_1755069419.pdf', '../uploads/videos/13/user_13_6895d0f41c38a.mp4', NULL, 1, '', '2025-08-05 07:06:17', '2025-08-13 07:16:59'),
(14, 'ziba', 'nasrii', 'zi@z.com', '$2y$10$PIXzKj221o5Eqlml6ii0G.Ko.UoXc92UGSVw9ItE4QqcyhwH5kO9W', NULL, NULL, NULL, 0, 'uploads/pics/14/profile_pic_689c6715cd565.jpg', 'uploads/covers/14/cover_photo_689c670dd9f93.jpg', 0, NULL, '', '2025-08-14', 'PHD', 'inovation house', 'Mon - Fri: 9:00 AM - 5:00 PM (CST)', '', '', '', '', '', NULL, 'available', '', '', '../uploads/pdfs/14/user_14_689c734ea8d40.pdf', '../uploads/videos/14/user_14_689c740f49e6e.mp4', '2025-08-13 14:43:04', 0, '', '2025-08-11 12:01:50', '2025-08-13 11:16:31');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `presentations`
--
ALTER TABLE `presentations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `saved_presentations`
--
ALTER TABLE `saved_presentations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
