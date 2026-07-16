<?php
/**
 * Lớp quản lý Session và Phân quyền người dùng
 */
class session {
    /**
     * Gán giá trị vào session
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Lấy giá trị từ session
     */
    public static function get($key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Xóa một key trong session
     */
    public static function delete($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Hủy toàn bộ session (Đăng xuất)
     */
    public static function destroy() {
        session_unset();
        session_destroy();
    }

    /**
     * Kiểm tra xem người dùng đã đăng nhập chưa
     */
    public static function isLoggedIn() {
        return self::get('user_logged_in') === true;
    }

    /**
     * Yêu cầu đăng nhập, nếu chưa đăng nhập sẽ chuyển hướng
     */
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'home/login');
            exit();
        }
    }

    /**
     * Kiểm tra vai trò của người dùng hiện tại
     * @param string|array $allowedRoles Danh sách vai trò được cho phép
     */
    public static function hasRole($allowedRoles) {
        $userRole = self::get('user_role');
        if (!$userRole) {
            return false;
        }

        if (is_array($allowedRoles)) {
            return in_array($userRole, $allowedRoles);
        }

        return $userRole === $allowedRoles;
    }

    /**
     * Yêu cầu vai trò cụ thể, nếu không đủ quyền sẽ chuyển hướng 403
     */
    public static function requireRole($allowedRoles) {
        self::requireLogin();
        if (!self::hasRole($allowedRoles)) {
            header('Location: ' . BASE_URL . 'errors/forbidden');
            exit();
        }
    }

    public static function requireAdmin() {
        self::requireLogin();
        $group = self::get('user_group');
        if ($group != 1 && $group != 2) {
            header('Location: ' . BASE_URL . 'errors/forbidden');
            exit();
        }
    }
}
