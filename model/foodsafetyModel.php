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
     * Lấy danh sách giấy chứng nhận vệ sinh ATTP, sức khỏe, tập huấn của tiểu thương
     */
    public function getCertificates($traderId = null, $docType = null, $status = null, $search = null, $marketId = null) {
        $sql = "SELECT c.*, dt.type_name AS doc_type, dt.type_code, t.fullname AS trader_name, t.phone AS trader_phone, t.description AS shop_name,
                       bl.line_name AS business_line,
                       ss.code AS status_code, ss.status_name, sc.color_class,
                       DATEDIFF(c.expiry_date, CURRENT_DATE) AS days_remaining
                FROM trader_attp c
                LEFT JOIN document_types dt ON c.doc_type_id = dt.id
                LEFT JOIN traders t ON c.trader_id = t.id
                LEFT JOIN business_lines bl ON bl.id = t.business_line_id
                LEFT JOIN system_statuses ss ON c.status_id = ss.id
                LEFT JOIN status_colors sc ON ss.color_id = sc.id
                WHERE ss.code != '99' AND (t.id IS NULL OR t.status_id != (SELECT id FROM system_statuses WHERE domain = 'trader' AND code = '99'))";
        
        $params = [];

        if ($marketId) {
            $sql .= " AND t.id IN (
                SELECT DISTINCT c2.trader_id 
                FROM contracts c2
                JOIN stalls s2 ON c2.stall_id = s2.id
                JOIN areas a2 ON s2.area_id = a2.id
                WHERE a2.market_id = :market_id
            )";
            $params['market_id'] = $marketId;
        }

        if ($traderId) {
            $sql .= " AND c.trader_id = :trader_id";
            $params['trader_id'] = $traderId;
        }

        if ($docType) {
            $sql .= " AND c.doc_type_id = :doc_type";
            $params['doc_type'] = $docType;
        }

        if ($status) {
            $sql .= " AND ss.code = :status";
            $params['status'] = $status;
        }

        if (!empty($search)) {
            $sql .= " AND (c.doc_number LIKE :search1 OR c.name LIKE :search2 OR t.fullname LIKE :search3)";
            $params['search1'] = "%$search%";
            $params['search2'] = "%$search%";
            $params['search3'] = "%$search%";
        }

        $sql .= " ORDER BY c.expiry_date ASC";
        return $this->db->select($sql, $params);
    }

    /**
     * Lấy thông tin một giấy chứng nhận theo ID
     */
    public function getById($id) {
        $sql = "SELECT c.*, dt.type_name AS doc_type, dt.type_code, t.fullname AS trader_name, t.phone AS trader_phone, t.description AS shop_name,
                       ss.code AS status_code, ss.status_name, sc.color_class,
                       DATEDIFF(c.expiry_date, CURRENT_DATE) AS days_remaining
                FROM trader_attp c
                LEFT JOIN document_types dt ON c.doc_type_id = dt.id
                LEFT JOIN traders t ON c.trader_id = t.id
                LEFT JOIN system_statuses ss ON c.status_id = ss.id
                LEFT JOIN status_colors sc ON ss.color_id = sc.id
                WHERE c.id = :id AND ss.code != '99' AND (t.id IS NULL OR t.status_id != (SELECT id FROM system_statuses WHERE domain = 'trader' AND code = '99'))";
        
        return $this->db->selectOne($sql, ['id' => $id]);
    }

    /**
     * Thêm giấy tờ vệ sinh ATTP mới
     */
    public function createCertificate($data) {
        $statusModel = new statusModel();
        $validStatusId = $statusModel->getIdByCode('attp', 'valid');

        $sql = "INSERT INTO trader_attp (trader_id, doc_type_id, doc_number, name, description, file, status_id, issuer, issue_date, expiry_date)
                VALUES (:trader_id, :doc_type_id, :doc_number, :name, :description, :file, :status_id, :issuer, :issue_date, :expiry_date)";
        
        $params = [
            'trader_id'   => $data['trader_id'],
            'doc_type_id' => $data['doc_type_id'],
            'doc_number'  => $data['doc_number'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'file'        => $data['file'] ?? null,
            'status_id'   => $data['status_id'] ?? $validStatusId,
            'issuer'      => $data['issuer'] ?? null,
            'issue_date'  => $data['issue_date'],
            'expiry_date' => $data['expiry_date']
        ];

        return $this->db->query($sql, $params);
    }

    /**
     * Cập nhật thông tin giấy tờ vệ sinh ATTP
     */
    public function updateCertificate($id, $data) {
        $sql = "UPDATE trader_attp 
                SET trader_id = :trader_id,
                    doc_type_id = :doc_type_id, 
                    doc_number = :doc_number, 
                    name = :name, 
                    description = :description, 
                    issuer = :issuer, 
                    issue_date = :issue_date, 
                    expiry_date = :expiry_date";
        
        $params = [
            'id'          => $id,
            'trader_id'   => $data['trader_id'],
            'doc_type_id' => $data['doc_type_id'],
            'doc_number'  => $data['doc_number'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'issuer'      => $data['issuer'] ?? null,
            'issue_date'  => $data['issue_date'],
            'expiry_date' => $data['expiry_date']
        ];

        if (isset($data['file'])) {
            $sql .= ", file = :file";
            $params['file'] = $data['file'];
        }

        if (isset($data['status_id'])) {
            $sql .= ", status_id = :status_id";
            $params['status_id'] = $data['status_id'];
        }

        $sql .= " WHERE id = :id";
        return $this->db->query($sql, $params);
    }

    /**
     * Xóa mềm giấy tờ (99)
     */
    public function deleteCertificate($id) {
        $statusModel = new statusModel();
        $deletedStatusId = $statusModel->getIdByCode('attp', '99');

        $sql = "UPDATE trader_attp SET status_id = :status_id WHERE id = :id";
        return $this->db->query($sql, [
            'id' => $id,
            'status_id' => $deletedStatusId
        ]);
    }

    /**
     * Lấy danh sách trạng thái giấy tờ vệ sinh ATTP (trừ 99)
     */
    public function getAttpStatuses() {
        $sql = "SELECT * FROM system_statuses WHERE domain = 'attp' AND code != '99' ORDER BY id ASC";
        return $this->db->select($sql);
    }

    public function autoUpdateExpiryStatus() {
        $today = date('Y-m-d');
        $sql = "UPDATE trader_attp 
                SET status_id = (SELECT id FROM system_statuses WHERE domain = 'attp' AND code = 'expired') 
                WHERE expiry_date < :today 
                  AND status_id = (SELECT id FROM system_statuses WHERE domain = 'attp' AND code = 'valid')";
        return $this->db->query($sql, ['today' => $today]);
    }

    /**
     * Kiểm tra xem số chứng nhận đã tồn tại chưa
     */
    public function isDocNumberExists($num, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM trader_attp WHERE doc_number = :num AND status_id != (SELECT id FROM system_statuses WHERE domain = 'attp' AND code = '99')";
        $params = ['num' => $num];
        if ($excludeId !== null) {
            $sql .= " AND id != :excludeId";
            $params['excludeId'] = $excludeId;
        }
        $res = $this->db->selectOne($sql, $params);
        return ($res['count'] ?? 0) > 0;
    }
}
