<?php
/**
 * File cấu hình hệ thống Quản lý Chợ
 */

// Báo lỗi (Tắt khi chạy thực tế - production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Thiết lập múi giờ Việt Nam
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Đường dẫn gốc hệ thống
define('DIR_ROOT', str_replace('\\', '/', __DIR__));
define('DIR_APP', DIR_ROOT . '/application');
define('DIR_CONTROLLER', DIR_ROOT . '/controller');
define('DIR_MODEL', DIR_ROOT . '/model');
define('DIR_TEMPLATE', DIR_ROOT . '/template');
define('DIR_UPLOAD', DIR_ROOT . '/uploads');

// Cấu hình URL cơ sở (Thay đổi tùy thuộc vào tên thư mục chạy trong XAMPP)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $protocol . '://' . $host . '/quanly_cho/');

// Cấu hình Cơ sở dữ liệu MySQL
define('DB_HOST', '127.0.0.1:3307');
define('DB_PORT', '3307');
define('DB_NAME', 'quanly_cho');
define('DB_USER', 'root');
define('DB_PASS', '');

// Bắt đầu Session nếu chưa chạy
if (session_status() === PHP_SESSION_NONE) {
    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
    session_start([
        'cookie_lifetime' => 0,          // Hết hạn khi đóng trình duyệt
        'cookie_httponly' => true,       // Chống đánh cắp session bằng JS
        'cookie_secure'   => $isSecure,  // Chỉ truyền qua HTTPS nếu có
        'cookie_samesite' => 'Lax'       // Bảo vệ CSRF cơ bản
    ]);
}
