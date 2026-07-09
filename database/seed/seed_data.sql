-- Dữ liệu mẫu khởi tạo dự án Quản lý Chợ (Phiên bản đầy đủ 18 bảng)
USE `quanly_cho`;

SET FOREIGN_KEY_CHECKS = 0;

-- Xóa dữ liệu cũ để tránh trùng lặp khi chạy lại
DELETE FROM `status_colors`;
DELETE FROM `system_statuses`;
DELETE FROM `roles`;
DELETE FROM `permissions`;
DELETE FROM `role_permissions`;
DELETE FROM `users`;
DELETE FROM `user_roles`;
DELETE FROM `areas`;
DELETE FROM `stalls`;
DELETE FROM `business_lines`;
DELETE FROM `traders`;
DELETE FROM `contracts`;
DELETE FROM `contract_appendices`;
DELETE FROM `utility_readings`;
DELETE FROM `bills`;
DELETE FROM `receipts_payments`;
DELETE FROM `trader_attp`;
DELETE FROM `food_safety_inspections`;
DELETE FROM `food_safety_violations`;
DELETE FROM `system_logs`;

-- 1. Nạp từ điển màu sắc (status_colors)
INSERT INTO `status_colors` (`id`, `color_class`, `description`) VALUES
(1, 'status-green', 'Xanh lá (Thành công, Hoạt động, Đã thanh toán)'),
(2, 'status-red', 'Đỏ (Khóa, Hủy, Chưa thanh toán, Lỗi)'),
(3, 'status-gray', 'Xám (Trống, Hết hạn, Đã xóa)'),
(4, 'status-orange', 'Cam (Đang sửa, Tạm dừng)'),
(5, 'status-blue', 'Xanh dương (Thanh lý, Thông tin phụ)'),
(6, 'status-yellow', 'Vàng (Kế hoạch, Chờ xử lý)'),
(7, 'status-white', 'Trắng (Trống/Chưa sử dụng)');

-- 1.5. Nạp từ điển trạng thái (system_statuses)
INSERT INTO `system_statuses` (`id`, `domain`, `code`, `status_name`, `color_id`) VALUES
(1, 'user', 'active', 'Hoạt động', 1),
(2, 'user', 'locked', 'Bị khóa', 2),
(3, 'stall', 'empty', 'Trống', 7),
(4, 'stall', 'rented', 'Đã thuê', 1),
(5, 'stall', 'repairing', 'Đang sửa chữa', 6),
(6, 'stall', 'locked', 'Tạm khóa', 2),
(7, 'trader', 'active', 'Đang kinh doanh', 1),
(8, 'trader', 'suspended', 'Tạm dừng', 6),
(9, 'trader', 'closed', 'Ngừng kinh doanh', 2),
(10, 'trader', '99', 'Đã xóa', 3),
(11, 'contract', 'active', 'Hoạt động', 1),
(12, 'contract', 'expired', 'Hết hạn', 3),
(13, 'contract', 'liquidated', 'Thanh lý', 5),
(14, 'contract', 'terminated', 'Chấm dứt trước hạn', 2),
(15, 'bill', 'unpaid', 'Chưa thanh toán', 2),
(16, 'bill', 'partially_paid', 'Trả một phần', 6),
(17, 'bill', 'paid', 'Đã thanh toán', 1),
(18, 'attp', 'valid', 'Còn hạn', 1),
(19, 'attp', 'expired', 'Hết hạn', 2),
(20, 'inspection', 'planned', 'Chưa thực hiện', 6),
(21, 'inspection', 'completed', 'Đã thực hiện', 1),
(22, 'inspection', 'cancelled', 'Đã hủy', 2),
(23, 'violation', 'pending', 'Đang xử lý', 2),
(24, 'violation', 'resolved', 'Đã chấp hành xong', 1),
(25, 'contract', '99', 'Đã xóa', 3),
(26, 'attp', '99', 'Đã xóa', 3);

