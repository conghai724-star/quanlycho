<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div style="display: flex; gap: 8px;">
        <select class="form-control" style="width: 140px; height: 36px; font-size: 13px;">
            <option value="">Trạng thái</option>
            <option value="paid">Đã thanh toán</option>
            <option value="unpaid">Chưa thanh toán</option>
        </select>
        <input type="text" class="form-control" placeholder="Mã sạp, tên tiểu thương..." style="width: 250px; height: 36px; font-size: 13px;">
        <button class="btn btn-outline" style="height: 36px; padding: 0 16px;">Lọc</button>
    </div>
    
    <button class="btn btn-primary" onclick="App.finance.simulateBillCalculation()" style="height: 36px; display: inline-flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-calculator"></i>
        Tổng hợp Hóa đơn tháng
    </button>
</div>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Hóa đơn thanh toán sạp chợ</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px; width: 120px;">Mã hóa đơn</th>
                        <th style="padding: 12px 16px; width: 100px;">Mã sạp</th>
                        <th style="padding: 12px 16px;">Tên tiểu thương</th>
                        <th style="padding: 12px 16px; width: 90px;">Kỳ thu</th>
                        <th style="padding: 12px 16px; width: 140px;">Tổng tiền</th>
                        <th style="padding: 12px 16px; width: 120px;">Hạn nộp tiền</th>
                        <th style="padding: 12px 16px; width: 130px;">Trạng thái</th>
                        <th style="padding: 12px 16px; text-align: right; width: 120px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bills)): ?>
                        <?php foreach ($bills as $bill): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($bill['bill_code']); ?>
                                </td>
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600;">
                                    <?php echo htmlspecialchars($bill['stall_code']); ?>
                                </td>
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($bill['trader_name']); ?>
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($bill['period']); ?>
                                </td>
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--primary);">
                                    <?php echo number_format($bill['total_amount'], 0, ',', '.'); ?> đ
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($bill['due_date']); ?>
                                </td>
                                <td style="padding: 14px 16px;">
                                    <?php if ($bill['status'] === 'paid'): ?>
                                        <span class="status status-green">Đã thanh toán</span>
                                    <?php else: ?>
                                        <span class="status status-red">Chưa thanh toán</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <div style="display: flex; justify-content: flex-end; gap: 4px;">
                                        <!-- Chi tiết hóa đơn (Bóc tách D.1 -> D.5) -->
                                        <button class="btn btn-outline btn-sm" onclick="App.finance.viewBillDetails('<?php echo htmlspecialchars($bill['bill_code']); ?>', '<?php echo htmlspecialchars($bill['stall_code']); ?>', '<?php echo htmlspecialchars($bill['trader_name']); ?>')" style="padding: 4px 6px;" title="Xem chi tiết hóa đơn">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <!-- Thu tiền chuyển hướng sang Form lập phiếu thu -->
                                        <a href="<?php echo BASE_URL; ?>admin/transaction_add?type=receipt" class="btn btn-outline btn-sm" style="padding: 4px 6px; text-decoration: none;" title="Lập Phiếu Thu">
                                            <i class="fa-solid fa-file-invoice-dollar"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="padding: 30px; text-align: center; color: var(--text-muted);">Không có dữ liệu hóa đơn.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


