<?php
/**
 * Lớp quản lý và sinh thông báo lỗi/thành công tự động cho toàn hệ thống
 */
class message {
    
    private static $entities = [
        'trader'   => 'tiểu thương',
        'user'     => 'người dùng',
        'stall'    => 'sạp',
        'contract' => 'hợp đồng',
        'bill'     => 'hóa đơn',
        'appendix' => 'phụ lục',
        'certificate' => 'giấy tờ/chứng nhận ATTP'
    ];

    private static $actions = [
        'create' => 'Thêm',
        'add'    => 'Thêm',
        'update' => 'Cập nhật',
        'edit'   => 'Cập nhật',
        'delete' => 'Xóa'
    ];

    /**
     * Sinh thông báo lỗi tự động theo loại lỗi và đối tượng
     */
    public static function error($type, $entity) {
        $entityName = self::$entities[$entity] ?? $entity;
        
        $templates = [
            'not_found'          => "{$entityName} không tồn tại trên hệ thống",
            'missing_id'         => "thiếu tham số ID {$entityName}",
            'method_not_allowed' => "phương thức không được hỗ trợ"
        ];

        $msg = $templates[$type] ?? $type;
        return self::mb_ucfirst($msg);
    }

    /**
     * Sinh thông báo kết quả hành động (Thành công / Thất bại)
     */
    public static function result($action, $entity, $isSuccess, $detail = '') {
        $actionName = self::$actions[$action] ?? $action;
        $entityName = self::$entities[$entity] ?? $entity;

        if ($isSuccess) {
            return "{$actionName} {$entityName} thành công";
        }

        $message = "{$actionName} {$entityName} thất bại";
        if (!empty($detail)) {
            // Nếu chi tiết lỗi khớp với bộ dịch lỗi mẫu
            $errorDetail = self::$entities[$detail] ?? $detail;
            if (isset(self::$entities[$detail])) {
                $errorDetail = self::error($detail, $entity);
            }
            $message .= ": " . $errorDetail;
        }
        return $message;
    }

    /**
     * Helper viết hoa chữ cái đầu tiên (hỗ trợ UTF-8 cho Tiếng Việt)
     */
    private static function mb_ucfirst($string) {
        if (empty($string)) return '';
        return mb_strtoupper(mb_substr($string, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($string, 1, null, 'UTF-8');
    }
}
