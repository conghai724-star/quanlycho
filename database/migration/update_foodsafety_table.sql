USE `quanly_cho`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Thêm trạng thái Đã xóa (99) cho ATTP trong system_statuses nếu chưa tồn tại
INSERT INTO `system_statuses` (`domain`, `code`, `status_name`, `color_id`)
SELECT 'attp', '99', 'Đã xóa', 3
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `system_statuses` WHERE `domain` = 'attp' AND `code` = '99'
);

-- 2. Cập nhật bảng trader_attp
-- Thêm các cột name, description, file, status_id vào trader_attp
ALTER TABLE `trader_attp`
ADD COLUMN `name` VARCHAR(255) NULL AFTER `doc_number`,
ADD COLUMN `description` TEXT NULL AFTER `name`,
ADD COLUMN `file` VARCHAR(255) NULL AFTER `description`,
ADD COLUMN `status_id` INT NULL AFTER `file`;

-- Thiết lập dữ liệu mặc định cho cột name dựa trên doc_type và doc_number
UPDATE `trader_attp` 
SET `name` = CASE 
    WHEN `doc_type` = 'ATTP' THEN CONCAT('Giấy chứng nhận ATTP số ', `doc_number`)
    WHEN `doc_type` = 'Health' THEN CONCAT('Giấy khám sức khỏe số ', `doc_number`)
    WHEN `doc_type` = 'Training' THEN CONCAT('Giấy tập huấn ATTP số ', `doc_number`)
    ELSE CONCAT('Giấy tờ ATTP số ', `doc_number`)
END
WHERE `name` IS NULL;

-- Cập nhật kiểu NOT NULL cho name sau khi đã điền dữ liệu
ALTER TABLE `trader_attp` MODIFY COLUMN `name` VARCHAR(255) NOT NULL;

-- Ánh xạ dữ liệu status cũ sang status_id mới
UPDATE `trader_attp` SET `status_id` = (SELECT `id` FROM `system_statuses` WHERE `domain` = 'attp' AND `code` = 'valid') WHERE `status` = 'valid';
UPDATE `trader_attp` SET `status_id` = (SELECT `id` FROM `system_statuses` WHERE `domain` = 'attp' AND `code` = 'expired') WHERE `status` = 'expired';

-- Thiết lập mặc định status_id = valid (18) nếu có bản ghi nào bị NULL
UPDATE `trader_attp` SET `status_id` = (SELECT `id` FROM `system_statuses` WHERE `domain` = 'attp' AND `code` = 'valid') WHERE `status_id` IS NULL;

ALTER TABLE `trader_attp` MODIFY COLUMN `status_id` INT NOT NULL DEFAULT 18;

-- Xóa cột status cũ
ALTER TABLE `trader_attp` DROP COLUMN `status`;

-- Thêm khóa ngoại cho status_id
ALTER TABLE `trader_attp` ADD CONSTRAINT `fk_trader_attp_status` FOREIGN KEY (`status_id`) REFERENCES `system_statuses`(`id`);

SET FOREIGN_KEY_CHECKS = 1;
