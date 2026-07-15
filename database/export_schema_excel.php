<?php
/**
 * Xuất Từ điển Dữ liệu (Data Dictionary) ra file Excel XML Spreadsheet
 * Mở bằng Microsoft Excel / LibreOffice / Google Sheets
 * 
 * Cách chạy: truy cập URL http://localhost/quanly_cho/database/export_schema_excel.php
 * hoặc chạy lệnh: d:\xampp\php\php.exe database/export_schema_excel.php
 */

// ========================= ĐỊNH NGHĨA BẢNG =========================
// Mỗi bảng gồm: số thứ tự, tên bảng VIẾT HOA, mô tả, danh sách cột
// Mỗi cột: [Tên Cột, Kiểu Dữ Liệu, Ràng Buộc / Mặc Định, Mô Tả / Ghi Chú]

$tables = [];

// ── 1. STATUS_COLORS ──
$tables[] = [
    'number' => 1,
    'name'   => 'STATUS_COLORS',
    'desc'   => 'Từ điển màu sắc / lớp CSS hiển thị cho các trạng thái trong hệ thống',
    'cols'   => [
        ['id',          'INT',          'PK, Auto Increment',    'Khóa chính, tự tăng'],
        ['color_class', 'VARCHAR(50)',  'NOT NULL, UNIQUE',      'Tên class CSS hiển thị (status-green, status-red, status-orange…)'],
        ['description', 'VARCHAR(100)', 'NULL',                  'Mô tả ý nghĩa nhóm màu'],
    ]
];

// ── 2. SYSTEM_STATUSES ──
$tables[] = [
    'number' => 2,
    'name'   => 'SYSTEM_STATUSES',
    'desc'   => 'Từ điển trạng thái tập trung của hệ thống. Các bảng nghiệp vụ liên kết thông qua status_id (FK)',
    'cols'   => [
        ['id',          'INT',          'PK, Auto Increment',                'Khóa chính, tự tăng'],
        ['domain',      'VARCHAR(50)',  'NOT NULL',                          'Phân hệ sở hữu trạng thái (user / stall / trader / contract / bill / attp / inspection / violation)'],
        ['code',        'VARCHAR(50)',  'NOT NULL',                          'Mã trạng thái kỹ thuật (active, empty, rented, unpaid, paid, valid, expired…)'],
        ['status_name', 'VARCHAR(100)', 'NOT NULL',                          'Tên hiển thị tiếng Việt (Hoạt động, Trống, Đã thuê…)'],
        ['color_id',    'INT',          'FK → status_colors(id), NULL',      'Liên kết lớp màu CSS tương ứng'],
    ]
];

// ── 3. ROLES ──
$tables[] = [
    'number' => 3,
    'name'   => 'ROLES',
    'desc'   => 'Danh mục vai trò phân quyền hệ thống (RBAC)',
    'cols'   => [
        ['id',          'INT',          'PK, Auto Increment',    'Khóa chính, tự tăng'],
        ['role_code',   'VARCHAR(50)',  'NOT NULL, UNIQUE',      'Mã vai trò kỹ thuật (admin, staff, accountant, manager)'],
        ['role_name',   'VARCHAR(100)', 'NOT NULL',              'Tên hiển thị tiếng Việt (Quản trị hệ thống, Nhân viên, Kế toán…)'],
        ['description', 'TEXT',         'NULL',                  'Mô tả chi tiết vai trò'],
        ['created_at',  'TIMESTAMP',    'DEFAULT NOW()',         'Thời điểm tạo bản ghi'],
    ]
];

// ── 4. PERMISSIONS ──
$tables[] = [
    'number' => 4,
    'name'   => 'PERMISSIONS',
    'desc'   => 'Danh mục quyền hành động chi tiết (RBAC)',
    'cols'   => [
        ['id',              'INT',          'PK, Auto Increment',    'Khóa chính, tự tăng'],
        ['permission_code', 'VARCHAR(100)', 'NOT NULL, UNIQUE',      'Mã quyền kỹ thuật (trader_view, trader_create, stall_edit…)'],
        ['permission_name', 'VARCHAR(150)', 'NOT NULL',              'Tên hiển thị tiếng Việt (Xem tiểu thương, Thêm tiểu thương…)'],
        ['module_group',    'VARCHAR(50)',  'NOT NULL',              'Nhóm phân hệ (trader, stall, contract, finance, foodsafety, user)'],
        ['description',     'TEXT',         'NULL',                  'Mô tả chi tiết quyền'],
        ['created_at',      'TIMESTAMP',    'DEFAULT NOW()',         'Thời điểm tạo bản ghi'],
    ]
];

// ── 5. ROLE_PERMISSIONS ──
$tables[] = [
    'number' => 5,
    'name'   => 'ROLE_PERMISSIONS',
    'desc'   => 'Bảng liên kết trung gian (N-N) gán Quyền cho Vai trò',
    'cols'   => [
        ['role_id',       'INT', 'PK, FK → roles(id), ON DELETE CASCADE',       'ID vai trò'],
        ['permission_id', 'INT', 'PK, FK → permissions(id), ON DELETE CASCADE', 'ID quyền'],
    ]
];

