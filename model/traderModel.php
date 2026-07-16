<?php
/**
 * Model quản lý thông tin Tiểu Thương (Traders)
 */
class traderModel {
    private $db;

    public function __construct() {
        $this->db = database::getInstance();
    }

    /**
     * Lấy toàn bộ danh sách tiểu thương kèm công nợ động và bộ lọc tìm kiếm
     */
    public function getAllTraders($search = '', $business_line_id = '', $status = '', $marketId = null) {
        $sql = "SELECT t.*, 
                       (
                           SELECT COALESCE(SUM(b.total_amount - b.paid_amount), 0)
                           FROM bills b
                           JOIN contracts c ON b.contract_id = c.id
                           WHERE c.trader_id = t.id AND b.status IN ('unpaid', 'partially_paid')
                       ) AS total_debt,
                        ss.code AS status_code,
                        ss.status_name,
                        sc.color_class,
                        bl.line_name AS business_line_name,
                        bl.line_code AS business_line_code
                FROM traders t
                LEFT JOIN system_statuses ss ON ss.id = t.status_id
                LEFT JOIN status_colors sc ON ss.color_id = sc.id
                LEFT JOIN business_lines bl ON bl.id = t.business_line_id
                WHERE ss.code != '99'";
        $params = [];

        if ($marketId) {
            $sql .= " AND t.id IN (
                SELECT DISTINCT c.trader_id 
                FROM contracts c
                JOIN stalls s ON c.stall_id = s.id
                JOIN areas a ON s.area_id = a.id
                WHERE a.market_id = :market_id
            )";
            $params['market_id'] = $marketId;
        }

        if (!empty($search)) {
            $sql .= " AND (t.fullname LIKE :search1 OR t.phone LIKE :search2 OR t.cccd LIKE :search3 OR t.trader_code LIKE :search4)";
            $params['search1'] = "%$search%";
            $params['search2'] = "%$search%";
            $params['search3'] = "%$search%";
            $params['search4'] = "%$search%";
        }

        if (!empty($business_line_id)) {
            $sql .= " AND t.business_line_id = :business_line_id";
            $params['business_line_id'] = $business_line_id;
        }

        if (!empty($status)) {
            $sql .= " AND ss.code = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY t.id DESC";
        return $this->db->select($sql, $params);
    }

    /**
     * Lấy thông tin chi tiết một tiểu thương
     */
    public function getTraderById($id) {
        $sql = "SELECT t.*, ss.code AS status_code, ss.status_name, sc.color_class,
                       bl.line_name AS business_line_name,
                       bl.line_code AS business_line_code
                FROM traders t
                LEFT JOIN system_statuses ss ON ss.id = t.status_id
                LEFT JOIN status_colors sc ON ss.color_id = sc.id
                LEFT JOIN business_lines bl ON bl.id = t.business_line_id
                WHERE t.id = :id AND (ss.code IS NULL OR ss.code != '99')";
        return $this->db->selectOne($sql, ['id' => $id]);
    }

    /**
     * Thêm tiểu thương mới
     */
    public function createTrader($data) {
        $statusModel = new statusModel();
        $activeStatusId = $statusModel->getIdByCode('trader', 'active');

        $sql = "INSERT INTO traders (trader_code, fullname, phone, cccd, address, business_line_id, description, license_file, status_id) 
                VALUES (:trader_code, :fullname, :phone, :cccd, :address, :business_line_id, :description, :license_file, :status_id)";
        
        $params = [
            'trader_code'      => $data['trader_code'],
            'fullname'         => $data['fullname'],
            'phone'            => $data['phone'],
            'cccd'             => $data['cccd'],
            'address'          => $data['address'] ?? null,
            'business_line_id' => $data['business_line_id'] ?: null,
            'description'      => $data['description'] ?? null,
            'license_file'     => $data['license_file'] ?? null,
            'status_id'        => $data['status_id'] ?: $activeStatusId
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    /**
     * Cập nhật thông tin tiểu thương
     */
    public function updateTrader($id, $data) {
        $statusModel = new statusModel();
        $activeStatusId = $statusModel->getIdByCode('trader', 'active');

        $sql = "UPDATE traders 
                SET fullname = :fullname, phone = :phone, cccd = :cccd, 
                    address = :address, business_line_id = :business_line_id, 
                    description = :description, license_file = :license_file, status_id = :status_id 
                WHERE id = :id";
        
        $params = [
            'id'               => $id,
            'fullname'         => $data['fullname'],
            'phone'            => $data['phone'],
            'cccd'             => $data['cccd'],
            'address'          => $data['address'] ?? null,
            'business_line_id' => $data['business_line_id'] ?: null,
            'description'      => $data['description'] ?? null,
            'license_file'     => $data['license_file'] ?? null,
            'status_id'        => $data['status_id'] ?: $activeStatusId
        ];

        return $this->db->query($sql, $params);
    }

    public function deleteTrader($id) {
        $sql = "UPDATE traders 
                SET status_id = (SELECT id FROM system_statuses WHERE domain = 'trader' AND code = '99') 
                WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }

    /**
     * Kiểm tra xem Mã tiểu thương đã tồn tại chưa
     */
    public function isTraderCodeExists($code, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM traders WHERE trader_code = :code";
        $params = ['code' => $code];
        if ($excludeId !== null) {
            $sql .= " AND id != :excludeId";
            $params['excludeId'] = $excludeId;
        }
        $res = $this->db->selectOne($sql, $params);
        return ($res['count'] ?? 0) > 0;
    }

    /**
     * Kiểm tra xem Số CCCD đã tồn tại chưa
     */
    public function isCccdExists($cccd, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM traders WHERE cccd = :cccd";
        $params = ['cccd' => $cccd];
        if ($excludeId !== null) {
            $sql .= " AND id != :excludeId";
            $params['excludeId'] = $excludeId;
        }
        $res = $this->db->selectOne($sql, $params);
        return ($res['count'] ?? 0) > 0;
    }

    /**
     * Lấy danh sách các trạng thái hợp lệ của tiểu thương (loại trừ đã xóa)
     */
    public function getTraderStatuses() {
        $sql = "SELECT * FROM system_statuses WHERE domain = 'trader' AND code != '99'";
        return $this->db->select($sql);
    }

    /**
     * Lấy danh sách toàn bộ ngành hàng
     */
    public function getBusinessLines() {
        $sql = "SELECT * FROM business_lines ORDER BY line_name ASC";
        return $this->db->select($sql);
    }

    /**
     * Lấy danh sách tiểu thương chưa thuê sạp (khả dụng để gán sạp)
     */
    public function getAvailableTraders() {
        $sql = "SELECT id, fullname, trader_code FROM traders 
                WHERE status_id = (SELECT id FROM system_statuses WHERE domain = 'trader' AND code = 'active')
                  AND id NOT IN (SELECT trader_id FROM contracts WHERE status_id = (SELECT id FROM system_statuses WHERE domain = 'contract' AND code = 'active'))
                ORDER BY fullname ASC";
        return $this->db->select($sql);
    }
}