-- 2. Nạp Danh mục vai trò (roles)
INSERT INTO `roles` (`role_code`, `role_name`, `description`) VALUES
('admin', 'Quản trị hệ thống', 'Toàn quyền điều hành và cấu hình hệ thống'),
('staff', 'Nhân viên ban quản lý', 'Ghi số điện nước, quản lý sạp, tiểu thương và lập hợp đồng'),
('accountant', 'Kế toán chợ', 'Quản lý hóa đơn, phiếu thu, phiếu chi và báo cáo tài chính');

-- 3. Nạp danh mục quyền chi tiết (permissions)
INSERT INTO `permissions` (`permission_code`, `permission_name`, `module_group`, `description`) VALUES
('trader_view', 'Xem danh sách tiểu thương', 'trader', 'Xem thông tin chi tiết các tiểu thương'),
('trader_create', 'Thêm mới tiểu thương', 'trader', 'Khai báo hồ sơ tiểu thương mới'),
('trader_edit', 'Chỉnh sửa tiểu thương', 'trader', 'Cập nhật hồ sơ tiểu thương'),
('trader_delete', 'Xóa tiểu thương', 'trader', 'Xóa hồ sơ tiểu thương khỏi hệ thống'),
('stall_view', 'Xem sơ đồ & sạp chợ', 'stall', 'Xem danh sách và sơ đồ phân bố sạp'),
('stall_edit', 'Cập nhật sạp chợ', 'stall', 'Khai báo, điều chỉnh thông tin sạp'),
('contract_view', 'Xem hợp đồng', 'contract', 'Xem danh sách hợp đồng thuê sạp'),
('contract_create', 'Lập hợp đồng mới', 'contract', 'Tạo hợp đồng thuê sạp mới cho tiểu thương'),
('contract_liquidate', 'Thanh lý hợp đồng', 'contract', 'Thanh lý hoặc chấm dứt hợp đồng'),
('finance_reading', 'Ghi điện nước', 'finance', 'Nhập chỉ số điện nước định kỳ cho các sạp'),
('finance_bill', 'Quản lý hóa đơn', 'finance', 'Lập hóa đơn thu tiền định kỳ'),
('finance_transaction', 'Quản lý thu chi', 'finance', 'Lập phiếu thu, phiếu chi tài chính'),
('foodsafety_view', 'Xem hồ sơ ATTP', 'foodsafety', 'Theo dõi hạn giấy tờ vệ sinh ATTP'),
('foodsafety_inspections', 'Kế hoạch kiểm tra ATTP', 'foodsafety', 'Lên kế hoạch và lưu kết quả thanh tra vệ sinh'),
('user_manage', 'Quản lý tài khoản & Phân quyền', 'user', 'Tạo mới, phân quyền cho nhân viên');

-- 4. Liên kết Vai trò - Quyền (role_permissions)
-- Admin có tất cả các quyền
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 1, id FROM `permissions`;

-- Staff (Nhân viên) có quyền quản lý sạp, tiểu thương, hợp đồng, ghi số điện nước, xem ATTP
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 2, id FROM `permissions` WHERE `permission_code` IN 
('trader_view', 'trader_create', 'trader_edit', 'stall_view', 'stall_edit', 'contract_view', 'contract_create', 'finance_reading', 'foodsafety_view');

-- Accountant (Kế toán) có quyền liên quan hóa đơn, thu chi, báo cáo, xem thông tin cơ bản
INSERT INTO `role_permissions` (`role_id`, `permission_id`)
SELECT 3, id FROM `permissions` WHERE `permission_code` IN 
('trader_view', 'stall_view', 'contract_view', 'finance_bill', 'finance_transaction', 'foodsafety_view');