// ── 6. USERS ──
$tables[] = [
    'number' => 6,
    'name'   => 'USERS',
    'desc'   => 'Tài khoản nhân viên Ban Quản Lý Chợ (Admin, Kế toán, Nhân viên nghiệp vụ)',
    'cols'   => [
        ['id',         'INT',          'PK, Auto Increment',    'Khóa chính, tự tăng'],
        ['username',   'VARCHAR(50)',  'NOT NULL, UNIQUE',      'Tên đăng nhập, duy nhất'],
        ['password',   'VARCHAR(255)', 'NOT NULL',              'Mật khẩu đã mã hóa'],
        ['fullname',   'VARCHAR(100)', 'NOT NULL',              'Họ và tên nhân viên'],
        ['email',      'VARCHAR(100)', 'NULL',                  'Email liên hệ (tùy chọn)'],
        ['is_active',  'TINYINT(1)',   'NOT NULL, DEFAULT 1',   'Trạng thái hoạt động (1=Hoạt động, 0=Bị khóa)'],
        ['user_group', 'INT',          'NOT NULL, DEFAULT 2',   'Nhóm người dùng (1: Admin, 2: Staff…)'],
        ['created_at', 'TIMESTAMP',    'DEFAULT NOW()',         'Thời điểm tạo tài khoản'],
        ['updated_at', 'TIMESTAMP',    'AUTO UPDATE',           'Thời điểm cập nhật gần nhất'],
    ]
];

// ── 7. USER_ROLES ──
$tables[] = [
    'number' => 7,
    'name'   => 'USER_ROLES',
    'desc'   => 'Bảng liên kết trung gian (N-N) gán Vai trò cho Tài khoản nhân viên',
    'cols'   => [
        ['user_id', 'INT', 'PK, FK → users(id), ON DELETE CASCADE', 'ID tài khoản nhân viên'],
        ['role_id', 'INT', 'PK, FK → roles(id), ON DELETE CASCADE', 'ID vai trò'],
    ]
];

// ── 8. MARKETS ──
$tables[] = [
    'number' => 8,
    'name'   => 'MARKETS',
    'desc'   => 'Danh sách Chợ trong kiến trúc Đa Chợ (Multi-market). Mỗi bản ghi là một chợ độc lập',
    'cols'   => [
        ['id',           'INT',            'PK, Auto Increment',      'Khóa chính, tự tăng'],
        ['market_code',  'VARCHAR(50)',    'NOT NULL, UNIQUE',         'Mã chợ (CHO_BT, CHO_AD, CHO_TD…)'],
        ['name',         'VARCHAR(150)',   'NOT NULL',                 'Tên chợ (VD: Chợ Bến Thành)'],
        ['phone',        'VARCHAR(20)',    'NULL',                     'Số điện thoại BQL chợ'],
        ['email',        'VARCHAR(100)',   'NULL',                     'Email liên hệ BQL chợ'],
        ['manager_name', 'VARCHAR(100)',   'NULL',                     'Tên trưởng Ban Quản Lý chợ'],
        ['logo',         'VARCHAR(255)',   'NULL',                     'Đường dẫn ảnh logo chợ'],
        ['province_id',  'INT',            'NULL',                    'ID Tỉnh / Thành phố'],
        ['district_id',  'INT',            'NULL',                    'ID Quận / Huyện'],
        ['ward_id',      'INT',            'NULL',                    'ID Phường / Xã'],
        ['latitude',     'DECIMAL(10,8)',  'NULL',                    'Vĩ độ bản đồ (Google Maps)'],
        ['longitude',    'DECIMAL(11,8)',  'NULL',                    'Kinh độ bản đồ (Google Maps)'],
        ['status_code',  'VARCHAR(50)',    "DEFAULT 'active'",        'Trạng thái hoạt động (active, inactive)'],
        ['created_by',   'INT',            'NULL',                    'ID user tạo chợ'],
        ['updated_by',   'INT',            'NULL',                    'ID user cập nhật chợ'],
        ['created_at',   'DATETIME',       'DEFAULT NOW()',           'Thời điểm tạo bản ghi'],
        ['updated_at',   'DATETIME',       'AUTO UPDATE',            'Thời điểm cập nhật gần nhất'],
    ]
];

// ── 9. USER_MARKETS ──
$tables[] = [
    'number' => 9,
    'name'   => 'USER_MARKETS',
    'desc'   => 'Bảng liên kết Nhân viên – Chợ – Vai trò. Cho phép 1 nhân viên làm việc tại nhiều chợ với vai trò khác nhau',
    'cols'   => [
        ['id',         'INT',      'PK, Auto Increment',                     'Khóa chính, tự tăng'],
        ['user_id',    'INT',      'NOT NULL, FK → users(id), ON DELETE CASCADE', 'ID tài khoản nhân viên'],
        ['market_id',  'INT',      'NOT NULL, FK → markets(id)',             'ID chợ được phân công'],
        ['role_id',    'INT',      'NOT NULL, FK → roles(id)',               'Vai trò cụ thể tại chợ này (Kế toán, NV nghiệp vụ…)'],
        ['created_at', 'DATETIME', 'DEFAULT NOW()',                          'Thời điểm phân công'],
    ]
];

