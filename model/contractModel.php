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
    public function getAll($status = null, $search = null, $marketId = null) {
        $sql = "SELECT c.*, t.fullname AS trader_name, t.phone AS trader_phone, s.stall_code, s.base_price AS price, a.area_name,
                       ss.code AS status_code, ss.status_name, sc.color_class,
                       DATEDIFF(c.end_date, CURRENT_DATE) AS days_remaining
                FROM contracts c
                LEFT JOIN traders t ON c.trader_id = t.id
                LEFT JOIN stalls s ON c.stall_id = s.id
                LEFT JOIN areas a ON s.area_id = a.id
                LEFT JOIN system_statuses ss ON c.status_id = ss.id
                LEFT JOIN status_colors sc ON ss.color_id = sc.id
                WHERE ss.code != '99' AND (t.id IS NULL OR t.status_id != (SELECT id FROM system_statuses WHERE domain = 'trader' AND code = '99'))";
        
        $params = [];

        if ($marketId) {
            $sql .= " AND a.market_id = :market_id";
            $params['market_id'] = $marketId;
        }

        if ($status) {
            $sql .= " AND ss.code = :status";
            $params['status'] = $status;
        }

        if (!empty($search)) {
            $sql .= " AND (c.contract_number LIKE :search1 OR c.name LIKE :search2 OR t.fullname LIKE :search3 OR s.stall_code LIKE :search4)";
            $params['search1'] = "%$search%";
            $params['search2'] = "%$search%";
            $params['search3'] = "%$search%";
            $params['search4'] = "%$search%";
        }

        $sql .= " ORDER BY c.id DESC";
        return $this->db->select($sql, $params);
    }

    /**
     * Lấy thông tin chi tiết một hợp đồng
     */
    public function getById($id) {
        $sql = "SELECT c.*, t.fullname AS trader_name, t.phone AS trader_phone, t.cccd AS trader_cccd,
                       s.stall_code, s.stall_type, s.area_size, s.base_price AS price, a.area_name,
                       ss.code AS status_code, ss.status_name, sc.color_class,
                       DATEDIFF(c.end_date, CURRENT_DATE) AS days_remaining
                FROM contracts c
                LEFT JOIN traders t ON c.trader_id = t.id
                LEFT JOIN stalls s ON c.stall_id = s.id
                LEFT JOIN areas a ON s.area_id = a.id
                LEFT JOIN system_statuses ss ON c.status_id = ss.id
                LEFT JOIN status_colors sc ON ss.color_id = sc.id
                WHERE c.id = :id AND ss.code != '99' AND (t.id IS NULL OR t.status_id != (SELECT id FROM system_statuses WHERE domain = 'trader' AND code = '99'))";
        return $this->db->selectOne($sql, ['id' => $id]);
    }

    /**
     * Tạo hợp đồng mới
     */
    public function create($data) {
        $statusModel = new statusModel();
        $activeStatusId = $statusModel->getIdByCode('contract', 'active');

        $sql = "INSERT INTO contracts (trader_id, stall_id, contract_number, name, description, contract_file, start_date, end_date, deposit, status_id) 
                VALUES (:trader_id, :stall_id, :contract_number, :name, :description, :contract_file, :start_date, :end_date, :deposit, :status_id)";
        
        $params = [
            'trader_id'       => $data['trader_id'],
            'stall_id'        => $data['stall_id'],
            'contract_number' => $data['contract_number'],
            'name'            => $data['name'],
            'description'     => $data['description'] ?? null,
            'contract_file'   => $data['contract_file'] ?? null,
            'start_date'      => $data['start_date'],
            'end_date'        => $data['end_date'],
            'deposit'         => $data['deposit'],
            'status_id'       => $data['status_id'] ?? $activeStatusId
        ];

        try {
            $this->db->beginTransaction();
            
            // 1. Tạo hợp đồng
            $this->db->query($sql, $params);
            $contractId = $this->db->lastInsertId();

            // 2. Cập nhật trạng thái sạp thành 'rented' (đã thuê)
            $stallModel = new stallModel();
            $stallModel->updateStatus($data['stall_id'], 'rented');

            $this->db->commit();
            return $contractId;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Cập nhật trạng thái hợp đồng trực tiếp
     */
    public function updateStatus($id, $status) {
        if (is_numeric($status)) {
            $statusId = $status;
        } else {
            $statusModel = new statusModel();
            $statusId = $statusModel->getIdByCode('contract', $status);
        }
        $sql = "UPDATE contracts SET status_id = :status_id WHERE id = :id";
        return $this->db->query($sql, ['id' => $id, 'status_id' => $statusId]);
    }

    /**
     * Gia hạn hợp đồng
     */
    public function renew($id, $newEndDate) {
        $statusModel = new statusModel();
        $activeStatusId = $statusModel->getIdByCode('contract', 'active');

        $sql = "UPDATE contracts SET end_date = :end_date, status_id = :status_id WHERE id = :id";
        return $this->db->query($sql, [
            'id' => $id,
            'end_date' => $newEndDate,
            'status_id' => $activeStatusId
        ]);
    }

    /**
     * Thanh lý hợp đồng
     */
    public function liquidate($id) {
        $statusModel = new statusModel();
        $liquidatedStatusId = $statusModel->getIdByCode('contract', 'liquidated');

        // Lấy thông tin hợp đồng trước khi thay đổi trạng thái
        $contract = $this->getById($id);
        if (!$contract) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            // 1. Cập nhật trạng thái hợp đồng
            $sql = "UPDATE contracts SET status_id = :status_id WHERE id = :id";
            $this->db->query($sql, ['id' => $id, 'status_id' => $liquidatedStatusId]);

            // 2. Trả sạp về trạng thái empty (trống)
            $stallModel = new stallModel();
            $stallModel->updateStatus($contract['stall_id'], 'empty');

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Chấm dứt hợp đồng trước hạn
     */
    public function terminate($id) {
        $statusModel = new statusModel();
        $terminatedStatusId = $statusModel->getIdByCode('contract', 'terminated');

        // Lấy thông tin hợp đồng trước khi thay đổi trạng thái
        $contract = $this->getById($id);
        if (!$contract) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            // 1. Cập nhật trạng thái hợp đồng
            $sql = "UPDATE contracts SET status_id = :status_id WHERE id = :id";
            $this->db->query($sql, ['id' => $id, 'status_id' => $terminatedStatusId]);

            // 2. Trả sạp về trạng thái empty (trống)
            $stallModel = new stallModel();
            $stallModel->updateStatus($contract['stall_id'], 'empty');

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Xóa mềm hợp đồng (99)
     */
    public function softDelete($id) {
        $statusModel = new statusModel();
        $deletedStatusId = $statusModel->getIdByCode('contract', '99');

        // Lấy thông tin hợp đồng trước khi thay đổi trạng thái
        $contract = $this->getById($id);
        if (!$contract) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            // 1. Cập nhật trạng thái hợp đồng thành 99 (Đã xóa)
            $sql = "UPDATE contracts SET status_id = :status_id WHERE id = :id";
            $this->db->query($sql, ['id' => $id, 'status_id' => $deletedStatusId]);

            // 2. Trả sạp về trạng thái empty (trống)
            $stallModel = new stallModel();
            $stallModel->updateStatus($contract['stall_id'], 'empty');

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Lấy danh sách phụ lục của hợp đồng
     */
    public function getAppendices($contractId) {
        $sql = "SELECT * FROM contract_appendices WHERE contract_id = :contract_id ORDER BY id DESC";
        return $this->db->select($sql, ['contract_id' => $contractId]);
    }

    /**
     * Thêm phụ lục hợp đồng
     */
    public function addAppendix($data) {
        $sql = "INSERT INTO contract_appendices (contract_id, appendix_number, name, sign_date, effect_date, content, file) 
                VALUES (:contract_id, :appendix_number, :name, :sign_date, :effect_date, :content, :file)";
        
        $params = [
            'contract_id'     => $data['contract_id'],
            'appendix_number' => $data['appendix_number'],
            'name'            => $data['name'],
            'sign_date'       => $data['sign_date'],
            'effect_date'     => $data['effect_date'],
            'content'         => $data['content'],
            'file'            => $data['file'] ?? null
        ];

        return $this->db->query($sql, $params);
    }

    /**
     * Lấy danh sách trạng thái của hợp đồng
     */
    public function getContractStatuses() {
        $sql = "SELECT * FROM system_statuses WHERE domain = 'contract' AND code != '99'";
        return $this->db->select($sql);
    }

    /**
     * Lấy hợp đồng đang hoạt động của một sạp
     */
    public function getActiveContractByStall($stallId) {
        $sql = "SELECT * FROM contracts WHERE stall_id = :stall_id AND status_id = (SELECT id FROM system_statuses WHERE domain = 'contract' AND code = 'active') LIMIT 1";
        return $this->db->selectOne($sql, ['stall_id' => $stallId]);
    }

    /**
     * Kiểm tra xem số hợp đồng đã tồn tại chưa
     */
    public function isContractNumberExists($num, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM contracts WHERE contract_number = :num AND status_id != (SELECT id FROM system_statuses WHERE domain = 'contract' AND code = '99')";
        $params = ['num' => $num];
        if ($excludeId !== null) {
            $sql .= " AND id != :excludeId";
            $params['excludeId'] = $excludeId;
        }
        $res = $this->db->selectOne($sql, $params);
        return ($res['count'] ?? 0) > 0;
    }

    /**
     * Kiểm tra xem số phụ lục đã tồn tại chưa
     */
    public function isAppendixNumberExists($num, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM contract_appendices WHERE appendix_number = :num";
        $params = ['num' => $num];
        if ($excludeId !== null) {
            $sql .= " AND id != :excludeId";
            $params['excludeId'] = $excludeId;
        }
        $res = $this->db->selectOne($sql, $params);
        return ($res['count'] ?? 0) > 0;
    }
}
