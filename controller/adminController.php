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
                SELECT m.id, m.name, m.market_code AS code,
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
        header("Location: " . BASE_URL . "system/users");
        exit();
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
        header("Location: " . BASE_URL . "system/user_add");
        exit();
    }

    public function user_edit($id = null) {
        header("Location: " . BASE_URL . "system/user_edit/" . $id);
        exit();
    }

    public function user_toggle_status($id = null) {
        header("Location: " . BASE_URL . "system/user_toggle_status/" . $id);
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
        header("Location: " . BASE_URL . "system/permissions");
        exit();
    }

    public function save_permissions() {
        header("Location: " . BASE_URL . "system/save_permissions");
        exit();
    }

    public function markets() {
        header("Location: " . BASE_URL . "system/markets");
        exit();
    }

    public function market_add() {
        header("Location: " . BASE_URL . "system/market_add");
        exit();
    }

    public function market_edit($id) {
        header("Location: " . BASE_URL . "system/market_edit/" . $id);
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
