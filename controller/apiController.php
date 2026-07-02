<?php
/**
 * Controller xử lý các yêu cầu AJAX / API trả về dữ liệu JSON
 */
class apiController {

    public function __construct() {
        // Chỉ cho phép truy cập API khi đã đăng nhập
        if (!session::isLoggedIn()) {
            $this->response(['error' => 'Chưa đăng nhập hệ thống'], 401);
        }
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

    /**
     * Helper xuất phản hồi JSON
     */
    protected function response($data, $statusCode = 200) {
        // Thiết lập header trả về JSON
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }
}
