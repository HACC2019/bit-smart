DROP PROCEDURE IF EXISTS add_effective_rate;
DROP PROCEDURE IF EXISTS add_max_income;

DELIMITER $$
CREATE PROCEDURE add_effective_rate(month_num CHAR(2), year_num INT, island INT,kwh INT,cost_kwh FLOAT,type INT)
BEGIN
    INSERT INTO `effective_rate` (`rate_id`, `month_num`, `year_num`, `island`, `kwh`, `cost_kwh`, `type`)
    VALUES(NULL, month_num, year_num, island,kwh,cost_kwh,type);
END$$

CREATE PROCEDURE add_max_income(island INT, year_num INT,num_person INT,max_income INT)
BEGIN
    INSERT INTO `max_income` (`income_id`, `island`, `year_num`, `num_person`, `max_income`)
    VALUES(NULL, island, year_num, num_person,max_income);
END$$
DELIMITER ;