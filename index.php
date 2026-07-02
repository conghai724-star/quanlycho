<?php
/**
 * Front Controller chính của hệ thống
 */

// Nạp file cấu hình hệ thống
require_once __DIR__ . '/config.php';

// Đăng ký cơ chế Autoload nạp tự động các Class
spl_autoload_register(function ($className) {
    // 1. Kiểm tra trong thư mục application (định dạng ClassName.class.php hoặc ClassName.php)
    $classFileApp1 = DIR_APP . '/' . $className . '.class.php';
    $classFileApp2 = DIR_APP . '/' . $className . '.php';
    if (file_exists($classFileApp1)) {
        require_once $classFileApp1;
        return;
    } elseif (file_exists($classFileApp2)) {
        require_once $classFileApp2;
        return;
    }

    // 2. Kiểm tra trong thư mục model (định dạng ClassName.php)
    $classFileModel = DIR_MODEL . '/' . $className . '.php';
    if (file_exists($classFileModel)) {
        require_once $classFileModel;
        return;
    }

    // 3. Kiểm tra trong thư mục controller (định dạng ClassName.php)
    $classFileController = DIR_CONTROLLER . '/' . $className . '.php';
    if (file_exists($classFileController)) {
        require_once $classFileController;
        return;
    }
});

// Khởi tạo đối tượng định tuyến Router
try {
    $router = new router();
    $router->dispatch();
} catch (Exception $e) {
    // Xử lý lỗi hệ thống
    echo "<h1>Hệ thống gặp sự cố</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
