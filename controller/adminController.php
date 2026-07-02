<?php
/**
 * Controller xử lý các trang quản trị của Ban Quản Lý
 */
class adminController {
    
    public function __construct() {
        // Bảo vệ toàn bộ khu vực admin, yêu cầu phải đăng nhập
        session::requireLogin();
    }

    /**
     * Trang Dashboard chính
     */
    public function dashboard() {
        // Dữ liệu mẫu để hiển thị biểu đồ và thống kê nhanh
        $stats = [
            'total_stalls' => 120,
            'rented_stalls' => 85,
            'empty_stalls' => 30,
            'repairing_stalls' => 5,
            'total_traders' => 82,
            'active_contracts' => 85,
            'revenue_this_month' => 450000000, // 450 triệu
        ];

        $this->view('backend/dashboard/index', [
            'title' => 'Bảng Điều Khiển',
            'stats' => $stats
        ]);
    }

    /**
     * Trang tùy biến chủ đề giao diện (Theme generator)
     */
    public function theme() {
        $this->view('backend/setting/theme', [
            'title' => 'Tùy Biến Giao Diện'
        ]);
    }

    /**
     * Phân hệ Quản lý Sạp chợ
     */
    public function stalls() {
        $stalls = [
            ['id' => 1, 'stall_code' => 'SẠP-A01', 'zone' => 'Khu A (Quần áo)', 'location' => 'Dãy A, Số 01', 'area' => 15, 'price' => 3000000, 'status' => 'rented'],
            ['id' => 2, 'stall_code' => 'SẠP-A02', 'zone' => 'Khu A (Quần áo)', 'location' => 'Dãy A, Số 02', 'area' => 15, 'price' => 3000000, 'status' => 'empty'],
            ['id' => 3, 'stall_code' => 'SẠP-B01', 'zone' => 'Khu B (Thực phẩm)', 'location' => 'Dãy B, Số 01', 'area' => 20, 'price' => 4500000, 'status' => 'rented'],
            ['id' => 4, 'stall_code' => 'SẠP-B02', 'zone' => 'Khu B (Thực phẩm)', 'location' => 'Dãy B, Số 02', 'area' => 20, 'price' => 4500000, 'status' => 'repairing'],
            ['id' => 5, 'stall_code' => 'SẠP-C01', 'zone' => 'Khu C (Ăn uống)', 'location' => 'Dãy C, Số 01', 'area' => 25, 'price' => 5000000, 'status' => 'empty'],
        ];

        $this->view('backend/stall/index', [
            'title' => 'Quản Lý Sạp Chợ',
            'stalls' => $stalls
        ]);
    }

    /**
     * Phân hệ Hợp đồng thuê sạp
     */
    public function contracts() {
        $contracts = [
            ['id' => 1, 'contract_code' => 'HĐ-2026-0001', 'trader_name' => 'Nguyễn Thị Thu Hà', 'stall_code' => 'SẠP-A01', 'start_date' => '01/01/2026', 'end_date' => '31/12/2026', 'price' => 3000000, 'deposit' => 6000000, 'status' => 'active'],
            ['id' => 2, 'contract_code' => 'HĐ-2026-0002', 'trader_name' => 'Trần Văn Hoàng', 'stall_code' => 'SẠP-B01', 'start_date' => '15/02/2026', 'end_date' => '15/02/2027', 'price' => 4500000, 'deposit' => 9000000, 'status' => 'active'],
            ['id' => 3, 'contract_code' => 'HĐ-2025-0089', 'trader_name' => 'Phạm Minh Tuấn', 'stall_code' => 'SẠP-A02', 'start_date' => '01/01/2025', 'end_date' => '31/12/2025', 'price' => 3000000, 'deposit' => 6000000, 'status' => 'expired'],
        ];

        $this->view('backend/contract/index', [
            'title' => 'Hợp Đồng Thuê Sạp',
            'contracts' => $contracts
        ]);
    }

