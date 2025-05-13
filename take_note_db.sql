SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- Database: `take_note_db`

-- Table structure for table `users`
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `activation_token` varchar(64) DEFAULT NULL,
  `is_activated` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `users`
INSERT INTO `users` (`user_id`, `email`, `display_name`, `password`, `activation_token`, `is_activated`, `created_at`) VALUES
(1, 'welcome@example.com', 'Welcome User', '$2y$10$examplehashedpassword1234567890abcdef', NULL, 1, '2023-08-29 01:43:23');

-- Table structure for table `tbl_notes`
CREATE TABLE `tbl_notes` (
  `tbl_notes_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `note_title` text NOT NULL,
  `note` longtext NOT NULL,
  `date_time` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`tbl_notes_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table `tbl_notes`
INSERT INTO `tbl_notes` (`tbl_notes_id`, `user_id`, `note_title`, `note`, `date_time`) VALUES
(1, 1, 'Welcome!!!', 'Start your first note here!!!', '2023-08-29 01:43:23');

-- AUTO_INCREMENT for table `users`
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- AUTO_INCREMENT for table `tbl_notes`
ALTER TABLE `tbl_notes`
  MODIFY `tbl_notes_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
