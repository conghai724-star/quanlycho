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
        $this->abort405('POST', 'create', 'stall');

        $data = [
            'area_id'       => $_POST['area_id'] ?? '',
            'stall_code'    => $_POST['stall_code'] ?? '',
            'stall_type_id' => $_POST['stall_type_id'] ?? '',
            'area_size'     => $_POST['area_size'] ?? '',
            'base_price'    => $_POST['base_price'] ?? '',
            'status_id'     => $_POST['status'] ?? 3
        ];

        $validator = new validator();
        $validator->required('area_id', $data['area_id'], 'Khu vực không được để trống.')
                  ->required('stall_code', $data['stall_code'], 'Mã sạp không được để trống.')
                  ->required('stall_type_id', $data['stall_type_id'], 'Vui lòng chọn loại sạp.')
                  ->required('area_size', $data['area_size'], 'Diện tích không được để trống.')
                  ->numeric('area_size', $data['area_size'], 'Diện tích phải là dạng số.')
                  ->min('area_size', $data['area_size'], 0.01, 'Diện tích phải lớn hơn 0.')
                  ->required('base_price', $data['base_price'], 'Đơn giá thuê không được để trống.')
                  ->numeric('base_price', $data['base_price'], 'Đơn giá thuê phải là dạng số.')
                  ->min('base_price', $data['base_price'], 0, 'Đơn giá thuê không được âm.');

        $this->abort400($validator, 'create', 'stall');

        try {
            $stallModel = new stallModel();
            $this->abort400(!$stallModel->isStallCodeExists($data['stall_code']), 'create', 'stall', 'Mã sạp đã tồn tại trên hệ thống');

            $stallModel->create($data);
            $this->apiResponse('create', 'stall', true);
        } catch (Exception $e) {
            $this->abort500($e, 'create', 'stall');
        }
    }

    /**
     * API cập nhật sạp (AJAX POST)
     */
    public function editStall() {
        $this->abort405('POST', 'update', 'stall');
        $this->abort400('id', 'update', 'stall');

        $id = $_POST['id'];

        $data = [
            'area_id'       => $_POST['area_id'] ?? '',
            'stall_code'    => $_POST['stall_code'] ?? '',
            'stall_type_id' => $_POST['stall_type_id'] ?? '',
            'area_size'     => $_POST['area_size'] ?? '',
            'base_price'    => $_POST['base_price'] ?? '',
            'status_id'     => $_POST['status'] ?? 3
        ];

        $validator = new validator();
        $validator->required('area_id', $data['area_id'], 'Khu vực không được để trống.')
                  ->required('stall_code', $data['stall_code'], 'Mã sạp không được để trống.')
                  ->required('stall_type_id', $data['stall_type_id'], 'Vui lòng chọn loại sạp.')
                  ->required('area_size', $data['area_size'], 'Diện tích không được để trống.')
                  ->numeric('area_size', $data['area_size'], 'Diện tích phải là dạng số.')
                  ->min('area_size', $data['area_size'], 0.01, 'Diện tích phải lớn hơn 0.')
                  ->required('base_price', $data['base_price'], 'Đơn giá thuê không được để trống.')
                  ->numeric('base_price', $data['base_price'], 'Đơn giá thuê phải là dạng số.')
                  ->min('base_price', $data['base_price'], 0, 'Đơn giá thuê không được âm.');

        $this->abort400($validator, 'update', 'stall');

        try {
            $stallModel = new stallModel();
            $this->abort404($stallModel, 'getById', $id, 'update', 'stall');

            $this->abort400(!$stallModel->isStallCodeExists($data['stall_code'], $id), 'update', 'stall', 'Mã sạp đã tồn tại trên hệ thống');

            $stallModel->update($id, $data);
            $this->apiResponse('update', 'stall', true);
        } catch (Exception $e) {
            $this->abort500($e, 'update', 'stall');
        }
    }

    /**
     * API xóa sạp (AJAX POST)
     */
    public function deleteStall() {
        $this->abort405('POST', 'delete', 'stall');
        $this->abort400('id', 'delete', 'stall');

        $id = $_POST['id'];

        try {
            $stallModel = new stallModel();
            $this->abort404($stallModel, 'getById', $id, 'delete', 'stall');

            $this->abort400(!$stallModel->hasActiveContract($id), 'delete', 'stall', 'Sạp đang có hợp đồng hoạt động, không thể xóa.');

            $stallModel->delete($id);
            $this->apiResponse('delete', 'stall', true);
        } catch (Exception $e) {
            $this->abort500($e, 'delete', 'stall');
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
            $stallModel = new stallModel();
            $stalls = $stallModel->getAvailableStallsForTransfer($excludeId);
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
            $traderModel = new traderModel();
            $traders = $traderModel->getAvailableTraders();
            $this->response($traders);
        } catch (Exception $e) {
            $this->response(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API gán sạp nhanh cho tiểu thương (AJAX POST)
     */
    public function assignStall() {
        $this->abort405('POST', 'create', 'contract');
        $this->abort400(['stall_id', 'trader_id'], 'create', 'contract', 'Vui lòng chọn đầy đủ sạp và tiểu thương.');

        $stallId = $_POST['stall_id'];
        $traderId = $_POST['trader_id'];

        try {
            $stallModel = new stallModel();
            $stall = $stallModel->getById($stallId);
            $this->abort400($stall && $stall['status'] === 'empty', 'create', 'contract', 'Sạp này không còn trống để cho thuê.');

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
            $this->abort500($e, 'create', 'contract');
        }
    }

    /**
     * API chuyển đổi sạp của tiểu thương (AJAX POST)
     */
    public function transferStall() {
        $this->abort405('POST', 'update', 'stall');
        $this->abort400(['current_stall_id', 'new_stall_id'], 'update', 'stall', 'Thiếu thông tin sạp hiện tại hoặc sạp mới.');

        $currentStallId = $_POST['current_stall_id'];
        $newStallId = $_POST['new_stall_id'];

        try {
            $stallModel = new stallModel();
            $currentStall = $stallModel->getById($currentStallId);
            $newStall = $stallModel->getById($newStallId);
            $this->abort400($currentStall && $newStall, 'update', 'stall', 'Không tìm thấy thông tin sạp.');

            $contractModel = new contractModel();
            $contract1 = $contractModel->getActiveContractByStall($currentStallId);
            $this->abort400($contract1 !== null && $contract1 !== false, 'update', 'stall', 'Không tìm thấy hợp đồng đang hoạt động cho sạp hiện tại.');

            $message = $stallModel->transferStall($currentStall, $newStall, $contract1);

            $this->response([
                'status' => 200,
                'message' => $message
            ]);
        } catch (Exception $e) {
            $this->abort500($e, 'update', 'stall');
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
        $this->abort405('POST', 'create', 'contract');

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

        $this->abort400($validator, 'create', 'contract');
        $this->abort400(strtotime($data['start_date']) <= strtotime($data['end_date']), 'create', 'contract', 'Ngày bắt đầu không được lớn hơn ngày kết thúc.');

        try {
            $contractModel = new contractModel();
            
            // Kiểm tra trùng số hợp đồng
            $this->abort400(!$contractModel->isContractNumberExists($data['contract_number']), 'create', 'contract', 'Số hợp đồng này đã tồn tại trên hệ thống.');

            // Kiểm tra xem sạp có đang trống hay không
            $stallModel = new stallModel();
            $stall = $stallModel->getById($data['stall_id']);
            $this->abort400($stall && $stall['status'] === 'empty', 'create', 'contract', 'Sạp được chọn không còn trống để cho thuê.');

            // Xử lý upload file PDF đính kèm (nếu có)
            if (isset($_FILES['contract_file']) && $_FILES['contract_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploader = new upload('contracts', ['pdf'], 15); // Chỉ nhận file PDF
                $savedFile = $uploader->save('contract_file');
                $this->abort400($savedFile !== false, 'create', 'contract', 'Lỗi tải file hợp đồng: ' . reset($uploader->getErrors()));
                $data['contract_file'] = $savedFile;
            }

            $contractModel->create($data);
            $this->apiResponse('create', 'contract', true);
        } catch (Exception $e) {
            $this->abort500($e, 'create', 'contract');
        }
    }

    /**
     * API gia hạn hợp đồng (AJAX POST)
     */
    public function renewContract() {
        $this->abort405('POST', 'update', 'contract');
        $this->abort400(['contract_id', 'new_end_date'], 'update', 'contract', 'Thiếu thông tin gia hạn.');

        $contractId = $_POST['contract_id'];
        $newEndDate = $_POST['new_end_date'];

        try {
            $contractModel = new contractModel();
            $contract = $this->abort404($contractModel, 'getById', $contractId, 'update', 'contract');

            $this->abort400(strtotime($newEndDate) > strtotime($contract['end_date']), 'update', 'contract', 'Ngày gia hạn mới phải sau ngày hết hạn hiện tại (' . $contract['end_date'] . ').');

            $contractModel->renew($contractId, $newEndDate);
            $this->apiResponse('update', 'contract', true);
        } catch (Exception $e) {
            $this->abort500($e, 'update', 'contract');
        }
    }

    /**
     * API thanh lý hợp đồng (AJAX POST)
     */
    public function liquidateContract() {
        $this->abort405('POST', 'update', 'contract');
        $this->abort400('contract_id', 'update', 'contract');

        $contractId = $_POST['contract_id'];

        try {
            $contractModel = new contractModel();
            $this->abort404($contractModel, 'getById', $contractId, 'update', 'contract');

            $contractModel->liquidate($contractId);
            $this->apiResponse('update', 'contract', true);
        } catch (Exception $e) {
            $this->abort500($e, 'update', 'contract');
        }
    }

    /**
     * API chấm dứt hợp đồng trước hạn (AJAX POST)
     */
    public function terminateContract() {
        $this->abort405('POST', 'update', 'contract');
        $this->abort400('contract_id', 'update', 'contract');

        $contractId = $_POST['contract_id'];

        try {
            $contractModel = new contractModel();
            $this->abort404($contractModel, 'getById', $contractId, 'update', 'contract');

            $contractModel->terminate($contractId);
            $this->apiResponse('update', 'contract', true);
        } catch (Exception $e) {
            $this->abort500($e, 'update', 'contract');
        }
    }

    /**
     * API xóa mềm hợp đồng (AJAX POST - status_id = 99)
     */
    public function deleteContract() {
        $this->abort405('POST', 'delete', 'contract');
        $this->abort400('contract_id', 'delete', 'contract');

        $contractId = $_POST['contract_id'];

        try {
            $contractModel = new contractModel();
            $this->abort404($contractModel, 'getById', $contractId, 'delete', 'contract');

            $contractModel->softDelete($contractId);
            $this->apiResponse('delete', 'contract', true);
        } catch (Exception $e) {
            $this->abort500($e, 'delete', 'contract');
        }
    }

    /**
     * API thêm phụ lục hợp đồng (AJAX POST)
     */
    public function addContractAppendix() {
        $this->abort405('POST', 'create', 'appendix');

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

        $this->abort400($validator, 'create', 'appendix');

        try {
            $contractModel = new contractModel();
            // Kiểm tra trùng số phụ lục
            $this->abort400(!$contractModel->isAppendixNumberExists($data['appendix_number']), 'create', 'appendix', 'Số phụ lục này đã tồn tại trên hệ thống.');

            // Xử lý upload file phụ lục (nếu có)
            if (isset($_FILES['appendix_file']) && $_FILES['appendix_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploader = new upload('contracts/appendices', ['jpg', 'jpeg', 'png', 'pdf'], 15);
                $savedFile = $uploader->save('appendix_file');
                $this->abort400($savedFile !== false, 'create', 'appendix', 'Lỗi tải file phụ lục: ' . reset($uploader->getErrors()));
                $data['file'] = $savedFile;
            }

            $contractModel->addAppendix($data);
            $this->apiResponse('create', 'appendix', true);
        } catch (Exception $e) {
            $this->abort500($e, 'create', 'appendix');
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
    /**
     * API thêm giấy tờ vệ sinh ATTP mới (AJAX POST)
     */
    public function addCertificate() {
        $this->abort405('POST', 'create', 'certificate');

        $data = [
            'trader_id'   => $_POST['trader_id'] ?? '',
            'doc_type_id' => $_POST['doc_type_id'] ?? '',
            'doc_number'  => $_POST['doc_number'] ?? '',
            'name'        => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'issuer'      => $_POST['issuer'] ?? '',
            'issue_date'  => $_POST['issue_date'] ?? '',
            'expiry_date' => $_POST['expiry_date'] ?? '',
        ];

        // Validation các trường bắt buộc
        $validator = new validator();
        $validator->required('trader_id', $data['trader_id'], 'Bạn phải chọn tiểu thương.')
                  ->required('doc_type_id', $data['doc_type_id'], 'Bạn phải chọn loại giấy tờ.')
                  ->required('doc_number', $data['doc_number'], 'Bạn phải nhập số giấy tờ/chứng nhận.')
                  ->required('name', $data['name'], 'Bạn phải nhập tên giấy tờ.')
                  ->required('issue_date', $data['issue_date'], 'Bạn phải nhập ngày hiệu lực bắt đầu.')
                  ->required('expiry_date', $data['expiry_date'], 'Bạn phải nhập ngày hiệu lực kết thúc.');

        $this->abort400($validator, 'create', 'certificate');
        $this->abort400(strtotime($data['issue_date']) <= strtotime($data['expiry_date']), 'create', 'certificate', 'Ngày hiệu lực bắt đầu không được lớn hơn ngày hết hạn.');

        try {
            $foodsafetyModel = new foodsafetyModel();
            $this->abort400(!$foodsafetyModel->isDocNumberExists($data['doc_number']), 'create', 'certificate', 'Số giấy tờ/chứng nhận này đã tồn tại trên hệ thống.');

            // Xử lý upload file đính kèm (nếu có)
            if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploader = new upload('foodsafety', ['jpg', 'jpeg', 'png', 'pdf'], 15);
                $savedFile = $uploader->save('certificate_file');
                $this->abort400($savedFile !== false, 'create', 'certificate', 'Lỗi tải file đính kèm: ' . reset($uploader->getErrors()));
                $data['file'] = $savedFile;
            }

            $foodsafetyModel->createCertificate($data);
            $this->apiResponse('create', 'certificate', true);
        } catch (Exception $e) {
            $this->abort500($e, 'create', 'certificate');
        }
    }

    /**
     * API cập nhật giấy tờ vệ sinh ATTP (AJAX POST)
     */
    public function editCertificate() {
        $this->abort405('POST', 'update', 'certificate');
        $this->abort400('id', 'update', 'certificate');

        $id = $_POST['id'];

        $data = [
            'trader_id'   => $_POST['trader_id'] ?? '',
            'doc_type_id' => $_POST['doc_type_id'] ?? '',
            'doc_number'  => $_POST['doc_number'] ?? '',
            'name'        => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'issuer'      => $_POST['issuer'] ?? '',
            'issue_date'  => $_POST['issue_date'] ?? '',
            'expiry_date' => $_POST['expiry_date'] ?? '',
        ];

        // Validation các trường bắt buộc
        $validator = new validator();
        $validator->required('trader_id', $data['trader_id'], 'Bạn phải chọn tiểu thương.')
                  ->required('doc_type_id', $data['doc_type_id'], 'Bạn phải chọn loại giấy tờ.')
                  ->required('doc_number', $data['doc_number'], 'Bạn phải nhập số giấy tờ/chứng nhận.')
                  ->required('name', $data['name'], 'Bạn phải nhập tên giấy tờ.')
                  ->required('issue_date', $data['issue_date'], 'Bạn phải nhập ngày hiệu lực bắt đầu.')
                  ->required('expiry_date', $data['expiry_date'], 'Bạn phải nhập ngày hiệu lực kết thúc.');

        $this->abort400($validator, 'update', 'certificate');
        $this->abort400(strtotime($data['issue_date']) <= strtotime($data['expiry_date']), 'update', 'certificate', 'Ngày hiệu lực bắt đầu không được lớn hơn ngày hết hạn.');

        try {
            $foodsafetyModel = new foodsafetyModel();
            $this->abort400(!$foodsafetyModel->isDocNumberExists($data['doc_number'], $id), 'update', 'certificate', 'Số giấy tờ/chứng nhận này đã tồn tại trên hệ thống.');

            // Xử lý upload file đính kèm (nếu có)
            if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploader = new upload('foodsafety', ['jpg', 'jpeg', 'png', 'pdf'], 15);
                $savedFile = $uploader->save('certificate_file');
                $this->abort400($savedFile !== false, 'update', 'certificate', 'Lỗi tải file đính kèm: ' . reset($uploader->getErrors()));
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

            $foodsafetyModel->updateCertificate($id, $data);
            $this->apiResponse('update', 'certificate', true);
        } catch (Exception $e) {
            $this->abort500($e, 'update', 'certificate');
        }
    }

    /**
     * API xóa mềm giấy tờ vệ sinh ATTP (AJAX POST)
     */
    public function deleteCertificate() {
        $this->abort405('POST', 'delete', 'certificate');
        $this->abort400('id', 'delete', 'certificate');

        $id = $_POST['id'];

        try {
            $foodsafetyModel = new foodsafetyModel();
            $foodsafetyModel->deleteCertificate($id);
            $this->apiResponse('delete', 'certificate', true);
        } catch (Exception $e) {
            $this->abort500($e, 'delete', 'certificate');
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

    //--------------BẮT ĐẦU QUẢN LÝ DANH MỤC--------------//

    /**
     * API lấy chi tiết một danh mục (AJAX GET)
     */
    public function getCategoryDetail() {
        $this->abort405('GET', 'view', 'category');
        
        $id = $_GET['id'] ?? '';
        $type = $_GET['type'] ?? '';
        
        $this->abort400($id && $type, 'view', 'category', 'Thiếu ID hoặc Loại danh mục.');

        try {
            $categoryModel = new categoryModel();
            $item = $categoryModel->getItemById($type, $id);
            if (!$item) {
                $this->httpAbortResponse('view', 'category', false, 'not_found', 404);
            }

            $this->response([
                'status' => 200,
                'data' => $item
            ]);
        } catch (Exception $e) {
            $this->abort500($e, 'view', 'category');
        }
    }

    /**
     * API thêm danh mục mới (AJAX POST)
     */
    public function addCategory() {
        $this->abort405('POST', 'create', 'category');
        $this->abort400('type', 'create', 'category', 'Thiếu loại danh mục.');

        $type = $_POST['type'];
        $categoryModel = new categoryModel();

        try {
            $data = [];
            $validator = new validator();

            if ($type === 'area') {
                $data = [
                    'area_name'   => $_POST['area_name'] ?? '',
                    'block'       => $_POST['block'] ?? '',
                    'lot'         => $_POST['lot'] ?? '',
                    'description' => $_POST['description'] ?? ''
                ];
                $validator->required('area_name', $data['area_name'], 'Tên khu vực không được để trống.');
                $this->abort400($validator, 'create', 'category');
                
                // Kiểm tra trùng tên
                $this->abort400(!$categoryModel->isCodeExists('area', 'area_name', $data['area_name']), 'create', 'category', 'Tên khu vực này đã tồn tại.');

            } elseif ($type === 'stall_type') {
                $data = [
                    'type_code'   => $_POST['type_code'] ?? '',
                    'type_name'   => $_POST['type_name'] ?? '',
                    'description' => $_POST['description'] ?? ''
                ];
                $validator->required('type_code', $data['type_code'], 'Mã loại sạp không được để trống.')
                          ->required('type_name', $data['type_name'], 'Tên loại sạp không được để trống.');
                $this->abort400($validator, 'create', 'category');

                $this->abort400(!$categoryModel->isCodeExists('stall_type', 'type_code', $data['type_code']), 'create', 'category', 'Mã loại sạp này đã tồn tại.');

            } elseif ($type === 'business_line') {
                $data = [
                    'line_code'   => $_POST['line_code'] ?? '',
                    'line_name'   => $_POST['line_name'] ?? '',
                    'description' => $_POST['description'] ?? ''
                ];
                $validator->required('line_code', $data['line_code'], 'Mã ngành hàng không được để trống.')
                          ->required('line_name', $data['line_name'], 'Tên ngành hàng không được để trống.');
                $this->abort400($validator, 'create', 'category');

                $this->abort400(!$categoryModel->isCodeExists('business_line', 'line_code', $data['line_code']), 'create', 'category', 'Mã ngành hàng này đã tồn tại.');

            } elseif ($type === 'document_type') {
                $data = [
                    'type_code'   => $_POST['type_code'] ?? '',
                    'type_name'   => $_POST['type_name'] ?? '',
                    'description' => $_POST['description'] ?? ''
                ];
                $validator->required('type_code', $data['type_code'], 'Mã loại giấy tờ không được để trống.')
                          ->required('type_name', $data['type_name'], 'Tên loại giấy tờ không được để trống.');
                $this->abort400($validator, 'create', 'category');

                $this->abort400(!$categoryModel->isCodeExists('document_type', 'type_code', $data['type_code']), 'create', 'category', 'Mã loại giấy tờ này đã tồn tại.');
            } else {
                $this->abort400(false, 'create', 'category', 'Loại danh mục không hợp lệ.');
            }

            $itemId = $categoryModel->createItem($type, $data);
            $this->response([
                'status' => 200,
                'message' => 'Thêm mới danh mục thành công!',
                'id' => $itemId
            ]);
        } catch (Exception $e) {
            $this->abort500($e, 'create', 'category');
        }
    }

    /**
     * API cập nhật danh mục (AJAX POST)
     */
    public function editCategory() {
        $this->abort405('POST', 'update', 'category');
        $this->abort400(['id', 'type'], 'update', 'category', 'Thiếu thông tin danh mục.');

        $id = $_POST['id'];
        $type = $_POST['type'];
        $categoryModel = new categoryModel();

        // Kiểm tra tồn tại
        $item = $categoryModel->getItemById($type, $id);
        if (!$item) {
            $this->httpAbortResponse('update', 'category', false, 'not_found', 404);
        }

        try {
            $data = [];
            $validator = new validator();

            if ($type === 'area') {
                $data = [
                    'area_name'   => $_POST['area_name'] ?? '',
                    'block'       => $_POST['block'] ?? '',
                    'lot'         => $_POST['lot'] ?? '',
                    'description' => $_POST['description'] ?? ''
                ];
                $validator->required('area_name', $data['area_name'], 'Tên khu vực không được để trống.');
                $this->abort400($validator, 'update', 'category');
                
                $this->abort400(!$categoryModel->isCodeExists('area', 'area_name', $data['area_name'], $id), 'update', 'category', 'Tên khu vực này đã tồn tại.');

            } elseif ($type === 'stall_type') {
                $data = [
                    'type_code'   => $_POST['type_code'] ?? '',
                    'type_name'   => $_POST['type_name'] ?? '',
                    'description' => $_POST['description'] ?? ''
                ];
                $validator->required('type_code', $data['type_code'], 'Mã loại sạp không được để trống.')
                          ->required('type_name', $data['type_name'], 'Tên loại sạp không được để trống.');
                $this->abort400($validator, 'update', 'category');

                $this->abort400(!$categoryModel->isCodeExists('stall_type', 'type_code', $data['type_code'], $id), 'update', 'category', 'Mã loại sạp này đã tồn tại.');

            } elseif ($type === 'business_line') {
                $data = [
                    'line_code'   => $_POST['line_code'] ?? '',
                    'line_name'   => $_POST['line_name'] ?? '',
                    'description' => $_POST['description'] ?? ''
                ];
                $validator->required('line_code', $data['line_code'], 'Mã ngành hàng không được để trống.')
                          ->required('line_name', $data['line_name'], 'Tên ngành hàng không được để trống.');
                $this->abort400($validator, 'update', 'category');

                $this->abort400(!$categoryModel->isCodeExists('business_line', 'line_code', $data['line_code'], $id), 'update', 'category', 'Mã ngành hàng này đã tồn tại.');

            } elseif ($type === 'document_type') {
                $data = [
                    'type_code'   => $_POST['type_code'] ?? '',
                    'type_name'   => $_POST['type_name'] ?? '',
                    'description' => $_POST['description'] ?? ''
                ];
                $validator->required('type_code', $data['type_code'], 'Mã loại giấy tờ không được để trống.')
                          ->required('type_name', $data['type_name'], 'Tên loại giấy tờ không được để trống.');
                $this->abort400($validator, 'update', 'category');

                $this->abort400(!$categoryModel->isCodeExists('document_type', 'type_code', $data['type_code'], $id), 'update', 'category', 'Mã loại giấy tờ này đã tồn tại.');
            } else {
                $this->abort400(false, 'update', 'category', 'Loại danh mục không hợp lệ.');
            }

            $categoryModel->updateItem($type, $id, $data);
            $this->response([
                'status' => 200,
                'message' => 'Cập nhật danh mục thành công!'
            ]);
        } catch (Exception $e) {
            $this->abort500($e, 'update', 'category');
        }
    }

    /**
     * API xóa danh mục (AJAX POST)
     */
    public function deleteCategory() {
        $this->abort405('POST', 'delete', 'category');
        $this->abort400(['id', 'type'], 'delete', 'category', 'Thiếu thông tin danh mục cần xóa.');

        $id = $_POST['id'];
        $type = $_POST['type'];
        $categoryModel = new categoryModel();

        // Kiểm tra tồn tại
        $item = $categoryModel->getItemById($type, $id);
        if (!$item) {
            $this->httpAbortResponse('delete', 'category', false, 'not_found', 404);
        }

        try {
            $categoryModel->deleteItem($type, $id);
            $this->response([
                'status' => 200,
                'message' => 'Xóa danh mục thành công!'
            ]);
        } catch (Exception $e) {
            $this->response([
                'status' => 400,
                'message' => $e->getMessage()
            ], 400);
        }
    }

}
