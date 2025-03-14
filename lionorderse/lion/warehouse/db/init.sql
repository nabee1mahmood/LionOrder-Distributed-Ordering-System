CREATE DATABASE IF NOT EXISTS warehouseDB;
USE warehouseDB;

CREATE TABLE IF NOT EXISTS warehouse (
    upc VARCHAR(50) PRIMARY KEY,       
    qty INT DEFAULT 0,                 
    availability BOOLEAN DEFAULT TRUE
);

