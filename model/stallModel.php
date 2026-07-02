<?php
/**
 * Model quản lý Khu vực và Sạp Chợ (Areas & Stalls)
 */
class stallModel {
    private $db;

    public function __construct() {
        $this->db = database::getInstance();
    }

    /* ==========================================================================
       1. Quản lý Khu vực (Areas)
       ========================================================================== */

    public function getAreas() {
        $sql = "SELECT * FROM areas ORDER BY area_name ASC";
        return $this->db->select($sql);
    }

    public function createArea($name, $desc = '') {
        $sql = "INSERT INTO areas (area_name, description) VALUES (:name, :desc)";
        $this->db->query($sql, ['name' => $name, 'desc' => $desc]);
        return $this->db->lastInsertId();
    }

    /* ==========================================================================
       2. Quản lý Sạp chợ (Stalls)
       ========================================================================== */

    /**
     * Lấy toàn bộ danh sách sạp kèm thông tin khu vực
     */
    public function getAll($areaId = null, $status = null) {
        $sql = "SELECT s.*, a.area_name 
                FROM stalls s
                LEFT JOIN areas a ON s.area_id = a.id";
        
        $where = [];
        $params = [];

        if ($areaId) {
            $where[] = "s.area_id = :area_id";
            $params['area_id'] = $areaId;
        }

        if ($status) {
            $where[] = "s.status = :status";
            $params['status'] = $status;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY a.area_name ASC, s.stall_code ASC";
        return $this->db->select($sql, $params);
    }

    public function getById($id) {
        $sql = "SELECT s.*, a.area_name 
                FROM stalls s 
                LEFT JOIN areas a ON s.area_id = a.id 
                WHERE s.id = :id";
        return $this->db->selectOne($sql, ['id' => $id]);
    }

    public function create($data) {
        $sql = "INSERT INTO stalls (area_id, stall_code, stall_type, area_size, base_price, status) 
                VALUES (:area_id, :stall_code, :stall_type, :area_size, :base_price, :status)";
        
        $params = [
            'area_id'    => $data['area_id'],
            'stall_code' => $data['stall_code'],
            'stall_type' => $data['stall_type'] ?? 'Quầy hàng',
            'area_size'  => $data['area_size'],
            'base_price' => $data['base_price'],
            'status'     => $data['status'] ?? 'empty'
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE stalls 
                SET area_id = :area_id, stall_code = :stall_code, stall_type = :stall_type, 
                    area_size = :area_size, base_price = :base_price, status = :status 
                WHERE id = :id";
        
        $params = [
            'id'         => $id,
            'area_id'    => $data['area_id'],
            'stall_code' => $data['stall_code'],
            'stall_type' => $data['stall_type'] ?? 'Quầy hàng',
            'area_size'  => $data['area_size'],
            'base_price' => $data['base_price'],
            'status'     => $data['status'] ?? 'empty'
        ];

        return $this->db->query($sql, $params);
    }

    public function updateStatus($id, $status) {
        $sql = "UPDATE stalls SET status = :status WHERE id = :id";
        return $this->db->query($sql, ['id' => $id, 'status' => $status]);
    }

    public function delete($id) {
        $sql = "DELETE FROM stalls WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
}