    /**
     * Phân hệ Ghi số Điện & Nước
     */
    public function utilities() {
        $readings = [
            ['id' => 1, 'period' => '06/2026', 'stall_code' => 'SẠP-A01', 'old_electric' => 1540, 'new_electric' => 1690, 'old_water' => 240, 'new_water' => 255, 'recorded_date' => '25/06/2026', 'recorder' => 'Lê Thị Bình'],
            ['id' => 2, 'period' => '06/2026', 'stall_code' => 'SẠP-B01', 'old_electric' => 3200, 'new_electric' => 3450, 'old_water' => 410, 'new_water' => 432, 'recorded_date' => '25/06/2026', 'recorder' => 'Lê Thị Bình'],
        ];

        $this->view('backend/finance/utilities', [
            'title' => 'Chỉ Số Điện & Nước',
            'readings' => $readings
        ]);
    }

    /**
     * Phân hệ Hóa đơn dịch vụ
     */
    public function bills() {
        $bills = [
            ['id' => 1, 'bill_code' => 'HĐ-0626-001', 'stall_code' => 'SẠP-A01', 'trader_name' => 'Nguyễn Thị Thu Hà', 'period' => '06/2026', 'total_amount' => 3650000, 'due_date' => '10/07/2026', 'status' => 'unpaid'],
            ['id' => 2, 'bill_code' => 'HĐ-0626-002', 'stall_code' => 'SẠP-B01', 'trader_name' => 'Trần Văn Hoàng', 'period' => '06/2026', 'total_amount' => 5480000, 'due_date' => '10/07/2026', 'status' => 'paid'],
        ];

        $this->view('backend/finance/bills', [
            'title' => 'Hóa Đơn Dịch Vụ',
            'bills' => $bills
        ]);
    }

    /**
     * Phân hệ Phiếu thu - Phiếu chi
     */
    public function transactions() {
        $transactions = [
            ['id' => 1, 'transaction_code' => 'PT-0001', 'type' => 'receipt', 'target' => 'Trần Văn Hoàng (SẠP-B01)', 'amount' => 5480000, 'note' => 'Thu tiền hóa đơn tháng 06/2026', 'date' => '28/06/2026', 'creator' => 'Nguyễn Văn An'],
            ['id' => 2, 'transaction_code' => 'PC-0001', 'type' => 'payment', 'target' => 'Công ty Điện lực Hà Nội', 'amount' => 12500000, 'note' => 'Thanh toán tiền điện tổng của chợ tháng 06/2026', 'date' => '29/06/2026', 'creator' => 'Nguyễn Văn An'],
        ];

        $this->view('backend/finance/transactions', [
            'title' => 'Thu - Chi Tài Chính',
            'transactions' => $transactions
        ]);
    }

    /**
     * Phân hệ An toàn thực phẩm
     */
    public function foodsafety() {
        $certificates = [
            ['id' => 1, 'trader_name' => 'Trần Văn Hoàng', 'shop_name' => 'Hộ kinh doanh Hoàng Thực Phẩm', 'cert_code' => '123/2025/ATTP-HN', 'issue_date' => '10/05/2025', 'expire_date' => '10/05/2028', 'status' => 'active'],
            ['id' => 2, 'trader_name' => 'Lê Thị Mai', 'shop_name' => 'Hộ rau sạch Mai Lê', 'cert_code' => '456/2024/ATTP-HN', 'issue_date' => '12/03/2024', 'expire_date' => '12/03/2027', 'status' => 'active'],
        ];

        $this->view('backend/foodsafety/index', [
            'title' => 'An Toàn Thực Phẩm',
            'certificates' => $certificates
        ]);
    }

