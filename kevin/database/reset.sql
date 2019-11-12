DROP TABLE IF EXISTS effective_rate;
DROP TABLE IF EXISTS max_income;

CREATE TABLE effective_rate (
    rate_id INT AUTO_INCREMENT PRIMARY KEY,
    month_num CHAR(2) NOT NULL,
    year_num INT NOT NULL,
    island INT NOT NULL,
    kwh INT NOT NULL,
    cost_kwh FLOAT NOT NULL,
    type INT NOT NULL
);

CREATE TABLE max_income (
    income_id INT AUTO_INCREMENT PRIMARY KEY,
    island INT NOT NULL,
    year_num INT NOT NULL,
    num_person INT NOT NULL,
    max_income INT NOT NULL
);