// ── 10. AREAS ──
$tables[] = [
    'number' => 10,
    'name'   => 'AREAS',
    'desc'   => 'Phân chia khu vực trong chợ (Khu A, Khu B, Khu Thực Phẩm…)',
    'cols'   => [
        ['id',          'INT',          'PK, Auto Increment',              'Khóa chính, tự tăng'],
        ['market_id',   'INT',          'NOT NULL, FK → markets(id)',      'ID Chợ sở hữu khu vực này'],
        ['area_name',   'VARCHAR(100)', 'NOT NULL, UNIQUE',                'Tên khu vực (Khu A, Khu B…)'],
        ['block',       'VARCHAR(50)',  'NULL',                            'Dãy (Block) trong khu vực'],
        ['lot',         'VARCHAR(50)',  'NULL',                            'Lô số (Lot) trong dãy'],
        ['description', 'TEXT',         'NULL',                            'Mô tả khu vực'],
        ['created_at',  'TIMESTAMP',    'DEFAULT NOW()',                   'Thời điểm tạo bản ghi'],
    ]
];

// ── 11. STALLS ──
$tables[] = [
    'number' => 11,
    'name'   => 'STALLS',
    'desc'   => 'Danh mục sạp chợ / mặt bằng kinh doanh kèm thông tin diện tích, giá thuê và tọa độ sơ đồ',
    'cols'   => [
        ['id',               'INT',            'PK, Auto Increment',                      'Khóa chính, tự tăng'],
        ['area_id',          'INT',            'NOT NULL, FK → areas(id), ON DELETE CASCADE', 'ID khu vực chứa sạp'],
        ['stall_code',       'VARCHAR(50)',    'NOT NULL, UNIQUE',                         'Mã sạp (SẠP-A01, SẠP-B10…)'],
        ['block',            'VARCHAR(50)',    'NULL',                                     'Dãy (Block) vị trí sạp'],
        ['lot',              'VARCHAR(50)',    'NULL',                                     'Lô số (Lot) vị trí sạp'],
        ['stall_type',       'VARCHAR(100)',   "NOT NULL, DEFAULT 'Quầy hàng'",            'Loại sạp (Kiot, Quầy hàng, Mặt bằng trống…)'],
        ['area_size',        'DECIMAL(10,2)',  'NOT NULL',                                 'Diện tích (m²)'],
        ['base_price',       'DECIMAL(15,2)',  'NOT NULL',                                 'Đơn giá thuê/tháng (VNĐ)'],
        ['status_id',        'INT',            'NOT NULL, DEFAULT 3, FK → system_statuses(id)', 'Trạng thái sạp (3=Trống, 4=Đã thuê, 5=Đang sửa, 6=Tạm khóa)'],
        ['map_coordinate_x', 'INT',           'NULL',                                     'Tọa độ X trên sơ đồ chợ'],
        ['map_coordinate_y', 'INT',           'NULL',                                     'Tọa độ Y trên sơ đồ chợ'],
        ['created_at',       'TIMESTAMP',     'DEFAULT NOW()',                             'Thời điểm tạo bản ghi'],
        ['updated_at',       'TIMESTAMP',     'AUTO UPDATE',                              'Thời điểm cập nhật gần nhất'],
    ]
];

// ── 12. BUSINESS_LINES ──
$tables[] = [
    'number' => 12,
    'name'   => 'BUSINESS_LINES',
    'desc'   => 'Danh mục ngành hàng kinh doanh trong chợ (Thực phẩm tươi sống, Quần áo, Gia dụng…)',
    'cols'   => [
        ['id',          'INT',          'PK, Auto Increment',    'Khóa chính, tự tăng'],
        ['line_code',   'VARCHAR(50)',  'NOT NULL, UNIQUE',      'Mã ngành hàng kỹ thuật'],
        ['line_name',   'VARCHAR(100)', 'NOT NULL',              'Tên ngành hàng hiển thị'],
        ['description', 'TEXT',         'NULL',                  'Mô tả ngành hàng'],
        ['created_at',  'TIMESTAMP',    'DEFAULT NOW()',         'Thời điểm tạo bản ghi'],
        ['updated_at',  'TIMESTAMP',    'AUTO UPDATE',           'Thời điểm cập nhật gần nhất'],
    ]
];

// ── 13. TRADERS ──
$tables[] = [
    'number' => 13,
    'name'   => 'TRADERS',
    'desc'   => 'Thông tin hồ sơ tiểu thương kinh doanh tại chợ',
    'cols'   => [
        ['id',               'INT',          'PK, Auto Increment',                              'Khóa chính, tự tăng'],
        ['trader_code',      'VARCHAR(50)',  'NOT NULL, UNIQUE',                                 'Mã tiểu thương (TT-001…)'],
        ['fullname',         'VARCHAR(100)', 'NOT NULL',                                         'Họ và tên tiểu thương'],
        ['phone',            'VARCHAR(15)',  'NOT NULL',                                         'Số điện thoại liên hệ'],
        ['cccd',             'VARCHAR(20)',  'NOT NULL, UNIQUE',                                 'Số Căn cước công dân, duy nhất'],
        ['business_line_id', 'INT',          'NULL, FK → business_lines(id), ON DELETE SET NULL', 'ID ngành hàng kinh doanh'],
        ['address',          'TEXT',         'NULL',                                             'Địa chỉ thường trú'],
        ['description',      'TEXT',         'NULL',                                             'Mô tả ngắn về tiểu thương/cửa hàng'],
        ['license_file',     'TEXT',         'NULL',                                             'Danh sách đường dẫn tài liệu đính kèm (JSON array)'],
        ['status_id',        'INT',          'NOT NULL, FK → system_statuses(id)',               'Trạng thái tiểu thương (7=Đang KD, 8=Tạm dừng, 9=Ngừng KD, 10=Đã xóa)'],
        ['created_at',       'TIMESTAMP',    'DEFAULT NOW()',                                    'Thời điểm tạo bản ghi'],
        ['updated_at',       'TIMESTAMP',    'AUTO UPDATE',                                     'Thời điểm cập nhật gần nhất'],
    ]
];

