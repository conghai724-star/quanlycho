<?php
/**
 * Model quản lý thông tin Tiểu Thương (Traders)
 */
class merchantModel {
    private $db;

    public function __construct() {
        $this->db = database::getInstance();
    }

    /**
     * Lấy toàn bộ danh sách tiểu thương
     */
    public function getAll($status = null) {
        $sql = "SELECT * FROM traders";
        $params = [];
        if ($status !== null) {
            $sql .= " WHERE status = :status";
            $params['status'] = $status;
        }
        $sql .= " ORDER BY id DESC";
        return $this->db->select($sql, $params);
    }

    /**
     * Lấy thông tin chi tiết một tiểu thương
     */
    public function getById($id) {
        $sql = "SELECT * FROM traders WHERE id = :id";
        return $this->db->selectOne($sql, ['id' => $id]);
    }

    /**
     * Thêm tiểu thương mới
     */
    public function create($data) {
        $sql = "INSERT INTO traders (trader_code, fullname, phone, cccd, address, business_line, status) 
                VALUES (:trader_code, :fullname, :phone, :cccd, :address, :business_line, :status)";
        
        $params = [
            'trader_code'   => $data['trader_code'],
            'fullname'      => $data['fullname'],
            'phone'         => $data['phone'],
            'cccd'          => $data['cccd'],
            'address'       => $data['address'] ?? null,
            'business_line' => $data['business_line'] ?? null,
            'status'        => $data['status'] ?? 'active'
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    /**
     * Cập nhật thông tin tiểu thương
     */
    public function update($id, $data) {
        $sql = "UPDATE traders 
                SET fullname = :fullname, phone = :phone, cccd = :cccd, 
                    address = :address, business_line = :business_line, status = :status 
                WHERE id = :id";
        
        $params = [
            'id'            => $id,
            'fullname'      => $data['fullname'],
            'phone'         => $data['phone'],
            'cccd'          => $data['cccd'],
            'address'       => $data['address'] ?? null,
            'business_line' => $data['business_line'] ?? null,
            'status'        => $data['status'] ?? 'active'
        ];

        return $this->db->query($sql, $params);
    }

    /**
     * Xóa thông tin tiểu thương
     */
    public function delete($id) {
        $sql = "DELETE FROM traders WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
}
