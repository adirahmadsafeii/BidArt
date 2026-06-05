-- IMPLEMENTASI MYSQL FUNCTION
-- Function untuk menghitung total bid dalam suatu lelang
DELIMITER //

CREATE FUNCTION hitung_total_bid(auction_id_param INT) 
RETURNS DECIMAL(15,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE total_amount DECIMAL(15,2) DEFAULT 0.00;
    
    SELECT COALESCE(SUM(bid_amount), 0.00) INTO total_amount
    FROM bids 
    WHERE auction_id = auction_id_param;
    
    RETURN total_amount;
END //

DELIMITER ;

-- Function untuk mendapatkan bid tertinggi
DELIMITER //

CREATE FUNCTION get_highest_bid(auction_id_param INT) 
RETURNS DECIMAL(15,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE highest_bid DECIMAL(15,2) DEFAULT 0.00;
    
    SELECT COALESCE(MAX(bid_amount), 0.00) INTO highest_bid
    FROM bids 
    WHERE auction_id = auction_id_param;
    
    RETURN highest_bid;
END //

DELIMITER ;

-- Function untuk menghitung total pengeluaran seorang user
DELIMITER //

CREATE FUNCTION get_user_total_spent(user_id_param INT) 
RETURNS DECIMAL(15,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE total_spent DECIMAL(15,2) DEFAULT 0.00;
    
    SELECT COALESCE(SUM(amount), 0.00) INTO total_spent
    FROM transactions 
    WHERE user_id = user_id_param AND type = 'payment' AND status = 'completed';
    
    RETURN total_spent;
END //

DELIMITER ;

-- Function untuk menghitung jumlah bidder unik dalam suatu lelang
DELIMITER //

CREATE FUNCTION hitung_jumlah_bidder(auction_id_param INT) 
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE jumlah INT DEFAULT 0;
    
    SELECT COUNT(DISTINCT bidder_id) INTO jumlah
    FROM bids 
    WHERE auction_id = auction_id_param;
    
    RETURN jumlah;
END //

DELIMITER ;

-- Function untuk mendapatkan label status lelang (active/ending soon/closed)
DELIMITER //

CREATE FUNCTION get_auction_status_label(auction_id_param INT) 
RETURNS VARCHAR(50)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE v_status VARCHAR(20);
    DECLARE v_time_left INT;
    DECLARE label VARCHAR(50);
    
    SELECT status, TIMESTAMPDIFF(SECOND, NOW(), end_time) 
    INTO v_status, v_time_left
    FROM auctions WHERE id = auction_id_param;
    
    IF v_status = 'closed' THEN
        SET label = 'Closed';
    ELSEIF v_status = 'cancelled' THEN
        SET label = 'Cancelled';
    ELSEIF v_time_left <= 0 THEN
        SET label = 'Expired';
    ELSEIF v_time_left <= 3600 THEN
        SET label = 'Ending Soon';
    ELSE
        SET label = 'Active';
    END IF;
    
    RETURN label;
END //

DELIMITER ;
