<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div style="display: flex; gap: 8px;">
        <input type="text" class="form-control" placeholder="Tìm số hợp đồng, tiểu thương..." style="width: 250px; height: 36px; font-size: 13px;">
        <select class="form-control" style="width: 150px; height: 36px; font-size: 13px;">
            <option value="">Trạng thái hiệu lực</option>
            <option value="active">Còn hiệu lực</option>
            <option value="expired">Hết hạn</option>
        </select>
        <button class="btn btn-outline" style="height: 36px; padding: 0 16px;">Lọc</button>
    </div>
    
    <a href="<?php echo BASE_URL; ?>admin/contract_add" class="btn btn-primary" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: white;">
        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 14px; height: 14px;"><path d="M8 2v12M2 8h12"/></svg>
        Lập Hợp đồng mới
    </a>
</div>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Hồ sơ Hợp đồng thuê mặt bằng sạp</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px; width: 120px;">Số Hợp đồng</th>
                        <th style="padding: 12px 16px;">Tên tiểu thương</th>
                        <th style="padding: 12px 16px; width: 90px;">Mã sạp</th>
                        <th style="padding: 12px 16px; width: 100px;">Ngày ký</th>
                        <th style="padding: 12px 16px; width: 100px;">Ngày hết hạn</th>
                        <th style="padding: 12px 16px; width: 120px;">Đơn giá thuê</th>
                        <th style="padding: 12px 16px; width: 110px;">Đặt cọc</th>
                        <th style="padding: 12px 16px; width: 90px; text-align: center;">Phụ lục</th>
                        <th style="padding: 12px 16px; width: 110px;">Trạng thái</th>
                        <th style="padding: 12px 16px; text-align: right; width: 160px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($contracts)): ?>
                        <?php foreach ($contracts as $index => $contract): ?>
                            <!-- Giả lập số phụ lục (Mục C.5) -->
                            <?php 
                                $appendixCounts = [1, 2, 0];
                                $appCount = $appendixCounts[$index % count($appendixCounts)];
                            ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($contract['contract_code']); ?>
                                </td>
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($contract['trader_name']); ?>
                                </td>
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600;">
                                    <?php echo htmlspecialchars($contract['stall_code']); ?>
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($contract['start_date']); ?>
                                </td>
                                <td style="padding: 14px 16px; color: <?php echo $contract['status'] === 'expired' ? 'var(--red)' : 'var(--text-muted)'; ?>; font-weight: <?php echo $contract['status'] === 'expired' ? '600' : 'normal'; ?>;">
                                    <?php echo htmlspecialchars($contract['end_date']); ?>
                                </td>
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--primary);">
                                    <?php echo number_format($contract['price'], 0, ',', '.'); ?> đ
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted);">
                                    <?php echo number_format($contract['deposit'], 0, ',', '.'); ?> đ
                                </td>
                                <td style="padding: 14px 16px; text-align: center;">
                                    <?php if ($appCount > 0): ?>
                                        <button class="btn btn-ghost btn-sm" onclick="App.contract.viewAppendix('<?php echo htmlspecialchars($contract['contract_code']); ?>')" style="padding: 4px; color: var(--primary);" title="Xem phụ lục hợp đồng">
                                            <i class="fa-solid fa-paperclip"></i> (<?php echo $appCount; ?>)
                                        </button>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 11px;">Không có</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px;">
                                    <?php if ($contract['status'] === 'active'): ?>
                                        <span class="status status-green">Còn hiệu lực</span>
                                    <?php else: ?>
                                        <span class="status status-red">Đã hết hạn</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <div style="display: flex; justify-content: flex-end; gap: 4px;">
                                        <!-- In hợp đồng (Mục C.8) -->
                                        <button class="btn btn-outline btn-sm" onclick="App.contract.printContract('<?php echo htmlspecialchars($contract['contract_code']); ?>')" style="padding: 4px 6px;" title="In hợp đồng">
                                            <i class="fa-solid fa-print"></i>
                                        </button>
                                        <!-- Gia hạn hợp đồng (Mục C.2) -->
                                        <button class="btn btn-outline btn-sm" onclick="App.contract.renewContract('<?php echo htmlspecialchars($contract['contract_code']); ?>')" style="padding: 4px 6px; color: var(--primary);" title="Gia hạn">
                                            <i class="fa-solid fa-calendar-plus"></i>
                                        </button>
                                        <!-- Chấm dứt / Thanh lý hợp đồng (Mục C.3, C.4) -->
                                        <button class="btn btn-outline btn-sm" onclick="App.contract.terminateContract('<?php echo htmlspecialchars($contract['contract_code']); ?>')" style="padding: 4px 6px; color: var(--red);" title="Thanh lý / Chấm dứt">
                                            <i class="fa-solid fa-file-contract"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="padding: 30px; text-align: center; color: var(--text-muted);">Không có dữ liệu hợp đồng.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


