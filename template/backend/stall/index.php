<!-- Định nghĩa style tùy biến cho trạng thái Trống (Màu trắng) -->
<style>
    .status-white {
        color: var(--text-muted);
    }
    .status-white:before {
        background: #fff;
        border: 1px solid var(--border-color);
    }
    /* Style tối ưu giao diện sáng tối cho status-white */
    [data-theme=dark] .status-white:before {
        background: #1a2332;
        border-color: rgba(255, 255, 255, 0.15);
    }
</style>

<!-- Thống kê tỷ lệ lấp đầy sạp chợ thực tế -->
<?php
$totalStalls = $stats['total'] ?? 0;
$rentedStalls = $stats['rented'] ?? 0;
$emptyStalls = $stats['empty'] ?? 0;
$repairingStalls = $stats['repairing'] ?? 0;

$rentedPercent = $totalStalls > 0 ? round(($rentedStalls / $totalStalls) * 100) : 0;
$emptyPercent = $totalStalls > 0 ? round(($emptyStalls / $totalStalls) * 100) : 0;
?>
<div class="row col-3" style="margin-bottom: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
    <!-- Sạp đã thuê -->
    <div class="card" style="margin-bottom: 0;">
        <div class="stat" style="padding: 16px;">
            <div class="stat-icon green" style="background-color: rgba(52, 168, 83, 0.1); color: #34A853;"><i class="fa-solid fa-store"></i></div>
            <div class="stat-content">
                <div class="stat-label">Sạp đã cho thuê</div>
                <div class="stat-value-row">
                    <span class="stat-value"><?php echo $rentedStalls; ?> / <?php echo $totalStalls; ?></span>
                    <span class="stat-change up" style="color: #34A853;"><?php echo $rentedPercent; ?>% lấp đầy</span>
                </div>
            </div>
        </div>
    </div>
    <!-- Sạp trống -->
    <div class="card" style="margin-bottom: 0;">
        <div class="stat" style="padding: 16px;">
            <div class="stat-icon yellow" style="background-color: rgba(251, 188, 4, 0.1); color: #FBBC04;"><i class="fa-solid fa-circle-plus"></i></div>
            <div class="stat-content">
                <div class="stat-label">Sạp trống sẵn sàng</div>
                <div class="stat-value-row">
                    <span class="stat-value"><?php echo $emptyStalls; ?> / <?php echo $totalStalls; ?></span>
                    <span class="stat-change up" style="color: #FBBC04;"><?php echo $emptyPercent; ?>% trống</span>
                </div>
            </div>
        </div>
    </div>
    <!-- Sạp đang bảo trì -->
    <div class="card" style="margin-bottom: 0;">
        <div class="stat" style="padding: 16px;">
            <div class="stat-icon red" style="background-color: rgba(234, 67, 53, 0.1); color: #EA4335;"><i class="fa-solid fa-wrench"></i></div>
            <div class="stat-content">
                <div class="stat-label">Sạp đang bảo trì</div>
                <div class="stat-value-row">
                    <span class="stat-value"><?php echo $repairingStalls; ?> sạp</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bộ lọc, Tab chọn & Nút thêm mới -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <!-- Form Lọc AJAX chuẩn mẫu tiểu thương -->
    <form action="<?php echo BASE_URL; ?>admin/stalls" method="GET" style="display: flex; gap: 8px; flex-wrap: wrap; margin: 0;">
        <input type="text" name="q" class="form-control" placeholder="Tìm mã sạp, dãy, lô..." value="<?php echo htmlspecialchars($search ?? ''); ?>" style="width: 240px; height: 36px; font-size: 13px;">
        
        <select name="area_id" class="form-control" style="width: 160px; height: 36px; font-size: 13px;">
            <option value="">Tất cả phân khu</option>
            <?php if (!empty($areas)): ?>
                <?php foreach ($areas as $a): ?>
                    <option value="<?php echo $a['id']; ?>" <?php echo ($area_filter ?? '') == $a['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($a['area_name']); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        
        <select name="status" class="form-control" style="width: 160px; height: 36px; font-size: 13px;">
            <option value="">Tất cả trạng thái</option>
            <?php if (!empty($statuses)): ?>
                <?php foreach ($statuses as $st): ?>
                    <option value="<?php echo htmlspecialchars($st['code']); ?>" <?php echo ($status_filter ?? '') === $st['code'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($st['status_name']); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        
        <button type="button" id="btn-filter-stalls" class="btn btn-outline" style="height: 36px; padding: 0 16px;">Lọc</button>
        <?php if (!empty($search) || !empty($area_filter) || !empty($status_filter)): ?>
            <a href="<?php echo BASE_URL; ?>admin/stalls" class="btn btn-ghost" style="height: 36px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; padding: 0 12px; color: var(--text-muted);">Xóa bộ lọc</a>
        <?php endif; ?>
    </form>
    
    <div style="display: flex; gap: 8px;">
        <a href="<?php echo BASE_URL; ?>admin/stall_add" class="btn btn-primary" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: white;">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 14px; height: 14px;"><path d="M8 2v12M2 8h12"/></svg>
            Khai báo Sạp mới
        </a>
    </div>
</div>

<!-- TAB 1: HIỂN THỊ DẠNG BẢNG (Dữ liệu thực) -->
<div id="view-table" class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Danh sách Sạp chợ & Mặt bằng (<span id="filter-total-stalls"><?php echo count($stalls); ?></span>)</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px; width: 120px;">Mã sạp</th>
                        <th style="padding: 12px 16px; width: 160px;">Phân khu</th>
                        <th style="padding: 12px 16px; width: 160px;">Vị trí cụ thể</th>
                        <th style="padding: 12px 16px; width: 110px;">Diện tích</th>
                        <th style="padding: 12px 16px; width: 160px;">Đơn giá thuê / tháng</th>
                        <th style="padding: 12px 16px;">Tiểu thương thuê</th>
                        <th style="padding: 12px 16px; width: 120px;">Trạng thái</th>
                        <th style="padding: 12px 16px; text-align: right; width: 130px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="table-body-stalls">
                    <?php require DIR_TEMPLATE . '/backend/stall/table_rows.php'; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- CSRF Token phục vụ AJAX -->
<?php csrf_field(); ?>

<!-- Nạp JS xử lý AJAX & Form sạp chợ -->
<script src="<?php echo BASE_URL; ?>public/assets/js/pages/admin/stall.js?v=<?php echo time(); ?>"></script>
