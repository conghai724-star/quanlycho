<?php
/**
 * Model quản lý tài khoản người dùng (Nhân viên BQL, Admin)
 */
class userModel {
    private $db;

    public function __construct() {
        $this->db = database::getInstance();
    }

    /**
     * Lấy người dùng theo ID
     */
    public function getById($id) {
        $sql = "SELECT id, username, fullname, email, role_code, is_active FROM users WHERE id = :id";
        return $this->db->selectOne($sql, ['id' => $id]);
    }

    /**
     * Lấy người dùng theo tên đăng nhập
     */
    public function getByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = :username";
        return $this->db->selectOne($sql, ['username' => $username]);
    }

    /**
     * Kiểm tra thông tin đăng nhập và trả về thông tin user
     */
    public function authenticate($username, $password) {
        $user = $this->getByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_active'] == 1) {
                return $user;
            }
        }
        return false;
    }

    /**
     * Thêm tài khoản người dùng mới
     */
    public function create($data) {
        $sql = "INSERT INTO users (username, password, fullname, email, role_code, is_active) 
                VALUES (:username, :password, :fullname, :email, :role_code, :is_active)";
        
        $params = [
            'username' => $data['username'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'fullname' => $data['fullname'],
            'email'    => $data['email'] ?? null,
            'role_code'=> $data['role_code'] ?? 'staff',
            'is_active'=> $data['is_active'] ?? 1
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }
}
