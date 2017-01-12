-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jan 10, 2017 at 10:30 PM
-- Server version: 10.1.19-MariaDB
-- PHP Version: 5.6.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ececlub_dinnerdance`
--

-- --------------------------------------------------------

--
-- Table structure for table `tables`
--

CREATE TABLE `tables` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `size` tinyint(4) NOT NULL DEFAULT '10',
  `num_members` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tables`
--

INSERT INTO `tables` (`id`, `size`, `num_members`) VALUES
(1, 10, 0),
(2, 10, 0),
(3, 10, 0),
(4, 10, 0),
(5, 10, 0),
(6, 10, 0),
(7, 10, 0),
(8, 10, 0),
(9, 10, 0),
(10, 10, 0),
(11, 10, 0),
(12, 10, 0),
(13, 10, 0),
(14, 10, 0),
(15, 10, 0),
(16, 10, 0),
(17, 10, 0),
(18, 10, 0),
(19, 10, 0),
(20, 10, 0),
(21, 10, 0),
(22, 10, 0),
(23, 10, 0),
(24, 10, 0),
(25, 10, 0),
(26, 10, 0),
(27, 10, 0),
(28, 10, 0),
(29, 10, 0),
(30, 10, 0),
(31, 10, 0),
(32, 10, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_num` int(11) NOT NULL,
  `dinnerdance_year` year(4) NOT NULL,
  `email` text NOT NULL,
  `first_name` varchar(20) DEFAULT NULL,
  `last_name` varchar(20) DEFAULT NULL,
  `display_name` varchar(40) DEFAULT NULL,
  `password` text NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `is_drinking_ticket` tinyint(1) NOT NULL DEFAULT '1',
  `is_early_bird` tinyint(1) NOT NULL DEFAULT '0',
  `reset_link` text,
  `reset_time` timestamp NULL DEFAULT NULL,
  `creation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `login_attempts` tinyint(4) NOT NULL DEFAULT '0',
  `account_lockout_time` timestamp NULL DEFAULT NULL,
  `is_activated` tinyint(1) NOT NULL DEFAULT '0',
  `year` varchar(3) DEFAULT NULL,
  `food` text,
  `table_num` int(11) DEFAULT NULL,
  `drinking_age` tinyint(1) DEFAULT NULL,
  `allergies` text,
  `bus_depart` int(11) DEFAULT NULL,
  `bus_return` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tables`
--
ALTER TABLE `tables`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD UNIQUE KEY `uq_dd_users` (`ticket_num`,`dinnerdance_year`),
  ADD UNIQUE KEY `id` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tables`
--
ALTER TABLE `tables`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