// ── 14. CONTRACTS ──
$tables[] = [
    'number' => 14,
    'name'   => 'CONTRACTS',
    'desc'   => 'Hợp đồng thuê sạp chợ giữa Ban Quản Lý và Tiểu thương',
    'cols'   => [
        ['id',              'INT',            'PK, Auto Increment',                           'Khóa chính, tự tăng'],
        ['trader_id',       'INT',            'NOT NULL, FK → traders(id), ON DELETE RESTRICT', 'ID tiểu thương ký hợp đồng'],
        ['stall_id',        'INT',            'NOT NULL, FK → stalls(id), ON DELETE RESTRICT',  'ID sạp chợ được thuê'],
        ['contract_number', 'VARCHAR(100)',   'NOT NULL, UNIQUE',                              'Số hợp đồng, duy nhất'],
        ['name',            'VARCHAR(255)',   'NOT NULL',                                      'Tên hợp đồng hiển thị'],
        ['description',     'TEXT',           'NULL',                                          'Mô tả / ghi chú hợp đồng'],
        ['contract_file',   'VARCHAR(255)',   'NULL',                                          'Đường dẫn file scan hợp đồng'],
        ['start_date',      'DATE',           'NOT NULL',                                      'Ngày bắt đầu hiệu lực'],
        ['end_date',        'DATE',           'NOT NULL',                                      'Ngày kết thúc / hết hạn'],
        ['deposit',         'DECIMAL(15,2)',  'NOT NULL, DEFAULT 0',                           'Tiền đặt cọc thuê sạp (VNĐ)'],
        ['status_id',       'INT',            'NOT NULL, DEFAULT 11, FK → system_statuses(id)', 'Trạng thái HĐ (11=Hoạt động, 12=Hết hạn, 13=Thanh lý, 14=Chấm dứt)'],
        ['created_at',      'TIMESTAMP',      'DEFAULT NOW()',                                 'Thời điểm tạo bản ghi'],
        ['updated_at',      'TIMESTAMP',      'AUTO UPDATE',                                  'Thời điểm cập nhật gần nhất'],
    ]
];

// ── 15. CONTRACT_APPENDICES ──
$tables[] = [
    'number' => 15,
    'name'   => 'CONTRACT_APPENDICES',
    'desc'   => 'Phụ lục hợp đồng – Dùng khi thay đổi giá thuê hoặc gia hạn mà không cần hủy hợp đồng gốc',
    'cols'   => [
        ['id',              'INT',          'PK, Auto Increment',                           'Khóa chính, tự tăng'],
        ['contract_id',     'INT',          'NOT NULL, FK → contracts(id), ON DELETE CASCADE', 'ID hợp đồng gốc'],
        ['appendix_number', 'VARCHAR(100)', 'NOT NULL, UNIQUE',                              'Số phụ lục, duy nhất'],
        ['name',            'VARCHAR(255)', 'NOT NULL',                                      'Tên phụ lục hiển thị'],
        ['sign_date',       'DATE',         'NOT NULL',                                      'Ngày ký phụ lục'],
        ['effect_date',     'DATE',         'NOT NULL',                                      'Ngày bắt đầu có hiệu lực'],
        ['content',         'TEXT',         'NOT NULL',                                      'Nội dung thay đổi / điều khoản bổ sung'],
        ['file',            'VARCHAR(255)', 'NULL',                                          'Đường dẫn file scan phụ lục'],
        ['created_at',      'TIMESTAMP',    'DEFAULT NOW()',                                 'Thời điểm tạo bản ghi'],
        ['updated_at',      'TIMESTAMP',    'AUTO UPDATE',                                  'Thời điểm cập nhật gần nhất'],
    ]
];

// ── 16. UTILITY_READINGS ──
$tables[] = [
    'number' => 16,
    'name'   => 'UTILITY_READINGS',
    'desc'   => 'Ghi nhận chỉ số điện nước định kỳ hàng tháng của từng sạp chợ',
    'cols'   => [
        ['id',           'INT',       'PK, Auto Increment',                           'Khóa chính, tự tăng'],
        ['stall_id',     'INT',       'NOT NULL, FK → stalls(id), ON DELETE CASCADE', 'ID sạp chợ được ghi chỉ số'],
        ['reading_date', 'DATE',      'NOT NULL',                                     'Ngày ghi nhận chỉ số'],
        ['electric_old', 'INT',       'NOT NULL',                                     'Chỉ số điện kỳ trước (kWh)'],
        ['electric_new', 'INT',       'NOT NULL',                                     'Chỉ số điện kỳ này (kWh)'],
        ['water_old',    'INT',       'NOT NULL',                                     'Chỉ số nước kỳ trước (m³)'],
        ['water_new',    'INT',       'NOT NULL',                                     'Chỉ số nước kỳ này (m³)'],
        ['created_by',   'INT',       'NOT NULL, FK → users(id), ON DELETE RESTRICT', 'ID nhân viên ghi nhận'],
        ['created_at',   'TIMESTAMP', 'DEFAULT NOW()',                                'Thời điểm tạo bản ghi'],
    ]
];

