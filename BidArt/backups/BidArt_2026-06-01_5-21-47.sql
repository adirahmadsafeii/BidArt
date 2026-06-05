-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: BidArt_db
-- ------------------------------------------------------
-- Server version	8.0.30

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `artworks`
--

DROP TABLE IF EXISTS `artworks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `artworks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text,
  `artist_id` int NOT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `artist_id` (`artist_id`),
  CONSTRAINT `artworks_ibfk_1` FOREIGN KEY (`artist_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `artworks`
--

LOCK TABLES `artworks` WRITE;
/*!40000 ALTER TABLE `artworks` DISABLE KEYS */;
INSERT INTO `artworks` VALUES (16,'The Lady','Dari kerajaan yunan 300SM',2,'assets/uploads/art_684c96613ca2b.jpeg','2025-06-13 21:21:37'),(17,'Trio Owl','Peninggalan Keluarga, dari belanda',2,'assets/uploads/art_684c96af9eec6.jpeg','2025-06-13 21:22:55'),(18,'Hug','-',2,'assets/uploads/art_684c9762c1c76.jpeg','2025-06-13 21:25:54'),(19,'Kaligrafi','--',2,'assets/uploads/art_684c97b9d70a5.jpeg','2025-06-13 21:27:21');
/*!40000 ALTER TABLE `artworks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `auctions`
--

DROP TABLE IF EXISTS `auctions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `auctions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `artwork_id` int NOT NULL,
  `starting_price` decimal(15,2) NOT NULL,
  `current_price` decimal(15,2) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `status` enum('active','closed','cancelled') DEFAULT 'active',
  `winner_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `artwork_id` (`artwork_id`),
  KEY `winner_id` (`winner_id`),
  CONSTRAINT `auctions_ibfk_1` FOREIGN KEY (`artwork_id`) REFERENCES `artworks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `auctions_ibfk_2` FOREIGN KEY (`winner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `auctions`
--

LOCK TABLES `auctions` WRITE;
/*!40000 ALTER TABLE `auctions` DISABLE KEYS */;
INSERT INTO `auctions` VALUES (10,16,340000.00,400000.00,'2025-06-14 04:21:37','2025-06-16 04:21:00','active',NULL,'2025-06-13 21:21:37'),(11,17,750000.00,750000.00,'2025-06-14 04:22:55','2025-06-19 04:22:00','active',NULL,'2025-06-13 21:22:55'),(12,18,250000.00,250000.00,'2025-06-14 04:25:54','2025-06-20 04:25:00','active',NULL,'2025-06-13 21:25:54'),(13,19,750000.00,750000.00,'2025-06-14 04:27:21','2025-06-17 04:27:00','active',NULL,'2025-06-13 21:27:21');
/*!40000 ALTER TABLE `auctions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bids`
--

DROP TABLE IF EXISTS `bids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bids` (
  `id` int NOT NULL AUTO_INCREMENT,
  `auction_id` int NOT NULL,
  `bidder_id` int NOT NULL,
  `bid_amount` decimal(15,2) NOT NULL,
  `bid_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `auction_id` (`auction_id`),
  KEY `bidder_id` (`bidder_id`),
  CONSTRAINT `bids_ibfk_1` FOREIGN KEY (`auction_id`) REFERENCES `auctions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bids_ibfk_2` FOREIGN KEY (`bidder_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bids`
--

LOCK TABLES `bids` WRITE;
/*!40000 ALTER TABLE `bids` DISABLE KEYS */;
INSERT INTO `bids` VALUES (8,10,3,350000.00,'2025-06-13 22:08:14'),(9,10,3,400000.00,'2025-06-13 22:17:23');
/*!40000 ALTER TABLE `bids` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `tr_check_auction_end` AFTER INSERT ON `bids` FOR EACH ROW BEGIN

    DECLARE v_end_time DATETIME;

    DECLARE v_status VARCHAR(20);

    

    -- Get auction end time and status

    SELECT end_time, status INTO v_end_time, v_status

    FROM auctions 

    WHERE id = NEW.auction_id;

    

    -- If auction has ended, close it

    IF NOW() >= v_end_time AND v_status = 'active' THEN

        CALL sp_tutup_lelang(NEW.auction_id);

    END IF;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `tr_bid_activity_log` AFTER INSERT ON `bids` FOR EACH ROW BEGIN

    INSERT INTO transactions (user_id, auction_id, type, amount, description, status)

    VALUES (NEW.bidder_id, NEW.auction_id, 'deposit', NEW.bid_amount, 

            CONCAT('Bid placed for auction ID: ', NEW.auction_id), 'completed');

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `tr_update_balance_after_bid` AFTER INSERT ON `bids` FOR EACH ROW BEGIN

    -- Reserve the bid amount (hold it)

    UPDATE users 

    SET balance = balance - NEW.bid_amount 

    WHERE id = NEW.bidder_id;

    

    -- Refund previous highest bidder if exists

    UPDATE users u

    INNER JOIN (

        SELECT bidder_id, bid_amount

        FROM bids 

        WHERE auction_id = NEW.auction_id 

        AND id < NEW.id

        ORDER BY bid_amount DESC, bid_time ASC

        LIMIT 1

    ) prev_bid ON u.id = prev_bid.bidder_id

    SET u.balance = u.balance + prev_bid.bid_amount

    WHERE prev_bid.bidder_id != NEW.bidder_id;

END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `auction_id` int DEFAULT NULL,
  `type` enum('deposit','withdrawal','payment','refund') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` text,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `auction_id` (`auction_id`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`auction_id`) REFERENCES `auctions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (11,3,NULL,'deposit',130.00,'Bid placed for auction ID: 9','completed','2025-06-12 16:14:36'),(12,3,NULL,'deposit',1000000.00,'Bid placed for auction ID: 8','completed','2025-06-12 22:32:40'),(13,3,NULL,'payment',1000000.00,'Payment for artwork purchase','completed','2025-06-13 00:37:30'),(14,2,NULL,'deposit',900000.00,'Payment received from artwork sale','completed','2025-06-13 00:37:30'),(15,3,NULL,'payment',130.00,'Payment for artwork purchase','completed','2025-06-13 21:17:24'),(16,2,NULL,'deposit',117.00,'Payment received from artwork sale','completed','2025-06-13 21:17:24'),(17,3,10,'deposit',350000.00,'Bid placed for auction ID: 10','completed','2025-06-13 22:08:14'),(18,3,10,'deposit',400000.00,'Bid placed for auction ID: 10','completed','2025-06-13 22:17:23');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('artist','buyer','admin') NOT NULL,
  `balance` decimal(15,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin@arthub.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Administrator','admin',0.00,'2025-06-11 05:46:11','2025-06-11 05:46:11'),(2,'artist','artist1@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Lian','artist',913707.00,'2025-06-11 05:46:11','2025-06-13 21:27:39'),(3,'buyer','buyer1@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Elen Fitria','buyer',97206458.00,'2025-06-11 05:46:11','2025-06-13 22:17:23');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'BidArt_db'
--
/*!50003 DROP FUNCTION IF EXISTS `get_highest_bid` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `get_highest_bid`(auction_id_param INT) RETURNS decimal(15,2)
    READS SQL DATA
    DETERMINISTIC
BEGIN

    DECLARE highest_bid DECIMAL(15,2) DEFAULT 0.00;

    

    SELECT COALESCE(MAX(bid_amount), 0.00) INTO highest_bid

    FROM bids 

    WHERE auction_id = auction_id_param;

    

    RETURN highest_bid;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP FUNCTION IF EXISTS `hitung_total_bid` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` FUNCTION `hitung_total_bid`(auction_id_param INT) RETURNS decimal(15,2)
    READS SQL DATA
    DETERMINISTIC
BEGIN

    DECLARE total_amount DECIMAL(15,2) DEFAULT 0.00;

    

    SELECT COALESCE(SUM(bid_amount), 0.00) INTO total_amount

    FROM bids 

    WHERE auction_id = auction_id_param;

    

    RETURN total_amount;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_place_bid` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_place_bid`(

    IN p_auction_id INT,

    IN p_bidder_id INT,

    IN p_bid_amount DECIMAL(15,2)

)
BEGIN

    DECLARE v_current_price DECIMAL(15,2);

    DECLARE v_user_balance DECIMAL(15,2);

    DECLARE v_auction_status VARCHAR(20);

    DECLARE v_end_time DATETIME;

    

    DECLARE EXIT HANDLER FOR SQLEXCEPTION

    BEGIN

        ROLLBACK;

        RESIGNAL;

    END;

    

    START TRANSACTION;

    

    -- Check auction status and current price

    SELECT current_price, status, end_time INTO v_current_price, v_auction_status, v_end_time

    FROM auctions WHERE id = p_auction_id;

    

    -- Check user balance

    SELECT balance INTO v_user_balance FROM users WHERE id = p_bidder_id;

    

    -- Validations

    IF v_auction_status != 'active' THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Auction is not active';

    END IF;

    

    IF NOW() > v_end_time THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Auction has ended';

    END IF;

    

    IF p_bid_amount <= v_current_price THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Bid must be higher than current price';

    END IF;

    

    IF v_user_balance < p_bid_amount THEN

        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Insufficient balance';

    END IF;

    

    -- Place bid

    INSERT INTO bids (auction_id, bidder_id, bid_amount) 

    VALUES (p_auction_id, p_bidder_id, p_bid_amount);

    

    -- Update current price

    UPDATE auctions SET current_price = p_bid_amount WHERE id = p_auction_id;

    

    COMMIT;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_tambah_karya_seni` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_tambah_karya_seni`(

    IN p_title VARCHAR(200),

    IN p_description TEXT,

    IN p_artist_id INT,

    IN p_starting_price DECIMAL(15,2),

    IN p_image_path VARCHAR(500)

)
BEGIN

    DECLARE artwork_id INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION

    BEGIN

        ROLLBACK;

        RESIGNAL;

    END;

    

    START TRANSACTION;

    

    -- Insert artwork

    INSERT INTO artworks (title, description, artist_id, image_path)

    VALUES (p_title, p_description, p_artist_id, p_image_path);

    

    SET artwork_id = LAST_INSERT_ID();

    

    -- Create auction (7 days from now)

    INSERT INTO auctions (artwork_id, starting_price, current_price, start_time, end_time)

    VALUES (artwork_id, p_starting_price, p_starting_price, NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY));

    

    COMMIT;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 DROP PROCEDURE IF EXISTS `sp_tutup_lelang` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_tutup_lelang`(IN p_auction_id INT)
BEGIN

    DECLARE v_winner_id INT DEFAULT NULL;

    DECLARE v_highest_bid DECIMAL(15,2) DEFAULT 0.00;

    DECLARE v_artist_id INT;

    DECLARE v_artwork_id INT;

    

    DECLARE EXIT HANDLER FOR SQLEXCEPTION

    BEGIN

        ROLLBACK;

        RESIGNAL;

    END;

    

    START TRANSACTION;

    

    -- Get auction details

    SELECT artwork_id INTO v_artwork_id FROM auctions WHERE id = p_auction_id;

    SELECT artist_id INTO v_artist_id FROM artworks WHERE id = v_artwork_id;

    

    -- Find winner (highest bidder)

    SELECT bidder_id, bid_amount INTO v_winner_id, v_highest_bid

    FROM bids 

    WHERE auction_id = p_auction_id 

    ORDER BY bid_amount DESC, bid_time ASC 

    LIMIT 1;

    

    -- Update auction status

    UPDATE auctions 

    SET status = 'closed', winner_id = v_winner_id, current_price = v_highest_bid

    WHERE id = p_auction_id;

    

    -- If there's a winner, process payment

    IF v_winner_id IS NOT NULL THEN

        -- Deduct from winner's balance

        UPDATE users SET balance = balance - v_highest_bid WHERE id = v_winner_id;

        

        -- Add to artist's balance (minus 10% commission)

        UPDATE users SET balance = balance + (v_highest_bid * 0.9) WHERE id = v_artist_id;

        

        -- Record transactions

        INSERT INTO transactions (user_id, auction_id, type, amount, description, status)

        VALUES (v_winner_id, p_auction_id, 'payment', v_highest_bid, 'Payment for artwork purchase', 'completed');

        

        INSERT INTO transactions (user_id, auction_id, type, amount, description, status)

        VALUES (v_artist_id, p_auction_id, 'deposit', v_highest_bid * 0.9, 'Payment received from artwork sale', 'completed');

    END IF;

    

    COMMIT;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-06-14  5:21:47
