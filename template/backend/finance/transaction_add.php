<!-- Form Lập Phiếu Thu / Chi Tài chính -->
<div style="margin-bottom: 20px;">
    <a href="<?php echo BASE_URL; ?>admin/transactions" class="btn btn-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-arrow-left"></i> Quay lại sổ quỹ
    </a>
</div>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">
            <?php echo $type === 'receipt' ? 'Lập Phiếu Thu Tài Chính' : 'Lập Phiếu Chi Quỹ'; ?>
        </div>
    </div>
    <div class="card-body" style="padding: 24px;">
        <form action="<?php echo BASE_URL; ?>admin/transaction_add" method="POST" enctype="multipart/form-data">
            <?php csrf_field(); ?>
            <!-- Hidden input để giữ loại phiếu -->
            <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Số phiếu -->
                <div class="form-group">
                    <label class="form-label" for="transaction_code" style="font-weight: 500;">Số phiếu giao dịch <span style="color: var(--red)">*</span></label>
                    <input type="text" id="transaction_code" name="transaction_code" class="form-control" 
                           placeholder="<?php echo $type === 'receipt' ? 'Ví dụ: PT-0002' : 'Ví dụ: PC-0002'; ?>" required>
                </div>

                <!-- Đối tượng thu / chi -->
                <div class="form-group">
                    <label class="form-label" for="target" style="font-weight: 500;">
                        <?php echo $type === 'receipt' ? 'Họ tên Người nộp tiền' : 'Họ tên Người nhận tiền (Nhà cung cấp)'; ?> <span style="color: var(--red)">*</span>
                    </label>
                    <input type="text" id="target" name="target" class="form-control" placeholder="Nhập tên đối tượng giao dịch" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Số tiền -->
                <div class="form-group">
                    <label class="form-label" for="amount" style="font-weight: 500;">Số tiền giao dịch (VNĐ) <span style="color: var(--red)">*</span></label>
                    <input type="number" id="amount" name="amount" class="form-control" placeholder="Nhập số tiền" required>
                </div>

                <!-- Phương thức thanh toán -->
                <div class="form-group">
                    <label class="form-label" for="payment_method" style="font-weight: 500;">Phương thức thanh toán</label>
                    <select id="payment_method" name="payment_method" class="form-control">
                        <option value="Tiền mặt">Tiền mặt</option>
                        <option value="Chuyển khoản">Chuyển khoản Ngân hàng</option>
                    </select>
                </div>
            </div>

            <!-- Nội dung chi tiết -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" for="note" style="font-weight: 500;">Nội dung thu/chi chi tiết <span style="color: var(--red)">*</span></label>
                <textarea id="note" name="note" class="form-control" rows="3" placeholder="Ví dụ: Thu tiền sạp thuê tháng 06/2026..." style="resize: vertical; font-family: inherit; font-size: 13.5px;" required></textarea>
            </div>

            <!-- Đính kèm file -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" for="attachment" style="font-weight: 500;">Tài liệu đính kèm (Ảnh hóa đơn, biên lai, chứng từ...)</label>
                <input type="file" id="attachment" name="attachment" class="form-control" style="padding: 4px 12px; height: auto;">
                <small class="text-muted" style="display: block; margin-top: 4px; font-size: 12px;">Định dạng cho phép: JPG, JPEG, PNG, PDF. Dung lượng tối đa: 5MB.</small>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color-light); margin: 24px 0;">

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="<?php echo BASE_URL; ?>admin/transactions" class="btn btn-outline" style="text-decoration: none;">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-file-invoice-dollar"></i> 
                    <?php echo $type === 'receipt' ? 'Lập phiếu thu' : 'Lập phiếu chi'; ?>
                </button>
            </div>
        </form>
    </div>
</div>
