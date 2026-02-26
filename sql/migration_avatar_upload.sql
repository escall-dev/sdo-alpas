-- Migration: Avatar Upload System
-- Adds avatar_updated_at column to admin_users table
-- The avatar_url column already exists as varchar(500), nullable
-- This migration only adds the timestamp tracking column

ALTER TABLE admin_users 
ADD COLUMN avatar_updated_at DATETIME NULL DEFAULT NULL AFTER avatar_url;
