CREATE DATABASE bankly_v2;

use bankly_v2;

-- customers table

CREATE TABLE customers (
    customer_id INT(11) NOT NULL AUTO_INCREMENT,
    full_name VARCHAR(255) DEFAULT NULL,
    email VARCHAR(65) DEFAULT NULL,
    cin VARCHAR(20) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    PRIMARY KEY (customer_id)
);

-- accounts table 

CREATE TABLE accounts (
    account_id INT(11) NOT NULL AUTO_INCREMENT,
    account_num INT(11) DEFAULT NULL,
    account_type ENUM('saving','business','checking') DEFAULT NULL,
    balance DECIMAL(12,2) DEFAULT NULL,
    customer_id INT(11) DEFAULT NULL,
    PRIMARY KEY (account_id)
);

-- transictions table

CREATE TABLE transictions (
    transictions_id INT(11) NOT NULL AUTO_INCREMENT,
    amount DECIMAL(12,2) DEFAULT NULL,
    transictions_type ENUM('depot','retrait') DEFAULT NULL,
    account_id INT(11) DEFAULT NULL,
    transictions_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (transictions_id),
    KEY account_id_idx (account_id)
);

-- user table

CREATE TABLE user (
    user_id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(255) DEFAULT NULL,
    password VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (user_id)
);

-- customers
INSERT INTO customers (full_name, email, cin, phone) VALUES
('Ahmed El Amrani', 'ahmed@gmail.com', 'AB123456', '0612345678'),
('Sara Benali', 'sara@gmail.com', 'CD789012', '0623456789');

-- accounts
INSERT INTO accounts (account_num, account_type, balance, customer_id) VALUES
(100001, 'saving', 5000.00, 1),
(100002, 'checking', 2500.50, 2);


-- transictions 
INSERT INTO transictions (amount, transictions_type, account_id, transictions_date) VALUES
(1000.00, 'depot', 1, '2025-12-15 11:06:25'),
(500.00, 'retrait', 2, '2025-12-15 11:06:25');

-- users

INSERT INTO user (username, password) VALUES
('admin', 'admin123'),
('agent1', 'agent123');
