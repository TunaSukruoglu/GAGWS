-- Add missing columns to properties table
-- Execute this SQL to add the missing form fields to the database

ALTER TABLE properties 
ADD COLUMN IF NOT EXISTS usage_status VARCHAR(50) NULL,
ADD COLUMN IF NOT EXISTS dues DECIMAL(10,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS credit_eligible TINYINT(1) DEFAULT 0,
ADD COLUMN IF NOT EXISTS deed_status VARCHAR(100) NULL,
ADD COLUMN IF NOT EXISTS exchange VARCHAR(20) NULL,
ADD COLUMN IF NOT EXISTS location_type VARCHAR(50) NULL;

-- Update any existing records to have default values
UPDATE properties 
SET 
    usage_status = 'Boş' WHERE usage_status IS NULL,
    dues = 0 WHERE dues IS NULL,
    credit_eligible = 0 WHERE credit_eligible IS NULL,
    deed_status = 'Kat Mülkiyetli' WHERE deed_status IS NULL,
    exchange = 'Hayır' WHERE exchange IS NULL,
    location_type = 'standalone' WHERE location_type IS NULL;
