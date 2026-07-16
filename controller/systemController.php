<?php
/**
 * Controller xử lý các trang quản trị mức hệ thống/tổng hợp (Global System Scope)
 * Chỉ dành cho Quản trị tối cao (super_market) và Quản lý chợ (admin_market)
 */
class systemController {
    
    public function __construct() {
        // Yêu cầu đăng nhập BQL
        session::requireAdmin();
        
        $actorCode = session::get('actor_code');
        if ($actorCode !== 'super_market' && $actorCode !== 'admin_market') {
            header("HTTP/1.1 403 Forbidden");
            echo "<h1>403 Forbidden</h1><p>Bạn không có quyền truy cập hệ thống tổng quan.</p>";
            exit();
        }
    }

    /**
     * Trang tổng quan hợp nhất (Main Dashboard)
     */
    public function dashboard() {
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
        $totalMarkets = count($accessibleMarketIds);
        
        // 1. Tổng số sạp & Số sạp đã thuê (Stalls join Areas)
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
        
        // 2. Tổng số tiểu thương đang hoạt động
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
        
        // 3. Doanh thu tổng hợp tháng này (Bills join Contracts join Stalls join Areas)
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
        
        // 4. Thống kê từng chợ để vẽ danh sách Grid
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
    }

    /**
     * Quản lý Chợ (Chỉ dành cho Super Market)
     */
    public function markets() {
        if (!marketService::isSuperAdmin()) {
            header("HTTP/1.1 403 Forbidden");
            echo "<h1>403 Forbidden</h1><p>Bạn không có quyền truy cập chức năng này.</p>";
            exit();
        }

        $search = $_GET['q'] ?? '';
        $marketModel = new marketModel();
        $markets = $marketModel->getAll($search);

        $this->view('backend/market/index', [
            'title' => 'Quản Lý Danh Sách Chợ',
            'markets' => $markets,
            'search' => $search
        ]);
    }

