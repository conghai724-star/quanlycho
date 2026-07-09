<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div style="display: flex; gap: 8px;">
        <select class="form-control" style="width: 140px; height: 36px; font-size: 13px;">
            <option value="">Tất cả loại phiếu</option>
            <option value="receipt">Phiếu Thu (+)</option>
            <option value="payment">Phiếu Chi (-)</option>
        </select>
        <input type="text" class="form-control" placeholder="Mã phiếu, nội dung..." style="width: 250px; height: 36px; font-size: 13px;">
        <button class="btn btn-outline" style="height: 36px; padding: 0 16px;">Tìm</button>
    </div>
    
    <!-- Link trỏ đến Form lập phiếu thu và chi -->
    <div style="display: flex; gap: 8px;">
        <a href="<?php echo BASE_URL; ?>admin/transaction_add?type=payment" class="btn btn-outline" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
            <i class="fa-solid fa-minus text-danger"></i> Lập Phiếu Chi
        </a>
        <a href="<?php echo BASE_URL; ?>admin/transaction_add?type=receipt" class="btn btn-primary" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: white;">
            <i class="fa-solid fa-plus"></i> Lập Phiếu Thu
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Sổ nhật ký Thu - Chi tài chính chợ</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px; width: 120px;">Mã phiếu</th>
                        <th style="padding: 12px 16px; width: 120px;">Phân loại</th>
                        <th style="padding: 12px 16px;">Đối tượng giao dịch</th>
                        <th style="padding: 12px 16px; width: 150px;">Số tiền</th>
                        <th style="padding: 12px 16px;">Nội dung chi tiết</th>
                        <th style="padding: 12px 16px; width: 120px;">Ngày lập</th>
                        <th style="padding: 12px 16px; width: 120px;">Người lập</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $tx): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600; color: var(--text-heading); display: flex; align-items: center; gap: 6px;">
                                    <?php echo htmlspecialchars($tx['transaction_code']); ?>
                                    <?php if (!empty($tx['attachment_path'])): ?>
                                        <a href="<?php echo BASE_URL; ?>uploads/finance/<?php echo htmlspecialchars($tx['attachment_path']); ?>" target="_blank" style="color: var(--primary);" title="Xem tệp đính kèm">
                                            <i class="fa-solid fa-paperclip"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px;">
                                    <?php if ($tx['type'] === 'receipt'): ?>
                                        <span class="status status-green" style="font-weight: 600;"><i class="fa-solid fa-plus-circle me-1"></i> Phiếu Thu</span>
                                    <?php else: ?>
                                        <span class="status status-red" style="font-weight: 600;"><i class="fa-solid fa-minus-circle me-1"></i> Phiếu Chi</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($tx['target']); ?>
                                </td>
                                <td style="padding: 14px 16px; font-weight: 600; color: <?php echo $tx['type'] === 'receipt' ? 'var(--primary)' : 'var(--red)'; ?>;">
                                    <?php echo $tx['type'] === 'receipt' ? '+' : '-'; ?> <?php echo number_format($tx['amount'], 0, ',', '.'); ?> đ
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($tx['note']); ?>
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($tx['date']); ?>
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($tx['creator']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 30px; text-align: center; color: var(--text-muted);">Không có dữ liệu giao dịch thu chi.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