// ── 17. BILLS ──
$tables[] = [
    'number' => 17,
    'name'   => 'BILLS',
    'desc'   => 'Hóa đơn dịch vụ hàng tháng của tiểu thương (tiền thuê, điện, nước, phí quản lý, vệ sinh, bảo vệ…)',
    'cols'   => [
        ['id',              'INT',            'PK, Auto Increment',                              'Khóa chính, tự tăng'],
        ['contract_id',     'INT',            'NOT NULL, FK → contracts(id), ON DELETE CASCADE',  'ID hợp đồng liên kết'],
        ['bill_code',       'VARCHAR(50)',    'NOT NULL, UNIQUE',                                 'Mã hóa đơn (HD-202607-001…)'],
        ['invoice_date',    'DATE',           'NOT NULL',                                        'Ngày lập hóa đơn'],
        ['due_date',        'DATE',           'NOT NULL',                                        'Hạn thanh toán'],
        ['rent_amount',     'DECIMAL(15,2)',  'NOT NULL, DEFAULT 0',                             'Tiền thuê sạp (VNĐ)'],
        ['electric_amount', 'DECIMAL(15,2)',  'NOT NULL, DEFAULT 0',                             'Tiền điện (VNĐ)'],
        ['water_amount',    'DECIMAL(15,2)',  'NOT NULL, DEFAULT 0',                             'Tiền nước (VNĐ)'],
        ['management_fee',  'DECIMAL(15,2)',  'NOT NULL, DEFAULT 0',                             'Phí quản lý (VNĐ)'],
        ['sanitation_fee',  'DECIMAL(15,2)',  'NOT NULL, DEFAULT 0',                             'Phí vệ sinh (VNĐ)'],
        ['security_fee',    'DECIMAL(15,2)',  'NOT NULL, DEFAULT 0',                             'Phí bảo vệ (VNĐ)'],
        ['other_fee',       'DECIMAL(15,2)',  'NOT NULL, DEFAULT 0',                             'Phí khác (VNĐ)'],
        ['total_amount',    'DECIMAL(15,2)',  'NOT NULL, DEFAULT 0',                             'Tổng cộng hóa đơn (VNĐ)'],
        ['paid_amount',     'DECIMAL(15,2)',  'NOT NULL, DEFAULT 0',                             'Số tiền đã trả thực tế (VNĐ)'],
        ['status',          'VARCHAR(20)',    "NOT NULL, DEFAULT 'unpaid'",                       'Trạng thái thanh toán (unpaid / partially_paid / paid)'],
        ['created_at',      'TIMESTAMP',      'DEFAULT NOW()',                                   'Thời điểm tạo bản ghi'],
        ['updated_at',      'TIMESTAMP',      'AUTO UPDATE',                                    'Thời điểm cập nhật gần nhất'],
    ]
];

// ── 18. RECEIPTS_PAYMENTS ──
$tables[] = [
    'number' => 18,
    'name'   => 'RECEIPTS_PAYMENTS',
    'desc'   => 'Sổ quỹ Thu – Chi tài chính của chợ. Lưu trữ phiếu thu (tiền hóa đơn, tiền cọc) và phiếu chi (lương, sửa chữa…)',
    'cols'   => [
        ['id',               'INT',            'PK, Auto Increment',                           'Khóa chính, tự tăng'],
        ['transaction_code', 'VARCHAR(50)',    'NOT NULL, UNIQUE',                               'Mã phiếu thu/chi (PT-0001, PC-0002…)'],
        ['type',             'VARCHAR(10)',    'NOT NULL',                                       'Loại giao dịch (receipt = Thu, payment = Chi)'],
        ['amount',           'DECIMAL(15,2)',  'NOT NULL',                                      'Số tiền giao dịch (VNĐ)'],
        ['transaction_date', 'DATE',           'NOT NULL',                                      'Ngày thực hiện giao dịch'],
        ['category',         'VARCHAR(100)',   'NOT NULL',                                      'Danh mục (Tiền thuê sạp, Tiền điện, Lương nhân viên, Sửa chữa…)'],
        ['note',             'TEXT',           'NULL',                                          'Ghi chú phiếu thu/chi'],
        ['reference_id',     'INT',            'NULL',                                          'ID hóa đơn liên kết (nếu là phiếu thu tiền hóa đơn)'],
        ['market_id',        'INT',            'NULL, FK → markets(id)',                        'ID Chợ liên kết (multi-market)'],
        ['created_by',       'INT',            'NOT NULL, FK → users(id), ON DELETE RESTRICT',  'ID nhân viên lập phiếu'],
        ['created_at',       'TIMESTAMP',      'DEFAULT NOW()',                                 'Thời điểm tạo bản ghi'],
    ]
];

