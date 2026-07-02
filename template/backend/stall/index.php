<!-- Thống kê tỷ lệ lấp đầy sạp chợ (Mục B.9 trong Excel) -->
<div class="row col-3" style="margin-bottom: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
    <!-- Sạp đã thuê -->
    <div class="card" style="margin-bottom: 0;">
        <div class="stat" style="padding: 16px;">
            <div class="stat-icon green" style="background-color: rgba(52, 168, 83, 0.1); color: #34A853;"><i class="fa-solid fa-store"></i></div>
            <div class="stat-content">
                <div class="stat-label">Sạp đã cho thuê</div>
                <div class="stat-value-row">
                    <span class="stat-value">3 / 5</span>
                    <span class="stat-change up" style="color: #34A853;">60% lấp đầy</span>
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
                    <span class="stat-value">2 / 5</span>
                    <span class="stat-change up" style="color: #FBBC04;">40% trống</span>
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
                    <span class="stat-value">1 sạp</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bộ lọc, Tab chọn & Nút thêm mới -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <!-- Nút chuyển đổi Tab -->
    <div class="segmented" role="radiogroup" style="max-width: 320px;">
        <label><input type="radio" name="view-mode" value="table" checked onclick="App.stall.switchView('table')"><span>Danh sách bảng</span></label>
        <label><input type="radio" name="view-mode" value="map" onclick="App.stall.switchView('map')"><span>Sơ đồ sạp chợ (Map)</span></label>
    </div>
    
    <div style="display: flex; gap: 8px;">
        <a href="<?php echo BASE_URL; ?>admin/stall_add" class="btn btn-primary" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: white;">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 14px; height: 14px;"><path d="M8 2v12M2 8h12"/></svg>
            Khai báo Sạp mới
        </a>
    </div>
</div>

<!-- TAB 1: HIỂN THỊ DẠNG BẢNG (Mặc định) -->
<div id="view-table" class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Danh sách Sạp chợ & Mặt bằng</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px; width: 120px;">Mã sạp</th>
                        <th style="padding: 12px 16px;">Phân khu</th>
                        <th style="padding: 12px 16px;">Vị trí cụ thể</th>
                        <th style="padding: 12px 16px; width: 120px;">Diện tích</th>
                        <th style="padding: 12px 16px; width: 150px;">Đơn giá thuê / tháng</th>
                        <th style="padding: 12px 16px; width: 140px;">Trạng thái</th>
                        <th style="padding: 12px 16px; text-align: right; width: 120px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($stalls)): ?>
                        <?php foreach ($stalls as $stall): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($stall['stall_code']); ?>
                                </td>
                                <td style="padding: 14px 16px;">
                                    <span class="chip"><?php echo htmlspecialchars($stall['zone']); ?></span>
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($stall['location']); ?>
                                </td>
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($stall['area']); ?> m²
                                </td>
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--primary);">
                                    <?php echo number_format($stall['price'], 0, ',', '.'); ?> đ
                                </td>
                                <td style="padding: 14px 16px;">
                                    <?php if ($stall['status'] === 'rented'): ?>
                                        <span class="status status-green">Đã cho thuê</span>
                                    <?php elseif ($stall['status'] === 'empty'): ?>
                                        <span class="status status-yellow">Đang trống</span>
                                    <?php else: ?>
                                        <span class="status status-red">Đang sửa chữa</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <div style="display: flex; justify-content: flex-end; gap: 6px;">
                                        <a href="<?php echo BASE_URL; ?>admin/stall_edit/<?php echo $stall['id']; ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 11px; text-decoration: none;" title="Chỉnh sửa">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 30px; text-align: center; color: var(--text-muted);">Không có dữ liệu sạp.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TAB 2: HIỂN THỊ DẠNG SƠ ĐỒ CHỢ (Mục B.2 trong Excel) -->