    /**
     * Thêm chợ mới (Chỉ dành cho Super Market)
     */
    public function market_add() {
        if (!marketService::isSuperAdmin()) {
            header("HTTP/1.1 403 Forbidden");
            echo "<h1>403 Forbidden</h1><p>Bạn không có quyền truy cập chức năng này.</p>";
            exit();
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $code = trim($_POST['market_code'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $manager = trim($_POST['manager_name'] ?? '');
            $status = $_POST['status_code'] ?? 'active';

            if (empty($name) || empty($code)) {
                $error = 'Vui lòng nhập đầy đủ Tên chợ và Mã chợ!';
            } else {
                try {
                    $marketModel = new marketModel();
                    $data = [
                        'market_code' => $code,
                        'name' => $name,
                        'phone' => $phone,
                        'email' => $email,
                        'manager_name' => $manager,
                        'status_code' => $status
                    ];
                    $marketModel->create($data);
                    header("Location: " . BASE_URL . "system/markets");
                    exit();
                } catch (Exception $e) {
                    $error = 'Lỗi hệ thống: ' . $e->getMessage();
                }
            }
        }

        $this->view('backend/market/add', [
            'title' => 'Thêm Chợ Mới',
            'error' => $error,
            'success' => $success
        ]);
    }

    /**
     * Sửa chợ (Chỉ dành cho Super Market)
     */
    public function market_edit($id) {
        if (!marketService::isSuperAdmin()) {
            header("HTTP/1.1 403 Forbidden");
            echo "<h1>403 Forbidden</h1><p>Bạn không có quyền truy cập chức năng này.</p>";
            exit();
        }

        $marketModel = new marketModel();
        $market = $marketModel->getById($id);

        if (!$market) {
            header("Location: " . BASE_URL . "system/markets");
            exit();
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $code = trim($_POST['market_code'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $manager = trim($_POST['manager_name'] ?? '');
            $status = $_POST['status_code'] ?? 'active';

            if (empty($name) || empty($code)) {
                $error = 'Vui lòng nhập đầy đủ Tên chợ và Mã chợ!';
            } else {
                try {
                    $data = [
                        'market_code' => $code,
                        'name' => $name,
                        'phone' => $phone,
                        'email' => $email,
                        'manager_name' => $manager,
                        'status_code' => $status
                    ];
                    $marketModel->update($id, $data);
                    header("Location: " . BASE_URL . "system/markets");
                    exit();
                } catch (Exception $e) {
                    $error = 'Lỗi hệ thống: ' . $e->getMessage();
                }
            }
        }

        $this->view('backend/market/edit', [
            'title' => 'Sửa Thông Tin Chợ',
            'market' => $market,
            'error' => $error,
            'success' => $success
        ]);
    }

    /**
     * Danh sách tài khoản người dùng
     */
    public function users() {
        $db = database::getInstance();
        $search = $_GET['q'] ?? '';
        $actorId = $_GET['actor_id'] ?? '';
        
        $params = [];
        // Xây dựng câu SQL lấy danh sách tài khoản kèm tên vai trò
        $sql = "SELECT u.*, sa.actor_name, sa.actor_code 
                FROM users u
                LEFT JOIN system_actors sa ON u.actor_id = sa.id
                WHERE 1=1";
                
        if ($search) {
            $sql .= " AND (u.username LIKE :search OR u.fullname LIKE :search OR u.email LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        if ($actorId) {
            $sql .= " AND u.actor_id = :actor_id";
            $params['actor_id'] = $actorId;
        }
        
        // Quản lý chợ (admin_market) chỉ nhìn thấy tài khoản nhân viên (admin) của các chợ mình quản lý
        if (marketService::isAdminMarket()) {
            $managerId = session::get('user_id');
            $sql .= " AND sa.actor_code = 'admin' AND u.id IN (
                SELECT DISTINCT user_id FROM user_markets WHERE market_id IN (
                    SELECT market_id FROM user_markets WHERE user_id = :manager_id
                )
            )";
            $params['manager_id'] = $managerId;
        }
        
        $sql .= " ORDER BY u.id DESC";
        $usersList = $db->select($sql, $params);
        
        // Lấy danh sách vai trò cho bộ lọc
        $actors = $db->select("SELECT * FROM system_actors ORDER BY id ASC");
        
        // Lấy audit log
        $logs = $db->select("
            SELECT l.*, u.fullname 
            FROM system_logs l 
            LEFT JOIN users u ON l.user_id = u.id 
            ORDER BY l.id DESC LIMIT 100
        ");

        $this->view('backend/user/index', [
            'title' => 'Quản Lý Tài Khoản',
            'users' => $usersList,
            'actors' => $actors,
            'logs' => $logs,
            'search' => $search,
            'actor_filter' => $actorId
        ]);
    }

    /**
     * Thêm tài khoản mới
     */
    public function user_add() {
        $error = '';
        $success = '';
        $db = database::getInstance();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $fullname = trim($_POST['fullname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $actorId = $_POST['actor_id'] ?? '';
            $marketIds = $_POST['market_ids'] ?? [];

            if (empty($username) || empty($password) || empty($fullname) || empty($actorId)) {
                $error = 'Vui lòng nhập đầy đủ các trường bắt buộc!';
            } else {
                try {
                    $userModel = new userModel();
                    
                    // Kiểm tra username trùng lặp
                    $exists = $db->selectOne("SELECT id FROM users WHERE username = :username", ['username' => $username]);
                    if ($exists) {
                        $error = 'Tên đăng nhập đã tồn tại!';
                    } else {
                        // Tạo tài khoản
                        $newUserId = $userModel->create([
                            'username' => $username,
                            'password' => $password,
                            'fullname' => $fullname,
                            'email'    => $email,
                            'actor_id' => $actorId
                        ]);

                        // Gán liên kết chợ nếu có chọn
                        if (!empty($marketIds)) {
                            foreach ($marketIds as $mId) {
                                $db->query("
                                    INSERT INTO user_markets (user_id, market_id, role_id)
                                    VALUES (:user_id, :market_id, :role_id)
                                ", [
                                    'user_id'   => $newUserId,
                                    'market_id' => $mId,
                                    'role_id'   => ($actorId == 2 ? 4 : 2) // Vai trò mặc định trong user_markets
                                ]);
                            }
                        }

                        $success = 'Tạo tài khoản thành công!';
                        header("Location: " . BASE_URL . "system/users");
                        exit();
                    }
                } catch (Exception $e) {
                    $error = 'Lỗi hệ thống: ' . $e->getMessage();
                }
            }
        }

        // Lấy danh sách các chợ & vai trò để chọn gán
        $markets = $db->select("SELECT id, name FROM markets WHERE status_code = 'active'");
        $actors = $db->select("SELECT * FROM system_actors ORDER BY id ASC");

        $this->view('backend/user/add', [
            'title' => 'Tạo Tài Khoản Mới',
            'markets' => $markets,
            'actors' => $actors,
            'error' => $error,
            'success' => $success
        ]);
    }

    /**
     * Sửa tài khoản
     */
    public function user_edit($id = null) {
        if (!$id) {
            header("Location: " . BASE_URL . "system/users");
            exit();
        }

        $db = database::getInstance();
        $userModel = new userModel();
        $user = $userModel->getById($id);

        if (!$user) {
            header("Location: " . BASE_URL . "system/users");
            exit();
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fullname = trim($_POST['fullname'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $actorId = $_POST['actor_id'] ?? $user['actor_id'];
            $password = $_POST['password'] ?? '';
            $marketIds = $_POST['market_ids'] ?? [];

            if (empty($fullname)) {
                $error = 'Họ tên không được để trống!';
            } else {
                try {
                    // Cập nhật thông tin cơ bản
                    $updateData = [
                        'fullname' => $fullname,
                        'email'    => $email,
                        'actor_id' => $actorId
                    ];
                    if (!empty($password)) {
                        $updateData['password'] = $password;
                    }
                    $userModel->update($id, $updateData);

                    // Cập nhật liên kết chợ (Chỉ cập nhật cho các chợ mà người quản lý có quyền gán)
                    if (marketService::isSuperAdmin()) {
                        $db->query("DELETE FROM user_markets WHERE user_id = :id", ['id' => $id]);
                        foreach ($marketIds as $mId) {
                            $db->query("
                                INSERT INTO user_markets (user_id, market_id, role_id)
                                VALUES (:user_id, :market_id, :role_id)
                            ", [
                                'user_id'   => $id,
                                'market_id' => $mId,
                                'role_id'   => ($actorId == 2 ? 4 : 2)
                            ]);
                        }
                    } else {
                        // admin_market chỉ sửa được liên kết tại các chợ họ quản lý
                        $managerId = session::get('user_id');
                        $managerMarkets = array_column($db->select("SELECT market_id FROM user_markets WHERE user_id = :id", ['id' => $managerId]), 'market_id');
                        if (!empty($managerMarkets)) {
                            $placeholders = implode(',', array_fill(0, count($managerMarkets), '?'));
                            $deleteParams = array_merge([$id], $managerMarkets);
                            $db->query("DELETE FROM user_markets WHERE user_id = ? AND market_id IN ($placeholders)", $deleteParams);
                            
                            foreach ($marketIds as $mId) {
                                if (in_array($mId, $managerMarkets)) {
                                    $db->query("
                                        INSERT INTO user_markets (user_id, market_id, role_id)
                                        VALUES (:user_id, :market_id, :role_id)
                                    ", [
                                        'user_id'   => $id,
                                        'market_id' => $mId,
                                        'role_id'   => 2
                                    ]);
                                }
                            }
                        }
                    }

                    $success = 'Cập nhật tài khoản thành công!';
                    header("Location: " . BASE_URL . "system/users");
                    exit();
                } catch (Exception $e) {
                    $error = 'Lỗi hệ thống: ' . $e->getMessage();
                }
            }
        }

        // Lấy danh sách chợ đã được gán hiện tại
        $assignedMarkets = array_column($db->select("SELECT market_id FROM user_markets WHERE user_id = :id", ['id' => $id]), 'market_id');
        $markets = $db->select("SELECT id, name FROM markets WHERE status_code = 'active'");
        $actors = $db->select("SELECT * FROM system_actors ORDER BY id ASC");

        $this->view('backend/user/edit', [
            'title' => 'Sửa Tài Khoản',
            'user' => $user,
            'markets' => $markets,
            'actors' => $actors,
            'assignedMarkets' => $assignedMarkets,
            'error' => $error,
            'success' => $success
        ]);
    }

    /**
     * Khóa / Mở khóa tài khoản (AJAX POST)
     */
    public function user_toggle_status($id = null) {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$id) {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ.']);
            exit();
        }

        // Không cho tự khóa chính mình
        if ((int)$id === (int)session::get('user_id')) {
            echo json_encode(['success' => false, 'message' => 'Bạn không thể tự khóa tài khoản của chính mình!']);
            exit();
        }

        try {
            $db = database::getInstance();
            $user = $db->selectOne("SELECT is_active, fullname FROM users WHERE id = :id", ['id' => $id]);
            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'Người dùng không tồn tại']);
                exit();
            }

            $newStatus = $user['is_active'] == 1 ? 0 : 1;
            $db->query("UPDATE users SET is_active = :status WHERE id = :id", ['status' => $newStatus, 'id' => $id]);

            $msg = $newStatus == 1 ? "Đã kích hoạt lại tài khoản {$user['fullname']}." : "Đã khóa tài khoản {$user['fullname']}.";
            echo json_encode(['success' => true, 'message' => $msg, 'new_status' => $newStatus]);
            exit();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
            exit();
        }
    }

