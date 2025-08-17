-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 17, 2025 at 02:24 PM
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
(26, 19, 20, 'accepted', '2025-08-17 11:47:03', '2025-08-17 11:47:19');

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

-- --------------------------------------------------------

--
-- Table structure for table `presentations`
--

CREATE TABLE `presentations` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `video_path` varchar(255) DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `role` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `presentations`
--

INSERT INTO `presentations` (`id`, `user_id`, `title`, `description`, `pdf_path`, `video_path`, `keywords`, `created_at`, `role`) VALUES
(30, 19, 'مهشید', 'مهشید خودسیانیمهشید خودسیانیمهشید خودسیانیمهشید خودسیانی', '../uploads/pdfs/19/pres_68a1c5732f00f.pdf', '../uploads/videos/19/user_19_68a1c5733397b.mp4', 'مهشید, تستی', '2025-08-17 15:35:07', 'Presenter');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `rater_user_id` int(12) NOT NULL,
  `presentation_id` int(10) UNSIGNED NOT NULL,
  `rating_value` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `rater_user_id`, `presentation_id`, `rating_value`, `comment`, `created_at`) VALUES
(12, 20, 30, 3, 'goooood', '2025-08-17 12:05:53');

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
  `status` int(1) NOT NULL DEFAULT 1,
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
  `last_resume_update` timestamp NULL DEFAULT NULL,
  `hide_resume` int(1) NOT NULL DEFAULT 0,
  `biography` longtext DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `family`, `email`, `password`, `reset_code`, `reset_code_expires_at`, `status`, `google_id`, `is_google_user`, `profile_pic`, `cover_photo`, `is_active`, `email_verification_token`, `university`, `birthdate`, `education`, `workplace`, `meeting_info`, `linkedin_url`, `x_url`, `google_scholar_url`, `github_url`, `website_url`, `custom_profile_link`, `availability_status`, `meeting_link`, `google_calendar`, `resume_pdf_path`, `intro_video_path`, `last_resume_update`, `hide_resume`, `biography`, `created_at`, `updated_at`) VALUES
(19, 'mahshid', 'khodsiani', 'm@m.com', '$2y$10$56.oLPEAwuJL1zmNfsv20OT6WKr3GImNot0zIKI/4K2/SO.AXrTE6', NULL, NULL, 1, NULL, 0, 'uploads/pics/19/profile_pic_68a1c0058776c.jpg', 'images/11.jpg', 0, NULL, 'shahrekord', '2025-08-20', '', '', 'Mon - Fri: 9:00 AM - 5:00 PM (CST)', '', '', '', '', '', NULL, 'available', NULL, NULL, '../uploads/pdfs/19/user_19_68a1c4cc4870f.pdf', NULL, '2025-08-17 12:02:20', 0, '', '2025-08-17 11:40:31', '2025-08-17 12:02:20'),
(20, 'koorosh', 'fakari', 'fa@k.com', '$2y$10$fY1rMQgo4ArINv.KsFw36eadcTJQ67U059ImDHv2eJHKycgU3iPjW', NULL, NULL, 1, NULL, 0, 'uploads/pics/20/profile_pic_68a1bfe2425de.png', 'images/11.jpg', 0, NULL, '', '0000-00-00', '', '', 'Mon - Fri: 9:00 AM - 5:00 PM (CST)', '', '', '', '', '', NULL, 'available', NULL, NULL, '../uploads/pdfs/20/user_20_68a1c2ac8d563.pdf', NULL, '2025-08-16 20:30:00', 0, '', '2025-08-17 11:40:55', '2025-08-17 11:53:16');

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
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rating` (`rater_user_id`,`presentation_id`),
  ADD KEY `fk_ratings_users` (`rater_user_id`),
  ADD KEY `fk_ratings_presentations` (`presentation_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `presentations`
--
ALTER TABLE `presentations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `saved_presentations`
--
ALTER TABLE `saved_presentations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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
-- Constraints for table `ratings`
--
ALTER TABLE `ratings`
  ADD CONSTRAINT `fk_ratings_presentations` FOREIGN KEY (`presentation_id`) REFERENCES `presentations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ratings_users` FOREIGN KEY (`rater_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
