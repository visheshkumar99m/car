-- SQL script to add brand_logo column to brands table
ALTER TABLE brands ADD COLUMN brand_logo VARCHAR(255) NULL AFTER description; 