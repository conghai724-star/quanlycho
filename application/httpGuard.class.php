<?php
/**
 * Trait httpGuard - Hỗ trợ các hàm kiểm tra lỗi HTTP nhanh (HTTP Guard/Assertion Helpers)
 * Có thể tái sử dụng dễ dàng trong mọi Controller thuộc các dự án PHP khác nhau.
 */
trait httpGuard {

    /**
     * Chặn và trả về lỗi 405 Method Not Allowed nếu sai phương thức HTTP
     */
    protected function abort405($expectedMethod, $action, $entity) {
        if ($_SERVER['REQUEST_METHOD'] !== strtoupper($expectedMethod)) {
            $this->httpAbortResponse($action, $entity, false, 'method_not_allowed', 405);
        }
    }

    /**
     * Chặn và trả về lỗi 403 Forbidden nếu điều kiện không thỏa mãn
     */
    protected function abort403($condition, $action, $entity, $detail = 'Bạn không có quyền thực hiện hành động này.') {
        if (!$condition) {
            $this->httpAbortResponse($action, $entity, false, $detail, 403);
        }
    }

    /**
     * Chặn và trả về lỗi 404 Not Found nếu không tìm thấy bản ghi.
     * Trả về dữ liệu bản ghi nếu tìm thấy.
     */
    protected function abort404($model, $method, $id, $action, $entity) {
        $record = $model->$method($id);
        if (!$record) {
            $this->httpAbortResponse($action, $entity, false, 'not_found', 404);
        }
        return $record;
    }

    /**
     * Chặn và trả về lỗi 400 Bad Request đa năng:
     * - Nếu $check là chuỗi hoặc mảng: Kiểm tra xem các tham số request tương ứng có bị thiếu/trống không.
     * - Nếu $check là đối tượng validator: Kiểm tra tính hợp lệ và lấy lỗi đầu tiên.
     * - Nếu $check là biểu thức boolean/giá trị khác: Chặn nếu giá trị là false/empty.
     */
    protected function abort400($check, $action, $entity, $detail = '') {
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
     * Phản hồi lỗi HTTP tập trung:
     * Tự động phát hiện và gọi apiResponse của dự án hiện tại (nếu có)
     * hoặc tự xuất JSON lỗi tiêu chuẩn nếu mang sang dự án khác.
     */
    protected function httpAbortResponse($action, $entity, $isSuccess, $detail = '', $statusCode = null) {
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
