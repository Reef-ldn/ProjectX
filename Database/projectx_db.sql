-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 08, 2025 at 12:23 AM
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
-- Database: `projectx_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `comment_text`, `created_at`, `parent_id`) VALUES
(67, 25, 33, 'You make it look too easy !', '2025-04-30 16:18:30', NULL),
(68, 28, 32, 'Yeah, I will be there,', '2025-04-30 16:29:03', NULL),
(69, 30, 35, 'You wish', '2025-04-30 16:32:13', NULL),
(70, 28, 35, 'Im rolling', '2025-04-30 16:32:26', NULL),
(71, 27, 35, '2 for 1 special', '2025-04-30 16:32:47', NULL),
(72, 28, 34, 'Perfect, couple more and we good to go', '2025-04-30 16:37:23', NULL),
(73, 30, 34, 'No chance.', '2025-04-30 16:38:13', NULL),
(74, 31, 32, 'Agreed', '2025-04-30 16:41:38', NULL),
(75, 30, 32, 'I dont listen to haters', '2025-04-30 16:42:03', NULL),
(76, 34, 35, '3 MINIMUM', '2025-04-30 16:45:03', NULL),
(77, 34, 34, '1, lets have a humble day', '2025-04-30 16:47:47', NULL),
(78, 36, 32, 'get fitter and you wont get benched again', '2025-04-30 16:49:01', NULL),
(79, 35, 32, 'clean', '2025-04-30 16:49:14', NULL),
(80, 34, 32, '2 GA will do', '2025-04-30 16:49:26', NULL),
(81, 38, 35, 'good goal bro', '2025-04-30 16:53:54', NULL),
(93, 39, 32, 'I\'ll be there', '2025-04-30 19:16:02', NULL),
(94, 39, 33, 'Let\'s do it', '2025-04-30 19:20:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `follows`
--

CREATE TABLE `follows` (
  `id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL COMMENT 'The user that follows',
  `followed_id` int(11) NOT NULL COMMENT 'The user being followed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `follows`
--

INSERT INTO `follows` (`id`, `follower_id`, `followed_id`, `created_at`) VALUES
(8, 24, 25, '2025-03-02 16:37:52'),
(9, 24, 27, '2025-03-03 20:18:45'),
(10, 24, 23, '2025-03-05 13:15:43'),
(12, 21, 29, '2025-03-06 16:32:43'),
(14, 21, 21, '2025-03-10 17:09:52'),
(36, 21, 20, '2025-04-01 17:35:09'),
(37, 21, 24, '2025-04-10 14:05:27'),
(39, 22, 21, '2025-04-14 04:35:13'),
(41, 24, 21, '2025-04-30 03:28:36'),
(44, 35, 33, '2025-04-30 16:32:58'),
(45, 35, 32, '2025-04-30 16:33:01'),
(46, 35, 34, '2025-04-30 16:33:05'),
(47, 34, 33, '2025-04-30 16:38:29'),
(48, 34, 32, '2025-04-30 16:38:47'),
(51, 32, 34, '2025-04-30 18:13:37'),
(53, 32, 33, '2025-04-30 18:15:54'),
(54, 33, 35, '2025-04-30 19:20:26'),
(55, 38, 37, '2025-05-06 23:20:52');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`id`, `user_id`, `post_id`, `created_at`) VALUES
(125, 33, 25, '2025-04-30 16:18:21'),
(126, 33, 26, '2025-04-30 16:19:51'),
(127, 33, 27, '2025-04-30 16:21:18'),
(128, 34, 27, '2025-04-30 16:22:15'),
(130, 34, 25, '2025-04-30 16:22:19'),
(131, 32, 25, '2025-04-30 16:27:50'),
(132, 32, 27, '2025-04-30 16:27:54'),
(133, 32, 26, '2025-04-30 16:27:55'),
(134, 32, 28, '2025-04-30 16:28:29'),
(135, 32, 29, '2025-04-30 16:29:20'),
(136, 35, 29, '2025-04-30 16:32:18'),
(137, 35, 27, '2025-04-30 16:32:31'),
(138, 35, 25, '2025-04-30 16:33:41'),
(139, 34, 28, '2025-04-30 16:37:29'),
(140, 34, 32, '2025-04-30 16:38:00'),
(141, 34, 30, '2025-04-30 16:38:16'),
(142, 34, 31, '2025-04-30 16:38:22'),
(143, 34, 29, '2025-04-30 16:38:24'),
(144, 32, 33, '2025-04-30 16:40:42'),
(145, 32, 32, '2025-04-30 16:41:23'),
(146, 32, 30, '2025-04-30 16:41:29'),
(147, 32, 31, '2025-04-30 16:41:34'),
(148, 35, 35, '2025-04-30 16:44:57'),
(149, 35, 34, '2025-04-30 16:45:07'),
(150, 35, 33, '2025-04-30 16:45:09'),
(151, 35, 31, '2025-04-30 16:45:14'),
(152, 34, 35, '2025-04-30 16:47:30'),
(153, 34, 34, '2025-04-30 16:48:01'),
(154, 32, 36, '2025-04-30 16:48:44'),
(155, 32, 35, '2025-04-30 16:49:07'),
(157, 33, 38, '2025-04-30 16:52:36'),
(158, 35, 39, '2025-04-30 16:53:26'),
(159, 35, 36, '2025-04-30 16:53:36'),
(160, 35, 37, '2025-04-30 16:53:41'),
(161, 35, 38, '2025-04-30 16:53:45'),
(163, 33, 39, '2025-04-30 19:19:57'),
(164, 33, 37, '2025-04-30 19:20:11'),
(165, 33, 35, '2025-04-30 19:20:20'),
(166, 33, 28, '2025-04-30 19:28:36'),
(168, 36, 38, '2025-05-05 23:59:36'),
(169, 36, 37, '2025-05-05 23:59:37'),
(170, 36, 33, '2025-05-05 23:59:44'),
(177, 32, 39, '2025-05-07 21:51:51');

