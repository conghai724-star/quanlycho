<!-- Form Lập Hóa Đơn Dịch Vụ Mới -->
<div style="margin-bottom: 20px;">
    <a href="<?php echo BASE_URL; ?>admin/bills" class="btn btn-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách hóa đơn
    </a>
</div>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Lập Hóa Đơn Dịch Vụ Mới</div>
    </div>
    <div class="card-body" style="padding: 24px;">
        <form action="<?php echo BASE_URL; ?>admin/bill_add" method="POST" enctype="multipart/form-data">
            <?php csrf_field(); ?>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Hợp đồng / Sạp thuê -->
                <div class="form-group">
                    <label class="form-label" for="contract_id" style="font-weight: 500;">Chọn Sạp / Hợp đồng thuê <span style="color: var(--red)">*</span></label>
                    <select id="contract_id" name="contract_id" class="form-control" required>
                        <option value="">-- Chọn sạp đang hoạt động --</option>
                        <?php if (!empty($contracts)): ?>
                            <?php foreach ($contracts as $c): ?>
                                <option value="<?php echo $c['id']; ?>">
                                    <?php echo htmlspecialchars($c['stall_code']); ?> - <?php echo htmlspecialchars($c['trader_name']); ?> (HĐ: <?php echo htmlspecialchars($c['contract_number'] ?? ($c['contract_code'] ?? '')); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <!-- Fallback dữ liệu mẫu nếu DB trống -->
                            <option value="1">SẠP-A01 - Nguyễn Thị Thu Hà (HĐ-2026-0001)</option>
                            <option value="2">SẠP-B01 - Trần Văn Hoàng (HĐ-2026-0002)</option>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Mã hóa đơn -->
                <div class="form-group">
                    <label class="form-label" for="bill_code" style="font-weight: 500;">Mã hóa đơn <span style="color: var(--red)">*</span></label>
                    <input type="text" id="bill_code" name="bill_code" class="form-control" placeholder="Ví dụ: HD-202607-003" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Ngày lập -->
                <div class="form-group">
                    <label class="form-label" for="invoice_date" style="font-weight: 500;">Ngày lập hóa đơn <span style="color: var(--red)">*</span></label>
                    <input type="date" id="invoice_date" name="invoice_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <!-- Hạn thanh toán -->
                <div class="form-group">
                    <label class="form-label" for="due_date" style="font-weight: 500;">Hạn thanh toán hóa đơn <span style="color: var(--red)">*</span></label>
                    <input type="date" id="due_date" name="due_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+15 days')); ?>" required>
                </div>
            </div>

            <h4 style="margin: 24px 0 12px 0; padding-bottom: 8px; border-bottom: 1px solid var(--border-color-light); font-size: 14px; font-weight: 600; color: var(--primary);">Các khoản chi tiết phí (VNĐ)</h4>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
                <!-- Tiền thuê sạp -->
                <div class="form-group">
                    <label class="form-label" for="rent_amount" style="font-size: 13px;">Tiền thuê sạp</label>
                    <input type="number" id="rent_amount" name="rent_amount" class="form-control" value="0" min="0">
                </div>

                <!-- Tiền điện -->
                <div class="form-group">
                    <label class="form-label" for="electric_amount" style="font-size: 13px;">Tiền điện</label>
                    <input type="number" id="electric_amount" name="electric_amount" class="form-control" value="0" min="0">
                </div>

                <!-- Tiền nước -->
                <div class="form-group">
                    <label class="form-label" for="water_amount" style="font-size: 13px;">Tiền nước</label>
                    <input type="number" id="water_amount" name="water_amount" class="form-control" value="0" min="0">
                </div>

                <!-- Phí quản lý -->
                <div class="form-group">
                    <label class="form-label" for="management_fee" style="font-size: 13px;">Phí quản lý</label>
                    <input type="number" id="management_fee" name="management_fee" class="form-control" value="0" min="0">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
                <!-- Phí vệ sinh -->
                <div class="form-group">
                    <label class="form-label" for="sanitation_fee" style="font-size: 13px;">Phí vệ sinh</label>
                    <input type="number" id="sanitation_fee" name="sanitation_fee" class="form-control" value="0" min="0">
                </div>

                <!-- Phí bảo vệ -->
                <div class="form-group">
                    <label class="form-label" for="security_fee" style="font-size: 13px;">Phí bảo vệ</label>
                    <input type="number" id="security_fee" name="security_fee" class="form-control" value="0" min="0">
                </div>

                <!-- Phí khác -->
                <div class="form-group">
                    <label class="form-label" for="other_fee" style="font-size: 13px;">Chi phí khác</label>
                    <input type="number" id="other_fee" name="other_fee" class="form-control" value="0" min="0">
                </div>

                <!-- Rỗng để giữ grid cân xứng -->
                <div class="form-group"></div>
            </div>

            <h4 style="margin: 24px 0 12px 0; padding-bottom: 8px; border-bottom: 1px solid var(--border-color-light); font-size: 14px; font-weight: 600; color: var(--primary);">Đính kèm hóa đơn / chứng từ</h4>

            <!-- Đính kèm file -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" for="attachment" style="font-weight: 500;">Tài liệu đính kèm (Ảnh hóa đơn VAT, file scan PDF...)</label>
                <input type="file" id="attachment" name="attachment" class="form-control" style="padding: 4px 12px; height: auto;">
                <small class="text-muted" style="display: block; margin-top: 4px; font-size: 12px;">Định dạng cho phép: JPG, JPEG, PNG, PDF. Dung lượng tối đa: 5MB.</small>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color-light); margin: 24px 0;">

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="<?php echo BASE_URL; ?>admin/bills" class="btn btn-outline" style="text-decoration: none;">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-file-invoice"></i> Lập hóa đơn
                </button>
            </div>
        </form>
    </div>
</div>