// ── 19. TRADER_ATTP ──
$tables[] = [
    'number' => 19,
    'name'   => 'TRADER_ATTP',
    'desc'   => 'Quản lý giấy tờ An toàn Thực phẩm (ATTP) của tiểu thương (Chứng nhận ATTP, Giấy khám SK, Tập huấn ATTP)',
    'cols'   => [
        ['id',          'INT',          'PK, Auto Increment',                              'Khóa chính, tự tăng'],
        ['trader_id',   'INT',          'NOT NULL, FK → traders(id), ON DELETE CASCADE',   'ID tiểu thương sở hữu giấy tờ'],
        ['doc_type',    'VARCHAR(50)',  'NOT NULL',                                        'Loại giấy tờ (ATTP / Health / Training)'],
        ['doc_number',  'VARCHAR(100)', 'NOT NULL',                                        'Số giấy chứng nhận'],
        ['name',        'VARCHAR(255)', 'NOT NULL',                                        'Tên giấy tờ / chứng nhận'],
        ['description', 'TEXT',         'NULL',                                            'Mô tả ngắn'],
        ['file',        'VARCHAR(255)', 'NULL',                                            'Đường dẫn file đính kèm (scan/ảnh)'],
        ['status_id',   'INT',          'NOT NULL, DEFAULT 18, FK → system_statuses(id)',  'Trạng thái (18=Còn hạn, 19=Hết hạn)'],
        ['issuer',      'VARCHAR(150)', 'NULL',                                            'Cơ quan cấp giấy'],
        ['issue_date',  'DATE',         'NOT NULL',                                        'Ngày cấp'],
        ['expiry_date', 'DATE',         'NOT NULL',                                        'Ngày hết hạn'],
        ['created_at',  'TIMESTAMP',    'DEFAULT NOW()',                                   'Thời điểm tạo bản ghi'],
        ['updated_at',  'TIMESTAMP',    'AUTO UPDATE',                                    'Thời điểm cập nhật gần nhất'],
    ]
];

// ── 20. FOOD_SAFETY_INSPECTIONS ──
$tables[] = [
    'number' => 20,
    'name'   => 'FOOD_SAFETY_INSPECTIONS',
    'desc'   => 'Kế hoạch kiểm tra vệ sinh An toàn Thực phẩm định kỳ hoặc đột xuất của BQL và cơ quan chức năng',
    'cols'   => [
        ['id',               'INT',          'PK, Auto Increment',          'Khóa chính, tự tăng'],
        ['market_id',        'INT',          'NOT NULL, FK → markets(id)',  'ID Chợ được kiểm tra'],
        ['inspection_title', 'VARCHAR(255)', 'NOT NULL',                    'Tên đợt thanh tra / kiểm tra'],
        ['inspection_team',  'VARCHAR(255)', 'NOT NULL',                    'Tên đoàn kiểm tra'],
        ['planned_date',     'DATE',         'NOT NULL',                    'Ngày dự kiến kiểm tra'],
        ['actual_date',      'DATE',         'NULL',                        'Ngày thực tế kiểm tra'],
        ['status',           'VARCHAR(20)',  "NOT NULL, DEFAULT 'planned'", 'Trạng thái (planned / completed / cancelled)'],
        ['notes',            'TEXT',         'NULL',                        'Ghi chú / kết luận kiểm tra'],
        ['created_at',       'TIMESTAMP',    'DEFAULT NOW()',               'Thời điểm tạo bản ghi'],
    ]
];

// ── 21. FOOD_SAFETY_VIOLATIONS ──
$tables[] = [
    'number' => 21,
    'name'   => 'FOOD_SAFETY_VIOLATIONS',
    'desc'   => 'Biên bản ghi nhận & xử lý vi phạm vệ sinh ATTP của các hộ kinh doanh',
    'cols'   => [
        ['id',               'INT',          'PK, Auto Increment',                                          'Khóa chính, tự tăng'],
        ['trader_id',        'INT',          'NOT NULL, FK → traders(id), ON DELETE CASCADE',                'ID tiểu thương vi phạm'],
        ['inspection_id',    'INT',          'NULL, FK → food_safety_inspections(id), ON DELETE SET NULL',   'ID đợt kiểm tra phát hiện vi phạm (nếu có)'],
        ['violation_code',   'VARCHAR(50)',  'NOT NULL, UNIQUE',                                             'Mã biên bản vi phạm (BBVP-0089…)'],
        ['violation_date',   'DATE',         'NOT NULL',                                                     'Ngày vi phạm'],
        ['description',      'TEXT',         'NOT NULL',                                                     'Mô tả hành vi vi phạm'],
        ['penalty_measure',  'TEXT',         'NOT NULL',                                                     'Hình thức xử lý (Cảnh cáo, Phạt tiền, Đình chỉ sạp…)'],
        ['status',           'VARCHAR(20)',  "NOT NULL, DEFAULT 'pending'",                                  'Trạng thái xử lý (pending / resolved)'],
        ['resolved_date',    'DATE',         'NULL',                                                         'Ngày hoàn tất khắc phục'],
        ['created_at',       'TIMESTAMP',    'DEFAULT NOW()',                                                'Thời điểm tạo bản ghi'],
        ['updated_at',       'TIMESTAMP',    'AUTO UPDATE',                                                 'Thời điểm cập nhật gần nhất'],
    ]
];

