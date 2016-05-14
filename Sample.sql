-- phpMyAdmin SQL Dump
-- version 4.6.0deb1.trusty~ppa.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 14, 2016 at 01:50 AM
-- Server version: 5.5.49-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1+sury.org~quantal+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ServiceTags`
--

-- --------------------------------------------------------

--
-- Table structure for table `TagList`
--

CREATE TABLE `TagList` (
  `ServiceTag` varchar(8) NOT NULL,
  `WhichList` set('Imported-Good','Imported-Bad','Manually-Scanned','') NOT NULL,
  `Found` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `TagList`
--

INSERT INTO `TagList` (`ServiceTag`, `WhichList`, `Found`) VALUES
('ABC123', 'Manually-Scanned', 1),
('ABCD1234', 'Manually-Scanned', 1),
('IBAS123', 'Imported-Bad', 1),
('IBNYS12', 'Imported-Bad', 0),
('IGAS123', 'Imported-Good', 1),
('IGNYS12', 'Imported-Good', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `TagList`
--
ALTER TABLE `TagList`
  ADD PRIMARY KEY (`ServiceTag`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