-- 5. Tài khoản người dùng mặc định (users)
-- Mật khẩu mặc định: admin123 cho admin, staff123 cho staff, accountant123 cho accountant
INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `email`, `is_active`, `user_group`) VALUES
(1, 'admin', '$2y$10$O1TJx90uJMLCMEwz3whX9eFfFItlMJdaCpOkn7QfPyJeRwayswDoW', 'Ban Quản Lý Chợ', 'bql.cho@gmail.com', 1, 1),
(2, 'nhanvien1', '$2y$10$PbdTDxALL6kNIh8bluNJ9OU5aab4XXSV74aAygOys/BPyaMPClczu', 'Nguyễn Văn Thu', 'nvthu.cho@gmail.com', 1, 2),
(3, 'ketoan1', '$2y$10$aufmDhq1tjcdncYQaFRAHun37TXxACHxFynBjw6BHKdtjS8lQ/K.y', 'Trần Thị Kế Toán', 'ketoan.cho@gmail.com', 1, 2);

-- 6. Liên kết Tài khoản - Vai trò (user_roles)
INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(1, 1), -- admin -> Admin
(2, 2), -- nhanvien1 -> Staff
(3, 3); -- ketoan1 -> Accountant

-- 7. Danh mục khu vực chợ (areas)
INSERT INTO `areas` (`id`, `area_name`, `block`, `lot`, `description`) VALUES
(1, 'Khu A (Quần áo)', 'Dãy A', 'Lô 1', 'Khu vực chuyên doanh quần áo, giày dép và phụ kiện thời trang'),
(2, 'Khu B (Thực phẩm)', 'Dãy B', 'Lô 2', 'Khu vực chuyên doanh thực phẩm tươi sống, rau củ quả và đồ khô'),
(3, 'Khu C (Ẩm thực)', 'Dãy C', 'Lô 3', 'Khu vực chuyên kinh doanh dịch vụ ăn uống, nước giải khát');

-- 8. Danh mục sạp chợ mẫu (stalls)
INSERT INTO `stalls` (`id`, `area_id`, `stall_code`, `stall_type`, `area_size`, `base_price`, `status_id`, `map_coordinate_x`, `map_coordinate_y`) VALUES
(1, 1, 'SẠP-A01', 'Kiot cao cấp', 15.00, 3500000.00, 4, 120, 150),
(2, 1, 'SẠP-A02', 'Kiot cao cấp', 15.00, 3500000.00, 4, 120, 200),
(3, 1, 'SẠP-A03', 'Quầy hàng tiêu chuẩn', 10.00, 2000000.00, 3, 120, 250),
(4, 1, 'SẠP-A04', 'Quầy hàng tiêu chuẩn', 10.00, 2000000.00, 3, 120, 300),
(5, 1, 'SẠP-A05', 'Quầy hàng tiêu chuẩn', 10.00, 2000000.00, 4, 120, 350),
(6, 2, 'SẠP-B01', 'Quầy tươi sống', 8.00, 1500000.00, 4, 240, 150),
(7, 2, 'SẠP-B02', 'Quầy tươi sống', 8.00, 1500000.00, 3, 240, 200),
(8, 2, 'SẠP-B12', 'Mặt bằng tự do', 12.00, 1800000.00, 4, 240, 250),
(9, 3, 'SẠP-C01', 'Quầy ăn uống', 12.00, 2500000.00, 3, 360, 150),
(10, 3, 'SẠP-C02', 'Quầy ăn uống', 12.00, 2500000.00, 5, 360, 200);

-- 8.5. Danh mục ngành hàng mẫu (business_lines)
INSERT INTO `business_lines` (`id`, `line_code`, `line_name`, `description`) VALUES
(1, 'THOI_TRANG', 'Thời trang & May mặc', 'Quần áo, giày dép, phụ kiện thời trang'),
(2, 'THUC_PHAM', 'Thực phẩm tươi sống', 'Thịt, cá, rau củ quả, thực phẩm hàng ngày'),
(3, 'AM_THUC', 'Ẩm thực & Đồ uống', 'Đồ ăn chín, nước giải khát, quán ăn'),
(4, 'GIA_DUNG', 'Đồ gia dụng & Tạp hóa', 'Bát đĩa, đồ dùng nhà bếp, bột giặt, tạp hóa'),
(5, 'MY_PHAM', 'Mỹ phẩm & Hóa mỹ phẩm', 'Son phấn, dầu gội, sữa tắm, chăm sóc sắc đẹp');

