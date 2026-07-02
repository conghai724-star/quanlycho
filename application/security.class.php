<?php
/**
 * Lớp quản lý bảo mật hệ thống (CSRF, XSS, etc.)
 */
class security {
    
    /**
     * Lấy token CSRF hiện tại trong Session, nếu chưa có sẽ tự động sinh mới
     */
    public static function getToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            self::generateToken();
        }
        
        return $_SESSION['csrf_token'];
    }

    /**
     * Tự động sinh mới mã token ngẫu nhiên bảo mật cao
     */
    public static function generateToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    /**
     * Làm mới token (chỉ gọi khi login, logout hoặc xoay vòng khóa khi cần thiết)
     */
    public static function regenerateToken() {
        return self::generateToken();
    }

    /**
     * Xác thực token CSRF gửi lên từ POST/PUT/PATCH/DELETE Form hoặc từ AJAX Header
     */
    public static function validateToken($token = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $sessionToken = $_SESSION['csrf_token'] ?? null;
        if (!$sessionToken) {
            return false;
        }

        // 1. Nếu không truyền trực tiếp, tự động tìm trong POST request
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? null;

            // 2. Nếu POST không có, tìm trong Header HTTP X-CSRF-TOKEN (cho AJAX request)
            if (!$token && isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
                $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
            }
        }

        if (!$token) {
            return false;
        }

        // So sánh an toàn tránh tấn công Timing Attack
        return hash_equals($sessionToken, $token);
    }

    /**
     * Tự động kiểm tra và yêu cầu CSRF đối với các request ghi dữ liệu (POST, PUT, PATCH, DELETE)
     */
    public static function requireCsrf() {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            if (!self::validateToken()) {
                http_response_code(403);
                echo "<div style='font-family: \"Inter\", sans-serif; text-align: center; padding: 50px;'>";
                echo "<h1 style='color: #e74c3c; font-size: 32px;'>403 Forbidden</h1>";
                echo "<p style='color: #666; font-size: 16px;'>Yêu cầu bị từ chối do mã xác thực bảo mật (CSRF Token) không hợp lệ hoặc đã hết hạn.</p>";
                echo "<a href='javascript:history.back()' style='color: #1ABB9C; text-decoration: none; font-weight: 600;'>Quay lại trang trước</a>";
                echo "</div>";
                exit();
            }
        }
    }

    /**
     * Sinh thẻ HTML input chứa token CSRF để chèn vào Form
     */
    public static function csrf_field() {
        return '<input type="hidden" name="csrf_token" value="' . self::getToken() . '">';
    }

    /**
     * Sinh thẻ HTML meta chứa token CSRF để dùng cho các request AJAX
     */
    public static function csrf_meta() {
        return '<meta name="csrf-token" content="' . self::getToken() . '">';
    }
}

// Đăng ký các helper functions toàn cục (global helpers) giúp dễ sử dụng trong các template view
if (!function_exists('csrf_field')) {
    function csrf_field() {
        echo security::csrf_field();
    }
}

if (!function_exists('csrf_meta')) {
    function csrf_meta() {
        echo security::csrf_meta();
    }
}
