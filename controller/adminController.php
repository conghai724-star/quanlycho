<?php
/**
 * Controller xử lý các trang quản trị của Ban Quản Lý
 */
class adminController {
    
    public function __construct() {
        // Bảo vệ toàn bộ khu vực admin, yêu cầu phải đăng nhập với quyền admin
        session::requireAdmin();

        // Tự động kiểm tra phân quyền (Access Control Middleware) dựa trên action đang gọi
        if (isset($_GET['url'])) {
            $urlParts = explode('/', rtrim($_GET['url'], '/'));
            $action = $urlParts[1] ?? 'dashboard';

            // Mapping các action với các phân hệ phân quyền tương ứng
            $moduleMapping = [
                // Phân hệ Tiểu thương
                'traders'             => 'trader',
                'trader_add'          => 'trader',
                'trader_edit'         => 'trader',
                'trader_delete'       => 'trader',
                'trader_export_excel' => 'trader',
                'trader_export_pdf'   => 'trader',

                // Phân hệ Sạp chợ & Sơ đồ
                'stalls'              => 'stall',
                'stall_add'           => 'stall',
                'stall_edit'          => 'stall',
                'stall_delete'        => 'stall',
                'map_editor'          => 'stall',
                'map_tree'            => 'stall',

                // Phân hệ Hợp đồng
                'contracts'           => 'contract',
                'contract_add'        => 'contract',
                'contract_edit'       => 'contract',
                'contract_delete'     => 'contract',

                // Phân hệ Tài chính & Dịch vụ
                'utilities'           => 'finance',
                'utility_add'         => 'finance',
                'utility_edit'        => 'finance',
                'bills'               => 'finance',
                'bill_add'            => 'finance',
                'bill_detail'         => 'finance',
                'transactions'        => 'finance',
                'transaction_add'     => 'finance',

                // Phân hệ An toàn vệ sinh thực phẩm
                'foodsafety'          => 'foodsafety',
                'foodsafety_add'      => 'foodsafety',
                'foodsafety_edit'     => 'foodsafety',
            ];

            if (isset($moduleMapping[$action])) {
                marketService::requireModuleAccess($moduleMapping[$action]);
            }
        }
    }

    public function dashboard() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $actorCode = session::get('actor_code');
        
        // Nếu là super_market hoặc admin_market và chưa chọn chợ cụ thể (active_market_id == 0 hoặc chưa có)
        // hoặc người dùng click xem trang tổng quan (?type=all)
        $viewingMainDashboard = isset($_GET['type']) && $_GET['type'] === 'all';
        $activeMarketId = session::get('active_market_id');
        
