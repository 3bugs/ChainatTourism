-- phpMyAdmin SQL Dump
-- version 4.1.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Aug 10, 2019 at 04:25 PM
-- Server version: 5.5.37
-- PHP Version: 5.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `5911011802058_mydb`
--

-- --------------------------------------------------------

--
-- Table structure for table `chainat_gallery`
--

CREATE TABLE IF NOT EXISTS `chainat_gallery` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `place_id` int(11) NOT NULL,
  `image_file_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=40 ;

--
-- Dumping data for table `chainat_gallery`
--

INSERT INTO `chainat_gallery` (`id`, `place_id`, `image_file_name`, `created_at`) VALUES
(1, 1, 'dam01.jpg', '2019-08-10 08:16:26'),
(2, 1, 'dam02.jpg', '2019-08-10 08:16:26'),
(3, 1, 'dam03.jpg', '2019-08-10 08:16:37'),
(4, 1, 'dam04.jpg', '2019-08-10 08:16:37'),
(5, 1, 'dam05.jpg', '2019-08-10 08:16:48'),
(6, 1, 'dam06.jpg', '2019-08-10 08:16:48'),
(7, 1, 'dam07.jpg', '2019-08-10 08:17:00'),
(8, 1, 'dam08.jpg', '2019-08-10 08:17:00'),
(9, 1, 'dam09.jpg', '2019-08-10 08:17:06'),
(10, 2, 'market01.jpg', '2019-08-10 08:47:34'),
(11, 2, 'market02.jpg', '2019-08-10 08:47:34'),
(12, 2, 'market03.jpg', '2019-08-10 08:47:46'),
(13, 2, 'market04.jpg', '2019-08-10 08:47:46'),
(14, 2, 'market05.jpg', '2019-08-10 08:47:56'),
(15, 2, 'market06.jpg', '2019-08-10 08:47:56'),
(16, 2, 'market07.jpg', '2019-08-10 08:48:08'),
(17, 2, 'market08.jpg', '2019-08-10 08:48:08'),
(18, 2, 'market09.jpg', '2019-08-10 08:48:18'),
(19, 2, 'market10.jpg', '2019-08-10 08:48:18'),
(20, 3, 'museum01.jpg', '2019-08-10 09:02:21'),
(21, 3, 'museum02.jpg', '2019-08-10 09:02:21'),
(22, 3, 'museum03.jpg', '2019-08-10 09:02:32'),
(23, 3, 'museum04.jpg', '2019-08-10 09:02:32'),
(24, 3, 'museum05.jpg', '2019-08-10 09:02:43'),
(25, 3, 'museum06.jpg', '2019-08-10 09:02:43'),
(26, 3, 'museum07.jpg', '2019-08-10 09:02:53'),
(27, 3, 'museum08.jpg', '2019-08-10 09:02:53'),
(28, 3, 'museum09.jpg', '2019-08-10 09:03:06'),
(29, 3, 'museum10.jpg', '2019-08-10 09:03:06'),
(30, 3, 'museum11.jpg', '2019-08-10 09:03:12'),
(31, 4, 'som01.jpg', '2019-08-10 09:12:06'),
(32, 4, 'som02.jpg', '2019-08-10 09:12:06'),
(33, 4, 'som03.jpg', '2019-08-10 09:12:19'),
(34, 4, 'som04.jpg', '2019-08-10 09:12:19'),
(35, 4, 'som05.jpg', '2019-08-10 09:12:31'),
(36, 4, 'som06.jpg', '2019-08-10 09:12:31'),
(37, 4, 'som07.jpg', '2019-08-10 09:12:42'),
(38, 4, 'som08.jpg', '2019-08-10 09:12:42'),
(39, 4, 'som09.jpg', '2019-08-10 09:12:48');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
