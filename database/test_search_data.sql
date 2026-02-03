-- Test podaci za Full-Text Search demonstraciju

-- Dodaj test korisnike za pretragu (password: "test123")
INSERT INTO users (username, email, password, verification_code, is_verified) VALUES
('admin', 'admin@hotelmanagement.hr', '$2y$10$dummyhash', '000000', 1),
('zagreb_user', 'info@zagreb-hotels.hr', '$2y$10$dummyhash', '000000', 1),
('split_user', 'contact@split-hotels.hr', '$2y$10$dummyhash', '000000', 1),
('test_user', 'test@gmail.com', '$2y$10$dummyhash', '000000', 1),
('booking_admin', 'bookings@hotel.com', '$2y$10$dummyhash', '000000', 1)
ON DUPLICATE KEY UPDATE username=username;

-- Dodaj više hotela u različitim gradovima
INSERT INTO hotels (naziv, adresa, grad, zupanija, kapacitet, broj_soba, broj_gostiju, slobodno_soba) VALUES
('Hotel Esplanade Zagreb', 'Mihanovićeva 1', 'Zagreb', 'Grad Zagreb', 200, 100, 150, 50),
('Hotel Dubrovnik Zagreb', 'Ljudevita Gaja 1', 'Zagreb', 'Grad Zagreb', 300, 150, 200, 100),
('Sheraton Zagreb Hotel', 'Kneza Borne 2', 'Zagreb', 'Grad Zagreb', 400, 200, 300, 120),
('Hotel Jägerhorn Zagreb', 'Ilica 14', 'Zagreb', 'Grad Zagreb', 50, 25, 30, 15),
('Hotel Split Luxury', 'Obala Hrvatskog narodnog preporoda 22', 'Split', 'Splitsko-dalmatinska', 250, 125, 180, 80),
('Hotel Park Split', 'Hatzeov perivoj 3', 'Split', 'Splitsko-dalmatinska', 180, 90, 120, 60),
('Hotel Ambasador Rijeka', 'Andrije Kačića Miošića 30', 'Rijeka', 'Primorsko-goranska', 220, 110, 160, 70),
('Hotel Continental Rijeka', 'Šetalište Andrije Kačića Miošića 1', 'Rijeka', 'Primorsko-goranska', 300, 150, 220, 100),
('Hotel Osijek', 'Šamačka 4', 'Osijek', 'Osječko-baranjska', 150, 75, 100, 50),
('Hotel Central Osijek', 'Trg Ante Starčevića 6', 'Osijek', 'Osječko-baranjska', 120, 60, 80, 40);

-- Provjera
SELECT COUNT(*) as total_hotels FROM hotels;
SELECT COUNT(*) as total_users FROM users;