        if (($actorCode === 'super_market' || $actorCode === 'admin_market') && (!$activeMarketId || $viewingMainDashboard)) {
            // Thiết lập active_market_id = 0 biểu thị đang ở Trang tổng
            session::set('active_market_id', 0);
            
            $db = database::getInstance();
            $accessibleMarketIds = marketService::getAccessibleMarketIds();
            
            if (empty($accessibleMarketIds)) {
                $this->view('backend/dashboard/main_dashboard', [
                    'title' => 'Trang Tổng Quan Hợp Nhất',
                    'markets' => [],
                    'stats' => [
                        'total_markets' => 0,
                        'total_stalls' => 0,
                        'rented_stalls' => 0,
                        'occupancy_rate' => 0,
                        'total_traders' => 0,
                        'total_revenue' => 0
                    ]
                ]);
                return;
            }
            
            $marketIdsStr = implode(',', $accessibleMarketIds);
            
            // 1. Tổng số chợ
            $totalMarkets = count($accessibleMarketIds);
            
            // 2. Tổng số sạp & Số sạp đã thuê (Stalls join Areas)
            $stallStats = $db->selectOne("
                SELECT COUNT(*) as total_stalls,
                       SUM(CASE WHEN ss.code = 'rented' THEN 1 ELSE 0 END) as rented_stalls
                FROM stalls s
                JOIN areas a ON s.area_id = a.id
                JOIN system_statuses ss ON s.status_id = ss.id
                WHERE a.market_id IN ($marketIdsStr) AND ss.code != '99'
            ");
            
            $totalStalls = (int)($stallStats['total_stalls'] ?? 0);
            $rentedStalls = (int)($stallStats['rented_stalls'] ?? 0);
            $occupancyRate = $totalStalls > 0 ? round(($rentedStalls / $totalStalls) * 100) : 0;
            
            // 3. Tổng số tiểu thương đang hoạt động (Traders join Contracts join Stalls join Areas)
            $traderStats = $db->selectOne("
                SELECT COUNT(DISTINCT t.id) as total_traders
                FROM traders t
                JOIN contracts c ON c.trader_id = t.id
                JOIN stalls s ON c.stall_id = s.id
                JOIN areas a ON s.area_id = a.id
                JOIN system_statuses cs ON c.status_id = cs.id
                WHERE a.market_id IN ($marketIdsStr) AND cs.code = 'active'
            ");
            $totalTraders = (int)($traderStats['total_traders'] ?? 0);
            
            // 4. Doanh thu tổng hợp tháng này (Bills join Contracts join Stalls join Areas)
            $revenueStats = $db->selectOne("
                SELECT SUM(b.total_amount) as total_revenue
                FROM bills b
                JOIN contracts c ON b.contract_id = c.id
                JOIN stalls s ON c.stall_id = s.id
                JOIN areas a ON s.area_id = a.id
                WHERE a.market_id IN ($marketIdsStr) 
                  AND b.status = 'paid'
                  AND DATE_FORMAT(b.invoice_date, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')
            ");
            $totalRevenue = (float)($revenueStats['total_revenue'] ?? 0);
            
            // 5. Thống kê từng chợ để vẽ danh sách Grid
            $marketsData = $db->select("
                SELECT m.id, m.name, m.code,
                       (SELECT COUNT(*) FROM stalls s JOIN areas a ON s.area_id = a.id WHERE a.market_id = m.id) as total_stalls,
                       (SELECT COUNT(*) FROM stalls s JOIN areas a ON s.area_id = a.id JOIN system_statuses ss ON s.status_id = ss.id WHERE a.market_id = m.id AND ss.code = 'rented') as rented_stalls,
                       (SELECT SUM(b.total_amount) FROM bills b JOIN contracts c ON b.contract_id = c.id JOIN stalls s ON c.stall_id = s.id JOIN areas a ON s.area_id = a.id WHERE a.market_id = m.id AND b.status = 'paid' AND DATE_FORMAT(b.invoice_date, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')) as monthly_revenue
                FROM markets m
                WHERE m.id IN ($marketIdsStr) AND m.status_code = 'active'
            ");
            
            $this->view('backend/dashboard/main_dashboard', [
                'title' => 'Trang Tổng Quan Hợp Nhất',
                'markets' => $marketsData,
                'stats' => [
                    'total_markets' => $totalMarkets,
                    'total_stalls' => $totalStalls,
                    'rented_stalls' => $rentedStalls,
                    'occupancy_rate' => $occupancyRate,
                    'total_traders' => $totalTraders,
                    'total_revenue' => $totalRevenue
                ]
            ]);
            return;
        }
        
        // Nếu đã có active_market_id hoặc là nhân viên thường, hiển thị dashboard chi tiết của chợ đó
        $marketId = marketService::currentMarketId();
        $db = database::getInstance();
        
        $stallStats = $db->selectOne("
            SELECT COUNT(*) as total_stalls,
                   SUM(CASE WHEN ss.code = 'rented' THEN 1 ELSE 0 END) as rented_stalls,
                   SUM(CASE WHEN ss.code = 'empty' THEN 1 ELSE 0 END) as empty_stalls,
                   SUM(CASE WHEN ss.code = 'repairing' THEN 1 ELSE 0 END) as repairing_stalls
            FROM stalls s
            JOIN areas a ON s.area_id = a.id
            JOIN system_statuses ss ON s.status_id = ss.id
            WHERE a.market_id = :market_id AND ss.code != '99'
        ", ['market_id' => $marketId]);
        
        $traderStats = $db->selectOne("
            SELECT COUNT(DISTINCT t.id) as total_traders
            FROM traders t
            JOIN contracts c ON c.trader_id = t.id
            JOIN stalls s ON c.stall_id = s.id
            JOIN areas a ON s.area_id = a.id
            JOIN system_statuses cs ON c.status_id = cs.id
            WHERE a.market_id = :market_id AND cs.code = 'active'
        ", ['market_id' => $marketId]);
        
        $revenueStats = $db->selectOne("
            SELECT SUM(b.total_amount) as total_revenue
            FROM bills b
            JOIN contracts c ON b.contract_id = c.id
            JOIN stalls s ON c.stall_id = s.id
            JOIN areas a ON s.area_id = a.id
            WHERE a.market_id = :market_id 
              AND b.status = 'paid'
              AND DATE_FORMAT(b.invoice_date, '%Y-%m') = DATE_FORMAT(CURRENT_DATE(), '%Y-%m')
        ", ['market_id' => $marketId]);

        $stats = [
            'total_stalls' => (int)($stallStats['total_stalls'] ?? 0),
            'rented_stalls' => (int)($stallStats['rented_stalls'] ?? 0),
            'empty_stalls' => (int)($stallStats['empty_stalls'] ?? 0),
            'repairing_stalls' => (int)($stallStats['repairing_stalls'] ?? 0),
            'total_traders' => (int)($traderStats['total_traders'] ?? 0),
            'revenue_this_month' => (float)($revenueStats['total_revenue'] ?? 0),
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
        $areaId = $_GET['area_id'] ?? '';
        $status = $_GET['status'] ?? '';
        $search = $_GET['q'] ?? '';

        $stallModel = new stallModel();
        
        $stalls = [];
        $areas = [];
        $statuses = [];
        $stats = [
            'total' => 0,
            'rented' => 0,
            'empty' => 0,
            'repairing' => 0,
            'locked' => 0
        ];

        try {
            $stalls = $stallModel->getAll($areaId ?: null, $status ?: null, $search ?: null);
            $areas = $stallModel->getAreas();
            $statuses = $stallModel->getStallStatuses();

            // Lấy toàn bộ sạp để tính thống kê
            $allStalls = $stallModel->getAll();
            $stats['total'] = count($allStalls);
            foreach ($allStalls as $s) {
                if ($s['status'] === 'rented') $stats['rented']++;
                elseif ($s['status'] === 'empty') $stats['empty']++;
                elseif ($s['status'] === 'repairing') $stats['repairing']++;
                elseif ($s['status'] === 'locked') $stats['locked']++;
            }
        } catch (Exception $e) {
            error_log('[stalls] EXCEPTION: ' . $e->getMessage());
        }

        $this->view('backend/stall/index', [
            'title' => 'Quản Lý Sạp Chợ',
            'stalls' => $stalls,
            'areas' => $areas,
            'statuses' => $statuses,
            'stats' => $stats,
            'search' => $search,
            'area_filter' => $areaId,
            'status_filter' => $status
        ]);
    }

    /**
     * Phân hệ Hợp đồng thuê sạp
     */
    public function contracts() {
        $status = $_GET['status'] ?? '';
        $search = $_GET['q'] ?? '';
        $contractModel = new contractModel();
        
        $contracts = [];
        $statuses = [];
        
        try {
            $contracts = $contractModel->getAll($status ?: null, $search ?: null);
            $statuses = $contractModel->getContractStatuses();
        } catch (Exception $e) {
            error_log('[contracts] EXCEPTION: ' . $e->getMessage());
        }

        $this->view('backend/contract/index', [
            'title' => 'Hợp Đồng Thuê Sạp',
            'contracts' => $contracts,
            'statuses' => $statuses,
            'status_filter' => $status,
            'search' => $search
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

    public function foodsafety() {
        $foodsafetyModel = new foodsafetyModel();
        
        $certificates = [];
        $statuses = [];

        try {
            // Tự động cập nhật trạng thái hết hạn trước khi hiển thị
            $foodsafetyModel->autoUpdateExpiryStatus();
            
            $certificates = $foodsafetyModel->getCertificates();
            $statuses = $foodsafetyModel->getAttpStatuses();
        } catch (Exception $e) {
            error_log('[foodsafety] EXCEPTION: ' . $e->getMessage());
        }

        $this->view('backend/foodsafety/index', [
            'title' => 'An Toàn Thực Phẩm',
            'certificates' => $certificates,
            'statuses' => $statuses
        ]);
    }

    /**
     * Phân hệ Quản lý tài khoản & phân quyền
     */
    public function users() {
        $userModel = new userModel();
        $users = $userModel->getAll();

        $this->view('backend/user/index', [
            'title' => 'Tài Khoản & Phân Quyền',
            'users' => $users
        ]);
    }

    /**
     * Thêm Sạp chợ mới (chỉ GET - hiển thị form)
     */
    public function stall_add() {
        $stallModel = new stallModel();
        $categoryModel = new categoryModel();
        $areas = [];
        $statuses = [];
        $stallTypes = [];

        try {
            $areas = $stallModel->getAreas();
            $statuses = $stallModel->getStallStatuses();
            $stallTypes = $categoryModel->getItems('stall_type');
        } catch (Exception $e) {
            error_log('[stall_add] EXCEPTION: ' . $e->getMessage());
        }

        $this->view('backend/stall/add', [
            'title'      => 'Khai Báo Sạp Chợ Mới',
            'data'       => ['area_id' => '', 'stall_code' => '', 'stall_type_id' => '', 'area_size' => '', 'base_price' => '', 'status_id' => 3],
            'areas'      => $areas,
            'statuses'   => $statuses,
            'stallTypes' => $stallTypes
        ]);
    }

    /**
     * Chỉnh sửa Sạp chợ (chỉ GET - hiển thị form)
     */
    public function stall_edit($id) {
        if (!$id) {
            header('Location: ' . BASE_URL . 'admin/stalls');
            exit();
        }

        $stallModel = new stallModel();
        $categoryModel = new categoryModel();
        $stall = null;
        $areas = [];
        $statuses = [];
        $stallTypes = [];

        try {
            $stall = $stallModel->getById($id);
            if (!$stall) {
                throw new Exception('Không tìm thấy sạp chợ yêu cầu.');
            }
            $areas = $stallModel->getAreas();
            $statuses = $stallModel->getStallStatuses();
            $stallTypes = $categoryModel->getItems('stall_type');
        } catch (Exception $e) {
            session::set('error_message', $e->getMessage());
            header('Location: ' . BASE_URL . 'admin/stalls');
            exit();
        }

        $this->view('backend/stall/edit', [
            'title'      => 'Chỉnh Sửa Sạp Chợ',
            'stall'      => $stall,
            'areas'      => $areas,
            'statuses'   => $statuses,
            'stallTypes' => $stallTypes
        ]);
    }

    /**
     * Lập Hợp đồng mới
     */
    public function contract_add() {
        $traders = [];
        $emptyStalls = [];
        
        try {
            $traderModel = new traderModel();
            // Lấy tiểu thương hoạt động
            $traders = $traderModel->getAllTraders('', '', 'active');
            
            $stallModel = new stallModel();
            // Lấy các sạp trống
            $emptyStalls = $stallModel->getAll(null, 'empty');
        } catch (Exception $e) {
            error_log('[contract_add] EXCEPTION: ' . $e->getMessage());
        }

        $this->view('backend/contract/add', [
            'title' => 'Lập Hợp Đồng Thuê Sạp',
            'traders' => $traders,
            'emptyStalls' => $emptyStalls
        ]);
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
     * Lập hóa đơn mới (Mockup Form / View Action)
     */
    public function bill_add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            session::set('success_message', 'Lập hóa đơn mới thành công!');
            header('Location: ' . BASE_URL . 'admin/bills');
            exit();
        }

        $contracts = [];
        try {
            $contractModel = new contractModel();
            $contracts = $contractModel->getAll();
        } catch (Exception $e) {
            $contracts = [
                ['id' => 1, 'contract_code' => 'HĐ-2026-0001', 'trader_name' => 'Nguyễn Thị Thu Hà', 'stall_code' => 'SẠP-A01'],
                ['id' => 2, 'contract_code' => 'HĐ-2026-0002', 'trader_name' => 'Trần Văn Hoàng', 'stall_code' => 'SẠP-B01'],
            ];
        }

        $this->view('backend/finance/bill_add', [
            'title' => 'Lập Hóa Đơn Mới',
            'contracts' => $contracts
        ]);
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

    public function foodsafety_add() {
        $traders = [];
        $documentTypes = [];
        try {
            $traderModel = new traderModel();
            // Lấy danh sách tiểu thương đang hoạt động
            $traders = $traderModel->getAllTraders(null, null, 'active');

            $categoryModel = new categoryModel();
            $documentTypes = $categoryModel->getItems('document_type');
        } catch (Exception $e) {
            error_log('[foodsafety_add] EXCEPTION: ' . $e->getMessage());
        }

        $this->view('backend/foodsafety/add', [
            'title' => 'Khai Báo Chứng Nhận ATTP',
            'traders' => $traders,
            'documentTypes' => $documentTypes
        ]);
    }

    public function foodsafety_edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ' . BASE_URL . 'admin/foodsafety');
            exit();
        }

        $certificate = null;
        $traders = [];
        $documentTypes = [];
        try {
            $foodsafetyModel = new foodsafetyModel();
            $certificate = $foodsafetyModel->getById($id);
            if (!$certificate) {
                header('Location: ' . BASE_URL . 'admin/foodsafety');
                exit();
            }

            $traderModel = new traderModel();
            // Lấy danh sách tiểu thương đang hoạt động
            $traders = $traderModel->getAllTraders('', '', 'active');

            $categoryModel = new categoryModel();
            $documentTypes = $categoryModel->getItems('document_type');
        } catch (Exception $e) {
            error_log('[foodsafety_edit] EXCEPTION: ' . $e->getMessage());
        }

        $this->view('backend/foodsafety/edit', [
            'title' => 'Chỉnh Sửa Chứng Nhận ATTP',
            'certificate' => $certificate,
            'traders' => $traders,
            'documentTypes' => $documentTypes
        ]);
    }

    /**
     * Trang thiết lập sơ đồ chợ tương tác dành cho Admin
     */
    public function map_editor() {
        $stalls = [];
        $unmappedStalls = [];
        try {
            $mapModel = new mapModel();
            $unmappedStalls = $mapModel->getUnmappedStalls();
            
            $db = database::getInstance();
            $stalls = $db->select("SELECT s.id, s.stall_code, s.stall_type, s.area_size, s.base_price, 
                                          ss.status_name, ss.code AS status_code, sc.color_class,
                                          a.area_name, a.block, a.lot,
                                          t.fullname AS trader_name, t.phone AS trader_phone,
                                          con.contract_number, con.end_date AS contract_end_date
                                   FROM stalls s 
                                   LEFT JOIN areas a ON s.area_id = a.id
                                   JOIN system_statuses ss ON s.status_id = ss.id 
                                   LEFT JOIN status_colors sc ON ss.color_id = sc.id
                                   LEFT JOIN contracts con ON con.stall_id = s.id AND con.status_id = (SELECT id FROM system_statuses WHERE domain = 'contract' AND code = 'active')
                                   LEFT JOIN traders t ON con.trader_id = t.id
                                   WHERE ss.code != '99' 
                                   ORDER BY s.stall_code ASC");
        } catch (Exception $e) {
            error_log('[map_editor] EXCEPTION: ' . $e->getMessage());
        }

        $this->view('backend/map/editor', [
            'title' => 'Thiết lập Sơ đồ chợ tương tác',
            'unmappedStalls' => $unmappedStalls,
            'stalls' => $stalls
        ]);
    }

    /**
     * Trang sơ đồ cây sạp chợ tương tác dành cho Admin
     */
    public function map_tree() {
        $this->view('backend/map/tree', [
            'title' => 'Sơ đồ Cây sạp chợ tương tác'
        ]);
    }

    /**
     * Tạo tài khoản nhân viên mới
     */
    public function user_add() {
        if (!marketService::isSuperAdmin() && !marketService::isAdminMarket()) {
            marketService::requireModuleAccess('user_management_restricted');
        }

        $db = database::getInstance();
        $error = '';
        $data = [
            'username' => '',
            'fullname' => '',
            'email' => '',
            'role' => 'admin',
            'status' => 'active'
        ];

        // Lấy danh sách chợ có thể phân quyền
        if (marketService::isSuperAdmin()) {
            $marketsList = $db->select("SELECT id, name FROM markets WHERE status_code = 'active' ORDER BY name ASC");
        } else {
            $managerUserId = session::get('user_id');
            $marketsList = $db->select("
                SELECT m.id, m.name 
                FROM user_markets um
                JOIN markets m ON um.market_id = m.id
                WHERE um.user_id = :manager_id AND m.status_code = 'active'
                ORDER BY m.name ASC
            ", ['manager_id' => $managerUserId]);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data['username'] = trim($_POST['username'] ?? '');
            $data['fullname'] = trim($_POST['fullname'] ?? '');
            $data['email'] = trim($_POST['email'] ?? '');
            $data['password'] = $_POST['password'] ?? '';
            $data['role'] = $_POST['role'] ?? 'admin'; // actor_code
            $data['status'] = $_POST['status'] ?? 'active';
            $checkedMarkets = $_POST['markets'] ?? [];

            // Admin Market chỉ có quyền tạo Nhân viên thường (admin)
            if (marketService::isAdminMarket() && $data['role'] !== 'admin') {
                $data['role'] = 'admin';
            }

            $is_active = ($data['status'] === 'active') ? 1 : 0;

            $validator = new validator();
            $validator->required('username', $data['username'], 'Vui lòng nhập tên đăng nhập.')
                       ->required('password', $data['password'], 'Vui lòng nhập mật khẩu.')
                       ->required('fullname', $data['fullname'], 'Vui lòng nhập họ tên.')
                       ->email('email', $data['email'], 'Email không đúng định dạng.');

            if ($validator->isValid()) {
                $userModel = new userModel();
                if ($userModel->getByUsername($data['username'])) {
                    $error = 'Tên đăng nhập đã tồn tại.';
                } else if ($data['email'] && $userModel->getByEmail($data['email'])) {
                    $error = 'Email này đã được đăng ký cho tài khoản khác.';
                } else {
                    try {
                        // Lấy actor_id tương ứng
                        $actor = $db->selectOne("SELECT id FROM system_actors WHERE actor_code = :code", ['code' => $data['role']]);
                        $actorId = $actor ? (int)$actor['id'] : 3;

                        $newUserId = $userModel->create([
                            'username' => $data['username'],
                            'password' => $data['password'],
                            'fullname' => $data['fullname'],
                            'email' => $data['email'],
                            'user_group' => ($data['role'] === 'super_market') ? 1 : 2,
                            'actor_id' => $actorId,
                            'is_active' => $is_active
                        ]);

                        // Lưu liên kết chợ trong user_markets
                        $roleMapping = [
                            'super_market' => 1,
                            'admin_market' => 4,
                            'admin' => 2
                        ];
                        $roleId = $roleMapping[$data['role']] ?? 2;

                        if ($data['role'] !== 'super_market') {
                            foreach ($checkedMarkets as $mId) {
                                // Xác minh chợ nằm trong quyền quản lý của admin_market
                                if (marketService::isAdminMarket() && !in_array((int)$mId, array_column($marketsList, 'id'))) {
                                    continue;
                                }
                                $db->query("
                                    INSERT INTO user_markets (user_id, market_id, role_id)
                                    VALUES (:user_id, :market_id, :role_id)
                                ", [
                                    'user_id' => $newUserId,
                                    'market_id' => $mId,
                                    'role_id' => $roleId
                                ]);
                            }
                        }

                        session::set('success_message', 'Tạo tài khoản thành công!');
                        header('Location: ' . BASE_URL . 'admin/users');
                        exit();
                    } catch (Exception $e) {
                        $error = 'Lỗi hệ thống: ' . $e->getMessage();
                    }
                }
            } else {
                $errors = $validator->getErrors();
                $error = reset($errors);
            }
        }

        $this->view('backend/user/add', [
            'title' => 'Tạo Tài Khoản Nhân Viên',
            'data' => $data,
            'marketsList' => $marketsList,
            'error' => $error
        ]);
    }

    /**
     * Chỉnh sửa tài khoản nhân viên
     */
    public function user_edit($id = null) {
        if (!marketService::isSuperAdmin() && !marketService::isAdminMarket()) {
            marketService::requireModuleAccess('user_management_restricted');
        }

        if (!$id) {
            header('Location: ' . BASE_URL . 'admin/users');
            exit();
        }

        $db = database::getInstance();
        $userModel = new userModel();
        
        // Load user joined with actor info
        $user = $db->selectOne("
            SELECT u.*, sa.actor_code 
            FROM users u 
            LEFT JOIN system_actors sa ON u.actor_id = sa.id 
            WHERE u.id = :id
        ", ['id' => $id]);

        if (!$user) {
            session::set('error_message', 'Không tìm thấy tài khoản nhân viên.');
            header('Location: ' . BASE_URL . 'admin/users');
            exit();
        }

        // Lấy danh sách chợ có thể phân quyền
        if (marketService::isSuperAdmin()) {
            $marketsList = $db->select("SELECT id, name FROM markets WHERE status_code = 'active' ORDER BY name ASC");
        } else {
            $managerUserId = session::get('user_id');
            $marketsList = $db->select("
                SELECT m.id, m.name 
                FROM user_markets um
                JOIN markets m ON um.market_id = m.id
                WHERE um.user_id = :manager_id AND m.status_code = 'active'
                ORDER BY m.name ASC
            ", ['manager_id' => $managerUserId]);

            // Bảo mật: admin_market chỉ được sửa tài khoản admin thường có liên kết với chợ của mình quản lý
            $isAssociated = $db->selectOne("
                SELECT 1 FROM user_markets 
                WHERE user_id = :target_id AND market_id IN (
                    SELECT market_id FROM user_markets WHERE user_id = :manager_id
                )
            ", ['target_id' => $id, 'manager_id' => $managerUserId]);

            if (!$isAssociated && $user['actor_code'] !== 'admin') {
                session::set('error_message', 'Bạn không có quyền chỉnh sửa tài khoản này.');
                header('Location: ' . BASE_URL . 'admin/users');
                exit();
            }
        }

        // Lấy các chợ đã được gán hiện tại của user này
        $assignedMarkets = array_column($db->select("SELECT market_id FROM user_markets WHERE user_id = :id", ['id' => $id]), 'market_id');

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullname = trim($_POST['fullname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? $user['actor_code']; // actor_code
            $status = $_POST['status'] ?? ($user['is_active'] ? 'active' : 'inactive');
            $checkedMarkets = $_POST['markets'] ?? [];

            // Admin Market chỉ được giữ nguyên vai trò 'admin' hoặc ép về 'admin'
            if (marketService::isAdminMarket()) {
                $role = 'admin';
            }

            $is_active = ($status === 'active') ? 1 : 0;

            $validator = new validator();
            $validator->required('fullname', $fullname, 'Vui lòng nhập họ tên.')
                       ->email('email', $email, 'Email không đúng định dạng.');

            if ($validator->isValid()) {
                $dupUser = $userModel->getByEmail($email);
                if ($dupUser && $dupUser['id'] != $id) {
                    $error = 'Email này đã được sử dụng bởi một tài khoản khác.';
                } else {
                    try {
                        // Lấy actor_id tương ứng
                        $actor = $db->selectOne("SELECT id FROM system_actors WHERE actor_code = :code", ['code' => $role]);
                        $actorId = $actor ? (int)$actor['id'] : 3;

                        $userModel->update($id, [
                            'fullname' => $fullname,
                            'email' => $email,
                            'user_group' => ($role === 'super_market') ? 1 : 2,
                            'actor_id' => $actorId,
                            'is_active' => $is_active
                        ]);

                        if (!empty($password)) {
                            $userModel->updatePassword($id, $password);
                        }

                        // Cập nhật gán chợ trong user_markets
                        // Xóa các liên kết chợ cũ trong phạm vi chợ quản lý
                        $marketsScopeIds = array_column($marketsList, 'id');
                        if (!empty($marketsScopeIds)) {
                            $placeholders = implode(',', array_map(function($i) { return ":m{$i}"; }, range(0, count($marketsScopeIds) - 1)));
                            $deleteParams = ['id' => $id];
                            foreach ($marketsScopeIds as $idx => $mId) {
                                $deleteParams["m{$idx}"] = $mId;
                            }
                            $db->query("DELETE FROM user_markets WHERE user_id = :id AND market_id IN ($placeholders)", $deleteParams);
                        }

                        // Thêm các liên kết chợ mới
                        $roleMapping = [
                            'super_market' => 1,
                            'admin_market' => 4,
                            'admin' => 2
                        ];
                        $roleId = $roleMapping[$role] ?? 2;

                        if ($role !== 'super_market') {
                            foreach ($checkedMarkets as $mId) {
                                if (in_array((int)$mId, $marketsScopeIds)) {
                                    $db->query("
                                        INSERT INTO user_markets (user_id, market_id, role_id)
                                        VALUES (:user_id, :market_id, :role_id)
                                    ", [
                                        'user_id' => $id,
                                        'market_id' => $mId,
                                        'role_id' => $roleId
                                    ]);
                                }
                            }
                        }

                        session::set('success_message', 'Cập nhật tài khoản nhân viên thành công!');
                        header('Location: ' . BASE_URL . 'admin/users');
                        exit();
                    } catch (Exception $e) {
                        $error = 'Lỗi hệ thống: ' . $e->getMessage();
                    }
                }
            } else {
                $errors = $validator->getErrors();
                $error = reset($errors);
            }

            // Đồng bộ dữ liệu để hiển thị lại nếu lỗi
            $user['fullname'] = $fullname;
            $user['email'] = $email;
            $user['actor_code'] = $role;
            $user['is_active'] = $is_active;
            $assignedMarkets = $checkedMarkets;
        }

        $this->view('backend/user/edit', [
            'title' => 'Chỉnh Sửa Tài Khoản Nhân Viên',
            'user' => $user,
            'marketsList' => $marketsList,
            'assignedMarkets' => $assignedMarkets,
            'error' => $error
        ]);
    }

    /**
     * Khóa/Mở khóa tài khoản nhân viên (AJAX POST)
     */
    public function user_toggle_status($id = null) {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $id) {
            $userModel = new userModel();
            $user = $userModel->getById($id);
            if ($user) {
                if ($user['id'] == session::get('user_id')) {
                    echo json_encode(['success' => false, 'message' => 'Bạn không thể tự khóa tài khoản của chính mình!']);
                    exit();
                }

                $newStatus = $user['is_active'] == 1 ? 0 : 1;
                $userModel->update($id, [
                    'fullname' => $user['fullname'],
                    'email' => $user['email'],
                    'user_group' => $user['user_group'],
                    'is_active' => $newStatus
                ]);

                echo json_encode(['success' => true, 'new_status' => $newStatus]);
                exit();
            }
        }
        echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
        exit();
    }

    /**
     * Phân hệ Quản lý Tiểu thương
     */
    public function traders() {
        $traderModel = new traderModel();
        
        $search = $_GET['q'] ?? '';
        $business_line = $_GET['business_line'] ?? '';
        $status = $_GET['status'] ?? '';
        
        $traders = [];
        $business_lines = [];
        $statuses = [];
        
        try {
            // Lấy danh sách tiểu thương theo bộ lọc
            $traders = $traderModel->getAllTraders($search, $business_line, $status);
            $statuses = $traderModel->getTraderStatuses();
            
            // Lấy danh sách các ngành hàng từ DB
            $business_lines = $traderModel->getBusinessLines();
        } catch (Exception $e) {
            // Fallback khi lỗi cơ sở dữ liệu
            error_log('[traders] EXCEPTION: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            $traders = [];
            $business_lines = [];
            $statuses = [];
        }

        $this->view('backend/trader/index', [
            'title' => 'Quản Lý Tiểu Thương',
            'traders' => $traders,
            'business_lines' => $business_lines,
            'statuses' => $statuses,
            'search' => $search,
            'business_line_filter' => $business_line,
            'status_filter' => $status
        ]);
    }

    /**
     * Thêm tiểu thương mới (chỉ GET - hiển thị form)
     */
    public function trader_add() {
        $statuses = [];
        $business_lines = [];
        try {
            $traderModel = new traderModel();
            $statuses = $traderModel->getTraderStatuses();
            $business_lines = $traderModel->getBusinessLines();
        } catch (Exception $e) {}

        $this->view('backend/trader/add', [
            'title'          => 'Thêm Tiểu Thương Mới',
            'data'           => ['trader_code' => '', 'fullname' => '', 'phone' => '', 'cccd' => '', 'address' => '', 'business_line_id' => '', 'description' => '', 'status_id' => 7],
            'statuses'       => $statuses,
            'business_lines' => $business_lines
        ]);
    }

    /**
     * Sửa thông tin tiểu thương (chỉ GET - hiển thị form)
     */
    public function trader_edit($id) {
        if (!$id) {
            header('Location: ' . BASE_URL . 'admin/traders');
            exit();
        }

        $traderModel = new traderModel();

        try {
            $trader = $traderModel->getTraderById($id);
            if (!$trader) {
                throw new Exception(message::error('not_found', 'trader'));
            }
        } catch (Exception $e) {
            session::set('error_message', $e->getMessage());
            header('Location: ' . BASE_URL . 'admin/traders');
            exit();
        }

        $statuses = [];
        $business_lines = [];
        try {
            $statuses = $traderModel->getTraderStatuses();
            $business_lines = $traderModel->getBusinessLines();
        } catch (Exception $e) {}

        $this->view('backend/trader/edit', [
            'title'          => 'Chỉnh Sửa Tiểu Thương',
            'trader'         => $trader,
            'statuses'       => $statuses,
            'business_lines' => $business_lines
        ]);
    }

    /**
     * Xuất danh sách tiểu thương ra file Excel (.xlsx thật sự)
     */
    public function trader_export_excel() {
        $traderModel = new traderModel();
        $search = $_GET['q'] ?? '';
        $business_line = $_GET['business_line'] ?? '';
        $status = $_GET['status'] ?? '';

        try {
            $traders = $traderModel->getAllTraders($search, $business_line, $status);
        } catch (Exception $e) {
            $traders = [];
        }

        $headers = ['Mã tiểu thương', 'Họ và tên', 'Số điện thoại', 'Số CCCD', 'Địa chỉ', 'Ngành hàng', 'Trạng thái', 'Công nợ (đ)'];
        $rows = [];
        foreach ($traders as $t) {
            $rows[] = [
                $t['trader_code'],
                $t['fullname'],
                $t['phone'],
                $t['cccd'],
                $t['address'] ?? '',
                $t['business_line_name'] ?? 'Chưa cập nhật',
                $t['status_name'] ?? 'Không rõ',
                (int)($t['total_debt'] ?? 0)
            ];
        }

        SimpleXlsx::download('danh_sach_tieu_thuong.xlsx', $headers, $rows);
    }

    /**
     * Xuất danh sách tiểu thương ra file PDF (tải xuống trực tiếp)
     */
    public function trader_export_pdf() {
        $traderModel = new traderModel();
        $search = $_GET['q'] ?? '';
        $business_line = $_GET['business_line'] ?? '';
        $status = $_GET['status'] ?? '';

        try {
            $traders = $traderModel->getAllTraders($search, $business_line, $status);
        } catch (Exception $e) {
            $traders = [];
        }

        // Mô tả bộ lọc
        $filterParts = [];
        if ($search) $filterParts[] = 'Từ khóa: ' . $search;
        if ($status) $filterParts[] = 'Trạng thái: ' . $status;
        if ($business_line && !empty($traders)) {
            $filterParts[] = 'Ngành hàng: ' . ($traders[0]['business_line_name'] ?? $business_line);
        }
        $filterDesc = implode(' | ', $filterParts);

        // Sinh nội dung HTML cho PDF
        ob_start();
        $title = 'Báo cáo danh sách tiểu thương';
        require DIR_TEMPLATE . '/backend/trader/print.php';
        $html = ob_get_clean();

        // Nạp mPDF từ vendor của dự án khác trên cùng máy chủ
        $autoload = 'D:/xampp/htdocs/vieclam.vn/application/vendor/autoload.php';
        if (!file_exists($autoload)) {
            // Fallback: mở trang HTML nếu mPDF không có
            header('Content-Type: text/html; charset=utf-8');
            echo $html;
            exit();
        }

        require_once $autoload;

        // mPDF cũ có Deprecated warning trên PHP 8.x — tắt tạm để không in ra output
        // ponytail: bỏ dòng này khi nâng cấp lên mPDF 8.x mới hơn
        $prevErrLevel = error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
        ob_start(); // bắt bất kỳ output thừa nào (whitespace, warning leak)

        $mpdf = new \Mpdf\Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4-L',   // A4 ngang để bảng không bị chật
            'margin_left'   => 12,
            'margin_right'  => 12,
            'margin_top'    => 14,
            'margin_bottom' => 14,
        ]);
        $mpdf->SetTitle('Báo cáo tiểu thương');
        $mpdf->WriteHTML($html);

        ob_end_clean(); // xóa hết output cũ trước khi gửi binary PDF
        error_reporting($prevErrLevel);

        $mpdf->Output('danh_sach_tieu_thuong.pdf', \Mpdf\Output\Destination::DOWNLOAD);
        exit();
    }



    /**
     * Đổi mật khẩu cá nhân
     */
    public function change_password() {
        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $oldPassword = $_POST['old_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $validator = new validator();
            $validator->required('old_password', $oldPassword, 'Vui lòng nhập mật khẩu hiện tại.')
                      ->required('new_password', $newPassword, 'Vui lòng nhập mật khẩu mới.')
                      ->minLength('new_password', $newPassword, 6, 'Mật khẩu mới phải từ 6 ký tự trở lên.')
                      ->required('confirm_password', $confirmPassword, 'Vui lòng xác nhận mật khẩu mới.')
                      ->matches('confirm_password', $confirmPassword, $newPassword, 'Xác nhận mật khẩu mới không khớp.');

            if ($validator->isValid()) {
                $userModel = new userModel();
                $userId = session::get('user_id');
                $user = $userModel->getByUsername(session::get('username'));

                if ($user && password_verify($oldPassword, $user['password'])) {
                    try {
                        $userModel->updatePassword($userId, $newPassword);
                        $success = 'Đổi mật khẩu thành công!';
                    } catch (Exception $e) {
                        $error = 'Lỗi hệ thống: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Mật khẩu hiện tại không chính xác.';
                }
            } else {
                $errors = $validator->getErrors();
                $error = reset($errors);
            }
        }

        $this->view('backend/auth/change_password', [
            'title' => 'Đổi Mật Khẩu',
            'error' => $error,
            'success' => $success
        ]);
    }
    /**
     * Quản lý các danh mục hệ thống (Khu vực, Loại sạp, Ngành hàng, Loại giấy tờ)
     */
    public function categories() {
        $categoryModel = new categoryModel();

        // Chuẩn bị dữ liệu ban đầu cho các danh mục
        $areas = $categoryModel->getItems('area');
        $stallTypes = $categoryModel->getItems('stall_type');
        $businessLines = $categoryModel->getItems('business_line');
        $documentTypes = $categoryModel->getItems('document_type');

        $this->view('backend/category/index', [
            'title'         => 'Quản Lý Danh Mục',
            'areas'         => $areas,
            'stallTypes'    => $stallTypes,
            'businessLines' => $businessLines,
            'documentTypes' => $documentTypes
        ]);
    }

    /**
     * Giao diện phân quyền phân hệ cho nhân viên (chỉ dành cho admin_market và super_market)
     */
    public function permissions() {
        if (!marketService::isAdminMarket() && !marketService::isSuperAdmin()) {
            marketService::requireModuleAccess('permissions_management_restricted');
        }

        $db = database::getInstance();
        $managerId = session::get('user_id');

        // Lấy danh sách chợ mà manager này quản lý
        if (marketService::isSuperAdmin()) {
            $managedMarkets = $db->select("SELECT id, name FROM markets WHERE status_code = 'active'");
        } else {
            $managedMarkets = $db->select("
                SELECT m.id, m.name 
                FROM user_markets um
                JOIN markets m ON um.market_id = m.id
                WHERE um.user_id = :manager_id AND m.status_code = 'active'
            ", ['manager_id' => $managerId]);
        }

        if (empty($managedMarkets)) {
            $this->view('backend/user/permissions', [
                'title' => 'Phân Quyền Nhân Viên',
                'error' => 'Bạn chưa được gán quản lý chợ nào.',
                'staffList' => [],
                'managedMarkets' => [],
                'permissions' => []
            ]);
            return;
        }

        $marketIds = array_column($managedMarkets, 'id');
        $marketIdsStr = implode(',', $marketIds);

        // Lấy danh sách nhân viên (admin thường) của các chợ này
        $staffList = $db->select("
            SELECT DISTINCT u.id, u.username, u.fullname, u.email 
            FROM users u
            JOIN system_actors sa ON u.actor_id = sa.id
            JOIN user_markets um ON u.id = um.user_id
            WHERE sa.actor_code = 'admin' AND um.market_id IN ($marketIdsStr)
            ORDER BY u.fullname ASC
        ");

        // Lấy toàn bộ quyền hiện tại của các nhân viên này tại các chợ quản lý
        $rawPerms = $db->select("
            SELECT user_id, market_id, module_code 
            FROM user_market_permissions
            WHERE market_id IN ($marketIdsStr)
        ");

        // Group permissions by user_id and market_id for easy lookup in view
        $permissions = [];
        foreach ($rawPerms as $p) {
            $permissions[$p['user_id']][$p['market_id']][$p['module_code']] = true;
        }

        $this->view('backend/user/permissions', [
            'title' => 'Phân Quyền Nhân Viên',
            'staffList' => $staffList,
            'managedMarkets' => $managedMarkets,
            'permissions' => $permissions
        ]);
    }

    /**
     * AJAX API Lưu phân quyền nhân viên (POST)
     */
    public function save_permissions() {
        header('Content-Type: application/json; charset=utf-8');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Phương thức không được hỗ trợ.']);
            exit();
        }

        if (!marketService::isAdminMarket() && !marketService::isSuperAdmin()) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền thực hiện chức năng này.']);
            exit();
        }

        $db = database::getInstance();
        $managerId = session::get('user_id');

        // Lấy danh sách chợ mà manager này quản lý để xác thực dữ liệu gửi lên
        if (marketService::isSuperAdmin()) {
            $managedMarkets = $db->select("SELECT id FROM markets WHERE status_code = 'active'");
        } else {
            $managedMarkets = $db->select("
                SELECT market_id as id FROM user_markets WHERE user_id = :manager_id
            ", ['manager_id' => $managerId]);
        }
        $managedMarketIds = array_column($managedMarkets, 'id');

        $userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
        $marketId = isset($_POST['market_id']) ? (int)$_POST['market_id'] : 0;
        $module = isset($_POST['module']) ? trim($_POST['module']) : '';
        $checked = isset($_POST['checked']) ? (int)$_POST['checked'] : 0;

        if (!$userId || !$marketId || !$module) {
            echo json_encode(['success' => false, 'message' => 'Thiếu tham số hợp lệ.']);
            exit();
        }

        // Kiểm tra xem manager có quyền quản lý chợ này không
        if (!in_array($marketId, $managedMarketIds)) {
            echo json_encode(['success' => false, 'message' => 'Bạn không có quyền quản lý chợ này.']);
            exit();
        }

        // Kiểm tra xem user_id có đúng là Staff (role 'admin') thuộc chợ này không
        $isStaff = $db->selectOne("
            SELECT 1 FROM users u
            JOIN system_actors sa ON u.actor_id = sa.id
            JOIN user_markets um ON u.id = um.user_id
            WHERE u.id = :user_id AND sa.actor_code = 'admin' AND um.market_id = :market_id
        ", ['user_id' => $userId, 'market_id' => $marketId]);

        if (!$isStaff) {
            echo json_encode(['success' => false, 'message' => 'Nhân viên này không thuộc quyền quản lý của bạn tại chợ đã chọn.']);
            exit();
        }

        // Thực hiện cập nhật quyền
        try {
            if ($checked === 1) {
                $db->query("
                    INSERT INTO user_market_permissions (user_id, market_id, module_code)
                    VALUES (:user_id, :market_id, :module_code)
                    ON DUPLICATE KEY UPDATE module_code = :module_code
                ", [
                    'user_id' => $userId,
                    'market_id' => $marketId,
                    'module_code' => $module
                ]);
            } else {
                $db->query("
                    DELETE FROM user_market_permissions 
                    WHERE user_id = :user_id AND market_id = :market_id AND module_code = :module_code
                ", [
                    'user_id' => $userId,
                    'market_id' => $marketId,
                    'module_code' => $module
                ]);
            }

            echo json_encode(['success' => true, 'message' => 'Cập nhật quyền thành công!']);
            exit();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
            exit();
        }
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
