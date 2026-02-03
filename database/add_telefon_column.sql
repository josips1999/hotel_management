-- Add telefon column to hotels table
USE hotel_management;

ALTER TABLE hotels ADD COLUMN IF NOT EXISTS telefon VARCHAR(20) AFTER broj_soba;

-- Verify the change
DESCRIBE hotels;
