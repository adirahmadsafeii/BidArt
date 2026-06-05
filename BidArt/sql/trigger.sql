-- IMPLEMENTASI MYSQL TRIGGER
-- Trigger untuk otomatis menutup lelang yang sudah berakhir
DELIMITER //

CREATE TRIGGER tr_check_auction_end
AFTER INSERT ON bids
FOR EACH ROW
BEGIN
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
END //

DELIMITER ;

-- Trigger untuk log aktivitas bid
DELIMITER //

CREATE TRIGGER tr_bid_activity_log
AFTER INSERT ON bids
FOR EACH ROW
BEGIN
    INSERT INTO transactions (user_id, auction_id, type, amount, description, status)
    VALUES (NEW.bidder_id, NEW.auction_id, 'deposit', NEW.bid_amount, 
            CONCAT('Bid placed for auction ID: ', NEW.auction_id), 'completed');
END //

DELIMITER ;

-- Trigger untuk update balance setelah bid
DELIMITER //

CREATE TRIGGER tr_update_balance_after_bid
AFTER INSERT ON bids
FOR EACH ROW
BEGIN
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
END //

DELIMITER ;
