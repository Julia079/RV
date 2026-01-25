-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 22, 2026 at 03:59 PM
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
-- Database: `rv`
--
CREATE DATABASE IF NOT EXISTS `rv` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `rv`;
-- --------------------------------------------------------

--
-- Table structure for table `tbl_movie_review`
--

CREATE TABLE `tbl_movie_review` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `movie_title` varchar(255) DEFAULT NULL,
  `review` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_movie_review`
--

INSERT INTO `tbl_movie_review` (`id`, `user_id`, `movie_id`, `movie_title`, `review`, `created_at`) VALUES
(1, 1, 83533, 'Avatar: Fire and Ash', 'Avatar: Fire and Ash delivers a visually stunning return to Pandora, blending breathtaking effects with a darker, more intense emotional tone. The film expands the world and its conflicts, exploring themes of anger, loss, and survival while maintaining James Cameron’s signature spectacle. Though the story feels familiar at times, the powerful visuals and immersive atmosphere make it a compelling and memorable cinematic experience.', '2026-01-22 12:44:57');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'mrnmtchell', 'Maureene Mitchell', 'mtmitchell@gmail.com', '$2y$10$ZbNepJeaDIt5K2XYKM2CxOiyTMwsuu3mz.p6dnDyDvkwhGpD1xMta', '2026-01-22 10:53:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_movie_review`
--
ALTER TABLE `tbl_movie_review`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_movie_review`
--
ALTER TABLE `tbl_movie_review`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_movie_review`
--
ALTER TABLE `tbl_movie_review`
  ADD CONSTRAINT `tbl_movie_review_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
