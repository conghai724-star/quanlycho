<?php
/**
 * Controller xử lý các trang hiển thị lỗi hệ thống (403, 404, etc.)
 */
class errorsController {

    /**
     * Trang lỗi 403 Forbidden - Truy cập bị từ chối
     */
    public function forbidden() {
        http_response_code(403);
        $this->view('backend/errors/403', [
            'title' => '403 Forbidden - Truy cập bị từ chối'
        ]);
    }

    /**
     * Trang lỗi 404 Not Found - Không tìm thấy trang
     */
    public function notfound() {
        http_response_code(404);
        $this->view('backend/errors/404', [
            'title' => '404 Not Found - Không tìm thấy trang'
        ]);
    }

    /**
     * Hàm render view lỗi
     */
    protected function view($templatePath, $data = []) {
        extract($data);

        // Nạp layout trên
        if (file_exists(DIR_TEMPLATE . '/backend/layouts/header.php')) {
            require_once DIR_TEMPLATE . '/backend/layouts/header.php';
        }

        $viewFile = DIR_TEMPLATE . '/' . $templatePath . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            // Giao diện fallback mặc định (nếu chưa tạo file view)
            echo "<div style='font-family: sans-serif; text-align: center; padding: 100px 20px;'>";
            echo "<h1 style='font-size: 72px; color: #e74c3c; margin: 0;'>403</h1>";
            echo "<h2 style='color: #2c3e50; margin: 10px 0;'>Không có quyền truy cập</h2>";
            echo "<p style='color: #7f8c8d; font-size: 15px; margin-bottom: 20px;'>Tài khoản của bạn không có đủ quyền hạn để truy cập vào khu vực này.</p>";
            echo "<p><a href='" . BASE_URL . "admin/dashboard' style='color: #1abc9c; text-decoration: none; font-weight: bold;'>Quay lại trang chủ</a></p>";
            echo "</div>";
        }

        // Nạp layout dưới
        if (file_exists(DIR_TEMPLATE . '/backend/layouts/footer.php')) {
            require_once DIR_TEMPLATE . '/backend/layouts/footer.php';
        }
    }
}