-- 9. API Tiểu thương mẫu (traders)
INSERT INTO `traders` (`id`, `trader_code`, `fullname`, `phone`, `cccd`, `address`, `business_line_id`, `license_file`, `status_id`) VALUES
(1, 'TT-0001', 'Nguyễn Thị Thu Hà', '0912345678', '001195001234', '12 Phố Huế, Hai Bà Trưng, Hà Nội', 1, NULL, 7),
(2, 'TT-0002', 'Trần Văn Hoàng', '0987654321', '002196005678', '45 Đại Cồ Việt, Bách Khoa, Hà Nội', 2, NULL, 7),
(3, 'TT-0003', 'Phạm Minh Tuấn', '0905112233', '003197009012', '78 Lò Đúc, Đống Đa, Hà Nội', 1, NULL, 7),
(4, 'TT-0004', 'Lê Thị Mai', '0934556677', '004198003456', '99 Bạch Mai, Hai Bà Trưng, Hà Nội', 2, NULL, 7);

-- 10. Hợp đồng mẫu (contracts)
INSERT INTO `contracts` (`id`, `trader_id`, `stall_id`, `contract_number`, `name`, `description`, `contract_file`, `start_date`, `end_date`, `deposit`, `status_id`) VALUES
(1, 1, 1, 'HĐ-SA01-2026', 'Hợp đồng thuê sạp SẠP-A01', 'Hợp đồng thuê sạp thời trang Kiot cao cấp', NULL, '2026-01-01', '2026-12-31', 7000000.00, 11),
(2, 2, 6, 'HĐ-SB01-2026', 'Hợp đồng thuê sạp SẠP-B01', 'Hợp đồng thuê sạp thực phẩm tươi sống', NULL, '2026-01-15', '2026-07-15', 3000000.00, 11),
(3, 3, 5, 'HĐ-SA05-2026', 'Hợp đồng thuê sạp SẠP-A05', 'Hợp đồng thuê sạp quần áo tiêu chuẩn', NULL, '2026-02-01', '2026-08-01', 4000000.00, 11);

-- 11. Phụ lục hợp đồng mẫu (contract_appendices)
INSERT INTO `contract_appendices` (`contract_id`, `appendix_number`, `name`, `sign_date`, `effect_date`, `content`, `file`) VALUES
(1, 'PL-SA01-2026-01', 'Phụ lục điều chỉnh đơn giá', '2026-06-01', '2026-06-15', 'Điều chỉnh đơn giá thuê từ 3,500,000đ thành 3,800,000đ kể từ ngày 15/06/2026 do nâng cấp Kiot.', NULL);

-- 12. Ghi nhận số điện nước mẫu (utility_readings)
INSERT INTO `utility_readings` (`stall_id`, `reading_date`, `electric_old`, `electric_new`, `water_old`, `water_new`, `created_by`) VALUES
(1, '2026-06-25', 1540, 1690, 240, 255, 2),
(6, '2026-06-25', 3200, 3450, 410, 432, 2);

-- 13. Hóa đơn dịch vụ mẫu (bills)
INSERT INTO `bills` (`id`, `contract_id`, `bill_code`, `invoice_date`, `due_date`, `rent_amount`, `electric_amount`, `water_amount`, `management_fee`, `sanitation_fee`, `security_fee`, `other_fee`, `total_amount`, `paid_amount`, `status`) VALUES
(1, 1, 'HD-202606-001', '2026-06-25', '2026-07-10', 3500000.00, 450000.00, 120000.00, 100000.00, 50000.00, 50000.00, 0.00, 4270000.00, 0.00, 'unpaid'),
(2, 2, 'HD-202606-002', '2026-06-25', '2026-07-10', 1500000.00, 750000.00, 176000.00, 100000.00, 50000.00, 50000.00, 0.00, 2626000.00, 2626000.00, 'paid');

