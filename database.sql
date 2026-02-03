-- Hotel Management System Database Schema
-- Created: 2026-01-21

CREATE DATABASE IF NOT EXISTS hotel_managment CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE hotel_managment;

-- Hoteli table
CREATE TABLE IF NOT EXISTS hoteli (
    id INT AUTO_INCREMENT PRIMARY KEY,
    naziv VARCHAR(255) NOT NULL,
    adresa VARCHAR(255) NOT NULL,
    grad VARCHAR(100) NOT NULL,
    zupanija VARCHAR(100) NOT NULL,
    kapacitet INT NOT NULL DEFAULT 0,
    broj_soba INT NOT NULL DEFAULT 0,
    broj_gostiju INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_broj_gostiju CHECK (broj_gostiju >= 0),
    CONSTRAINT chk_kapacitet CHECK (kapacitet >= broj_gostiju)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample data for testing
INSERT INTO hoteli (naziv, adresa, grad, zupanija, kapacitet, broj_soba, broj_gostiju) VALUES
('Hotel Adriatic', 'Obala hrvatskih branitelja 28', 'Split', 'Splitsko-dalmatinska', 120, 60, 85),
('Grand Hotel Zagreb', 'Trg bana Jelačića 10', 'Zagreb', 'Grad Zagreb', 200, 100, 150),
('Hotel Park', 'Šetalište Ivana Pavla II 19', 'Rovinj', 'Istarska', 80, 40, 45),
('Hotel Esplanade', 'Mihanovićeva 1', 'Zagreb', 'Grad Zagreb', 150, 75, 120),
('Hotel Dubrovnik Palace', 'Masarykov put 20', 'Dubrovnik', 'Dubrovačko-neretvanska', 180, 90, 95),
('Hotel Imperial', 'Maršala Tita 172', 'Opatija', 'Primorsko-goranska', 100, 50, 60),
('Hotel Bellevue', 'Pera Čingrije 7', 'Mali Lošinj', 'Primorsko-goranska', 90, 45, 30),
('Hotel Kvarner', 'Obala Vladimira Nazora 2', 'Rijeka', 'Primorsko-goranska', 110, 55, 70);
