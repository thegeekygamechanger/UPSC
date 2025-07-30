-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 31, 2025 at 12:21 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `upsc_todo_app`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateTodoWithItems` (IN `p_user_id` INT, IN `p_title` VARCHAR(255), IN `p_description` TEXT, IN `p_priority` VARCHAR(20), IN `p_due_date` DATETIME, IN `p_items` JSON)   BEGIN
    DECLARE todo_list_id INT;
    DECLARE i INT DEFAULT 0;
    DECLARE items_count INT;
    
    START TRANSACTION;
    
    -- Insert todo list
    INSERT INTO todo_lists (user_id, title, description, priority, due_date)
    VALUES (p_user_id, p_title, p_description, p_priority, p_due_date);
    
    SET todo_list_id = LAST_INSERT_ID();
    
    -- Insert todo items
    SET items_count = JSON_LENGTH(p_items);
    
    WHILE i < items_count DO
        INSERT INTO todo_items (todo_list_id, content, category_id, position)
        VALUES (
            todo_list_id,
            JSON_UNQUOTE(JSON_EXTRACT(p_items, CONCAT('$[', i, '].content'))),
            JSON_EXTRACT(p_items, CONCAT('$[', i, '].category_id')),
            i
        );
        SET i = i + 1;
    END WHILE;
    
    COMMIT;
    
    SELECT todo_list_id as new_todo_list_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetPendingReminders` ()   BEGIN
    SELECT 
        tl.id,
        tl.user_id,
        tl.title,
        tl.due_date,
        u.name,
        u.email,
        u.phone_number
    FROM todo_lists tl
    JOIN users u ON tl.user_id = u.id
    WHERE tl.reminder_sent = FALSE
    AND tl.status != 'completed'
    AND tl.due_date IS NOT NULL
    AND tl.due_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 90 MINUTE)
    AND u.email IS NOT NULL;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `color` varchar(7) DEFAULT '#3B82F6',
  `icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `color`, `icon`, `created_at`) VALUES
(1, 'General Studies', '#3B82F6', 'book', '2025-07-30 21:08:43'),
(2, 'Current Affairs', '#10B981', 'newspaper', '2025-07-30 21:08:43'),
(3, 'Essay Writing', '#F59E0B', 'pen', '2025-07-30 21:08:43'),
(4, 'Optional Subject', '#8B5CF6', 'graduation-cap', '2025-07-30 21:08:43'),
(5, 'Revision', '#EF4444', 'refresh', '2025-07-30 21:08:43');

-- --------------------------------------------------------

--
-- Table structure for table `community_messages`
--

CREATE TABLE `community_messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `is_edited` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `current_affairs`
--

CREATE TABLE `current_affairs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `source` varchar(255) DEFAULT NULL,
  `importance` enum('low','medium','high') DEFAULT 'medium',
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `daily_progress`
-- (See below for the actual view)
--
CREATE TABLE `daily_progress` (
`user_id` int(11)
,`name` varchar(100)
,`completion_date` date
,`items_completed` bigint(21)
,`hours_studied` decimal(4,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `motivational_quotes`
--

CREATE TABLE `motivational_quotes` (
  `id` int(11) NOT NULL,
  `quote` text NOT NULL,
  `author` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `motivational_quotes`
--

INSERT INTO `motivational_quotes` (`id`, `quote`, `author`, `category`, `is_active`, `created_at`) VALUES
(1, 'Success is not final, failure is not fatal: it is the courage to continue that counts.', 'Winston Churchill', 'perseverance', 1, '2025-07-30 21:10:53'),
(2, 'The future depends on what you do today.', 'Mahatma Gandhi', 'action', 1, '2025-07-30 21:10:53'),
(3, 'Dreams are not what you see in sleep, they are things which do not let you sleep.', 'Dr. APJ Abdul Kalam', 'dreams', 1, '2025-07-30 21:10:53'),
(4, 'Winners are not those who never fail but those who never quit.', 'Unknown', 'persistence', 1, '2025-07-30 21:10:53'),
(5, 'The best time to plant a tree was 20 years ago. The second best time is now.', 'Chinese Proverb', 'action', 1, '2025-07-30 21:10:53');

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `is_pinned` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_logs`
--

CREATE TABLE `notification_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('email','sms') NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `sent_at` datetime DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `privacy_articles`
--

CREATE TABLE `privacy_articles` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `privacy_articles`
--

INSERT INTO `privacy_articles` (`id`, `title`, `content`, `category`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 'Your Privacy Matters', 'At UPSC Todo App, we are committed to protecting your privacy and ensuring the security of your personal information. This article explains how we collect, use, and safeguard your data while providing you with the best study planning experience.\r\n\r\n**What Information We Collect:**\r\n- Phone number for authentication\r\n- Name and email for personalized reminders\r\n- Study progress and todo items\r\n- Community interactions\r\n\r\n**How We Use Your Information:**\r\n- To send study reminders via email/SMS\r\n- To track your progress and provide insights\r\n- To enable community features\r\n- To improve our services\r\n\r\n**Data Security:**\r\n- All passwords are encrypted\r\n- Secure HTTPS connections\r\n- Regular security audits\r\n- No sharing with third parties\r\n\r\n**Your Rights:**\r\n- Access your data anytime\r\n- Update or delete your information\r\n- Opt-out of notifications\r\n- Export your data\r\n\r\nRemember, your success is our priority, and protecting your privacy is fundamental to building trust.', 'privacy', 1, '2025-07-30 21:13:36', '2025-07-30 21:13:36');

-- --------------------------------------------------------

--
-- Table structure for table `study_credits`
--

CREATE TABLE `study_credits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `hours_studied` decimal(4,2) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `todo_items`
--