// ── 22. SYSTEM_LOGS ──
$tables[] = [
    'number' => 22,
    'name'   => 'SYSTEM_LOGS',
    'desc'   => 'Nhật ký hoạt động hệ thống – Ghi lại mọi thao tác của nhân viên để phục vụ kiểm toán & bảo mật',
    'cols'   => [
        ['id',                 'INT',          'PK, Auto Increment',                          'Khóa chính, tự tăng'],
        ['user_id',            'INT',          'NULL, FK → users(id), ON DELETE SET NULL',    'ID nhân viên thực hiện thao tác'],
        ['action_type',        'VARCHAR(50)',  'NOT NULL',                                    'Loại thao tác (login, logout, create, update, delete, view, export)'],
        ['action_description', 'TEXT',         'NOT NULL',                                    'Mô tả chi tiết thao tác'],
        ['ip_address',         'VARCHAR(45)',  'NULL',                                        'Địa chỉ IP của nhân viên'],
        ['user_agent',         'TEXT',         'NULL',                                        'Chuỗi User Agent trình duyệt'],
        ['created_at',         'TIMESTAMP',    'DEFAULT NOW()',                               'Thời điểm ghi nhận'],
    ]
];

// ── 23. MARKET_MAP_ELEMENTS ──
$tables[] = [
    'number' => 23,
    'name'   => 'MARKET_MAP_ELEMENTS',
    'desc'   => 'Quản lý các phần tử đồ họa trên sơ đồ chợ tương tác (sạp, cổng, đường đi, tiện ích, nhãn chữ…)',
    'cols'   => [
        ['id',           'INT',         'PK, Auto Increment',                          'Khóa chính, tự tăng'],
        ['element_type', 'VARCHAR(50)', 'NOT NULL',                                    'Loại phần tử (stall / gate / door / street / utility / text)'],
        ['element_name', 'VARCHAR(100)','NULL',                                        'Tên hiển thị của phần tử trên sơ đồ'],
        ['stall_id',     'INT',         'NULL, FK → stalls(id), ON DELETE SET NULL, UNIQUE', 'Liên kết sạp chợ (chỉ dùng khi element_type = stall)'],
        ['pos_x',        'INT',         'NOT NULL, DEFAULT 100',                       'Tọa độ X (px) trên canvas'],
        ['pos_y',        'INT',         'NOT NULL, DEFAULT 100',                       'Tọa độ Y (px) trên canvas'],
        ['width',        'INT',         'NOT NULL, DEFAULT 80',                        'Chiều rộng phần tử (px)'],
        ['height',       'INT',         'NOT NULL, DEFAULT 60',                        'Chiều cao phần tử (px)'],
        ['rotation',     'INT',         'NOT NULL, DEFAULT 0',                         'Góc xoay (độ: 0, 90, 180, 270)'],
        ['color',        'VARCHAR(20)', 'NULL',                                        'Mã màu tùy chỉnh (#hex) cho đường đi/khối tiện ích'],
    ]
];


// ========================= XUẤT EXCEL XML =========================

$filename = 'TuDien_CSDL_QuanLyCho_' . date('Ymd_His') . '.xls';

// CLI: dùng output buffering → ghi file trực tiếp (tránh lỗi encoding PowerShell)
// Web: gửi header download
$isCli = (php_sapi_name() === 'cli');
if ($isCli) {
    ob_start();
} else {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
}

// BOM cho UTF-8
echo "\xEF\xBB\xBF";

// ── Mở Workbook XML ──
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">

<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
 <Title>Từ điển Cơ sở dữ liệu - Hệ thống Quản lý Chợ</Title>
 <Author>Hệ thống Quản lý Chợ</Author>
 <Created><?php echo date('Y-m-d\TH:i:s\Z'); ?></Created>
</DocumentProperties>

<Styles>
 <!-- Tiêu đề bảng lớn (merge) -->
 <Style ss:ID="sTitle">
  <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
  <Font ss:FontName="Arial" ss:Size="13" ss:Bold="1" ss:Color="#FFFFFF"/>
  <Interior ss:Color="#1B4F72" ss:Pattern="Solid"/>
  <Borders>
   <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0D3B5E"/>
   <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0D3B5E"/>
   <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0D3B5E"/>
   <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#0D3B5E"/>
  </Borders>
 </Style>
 <!-- Dòng mô tả bảng -->
 <Style ss:ID="sDesc">
  <Alignment ss:Horizontal="Left" ss:Vertical="Center" ss:WrapText="1"/>
  <Font ss:FontName="Arial" ss:Size="10" ss:Italic="1" ss:Color="#2C3E50"/>
  <Interior ss:Color="#EBF5FB" ss:Pattern="Solid"/>
  <Borders>
   <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#AED6F1"/>
   <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#AED6F1"/>
   <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#AED6F1"/>
  </Borders>
 </Style>
 <!-- Header cột -->
 <Style ss:ID="sHeader">
  <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>
  <Font ss:FontName="Arial" ss:Size="10" ss:Bold="1" ss:Color="#FFFFFF"/>
  <Interior ss:Color="#2E86C1" ss:Pattern="Solid"/>
  <Borders>
   <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#1B4F72"/>
   <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#1B4F72"/>
   <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#1B4F72"/>
   <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#1B4F72"/>
  </Borders>
 </Style>
 <!-- Dữ liệu cột thường -->
 <Style ss:ID="sData">
  <Alignment ss:Vertical="Center" ss:WrapText="1"/>
  <Font ss:FontName="Consolas" ss:Size="10" ss:Color="#2C3E50"/>
  <Borders>
   <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D5DBDB"/>
   <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D5DBDB"/>
   <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D5DBDB"/>
  </Borders>
 </Style>
 <!-- Dữ liệu cột - dòng chẵn -->
 <Style ss:ID="sDataAlt">
  <Alignment ss:Vertical="Center" ss:WrapText="1"/>
  <Font ss:FontName="Consolas" ss:Size="10" ss:Color="#2C3E50"/>
  <Interior ss:Color="#F2F9FC" ss:Pattern="Solid"/>
  <Borders>
   <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D5DBDB"/>
   <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D5DBDB"/>
   <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#D5DBDB"/>
  </Borders>
 </Style>
 <!-- Dòng chú thích (Legend) -->
 <Style ss:ID="sLegend">
  <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
  <Font ss:FontName="Arial" ss:Size="9" ss:Color="#7F8C8D"/>
 </Style>
 <!-- Dòng trống phân cách -->
 <Style ss:ID="sSpacer">
  <Interior ss:Color="#FFFFFF" ss:Pattern="Solid"/>
 </Style>
