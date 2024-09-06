-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 15, 2024 at 07:34 AM
-- Server version: 8.0.23
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
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `inquiry_list`
--

INSERT INTO `inquiry_list` (`id`, `fullname`, `contact`, `email`, `message`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Jane Doe', '09123546788', 'jdoe@mail.com', 'Vestibulum suscipit felis at magna congue gravida. Quisque interdum eu odio sed vulputate.', 1, '2023-05-01 14:11:19', '2023-05-01 14:25:47');

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

INSERT INTO `item_list` (`id`, `category_id`, `fullname`, `title`, `description`, `contact`, `image_path`, `status`, `created_at`, `updated`, `email`) VALUES
(1, 2, 'Mark Cooper', 'Found Keys at Central Park', 'Suspendisse nisl diam, pretium ut placerat nec, pellentesque in tortor. Suspendisse vitae arcu a mi dapibus elementum ac dignissim tellus. Duis vitae molestie lacus, porttitor lacinia justo. Ut vulputate, ipsum interdum consequat mollis, odio nisl vulputate est, quis ornare nisi massa a odio.', '09123564789', 'uploads/items/1.png?v=1682912925', 1, '2023-05-01 11:48:45', '2023-05-01 11:48:45', ''),
(3, 1, 'Claire Blake', 'Found an Android Phone @ Restaurant Parking Lot', 'Etiam accumsan quis augue a pulvinar. Etiam pretium sodales ipsum, cursus venenatis urna fringilla vel. Nunc fringilla non magna sit amet pharetra. Nam iaculis rutrum eleifend. Mauris rutrum, urna eget rhoncus consequat, purus mauris luctus orci, at venenatis ex elit sed risus.', '09123654897', 'uploads/items/3.png?v=1682916949', 1, '2023-05-01 12:55:48', '2023-05-01 12:55:49', ''),
(5, 3, 'Samantha Lou', 'Found a Watch left @ Room 101', 'Sed ultricies turpis eget commodo condimentum. Nam ac lorem vitae nulla fringilla imperdiet sit amet a arcu. Maecenas malesuada felis eleifend condimentum porttitor. Cras sed metus nec nibh interdum bibendum sit amet at sem.', '09457778988', 'uploads/items/5.png?v=1682917427', 1, '2023-05-01 13:03:47', '2023-05-01 13:03:47', ''),
(6, 1, 'Wilson Smith', 'Found Something @ The Mall', 'Donec metus sem, volutpat id mi in, fringilla aliquet odio. Donec eleifend sem et ex maximus tristique. Donec porttitor venenatis aliquet. Aliquam tristique est sed nulla fermentum aliquam eget sed ex', '09123564789', NULL, 2, '2023-05-01 13:34:29', '2023-05-01 14:04:10', ''),
(7, 1, 'Daga', 'daga', 'nawala sa daga', '12213123', 'uploads/items/7.png?v=1723298758', 1, '2024-08-10 22:05:58', '2024-08-10 22:06:28', ''),
(9, 1, 'mark', 'title', 'burat na pinasok', '09489403123', NULL, 1, '2024-08-12 23:02:47', '2024-08-12 23:20:47', ''),
(10, 1, 'test', 'dsadas', 'asdasd', '122323', 'uploads/items/10.png?v=1723476291', 0, '2024-08-12 23:24:51', '2024-08-12 23:24:51', '');

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
  `date_updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='2';

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `middlename`, `lastname`, `username`, `password`, `avatar`, `last_login`, `type`, `date_added`, `date_updated`) VALUES
(1, 'Adminstrator', '', 'Admin', 'admin', '$2y$10$lu9Lz9d61nsRRq5aXGOrmuik6tzhMif.AIQTmxgj4LTHf3M9hyGtW', 'uploads/avatars/1.png?v=1678760026', NULL, 1, '2021-01-20 14:02:37', '2023-04-26 16:01:02'),
(9, 'Claire', '', 'Blake', 'cblake', '$2y$10$DFEet3AmXnsVKls912SbHey87bsXauL7nannya2CjtV7m37dNZhNe', 'uploads/avatars/9.png?v=1682495668', NULL, 2, '2023-04-26 15:54:27', '2023-04-26 16:02:36'),
(10, 'Nicos', 'Buenaflor', 'Panes', 'admin2', '$2y$10$nho3na7NKWLCk.1vctYcdOWbuaTsLixXtRAzwcff.8nLgW4EeyjAG', 'uploads/avatars/10.png?v=1723301537', NULL, 1, '2024-08-10 22:52:17', '2024-08-10 22:52:17');

-- --------------------------------------------------------

--
-- Table structure for table `user_member`
--

CREATE TABLE `user_member` (
  `id` int NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `course_year_section` varchar(255) NOT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `registration_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `college` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_member`
--

INSERT INTO `user_member` (`id`, `first_name`, `last_name`, `course_year_section`, `verified`, `email`, `password`, `registration_date`, `college`) VALUES
(1, 'Darwin', 'Villanueva', 'BSINFOTECH 4 - C', 1, 'darwin@gmail.com', '$2y$10$MnOs1v0tPWb.gYA8m78B4OaaOeY8rvH6njRYpOMWgV05BkkgeEWA.', '2024-08-15 05:06:19', 'CCIT'),
(2, 'Menard', 'Bundang', 'BSHM 2 A', 1, 'menard@gmail.com', '$2y$10$5Lra0WUQemECnunw4lf/K.5La1IoietlPwsTg9N2gFlQ29Cj/LSjy', '2024-08-15 05:17:14', 'CTHM');

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
-- Indexes for table `category_list`
--
ALTER TABLE `category_list`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `category_list`
--
ALTER TABLE `category_list`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inquiry_list`
--
ALTER TABLE `inquiry_list`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `item_list`
--
ALTER TABLE `item_list`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
