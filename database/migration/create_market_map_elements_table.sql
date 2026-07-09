-- Tạo bảng market_map_elements quản lý sơ đồ chợ tương tác
USE `quanly_cho`;

CREATE TABLE IF NOT EXISTS `market_map_elements` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `element_type` VARCHAR(50) NOT NULL, -- stall, gate, door, street, utility, text
    `element_name` VARCHAR(100) NULL,
    `stall_id` INT NULL, -- FK liên kết bảng stalls nếu là sạp chợ
    `pos_x` INT NOT NULL DEFAULT 100,
    `pos_y` INT NOT NULL DEFAULT 100,
    `width` INT NOT NULL DEFAULT 80,
    `height` INT NOT NULL DEFAULT 60,
    `rotation` INT NOT NULL DEFAULT 0, -- Góc xoay (độ: 0, 90, 180, 270)
    `color` VARCHAR(20) NULL, -- Mã màu tùy chỉnh cho đường đi/khối tiện ích
    FOREIGN KEY (`stall_id`) REFERENCES `stalls`(`id`) ON DELETE SET NULL,
    UNIQUE KEY `uk_stall` (`stall_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
