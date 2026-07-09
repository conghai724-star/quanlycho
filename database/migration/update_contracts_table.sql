USE `quanly_cho`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Thêm trạng thái Đã xóa (99) cho Hợp đồng trong system_statuses nếu chưa tồn tại
INSERT INTO `system_statuses` (`domain`, `code`, `status_name`, `color_id`)
SELECT 'contract', '99', 'Đã xóa', 3
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `system_statuses` WHERE `domain` = 'contract' AND `code` = '99'
);

-- 2. Cập nhật bảng contracts
-- Thêm các cột name, description, contract_file, status_id vào contracts
ALTER TABLE `contracts`
ADD COLUMN `name` VARCHAR(255) NULL AFTER `contract_number`,
ADD COLUMN `description` TEXT NULL AFTER `name`,
ADD COLUMN `contract_file` VARCHAR(255) NULL AFTER `description`,
ADD COLUMN `status_id` INT NULL AFTER `contract_file`;

-- Thiết lập dữ liệu mặc định cho cột name từ contract_number
UPDATE `contracts` SET `name` = CONCAT('Hợp đồng thuê sạp ', contract_number) WHERE `name` IS NULL;

-- Cập nhật kiểu NOT NULL cho name sau khi đã điền dữ liệu
ALTER TABLE `contracts` MODIFY COLUMN `name` VARCHAR(255) NOT NULL;

-- Ánh xạ dữ liệu status cũ sang status_id mới
UPDATE `contracts` SET `status_id` = (SELECT `id` FROM `system_statuses` WHERE `domain` = 'contract' AND `code` = 'active') WHERE `status` = 'active';
UPDATE `contracts` SET `status_id` = (SELECT `id` FROM `system_statuses` WHERE `domain` = 'contract' AND `code` = 'expired') WHERE `status` = 'expired';
UPDATE `contracts` SET `status_id` = (SELECT `id` FROM `system_statuses` WHERE `domain` = 'contract' AND `code` = 'liquidated') WHERE `status` = 'liquidated';
UPDATE `contracts` SET `status_id` = (SELECT `id` FROM `system_statuses` WHERE `domain` = 'contract' AND `code` = 'terminated') WHERE `status` = 'terminated';

-- Thiết lập mặc định status_id = active (11) nếu có bản ghi nào bị NULL
UPDATE `contracts` SET `status_id` = (SELECT `id` FROM `system_statuses` WHERE `domain` = 'contract' AND `code` = 'active') WHERE `status_id` IS NULL;

ALTER TABLE `contracts` MODIFY COLUMN `status_id` INT NOT NULL DEFAULT 11;

-- Xóa cột status cũ
ALTER TABLE `contracts` DROP COLUMN `status`;

-- Thêm khóa ngoại cho status_id
ALTER TABLE `contracts` ADD CONSTRAINT `fk_contracts_status` FOREIGN KEY (`status_id`) REFERENCES `system_statuses`(`id`);

-- 3. Cập nhật bảng contract_appendices
-- Thêm các cột name, file
ALTER TABLE `contract_appendices`
ADD COLUMN `name` VARCHAR(255) NULL AFTER `appendix_number`,
ADD COLUMN `file` VARCHAR(255) NULL AFTER `content`;

-- Điền dữ liệu mặc định cho name
UPDATE `contract_appendices` SET `name` = CONCAT('Phụ lục ', appendix_number) WHERE `name` IS NULL;

ALTER TABLE `contract_appendices` MODIFY COLUMN `name` VARCHAR(255) NOT NULL;

SET FOREIGN_KEY_CHECKS = 1;
