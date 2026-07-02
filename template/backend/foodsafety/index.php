<!-- Phân loại Tab & Nút thêm mới -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <!-- Nút chuyển đổi Tab -->
    <div class="segmented" role="radiogroup" style="max-width: 380px;">
        <label><input type="radio" name="fs-mode" value="docs" checked onclick="App.foodsafety.switchTab('docs')"><span>Giấy tờ & Chứng nhận</span></label>
        <label><input type="radio" name="fs-mode" value="inspections" onclick="App.foodsafety.switchTab('inspections')"><span>Thanh tra & Vi phạm</span></label>
    </div>
    
    <a href="<?php echo BASE_URL; ?>admin/foodsafety_add" class="btn btn-primary" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: white;">
        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 14px; height: 14px;"><path d="M8 2v12M2 8h12"/></svg>
        Khai báo Chứng nhận mới
    </a>
</div>

<!-- TAB 1: GIẤY TỜ & CHỨNG NHẬN (ATTP, SỨC KHỎE, TẬP HUẤN) -->
<div id="fs-docs" class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Hồ sơ Chứng nhận vệ sinh ATTP tiểu thương</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px;">Tiểu thương</th>
                        <th style="padding: 12px 16px;">Cơ sở kinh doanh</th>
                        <th style="padding: 12px 16px; width: 140px;">Giấy chứng nhận ATTP (E.1)</th>
                        <th style="padding: 12px 16px; width: 140px;">Khám sức khỏe (E.2)</th>
                        <th style="padding: 12px 16px; width: 140px;">Tập huấn ATTP (E.3)</th>
                        <th style="padding: 12px 16px; width: 120px;">Ngày hết hạn (E.4)</th>
                        <th style="padding: 12px 16px; width: 120px;">Trạng thái</th>
                        <th style="padding: 12px 16px; text-align: right; width: 80px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($certificates)): ?>
                        <?php foreach ($certificates as $index => $cert): ?>
                            <!-- Giả lập trạng thái khám sức khỏe & tập huấn -->
                            <?php 
                                $healthStatus = ['Đã khám (Hạn 2027)', 'Đã khám (Hạn 2026)', 'Hết hạn'];
                                $trainingStatus = ['Đã tập huấn', 'Đã tập huấn', 'Chưa tập huấn'];
                                $hStatus = $healthStatus[$index % count($healthStatus)];
                                $tStatus = $trainingStatus[$index % count($trainingStatus)];
                            ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($cert['trader_name']); ?>
                                </td>
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($cert['shop_name']); ?>
                                </td>
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600; font-size: 11.5px; color: var(--primary);">
                                    <?php echo htmlspecialchars($cert['cert_code']); ?>
                                </td>
                                <td style="padding: 14px 16px; color: <?php echo $hStatus === 'Hết hạn' ? 'var(--red)' : 'var(--text-muted)'; ?>;">
                                    <?php echo $hStatus; ?>
                                </td>
                                <td style="padding: 14px 16px; color: <?php echo $tStatus === 'Chưa tập huấn' ? 'var(--red)' : 'var(--text-muted)'; ?>;">
                                    <?php echo $tStatus; ?>
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($cert['expire_date']); ?>
                                </td>
                                <td style="padding: 14px 16px;">
                                    <?php if ($cert['status'] === 'active'): ?>
                                        <span class="status status-green">Đang hiệu lực</span>
                                    <?php else: ?>
                                        <span class="status status-red">Hết hạn</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <button class="btn btn-outline btn-sm" onclick="alert('Tính năng cập nhật hồ sơ đang phát triển!')" style="padding: 4px 8px; font-size: 11px;">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="padding: 30px; text-align: center; color: var(--text-muted);">Không có dữ liệu chứng nhận ATTP.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TAB 2: THANH TRA & VI PHẠM (E.6, E.7, E.8, E.9) -->
<div id="fs-inspections" class="card" style="display: none;">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Kế hoạch kiểm tra & Nhật ký vi phạm vệ sinh ATTP</div>
    </div>
    <div class="card-body" style="padding: 20px 0 0 0;">
        <!-- Kế hoạch thanh tra (E.6, E.7) -->
        <div style="padding: 0 20px 20px 20px;">
            <h4 style="font-size: 14px; font-weight: 600; color: var(--text-heading); margin-bottom: 12px;"><i class="fa-solid fa-circle-check text-success me-2"></i> Kế hoạch kiểm tra định kỳ (E.6, E.7)</h4>
            <div class="table-responsive">
                <table class="table" style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                    <thead>
                        <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                            <th style="padding: 10px 12px;">Đợt kiểm tra</th>
                            <th style="padding: 10px 12px;">Đoàn kiểm tra</th>
                            <th style="padding: 10px 12px;">Ngày dự kiến</th>
                            <th style="padding: 10px 12px;">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 10px 12px; font-weight: 600;">Kiểm tra định kỳ quý 2/2026</td>
                            <td style="padding: 10px 12px; color: var(--text-muted);">Ban quản lý chợ + Phòng Y tế Quận</td>
                            <td style="padding: 10px 12px; color: var(--text-muted);">15/07/2026</td>
                            <td style="padding: 10px 12px;"><span class="status status-yellow">Chưa thực hiện</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Nhật ký Vi phạm (E.8, E.9) -->
        <div style="padding: 20px 20px 20px 20px; border-top: 1px solid var(--border-color-light);">
            <h4 style="font-size: 14px; font-weight: 600; color: var(--text-heading); margin-bottom: 12px;"><i class="fa-solid fa-circle-exclamation text-danger me-2"></i> Biên bản Ghi nhận & Xử lý Vi phạm (E.8, E.9)</h4>
            <div class="table-responsive">
                <table class="table" style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                    <thead>
                        <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                            <th style="padding: 10px 12px; width: 120px;">Mã biên bản</th>
                            <th style="padding: 10px 12px;">Hộ kinh doanh vi phạm</th>
                            <th style="padding: 10px 12px;">Nội dung vi phạm</th>
                            <th style="padding: 10px 12px;">Hình thức xử lý (E.9)</th>
                            <th style="padding: 10px 12px; width: 140px;">Trạng thái xử lý</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td class="cell-mono" style="padding: 10px 12px; font-weight: 600;">BBVP-0089</td>
                            <td style="padding: 10px 12px; font-weight: 600;">Hộ kinh doanh Hoàng Thực Phẩm</td>
                            <td style="padding: 10px 12px; color: var(--text-muted);">Không đeo găng tay khi chế biến, bày thực phẩm chín không che đậy</td>
                            <td style="padding: 10px 12px; color: var(--red); font-weight: 600;">Phạt cảnh cáo, đình chỉ sạp 3 ngày</td>
                            <td style="padding: 10px 12px;"><span class="status status-green">Đã chấp hành xong</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