</Styles>

<?php
$esc = function($s) { return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8'); };

foreach ($tables as $i => $table) {
    $num  = $table['number'];
    $name = $esc($table['name']);
    $desc = $esc($table['desc']);
    $cols = $table['cols'];
    // Tên tab: "1. status_colors" (viết thường, tối đa 31 ký tự cho Excel)
    $sheetName = $esc($num . '. ' . mb_strtolower($table['name']));
    if (mb_strlen($sheetName) > 31) $sheetName = mb_substr($sheetName, 0, 31);
?>
<Worksheet ss:Name="<?php echo $sheetName; ?>">
 <Table ss:DefaultColumnWidth="120" ss:DefaultRowHeight="22">
  <Column ss:Index="1" ss:Width="180"/>
  <Column ss:Index="2" ss:Width="140"/>
  <Column ss:Index="3" ss:Width="280"/>
  <Column ss:Index="4" ss:Width="380"/>
<?php
    // ── Dòng tiêu đề bảng (merge 4 cột) ──
    echo "  <Row ss:Height=\"30\">\n";
    echo "   <Cell ss:StyleID=\"sTitle\" ss:MergeAcross=\"3\"><Data ss:Type=\"String\">Bảng: {$num}. {$name}</Data></Cell>\n";
    echo "  </Row>\n";

    // ── Dòng mô tả (merge 4 cột) ──
    echo "  <Row ss:Height=\"24\">\n";
    echo "   <Cell ss:StyleID=\"sDesc\" ss:MergeAcross=\"3\"><Data ss:Type=\"String\">{$desc}</Data></Cell>\n";
    echo "  </Row>\n";

    // ── Dòng trống ──
    echo "  <Row ss:Height=\"6\"><Cell ss:StyleID=\"sSpacer\"/></Row>\n";

    // ── Header cột ──
    echo "  <Row ss:Height=\"24\">\n";
    echo "   <Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">Tên Cột</Data></Cell>\n";
    echo "   <Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">Kiểu Dữ Liệu</Data></Cell>\n";
    echo "   <Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">Ràng Buộc / Mặc Định</Data></Cell>\n";
    echo "   <Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">Mô Tả / Ghi Chú</Data></Cell>\n";
    echo "  </Row>\n";

    // ── Dữ liệu cột ──
    foreach ($cols as $ci => $col) {
        $style = ($ci % 2 === 0) ? 'sData' : 'sDataAlt';
        echo "  <Row>\n";
        echo "   <Cell ss:StyleID=\"{$style}\"><Data ss:Type=\"String\">{$esc($col[0])}</Data></Cell>\n";
        echo "   <Cell ss:StyleID=\"{$style}\"><Data ss:Type=\"String\">{$esc($col[1])}</Data></Cell>\n";
        echo "   <Cell ss:StyleID=\"{$style}\"><Data ss:Type=\"String\">{$esc($col[2])}</Data></Cell>\n";
        echo "   <Cell ss:StyleID=\"{$style}\"><Data ss:Type=\"String\">{$esc($col[3])}</Data></Cell>\n";
        echo "  </Row>\n";
    }

    // ── Dòng chú thích (Legend) ──
    echo "  <Row ss:Height=\"20\">\n";
    echo "   <Cell ss:StyleID=\"sLegend\" ss:MergeAcross=\"3\"><Data ss:Type=\"String\">🔴 Đỏ đậm = Khóa chính (PK)   🟢 Xanh lá = Khóa ngoại (FK)   ⚪ Trắng = Cột thường</Data></Cell>\n";
    echo "  </Row>\n";
?>
 </Table>
 <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
  <FitToPage/>
  <Print>
   <ValidPrinterInfo/>
   <HorizontalResolution>600</HorizontalResolution>
   <VerticalResolution>600</VerticalResolution>
  </Print>
 </WorksheetOptions>
</Worksheet>
<?php } ?>
</Workbook>
<?php
// CLI: ghi buffer ra file trực tiếp
if ($isCli) {
    $content = ob_get_clean();
    $outPath = __DIR__ . DIRECTORY_SEPARATOR . $filename;
    file_put_contents($outPath, $content);
    echo "Da xuat thanh cong: " . $outPath . "\n";
}

