-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 21, 2025 at 11:31 AM
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
(1, 'mahshid', 'khodsiani', 'm@m.com', '$2y$10$8vTL3tuJ6xnllkpo.lLNFeCDpEp/yycKYpMp.gwxUgCaXh8kcinfm', 'uploads/pics/1/profile_pic_687debc66fe75.jpg', 'isfahan', '0000-00-00', 'bachelor', '', 'Mon - Fri: 9:00 AM - 5:00 PM (CST)', '', '', '', 'https://www.linkedin.com/in/mahshid-khodsiani-27626835a', 'https://moonshid.com/', NULL, 'google_calendar_embed', 'https://calendar.google.com/calendar/u/0?cid=bWFoc2hpZGtob2RzaWFuaTJAZ21haWwuY29t', 'https://calendar.google.com/calendar/u/0?cid=bWFoc2hpZGtob2RzaWFuaTJAZ21haWwuY29t', '../uploads/pdfs/1/user_1_687e06dfb3318.pdf', '../uploads/videos/1/user_1_687e06f36dbe2.mp4', '2025-07-21 12:50:42', 'از htmlspecialchars() برای جلوگیری از XSS استفاده شده است.\r\n\r\nآیکون Font Awesome برای زیبایی بیشتر اضافه شده است.\r\n\r\nمحدودیت 500 کاراکتری برای متن بیوگرافی در نظر گرفته شده است.\r\n\r\n', '2025-07-19 15:03:35', '2025-07-21 09:29:02'),
(2, 'برنامه نویسی', 'اسپادانا', 's.nader@artamoz.com', '123', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-19 15:03:35', '2025-07-19 15:03:35'),
(3, 'برنامه نویسی', 'اسپادانا', 'a@artamoz.com', '123', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-19 15:03:35', '2025-07-19 15:03:35'),
(4, 'برنامه نویسی', 'اسپادانا', 's@artamoz.com', '123', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, NULL, NULL, NULL, '2025-07-19 15:03:35', '2025-07-19 15:03:35');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(12) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
