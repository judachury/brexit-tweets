-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.7.24 - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             10.2.0.5599
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping database structure for brexit_tweets
CREATE DATABASE IF NOT EXISTS `brexit_tweets` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;
USE `brexit_tweets`;

-- Dumping structure for table brexit_tweets.tweets
CREATE TABLE IF NOT EXISTS `tweets` (
  `id` bigint(250) NOT NULL AUTO_INCREMENT,
  `tweet_id` bigint(100) NOT NULL,
  `tweet_user_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `tweet_text` varchar(285) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `tweet_create_at` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tweet_user_screenname` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tweet_user_image_url` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter_account_location` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tweet_location` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sentiment` enum('positive','negative','neutral') COLLATE utf8mb4_unicode_ci DEFAULT 'neutral',
  `sentiment_icon` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `Index 2` (`tweet_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Store all new twitter comments about Brexit';

-- Dumping data for table brexit_tweets.tweets: ~0 rows (approximately)
DELETE FROM `tweets`;
/*!40000 ALTER TABLE `tweets` DISABLE KEYS */;
/*!40000 ALTER TABLE `tweets` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