    /**
     * Phân quyền nhân viên (Staff permissions grid)
     */
    public function permissions() {
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

        // Lấy ma trận quyền hiện có
        $rawPerms = $db->select("
            SELECT user_id, market_id, module_code 
            FROM user_market_permissions 
            WHERE market_id IN ($marketIdsStr)
        ");

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
     * AJAX Lưu quyền
     */
    public function save_permissions() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Yêu cầu không hợp lệ']);
            exit();
        }

        $userId = $_POST['user_id'] ?? '';
        $marketId = $_POST['market_id'] ?? '';
        $module = $_POST['module'] ?? '';
        $checked = $_POST['checked'] ?? 0;

        if (empty($userId) || empty($marketId) || empty($module)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu tham số']);
            exit();
        }

        try {
            $db = database::getInstance();

            if ($checked == 1) {
                $db->query("
                    INSERT INTO user_market_permissions (user_id, market_id, module_code)
                    VALUES (:user_id, :market_id, :module_code)
                    ON DUPLICATE KEY UPDATE module_code = VALUES(module_code)
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
     * Render view
     */
    protected function view($templatePath, $data = []) {
        extract($data);
        if (file_exists(DIR_TEMPLATE . '/backend/layouts/header.php')) {
            require_once DIR_TEMPLATE . '/backend/layouts/header.php';
        }
        if (file_exists(DIR_TEMPLATE . '/backend/layouts/sidebar.php')) {
            require_once DIR_TEMPLATE . '/backend/layouts/sidebar.php';
        }
        if (file_exists(DIR_TEMPLATE . '/backend/layouts/navbar.php')) {
            require_once DIR_TEMPLATE . '/backend/layouts/navbar.php';
        }
        $viewFile = DIR_TEMPLATE . '/' . $templatePath . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            echo "<div class='container-fluid'><p class='text-danger'>Không tìm thấy giao diện: {$templatePath}</p></div>";
        }
        if (file_exists(DIR_TEMPLATE . '/backend/layouts/footer.php')) {
            require_once DIR_TEMPLATE . '/backend/layouts/footer.php';
        }
    }
}
