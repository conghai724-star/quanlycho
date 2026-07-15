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
        
        $this->view('frontend/home/index', [
            'title' => 'Chợ Trung Tâm Thành Phố — Cổng thông tin quản lý chợ',
            'activePage' => 'home'
        ]);
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
                    // ponytail: user_role derived from user_group; role_code column doesn't exist in DB
                    session::set('user_role', $user['user_group'] == 1 ? 'admin' : 'staff');
                    session::set('user_group', $user['user_group']);

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
     * Trang Giới thiệu
     */
    public function about() {
        $this->view('frontend/home/about', [
            'title' => 'Giới thiệu - Chợ Trung Tâm Thành Phố',
            'activePage' => 'about'
        ]);
    }

    /**
     * Trang Sơ đồ chợ
     */
    public function map() {
        $elements = [];
        try {
            $mapModel = new mapModel();
            $elements = $mapModel->getElements();
        } catch (Exception $e) {
            error_log('[homeController::map] EXCEPTION: ' . $e->getMessage());
        }

        $this->view('frontend/home/map', [
            'title' => 'Sơ đồ chợ - Chợ Trung Tâm Thành Phố',
            'activePage' => 'map',
            'elements' => $elements
        ]);
    }

    /**
     * Trang Sơ đồ cây sạp chợ công khai cho khách hàng tra cứu
     */
    public function map_tree() {
        $this->view('frontend/home/tree', [
            'title' => 'Tra cứu Sơ đồ Sạp chợ',
            'activePage' => 'map_tree'
        ]);
    }


    /**
     * Danh sách tiểu thương
     */
    public function traders() {
        $traderModel = new traderModel();
        $traders = [];
        try {
            $traders = $traderModel->getAllTraders('', '', 'active');
        } catch (Exception $e) {
            // Fallback
        }

        $this->view('frontend/home/traders', [
            'title' => 'Danh sách Tiểu thương - Chợ Trung Tâm Thành Phố',
            'activePage' => 'traders',
            'traders' => $traders
        ]);
    }

    /**
     * Đăng ký thuê sạp trực tuyến
     */
    public function register() {
        $success = false;
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Giả lập lưu hồ sơ thành công
            $success = true;
        }

        $this->view('frontend/home/register', [
            'title' => 'Đăng ký thuê sạp - Chợ Trung Tâm Thành Phố',
            'activePage' => 'register',
            'success' => $success,
            'error' => $error
        ]);
    }

    /**
     * Yêu cầu khôi phục mật khẩu
     */
    public function forgot_password() {
        if (session::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'admin/dashboard');
            exit();
        }

        $error = '';
        $success = '';
        $reset_link = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            
            $validator = new validator();
            $validator->required('email', $email, 'Vui lòng nhập email.')
                      ->email('email', $email, 'Email không đúng định dạng.');

            if ($validator->isValid()) {
                $userModel = new userModel();
                $user = $userModel->getByEmail($email);

                if ($user) {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                    try {
                        $userModel->setResetToken($email, $token, $expires);
                        $reset_link = BASE_URL . 'home/reset_password/' . $token;
                        $success = 'Yêu cầu khôi phục mật khẩu đã được gửi!';

                        // Log to debug.txt
                        $logMsg = sprintf(
                            "[%s] PASSWORD RECOVERY: User %s (ID: %d) requested password reset. Link: %s\n",
                            date('Y-m-d H:i:s'),
                            $user['username'],
                            $user['id'],
                            $reset_link
                        );
                        file_put_contents(DIR_ROOT . '/debug.txt', $logMsg, FILE_APPEND);
                    } catch (Exception $e) {
                        $error = 'Lỗi hệ thống: ' . $e->getMessage();
                    }
                } else {
                    $error = 'Email này không tồn tại trên hệ thống.';
                }
            } else {
                $errors = $validator->getErrors();
                $error = reset($errors);
            }
        }

        $this->view('backend/auth/forgot_password', [
            'error' => $error,
            'success' => $success,
            'reset_link' => $reset_link
        ]);
    }

    /**
     * Đặt lại mật khẩu mới qua token
     */
    public function reset_password($token = null) {
        if (session::isLoggedIn()) {
            header('Location: ' . BASE_URL . 'admin/dashboard');
            exit();
        }

        if (empty($token)) {
            header('Location: ' . BASE_URL . 'home/login');
            exit();
        }

        $userModel = new userModel();
        $user = $userModel->getByResetToken($token);

        if (!$user) {
            $this->view('backend/auth/reset_password', [
                'token' => $token,
                'error' => 'Link khôi phục mật khẩu không hợp lệ hoặc đã hết hạn.',
                'success' => ''
            ]);
            return;
        }

        $error = '';
        $success = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $validator = new validator();
            $validator->required('new_password', $newPassword, 'Vui lòng nhập mật khẩu mới.')
                      ->minLength('new_password', $newPassword, 6, 'Mật khẩu mới phải từ 6 ký tự trở lên.')
                      ->required('confirm_password', $confirmPassword, 'Vui lòng xác nhận mật khẩu mới.')
                      ->matches('confirm_password', $confirmPassword, $newPassword, 'Xác nhận mật khẩu mới không khớp.');

            if ($validator->isValid()) {
                try {
                    $userModel->updatePassword($user['id'], $newPassword);
                    $userModel->clearResetToken($user['id']);
                    $success = 'Đặt lại mật khẩu thành công! Bạn có thể đăng nhập bằng mật khẩu mới.';
                } catch (Exception $e) {
                    $error = 'Lỗi hệ thống: ' . $e->getMessage();
                }
            } else {
                $errors = $validator->getErrors();
                $error = reset($errors);
            }
        }

        $this->view('backend/auth/reset_password', [
            'token' => $token,
            'error' => $error,
            'success' => $success
        ]);
    }

    /**
     * Hàm render view tiện ích
     */
    protected function view($templatePath, $data = []) {
        // Giải nén các phần tử mảng thành các biến độc lập
        extract($data);

        $isFrontend = str_starts_with($templatePath, 'frontend/');

        // Nạp layout trên của frontend
        if ($isFrontend) {
            if (file_exists(DIR_TEMPLATE . '/frontend/layouts/header.php')) {
                require_once DIR_TEMPLATE . '/frontend/layouts/header.php';
            }
            if (file_exists(DIR_TEMPLATE . '/frontend/layouts/navbar.php')) {
                require_once DIR_TEMPLATE . '/frontend/layouts/navbar.php';
            }
        }

        $viewFile = DIR_TEMPLATE . '/' . $templatePath . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            echo "Không tìm thấy giao diện View: " . $templatePath;
        }

        // Nạp layout dưới của frontend
        if ($isFrontend) {
            if (file_exists(DIR_TEMPLATE . '/frontend/layouts/footer.php')) {
                require_once DIR_TEMPLATE . '/frontend/layouts/footer.php';
            }
        }
    }
}
