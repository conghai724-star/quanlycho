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
                        header('Location: ' . BASE_URL . 'system/users');
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
     * Sửa tài khoản
     */
    public function user_edit($id = null) {
        if (!$id) {
            header('Location: ' . BASE_URL . 'system/users');
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
            header('Location: ' . BASE_URL . 'system/users');
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
                header('Location: ' . BASE_URL . 'system/users');
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
                        header('Location: ' . BASE_URL . 'system/users');
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
