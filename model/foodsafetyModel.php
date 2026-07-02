<?php
/**
 * Model quản lý An Toàn Thực Phẩm và giấy tờ liên quan (Food Safety)
 */
class foodsafetyModel {
    private $db;

    public function __construct() {
        $this->db = database::getInstance();
    }

    /**
     * Lấy danh sách giấy chứng nhận vệ sinh ATTP của tiểu thương
     */
    public function getCertificates($traderId = null) {
        $sql = "SELECT c.*, t.fullname AS trader_name, t.business_line
                FROM trader_attp c
                LEFT JOIN traders t ON c.trader_id = t.id";
        
        $params = [];
        if ($traderId) {
            $sql .= " WHERE c.trader_id = :trader_id";
            $params['trader_id'] = $traderId;
        }

        $sql .= " ORDER BY c.expiry_date ASC";
        return $this->db->select($sql, $params);
    }

    /**
     * Thêm giấy tờ vệ sinh ATTP mới
     */
    public function createCertificate($data) {
        $sql = "INSERT INTO trader_attp (trader_id, doc_type, doc_number, issue_date, expiry_date, status)
                VALUES (:trader_id, :doc_type, :doc_number, :issue_date, :expiry_date, :status)";
        
        $params = [
            'trader_id'  => $data['trader_id'],
            'doc_type'   => $data['doc_type'], // 'ATTP' | 'Health' (giấy khám sức khỏe) | 'Training' (giấy tập huấn)
            'doc_number' => $data['doc_number'],
            'issue_date' => $data['issue_date'],
            'expiry_date'=> $data['expiry_date'],
            'status'     => $data['status'] ?? 'valid'
        ];

        return $this->db->query($sql, $params);
    }

    /**
     * Tự động quét và cập nhật trạng thái hết hạn của các giấy tờ
     */
    public function autoUpdateExpiryStatus() {
        $today = date('Y-m-d');
        // Cập nhật các chứng nhận hết hạn thành 'expired'
        $sql = "UPDATE trader_attp 
                SET status = 'expired' 
                WHERE expiry_date < :today AND status = 'valid'";
        return $this->db->query($sql, ['today' => $today]);
    }
}