<div id="view-map" class="card" style="display: none;">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Sơ đồ mặt bằng Kiot sạp chợ trực quan</div>
    </div>
    <div class="card-body" style="padding: 24px;">
        
        <!-- Chỉ dẫn màu sắc -->
        <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 24px; font-size: 12px; border-bottom: 1px solid var(--border-color-light); padding-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 14px; height: 14px; background-color: var(--green); border-radius: 4px;"></span>
                <span>Đã cho thuê (Đang kinh doanh)</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 14px; height: 14px; background-color: var(--yellow); border-radius: 4px;"></span>
                <span>Sạp trống (Sẵn sàng cho thuê)</span>
            </div>
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="display: inline-block; width: 14px; height: 14px; background-color: var(--red); border-radius: 4px;"></span>
                <span>Đang bảo trì / Sửa chữa</span>
            </div>
        </div>

        <!-- Layout Sơ đồ chợ phân theo Phân khu -->
        <div style="display: flex; flex-direction: column; gap: 32px;">
            <!-- Phân khu A -->
            <div>
                <h4 style="font-size: 14px; font-weight: 600; color: var(--text-heading); margin-bottom: 12px;"><i class="fa-solid fa-shirt text-primary me-2"></i> Khu A (Thời trang - Quần áo)</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 12px;">
                    <div onclick="App.stall.clickStall('SẠP-A01', 'rented', 'Nguyễn Thị Thu Hà', 'Quần áo')" style="background-color: var(--green); color: white; padding: 20px; border-radius: var(--radius); cursor: pointer; text-align: center;">
                        <strong style="display: block; font-size: 15px;">SẠP-A01</strong>
                        <span style="font-size: 11px; opacity: 0.9;">Đã thuê (Hà)</span>
                    </div>
                    <div onclick="App.stall.clickStall('SẠP-A02', 'empty', '', '')" style="background-color: var(--yellow); color: #000; padding: 20px; border-radius: var(--radius); cursor: pointer; text-align: center;">
                        <strong style="display: block; font-size: 15px;">SẠP-A02</strong>
                        <span style="font-size: 11px; opacity: 0.8;">Đang trống</span>
                    </div>
                    <div onclick="App.stall.clickStall('SẠP-A03', 'rented', 'Phạm Minh Tuấn', 'Thời trang')" style="background-color: var(--green); color: white; padding: 20px; border-radius: var(--radius); cursor: pointer; text-align: center;">
                        <strong style="display: block; font-size: 15px;">SẠP-A03</strong>
                        <span style="font-size: 11px; opacity: 0.9;">Đã thuê (Tuấn)</span>
                    </div>
                    <div onclick="App.stall.clickStall('SẠP-A04', 'empty', '', '')" style="background-color: var(--yellow); color: #000; padding: 20px; border-radius: var(--radius); cursor: pointer; text-align: center;">
                        <strong style="display: block; font-size: 15px;">SẠP-A04</strong>
                        <span style="font-size: 11px; opacity: 0.8;">Đang trống</span>
                    </div>
                </div>
            </div>

            <!-- Phân khu B -->
            <div>
                <h4 style="font-size: 14px; font-weight: 600; color: var(--text-heading); margin-bottom: 12px;"><i class="fa-solid fa-apple-whole text-success me-2"></i> Khu B (Thực phẩm tươi sống)</h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 12px;">
                    <div onclick="App.stall.clickStall('SẠP-B01', 'rented', 'Trần Văn Hoàng', 'Thịt gia súc')" style="background-color: var(--green); color: white; padding: 20px; border-radius: var(--radius); cursor: pointer; text-align: center;">
                        <strong style="display: block; font-size: 15px;">SẠP-B01</strong>
                        <span style="font-size: 11px; opacity: 0.9;">Đã thuê (Hoàng)</span>
                    </div>
                    <div onclick="App.stall.clickStall('SẠP-B02', 'repairing', '', '')" style="background-color: var(--red); color: white; padding: 20px; border-radius: var(--radius); cursor: pointer; text-align: center;">
                        <strong style="display: block; font-size: 15px;">SẠP-B02</strong>
                        <span style="font-size: 11px; opacity: 0.9;">Đang sửa chữa</span>
                    </div>
                    <div onclick="App.stall.clickStall('SẠP-B03', 'empty', '', '')" style="background-color: var(--yellow); color: #000; padding: 20px; border-radius: var(--radius); cursor: pointer; text-align: center;">
                        <strong style="display: block; font-size: 15px;">SẠP-B03</strong>
                        <span style="font-size: 11px; opacity: 0.8;">Đang trống</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


