-- SQL Migration: Nâng cấp hệ thống phân quyền 3 cấp (super_market, admin_market, admin)
USE `quanly_cho`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Tạo bảng system_actors (Từ điển tác nhân)
DROP TABLE IF EXISTS `system_actors`;
CREATE TABLE `system_actors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `actor_code` VARCHAR(50) NOT NULL UNIQUE,
    `actor_name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Chèn 3 vai trò tác nhân hệ thống
INSERT INTO `system_actors` (`actor_code`, `actor_name`, `description`) VALUES
('super_market', 'Quản trị tối cao (Super Admin)', 'Toàn quyền truy cập hệ thống, quản lý danh sách các chợ và phân key cho admin_market.'),
('admin_market', 'Quản lý chợ (Market Manager)', 'Quản trị viên của một hoặc nhiều chợ được phân quyền, quản lý nhân viên và phân quyền phân hệ cho nhân viên.'),
('admin', 'Nhân viên vận hành (Staff)', 'Nhân viên trực tiếp vận hành các nghiệp vụ được phân công trong chợ.');

-- 3. Cập nhật bảng users để liên kết vai trò tác nhân
ALTER TABLE `users` ADD COLUMN `actor_id` INT NULL AFTER `user_group`;

-- Ánh xạ các tài khoản hiện tại:
-- admin (id=1) -> super_market
UPDATE `users` SET `actor_id` = (SELECT `id` FROM `system_actors` WHERE `actor_code` = 'super_market') WHERE `id` = 1;
-- quanly_cho1 (id=2) -> admin_market
UPDATE `users` SET `actor_id` = (SELECT `id` FROM `system_actors` WHERE `actor_code` = 'admin_market') WHERE `id` = 2;
-- nhanvien_diennuoc (id=3) và nhanvien_attp (id=4) -> admin
UPDATE `users` SET `actor_id` = (SELECT `id` FROM `system_actors` WHERE `actor_code` = 'admin') WHERE `id` IN (3, 4);

-- Thiết lập giá trị mặc định cho các tài khoản khác nếu có
UPDATE `users` SET `actor_id` = (SELECT `id` FROM `system_actors` WHERE `actor_code` = 'super_market') WHERE `user_group` = 1 AND `actor_id` IS NULL;
UPDATE `users` SET `actor_id` = (SELECT `id` FROM `system_actors` WHERE `actor_code` = 'admin') WHERE `actor_id` IS NULL;

-- Thiết lập ràng buộc NOT NULL và khóa ngoại
ALTER TABLE `users` MODIFY COLUMN `actor_id` INT NOT NULL;
ALTER TABLE `users` ADD CONSTRAINT `fk_users_actor` FOREIGN KEY (`actor_id`) REFERENCES `system_actors`(`id`);

-- 4. Tạo bảng user_market_permissions (Phân quyền phân hệ của admin)
DROP TABLE IF EXISTS `user_market_permissions`;
CREATE TABLE `user_market_permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `market_id` INT NOT NULL,
    `module_code` VARCHAR(50) NOT NULL, -- trader, stall, contract, finance, foodsafety
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`market_id`) REFERENCES `markets`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uk_user_market_module` (`user_id`, `market_id`, `module_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Cấp quyền mặc định cho nhân viên hiện tại để tránh mất quyền đột ngột
-- Nhận viên điện nước (id=3) tại Chợ Trung Tâm (id=1): được quyền quản lý finance, stall
INSERT INTO `user_market_permissions` (`user_id`, `market_id`, `module_code`) VALUES
(3, 1, 'finance'),
(3, 1, 'stall');

-- Nhân viên an toàn vệ sinh thực phẩm (id=4) tại Chợ Trung Tâm (id=1): được quyền quản lý foodsafety, trader
INSERT INTO `user_market_permissions` (`user_id`, `market_id`, `module_code`) VALUES
(4, 1, 'foodsafety'),
(4, 1, 'trader');

SET FOREIGN_KEY_CHECKS = 1;
