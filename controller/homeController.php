<?php
/**
 * Controller xử lý các trang công cộng và Auth
 */
class homeController {
    
    /**
     * Trang chủ hệ thống
     */
    public function index() {
        // Nếu đã đăng nhập, vào Dashboard
        if (session::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'admin/dashboard');
            exit();
        }
        
        // Nếu chưa đăng nhập, chuyển sang trang đăng nhập
        header('Location: ' . BASE_URL . 'home/login');
        exit();
    }

    /**
     * Xử lý đăng nhập
     */
    public function login() {
        if (session::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'admin/dashboard');
            exit();
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';

            $validator = new validator();
            $validator->required('username', $username, 'Vui lòng nhập tài khoản.')
                      ->required('password', $password, 'Vui lòng nhập mật khẩu.');

            if ($validator->isValid()) {
                $userModel = new userModel();
                $user = $userModel->authenticate($username, $password);

                if ($user) {
                    session::set('user_logged_in', true);
                    session::set('user_id', $user['id']);
                    session::set('username', $user['username']);
                    session::set('user_fullname', $user['fullname']);
                    session::set('user_role', $user['role_code']);

                    header('Location: ' . BASE_URL . 'admin/dashboard');
                    exit();
                } else {
                    $error = 'Tài khoản hoặc mật khẩu không chính xác hoặc tài khoản đã bị khóa.';
                }
            } else {
                $errors = $validator->getErrors();
                $error = reset($errors); // Lấy lỗi đầu tiên
            }
        }

        // Gọi View Đăng nhập
        $this->view('backend/auth/login', ['error' => $error]);
    }

    /**
     * Đăng xuất hệ thống
     */
    public function logout() {
        session::destroy();
        header('Location: ' . BASE_URL . 'home/login');
        exit();
    }

    /**
     * Hàm render view tiện ích
     */
    protected function view($templatePath, $data = []) {
        // Giải nén các phần tử mảng thành các biến độc lập
        extract($data);

        $viewFile = DIR_TEMPLATE . '/' . $templatePath . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            echo "Không tìm thấy giao diện View: " . $templatePath;
        }
    }
}
