-- Khởi tạo Cơ sở dữ liệu Quản lý Chợ (Cập nhật phiên bản phân quyền RBAC & Quản lý Trạng thái tập trung)
CREATE DATABASE IF NOT EXISTS `quanly_cho` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `quanly_cho`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Bảng system_statuses (Từ điển trạng thái hệ thống)
DROP TABLE IF EXISTS `system_statuses`;
CREATE TABLE `system_statuses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `entity_type` VARCHAR(50) NOT NULL, -- user, stall, trader, contract, bill, attp, inspection, violation
    `status_code` VARCHAR(50) NOT NULL, -- active, empty, rented, unpaid, paid, valid, expired...
    `status_name` VARCHAR(100) NOT NULL, -- Hoạt động, Trống, Đã thuê, Chưa thanh toán...
    `color_class` VARCHAR(50) NULL,      -- Class CSS hiển thị màu sắc badge (ví dụ: status-green, status-yellow...)
    UNIQUE KEY `uk_entity_status` (`entity_type`, `status_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Bảng roles (Danh mục vai trò)
DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_code` VARCHAR(50) NOT NULL UNIQUE, -- admin, staff, accountant, manager
    `role_name` VARCHAR(100) NOT NULL, -- Quản trị hệ thống, Nhân viên, Kế toán...
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Bảng permissions (Danh mục quyền hành động)
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `permission_code` VARCHAR(100) NOT NULL UNIQUE, -- trader_view, trader_create, stall_edit...
    `permission_name` VARCHAR(150) NOT NULL, -- Xem tiểu thương, Thêm tiểu thương...
    `module_group` VARCHAR(50) NOT NULL, -- trader, stall, contract, finance, foodsafety, user
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Bảng role_permissions (Liên kết Vai trò - Quyền)
DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE `role_permissions` (
    `role_id` INT NOT NULL,
    `permission_id` INT NOT NULL,
    PRIMARY KEY (`role_id`, `permission_id`),
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Bảng users (Tài khoản nhân viên ban quản lý)
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `fullname` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Bảng user_roles (Liên kết Tài khoản - Vai trò)
DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE `user_roles` (
    `user_id` INT NOT NULL,
    `role_id` INT NOT NULL,
    PRIMARY KEY (`user_id`, `role_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Bảng areas (Khu vực chợ)
