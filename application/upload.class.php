<?php
/**
 * Lớp xử lý Upload File an toàn
 */
class upload {
    private $allowedTypes = [];
    private $maxSize; // tính bằng Bytes
    private $uploadDir;
    private $errors = [];

    public function __construct($uploadSubDir = 'temp', $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'], $maxSizeMb = 5) {
        $this->uploadDir = DIR_UPLOAD . '/' . rtrim($uploadSubDir, '/');
        $this->allowedTypes = array_map('strtolower', $allowedTypes);
        $this->maxSize = $maxSizeMb * 1024 * 1024; // Đổi sang bytes

        // Tự động tạo thư mục nếu chưa có
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    /**
     * Thực hiện upload file từ $_FILES
     * @param string $inputName Tên của input file trong form
     * @return string|false Trả về tên file đã lưu thành công hoặc false nếu lỗi
     */
    public function save($inputName) {
        if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] === UPLOAD_ERR_NO_FILE) {
            $this->errors[] = "Không tìm thấy file tải lên.";
            return false;
        }

        $file = $_FILES[$inputName];

        // 1. Kiểm tra lỗi hệ thống khi upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = "Lỗi hệ thống khi tải file: Code " . $file['error'];
            return false;
        }

        // 2. Kiểm tra dung lượng file
        if ($file['size'] > $this->maxSize) {
            $this->errors[] = "Dung lượng file vượt quá giới hạn cho phép (" . ($this->maxSize / 1024 / 1024) . "MB).";
            return false;
        }

        // 3. Kiểm tra định dạng file
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $this->allowedTypes)) {
            $this->errors[] = "Định dạng file không được phép. Chỉ cho phép các định dạng: " . implode(', ', $this->allowedTypes);
            return false;
        }

        // 4. Đổi tên file để tránh trùng và ký tự đặc biệt
        $newFileName = uniqid('file_', true) . '.' . $ext;
        $destPath = $this->uploadDir . '/' . $newFileName;

        // 5. Di chuyển file vào thư mục đích
        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            return $newFileName; // Trả về tên file để lưu DB
        } else {
            $this->errors[] = "Không thể ghi file vào thư mục đích.";
            return false;
        }
    }

    /**
     * Lấy các lỗi xảy ra trong quá trình upload
     */
    public function getErrors() {
        return $this->errors;
    }
}
