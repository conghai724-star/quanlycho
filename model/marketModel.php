<?php
/**
 * Model quản lý danh sách Chợ (Markets)
 */
class marketModel {
    private $db;

    public function __construct() {
        $this->db = database::getInstance();
    }

    /**
     * Lấy toàn bộ danh sách chợ
     */
    public function getAll($search = '') {
        $sql = "SELECT * FROM markets WHERE status_code != 'deleted'";
        $params = [];
        if ($search) {
            $sql .= " AND (name LIKE :search OR market_code LIKE :search)";
            $params['search'] = "%{$search}%";
        }
        $sql .= " ORDER BY id DESC";
        return $this->db->select($sql, $params);
    }

    /**
     * Lấy chi tiết chợ theo ID
     */
    public function getById($id) {
        return $this->db->selectOne("SELECT * FROM markets WHERE id = :id", ['id' => $id]);
    }

    /**
     * Tạo chợ mới
     */
    public function create($data) {
        $sql = "INSERT INTO markets (market_code, name, phone, email, manager_name, status_code)
                VALUES (:market_code, :name, :phone, :email, :manager_name, :status_code)";
        
        $this->db->query($sql, [
            'market_code'  => $data['market_code'],
            'name'         => $data['name'],
            'phone'        => $data['phone'] ?? null,
            'email'        => $data['email'] ?? null,
            'manager_name' => $data['manager_name'] ?? null,
            'status_code'  => $data['status_code'] ?? 'active'
        ]);
        
        return $this->db->lastInsertId();
    }

    /**
     * Cập nhật thông tin chợ
     */
    public function update($id, $data) {
        $sql = "UPDATE markets SET 
                    market_code = :market_code,
                    name = :name,
                    phone = :phone,
                    email = :email,
                    manager_name = :manager_name,
                    status_code = :status_code
                WHERE id = :id";
        
        return $this->db->query($sql, [
            'id'           => $id,
            'market_code'  => $data['market_code'],
            'name'         => $data['name'],
            'phone'        => $data['phone'] ?? null,
            'email'        => $data['email'] ?? null,
            'manager_name' => $data['manager_name'] ?? null,
            'status_code'  => $data['status_code'] ?? 'active'
        ]);
    }
    /**
     * Xóa chợ (Soft Delete)
     */
    public function delete($id) {
        $sql = "UPDATE markets SET status_code = 'deleted' WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
}
