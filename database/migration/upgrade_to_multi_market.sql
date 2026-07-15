-- SQL Migration: Nâng cấp Hệ thống sang kiến trúc Đa Chợ (Multi-market)
-- Backup Database trước khi thực hiện chạy script này!

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Tạo bảng markets
CREATE TABLE IF NOT EXISTS `markets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `market_code` VARCHAR(50) NOT NULL UNIQUE,   -- Quy tắc đặt mã: CHO_BT, CHO_AD, CHO_TD...
    `name` VARCHAR(150) NOT NULL,                 -- Tên chợ (VD: Chợ Bến Thành)
    `phone` VARCHAR(20) NULL,                     -- Số điện thoại BQL chợ
    `email` VARCHAR(100) NULL,                    -- Email liên hệ BQL chợ
    `manager_name` VARCHAR(100) NULL,             -- Tên trưởng ban quản lý chợ
    `logo` VARCHAR(255) NULL,                     -- Đường dẫn ảnh logo chợ
    `province_id` INT NULL,                       -- Tỉnh / Thành phố
    `district_id` INT NULL,                       -- Quận / Huyện
    `ward_id` INT NULL,                           -- Phường / Xã
    `latitude` DECIMAL(10, 8) NULL,               -- Vĩ độ bản đồ
    `longitude` DECIMAL(11, 8) NULL,              -- Kinh độ bản đồ
    `status_code` VARCHAR(50) DEFAULT 'active',   -- Trạng thái hoạt động (active, inactive)
    `created_by` INT NULL,                        -- ID user tạo chợ
    `updated_by` INT NULL,                        -- ID user cập nhật chợ
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_market_status` (`status_code`),
    INDEX `idx_market_location` (`province_id`, `district_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Chèn bản ghi chợ mặc định đầu tiên
INSERT INTO `markets` (`id`, `market_code`, `name`, `status_code`) 
VALUES (1, 'CHO_TT', 'Chợ Trung Tâm', 'active')
ON DUPLICATE KEY UPDATE `name` = 'Chợ Trung Tâm';

-- 3. Nâng cấp bảng areas (Khu vực chợ)
ALTER TABLE `areas` ADD COLUMN `market_id` INT NULL;
UPDATE `areas` SET `market_id` = 1 WHERE `market_id` IS NULL;
ALTER TABLE `areas` MODIFY COLUMN `market_id` INT NOT NULL;
ALTER TABLE `areas` ADD CONSTRAINT `fk_areas_market` FOREIGN KEY (`market_id`) REFERENCES `markets`(`id`);
ALTER TABLE `areas` ADD INDEX `idx_areas_market` (`market_id`);

-- 4. Nâng cấp bảng food_safety_inspections (Thanh tra ATTP)
ALTER TABLE `food_safety_inspections` ADD COLUMN `market_id` INT NULL;
UPDATE `food_safety_inspections` SET `market_id` = 1 WHERE `market_id` IS NULL;
ALTER TABLE `food_safety_inspections` MODIFY COLUMN `market_id` INT NOT NULL;
ALTER TABLE `food_safety_inspections` ADD CONSTRAINT `fk_inspections_market` FOREIGN KEY (`market_id`) REFERENCES `markets`(`id`);
ALTER TABLE `food_safety_inspections` ADD INDEX `idx_inspections_market` (`market_id`);

-- 5. Nâng cấp bảng receipts_payments (Sổ quỹ thu chi)
ALTER TABLE `receipts_payments` ADD COLUMN `market_id` INT NULL;
UPDATE `receipts_payments` SET `market_id` = 1 WHERE `market_id` IS NULL;
-- Cột này cho phép NULL vì nếu thu chi đi kèm bill_id, market_id có thể tự suy luận gián tiếp.
ALTER TABLE `receipts_payments` ADD CONSTRAINT `fk_receipts_market` FOREIGN KEY (`market_id`) REFERENCES `markets`(`id`);
ALTER TABLE `receipts_payments` ADD INDEX `idx_receipts_market` (`market_id`);

-- 6. Tạo bảng user_markets
CREATE TABLE IF NOT EXISTS `user_markets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `market_id` INT NOT NULL,
    `role_id` INT NOT NULL,  -- Vai trò cụ thể tại chợ này (Kế toán, Nhân viên nghiệp vụ...)
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`market_id`) REFERENCES `markets`(`id`),
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`),
    UNIQUE KEY `uk_user_market_role` (`user_id`, `market_id`, `role_id`),
    INDEX `idx_user_markets_query` (`user_id`, `market_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Ánh xạ các tài khoản người dùng hiện có vào Chợ 1
INSERT INTO `user_markets` (`user_id`, `market_id`, `role_id`)
SELECT ur.user_id, 1, ur.role_id 
FROM `user_roles` ur
ON DUPLICATE KEY UPDATE `market_id` = 1;

-- 8. Tạo quyền bỏ qua scope chợ (Super Admin)
INSERT INTO `permissions` (`permission_code`, `permission_name`, `module_group`, `description`)
VALUES ('ignore_market_scope', 'Bỏ qua phạm vi giới hạn chợ (Xem liên chợ)', 'user', 'Quyền cho phép Quản trị viên tối cao truy cập dữ liệu của tất cả các chợ')
ON DUPLICATE KEY UPDATE `permission_name` = VALUES(`permission_name`);

-- Gán quyền này cho Vai trò Admin (role_code = 'admin')
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r, `permissions` p
WHERE r.role_code = 'admin' AND p.permission_code = 'ignore_market_scope'
ON DUPLICATE KEY UPDATE `role_id` = VALUES(`role_id`);

-- 9. Thêm các INDEX phụ trợ tối ưu truy vấn liên kết gián tiếp
ALTER TABLE `stalls` ADD INDEX `idx_stalls_area` (`area_id`);
ALTER TABLE `contracts` ADD INDEX `idx_contracts_stall` (`stall_id`);

SET FOREIGN_KEY_CHECKS = 1;
