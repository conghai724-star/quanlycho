<?php
/**
 * Lớp dịch vụ quản lý phân quyền và giới hạn phạm vi đa chợ (Multi-market Service)
 */
class marketService {

    /**
     * Kiểm tra xem người dùng hiện tại có phải là Quản trị tối cao (Super Admin) không.
     *
     * @return bool
     */
    public static function isSuperAdmin(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $actorCode = session::get('actor_code');
        return $actorCode === 'super_market';
    }

    /**
     * Kiểm tra xem người dùng hiện tại có phải là Quản lý chợ (Market Manager) không.
     *
     * @return bool
     */
    public static function isAdminMarket(): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $actorCode = session::get('actor_code');
        return $actorCode === 'admin_market';
    }

    /**
     * Kiểm tra quyền truy cập phân hệ (module) cụ thể đối với chợ hiện tại.
     *
     * @param string $module Tên phân hệ cần kiểm tra ('trader', 'stall', 'contract', 'finance', 'foodsafety')
     * @return bool
     */
    public static function checkModuleAccess(string $module): bool {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Quản trị tối cao có toàn quyền trên mọi phân hệ
        if (self::isSuperAdmin()) {
            return true;
        }

        $currentMarketId = self::currentMarketId();
        $accessibleMarkets = self::getAccessibleMarketIds();

        // Kiểm tra xem user có quyền truy cập vào chợ hiện tại không
        if (!in_array($currentMarketId, $accessibleMarkets)) {
            return false;
        }

        // 2. Quản lý chợ (admin_market) có toàn quyền trên các phân hệ thuộc chợ của mình
        if (self::isAdminMarket()) {
            return true;
        }

        // 3. Nhân viên vận hành (admin) chỉ được vào phân hệ được tick chọn
        $userId = session::get('user_id');
        if (!$userId) {
            return false;
        }

        $db = database::getInstance();
        $res = $db->selectOne("
            SELECT id 
            FROM user_market_permissions 
            WHERE user_id = :user_id AND market_id = :market_id AND module_code = :module_code
        ", [
            'user_id'     => $userId,
            'market_id'   => $currentMarketId,
            'module_code' => $module
        ]);

        return !empty($res);
    }

    /**
     * Yêu cầu quyền truy cập phân hệ, nếu không có quyền sẽ hiển thị trang 403 hoặc trả về lỗi JSON.
     *
     * @param string $module Tên phân hệ
     */
    public static function requireModuleAccess(string $module) {
        if (!self::checkModuleAccess($module)) {
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(403);
                echo json_encode([
                    'status'  => 403,
                    'message' => 'Bạn không có quyền truy cập chức năng này tại chợ đang chọn.'
                ], JSON_UNESCAPED_UNICODE);
                exit();
            } else {
                http_response_code(403);
                // Hiển thị giao diện báo lỗi 403
                echo "<div style='padding: 50px; text-align: center; font-family: sans-serif;'>";
                echo "<h1 style='color: #e74c3c; font-size: 48px; margin-bottom: 10px;'>403 Forbidden</h1>";
                echo "<p style='color: #7f8c8d; font-size: 18px;'>Bạn không có quyền truy cập vào phân hệ này tại chợ hiện tại.</p>";
                echo "<a href='" . BASE_URL . "admin/dashboard' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background: #3498db; color: #fff; text-decoration: none; border-radius: 4px;'>Quay lại Trang chủ</a>";
                echo "</div>";
                exit();
            }
        }
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
            // Nhân viên hoặc Quản lý chợ chỉ truy cập các chợ được phân công trong user_markets
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
