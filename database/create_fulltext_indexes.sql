-- Full-Text Search Indexes
-- Za pretragu nad više stupaca u različitim tablicama

-- Full-Text index za tablicu hotels (pretraga po nazivu, adresi i gradu)
ALTER TABLE hotels ADD FULLTEXT INDEX ft_hotels_search (naziv, adresa, grad);

-- Full-Text index za tablicu users (pretraga po korisničkom imenu i emailu)
ALTER TABLE users ADD FULLTEXT INDEX ft_users_search (username, email);

-- Provjera kreiranih indeksa
SHOW INDEX FROM hotels WHERE Key_name = 'ft_hotels_search';
SHOW INDEX FROM users WHERE Key_name = 'ft_users_search';
