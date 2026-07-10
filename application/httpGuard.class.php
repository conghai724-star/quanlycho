<?php
/**
 * Trait httpGuard - Hỗ trợ các hàm kiểm tra lỗi HTTP nhanh (HTTP Guard/Assertion Helpers)
 * Có thể tái sử dụng dễ dàng trong mọi Controller thuộc các dự án PHP khác nhau.
 */
trait httpGuard {

    /**
     * Chặn lỗi 405 Method Not Allowed nếu sai phương thức HTTP
     * 
     * @param $expectedMethod Phương thức mong đợi ('POST', 'GET') - Khai báo tĩnh
     * @param $action Hành động xử lý ('create', 'delete') - Khai báo tĩnh
     * @param $entity Thực thể nghiệp vụ ('trader', 'stall') - Khai báo tĩnh
     */
    protected function abort405(string $expectedMethod, string $action, string $entity) {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($expectedMethod)) {
            $this->httpAbortResponse($action, $entity, false, 'method_not_allowed', 405);
        }
    }

    /**
     * Chặn lỗi 403 Forbidden nếu điều kiện không thỏa mãn
     * 
     * @param $condition Điều kiện để đi tiếp (Ví dụ: kiểm tra quyền) - Biểu thức logic
     * @param $action Hành động xử lý ('create', 'delete') - Khai báo tĩnh
     * @param $entity Thực thể nghiệp vụ ('trader', 'stall') - Khai báo tĩnh
     * @param $detail Thông báo lỗi chi tiết hiển thị cho client - Khai báo tĩnh
     */
    protected function abort403(bool $condition, string $action, string $entity, string $detail = 'Bạn không có quyền thực hiện hành động này.') {
        if (!$condition) {
            $this->httpAbortResponse($action, $entity, false, $detail, 403);
        }
    }

    /**
     * Chặn lỗi 404 Not Found nếu không tìm thấy bản ghi (trả về dữ liệu nếu tìm thấy)
     * 
     * @param $model Đối tượng Model kết nối DB (Ví dụ: new traderModel()) - Khởi tạo từ Controller
     * @param $method Tên hàm truy vấn trong Model (Ví dụ: 'getTraderById') - Khai báo tĩnh
     * @param $id ID của bản ghi cần tìm - Lấy từ request ($_POST['id'] hoặc $_GET['id'])
     * @param $action Hành động xử lý ('delete', 'update') - Khai báo tĩnh
     * @param $entity Thực thể nghiệp vụ ('trader', 'stall') - Khai báo tĩnh
     * @return array Dữ liệu bản ghi tìm thấy
     */
    protected function abort404(object $model, string $method, $id, string $action, string $entity): array {
        $record = $model->$method($id);
        if (!$record) {
            $this->httpAbortResponse($action, $entity, false, 'not_found', 404);
        }
        return $record;
    }

    /**
     * Chặn lỗi 400 Bad Request đa năng (Thiếu tham số, lỗi validator, hoặc điều kiện logic)
     * 
     * @param $check Tham số cần kiểm tra (Chuỗi/Mảng: tham số request; validator: kiểm tra validate; bool: logic)
     * @param $action Hành động xử lý ('create', 'update') - Khai báo tĩnh
     * @param $entity Thực thể nghiệp vụ ('trader', 'stall') - Khai báo tĩnh
     * @param $detail Thông báo lỗi chi tiết khi kiểm tra logic thất bại - Khai báo tĩnh
     */
    protected function abort400($check, string $action, string $entity, string $detail = '') {
        // Trường hợp 1: Kiểm tra thiếu tham số (chuỗi hoặc mảng)
        if (is_string($check) || is_array($check)) {
            $params = is_array($check) ? $check : [$check];
            foreach ($params as $param) {
                $val = $_POST[$param] ?? $_GET[$param] ?? null;
                if ($val === null || (is_string($val) && trim($val) === '')) {
                    $this->httpAbortResponse($action, $entity, false, "missing_{$param}", 400);
                }
            }
            return;
        }

        // Trường hợp 2: Kiểm tra đối tượng validator
        if ($check instanceof validator) {
            if (!$check->isValid()) {
                $errors = $check->getErrors();
                $firstError = reset($errors);
                $this->httpAbortResponse($action, $entity, false, $firstError, 400);
            }
            return;
        }

        // Trường hợp 3: Kiểm tra biểu thức logic (boolean/empty)
        if (!$check) {
            $this->httpAbortResponse($action, $entity, false, $detail, 400);
        }
    }

    /**
     * Phản hồi lỗi HTTP tập trung (Tự động phát hiện apiResponse của dự án hoặc xuất JSON lỗi mặc định)
     * 
     * @param $action Hành động xử lý - Lấy từ hàm abort truyền sang
     * @param $entity Thực thể nghiệp vụ - Lấy từ hàm abort truyền sang
     * @param $isSuccess Trạng thái thành công hay thất bại - Lấy từ hàm abort truyền sang
     * @param $detail Thông báo lỗi chi tiết - Lấy từ hàm abort truyền sang
     * @param $statusCode Mã lỗi HTTP - Lấy từ hàm abort truyền sang
     */
    protected function httpAbortResponse(string $action, string $entity, bool $isSuccess, string $detail = '', ?int $statusCode = null) {
        // Nếu Controller đang dùng có định nghĩa apiResponse thì ưu tiên gọi
        if (method_exists($this, 'apiResponse')) {
            $this->apiResponse($action, $entity, $isSuccess, $detail, $statusCode);
            return;
        }

        // Phản hồi dự phòng tự động (Khi dùng cho dự án khác)
        $code = $statusCode ?? ($isSuccess ? 200 : 400);
        $message = class_exists('message') 
            ? message::result($action, $entity, $isSuccess, $detail) 
            : "{$action}_{$entity}_" . ($isSuccess ? 'success' : 'failed') . ($detail ? ": {$detail}" : "");

        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        echo json_encode([
            'status'  => $code,
            'message' => $message
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }
}
