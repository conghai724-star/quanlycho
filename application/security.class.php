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
        
        // Sử dụng random_bytes để tạo độ ngẫu nhiên an toàn tuyệt đối
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    /**
     * Làm mới token (nên chạy sau khi đăng nhập, đăng xuất)
     */
    public static function regenerateToken() {
        return self::generateToken();
    }

    /**
     * Xác thực token CSRF gửi lên từ POST Form hoặc từ AJAX Header
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
}
