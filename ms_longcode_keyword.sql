-- phpMyAdmin SQL Dump
-- version 3.5.8.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 03, 2014 at 12:38 PM
-- Server version: 5.5.38-log
-- PHP Version: 5.4.29

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `test_betatest`
--

-- --------------------------------------------------------

--
-- Table structure for table `ms_longcode_keyword`
--

CREATE TABLE IF NOT EXISTS `ms_longcode_keyword` (
  `key_id` int(11) NOT NULL AUTO_INCREMENT,
  `longcode` bigint(20) NOT NULL,
  `keyword` varchar(20) NOT NULL,
  `user_pid` int(11) NOT NULL,
  `senderid` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `url` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`key_id`),
  KEY `longcode` (`longcode`,`keyword`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2060 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