-- 14. Phiếu Thu - Phiếu Chi mẫu (receipts_payments)
INSERT INTO `receipts_payments` (`transaction_code`, `type`, `amount`, `transaction_date`, `category`, `note`, `reference_id`, `created_by`) VALUES
('PT-0001', 'receipt', 2626000.00, '2026-06-28', 'Thu tiền hóa đơn', 'Thu tiền hóa đơn HD-202606-002 sạp SẠP-B01 tháng 06/2026', 2, 3),
('PC-0001', 'payment', 12500000.00, '2026-06-29', 'Điện nước chung', 'Thanh toán tiền điện tổng của chợ tháng 06/2026 cho EVN', NULL, 3);

-- 15. Hồ sơ vệ sinh ATTP tiểu thương (trader_attp)
INSERT INTO `trader_attp` (`trader_id`, `doc_type`, `doc_number`, `name`, `description`, `file`, `status_id`, `issuer`, `issue_date`, `expiry_date`) VALUES
(2, 'ATTP', '123/2025/ATTP-HN', 'Giấy chứng nhận cơ sở đủ điều kiện ATTP', 'Giấy chứng nhận ATTP cho sạp kinh doanh giò chả', NULL, 18, 'Chi cục ATTP Hà Nội', '2025-05-10', '2028-05-10'),
(2, 'Health', 'GKSK-9988', 'Giấy khám sức khỏe định kỳ năm 2026', 'Giấy khám sức khỏe tiểu thương Trần Văn Hoàng', NULL, 18, 'Bệnh viện Quận 1', '2026-01-10', '2027-01-10'),
(4, 'ATTP', '456/2024/ATTP-HN', 'Giấy chứng nhận cơ sở đủ điều kiện ATTP', 'Giấy chứng nhận kinh doanh rau củ quả sạch', NULL, 18, 'Chi cục ATTP Hà Nội', '2024-03-12', '2027-03-12'),
(4, 'Training', 'TH-ATTP-2026', 'Giấy xác nhận tập huấn kiến thức ATTP', 'Xác nhận tập huấn kiến thức an toàn thực phẩm sạp Mai Lê', NULL, 18, 'Trung tâm Y tế Dự phòng Quận', '2026-02-15', '2029-02-15');

-- 16. Kế hoạch kiểm tra vệ sinh ATTP (food_safety_inspections)
INSERT INTO `food_safety_inspections` (`id`, `inspection_title`, `inspection_team`, `planned_date`, `actual_date`, `status`, `notes`) VALUES
(1, 'Kiểm tra định kỳ quý 2/2026', 'Ban quản lý chợ + Phòng Y tế Quận', '2026-07-15', NULL, 'planned', 'Kiểm tra giấy khám sức khỏe và trang bị bảo hộ của hộ kinh doanh tươi sống và thực phẩm chín.');

-- 17. Nhật ký vi phạm vệ sinh ATTP mẫu (food_safety_violations)
INSERT INTO `food_safety_violations` (`violation_code`, `trader_id`, `inspection_id`, `violation_date`, `description`, `penalty_measure`, `status`, `resolved_date`) VALUES
('BBVP-0089', 2, NULL, '2026-06-20', 'Không đeo găng tay khi chế biến, bày thực phẩm chín không che đậy gây mất vệ sinh', 'Phạt cảnh cáo, đình chỉ sạp 3 ngày', 'resolved', '2026-06-23');

-- 18. Nhật ký hoạt động hệ thống mẫu (system_logs)
INSERT INTO `system_logs` (`user_id`, `action_type`, `action_description`, `ip_address`, `user_agent`) VALUES
(1, 'login', 'Đăng nhập hệ thống thành công', '192.168.1.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/126.0.0.0'),
(1, 'create', 'Thêm mới phụ lục hợp đồng số PL-SA01-2026-01 cho HĐ-SA01-2026', '192.168.1.15', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/126.0.0.0');

SET FOREIGN_KEY_CHECKS = 1;