-- --------------------------------------------------------

--
-- Table structure for table `managers`
--

CREATE TABLE `managers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `profile_pic` varchar(255) NOT NULL,
  `current_team` varchar(100) NOT NULL,
  `current_league` varchar(100) NOT NULL,
  `spoken_language` varchar(50) NOT NULL,
  `matches_managed` int(11) NOT NULL,
  `age` int(11) NOT NULL,
  `country` varchar(100) NOT NULL,
  `motm` int(11) NOT NULL,
  `moty` int(11) NOT NULL,
  `clean sheets` int(11) NOT NULL,
  `awards` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `managers`
--

INSERT INTO `managers` (`id`, `user_id`, `profile_pic`, `current_team`, `current_league`, `spoken_language`, `matches_managed`, `age`, `country`, `motm`, `moty`, `clean sheets`, `awards`) VALUES
(1, 27, '', '', '', '', 0, 0, '', 0, 0, 0, ''),
(2, 37, '', 'Kingsley Rangers', 'Division 5', 'English', 12, 32, 'England', 3, 2, 12, '');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL COMMENT 'The user that sent the text',
  `receiver_id` int(11) NOT NULL COMMENT 'the user that received the text',
  `content` text DEFAULT NULL,
  `shared_post_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `content`, `shared_post_id`, `created_at`) VALUES
(28, 32, 33, NULL, 29, '2025-04-30 16:41:02'),
(29, 32, 33, 'Lovely goal from you lad!', NULL, '2025-04-30 16:41:13'),
(30, 35, 33, NULL, 34, '2025-04-30 16:55:34'),
(31, 35, 33, 'So, how many did you end up getting ??', NULL, '2025-04-30 16:55:51'),
(32, 33, 35, NULL, 28, '2025-04-30 19:30:05'),
(33, 33, 35, 'I got 3 goals bro', NULL, '2025-04-30 19:30:37'),
(35, 32, 35, 'Hello', NULL, '2025-05-01 15:56:38'),
(36, 32, 35, 'How are you doing', NULL, '2025-05-01 15:56:44'),
(37, 36, 35, 'Test', NULL, '2025-05-05 23:58:43'),
(38, 32, 33, NULL, 39, '2025-05-06 01:30:02'),
(39, 38, 37, 'Hello Manager man', NULL, '2025-05-06 23:21:00'),
(40, 32, 36, 'Hello', NULL, '2025-05-07 16:59:01'),
(41, 32, 33, NULL, 34, '2025-05-07 21:50:20'),
(42, 32, 33, 'Did you score?', NULL, '2025-05-07 21:50:32');

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE `players` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `profile_pic` varchar(255) NOT NULL,
  `height` int(11) NOT NULL,
  `weight` int(11) NOT NULL,
  `age` int(11) NOT NULL,
  `preferred_position` varchar(50) NOT NULL,
  `preferred_foot` varchar(10) NOT NULL,
  `current_team` varchar(100) NOT NULL,
  `goals` int(11) NOT NULL,
  `assists` int(11) NOT NULL,
  `motm` int(11) NOT NULL,
  `potm` int(11) NOT NULL,
  `awards` text NOT NULL,
  `country` varchar(100) NOT NULL,
  `current_league` varchar(100) NOT NULL,
  `appearances` int(255) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `players`