CREATE TABLE `todo_items` (
  `id` int(11) NOT NULL,
  `todo_list_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `content` text NOT NULL,
  `is_completed` tinyint(1) DEFAULT 0,
  `completed_at` datetime DEFAULT NULL,
  `position` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `todo_lists`
--

CREATE TABLE `todo_lists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('pending','in_progress','completed') DEFAULT 'pending',
  `due_date` datetime DEFAULT NULL,
  `reminder_sent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `user_todo_summary`
-- (See below for the actual view)
--
CREATE TABLE `user_todo_summary` (
`user_id` int(11)
,`name` varchar(100)
,`total_lists` bigint(21)
,`total_items` bigint(21)
,`completed_items` decimal(22,0)
,`last_activity` timestamp
);

-- --------------------------------------------------------

--
-- Structure for view `daily_progress`
--
DROP TABLE IF EXISTS `daily_progress`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `daily_progress`  AS SELECT `u`.`id` AS `user_id`, `u`.`name` AS `name`, cast(`ti`.`completed_at` as date) AS `completion_date`, count(`ti`.`id`) AS `items_completed`, `sc`.`hours_studied` AS `hours_studied` FROM (((`users` `u` join `todo_lists` `tl` on(`u`.`id` = `tl`.`user_id`)) join `todo_items` `ti` on(`tl`.`id` = `ti`.`todo_list_id` and `ti`.`is_completed` = 1)) left join `study_credits` `sc` on(`u`.`id` = `sc`.`user_id` and cast(`ti`.`completed_at` as date) = `sc`.`date`)) GROUP BY `u`.`id`, `u`.`name`, cast(`ti`.`completed_at` as date), `sc`.`hours_studied` ;

-- --------------------------------------------------------

--
-- Structure for view `user_todo_summary`
--
DROP TABLE IF EXISTS `user_todo_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_todo_summary`  AS SELECT `u`.`id` AS `user_id`, `u`.`name` AS `name`, count(distinct `tl`.`id`) AS `total_lists`, count(distinct `ti`.`id`) AS `total_items`, sum(case when `ti`.`is_completed` = 1 then 1 else 0 end) AS `completed_items`, max(`tl`.`updated_at`) AS `last_activity` FROM ((`users` `u` left join `todo_lists` `tl` on(`u`.`id` = `tl`.`user_id`)) left join `todo_items` `ti` on(`tl`.`id` = `ti`.`todo_list_id`)) GROUP BY `u`.`id`, `u`.`name` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `community_messages`
--
ALTER TABLE `community_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_community_messages_user` (`user_id`,`created_at`);

--
-- Indexes for table `current_affairs`
--
ALTER TABLE `current_affairs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_date_importance` (`date`,`importance`);

--
-- Indexes for table `motivational_quotes`
--
ALTER TABLE `motivational_quotes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_user_pinned` (`user_id`,`is_pinned`),
  ADD KEY `idx_notes_user_category` (`user_id`,`category_id`,`is_pinned`);

--
-- Indexes for table `notification_logs`
--
ALTER TABLE `notification_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_status` (`user_id`,`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `privacy_articles`
--
ALTER TABLE `privacy_articles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `study_credits`
--
ALTER TABLE `study_credits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_date` (`user_id`,`date`),
  ADD KEY `idx_user_date` (`user_id`,`date`);

--
-- Indexes for table `todo_items`
--
ALTER TABLE `todo_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_list_position` (`todo_list_id`,`position`);

--
-- Indexes for table `todo_lists`
--
ALTER TABLE `todo_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_status` (`user_id`,`status`),
  ADD KEY `idx_due_date` (`due_date`),
  ADD KEY `idx_todo_reminder` (`reminder_sent`,`status`,`due_date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone_number` (`phone_number`),
  ADD KEY `idx_phone` (`phone_number`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_token` (`session_token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `community_messages`
--
ALTER TABLE `community_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `current_affairs`
--
ALTER TABLE `current_affairs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `motivational_quotes`
--
ALTER TABLE `motivational_quotes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_logs`
--
ALTER TABLE `notification_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `privacy_articles`
--
ALTER TABLE `privacy_articles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `study_credits`
--
ALTER TABLE `study_credits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `todo_items`
--
ALTER TABLE `todo_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `todo_lists`
--
ALTER TABLE `todo_lists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `community_messages`
--
ALTER TABLE `community_messages`
  ADD CONSTRAINT `community_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `community_messages_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `community_messages` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notification_logs`
--
ALTER TABLE `notification_logs`
  ADD CONSTRAINT `notification_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `study_credits`
--
ALTER TABLE `study_credits`
  ADD CONSTRAINT `study_credits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `todo_items`
--
ALTER TABLE `todo_items`
  ADD CONSTRAINT `todo_items_ibfk_1` FOREIGN KEY (`todo_list_id`) REFERENCES `todo_lists` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `todo_items_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `todo_lists`
--
ALTER TABLE `todo_lists`
  ADD CONSTRAINT `todo_lists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
