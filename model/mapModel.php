<?php
/**
 * Model quản lý sơ đồ chợ tương tác (Market Map Elements)
 */
class mapModel {
    private $db;

    public function __construct() {
        $this->db = database::getInstance();
    }

    /**
     * Lấy danh sách toàn bộ phần tử trên bản đồ kèm thông tin liên kết sạp
     */
    public function getElements() {
        $sql = "SELECT mme.*,
                       s.stall_code, s.stall_type, s.area_size, s.base_price,
                       ss.code AS status_code, ss.status_name, sc.color_class,
                       a.area_name, a.block, a.lot,
                       t.fullname AS trader_name, t.phone AS trader_phone,
                       con.contract_number, con.end_date AS contract_end_date
                FROM market_map_elements mme
                LEFT JOIN stalls s ON mme.stall_id = s.id
                LEFT JOIN areas a ON s.area_id = a.id
                LEFT JOIN system_statuses ss ON s.status_id = ss.id
                LEFT JOIN status_colors sc ON ss.color_id = sc.id
                LEFT JOIN contracts con ON con.stall_id = s.id AND con.status_id = (SELECT id FROM system_statuses WHERE domain = 'contract' AND code = 'active')
                LEFT JOIN traders t ON con.trader_id = t.id
                ORDER BY mme.id ASC";
        
        return $this->db->select($sql);
    }

    /**
     * Lưu cấu hình bản đồ (Xóa cũ nạp mới trong transaction)
     */
    public function saveElements($elements) {
        try {
            $this->db->beginTransaction();

            // 1. Xóa sạch sơ đồ cũ
            $this->db->query("DELETE FROM market_map_elements");

            // Reset tọa độ cũ trên bảng stalls về NULL
            $this->db->query("UPDATE stalls SET map_coordinate_x = NULL, map_coordinate_y = NULL");

            // 2. Chèn các phần tử sơ đồ mới
            $sqlInsert = "INSERT INTO market_map_elements 
                            (element_type, element_name, stall_id, pos_x, pos_y, width, height, rotation, color) 
                          VALUES 
                            (:element_type, :element_name, :stall_id, :pos_x, :pos_y, :width, :height, :rotation, :color)";

            $sqlUpdateStall = "UPDATE stalls SET map_coordinate_x = :pos_x, map_coordinate_y = :pos_y WHERE id = :stall_id";

            foreach ($elements as $el) {
                $params = [
                    'element_type' => $el['element_type'],
                    'element_name' => $el['element_name'] ?: null,
                    'stall_id'     => !empty($el['stall_id']) ? (int)$el['stall_id'] : null,
                    'pos_x'        => (int)$el['pos_x'],
                    'pos_y'        => (int)$el['pos_y'],
                    'width'        => (int)$el['width'],
                    'height'       => (int)$el['height'],
                    'rotation'     => (int)$el['rotation'],
                    'color'        => $el['color'] ?: null,
                ];

                $this->db->query($sqlInsert, $params);

                // Nếu là sạp, đồng bộ tọa độ ngược về bảng stalls
                if ($el['element_type'] === 'stall' && !empty($el['stall_id'])) {
                    $this->db->query($sqlUpdateStall, [
                        'pos_x'    => (int)$el['pos_x'],
                        'pos_y'    => (int)$el['pos_y'],
                        'stall_id' => (int)$el['stall_id']
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Lấy danh sách các sạp chưa được gán vào sơ đồ
     */
    public function getUnmappedStalls() {
        $sql = "SELECT s.id, s.stall_code, s.base_price, s.area_size, ss.status_name, sc.color_class
                FROM stalls s
                LEFT JOIN system_statuses ss ON s.status_id = ss.id
                LEFT JOIN status_colors sc ON ss.color_id = sc.id
                WHERE s.id NOT IN (SELECT stall_id FROM market_map_elements WHERE stall_id IS NOT NULL)
                  AND ss.code != '99'
                ORDER BY s.stall_code ASC";
        
        return $this->db->select($sql);
    }
}
