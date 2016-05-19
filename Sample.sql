-- phpMyAdmin SQL Dump
-- version 4.6.0deb1.trusty~ppa.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 19, 2016 at 02:49 PM
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
-- Table structure for table `Sessions`
--

CREATE TABLE `Sessions` (
  `SessionID` varchar(50) NOT NULL,
  `FirstScan` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `LastScan` timestamp NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `Sessions`
--

INSERT INTO `Sessions` (`SessionID`, `FirstScan`, `LastScan`) VALUES
('test-s0', '2016-05-17 14:50:25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `TagList`
--

CREATE TABLE `TagList` (
  `ServiceTag` varchar(8) NOT NULL,
  `WhichList` set('Imported-Good','Imported-Bad','Manually-Scanned','') NOT NULL,
  `OrderNumber` int(11) DEFAULT NULL,
  `Found` tinyint(1) NOT NULL,
  `FoundInSession` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `TagList`
--

INSERT INTO `TagList` (`ServiceTag`, `WhichList`, `OrderNumber`, `Found`, `FoundInSession`) VALUES
('IBAS123', 'Imported-Bad', 654321, 1, 'test-s0'),
('IBNYS12', 'Imported-Bad', 654321, 0, 'test-s0'),
('IGAS123', 'Imported-Good', 123456, 1, 'test-s0'),
('IGNYS12', 'Imported-Good', 123456, 0, 'test-s0'),
('ONL1234', 'Manually-Scanned', NULL, 0, 'test-s0');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Sessions`
--
ALTER TABLE `Sessions`
  ADD PRIMARY KEY (`SessionID`);

--
-- Indexes for table `TagList`
--
ALTER TABLE `TagList`
  ADD PRIMARY KEY (`ServiceTag`),
  ADD KEY `Sessions` (`FoundInSession`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `TagList`
--
ALTER TABLE `TagList`
  ADD CONSTRAINT `Sessions` FOREIGN KEY (`FoundInSession`) REFERENCES `Sessions` (`SessionID`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
