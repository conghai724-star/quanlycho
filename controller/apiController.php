<?php
/**
 * Controller xử lý các yêu cầu AJAX / API trả về dữ liệu JSON
 */
class apiController {
    use httpGuard;

    public function __construct() {
        // Chỉ cho phép truy cập API khi đã đăng nhập với quyền admin (user_group = 1)
        if (!session::isLoggedIn() || session::get('user_group') != 1) {
            $this->response(['error' => 'Bạn không có quyền thực hiện hành động này.'], 403);
        }
    }

    /**
     * Helper xuất phản hồi JSON
     */
    protected function response($data, $statusCode = 200) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    /**
     * Helper xuất phản hồi JSON định dạng tiếng Việt chuẩn theo hành động
     */
    protected function apiResponse($action, $entity, $isSuccess, $detail = '', $statusCode = null) {
        $code = $statusCode ?? ($isSuccess ? 200 : 400);
        $message = message::result($action, $entity, $isSuccess, $detail);
        
        $this->response([
            'status'  => $code,
            'message' => $message
        ], $code);
    }

    /**
     * API thống kê doanh thu theo tháng (Phục vụ vẽ biểu đồ Chart.js)
     */
    public function getRevenueData() {
        // Dữ liệu giả lập doanh thu 6 tháng gần nhất
        $data = [
            'labels' => ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
            'revenue' => [380000000, 420000000, 400000000, 450000000, 480000000, 450000000],
            'expense' => [120000000, 130000000, 115000000, 140000000, 150000000, 135000000]
        ];

        $this->response($data);
    }
//--------------BẮT ĐẦU QUẢN LÝ TIỂU THƯƠNG--------------//
    /**
     * API lọc và tìm kiếm tiểu thương qua AJAX
     */
    public function filterTraders() {
        $search = $_GET['q'] ?? '';
        $business_line = $_GET['business_line'] ?? '';
        $status = $_GET['status'] ?? '';

        try {
            $traderModel = new traderModel();
            $traders = $traderModel->getAllTraders($search, $business_line, $status);

            // Nạp template table_rows.php để sinh ra HTML
            ob_start();
            // Nạp biến $traders cho view table_rows.php
            require DIR_TEMPLATE . '/backend/trader/table_rows.php';
            $html = ob_get_clean();

            // Sinh query string mới phục vụ cập nhật các link export file
            $queryString = http_build_query([
                'q' => $search,
                'business_line' => $business_line,
                'status' => $status
            ]);

            $this->response([
                'status' => 200,
                'total' => count($traders),
                'html' => $html,
                'queryString' => $queryString
            ]);
        } catch (Exception $e) {
            $this->response([
                'status' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API xóa tiểu thương (AJAX POST)
     */
    public function deleteTrader() {
        $this->abort405('POST', 'delete', 'trader');
        $this->abort400('id', 'delete', 'trader');

        $id = $_POST['id'];

        try {
            $traderModel = new traderModel();
            $trader = $this->abort404($traderModel, 'getTraderById', $id, 'delete', 'trader');

            $traderModel->deleteTrader($id);
            $this->apiResponse('delete', 'trader', true);
        } catch (Exception $e) {
            $this->abort500($e, 'delete', 'trader');
        }
    }

    /**
     * API thêm tiểu thương mới (AJAX POST)
     */
    /**
     * API thêm tiểu thương mới (AJAX POST)
     */
    public function addTrader() {
        $this->abort405('POST', 'create', 'trader');

        $data = [
            'trader_code'      => $_POST['trader_code'] ?? '',
            'fullname'         => $_POST['fullname'] ?? '',
            'phone'            => $_POST['phone'] ?? '',
            'cccd'             => $_POST['cccd'] ?? '',
            'address'          => $_POST['address'] ?? '',
            'business_line_id' => $_POST['business_line_id'] ?? null,
            'description'      => $_POST['description'] ?? '',
            'status_id'        => $_POST['status'] ?? 7
        ];

        // Xác thực dữ liệu
        $validator = new validator();
        $validator->required('trader_code', $data['trader_code'], 'Mã tiểu thương không được để trống.')
                  ->required('fullname', $data['fullname'], 'Họ tên không được để trống.')
                  ->required('phone', $data['phone'], 'Số điện thoại không được để trống.')
                  ->phone('phone', $data['phone'], 'Số điện thoại không đúng định dạng Việt Nam.')
                  ->required('cccd', $data['cccd'], 'Số CCCD không được để trống.');

        $this->abort400($validator, 'create', 'trader');

        try {
            $traderModel = new traderModel();
            
            // Kiểm tra trùng lặp
            $this->abort400(!$traderModel->isTraderCodeExists($data['trader_code']), 'create', 'trader', 'Mã tiểu thương đã tồn tại trên hệ thống');
            $this->abort400(!$traderModel->isCccdExists($data['cccd']), 'create', 'trader', 'Số CCCD đã tồn tại trên hệ thống');

            // Xử lý upload nhiều tài liệu đính kèm (nếu có)
            $uploadedFiles = $this->uploadMultipleFiles('license_files', 'traders', ['jpg', 'jpeg', 'png', 'pdf'], 10, 'create', 'trader');
            $data['license_file'] = !empty($uploadedFiles) ? json_encode($uploadedFiles) : null;

            $traderModel->createTrader($data);
            $this->apiResponse('create', 'trader', true);
        } catch (Exception $e) {
            $this->abort500($e, 'create', 'trader');
        }
    }

    /**
     * API sửa thông tin tiểu thương (AJAX POST)
     */
    public function editTrader() {
        $this->abort405('POST', 'update', 'trader');
        $this->abort400('id', 'update', 'trader');

        $id = $_POST['id'];

        $data = [
            'fullname'         => $_POST['fullname'] ?? '',
            'phone'            => $_POST['phone'] ?? '',
            'cccd'             => $_POST['cccd'] ?? '',
            'address'          => $_POST['address'] ?? '',
            'business_line_id' => $_POST['business_line_id'] ?? null,
            'description'      => $_POST['description'] ?? '',
            'status_id'        => $_POST['status'] ?? 7
        ];

        // Xác thực dữ liệu
        $validator = new validator();
        $validator->required('fullname', $data['fullname'], 'Họ tên không được để trống.')
                  ->required('phone', $data['phone'], 'Số điện thoại không được để trống.')
                  ->phone('phone', $data['phone'], 'Số điện thoại không đúng định dạng Việt Nam.')
                  ->required('cccd', $data['cccd'], 'Số CCCD không được để trống.');

        $this->abort400($validator, 'update', 'trader');

        try {
            $traderModel = new traderModel();
            $trader = $this->abort404($traderModel, 'getTraderById', $id, 'update', 'trader');

            // Kiểm tra trùng lặp số CCCD (loại trừ bản ghi hiện tại)
            $this->abort400(!$traderModel->isCccdExists($data['cccd'], $id), 'update', 'trader', 'Số CCCD đã tồn tại trên hệ thống');

            // Xử lý các file cũ còn lại sau khi người dùng xóa bớt trên giao diện
            $existingFiles = [];
            if (!empty($trader['license_file'])) {
                $decoded = json_decode($trader['license_file'], true);
                $existingFiles = is_array($decoded) ? $decoded : [$trader['license_file']];
            }
            $keptFiles = $_POST['existing_files'] ?? [];
            $existingFiles = array_intersect($existingFiles, $keptFiles);

            // Xử lý upload các file mới
            $uploadedFiles = $this->uploadMultipleFiles('license_files', 'traders', ['jpg', 'jpeg', 'png', 'pdf'], 10, 'update', 'trader');
            $finalFiles = array_merge($existingFiles, $uploadedFiles);
            $data['license_file'] = !empty($finalFiles) ? json_encode(array_values($finalFiles)) : null;

            $traderModel->updateTrader($id, $data);
            $this->apiResponse('update', 'trader', true);
        } catch (Exception $e) {
            $this->abort500($e, 'update', 'trader');
        }
    }


//--------------KẾT THÚC QUẢN LÝ TIỂU THƯƠNG--------------//
//--------------BẮT ĐẦU QUẢN LÝ SẠP CHỢ--------------//

    /**
     * API lọc và tìm kiếm sạp chợ qua AJAX
     */
    public function filterStalls() {
        $search = $_GET['q'] ?? '';
        $area_id = $_GET['area_id'] ?? '';
        $status = $_GET['status'] ?? '';

        try {
            $stallModel = new stallModel();
            $stalls = $stallModel->getAll($area_id ?: null, $status ?: null, $search ?: null);

            ob_start();
            require DIR_TEMPLATE . '/backend/stall/table_rows.php';
            $html = ob_get_clean();

            $queryString = http_build_query([
                'q' => $search,
                'area_id' => $area_id,
                'status' => $status
            ]);

            $this->response([
                'status' => 200,
                'total' => count($stalls),
                'html' => $html,
                'queryString' => $queryString
            ]);
        } catch (Exception $e) {
            $this->response([
                'status' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API thêm sạp mới (AJAX POST)
     */
    public function addStall() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->apiResponse('create', 'stall', false, 'method_not_allowed', 405);
        }

        $data = [
            'area_id'    => $_POST['area_id'] ?? '',
            'stall_code' => $_POST['stall_code'] ?? '',
            'stall_type' => $_POST['stall_type'] ?? 'Quầy hàng',
            'area_size'  => $_POST['area_size'] ?? '',
            'base_price' => $_POST['base_price'] ?? '',
            'status_id'  => $_POST['status'] ?? 3
        ];

        $validator = new validator();
        $validator->required('area_id', $data['area_id'], 'Khu vực không được để trống.')
                  ->required('stall_code', $data['stall_code'], 'Mã sạp không được để trống.')
                  ->required('area_size', $data['area_size'], 'Diện tích không được để trống.')
                  ->numeric('area_size', $data['area_size'], 'Diện tích phải là dạng số.')
                  ->min('area_size', $data['area_size'], 0.01, 'Diện tích phải lớn hơn 0.')
                  ->required('base_price', $data['base_price'], 'Đơn giá thuê không được để trống.')
                  ->numeric('base_price', $data['base_price'], 'Đơn giá thuê phải là dạng số.')
                  ->min('base_price', $data['base_price'], 0, 'Đơn giá thuê không được âm.');

        if (!$validator->isValid()) {
            $errors = $validator->getErrors();
            $firstError = reset($errors);
            $this->apiResponse('create', 'stall', false, $firstError, 400);
        }

        try {
            $stallModel = new stallModel();
            
            if ($stallModel->isStallCodeExists($data['stall_code'])) {
                $this->apiResponse('create', 'stall', false, 'Mã sạp đã tồn tại trên hệ thống', 400);
            }

            $stallModel->create($data);
            $this->apiResponse('create', 'stall', true);
        } catch (Exception $e) {
            $this->apiResponse('create', 'stall', false, $e->getMessage(), 500);
        }
    }

    /**
     * API cập nhật sạp (AJAX POST)
     */
    public function editStall() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->apiResponse('update', 'stall', false, 'method_not_allowed', 405);
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->apiResponse('update', 'stall', false, 'missing_id', 400);
        }

        $data = [
            'area_id'    => $_POST['area_id'] ?? '',
            'stall_code' => $_POST['stall_code'] ?? '',
            'stall_type' => $_POST['stall_type'] ?? 'Quầy hàng',
            'area_size'  => $_POST['area_size'] ?? '',
            'base_price' => $_POST['base_price'] ?? '',
            'status_id'  => $_POST['status'] ?? 3
        ];

        $validator = new validator();
        $validator->required('area_id', $data['area_id'], 'Khu vực không được để trống.')
                  ->required('stall_code', $data['stall_code'], 'Mã sạp không được để trống.')
                  ->required('area_size', $data['area_size'], 'Diện tích không được để trống.')
                  ->numeric('area_size', $data['area_size'], 'Diện tích phải là dạng số.')
                  ->min('area_size', $data['area_size'], 0.01, 'Diện tích phải lớn hơn 0.')
                  ->required('base_price', $data['base_price'], 'Đơn giá thuê không được để trống.')
                  ->numeric('base_price', $data['base_price'], 'Đơn giá thuê phải là dạng số.')
                  ->min('base_price', $data['base_price'], 0, 'Đơn giá thuê không được âm.');

        if (!$validator->isValid()) {
            $errors = $validator->getErrors();
            $firstError = reset($errors);
            $this->apiResponse('update', 'stall', false, $firstError, 400);
        }

        try {
            $stallModel = new stallModel();
            $stall = $stallModel->getById($id);
            if (!$stall) {
                $this->apiResponse('update', 'stall', false, 'not_found', 404);
            }

            if ($stallModel->isStallCodeExists($data['stall_code'], $id)) {
                $this->apiResponse('update', 'stall', false, 'Mã sạp đã tồn tại trên hệ thống', 400);
            }

            $stallModel->update($id, $data);
            $this->apiResponse('update', 'stall', true);
        } catch (Exception $e) {
            $this->apiResponse('update', 'stall', false, $e->getMessage(), 500);
        }
    }

    /**
     * API xóa sạp (AJAX POST)
     */
    public function deleteStall() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->apiResponse('delete', 'stall', false, 'method_not_allowed', 405);
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            $this->apiResponse('delete', 'stall', false, 'missing_id', 400);
        }

        try {
            $stallModel = new stallModel();
            $stall = $stallModel->getById($id);
            if (!$stall) {
                $this->apiResponse('delete', 'stall', false, 'not_found', 404);
            }

            if ($stallModel->hasActiveContract($id)) {
                $this->apiResponse('delete', 'stall', false, 'Sạp đang có hợp đồng hoạt động, không thể xóa.', 400);
            }

            $stallModel->delete($id);
            $this->apiResponse('delete', 'stall', true);
        } catch (Exception $e) {
            $this->apiResponse('delete', 'stall', false, $e->getMessage(), 500);
        }
    }

    /**
     * API lấy danh sách sạp đang trống (AJAX GET)
     */
    public function getEmptyStalls() {
        try {
            $stallModel = new stallModel();
            $stalls = $stallModel->getAll(null, 'empty');
            $this->response($stalls);
        } catch (Exception $e) {
            $this->response(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API lấy danh sách sạp khả dụng để chuyển đổi (AJAX GET)
     */
    public function getAvailableStallsForTransfer() {
        try {
            $excludeId = $_GET['exclude_id'] ?? null;
            $db = database::getInstance();
            
            $sql = "SELECT s.id, s.stall_code, a.area_name, ss.status_name, ss.code AS status_code,
                           t.fullname AS trader_name
                    FROM stalls s
                    LEFT JOIN areas a ON s.area_id = a.id
                    LEFT JOIN system_statuses ss ON s.status_id = ss.id
                    LEFT JOIN contracts c ON c.stall_id = s.id AND c.status_id = (SELECT id FROM system_statuses WHERE domain = 'contract' AND code = 'active')
                    LEFT JOIN traders t ON c.trader_id = t.id
                    WHERE s.id != :exclude_id
                    ORDER BY a.area_name ASC, s.stall_code ASC";
            
            $stalls = $db->select($sql, ['exclude_id' => $excludeId]);
            $this->response($stalls);
        } catch (Exception $e) {
            $this->response(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API lấy danh sách tiểu thương chưa thuê sạp (AJAX GET)
     */
    public function getAvailableTraders() {
        try {
            $db = database::getInstance();
            $sql = "SELECT id, fullname, trader_code FROM traders 
                    WHERE status_id = (SELECT id FROM system_statuses WHERE domain = 'trader' AND code = 'active')
                      AND id NOT IN (SELECT trader_id FROM contracts WHERE status_id = (SELECT id FROM system_statuses WHERE domain = 'contract' AND code = 'active'))
                    ORDER BY fullname ASC";
            $traders = $db->select($sql);
            $this->response($traders);
        } catch (Exception $e) {
            $this->response(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API gán sạp nhanh cho tiểu thương (AJAX POST)
     */
    public function assignStall() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->apiResponse('create', 'contract', false, 'method_not_allowed', 405);
        }

        $stallId = $_POST['stall_id'] ?? null;
        $traderId = $_POST['trader_id'] ?? null;

        if (!$stallId || !$traderId) {
            $this->apiResponse('create', 'contract', false, 'Vui lòng chọn đầy đủ sạp và tiểu thương.', 400);
        }

        try {
            $stallModel = new stallModel();
            $stall = $stallModel->getById($stallId);
            if (!$stall || $stall['status'] !== 'empty') {
                $this->apiResponse('create', 'contract', false, 'Sạp này không còn trống để cho thuê.', 400);
            }

            $contractModel = new contractModel();
            $contractData = [
                'trader_id' => $traderId,
                'stall_id' => $stallId,
                'name' => 'Hợp đồng thuê sạp ' . $stall['stall_code'],
                'contract_number' => 'HĐ-GAN-' . date('Ymd') . '-' . rand(100, 999),
                'start_date' => date('Y-m-d'),
                'end_date' => date('Y-m-d', strtotime('+1 year')),
                'deposit' => $stall['base_price'] * 2,
                'status' => 'active'
            ];

            $contractModel->create($contractData);
            
            $this->response([
                'status' => 200,
                'message' => 'Gán sạp cho tiểu thương thành công!'
            ]);
        } catch (Exception $e) {
            $this->apiResponse('create', 'contract', false, $e->getMessage(), 500);
        }
    }

    /**
     * API chuyển đổi sạp của tiểu thương (AJAX POST)
     */
    public function transferStall() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->apiResponse('update', 'stall', false, 'method_not_allowed', 405);
        }

        $currentStallId = $_POST['current_stall_id'] ?? null;
        $newStallId = $_POST['new_stall_id'] ?? null;

        if (!$currentStallId || !$newStallId) {
            $this->apiResponse('update', 'stall', false, 'Thiếu thông tin sạp hiện tại hoặc sạp mới.', 400);
        }

        try {
            $db = database::getInstance();
            
            $stallModel = new stallModel();
            $currentStall = $stallModel->getById($currentStallId);
            $newStall = $stallModel->getById($newStallId);
            if (!$currentStall || !$newStall) {
                $this->apiResponse('update', 'stall', false, 'Không tìm thấy thông tin sạp.', 404);
            }

            // Lấy hợp đồng hoạt động của sạp hiện tại
            $sqlContract1 = "SELECT * FROM contracts WHERE stall_id = :stall_id AND status_id = (SELECT id FROM system_statuses WHERE domain = 'contract' AND code = 'active') LIMIT 1";
            $contract1 = $db->selectOne($sqlContract1, ['stall_id' => $currentStallId]);
            if (!$contract1) {
                $this->apiResponse('update', 'stall', false, 'Không tìm thấy hợp đồng đang hoạt động cho sạp hiện tại.', 404);
            }

            $db->beginTransaction();

            $statusModel = new statusModel();
            $emptyStatusId = $statusModel->getIdByCode('stall', 'empty');
            $rentedStatusId = $statusModel->getIdByCode('stall', 'rented');

            // Kiểm tra trạng thái của sạp mới
            if ($newStall['status'] === 'empty') {
                // Trường hợp 1: Chuyển sang sạp trống (Đơn phương)
                $sqlUpdateContract = "UPDATE contracts SET stall_id = :new_stall_id WHERE id = :contract_id";
                $db->query($sqlUpdateContract, [
                    'new_stall_id' => $newStallId,
                    'contract_id'  => $contract1['id']
                ]);

                $stallModel->updateStatus($currentStallId, $emptyStatusId);
                $stallModel->updateStatus($newStallId, $rentedStatusId);
                $message = 'Chuyển đổi sạp thành công!';
            } else {
                // Trường hợp 2: Đổi sạp giữa 2 tiểu thương (Cả hai sạp đều đang hoạt động)
                 $sqlContract2 = "SELECT * FROM contracts WHERE stall_id = :stall_id AND status_id = (SELECT id FROM system_statuses WHERE domain = 'contract' AND code = 'active') LIMIT 1";
                $contract2 = $db->selectOne($sqlContract2, ['stall_id' => $newStallId]);
                
                if (!$contract2) {
                    // Nếu sạp mới không có hợp đồng hoạt động nhưng trạng thái khác empty, vẫn cho phép chuyển đơn phương
                    $sqlUpdateContract = "UPDATE contracts SET stall_id = :new_stall_id WHERE id = :contract_id";
                    $db->query($sqlUpdateContract, [
                        'new_stall_id' => $newStallId,
                        'contract_id'  => $contract1['id']
                    ]);
                    $stallModel->updateStatus($currentStallId, $emptyStatusId);
                    $stallModel->updateStatus($newStallId, $rentedStatusId);
                    $message = 'Chuyển đổi sạp sang sạp mới thành công!';
                } else {
                    // Thực hiện tráo đổi (swap) stall_id của 2 hợp đồng hoạt động
                    $sqlUpdateContract1 = "UPDATE contracts SET stall_id = :new_stall_id WHERE id = :contract_id";
                    $db->query($sqlUpdateContract1, [
                        'new_stall_id' => $newStallId,
                        'contract_id'  => $contract1['id']
                    ]);

                    $sqlUpdateContract2 = "UPDATE contracts SET stall_id = :new_stall_id WHERE id = :contract_id";
                    $db->query($sqlUpdateContract2, [
                        'new_stall_id' => $currentStallId,
                        'contract_id'  => $contract2['id']
                    ]);

                    // Trạng thái của cả 2 sạp giữ nguyên là 'rented' (đã thuê)
                    $stallModel->updateStatus($currentStallId, $rentedStatusId);
                    $stallModel->updateStatus($newStallId, $rentedStatusId);
                    $message = 'Tráo đổi sạp giữa 2 tiểu thương thành công!';
                }
            }

            $db->commit();
            $this->response([
                'status' => 200,
                'message' => $message
            ]);
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $this->response([
                'status' => 500,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * API dùng chung kiểm tra sự tồn tại (trùng lặp) của mã/CCCD thời gian thực (blur check)
     * GET api/checkExists?type=[stall_code|trader_code|cccd]&value=xxx&exclude_id=yyy
     */
    public function checkExists() {
        $type = $_GET['type'] ?? '';
        $value = $_GET['value'] ?? '';
        $excludeId = $_GET['exclude_id'] ?? null;

        if (empty($type) || empty($value)) {
            $this->response(['exists' => false]);
        }

        try {
            $exists = false;
            switch ($type) {
                case 'stall_code':
                    $stallModel = new stallModel();
                    $exists = $stallModel->isStallCodeExists($value, $excludeId);
                    break;
                case 'trader_code':
                    $traderModel = new traderModel();
                    $exists = $traderModel->isTraderCodeExists($value, $excludeId);
                    break;
                case 'cccd':
                    $traderModel = new traderModel();
                    $exists = $traderModel->isCccdExists($value, $excludeId);
                    break;
                case 'contract_number':
                    $db = database::getInstance();
                    $chk = $db->selectOne("SELECT COUNT(*) as count FROM contracts WHERE contract_number = :num AND status_id != (SELECT id FROM system_statuses WHERE domain = 'contract' AND code = '99')", ['num' => $value]);
                    $exists = ($chk['count'] ?? 0) > 0;
                    break;
            }
            $this->response(['exists' => $exists]);
        } catch (Exception $e) {
            $this->response(['exists' => false, 'error' => $e->getMessage()]);
        }
    }

    //--------------BẮT ĐẦU QUẢN LÝ HỢP ĐỒNG--------------//

    /**
     * API lọc và tìm kiếm hợp đồng qua AJAX
     */
    public function filterContracts() {
        $search = $_GET['q'] ?? '';
        $status = $_GET['status'] ?? '';

        try {
            $contractModel = new contractModel();
            $contracts = $contractModel->getAll($status ?: null, $search ?: null);

            ob_start();
            require DIR_TEMPLATE . '/backend/contract/table_rows.php';
            $html = ob_get_clean();

            $queryString = http_build_query([
                'q' => $search,
                'status' => $status
            ]);

            $this->response([
                'status' => 200,
                'total' => count($contracts),
                'html' => $html,
                'queryString' => $queryString
            ]);
        } catch (Exception $e) {
            $this->response([
                'status' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API thêm hợp đồng mới (AJAX POST)
     */
    public function addContract() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->apiResponse('create', 'contract', false, 'method_not_allowed', 405);
        }

        $data = [
            'trader_id'       => $_POST['trader_id'] ?? '',
            'stall_id'        => $_POST['stall_id'] ?? '',
            'contract_number' => $_POST['contract_number'] ?? '',
            'name'            => $_POST['name'] ?? '',
            'description'     => $_POST['description'] ?? '',
            'start_date'      => $_POST['start_date'] ?? '',
            'end_date'        => $_POST['end_date'] ?? '',
            'deposit'         => $_POST['deposit'] ?? 0,
        ];

        // Xác thực dữ liệu
        $validator = new validator();
        $validator->required('trader_id', $data['trader_id'], 'Vui lòng chọn tiểu thương.')
                  ->required('stall_id', $data['stall_id'], 'Vui lòng chọn sạp chợ.')
                  ->required('contract_number', $data['contract_number'], 'Số hợp đồng không được để trống.')
                  ->required('name', $data['name'], 'Tên hợp đồng không được để trống.')
                  ->required('start_date', $data['start_date'], 'Vui lòng nhập ngày bắt đầu.')
                  ->required('end_date', $data['end_date'], 'Vui lòng nhập ngày hết hạn.')
                  ->required('deposit', $data['deposit'], 'Vui lòng nhập tiền đặt cọc.');

        if (!$validator->isValid()) {
            $errors = $validator->getErrors();
            $this->apiResponse('create', 'contract', false, reset($errors), 400);
        }

        if (strtotime($data['start_date']) > strtotime($data['end_date'])) {
            $this->apiResponse('create', 'contract', false, 'Ngày bắt đầu không được lớn hơn ngày kết thúc.', 400);
        }

        try {
            $db = database::getInstance();
            $contractModel = new contractModel();
            
            // Kiểm tra trùng số hợp đồng
            $checkNum = $db->selectOne("SELECT COUNT(*) as count FROM contracts WHERE contract_number = :num AND status_id != (SELECT id FROM system_statuses WHERE domain = 'contract' AND code = '99')", ['num' => $data['contract_number']]);
            if (($checkNum['count'] ?? 0) > 0) {
                $this->apiResponse('create', 'contract', false, 'Số hợp đồng này đã tồn tại trên hệ thống.', 400);
            }

            // Kiểm tra xem sạp có đang trống hay không
            $stallModel = new stallModel();
            $stall = $stallModel->getById($data['stall_id']);
            if (!$stall || $stall['status'] !== 'empty') {
                $this->apiResponse('create', 'contract', false, 'Sạp được chọn không còn trống để cho thuê.', 400);
            }

            // Xử lý upload file PDF đính kèm (nếu có)
            if (isset($_FILES['contract_file']) && $_FILES['contract_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploader = new upload('contracts', ['pdf'], 15); // Chỉ nhận file PDF
                $savedFile = $uploader->save('contract_file');
                if ($savedFile === false) {
                    $errors = $uploader->getErrors();
                    $this->apiResponse('create', 'contract', false, 'Lỗi tải file hợp đồng: ' . reset($errors), 400);
                }
                $data['contract_file'] = $savedFile;
            }

            $contractModel->create($data);
            $this->apiResponse('create', 'contract', true);
        } catch (Exception $e) {
            $this->apiResponse('create', 'contract', false, $e->getMessage(), 500);
        }
    }

    /**
     * API gia hạn hợp đồng (AJAX POST)
     */
    public function renewContract() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->apiResponse('update', 'contract', false, 'method_not_allowed', 405);
        }

        $contractId = $_POST['contract_id'] ?? null;
        $newEndDate = $_POST['new_end_date'] ?? null;

        if (!$contractId || !$newEndDate) {
            $this->apiResponse('update', 'contract', false, 'Thiếu thông tin gia hạn.', 400);
        }

        try {
            $contractModel = new contractModel();
            $contract = $contractModel->getById($contractId);
            if (!$contract) {
                $this->apiResponse('update', 'contract', false, 'Không tìm thấy hợp đồng.', 404);
            }

            if (strtotime($newEndDate) <= strtotime($contract['end_date'])) {
                $this->apiResponse('update', 'contract', false, 'Ngày gia hạn mới phải sau ngày hết hạn hiện tại (' . $contract['end_date'] . ').', 400);
            }

            $contractModel->renew($contractId, $newEndDate);
            $this->apiResponse('update', 'contract', true);
        } catch (Exception $e) {
            $this->apiResponse('update', 'contract', false, $e->getMessage(), 500);
        }
    }

    /**
     * API thanh lý hợp đồng (AJAX POST)
     */
    public function liquidateContract() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->apiResponse('update', 'contract', false, 'method_not_allowed', 405);
        }

        $contractId = $_POST['contract_id'] ?? null;
        if (!$contractId) {
            $this->apiResponse('update', 'contract', false, 'missing_id', 400);
        }

        try {
            $contractModel = new contractModel();
            $contract = $contractModel->getById($contractId);
            if (!$contract) {
                $this->apiResponse('update', 'contract', false, 'Không tìm thấy hợp đồng.', 404);
            }

            $contractModel->liquidate($contractId);
            $this->apiResponse('update', 'contract', true);
        } catch (Exception $e) {
            $this->apiResponse('update', 'contract', false, $e->getMessage(), 500);
        }
    }

    /**
     * API chấm dứt hợp đồng trước hạn (AJAX POST)
     */
    public function terminateContract() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->apiResponse('update', 'contract', false, 'method_not_allowed', 405);
        }

        $contractId = $_POST['contract_id'] ?? null;
        if (!$contractId) {
            $this->apiResponse('update', 'contract', false, 'missing_id', 400);
        }

        try {
            $contractModel = new contractModel();
            $contract = $contractModel->getById($contractId);
            if (!$contract) {
                $this->apiResponse('update', 'contract', false, 'Không tìm thấy hợp đồng.', 404);
            }

            $contractModel->terminate($contractId);
            $this->apiResponse('update', 'contract', true);
        } catch (Exception $e) {
            $this->apiResponse('update', 'contract', false, $e->getMessage(), 500);
        }
    }

    /**
     * API xóa mềm hợp đồng (AJAX POST - status_id = 99)
     */
    public function deleteContract() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->apiResponse('delete', 'contract', false, 'method_not_allowed', 405);
        }

        $contractId = $_POST['contract_id'] ?? null;
        if (!$contractId) {
            $this->apiResponse('delete', 'contract', false, 'missing_id', 400);
        }

        try {
            $contractModel = new contractModel();
            $contract = $contractModel->getById($contractId);
            if (!$contract) {
                $this->apiResponse('delete', 'contract', false, 'Không tìm thấy hợp đồng.', 404);
            }

            $contractModel->softDelete($contractId);
            $this->apiResponse('delete', 'contract', true);
        } catch (Exception $e) {
            $this->apiResponse('delete', 'contract', false, $e->getMessage(), 500);
        }
    }

    /**
     * API thêm phụ lục hợp đồng (AJAX POST)
     */
    public function addContractAppendix() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->apiResponse('create', 'appendix', false, 'method_not_allowed', 405);
        }

        $data = [
            'contract_id'     => $_POST['contract_id'] ?? '',
            'appendix_number' => $_POST['appendix_number'] ?? '',
            'name'            => $_POST['name'] ?? '',
            'sign_date'       => $_POST['sign_date'] ?? '',
            'effect_date'     => $_POST['effect_date'] ?? '',
            'content'         => $_POST['content'] ?? '',
        ];

        $validator = new validator();
        $validator->required('contract_id', $data['contract_id'], 'Thiếu ID hợp đồng.')
                  ->required('appendix_number', $data['appendix_number'], 'Số phụ lục không được để trống.')
                  ->required('name', $data['name'], 'Tên phụ lục không được để trống.')
                  ->required('sign_date', $data['sign_date'], 'Vui lòng nhập ngày ký.')
                  ->required('effect_date', $data['effect_date'], 'Vui lòng nhập ngày có hiệu lực.')
                  ->required('content', $data['content'], 'Nội dung phụ lục không được để trống.');

        if (!$validator->isValid()) {
            $errors = $validator->getErrors();
            $this->apiResponse('create', 'appendix', false, reset($errors), 400);
        }

        try {
            $db = database::getInstance();
            // Kiểm tra trùng số phụ lục
            $checkNum = $db->selectOne("SELECT COUNT(*) as count FROM contract_appendices WHERE appendix_number = :num", ['num' => $data['appendix_number']]);
            if (($checkNum['count'] ?? 0) > 0) {
                $this->apiResponse('create', 'appendix', false, 'Số phụ lục này đã tồn tại trên hệ thống.', 400);
            }

            // Xử lý upload file phụ lục (nếu có)
            if (isset($_FILES['appendix_file']) && $_FILES['appendix_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploader = new upload('contracts/appendices', ['jpg', 'jpeg', 'png', 'pdf'], 15);
                $savedFile = $uploader->save('appendix_file');
                if ($savedFile === false) {
                    $errors = $uploader->getErrors();
                    $this->apiResponse('create', 'appendix', false, 'Lỗi tải file phụ lục: ' . reset($errors), 400);
                }
                $data['file'] = $savedFile;
            }

            $contractModel = new contractModel();
            $contractModel->addAppendix($data);
            $this->apiResponse('create', 'appendix', true);
        } catch (Exception $e) {
            $this->apiResponse('create', 'appendix', false, $e->getMessage(), 500);
        }
    }

    /**
     * API lấy danh sách phụ lục hợp đồng (AJAX GET)
     */
    public function getContractAppendices() {
        $contractId = $_GET['contract_id'] ?? null;
        if (!$contractId) {
            $this->response(['status' => 400, 'message' => 'Thiếu ID hợp đồng.'], 400);
        }

        try {
            $contractModel = new contractModel();
            $appendices = $contractModel->getAppendices($contractId);
            $this->response([
                'status' => 200,
                'data' => $appendices
            ]);
        } catch (Exception $e) {
            $this->response([
                'status' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    //--------------BẮT ĐẦU QUẢN LÝ AN TOÀN THỰC PHẨM (ATTP)--------------//

    /**
     * API lọc và tìm kiếm giấy tờ vệ sinh ATTP qua AJAX
     */
    public function filterCertificates() {
        $search = $_GET['q'] ?? '';
        $docType = $_GET['doc_type'] ?? '';
        $status = $_GET['status'] ?? '';

        try {
            $foodsafetyModel = new foodsafetyModel();
            $certificates = $foodsafetyModel->getCertificates(null, $docType ?: null, $status ?: null, $search ?: null);

            ob_start();
            require DIR_TEMPLATE . '/backend/foodsafety/table_rows.php';
            $html = ob_get_clean();

            $queryString = http_build_query([
                'q' => $search,
                'doc_type' => $docType,
                'status' => $status
            ]);

            $this->response([
                'status' => 200,
                'total' => count($certificates),
                'html' => $html,
                'queryString' => $queryString
            ]);
        } catch (Exception $e) {
            $this->response([
                'status' => 500,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API lấy chi tiết một giấy tờ vệ sinh ATTP (AJAX GET)
     */
    public function getCertificateDetail() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->response(['status' => 400, 'message' => 'Thiếu ID giấy tờ.'], 400);
        }
        try {
            $foodsafetyModel = new foodsafetyModel();
            $cert = $foodsafetyModel->getById($id);
            if (!$cert) {
                $this->response(['status' => 404, 'message' => 'Không tìm thấy giấy tờ.'], 404);
            }
            $this->response([
                'status' => 200,
                'data' => $cert
            ]);
        } catch (Exception $e) {
            $this->response([
                'status' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API thêm giấy tờ vệ sinh ATTP mới (AJAX POST)
     */
    public function addCertificate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->apiResponse('create', 'certificate', false, 'method_not_allowed', 405);
        }

        $data = [
            'trader_id'   => $_POST['trader_id'] ?? '',
            'doc_type'    => $_POST['doc_type'] ?? '',
            'doc_number'  => $_POST['doc_number'] ?? '',
            'name'        => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'issuer'      => $_POST['issuer'] ?? '',
            'issue_date'  => $_POST['issue_date'] ?? '',
            'expiry_date' => $_POST['expiry_date'] ?? '',
        ];

        // Validation các trường bắt buộc
        $errors = [];
        if (empty($data['trader_id'])) $errors[] = 'Bạn phải chọn tiểu thương.';
        if (empty($data['doc_type'])) $errors[] = 'Bạn phải chọn loại giấy tờ.';
        if (empty($data['doc_number'])) $errors[] = 'Bạn phải nhập số giấy tờ/chứng nhận.';
        if (empty($data['name'])) $errors[] = 'Bạn phải nhập tên giấy tờ.';
        if (empty($data['issue_date'])) $errors[] = 'Bạn phải nhập ngày hiệu lực bắt đầu.';
        if (empty($data['expiry_date'])) $errors[] = 'Bạn phải nhập ngày hiệu lực kết thúc.';

        if (!empty($errors)) {
            $this->apiResponse('create', 'certificate', false, reset($errors), 400);
        }

        if (strtotime($data['issue_date']) > strtotime($data['expiry_date'])) {
            $this->apiResponse('create', 'certificate', false, 'Ngày hiệu lực bắt đầu không được lớn hơn ngày hết hạn.', 400);
        }

        try {
            $db = database::getInstance();
            $checkNum = $db->selectOne("SELECT COUNT(*) as count FROM trader_attp WHERE doc_number = :num AND status_id != (SELECT id FROM system_statuses WHERE domain = 'attp' AND code = '99')", ['num' => $data['doc_number']]);
            if (($checkNum['count'] ?? 0) > 0) {
                $this->apiResponse('create', 'certificate', false, 'Số giấy tờ/chứng nhận này đã tồn tại trên hệ thống.', 400);
            }

            // Xử lý upload file đính kèm (nếu có)
            if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploader = new upload('foodsafety', ['jpg', 'jpeg', 'png', 'pdf'], 15);
                $savedFile = $uploader->save('certificate_file');
                if ($savedFile === false) {
                    $errors = $uploader->getErrors();
                    $this->apiResponse('create', 'certificate', false, 'Lỗi tải file đính kèm: ' . reset($errors), 400);
                }
                $data['file'] = $savedFile;
            }

            $foodsafetyModel = new foodsafetyModel();
            $foodsafetyModel->createCertificate($data);
            $this->apiResponse('create', 'certificate', true);
        } catch (Exception $e) {
            $this->apiResponse('create', 'certificate', false, $e->getMessage(), 500);
        }
    }

    /**
     * API cập nhật giấy tờ vệ sinh ATTP (AJAX POST)
     */
    public function editCertificate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->apiResponse('update', 'certificate', false, 'method_not_allowed', 405);
        }

        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            $this->apiResponse('update', 'certificate', false, 'Thiếu ID giấy tờ.', 400);
        }

        $data = [
            'trader_id'   => $_POST['trader_id'] ?? '',
            'doc_type'    => $_POST['doc_type'] ?? '',
            'doc_number'  => $_POST['doc_number'] ?? '',
            'name'        => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'issuer'      => $_POST['issuer'] ?? '',
            'issue_date'  => $_POST['issue_date'] ?? '',
            'expiry_date' => $_POST['expiry_date'] ?? '',
        ];

        // Validation các trường bắt buộc
        $errors = [];
        if (empty($data['trader_id'])) $errors[] = 'Bạn phải chọn tiểu thương.';
        if (empty($data['doc_type'])) $errors[] = 'Bạn phải chọn loại giấy tờ.';
        if (empty($data['doc_number'])) $errors[] = 'Bạn phải nhập số giấy tờ/chứng nhận.';
        if (empty($data['name'])) $errors[] = 'Bạn phải nhập tên giấy tờ.';
        if (empty($data['issue_date'])) $errors[] = 'Bạn phải nhập ngày hiệu lực bắt đầu.';
        if (empty($data['expiry_date'])) $errors[] = 'Bạn phải nhập ngày hiệu lực kết thúc.';

        if (!empty($errors)) {
            $this->apiResponse('update', 'certificate', false, reset($errors), 400);
        }

        if (strtotime($data['issue_date']) > strtotime($data['expiry_date'])) {
            $this->apiResponse('update', 'certificate', false, 'Ngày hiệu lực bắt đầu không được lớn hơn ngày hết hạn.', 400);
        }

        try {
            $db = database::getInstance();
            $checkNum = $db->selectOne("SELECT COUNT(*) as count FROM trader_attp WHERE doc_number = :num AND id != :id AND status_id != (SELECT id FROM system_statuses WHERE domain = 'attp' AND code = '99')", ['num' => $data['doc_number'], 'id' => $id]);
            if (($checkNum['count'] ?? 0) > 0) {
                $this->apiResponse('update', 'certificate', false, 'Số giấy tờ/chứng nhận này đã tồn tại trên hệ thống.', 400);
            }

            // Xử lý upload file đính kèm (nếu có)
            if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploader = new upload('foodsafety', ['jpg', 'jpeg', 'png', 'pdf'], 15);
                $savedFile = $uploader->save('certificate_file');
                if ($savedFile === false) {
                    $errors = $uploader->getErrors();
                    $this->apiResponse('update', 'certificate', false, 'Lỗi tải file đính kèm: ' . reset($errors), 400);
                }
                $data['file'] = $savedFile;
            }

            // Tự động kiểm tra hạn để cập nhật status_id
            $today = date('Y-m-d');
            $statusModel = new statusModel();
            if ($data['expiry_date'] < $today) {
                $data['status_id'] = $statusModel->getIdByCode('attp', 'expired');
            } else {
                $data['status_id'] = $statusModel->getIdByCode('attp', 'valid');
            }

            $foodsafetyModel = new foodsafetyModel();
            $foodsafetyModel->updateCertificate($id, $data);
            $this->apiResponse('update', 'certificate', true);
        } catch (Exception $e) {
            $this->apiResponse('update', 'certificate', false, $e->getMessage(), 500);
        }
    }

    /**
     * API xóa mềm giấy tờ vệ sinh ATTP (AJAX POST)
     */
    public function deleteCertificate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->apiResponse('delete', 'certificate', false, 'method_not_allowed', 405);
        }

        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            $this->apiResponse('delete', 'certificate', false, 'Thiếu ID giấy tờ.', 400);
        }

        try {
            $foodsafetyModel = new foodsafetyModel();
            $foodsafetyModel->deleteCertificate($id);
            $this->apiResponse('delete', 'certificate', true);
        } catch (Exception $e) {
            $this->apiResponse('delete', 'certificate', false, $e->getMessage(), 500);
        }
    }

    //--------------BẮT ĐẦU SƠ ĐỒ CHỢ TƯƠNG TÁC--------------//

    /**
     * API lấy danh sách các phần tử bản đồ (AJAX GET)
     */
    public function getMapElements() {
        try {
            $mapModel = new mapModel();
            $elements = $mapModel->getElements();
            $this->response([
                'status' => 200,
                'data' => $elements
            ]);
        } catch (Exception $e) {
            $this->response([
                'status' => 500,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * API lưu cấu hình các phần tử bản đồ (AJAX POST)
     */
    public function saveMapElements() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->response(['status' => 405, 'message' => 'Phương thức không được hỗ trợ.'], 405);
        }

        // Đọc dữ liệu JSON gửi lên
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if ($data === null || !isset($data['elements'])) {
            $this->response(['status' => 400, 'message' => 'Dữ liệu sơ đồ không hợp lệ.'], 400);
        }

        try {
            $mapModel = new mapModel();
            $mapModel->saveElements($data['elements']);
            $this->response([
                'status' => 200,
                'message' => 'Lưu sơ đồ chợ thành công!'
            ]);
        } catch (Exception $e) {
            $this->response([
                'status' => 500,
                'message' => 'Lưu sơ đồ thất bại: ' . $e->getMessage()
            ], 500);
        }
    }

    //--------------KẾT THÚC SƠ ĐỒ CHỢ TƯƠNG TÁC--------------//



}
