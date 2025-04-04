CREATE DATABASE FleetOpz;
USE FleetOpz;

CREATE TABLE Vehicles (
    vehicle_id INT AUTO_INCREMENT PRIMARY KEY,
    model VARCHAR(100) NOT NULL,
    year YEAR NOT NULL,
    licence_no VARCHAR(50) UNIQUE NOT NULL,
    status ENUM('Active', 'Inactive', 'Under Maintenance') NOT NULL
);

CREATE TABLE Drivers (
    driver_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact VARCHAR(20) UNIQUE NOT NULL,
    licence VARCHAR(50) UNIQUE NOT NULL,
    status ENUM('Available', 'Hired', 'Inactive') NOT NULL,
    vehicle_id INT,
    hire_date DATE,
    salary DECIMAL(10,2),
    FOREIGN KEY (vehicle_id) REFERENCES Vehicles(vehicle_id)
);

CREATE TABLE Maintainances (
    maintainance_id INT AUTO_INCREMENT PRIMARY KEY,
    vehicle_id INT NOT NULL,
    service_type VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    status ENUM('Pending', 'Completed') NOT NULL,
    cost DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (vehicle_id) REFERENCES Vehicles(vehicle_id)
);

CREATE TABLE FuelTransactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    vehicle_id INT NOT NULL,
    driver_id INT NOT NULL,
    fuel_type ENUM('Petrol', 'Diesel', 'Electric', 'CNG') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    cost DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (vehicle_id) REFERENCES Vehicles(vehicle_id),
    FOREIGN KEY (driver_id) REFERENCES Drivers(driver_id)
);

