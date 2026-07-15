<?php
/**
 * Lớp dịch vụ quản lý phân quyền và giới hạn phạm vi đa chợ (Multi-market Service)
 */
class marketService {

    /**
     * Kiểm tra xem người dùng hiện tại có phải là Super Admin không.
     * Super Admin là người dùng có vai trò 'admin' hoặc user_group = 1.
     *
     * @return bool
     */
    public static function isSuperAdmin(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $userRole = session::get('user_role');
        $userGroup = session::get('user_group');
        
        return $userGroup == 1 || $userRole === 'admin';
    }

    /**
     * Lấy ID chợ hiện tại đang hoạt động từ Session.
     * Nếu chưa chọn, tự động lấy chợ hợp lệ đầu tiên hoặc mặc định là 1.
     *
     * @return int
     */
    public static function currentMarketId(): int {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $marketId = session::get('active_market_id');
        if ($marketId) {
            return (int)$marketId;
        }

        // Tìm chợ hợp lệ đầu tiên được quyền truy cập
        $accessible = self::getAccessibleMarketIds();
        if (!empty($accessible)) {
            $marketId = $accessible[0];
        } else {
            $marketId = 1; // Mặc định dự phòng
        }

        session::set('active_market_id', (int)$marketId);
        return (int)$marketId;
    }

    /**
     * Lấy danh sách các ID chợ mà người dùng hiện tại được phép truy cập.
     * Super Admin có quyền truy cập toàn bộ các chợ đang hoạt động.
     *
     * @return array
     */
    public static function getAccessibleMarketIds(): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $cached = session::get('accessible_market_ids');
        if (is_array($cached)) {
            return $cached;
        }

        $userId = session::get('user_id');
        if (!$userId) {
            return [];
        }

        $db = database::getInstance();
        if (self::isSuperAdmin()) {
            // Super Admin truy cập toàn bộ các chợ đang hoạt động
            $rows = $db->select("SELECT id FROM markets WHERE status_code = 'active'");
            $ids = array_map(function($r) { return (int)$r['id']; }, $rows);
        } else {
            // Nhân viên thường chỉ truy cập các chợ được phân công trong user_markets
            $rows = $db->select("
                SELECT market_id 
                FROM user_markets um
                JOIN markets m ON um.market_id = m.id
                WHERE um.user_id = :user_id AND m.status_code = 'active'
            ", ['user_id' => $userId]);
            $ids = array_map(function($r) { return (int)$r['market_id']; }, $rows);
        }

        session::set('accessible_market_ids', $ids);
        return $ids;
    }

    /**
     * Tự động bổ sung điều kiện lọc theo phạm vi chợ (market_id) vào câu truy vấn SQL.
     *
     * @param string $sql Câu truy vấn gốc
     * @param string $alias Tên alias của bảng (nếu có, ví dụ: 'a' hoặc 'stalls')
     * @return string Câu truy vấn đã được bổ sung điều kiện lọc
     */
    public static function applyScope(string $sql, string $alias = ''): string {
        $marketId = self::currentMarketId();
        
        $prefix = $alias ? "{$alias}." : "";
        $condition = "{$prefix}market_id = {$marketId}";
        
        // Kiểm tra xem đã có mệnh đề WHERE trong câu truy vấn chưa
        if (stripos($sql, 'where') !== false) {
            return $sql . " AND " . $condition;
        } else {
            return $sql . " WHERE " . $condition;
        }
    }

    /**
     * Kiểm tra quyền thực hiện thao tác ghi/sửa dữ liệu đối với một chợ cụ thể.
     * Ném ra Exception nếu không có quyền.
     *
     * @param int|string $marketId ID chợ cần kiểm tra
     * @throws Exception
     */
    public static function checkWritePermission($marketId) {
        $accessible = self::getAccessibleMarketIds();
        if (!in_array((int)$marketId, $accessible) && !self::isSuperAdmin()) {
            throw new Exception("Bạn không có quyền thực hiện thao tác tại chợ này.");
        }
    }
}
