-- SQL Migration: Thêm 2 chợ mới và cập nhật phân quyền 3 tác nhân
USE `quanly_cho`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Thêm 2 chợ mới
INSERT INTO `markets` (`id`, `market_code`, `name`, `status_code`) VALUES
(2, 'CHO_BT', 'Chợ Bình Tây', 'active'),
(3, 'CHO_AD', 'Chợ An Đông', 'active')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `status_code` = VALUES(`status_code`);

-- 2. Thêm Khu vực (areas) cho các chợ mới
INSERT INTO `areas` (`id`, `market_id`, `area_name`, `block`, `lot`, `description`) VALUES
(2, 2, 'Khu A - Chợ Bình Tây', 'Dãy A', 'Lô 1-50', 'Khu vực bán đồ khô và gia vị'),
(3, 2, 'Khu B - Chợ Bình Tây', 'Dãy B', 'Lô 51-100', 'Khu vực bán đồ lưu niệm và thời trang'),
(4, 3, 'Khu C - Chợ An Đông', 'Tầng Trệt', 'Lô C1-C80', 'Khu vực giầy dép và túi xách')
ON DUPLICATE KEY UPDATE `market_id` = VALUES(`market_id`), `area_name` = VALUES(`area_name`);

-- 3. Thêm Sạp mẫu (stalls) cho các chợ mới
-- system_statuses: 3 = empty, 4 = rented
-- stall_type_id: 2
INSERT INTO `stalls` (`id`, `area_id`, `stall_code`, `stall_type_id`, `area_size`, `base_price`, `status_id`) VALUES
(6, 2, 'SẠP-BT01', 2, 15.00, 4500000.00, 3),
(7, 2, 'SẠP-BT02', 2, 15.00, 4500000.00, 4),
(8, 3, 'SẠP-BT55', 2, 10.00, 3000000.00, 3),
(9, 4, 'SẠP-AD01', 2, 12.00, 4000000.00, 3),
(10, 4, 'SẠP-AD02', 2, 12.00, 4000000.00, 4)
ON DUPLICATE KEY UPDATE `area_id` = VALUES(`area_id`), `status_id` = VALUES(`status_id`);

-- 4. Reset mật khẩu và đảm bảo vai trò chuẩn cho 3 tài khoản tác nhân chính (mật khẩu: 123456)
-- Super Admin (id=1, username=admin)
UPDATE `users` SET 
    `password` = '$2y$10$INIFxkRp6LY6iI.PmA8SzOQ/pS8jzf79290WE4EE8YyNAx8U5aYM6', 
    `fullname` = 'Quản trị tối cao (Super Market)',
    `actor_id` = (SELECT `id` FROM `system_actors` WHERE `actor_code` = 'super_market')
WHERE `id` = 1;

-- Market Manager (id=2, username=nhanvien1)
UPDATE `users` SET 
    `password` = '$2y$10$INIFxkRp6LY6iI.PmA8SzOQ/pS8jzf79290WE4EE8YyNAx8U5aYM6', 
    `fullname` = 'Quản lý chợ Bình Tây (Admin Market)',
    `actor_id` = (SELECT `id` FROM `system_actors` WHERE `actor_code` = 'admin_market')
WHERE `id` = 2;

-- Staff (id=3, username=ketoan1)
UPDATE `users` SET 
    `password` = '$2y$10$INIFxkRp6LY6iI.PmA8SzOQ/pS8jzf79290WE4EE8YyNAx8U5aYM6', 
    `fullname` = 'Nhân viên vận hành (Admin/Staff)',
    `actor_id` = (SELECT `id` FROM `system_actors` WHERE `actor_code` = 'admin')
WHERE `id` = 3;

-- 5. Liên kết các chợ mới cho Quản lý chợ (id=2) và Nhân viên (id=3)
INSERT INTO `user_markets` (`user_id`, `market_id`, `role_id`) VALUES
(2, 2, 4), -- Quản lý chợ (role_id 4) tại Chợ Bình Tây (market_id 2)
(2, 3, 4), -- Quản lý chợ (role_id 4) tại Chợ An Đông (market_id 3)
(3, 2, 2), -- Nhân viên (role_id 2) tại Chợ Bình Tây (market_id 2)
(3, 3, 2)  -- Nhân viên (role_id 2) tại Chợ An Đông (market_id 3)
ON DUPLICATE KEY UPDATE `role_id` = VALUES(`role_id`);

-- 6. Phân quyền phân hệ mặc định cho Nhân viên (id=3) tại các chợ mới
INSERT INTO `user_market_permissions` (`user_id`, `market_id`, `module_code`) VALUES
(3, 2, 'finance'),
(3, 2, 'stall'),
(3, 3, 'finance'),
(3, 3, 'trader')
ON DUPLICATE KEY UPDATE `module_code` = VALUES(`module_code`);

SET FOREIGN_KEY_CHECKS = 1;
