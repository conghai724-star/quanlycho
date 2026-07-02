<?php
/**
 * Lớp định tuyến (Router) phân tích URL và gọi Controller/Action tương ứng
 */
class router {
    protected $controller = 'homeController';
    protected $action = 'index';
    protected $params = [];

    public function __construct() {
        // Tự động chặn và xác thực token CSRF đối với tất cả các request thay đổi trạng thái (POST, PUT, DELETE)
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
            if (!security::validateToken()) {
                http_response_code(403);
                echo "<div style='font-family: \"Inter\", sans-serif; text-align: center; padding: 50px;'>";
                echo "<h1 style='color: #e74c3c; font-size: 32px;'>403 Forbidden</h1>";
                echo "<p style='color: #666; font-size: 16px;'>Yêu cầu bị từ chối do mã xác thực bảo mật (CSRF Token) không hợp lệ hoặc đã hết hạn.</p>";
                echo "<a href='javascript:history.back()' style='color: #1ABB9C; text-decoration: none; font-weight: 600;'>Quay lại trang trước</a>";
                echo "</div>";
                exit();
            }
        }

        $url = $this->parseUrl();

        // 1. Xác định Controller (mặc định là homeController)
        if (isset($url[0]) && !empty($url[0])) {
            $controllerName = $url[0] . 'Controller';
            // Kiểm tra file controller có tồn tại không
            if (file_exists(DIR_CONTROLLER . '/' . $controllerName . '.php')) {
                $this->controller = $controllerName;
                unset($url[0]);
            } else {
                // Nếu không tồn tại controller tương ứng, có thể trả về lỗi hoặc giữ mặc định
                // Để đơn giản, ta giữ mặc định hoặc chuyển hướng 404 sau này
            }
        }

        // Khởi tạo Controller (Cơ chế Autoload sẽ tự động nạp file)
        if (class_exists($this->controller)) {
            $this->controller = new $this->controller();
        } else {
            throw new Exception("Không tìm thấy class controller: " . $this->controller);
        }

        // 2. Xác định Action (phương thức trong controller, mặc định là index)
        if (isset($url[1]) && !empty($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->action = $url[1];
                unset($url[1]);
            } else {
                // Lỗi không tìm thấy Action
                throw new Exception("Không tìm thấy action '{$url[1]}' trong controller " . get_class($this->controller));
            }
        }

        // 3. Các phần còn lại của URL là tham số (Parameters)
        $this->params = $url ? array_values($url) : [];
    }

    /**
     * Thực thi gọi Controller Action kèm tham số
     */
    public function dispatch() {
        if (method_exists($this->controller, $this->action)) {
            call_user_func_array([$this->controller, $this->action], $this->params);
        } else {
            throw new Exception("Không thể thực thi action '{$this->action}'");
        }
    }

    /**
     * Tách URL thành mảng các phần tử
     */
    private function parseUrl() {
        if (isset($_GET['url'])) {
            // Loại bỏ ký tự / ở cuối và lọc URL hợp lệ
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            return explode('/', $url);
        }
        return [];
    }
}
