<!-- Form Lập Hợp đồng mới -->
<div style="margin-bottom: 20px;">
    <a href="<?php echo BASE_URL; ?>admin/contracts" class="btn btn-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách hợp đồng
    </a>
</div>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Lập hợp đồng cho thuê sạp chợ mới</div>
    </div>
    <div class="card-body" style="padding: 24px;">
        <form id="form-add-contract" action="<?php echo BASE_URL; ?>api/addContract" method="POST" enctype="multipart/form-data">
            <?php csrf_field(); ?>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Số hợp đồng -->
                <div class="form-group">
                    <label class="form-label" for="contract_number" style="font-weight: 500;">Số Hợp đồng <span style="color: var(--red)">*</span></label>
                    <input type="text" id="contract_number" name="contract_number" class="form-control" placeholder="Ví dụ: HĐ-2026-0003" required>
                    <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px; display: block;">Mã duy nhất định danh hợp đồng.</small>
                </div>

                <!-- Tên hợp đồng -->
                <div class="form-group">
                    <label class="form-label" for="name" style="font-weight: 500;">Tên Hợp đồng <span style="color: var(--red)">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Ví dụ: HĐ Thuê Sạp A-02 - TT Nguyễn Thị Thu Hà" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Tiểu thương thuê -->
                <div class="form-group">
                    <label class="form-label" for="trader_id" style="font-weight: 500;">Tiểu thương thuê <span style="color: var(--red)">*</span></label>
                    <select id="trader_id" name="trader_id" class="form-control" required>
                        <option value="">-- Chọn tiểu thương đang hoạt động --</option>
                        <?php if (!empty($traders)): ?>
                            <?php foreach ($traders as $trader): ?>
                                <option value="<?php echo $trader['id']; ?>">
                                    <?php echo htmlspecialchars($trader['fullname']); ?> (<?php echo htmlspecialchars($trader['trader_code']); ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Sạp chợ thuê -->
                <div class="form-group">
                    <label class="form-label" for="stall_id" style="font-weight: 500;">Chọn Sạp chợ <span style="color: var(--red)">*</span></label>
                    <select id="stall_id" name="stall_id" class="form-control" required>
                        <option value="">-- Chọn sạp đang trống --</option>
                        <?php if (!empty($emptyStalls)): ?>
                            <?php foreach ($emptyStalls as $stall): ?>
                                <option value="<?php echo $stall['id']; ?>" data-price="<?php echo $stall['base_price']; ?>">
                                    <?php echo htmlspecialchars($stall['stall_code']); ?> (<?php echo htmlspecialchars($stall['area_name']); ?> - <?php echo number_format($stall['area_size'], 1); ?> m² - <?php echo number_format($stall['base_price'], 0, ',', '.'); ?> đ/tháng)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Đơn giá thuê (tự động cập nhật) -->
                <div class="form-group">
                    <label class="form-label" for="rental_price_display" style="font-weight: 500;">Đơn giá thuê / tháng (VNĐ)</label>
                    <input type="text" id="rental_price_display" class="form-control" placeholder="0" readonly style="background-color: var(--bg-surface-secondary); font-weight: 600; color: var(--primary);">
                </div>

                <!-- Tiền cọc -->
                <div class="form-group">
                    <label class="form-label" for="deposit" style="font-weight: 500;">Tiền đặt cọc (VNĐ) <span style="color: var(--red)">*</span></label>
                    <input type="number" id="deposit" name="deposit" class="form-control" placeholder="Nhập số tiền đặt cọc" required>
                    <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px; display: block;">Mặc định tự động tính bằng 2 tháng tiền thuê sạp.</small>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Ngày ký -->
                <div class="form-group">
                    <label class="form-label" for="start_date" style="font-weight: 500;">Ngày bắt đầu hiệu lực <span style="color: var(--red)">*</span></label>
                    <input type="date" id="start_date" name="start_date" class="form-control" required>
                </div>

                <!-- Ngày hết hạn -->
                <div class="form-group">
                    <label class="form-label" for="end_date" style="font-weight: 500;">Ngày hết hạn hợp đồng <span style="color: var(--red)">*</span></label>
                    <input type="date" id="end_date" name="end_date" class="form-control" required>
                </div>
            </div>

            <!-- Mô tả ngắn -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" for="description" style="font-weight: 500;">Mô tả chi tiết hợp đồng</label>
                <textarea id="description" name="description" class="form-control" rows="2" placeholder="Ghi chú thêm về điều khoản đặc biệt, thỏa thuận phụ nếu có..." style="resize: vertical; font-family: inherit; font-size: 13px;"></textarea>
            </div>

            <!-- Đính kèm PDF -->
            <div class="form-group" style="margin-bottom: 24px; max-width: 450px;">
                <label class="form-label" for="contract_file" style="font-weight: 500;">File đính kèm Hợp đồng (PDF)</label>
                <input type="file" id="contract_file" name="contract_file" class="form-control" accept=".pdf" style="font-size: 13px; padding: 6px 12px;">
                <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px; display: block;">Chỉ chấp nhận file định dạng PDF. Dung lượng tối đa: 15MB.</small>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color-light); margin: 24px 0;">

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="<?php echo BASE_URL; ?>admin/contracts" class="btn btn-outline" style="text-decoration: none;">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-file-signature"></i> Ký kết hợp đồng
                </button>
            </div>
        </form>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>public/assets/js/pages/admin/contract.js?v=<?php echo time(); ?>"></script>
