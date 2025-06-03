-- Add 'mode' column to attendances (for office/remote tracking)
ALTER TABLE `attendances` ADD COLUMN `mode` VARCHAR(32) NULL AFTER `status`;

-- Optionally add 'notes' column for comments/justification
ALTER TABLE `attendances` ADD COLUMN `notes` TEXT NULL AFTER `mode`;
 
-- Optionally add 'verified_by' column for manager verification
ALTER TABLE `attendances` ADD COLUMN `verified_by` INT NULL AFTER `notes`; 