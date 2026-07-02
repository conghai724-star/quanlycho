<!-- Thanh tìm kiếm, xuất file và nút thêm mới -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <!-- Bộ lọc nhanh -->
    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <input type="text" class="form-control" placeholder="Tìm tên, SĐT, CCCD..." style="width: 240px; height: 36px; font-size: 13px;">
        <select class="form-control" style="width: 150px; height: 36px; font-size: 13px;">
            <option value="">Tất cả ngành hàng</option>
            <option value="Thực phẩm">Thực phẩm</option>
            <option value="Thời trang">Thời trang</option>
            <option value="Ẩm thực">Ẩm thực</option>
        </select>
        <button class="btn btn-outline" style="height: 36px; padding: 0 16px;">Lọc</button>
    </div>
    
    <!-- Xuất file Excel/PDF và Thêm mới -->
    <div style="display: flex; gap: 8px;">
        <button class="btn btn-outline" onclick="App.merchant.exportData('excel')" style="height: 36px; display: inline-flex; align-items: center; gap: 6px;" title="Xuất dữ liệu ra file Excel">
            <i class="fa-regular fa-file-excel text-success"></i> Xuất Excel
        </button>
        <button class="btn btn-outline" onclick="App.merchant.exportData('pdf')" style="height: 36px; display: inline-flex; align-items: center; gap: 6px;" title="Xuất dữ liệu ra file PDF">
            <i class="fa-regular fa-file-pdf text-danger"></i> Xuất PDF
        </button>
        <a href="<?php echo BASE_URL; ?>admin/trader_add" class="btn btn-primary" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: white;">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 14px; height: 14px;"><path d="M8 2v12M2 8h12"/></svg>
            Thêm Tiểu Thương
        </a>
    </div>
</div>

<!-- Bảng danh sách Tiểu thương -->
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Hồ sơ & Công nợ Tiểu thương hoạt động</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px; width: 110px;">Mã tiểu thương</th>
                        <th style="padding: 12px 16px;">Họ và tên</th>
                        <th style="padding: 12px 16px; width: 110px;">Điện thoại</th>
                        <th style="padding: 12px 16px; width: 110px;">Số CCCD</th>
                        <th style="padding: 12px 16px;">Địa chỉ</th>
                        <th style="padding: 12px 16px; width: 120px;">Ngành hàng</th>
                        <th style="padding: 12px 16px; width: 120px;">Công nợ</th>
                        <th style="padding: 12px 16px; width: 100px; text-align: center;">Giấy phép</th>
                        <th style="padding: 12px 16px; width: 120px;">Trạng thái</th>
                        <th style="padding: 12px 16px; text-align: right; width: 110px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($traders)): ?>
                        <?php foreach ($traders as $index => $trader): ?>
                            <!-- Giả lập dữ liệu công nợ và giấy phép cho mockup -->
                            <?php 
                                $debts = ['0 đ', '1.250.000 đ', '0 đ', '450.000 đ'];
                                $hasCert = [true, true, false, true];
                                $debtVal = $debts[$index % count($debts)];
                                $certVal = $hasCert[$index % count($hasCert)];
                            ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($trader['trader_code']); ?>
                                </td>
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($trader['fullname']); ?>
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($trader['phone']); ?>
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted); font-family: monospace;">
                                    <?php echo htmlspecialchars($trader['cccd']); ?>
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted); max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    <?php echo htmlspecialchars($trader['address']); ?>
                                </td>
                                <td style="padding: 14px 16px;">
                                    <span class="chip"><?php echo htmlspecialchars($trader['business_line'] ?: 'Chưa cập nhật'); ?></span>
                                </td>
                                <td style="padding: 14px 16px; font-weight: 600; color: <?php echo $debtVal !== '0 đ' ? 'var(--red)' : 'var(--text-muted)'; ?>;">
                                    <?php echo $debtVal; ?>
                                </td>
                                <td style="padding: 14px 16px; text-align: center;">
                                    <?php if ($certVal): ?>
                                        <button class="btn btn-ghost btn-sm" onclick="App.merchant.viewLicense('<?php echo htmlspecialchars($trader['fullname']); ?>')" style="padding: 4px; color: var(--primary);" title="Xem giấy phép kinh doanh">
                                            <i class="fa-regular fa-id-card" style="font-size: 16px;"></i>
                                        </button>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 11px;">Chưa nộp</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px;">
                                    <?php if ($trader['status'] === 'active'): ?>
                                        <span class="status status-green">Đang kinh doanh</span>
                                    <?php elseif ($trader['status'] === 'suspended'): ?>
                                        <span class="status status-yellow">Tạm nghỉ</span>
                                    <?php else: ?>
                                        <span class="status status-red">Ngừng kinh doanh</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <div style="display: flex; justify-content: flex-end; gap: 6px;">
                                        <a href="<?php echo BASE_URL; ?>admin/trader_edit/<?php echo $trader['id']; ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 11px; text-decoration: none;" title="Sửa">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <button class="btn btn-ghost btn-sm" onclick="App.merchant.confirmDelete(<?php echo $trader['id']; ?>, '<?php echo htmlspecialchars($trader['fullname']); ?>')" style="padding: 4px 8px; font-size: 11px; color: #EA4335;" title="Xóa">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="padding: 30px; text-align: center; color: var(--text-muted);">Không có dữ liệu tiểu thương.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