DROP TABLE IF EXISTS `areas`;
CREATE TABLE `areas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `area_name` VARCHAR(100) NOT NULL UNIQUE, -- Khu A, Khu B, Khu Thực Phẩm...
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Bảng stalls (Sạp chợ)
DROP TABLE IF EXISTS `stalls`;
CREATE TABLE `stalls` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `area_id` INT NOT NULL,
    `stall_code` VARCHAR(50) NOT NULL UNIQUE, -- SẠP-A01, SẠP-B10...
    `stall_type` VARCHAR(100) NOT NULL DEFAULT 'Quầy hàng', -- Kiot, Quầy hàng, Mặt bằng trống...
    `area_size` DECIMAL(10, 2) NOT NULL, -- Diện tích m2
    `base_price` DECIMAL(15, 2) NOT NULL, -- Đơn giá thuê/tháng
    `status` VARCHAR(20) NOT NULL DEFAULT 'empty', -- empty, rented, repairing, locked
    `map_coordinate_x` INT NULL, -- Tọa độ X vẽ sơ đồ
    `map_coordinate_y` INT NULL, -- Tọa độ Y vẽ sơ đồ
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`area_id`) REFERENCES `areas`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Bảng traders (Tiểu thương)
DROP TABLE IF EXISTS `traders`;
CREATE TABLE `traders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `trader_code` VARCHAR(50) NOT NULL UNIQUE,
    `fullname` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(15) NOT NULL,
    `cccd` VARCHAR(20) NOT NULL UNIQUE,
    `address` TEXT NULL,
    `business_line` VARCHAR(100) NULL, -- Ngành hàng kinh doanh (Thực phẩm, Quần áo...)
    `status` VARCHAR(20) NOT NULL DEFAULT 'active', -- active, suspended, closed
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Bảng contracts (Hợp đồng thuê sạp)
DROP TABLE IF EXISTS `contracts`;
CREATE TABLE `contracts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `trader_id` INT NOT NULL,
    `stall_id` INT NOT NULL,
    `contract_number` VARCHAR(100) NOT NULL UNIQUE,
    `start_date` DATE NOT NULL,
    `end_date` DATE NOT NULL,
    `deposit` DECIMAL(15, 2) NOT NULL DEFAULT 0, -- Tiền cọc thuê sạp
    `status` VARCHAR(20) NOT NULL DEFAULT 'active', -- active, expired, liquidated, terminated
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`trader_id`) REFERENCES `traders`(`id`) ON DELETE RESTRICT,
    FOREIGN KEY (`stall_id`) REFERENCES `stalls`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Bảng contract_appendices (Phụ lục hợp đồng)
DROP TABLE IF EXISTS `contract_appendices`;
CREATE TABLE `contract_appendices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `contract_id` INT NOT NULL,
    `appendix_number` VARCHAR(100) NOT NULL UNIQUE,
    `sign_date` DATE NOT NULL,
    `effect_date` DATE NOT NULL,
    `content` TEXT NOT NULL, -- Nội dung thay đổi/phụ lục
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`contract_id`) REFERENCES `contracts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. Bảng utility_readings (Ghi nhận số điện nước định kỳ)
DROP TABLE IF EXISTS `utility_readings`;
CREATE TABLE `utility_readings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `stall_id` INT NOT NULL,
    `reading_date` DATE NOT NULL,
    `electric_old` INT NOT NULL,
    `electric_new` INT NOT NULL,
    `water_old` INT NOT NULL,
    `water_new` INT NOT NULL,
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`stall_id`) REFERENCES `stalls`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Bảng bills (Hóa đơn dịch vụ hàng tháng)
DROP TABLE IF EXISTS `bills`;
CREATE TABLE `bills` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `contract_id` INT NOT NULL,
    `bill_code` VARCHAR(50) NOT NULL UNIQUE, -- HD-202607-001...
    `invoice_date` DATE NOT NULL,
    `due_date` DATE NOT NULL,
    `rent_amount` DECIMAL(15, 2) NOT NULL DEFAULT 0, -- Tiền thuê sạp
    `electric_amount` DECIMAL(15, 2) NOT NULL DEFAULT 0, -- Tiền điện
    `water_amount` DECIMAL(15, 2) NOT NULL DEFAULT 0, -- Tiền nước
    `management_fee` DECIMAL(15, 2) NOT NULL DEFAULT 0, -- Phí quản lý
    `sanitation_fee` DECIMAL(15, 2) NOT NULL DEFAULT 0, -- Phí vệ sinh
    `security_fee` DECIMAL(15, 2) NOT NULL DEFAULT 0, -- Phí bảo vệ
    `other_fee` DECIMAL(15, 2) NOT NULL DEFAULT 0, -- Phí khác
    `total_amount` DECIMAL(15, 2) NOT NULL DEFAULT 0, -- Tổng cộng hóa đơn
    `paid_amount` DECIMAL(15, 2) NOT NULL DEFAULT 0, -- Số tiền đã trả thực tế
    `status` VARCHAR(20) NOT NULL DEFAULT 'unpaid', -- unpaid, partially_paid, paid
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`contract_id`) REFERENCES `contracts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Bảng receipts_payments (Phiếu Thu - Phiếu Chi tài chính)
DROP TABLE IF EXISTS `receipts_payments`;
CREATE TABLE `receipts_payments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `transaction_code` VARCHAR(50) NOT NULL UNIQUE, -- PT-0001, PC-0002...
    `type` VARCHAR(10) NOT NULL, -- receipt, payment
    `amount` DECIMAL(15, 2) NOT NULL,
    `transaction_date` DATE NOT NULL,
    `category` VARCHAR(100) NOT NULL, -- Tiền thuê sạp, Tiền điện, Tiền nước, Lương nhân viên, Sửa chữa...
    `note` TEXT NULL,
    `reference_id` INT NULL, -- ID của Hóa đơn nếu là Phiếu thu tiền điện nước/sạp
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 15. Bảng trader_attp (Quản lý vệ sinh ATTP và các giấy tờ của tiểu thương)
DROP TABLE IF EXISTS `trader_attp`;
CREATE TABLE `trader_attp` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `trader_id` INT NOT NULL,
    `doc_type` VARCHAR(50) NOT NULL, -- ATTP, Health, Training
    `doc_number` VARCHAR(100) NOT NULL, -- Số giấy chứng nhận
    `issuer` VARCHAR(150) NULL, -- Cơ quan cấp
    `issue_date` DATE NOT NULL, -- Ngày cấp
    `expiry_date` DATE NOT NULL, -- Ngày hết hạn
    `status` VARCHAR(20) NOT NULL DEFAULT 'valid', -- valid, expired
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`trader_id`) REFERENCES `traders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 16. Bảng food_safety_inspections (Kế hoạch kiểm tra vệ sinh ATTP)
DROP TABLE IF EXISTS `food_safety_inspections`;
CREATE TABLE `food_safety_inspections` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `inspection_title` VARCHAR(255) NOT NULL, -- Tên đợt thanh tra
    `inspection_team` VARCHAR(255) NOT NULL, -- Đoàn kiểm tra
    `planned_date` DATE NOT NULL, -- Ngày dự kiến
    `actual_date` DATE NULL, -- Ngày thực tế kiểm tra
    `status` VARCHAR(20) NOT NULL DEFAULT 'planned', -- planned, completed, cancelled
    `notes` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 17. Bảng food_safety_violations (Biên bản & Xử lý vi phạm ATTP)
DROP TABLE IF EXISTS `food_safety_violations`;
CREATE TABLE `food_safety_violations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `trader_id` INT NOT NULL,
    `inspection_id` INT NULL,
    `violation_code` VARCHAR(50) NOT NULL UNIQUE, -- BBVP-0089...
    `violation_date` DATE NOT NULL,
    `description` TEXT NOT NULL,
    `penalty_measure` TEXT NOT NULL, -- Hình thức xử lý
    `status` VARCHAR(20) NOT NULL DEFAULT 'pending', -- pending, resolved
    `resolved_date` DATE NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`trader_id`) REFERENCES `traders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`inspection_id`) REFERENCES `food_safety_inspections`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 18. Bảng system_logs (Nhật ký hoạt động hệ thống)
DROP TABLE IF EXISTS `system_logs`;
CREATE TABLE `system_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action_type` VARCHAR(50) NOT NULL, -- login, logout, create, update, delete, view, export
    `action_description` TEXT NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
