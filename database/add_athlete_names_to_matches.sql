-- Migration to support individual athlete names for Open Categories (Badminton & Table Tennis)
ALTER TABLE matches 
ADD COLUMN athlete1_name VARCHAR(150) NULL AFTER team2_id,
ADD COLUMN athlete2_name VARCHAR(150) NULL AFTER athlete1_name;
