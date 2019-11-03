DROP PROCEDURE IF EXISTS add_entry;

DELIMITER $$
CREATE PROCEDURE add_entry(month_num CHAR(2), year_num INT, island INT,kwh INT,cost_kwh FLOAT,type INT)
BEGIN
    INSERT INTO `effective_rate` (`rate_id`, `month_num`, `year_num`, `island`, `kwh`, `cost_kwh`, `type`)
    VALUES(NULL, month_num, year_num, island,kwh,cost_kwh,type);
END$$
DELIMITER ;