--

INSERT INTO `players` (`id`, `user_id`, `profile_pic`, `height`, `weight`, `age`, `preferred_position`, `preferred_foot`, `current_team`, `goals`, `assists`, `motm`, `potm`, `awards`, `country`, `current_league`, `appearances`) VALUES
(8, 32, '', 183, 72, 21, 'Right Winger', 'Left', 'Kinglsey Rangers', 23, 8, 5, 2, 'League Title', 'England', 'League 1', 38),
(9, 33, '', 183, 72, 24, 'Left Winger', 'Left', 'Kinglsey Rangers', 28, 14, 8, 2, 'League Title', 'England', 'League 1', 31),
(10, 34, '', 183, 72, 24, 'Left Winger', 'Left', 'Kinglsey Rangers', 28, 14, 8, 2, 'League Title', 'England', 'League 1', 31),
(11, 35, '', 183, 72, 24, 'Left Winger', 'Left', 'Kinglsey Rangers', 28, 14, 8, 2, 'League Title', 'England', 'League 1', 31);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_type` varchar(20) NOT NULL COMMENT 'Video, image or Text Post',
  `title` varchar(100) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL COMMENT 'Stores the filename /path',
  `text_content` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_highlight` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `post_type`, `title`, `file_path`, `text_content`, `created_at`, `is_highlight`) VALUES
