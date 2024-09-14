-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 12, 2024 at 04:30 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lfis_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'd666e5c87d3392cdd1b00efc8ac4281c');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `category_name`) VALUES
(1, 'Watches', NULL),
(2, 'Electronics', NULL),
(3, 'Clothing', NULL),
(4, 'Accessories', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `category_list`
--

CREATE TABLE `category_list` (
  `id` bigint NOT NULL,
  `name` text NOT NULL,
  `description` text NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `category_list`
--

INSERT INTO `category_list` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Mobile Phones', '&lt;p&gt;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec id est enim. Fusce malesuada dapibus lobortis. Maecenas commodo cursus ante, a efficitur lorem ultrices a. Cras tincidunt, leo at consequat viverra, lacus elit tempus diam, sed scelerisque turpis purus eu ex. Sed placerat, sem vel accumsan maximus, nibh massa rhoncus mi, quis lacinia nisl quam eu purus.&lt;/p&gt;', 1, '2023-05-01 10:32:44', NULL),
(2, 'Keys', '&lt;p&gt;Nulla at tellus tristique, venenatis mauris a, commodo urna. Integer arcu quam, maximus id nulla vitae, eleifend lacinia tellus. Proin nec consequat risus. Sed et felis justo. Duis quis magna vel felis volutpat consectetur ut et enim. Integer nec auctor felis. Fusce nec mauris luctus, lacinia erat in, porttitor tellus. Nunc quis mauris velit. Sed nec libero vitae leo blandit mattis.&lt;/p&gt;', 1, '2023-05-01 10:34:27', NULL),
(3, 'Watches', '&lt;p&gt;Etiam volutpat dictum tempor. Nulla rutrum arcu eu volutpat pharetra. Aliquam non luctus ex. Maecenas nibh ipsum, efficitur in dui at, rhoncus convallis orci. Duis bibendum tempor sapien, non sollicitudin massa porttitor sed.&lt;/p&gt;', 1, '2023-05-01 10:35:58', '2023-05-01 10:36:15');

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `id` int NOT NULL,
  `item_id` int UNSIGNED NOT NULL,
  `claim_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(20) DEFAULT 'Pending',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `additional_info` text,
  `date_submitted` datetime DEFAULT CURRENT_TIMESTAMP,
  `username` varchar(255) DEFAULT NULL,
  `course` varchar(255) NOT NULL,
  `year` varchar(255) NOT NULL,
  `section` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `college` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `user_id` int NOT NULL,
  `user_course` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `claims`
--

INSERT INTO `claims` (`id`, `item_id`, `claim_date`, `status`, `description`, `additional_info`, `date_submitted`, `username`, `course`, `year`, `section`, `email`, `college`, `image_path`, `user_id`, `user_course`) VALUES
(57, 33, '2024-09-11 23:56:10', 'Pending', NULL, 'dfdsfsdf', '2024-09-11 23:56:10', NULL, 'Bachelor of Science in Biology', '4th - year', 'Section D', 'mark@gmail.com', 'CAS', NULL, 23, NULL),
(58, 33, '2024-09-12 23:55:08', 'Pending', NULL, 'asdadsa', '2024-09-12 23:55:08', NULL, 'Bachelor of Science in Biology', '4th - year', 'Section D', 'mark@gmail.com', 'CAS', NULL, 23, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `claim_history`
--

CREATE TABLE `claim_history` (
  `id` int UNSIGNED NOT NULL,
  `item_id` int UNSIGNED NOT NULL,
  `claimed_by` varchar(255) NOT NULL,
  `claimed_at` datetime NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `claim_images`
--

CREATE TABLE `claim_images` (
  `id` int NOT NULL,
  `claim_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `claim_requests`
--

CREATE TABLE `claim_requests` (
  `id` int UNSIGNED NOT NULL,
  `item_id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL,
  `request_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `admin_notes` text,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `id` int NOT NULL,
  `image_name` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inquiry_list`
--

CREATE TABLE `inquiry_list` (
  `id` bigint NOT NULL,
  `fullname` text NOT NULL,
  `contact` text NOT NULL,
  `email` text NOT NULL,
  `message` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `inquiry_list`
--

INSERT INTO `inquiry_list` (`id`, `fullname`, `contact`, `email`, `message`, `image_path`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Jane Doe', '09123546788', 'jdoe@mail.com', 'Vestibulum suscipit felis at magna congue gravida. Quisque interdum eu odio sed vulputate.', NULL, 1, '2023-05-01 14:11:19', '2023-05-01 14:25:47'),
(2, 'King', '09489403123', 'vdarwin860@gmail.com', 'king am i king', NULL, 1, '2024-08-26 22:44:05', '2024-08-26 22:44:33'),
(4, 'Joey Abong', '233213', 'king@gmail.com', 'jacked', NULL, 1, '2024-08-27 12:08:17', '2024-08-27 12:08:34'),
(8, 'Joey Abong', '09489403123', 'menard@gmail.com', 'sadasdasd', NULL, 0, '2024-08-27 20:53:47', NULL),
(9, 'Adrian', '09489403123', 'darwin@gmail.com', 'sadasda', NULL, 1, '2024-08-27 21:03:42', '2024-09-05 20:53:10');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `type` enum('lost','found') NOT NULL,
  `status` enum('pending','approved') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item_list`
--

CREATE TABLE `item_list` (
  `id` int UNSIGNED NOT NULL,
  `category_id` bigint NOT NULL,
  `fullname` text NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `landmark` varchar(255) NOT NULL,
  `time_found` datetime NOT NULL,
  `image_paths` text NOT NULL,
  `contact` text NOT NULL,
  `image_path` text,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `item_list`
--

INSERT INTO `item_list` (`id`, `category_id`, `fullname`, `title`, `description`, `landmark`, `time_found`, `image_paths`, `contact`, `image_path`, `status`, `created_at`, `updated`, `email`) VALUES
(1, 2, 'Mark Cooper', 'Found Keys at Central Park', 'Suspendisse nisl diam, pretium ut placerat nec, pellentesque in tortor. Suspendisse vitae arcu a mi dapibus elementum ac dignissim tellus. Duis vitae molestie lacus, porttitor lacinia justo. Ut vulputate, ipsum interdum consequat mollis, odio nisl vulputate est, quis ornare nisi massa a odio.', '', '0000-00-00 00:00:00', '', '09123564789', 'uploads/items/1.png?v=1682912925', 1, '2023-05-01 11:48:45', '2023-05-01 11:48:45', ''),
(3, 1, 'Claire Blake', 'Found an Android Phone @ Restaurant Parking Lot', 'Etiam accumsan quis augue a pulvinar. Etiam pretium sodales ipsum, cursus venenatis urna fringilla vel. Nunc fringilla non magna sit amet pharetra. Nam iaculis rutrum eleifend. Mauris rutrum, urna eget rhoncus consequat, purus mauris luctus orci, at venenatis ex elit sed risus.', '', '0000-00-00 00:00:00', '', '09123654897', 'uploads/items/3.png?v=1682916949', 1, '2023-05-01 12:55:48', '2023-05-01 12:55:49', ''),
(5, 3, 'Samantha Lou', 'Found a Watch left @ Room 101', 'Sed ultricies turpis eget commodo condimentum. Nam ac lorem vitae nulla fringilla imperdiet sit amet a arcu. Maecenas malesuada felis eleifend condimentum porttitor. Cras sed metus nec nibh interdum bibendum sit amet at sem.', '', '0000-00-00 00:00:00', '', '09457778988', 'uploads/items/5.png?v=1682917427', 1, '2023-05-01 13:03:47', '2023-05-01 13:03:47', ''),
(6, 1, 'Wilson Smith', 'Found Something @ The Mall', 'Donec metus sem, volutpat id mi in, fringilla aliquet odio. Donec eleifend sem et ex maximus tristique. Donec porttitor venenatis aliquet. Aliquam tristique est sed nulla fermentum aliquam eget sed ex', '', '0000-00-00 00:00:00', '', '09123564789', NULL, 2, '2023-05-01 13:34:29', '2023-05-01 14:04:10', ''),
(20, 1, 'Abotong', '254534', 'wawaw', '', '0000-00-00 00:00:00', '', '09489403123', 'uploads/items/20.png?v=1723903928', 2, '2024-08-17 22:12:08', '2024-08-17 23:08:24', 'menard@gmail.com'),
(21, 3, 'Joey Abong', 'nawalang bilat ', 'nawala sa tapat ng complex', '', '0000-00-00 00:00:00', '', '911', 'uploads/items/21.png?v=1723906307', 1, '2024-08-17 22:51:47', '2024-08-17 22:52:19', 'menard@gmail.com'),
(22, 1, 'Joey Abong', 'Okay ka lang Kevin?', 'naka record na ', '', '0000-00-00 00:00:00', '', '0009', 'uploads/items/22.png?v=1723906636', 1, '2024-08-17 22:57:15', '2024-08-17 22:58:19', 'menard@gmail.com'),
(23, 2, 'Nigga', 'NIggaz', 'lightweight baby', '', '0000-00-00 00:00:00', '', '911', 'uploads/items/23.png?v=1723907217', 1, '2024-08-17 23:06:57', '2024-08-17 23:08:09', 'killme@gmail.com'),
(24, 2, 'unnamed', 'Jelvin', 'missing dildo 15 inches', '', '0000-00-00 00:00:00', '', '098089089', 'uploads/items/24.png?v=1723907485', 1, '2024-08-17 23:11:23', '2024-08-17 23:14:30', 'killme@gmail.com'),
(25, 1, 'unnamed', 'missing body', 'missing dead or alive', 'ccit building', '2024-08-27 13:13:00', '', '09489403123', 'uploads/items/25.png?v=1723907604', 2, '2024-08-17 23:13:24', '2024-08-27 13:14:00', 'killme@gmail.com'),
(26, 1, 'taka', 'taka', 'takakakaka', '', '0000-00-00 00:00:00', '', '0934', 'uploads/items/26.png?v=1723999491', 1, '2024-08-19 00:44:48', '2024-08-19 00:49:48', 'menard@gmail.com'),
(27, 3, 'Lebron James', 'Honda Click Key', 'missing honda click key @cthm building', 'ccit building', '2024-09-11 22:34:00', '', '09489403123', 'uploads/items/27.png?v=1724160943', 1, '2024-08-20 21:35:24', '2024-09-11 22:34:31', 'lebron@gmail.com'),
(28, 1, 'Lebron James', 'Missing Nike Shoes', 'missing nike shoes color black', 'prmsu coop canteen', '2024-09-10 14:35:00', '', '09489403123', 'uploads/items/28.png?v=1724254610', 2, '2024-08-21 23:36:50', '2024-09-09 13:48:40', 'lebron@gmail.com'),
(29, 1, 'Lebron James', 'Missing Spalding Ball', 'missing 50 grams ball', 'front of ccit', '2024-10-02 01:23:00', '', '911', 'uploads/items/29.png?v=1724426528', 1, '2024-08-23 23:22:07', '2024-08-23 23:22:54', 'lebron@gmail.com'),
(30, 1, 'test', 'tetsr', 'test', 'test', '2024-08-24 00:11:00', '', '02942342', 'uploads/items/30.png?v=1724429481', 0, '2024-08-24 00:11:20', '2024-08-24 00:11:21', 'lebron@gmail.com'),
(31, 1, 'kkkk', 'kkk', 'kkk', 'lll', '2024-08-24 00:15:00', '', '0435345', 'uploads/items/31.png?v=1724429770', 0, '2024-08-24 00:16:03', '2024-08-24 00:16:10', 'lebron@gmail.com'),
(33, 1, 'sadasdasd', 'cxvxcv', 'sadasdasdasd', 'ccit building', '2024-09-05 20:48:00', '', '094894031231', 'uploads/items/33.png?v=1725540505', 1, '2024-09-05 20:48:24', '2024-09-06 12:24:02', 'mark@gmail.com'),
(34, 1, 'Lebron James', 'adfdf', 'dsfsdfsdfsdf', 'front of ccit', '2024-09-06 00:00:00', '', '094894031233', 'uploads/items/34.png?v=1725552046', 1, '2024-09-06 00:00:46', '2024-09-06 00:04:26', 'lebron@gmail.com'),
(35, 1, 'Abotong', 'zxczxc', 'aSasAS', 'ccit building', '2024-09-18 21:04:00', '', 'zxczxczxczx', 'uploads/items/35.png?v=1725714254', 1, '2024-09-07 21:04:14', '2024-09-11 22:42:22', 'mark@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `message_history`
--

CREATE TABLE `message_history` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `message` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','read','archived') DEFAULT 'pending',
  `is_published` tinyint(1) DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `landmark` varchar(255) DEFAULT NULL,
  `time_found` datetime DEFAULT NULL,
  `published` tinyint(1) DEFAULT '0',
  `status_post` varchar(20) DEFAULT 'Pending',
  `category` varchar(255) DEFAULT NULL,
  `category_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `message_images`
--

CREATE TABLE `message_images` (
  `id` int NOT NULL,
  `message_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `status` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `post_date` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_info`
--

CREATE TABLE `system_info` (
  `id` int NOT NULL,
  `meta_field` text NOT NULL,
  `meta_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `system_info`
--

INSERT INTO `system_info` (`id`, `meta_field`, `meta_value`) VALUES
(1, 'name', 'Lost Property Management Mobile Application'),
(6, 'short_name', 'Ramonian LostGems'),
(11, 'logo', 'uploads/logo.png?v=1682908055'),
(13, 'user_avatar', 'uploads/user_avatar.jpg'),
(14, 'cover', 'uploads/cover.png?v=1682908055'),
(17, 'phone', '903-436-9356'),
(18, 'mobile', '0917-351-8047'),
(19, 'email', 'info@simpleorganization.org'),
(20, 'address', '4226 Florence Street, Arlington, Texas, 76011');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `firstname` varchar(250) NOT NULL,
  `middlename` text,
  `lastname` varchar(250) NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `avatar` text,
  `last_login` datetime DEFAULT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '0',
  `date_added` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='2';

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `middlename`, `lastname`, `username`, `password`, `avatar`, `last_login`, `type`, `date_added`, `date_updated`, `email`) VALUES
(1, 'Adminstrator', '', 'Admin', 'admin', '$2y$10$lu9Lz9d61nsRRq5aXGOrmuik6tzhMif.AIQTmxgj4LTHf3M9hyGtW', 'uploads/avatars/1.png?v=1678760026', NULL, 1, '2021-01-20 14:02:37', '2023-04-26 16:01:02', ''),
(9, 'Claire', '', 'Blake', 'cblake', '$2y$10$DFEet3AmXnsVKls912SbHey87bsXauL7nannya2CjtV7m37dNZhNe', 'uploads/avatars/9.png?v=1682495668', NULL, 2, '2023-04-26 15:54:27', '2023-04-26 16:02:36', ''),
(10, 'Nicos', 'Buenaflor', 'Panes', 'admin2', '$2y$10$nho3na7NKWLCk.1vctYcdOWbuaTsLixXtRAzwcff.8nLgW4EeyjAG', 'uploads/avatars/10.png?v=1725721026', NULL, 1, '2024-08-10 22:52:17', '2024-09-07 22:57:06', '');

-- --------------------------------------------------------

--
-- Table structure for table `user_member`
--

CREATE TABLE `user_member` (
  `id` int NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `college` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `course` varchar(255) NOT NULL,
  `year` varchar(50) NOT NULL,
  `section` varchar(50) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `user_type` enum('admin','user','guest') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_member`
--

INSERT INTO `user_member` (`id`, `first_name`, `last_name`, `verified`, `email`, `password`, `registration_date`, `college`, `status`, `course`, `year`, `section`, `avatar`, `user_type`) VALUES
(22, 'dano', 'dano', 0, 'dano@gmail.com', '$2y$10$WBYUdKAM2akNIG3S9K5WfeFhUEYvIYH1.O.U91xnZNANwt5u/zC4e', '2024-09-09 08:59:26', 'CTE', 'pending', 'Bachelor of Secondary Education - Filipino Education', '2nd - year', 'Section C', '354487000_832471788422999_3327184366459764259_n.jpg', 'user'),
(23, 'Mark Chester', 'Villanueva', 0, 'mark@gmail.com', '$2y$10$Tnq9iMMdfTC3ZRbrmgH8uOAhEruooAUlG4dgjWKM74M5fRD2u2pte', '2024-09-11 15:50:46', 'CAS', 'pending', 'Bachelor of Science in Biology', '4th - year', 'Section D', 'Vacuum-Stainless-Tumbler-BLUE-YOUR-LOGO-HERE.jpg', 'user');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category_list`
--
ALTER TABLE `category_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `claim_history`
--
ALTER TABLE `claim_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `claim_images`
--
ALTER TABLE `claim_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `claim_id` (`claim_id`);

--
-- Indexes for table `claim_requests`
--
ALTER TABLE `claim_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inquiry_list`
--
ALTER TABLE `inquiry_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `item_list`
--
ALTER TABLE `item_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_category_id` (`category_id`);

--
-- Indexes for table `message_history`
--
ALTER TABLE `message_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `message_images`
--
ALTER TABLE `message_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `message_images_ibfk_1` (`message_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `system_info`
--
ALTER TABLE `system_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_member`
--
ALTER TABLE `user_member`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `category_list`
--
ALTER TABLE `category_list`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `claim_history`
--
ALTER TABLE `claim_history`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `claim_images`
--
ALTER TABLE `claim_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `claim_requests`
--
ALTER TABLE `claim_requests`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inquiry_list`
--
ALTER TABLE `inquiry_list`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `item_list`
--
ALTER TABLE `item_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `message_history`
--
ALTER TABLE `message_history`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `message_images`
--
ALTER TABLE `message_images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_info`
--
ALTER TABLE `system_info`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_member`
--
ALTER TABLE `user_member`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `claims`
--
ALTER TABLE `claims`
  ADD CONSTRAINT `claims_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `item_list` (`id`);

--
-- Constraints for table `claim_history`
--
ALTER TABLE `claim_history`
  ADD CONSTRAINT `claim_history_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `item_list` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `claim_images`
--
ALTER TABLE `claim_images`
  ADD CONSTRAINT `claim_images_ibfk_1` FOREIGN KEY (`claim_id`) REFERENCES `claims` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `claim_requests`
--
ALTER TABLE `claim_requests`
  ADD CONSTRAINT `claim_requests_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `item_list` (`id`),
  ADD CONSTRAINT `claim_requests_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_member` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `item_list`
--
ALTER TABLE `item_list`
  ADD CONSTRAINT `fk_category_id` FOREIGN KEY (`category_id`) REFERENCES `category_list` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_history`
--
ALTER TABLE `message_history`
  ADD CONSTRAINT `message_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_member` (`id`);

--
-- Constraints for table `message_images`
--
ALTER TABLE `message_images`
  ADD CONSTRAINT `message_images_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `message_history` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_member` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
