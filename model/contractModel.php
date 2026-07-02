<?php
/**
 * Model quản lý Hợp Đồng Thuê Sạp (Contracts)
 */
class contractModel {
    private $db;

    public function __construct() {
        $this->db = database::getInstance();
    }

    /**
     * Lấy toàn bộ danh sách hợp đồng
     */
    public function getAll($status = null) {
        $sql = "SELECT c.*, t.fullname AS trader_name, s.stall_code, a.area_name
                FROM contracts c
                LEFT JOIN traders t ON c.trader_id = t.id
                LEFT JOIN stalls s ON c.stall_id = s.id
                LEFT JOIN areas a ON s.area_id = a.id";
        
        $params = [];
        if ($status) {
            $sql .= " WHERE c.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY c.id DESC";
        return $this->db->select($sql, $params);
    }

    public function getById($id) {
        $sql = "SELECT c.*, t.fullname AS trader_name, t.phone AS trader_phone, t.cccd AS trader_cccd,
                       s.stall_code, s.stall_type, s.area_size, s.base_price, a.area_name
                FROM contracts c
                LEFT JOIN traders t ON c.trader_id = t.id
                LEFT JOIN stalls s ON c.stall_id = s.id
                LEFT JOIN areas a ON s.area_id = a.id
                WHERE c.id = :id";
        return $this->db->selectOne($sql, ['id' => $id]);
    }

    public function create($data) {
        $sql = "INSERT INTO contracts (trader_id, stall_id, contract_number, start_date, end_date, deposit, status) 
                VALUES (:trader_id, :stall_id, :contract_number, :start_date, :end_date, :deposit, :status)";
        
        $params = [
            'trader_id'       => $data['trader_id'],
            'stall_id'        => $data['stall_id'],
            'contract_number' => $data['contract_number'],
            'start_date'      => $data['start_date'],
            'end_date'        => $data['end_date'],
            'deposit'         => $data['deposit'],
            'status'          => $data['status'] ?? 'active'
        ];

        try {
            $this->db->beginTransaction();
            
            // 1. Tạo hợp đồng
            $this->db->query($sql, $params);
            $contractId = $this->db->lastInsertId();

            // 2. Cập nhật trạng thái sạp thành 'rented' (đã thuê)
            $updateStallSql = "UPDATE stalls SET status = 'rented' WHERE id = :stall_id";
            $this->db->query($updateStallSql, ['stall_id' => $data['stall_id']]);

            $this->db->commit();
            return $contractId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function updateStatus($id, $status) {
        $sql = "UPDATE contracts SET status = :status WHERE id = :id";
        return $this->db->query($sql, ['id' => $id, 'status' => $status]);
    }
}