(25, 32, 'video', '', 'uploads/20250413_201614000_iOS.MOV', 'Watch this free kick I just scored -  keeper stood no chance !', '2025-04-30 16:16:19', 1),
(26, 33, 'text', '', NULL, 'First post, not really sure how to use this platform, but let\'s connect !', '2025-04-30 16:18:12', NULL),
(27, 33, 'video', 'Too easy', 'uploads/20250413_192304000_iOS.MP4', 'Sent him shops lol, if only I scored', '2025-04-30 16:21:06', 1),
(28, 34, 'text', '', NULL, 'Anyone down for a 5-aside game in south London at 8pm tomorrow?', '2025-04-30 16:23:18', NULL),
(29, 32, 'video', '', 'uploads/20250413_201741000_iOS.MOV', 'Lovely assist from my boy @BigMo . ', '2025-04-30 16:27:40', NULL),
(30, 32, 'text', '', NULL, 'Mark my words, arsenal is winning the champions league.', '2025-04-30 16:30:43', 0),
(31, 35, 'text', '', NULL, 'The sun is shining today, perfect to go train.', '2025-04-30 16:33:23', NULL),
(32, 34, 'image', '', 'uploads/20250413_202059357_iOS.jpg', 'Light game today. 3-2 win (Scored 2)', '2025-04-30 16:36:45', 1),
(33, 32, 'video', '', 'uploads/20250413_201945000_iOS.MOV', 'This was a nasty run !', '2025-04-30 16:40:40', NULL),
(34, 33, 'text', '', NULL, 'How many goals you lot wanna see from me today?', '2025-04-30 16:43:14', NULL),
(35, 35, 'video', '', 'uploads/20250413_193748000_iOS.MP4', 'Lovelyyyyy', '2025-04-30 16:44:53', NULL),
(36, 34, 'image', '', 'uploads/20250413_202106685_iOS.jpg', 'hate getting benched lol', '2025-04-30 16:47:00', NULL),
(37, 32, 'image', '', 'uploads/20250413_202025947_iOS.jpg', 'In my element', '2025-04-30 16:50:47', 1),
(38, 33, 'video', '', 'uploads/20250413_193743000_iOS.MP4', 'Always right place, right time.', '2025-04-30 16:52:34', 1),
(39, 35, 'text', '', NULL, 'Anyone wanna go cage today? Need like 3 people', '2025-04-30 16:53:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `previous_teams`
--

CREATE TABLE `previous_teams` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `team_name` varchar(100) NOT NULL,
  `start_year` int(11) NOT NULL,
  `end_year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `previous_teams`
--

INSERT INTO `previous_teams` (`id`, `user_id`, `team_name`, `start_year`, `end_year`) VALUES
(1, 32, 'Charlton fc', 2021, 2024);

-- --------------------------------------------------------

--
-- Table structure for table `scouts`
--

CREATE TABLE `scouts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `profile_pic` varchar(255) NOT NULL,
  `current_team` varchar(100) NOT NULL,
  `current_league` varchar(100) NOT NULL,
  `spoken_language` varchar(50) NOT NULL,
  `Country` varchar(100) NOT NULL,
  `previous_teams` varchar(100) NOT NULL,
  `duration` int(11) NOT NULL,
  `achievements` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scouts`
--

INSERT INTO `scouts` (`id`, `user_id`, `profile_pic`, `current_team`, `current_league`, `spoken_language`, `Country`, `previous_teams`, `duration`, `achievements`) VALUES
(2, 38, '', 'Coventry City', 'Test League', 'Japanese', 'Japan', '', 7, '');

-- --------------------------------------------------------

--
-- Table structure for table `trophies`
--

CREATE TABLE `trophies` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `trophy_name` varchar(100) NOT NULL,
  `year_awarded` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trophies`
--

INSERT INTO `trophies` (`id`, `user_id`, `trophy_name`, `year_awarded`) VALUES
(3, 32, 'Young Player of the Season', 2024);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_type` varchar(20) NOT NULL DEFAULT 'player',
  `profile_pic` varchar(255) NOT NULL,
  `banner_pic` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `email`, `password`, `created_at`, `user_type`, `profile_pic`, `banner_pic`) VALUES
(32, 'TroyYoung', 'Troy Young', 'TroyYoung@email.com', '$2y$10$8quW8FE0H8jkI9OP7qgAqegzpvFmSrzgQkIkDJB2/rzjqWf.DtRO2', '2025-04-30 18:32:07', 'player', 'uploads/profile_pics/20250413_202025962_iOS.jpg', ''),
(33, 'BigMo', 'Mo Jo', 'BigMo@email.com', '$2y$10$UueKg0RjIExyu7rXENU0j.1KQfumlZpTAH1d3z1Ul0d8E6mZ.9aMG', '2025-04-30 19:19:21', 'player', 'uploads/profile_pics/Failing to Plan = Planing to fail.png', ''),
(34, 'Mikez', 'Michael Trello', 'Mikez@email.com', '$2y$10$yGLO13Lnod.8PpBT6.5BHONvu7Q00a38bgLOkxBAwy5BfDAkws4VO', '2025-04-30 19:18:41', 'player', 'uploads/profile_pics/20250413_202059113_iOS.jpg', ''),
(35, 'Nkwalalaka', 'Kingsley Nkwalalaka', 'Nkwalaka@email.com', '$2y$10$Nqsk9HTjenDre8XN2V.TAuzr7zJbtOCbYnk44gTjMn5Lu3XLokuJu', '2025-04-30 19:18:06', 'player', 'uploads/profile_pics/20250413_202059454_iOS.jpg', ''),
(36, 'FanTest', '', 'FanTest@email.com', '$2y$10$OCQeusZC/TGrRgCZrV1ym.toFBaLU4oC4UbgfIBnbbEP7t9Dpdsjm', '2025-05-05 23:57:47', 'fan', '', ''),
(37, 'ManagerTest', '', 'ManagerTest@email.com', '$2y$10$8TxjbM3EEfpdQSZIAojosuUv/UpRAzo2Wd/wszq/yMHIY66xX7ZZC', '2025-05-06 21:47:07', 'manager', '', ''),
(38, 'ScoutTest', 'Test Scout', 'ScoutTest@email.com', '$2y$10$XJCxMAizZuMpUJtA.deq3OqDCTbMKZ81RWeRHGU48LQjrV1y8YG8q', '2025-05-07 00:10:16', 'scout', '', ''),
(39, 'FanTest', '', 'FanTest@email.com', '$2y$10$mjxrh569izN8mmKqVBp4l.T/R/Akd.VMGZ3OOeXScE6VKeL6PG9lm', '2025-05-06 23:35:07', 'fan', '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `managers`
--
ALTER TABLE `managers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `previous_teams`
--
ALTER TABLE `previous_teams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `scouts`
--
ALTER TABLE `scouts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trophies`
--
ALTER TABLE `trophies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `follows`
--
ALTER TABLE `follows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=178;

--
-- AUTO_INCREMENT for table `managers`
--
ALTER TABLE `managers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `players`
--
ALTER TABLE `players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `previous_teams`
--
ALTER TABLE `previous_teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `scouts`
--
ALTER TABLE `scouts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `trophies`
--
ALTER TABLE `trophies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
