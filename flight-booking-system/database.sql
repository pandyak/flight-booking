CREATE DATABASE IF NOT EXISTS flight_booking;
USE flight_booking;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Airports table
CREATE TABLE IF NOT EXISTS airports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    city VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL DEFAULT 'India'
);

-- Flights table
CREATE TABLE IF NOT EXISTS flights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flight_number VARCHAR(20) NOT NULL,
    airline VARCHAR(100) NOT NULL,
    from_airport_id INT NOT NULL,
    to_airport_id INT NOT NULL,
    departure_time DATETIME NOT NULL,
    arrival_time DATETIME NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total_seats INT NOT NULL DEFAULT 180,
    available_seats INT NOT NULL DEFAULT 180,
    status ENUM('scheduled','cancelled','completed') DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (from_airport_id) REFERENCES airports(id),
    FOREIGN KEY (to_airport_id) REFERENCES airports(id)
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    flight_id INT NOT NULL,
    passengers INT NOT NULL DEFAULT 1,
    total_price DECIMAL(10,2) NOT NULL,
    passenger_name VARCHAR(100) NOT NULL,
    passenger_email VARCHAR(150) NOT NULL,
    passenger_phone VARCHAR(20) DEFAULT NULL,
    status ENUM('confirmed','cancelled','pending') DEFAULT 'confirmed',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (flight_id) REFERENCES flights(id)
);

-- =====================
-- ADMIN USER (password = 123)
-- =====================
INSERT INTO users (name, email, password, phone, role) VALUES
('Admin', 'admin@flights.com', '$2y$10$7QJ5r6R7sKkqK9wW6d8zUeV9GQz7GQ5w2j0KQx9Xy7Hk8j3Y6fYQG', '9999999999', 'admin');

-- Airports
INSERT INTO airports (code, name, city, country) VALUES
('DEL','Indira Gandhi International Airport','New Delhi','India'),
('BOM','Chhatrapati Shivaji Maharaj International Airport','Mumbai','India'),
('BLR','Kempegowda International Airport','Bangalore','India'),
('MAA','Chennai International Airport','Chennai','India'),
('CCU','Netaji Subhas Chandra Bose International Airport','Kolkata','India'),
('HYD','Rajiv Gandhi International Airport','Hyderabad','India'),
('GOI','Goa International Airport','Goa','India'),
('JAI','Jaipur International Airport','Jaipur','India'),
('COK','Cochin International Airport','Kochi','India'),
('AMD','Sardar Vallabhbhai Patel International Airport','Ahmedabad','India');

-- Flights
INSERT INTO flights (flight_number, airline, from_airport_id, to_airport_id, departure_time, arrival_time, price, total_seats, available_seats) VALUES
('AI-101','Air India',1,2,'2026-03-01 06:00:00','2026-03-01 08:15:00',4500,180,150),
('6E-202','IndiGo',2,3,'2026-03-01 09:30:00','2026-03-01 11:00:00',3200,180,170),
('SG-303','SpiceJet',1,3,'2026-03-01 14:00:00','2026-03-01 16:45:00',3800,180,160),
('UK-404','Vistara',3,4,'2026-03-02 07:00:00','2026-03-02 08:10:00',2900,180,175),
('AI-505','Air India',1,5,'2026-03-02 10:00:00','2026-03-02 12:30:00',5200,180,140),
('6E-606','IndiGo',2,6,'2026-03-02 15:00:00','2026-03-02 16:30:00',3100,180,165),
('SG-707','SpiceJet',1,7,'2026-03-03 08:00:00','2026-03-03 10:30:00',4800,180,155),
('UK-808','Vistara',4,1,'2026-03-03 12:00:00','2026-03-03 14:45:00',4200,180,145),
('AI-909','Air India',5,2,'2026-03-03 16:00:00','2026-03-03 18:30:00',5500,180,130),
('6E-110','IndiGo',6,1,'2026-03-04 06:30:00','2026-03-04 08:45:00',3600,180,172),
('SG-211','SpiceJet',7,2,'2026-03-04 11:00:00','2026-03-04 13:30:00',4100,180,158),
('UK-312','Vistara',3,5,'2026-03-04 14:30:00','2026-03-04 17:00:00',4700,180,168);
    