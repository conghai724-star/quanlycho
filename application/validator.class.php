<?php
/**
 * Lớp kiểm tra dữ liệu đầu vào (Validation Helper)
 */
class validator {
    private $errors = [];

    /**
     * Kiểm tra trường bắt buộc nhập
     */
    public function required($field, $value, $message = "Trường này là bắt buộc.") {
        if (is_array($value)) {
            if (empty($value)) {
                $this->errors[$field] = $message;
            }
        } else {
            if (trim($value) === '') {
                $this->errors[$field] = $message;
            }
        }
        return $this;
    }

    /**
     * Kiểm tra định dạng email
     */
    public function email($field, $value, $message = "Email không đúng định dạng.") {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    /**
     * Kiểm tra số điện thoại Việt Nam hợp lệ
     */
    public function phone($field, $value, $message = "Số điện thoại không hợp lệ.") {
        if (!empty($value)) {
            // Định dạng SĐT Việt Nam: 10 chữ số, bắt đầu bằng 03, 05, 07, 08, 09
            $pattern = '/^(03|05|07|08|09)[0-9]{8}$/';
            if (!preg_match($pattern, $value)) {
                $this->errors[$field] = $message;
            }
        }
        return $this;
    }

    /**
     * Kiểm tra độ dài tối thiểu
     */
    public function minLength($field, $value, $min, $message = null) {
        $msg = $message ?? "Trường này phải có ít nhất {$min} ký tự.";
        if (!empty($value) && mb_strlen($value) < $min) {
            $this->errors[$field] = $msg;
        }
        return $this;
    }

    /**
     * Kiểm tra độ dài tối đa
     */
    public function maxLength($field, $value, $max, $message = null) {
        $msg = $message ?? "Trường này không được vượt quá {$max} ký tự.";
        if (!empty($value) && mb_strlen($value) > $max) {
            $this->errors[$field] = $msg;
        }
        return $this;
    }

    /**
     * Kiểm tra khớp dữ liệu (ví dụ: xác nhận mật khẩu)
     */
    public function matches($field, $value, $compareValue, $message = "Xác nhận giá trị không khớp.") {
        if ($value !== $compareValue) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function numeric($field, $value, $message = "Trường này phải là dạng số.") {
        if (!empty($value) && !is_numeric($value)) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    /**
     * Kiểm tra giá trị tối thiểu
     */
    public function min($field, $value, $min, $message = null) {
        $msg = $message ?? "Trường này phải lớn hơn hoặc bằng {$min}.";
        if (!empty($value) && is_numeric($value) && $value < $min) {
            $this->errors[$field] = $msg;
        }
        return $this;
    }

    /**
     * Thêm lỗi thủ công
     */
    public function addError($field, $message) {
        $this->errors[$field] = $message;
    }

    /**
     * Kiểm tra xem có lỗi nào không
     */
    public function isValid() {
        return empty($this->errors);
    }

    /**
     * Lấy danh sách lỗi
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Lấy lỗi của một trường cụ thể
     */
    public function getError($field) {
        return $this->errors[$field] ?? null;
    }
}
