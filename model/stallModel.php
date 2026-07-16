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

    public function getAreas($marketId = null) {
        $sql = "SELECT * FROM areas";
        $params = [];
        if ($marketId) {
            $sql .= " WHERE market_id = :market_id";
            $params['market_id'] = $marketId;
        }
        $sql .= " ORDER BY area_name ASC";
        return $this->db->select($sql, $params);
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
     * Lấy toàn bộ danh sách sạp kèm thông tin khu vực và bộ lọc tìm kiếm
     */
    public function getAll($areaId = null, $status = null, $search = null, $marketId = null) {
        $sql = "SELECT s.*, st.type_name AS stall_type, ss.code AS status, ss.status_name, sc.color_class, a.area_name, a.block, a.lot, t.fullname AS trader_name, bl.line_name AS business_line_name 
                FROM stalls s
                LEFT JOIN stall_types st ON s.stall_type_id = st.id
                LEFT JOIN system_statuses ss ON s.status_id = ss.id
                LEFT JOIN status_colors sc ON ss.color_id = sc.id
                LEFT JOIN areas a ON s.area_id = a.id
                LEFT JOIN contracts c ON c.stall_id = s.id AND c.status_id = (SELECT id FROM system_statuses WHERE domain = 'contract' AND code = 'active')
                LEFT JOIN traders t ON c.trader_id = t.id
                LEFT JOIN business_lines bl ON t.business_line_id = bl.id";
        
        $where = [];
        $params = [];

        if ($marketId) {
            $where[] = "a.market_id = :market_id";
            $params['market_id'] = $marketId;
        }

        if ($areaId) {
            $where[] = "s.area_id = :area_id";
            $params['area_id'] = $areaId;
        }

        if ($status) {
            $where[] = "ss.code = :status";
            $params['status'] = $status;
        }

        if (!empty($search)) {
            $where[] = "(s.stall_code LIKE :search1 OR a.block LIKE :search2 OR a.lot LIKE :search3)";
            $params['search1'] = "%$search%";
            $params['search2'] = "%$search%";
            $params['search3'] = "%$search%";
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY a.area_name ASC, s.stall_code ASC";
        return $this->db->select($sql, $params);
    }

    public function getById($id) {
        $sql = "SELECT s.*, st.type_name AS stall_type, ss.code AS status, ss.status_name, sc.color_class, a.area_name, a.block, a.lot, t.fullname AS trader_name, bl.line_name AS business_line_name 
                FROM stalls s 
                LEFT JOIN stall_types st ON s.stall_type_id = st.id
                LEFT JOIN system_statuses ss ON s.status_id = ss.id
                LEFT JOIN status_colors sc ON ss.color_id = sc.id
                LEFT JOIN areas a ON s.area_id = a.id 
                LEFT JOIN contracts c ON c.stall_id = s.id AND c.status_id = (SELECT id FROM system_statuses WHERE domain = 'contract' AND code = 'active')
                LEFT JOIN traders t ON c.trader_id = t.id
                LEFT JOIN business_lines bl ON t.business_line_id = bl.id
                WHERE s.id = :id";
        return $this->db->selectOne($sql, ['id' => $id]);
    }

    public function create($data) {
        $statusModel = new statusModel();
        $emptyStatusId = $statusModel->getIdByCode('stall', 'empty');

        $sql = "INSERT INTO stalls (area_id, stall_code, stall_type_id, area_size, base_price, status_id) 
                VALUES (:area_id, :stall_code, :stall_type_id, :area_size, :base_price, :status_id)";
        
        $params = [
            'area_id'       => $data['area_id'],
            'stall_code'    => $data['stall_code'],
            'stall_type_id' => $data['stall_type_id'],
            'area_size'     => $data['area_size'],
            'base_price'    => $data['base_price'],
            'status_id'     => $data['status_id'] ?: $emptyStatusId
        ];

        $this->db->query($sql, $params);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $statusModel = new statusModel();
        $emptyStatusId = $statusModel->getIdByCode('stall', 'empty');

        $sql = "UPDATE stalls 
                SET area_id = :area_id, stall_code = :stall_code, 
                    stall_type_id = :stall_type_id, area_size = :area_size, base_price = :base_price, status_id = :status_id 
                WHERE id = :id";
        
        $params = [
            'id'            => $id,
            'area_id'       => $data['area_id'],
            'stall_code'    => $data['stall_code'],
            'stall_type_id' => $data['stall_type_id'],
            'area_size'     => $data['area_size'],
            'base_price'    => $data['base_price'],
            'status_id'     => $data['status_id'] ?: $emptyStatusId
        ];

        return $this->db->query($sql, $params);
    }

    public function updateStatus($id, $status) {
        if (is_numeric($status)) {
            $statusId = $status;
        } else {
            $statusModel = new statusModel();
            $statusId = $statusModel->getIdByCode('stall', $status);
        }
        $sql = "UPDATE stalls SET status_id = :status_id WHERE id = :id";
        return $this->db->query($sql, ['id' => $id, 'status_id' => $statusId]);
    }

    public function delete($id) {
        $sql = "DELETE FROM stalls WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }

    /**
     * Kiểm tra xem sạp có hợp đồng nào đang hoạt động không
     */
    public function hasActiveContract($stallId) {
        $sql = "SELECT COUNT(*) as count FROM contracts WHERE stall_id = :stall_id AND status_id = (SELECT id FROM system_statuses WHERE domain = 'contract' AND code = 'active')";
        $res = $this->db->selectOne($sql, ['stall_id' => $stallId]);
        return ($res['count'] ?? 0) > 0;
    }

    /**
     * Kiểm tra xem mã sạp đã tồn tại chưa
     */
    public function isStallCodeExists($code, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM stalls WHERE stall_code = :code";
        $params = ['code' => $code];
        if ($excludeId !== null) {
            $sql .= " AND id != :excludeId";
            $params['excludeId'] = $excludeId;
        }
        $res = $this->db->selectOne($sql, $params);
        return ($res['count'] ?? 0) > 0;
    }

    /**
     * Lấy danh sách các trạng thái của sạp
     */
    public function getStallStatuses() {
        $sql = "SELECT * FROM system_statuses WHERE domain = 'stall'";
        return $this->db->select($sql);
    }

    /**
     * Lấy danh sách sạp khả dụng để chuyển đổi
     */
    public function getAvailableStallsForTransfer($excludeId = null) {
        $sql = "SELECT s.id, s.stall_code, a.area_name, ss.status_name, ss.code AS status_code,
                       t.fullname AS trader_name
                FROM stalls s
                LEFT JOIN areas a ON s.area_id = a.id
                LEFT JOIN system_statuses ss ON s.status_id = ss.id
                LEFT JOIN contracts c ON c.stall_id = s.id AND c.status_id = (SELECT id FROM system_statuses WHERE domain = 'contract' AND code = 'active')
                LEFT JOIN traders t ON c.trader_id = t.id";
        
        $params = [];
        if ($excludeId !== null) {
            $sql .= " WHERE s.id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $sql .= " ORDER BY a.area_name ASC, s.stall_code ASC";
        return $this->db->select($sql, $params);
    }

    /**
     * Thực hiện chuyển đổi hoặc tráo đổi sạp giữa các tiểu thương
     * Trả về string thông báo kết quả
     */
    public function transferStall($currentStall, $newStall, $contract1) {
        try {
            $this->db->beginTransaction();

            $statusModel = new statusModel();
            $emptyStatusId = $statusModel->getIdByCode('stall', 'empty');
            $rentedStatusId = $statusModel->getIdByCode('stall', 'rented');

            $currentStallId = $currentStall['id'];
            $newStallId = $newStall['id'];

            // Kiểm tra trạng thái của sạp mới
            if ($newStall['status'] === 'empty') {
                // Trường hợp 1: Chuyển sang sạp trống (Đơn phương)
                $sqlUpdateContract = "UPDATE contracts SET stall_id = :new_stall_id WHERE id = :contract_id";
                $this->db->query($sqlUpdateContract, [
                    'new_stall_id' => $newStallId,
                    'contract_id'  => $contract1['id']
                ]);

                $this->updateStatus($currentStallId, $emptyStatusId);
                $this->updateStatus($newStallId, $rentedStatusId);
                $message = 'Chuyển đổi sạp thành công!';
            } else {
                // Trường hợp 2: Đổi sạp giữa 2 tiểu thương (Cả hai sạp đều đang hoạt động)
                $sqlContract2 = "SELECT * FROM contracts WHERE stall_id = :stall_id AND status_id = (SELECT id FROM system_statuses WHERE domain = 'contract' AND code = 'active') LIMIT 1";
                $contract2 = $this->db->selectOne($sqlContract2, ['stall_id' => $newStallId]);
                
                if (!$contract2) {
                    // Nếu sạp mới không có hợp đồng hoạt động nhưng trạng thái khác empty, vẫn cho phép chuyển đơn phương
                    $sqlUpdateContract = "UPDATE contracts SET stall_id = :new_stall_id WHERE id = :contract_id";
                    $this->db->query($sqlUpdateContract, [
                        'new_stall_id' => $newStallId,
                        'contract_id'  => $contract1['id']
                    ]);
                    $this->updateStatus($currentStallId, $emptyStatusId);
                    $this->updateStatus($newStallId, $rentedStatusId);
                    $message = 'Chuyển đổi sạp sang sạp mới thành công!';
                } else {
                    // Thực hiện tráo đổi (swap) stall_id của 2 hợp đồng hoạt động
                    $sqlUpdateContract1 = "UPDATE contracts SET stall_id = :new_stall_id WHERE id = :contract_id";
                    $this->db->query($sqlUpdateContract1, [
                        'new_stall_id' => $newStallId,
                        'contract_id'  => $contract1['id']
                    ]);

                    $sqlUpdateContract2 = "UPDATE contracts SET stall_id = :new_stall_id WHERE id = :contract_id";
                    $this->db->query($sqlUpdateContract2, [
                        'new_stall_id' => $currentStallId,
                        'contract_id'  => $contract2['id']
                    ]);

                    // Trạng thái của cả 2 sạp giữ nguyên là 'rented' (đã thuê)
                    $this->updateStatus($currentStallId, $rentedStatusId);
                    $this->updateStatus($newStallId, $rentedStatusId);
                    $message = 'Tráo đổi sạp giữa 2 tiểu thương thành công!';
                }
            }

            $this->db->commit();
            return $message;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }
}
