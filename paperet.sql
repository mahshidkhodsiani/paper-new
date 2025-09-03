-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 03, 2025 at 12:44 PM
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
-- Table structure for table `competitions`
--

CREATE TABLE `competitions` (
  `id` int(11) NOT NULL,
  `organizer_name` varchar(255) NOT NULL COMMENT 'Organizer Name (Mandatory)',
  `organizer_email` varchar(255) NOT NULL COMMENT 'Organizer Email (Mandatory)',
  `competition_title` varchar(255) NOT NULL COMMENT 'Competition Title (Mandatory)',
  `competition_description` text NOT NULL COMMENT 'Competition Description (Mandatory)',
  `start_date` date NOT NULL COMMENT 'Start Date (Mandatory)',
  `end_date` date NOT NULL COMMENT 'End Date (Mandatory)',
  `timezone` varchar(50) DEFAULT NULL,
  `room_link` varchar(255) DEFAULT NULL,
  `session_track` varchar(255) DEFAULT NULL,
  `presentation_duration` int(11) DEFAULT NULL,
  `buffer_duration` int(11) DEFAULT NULL,
  `presentation_order` enum('random','lock') DEFAULT NULL,
  `competition_visibility` enum('public','private') NOT NULL COMMENT 'Competition Visibility (Mandatory)',
  `participation_access` enum('open','application','private') NOT NULL COMMENT 'Participation Access (Mandatory)',
  `voting_system` enum('judges_only','public','hybrid') NOT NULL COMMENT 'Voting System (Mandatory)',
  `max_votes_per_participant` int(11) DEFAULT NULL,
  `results_visibility` enum('always_visible','after_voting','after_competition','judges_only') DEFAULT NULL,
  `max_participants` int(11) DEFAULT NULL,
  `max_submissions_per_participant` int(11) DEFAULT NULL,
  `judge_notes` text DEFAULT NULL,
  `submission_type` enum('file','url','text') NOT NULL COMMENT 'Submission Type (Mandatory)',
  `max_file_size` int(11) DEFAULT NULL,
  `allowed_formats` text DEFAULT NULL,
  `submission_guidelines` text DEFAULT NULL,
  `custom_fields_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_fields_json`)),
  `slide_deck_required` tinyint(1) DEFAULT NULL,
  `abstract_text_field` tinyint(1) DEFAULT NULL,
  `poster_image_optional` tinyint(1) DEFAULT NULL,
  `consent_recording` tinyint(1) DEFAULT NULL,
  `consent_public_display` tinyint(1) DEFAULT NULL,
  `scoring_rubric` longtext DEFAULT NULL,
  `score_weighting_system` enum('no_weighting','normalized','weighted','custom_formula') DEFAULT NULL,
  `custom_css` text DEFAULT NULL,
  `redirect_url` varchar(255) DEFAULT NULL,
  `enable_comments` tinyint(1) DEFAULT NULL,
  `moderate_submissions` tinyint(1) DEFAULT NULL,
  `enable_blind_review` tinyint(1) DEFAULT NULL,
  `require_conflict` tinyint(1) DEFAULT NULL,
  `late_submission_grace_period` int(11) DEFAULT NULL,
  `judging_visibility` enum('private','public_after','public_during') DEFAULT NULL,
  `webhook_url` varchar(255) DEFAULT NULL,
  `export_options` varchar(255) DEFAULT NULL,
  `per_criterion_score_scale` enum('1-5','1-10') DEFAULT NULL,
  `tie_break_policy` enum('impact','chair_decision','earliest_submission') DEFAULT NULL,
  `qa_time` int(11) DEFAULT NULL,
  `leaderboard_visibility` enum('judges_only','public_after','public_during') DEFAULT NULL,
  `notify_new_submission` tinyint(1) DEFAULT NULL,
  `send_schedule` tinyint(1) DEFAULT NULL,
  `email_winners` tinyint(1) DEFAULT NULL,
  `results_publish_date` date DEFAULT NULL,
  `winner_email_template` text DEFAULT NULL,
  `competition_category` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competitions`
--

INSERT INTO `competitions` (`id`, `organizer_name`, `organizer_email`, `competition_title`, `competition_description`, `start_date`, `end_date`, `timezone`, `room_link`, `session_track`, `presentation_duration`, `buffer_duration`, `presentation_order`, `competition_visibility`, `participation_access`, `voting_system`, `max_votes_per_participant`, `results_visibility`, `max_participants`, `max_submissions_per_participant`, `judge_notes`, `submission_type`, `max_file_size`, `allowed_formats`, `submission_guidelines`, `custom_fields_json`, `slide_deck_required`, `abstract_text_field`, `poster_image_optional`, `consent_recording`, `consent_public_display`, `scoring_rubric`, `score_weighting_system`, `custom_css`, `redirect_url`, `enable_comments`, `moderate_submissions`, `enable_blind_review`, `require_conflict`, `late_submission_grace_period`, `judging_visibility`, `webhook_url`, `export_options`, `per_criterion_score_scale`, `tie_break_policy`, `qa_time`, `leaderboard_visibility`, `notify_new_submission`, `send_schedule`, `email_winners`, `results_publish_date`, `winner_email_template`, `competition_category`) VALUES
(1, '33', 'techespadana@gmail.com', '33', '33', '2025-09-04', '2025-09-04', 'America/Chicago (CT)', '', '', 15, 5, '', '', '', '', 1, '', 0, 1, '0', '', 0, '', '0', '0', 1, 1, 0, 0, 0, '', '', '', '0', 0, 0, 0, 0, 0, '', '', '', '', '', 0, '', 0, 0, 0, '0000-00-00', '', NULL),
(2, '33', 'techespadana@gmail.com', '33', '33', '2025-09-04', '2025-09-04', 'America/Chicago (CT)', '', '', 15, 5, '', '', '', '', 1, '', 0, 1, '0', '', 0, '', '0', '0', 1, 1, 0, 0, 0, '', '', '', '0', 0, 0, 0, 0, 0, '', '', '', '', '', 0, '', 0, 0, 0, '0000-00-00', '', NULL),
(3, 'غتغتقغتق', 'mahshidkhodsiani2@gmail.com', 'moonshid', 'website', '2025-09-04', '2025-09-05', 'America/Chicago (CT)', '', '', 15, 5, '', '', '', '', 1, '', 0, 1, '0', '', 0, '', '0', '0', 1, 1, 0, 0, 0, '', '', '', '0', 0, 0, 0, 0, 0, '', '', '', '', '', 0, '', 0, 0, 0, '0000-00-00', '', NULL),
(4, 'bbbbbbbbb', 'techespadana@gmail.com', 'bb', 'bb', '2025-09-04', '2025-09-04', 'America/Chicago (CT)', '', '', 15, 5, '', '', '', '', 1, '', 0, 1, '0', '', 0, '', '0', '0', 1, 1, 0, 0, 0, '', '', '', '0', 0, 0, 0, 0, 0, '', '', '', '', '', 0, '', 0, 0, 0, '0000-00-00', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `competition_awards`
--

CREATE TABLE `competition_awards` (
  `id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `award_name` varchar(255) DEFAULT NULL,
  `award_value` varchar(255) DEFAULT NULL,
  `number_of_winners` int(11) DEFAULT NULL,
  `per_winner_prize` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competition_awards`
--

INSERT INTO `competition_awards` (`id`, `competition_id`, `award_name`, `award_value`, `number_of_winners`, `per_winner_prize`) VALUES
(1, 1, '', '', 0, ''),
(2, 2, '', '', 0, ''),
(3, 3, '', '', 0, ''),
(4, 4, '', '', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `competition_judges`
--

CREATE TABLE `competition_judges` (
  `id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `resume_file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `competition_participants`
--

CREATE TABLE `competition_participants` (
  `id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competition_participants`
--

INSERT INTO `competition_participants` (`id`, `competition_id`, `user_id`, `name`, `email`) VALUES
(1, 1, 19, 'mahshid khodsiani', 'm@m.com'),
(2, 2, 19, 'mahshid khodsiani', 'm@m.com');

-- --------------------------------------------------------

--
-- Table structure for table `competition_uploads`
--

CREATE TABLE `competition_uploads` (
  `id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `type` enum('logo','certificate_template','sample_certificate','competition_rubric','poster_image','slide_deck') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(50) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competition_uploads`
--

INSERT INTO `competition_uploads` (`id`, `competition_id`, `type`, `file_path`, `mime_type`, `file_size`) VALUES
(1, 1, 'slide_deck', 'uploads/68b808f2bd07e_Paperet Edits.pdf', 'application/pdf', 264654),
(2, 2, 'slide_deck', 'uploads/competitions/2/68b809813f663_Paperet Edits.pdf', 'application/pdf', 264654),
(3, 3, 'slide_deck', 'uploads/competitions/3/68b80ddf627c4_Paperet Edits.pdf', 'application/pdf', 264654),
(4, 4, 'slide_deck', 'uploads/competitions/4/68b80f3aa73d5_Paperet Edits.pdf', 'application/pdf', 264654);

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
-- Indexes for table `competitions`
--
ALTER TABLE `competitions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `competition_awards`
--
ALTER TABLE `competition_awards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `competition_id` (`competition_id`);

--
-- Indexes for table `competition_judges`
--
ALTER TABLE `competition_judges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `competition_id` (`competition_id`);

--
-- Indexes for table `competition_participants`
--
ALTER TABLE `competition_participants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_participant` (`competition_id`,`user_id`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `competition_uploads`
--
ALTER TABLE `competition_uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `competition_id` (`competition_id`);

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
-- AUTO_INCREMENT for table `competitions`
--
ALTER TABLE `competitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `competition_awards`
--
ALTER TABLE `competition_awards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `competition_judges`
--
ALTER TABLE `competition_judges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `competition_participants`
--
ALTER TABLE `competition_participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `competition_uploads`
--
ALTER TABLE `competition_uploads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- Constraints for table `competition_awards`
--
ALTER TABLE `competition_awards`
  ADD CONSTRAINT `competition_awards_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `competition_judges`
--
ALTER TABLE `competition_judges`
  ADD CONSTRAINT `competition_judges_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `competition_participants`
--
ALTER TABLE `competition_participants`
  ADD CONSTRAINT `competition_participants_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `competition_uploads`
--
ALTER TABLE `competition_uploads`
  ADD CONSTRAINT `competition_uploads_ibfk_1` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`) ON DELETE CASCADE;

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
