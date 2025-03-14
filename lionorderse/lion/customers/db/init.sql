CREATE DATABASE IF NOT EXISTS customersDB;
USE customersDB;

CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fname VARCHAR(100) NOT NULL,
    lname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    user VARCHAR(100) UNIQUE NOT NULL,
    pw VARCHAR(255) NOT NULL
);

INSERT INTO customers (id, fname, lname, email, user, pw)
VALUES (1, 'admin', 'admin', 'admin@gmail.com', 'admin', 'admin123')
ON DUPLICATE KEY UPDATE 
    fname = VALUES(fname),
    lname = VALUES(lname),
    email = VALUES(email),
    user = VALUES(user),
    pw = VALUES(pw);