    /**
     * Phân hệ Quản lý tài khoản & phân quyền
     */
    public function users() {
        $users = [
            ['id' => 1, 'username' => 'admin', 'fullname' => 'Ban Quản Lý Chợ', 'email' => 'admin@market.com', 'role' => 'admin', 'role_name' => 'Quản trị hệ thống', 'status' => 'active'],
            ['id' => 2, 'username' => 'ketoan_an', 'fullname' => 'Nguyễn Văn An', 'email' => 'an.nv@market.com', 'role' => 'staff', 'role_name' => 'Kế toán', 'status' => 'active'],
            ['id' => 3, 'username' => 'thuquy_binh', 'fullname' => 'Lê Thị Bình', 'email' => 'binh.lt@market.com', 'role' => 'staff', 'role_name' => 'Thủ quỹ / Nhân viên', 'status' => 'active'],
        ];

        $this->view('backend/user/index', [
            'title' => 'Tài Khoản & Phân Quyền',
            'users' => $users
        ]);
    }

    /**
     * Thêm Sạp chợ mới (Mockup Form)
     */
    public function stall_add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session::set('success_message', 'Khai báo sạp chợ mới thành công!');
            header('Location: ' . BASE_URL . 'admin/stalls');
            exit();
        }
        $this->view('backend/stall/add', ['title' => 'Khai Báo Sạp Chợ Mới']);
    }

    /**
     * Chỉnh sửa Sạp chợ (Mockup Form)
     */
    public function stall_edit($id) {
        $stall = ['id' => $id, 'stall_code' => 'SẠP-A01', 'zone' => 'Khu A (Quần áo)', 'location' => 'Dãy A, Số 01', 'area' => 15, 'price' => 3000000, 'status' => 'rented'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session::set('success_message', 'Cập nhật thông tin sạp chợ thành công!');
            header('Location: ' . BASE_URL . 'admin/stalls');
            exit();
        }
        $this->view('backend/stall/edit', ['title' => 'Chỉnh Sửa Sạp Chợ', 'stall' => $stall]);
    }

    /**
     * Lập Hợp đồng mới (Mockup Form)
     */
    public function contract_add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session::set('success_message', 'Lập hợp đồng thuê sạp thành công!');
            header('Location: ' . BASE_URL . 'admin/contracts');
            exit();
        }
        $this->view('backend/contract/add', ['title' => 'Lập Hợp Đồng Thuê Sạp']);
    }

    /**
     * Ghi chỉ số điện nước mới (Mockup Form)
     */
    public function utility_add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session::set('success_message', 'Ghi nhận chỉ số điện nước thành công!');
            header('Location: ' . BASE_URL . 'admin/utilities');
            exit();
        }
        $this->view('backend/finance/utility_add', ['title' => 'Ghi Số Điện Nước Mới']);
    }

    /**
     * Lập phiếu thu - chi (Mockup Form)
     */
    public function transaction_add() {
        $type = $_GET['type'] ?? 'receipt';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $msg = ($_POST['type'] === 'receipt') ? 'Lập phiếu thu thành công!' : 'Lập phiếu chi thành công!';
            session::set('success_message', $msg);
            header('Location: ' . BASE_URL . 'admin/transactions');
            exit();
        }
        $this->view('backend/finance/transaction_add', [
            'title' => ($type === 'receipt') ? 'Lập Phiếu Thu Tài Chính' : 'Lập Phiếu Chi Tài Chính',
            'type' => $type
        ]);
    }

    /**
     * Khai báo chứng nhận ATTP mới (Mockup Form)
     */
    public function foodsafety_add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session::set('success_message', 'Khai báo chứng nhận ATTP thành công!');
            header('Location: ' . BASE_URL . 'admin/foodsafety');
            exit();
        }
        $this->view('backend/foodsafety/add', ['title' => 'Khai Báo Chứng Nhận ATTP']);
    }

    /**
     * Tạo tài khoản nhân viên mới (Mockup Form)
     */
    public function user_add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session::set('success_message', 'Tạo tài khoản nhân viên thành công!');
            header('Location: ' . BASE_URL . 'admin/users');
            exit();
        }
        $this->view('backend/user/add', ['title' => 'Tạo Tài Khoản Nhân Viên']);
    }

    /**
     * Phân hệ Quản lý Tiểu thương
     */
    public function traders() {
        $traderModel = new merchantModel();
        $traders = [];
        
        try {
            // Thử lấy dữ liệu từ Database
            $traders = $traderModel->getAll();
        } catch (Exception $e) {
            // Nếu chưa kết nối/import database, fallback sang dữ liệu mẫu
            $traders = [
                [
                    'id' => 1,
                    'trader_code' => 'TT-0001',
                    'fullname' => 'Nguyễn Thị Thu Hà',
                    'phone' => '0912.345.678',
                    'cccd' => '001195001234',
                    'address' => '12 Phố Huế, Hai Bà Trưng, Hà Nội',
                    'business_line' => 'Quần áo thời trang',
                    'status' => 'active'
                ],
                [
                    'id' => 2,
                    'trader_code' => 'TT-0002',
                    'fullname' => 'Trần Văn Hoàng',
                    'phone' => '0987.654.321',
                    'cccd' => '002196005678',
                    'address' => '45 Đại Cồ Việt, Bách Khoa, Hà Nội',
                    'business_line' => 'Thịt gia súc, gia cầm',
                    'status' => 'active'
                ],
                [
                    'id' => 3,
                    'trader_code' => 'TT-0003',
                    'fullname' => 'Phạm Minh Tuấn',
                    'phone' => '0905.112.233',
                    'cccd' => '003197009012',
                    'address' => '78 Lò Đúc, Đống Đa, Hà Nội',
                    'business_line' => 'Quần áo trẻ em',
                    'status' => 'active'
                ],
                [
                    'id' => 4,
                    'trader_code' => 'TT-0004',
                    'fullname' => 'Lê Thị Mai',
                    'phone' => '0934.556.677',
                    'cccd' => '004198003456',
                    'address' => '99 Bạch Mai, Hai Bà Trưng, Hà Nội',
                    'business_line' => 'Rau củ quả sạch',
                    'status' => 'active'
                ]
            ];
        }

        $this->view('backend/merchant/index', [
            'title' => 'Quản Lý Tiểu Thương',
            'traders' => $traders
        ]);
    }

    /**
     * Thêm tiểu thương mới (GET/POST)
     */
    public function trader_add() {
        $error = '';
        $success = '';
        $data = [
            'trader_code' => '',
            'fullname' => '',
            'phone' => '',
            'cccd' => '',
            'address' => '',
            'business_line' => '',
            'status' => 'active'
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'trader_code'   => $_POST['trader_code'] ?? '',
                'fullname'      => $_POST['fullname'] ?? '',
                'phone'         => $_POST['phone'] ?? '',
                'cccd'          => $_POST['cccd'] ?? '',
                'address'       => $_POST['address'] ?? '',
                'business_line' => $_POST['business_line'] ?? '',
                'status'        => $_POST['status'] ?? 'active'
            ];

            // Xác thực dữ liệu form
            $validator = new validator();
            $validator->required('trader_code', $data['trader_code'], 'Mã tiểu thương không được để trống.')
                      ->required('fullname', $data['fullname'], 'Họ tên không được để trống.')
                      ->required('phone', $data['phone'], 'Số điện thoại không được để trống.')
                      ->phone('phone', $data['phone'], 'Số điện thoại không đúng định dạng Việt Nam.')
                      ->required('cccd', $data['cccd'], 'Số CCCD không được để trống.');

            if ($validator->isValid()) {
                $traderModel = new merchantModel();
                try {
                    $traderModel->create($data);
                    // Đăng ký thông báo thành công vào session để hiển thị sau redirect
                    session::set('success_message', 'Thêm tiểu thương mới thành công!');
                    header('Location: ' . BASE_URL . 'admin/traders');
                    exit();
                } catch (Exception $e) {
                    $error = 'Lỗi lưu dữ liệu: ' . $e->getMessage();
                }
            } else {
                $errors = $validator->getErrors();
                $error = reset($errors); // Lấy lỗi đầu tiên để hiển thị
            }
        }

        $this->view('backend/merchant/add', [
            'title' => 'Thêm Tiểu Thương Mới',
            'error' => $error,
            'data' => $data
        ]);
    }

    /**
     * Sửa thông tin tiểu thương (GET/POST)
     */
    public function trader_edit($id) {
        if (!$id) {
            header('Location: ' . BASE_URL . 'admin/traders');
            exit();
        }

        $traderModel = new merchantModel();
        $error = '';
        
        try {
            $trader = $traderModel->getById($id);
            if (!$trader) {
                throw new Exception("Tiểu thương không tồn tại trên hệ thống.");
            }
        } catch (Exception $e) {
            session::set('error_message', $e->getMessage());
            header('Location: ' . BASE_URL . 'admin/traders');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'fullname'      => $_POST['fullname'] ?? '',
                'phone'         => $_POST['phone'] ?? '',
                'cccd'          => $_POST['cccd'] ?? '',
                'address'       => $_POST['address'] ?? '',
                'business_line' => $_POST['business_line'] ?? '',
                'status'        => $_POST['status'] ?? 'active'
            ];

            // Xác thực dữ liệu
            $validator = new validator();
            $validator->required('fullname', $data['fullname'], 'Họ tên không được để trống.')
                      ->required('phone', $data['phone'], 'Số điện thoại không được để trống.')
                      ->phone('phone', $data['phone'], 'Số điện thoại không đúng định dạng.')
                      ->required('cccd', $data['cccd'], 'Số CCCD không được để trống.');

            if ($validator->isValid()) {
                try {
                    $traderModel->update($id, $data);
                    session::set('success_message', 'Cập nhật thông tin tiểu thương thành công!');
                    header('Location: ' . BASE_URL . 'admin/traders');
                    exit();
                } catch (Exception $e) {
                    $error = 'Lỗi cập nhật: ' . $e->getMessage();
                }
            } else {
                $errors = $validator->getErrors();
                $error = reset($errors);
            }
            
            // Đồng bộ dữ liệu hiển thị lại form nếu có lỗi
            $trader = array_merge($trader, $data);
        }

        $this->view('backend/merchant/edit', [
            'title' => 'Chỉnh Sửa Tiểu Thương',
            'error' => $error,
            'trader' => $trader
        ]);
    }

    /**
     * Xóa tiểu thương (GET)
     */
    public function trader_delete($id) {
        if ($id) {
            $traderModel = new merchantModel();
            try {
                $traderModel->delete($id);
                session::set('success_message', 'Xóa tiểu thương thành công!');
            } catch (Exception $e) {
                session::set('error_message', 'Không thể xóa tiểu thương: ' . $e->getMessage());
            }
        }
        header('Location: ' . BASE_URL . 'admin/traders');
        exit();
    }



    /**
     * Hàm render view kèm theo Layout của Admin Dashboard
     */
    protected function view($templatePath, $data = []) {
        // Giải nén mảng thành các biến độc lập
        extract($data);

        // Nạp layout trên
        if (file_exists(DIR_TEMPLATE . '/backend/layouts/header.php')) {
            require_once DIR_TEMPLATE . '/backend/layouts/header.php';
        }
        if (file_exists(DIR_TEMPLATE . '/backend/layouts/sidebar.php')) {
            require_once DIR_TEMPLATE . '/backend/layouts/sidebar.php';
        }
        if (file_exists(DIR_TEMPLATE . '/backend/layouts/navbar.php')) {
            require_once DIR_TEMPLATE . '/backend/layouts/navbar.php';
        }

        // Nạp nội dung trang con
        $viewFile = DIR_TEMPLATE . '/' . $templatePath . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            echo "<div class='container-fluid'><p class='text-danger'>Không tìm thấy giao diện: {$templatePath}</p></div>";
        }

        // Nạp layout dưới
        if (file_exists(DIR_TEMPLATE . '/backend/layouts/footer.php')) {
            require_once DIR_TEMPLATE . '/backend/layouts/footer.php';
        }
    }
}
