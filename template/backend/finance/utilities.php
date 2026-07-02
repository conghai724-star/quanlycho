<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div style="display: flex; gap: 8px;">
        <select class="form-control" style="width: 140px; height: 36px; font-size: 13px;">
            <option value="06/2026">Kỳ 06/2026</option>
            <option value="05/2026">Kỳ 05/2026</option>
        </select>
        <input type="text" class="form-control" placeholder="Mã sạp..." style="width: 150px; height: 36px; font-size: 13px;">
        <button class="btn btn-outline" style="height: 36px; padding: 0 16px;">Tìm</button>
    </div>
    
    <!-- Link trỏ đến Form ghi số điện nước mới -->
    <a href="<?php echo BASE_URL; ?>admin/utility_add" class="btn btn-primary" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: white;">
        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 14px; height: 14px;"><path d="M8 2v12M2 8h12"/></svg>
        Ghi điện nước mới
    </a>
</div>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Bảng chốt số điện & nước hàng tháng</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px; width: 100px;">Kỳ ghi</th>
                        <th style="padding: 12px 16px; width: 100px;">Mã sạp</th>
                        <th style="padding: 12px 16px; text-align: center;">Chỉ số điện (Cũ -> Mới)</th>
                        <th style="padding: 12px 16px; text-align: center; width: 110px;">Điện tiêu thụ</th>
                        <th style="padding: 12px 16px; text-align: center;">Chỉ số nước (Cũ -> Mới)</th>
                        <th style="padding: 12px 16px; text-align: center; width: 110px;">Nước tiêu thụ</th>
                        <th style="padding: 12px 16px; width: 120px;">Ngày chốt số</th>
                        <th style="padding: 12px 16px; width: 120px;">Người chốt</th>
                        <th style="padding: 12px 16px; text-align: right; width: 80px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($readings)): ?>
                        <?php foreach ($readings as $reading): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($reading['period']); ?>
                                </td>
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($reading['stall_code']); ?>
                                </td>
                                <td style="padding: 14px 16px; text-align: center; color: var(--text-muted);">
                                    <?php echo $reading['old_electric']; ?> <i class="fa-solid fa-arrow-right-long" style="font-size: 11px; margin: 0 4px;"></i> <span style="font-weight: 600; color: var(--text-heading);"><?php echo $reading['new_electric']; ?></span>
                                </td>
                                <td style="padding: 14px 16px; text-align: center; font-weight: 600; color: #f59f00;">
                                    <?php echo ($reading['new_electric'] - $reading['old_electric']); ?> kWh
                                </td>
                                <td style="padding: 14px 16px; text-align: center; color: var(--text-muted);">
                                    <?php echo $reading['old_water']; ?> <i class="fa-solid fa-arrow-right-long" style="font-size: 11px; margin: 0 4px;"></i> <span style="font-weight: 600; color: var(--text-heading);"><?php echo $reading['new_water']; ?></span>
                                </td>
                                <td style="padding: 14px 16px; text-align: center; font-weight: 600; color: #066fd1;">
                                    <?php echo ($reading['new_water'] - $reading['old_water']); ?> m³
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($reading['recorded_date']); ?>
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($reading['recorder']); ?>
                                </td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <button class="btn btn-outline btn-sm" onclick="alert('Tính năng chỉnh sửa đang phát triển!')" style="padding: 4px 8px; font-size: 11px;">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="padding: 30px; text-align: center; color: var(--text-muted);">Không có dữ liệu ghi số điện nước.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


