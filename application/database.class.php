<?php
/**
 * Lớp quản lý kết nối và truy vấn Cơ sở dữ liệu (PDO wrapper)
 */
class database {
    private static $instance = null;
    private $conn;

    // Khởi tạo kết nối (Private để chống khởi tạo tự do)
    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            throw new Exception("Kết nối Database thất bại: " . $e->getMessage());
        }
    }

    // Lấy instance duy nhất của Database (Pattern Singleton)
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Lấy kết nối PDO gốc
    public function getConnection() {
        return $this->conn;
    }

    // Thực thi câu lệnh SQL có tham số (INSERT, UPDATE, DELETE)
    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // Lấy nhiều bản ghi (SELECT)
    public function select($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    // Lấy một bản ghi duy nhất
    public function selectOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }

    // Lấy ID của bản ghi vừa chèn (Last Insert ID)
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }

    // Bắt đầu một Transaction
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    // Commit Transaction
    public function commit() {
        return $this->conn->commit();
    }

    // Rollback Transaction
    public function rollback() {
        return $this->conn->rollBack();
    }
}
