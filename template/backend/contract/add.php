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
        <form action="<?php echo BASE_URL; ?>admin/contract_add" method="POST">
            <?php csrf_field(); ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Số hợp đồng -->
                <div class="form-group">
                    <label class="form-label" for="contract_code" style="font-weight: 500;">Số Hợp đồng <span style="color: var(--red)">*</span></label>
                    <input type="text" id="contract_code" name="contract_code" class="form-control" placeholder="Ví dụ: HĐ-2026-0003" required>
                </div>

                <!-- Tiểu thương thuê -->
                <div class="form-group">
                    <label class="form-label" for="trader_name" style="font-weight: 500;">Tiểu thương thuê <span style="color: var(--red)">*</span></label>
                    <select id="trader_name" name="trader_name" class="form-control" required>
                        <option value="">-- Chọn tiểu thương --</option>
                        <option value="Nguyễn Thị Thu Hà">Nguyễn Thị Thu Hà (TT-0001)</option>
                        <option value="Trần Văn Hoàng">Trần Văn Hoàng (TT-0002)</option>
                        <option value="Phạm Minh Tuấn">Phạm Minh Tuấn (TT-0003)</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Sạp chợ thuê -->
                <div class="form-group">
                    <label class="form-label" for="stall_code" style="font-weight: 500;">Chọn Sạp chợ <span style="color: var(--red)">*</span></label>
                    <select id="stall_code" name="stall_code" class="form-control" required>
                        <option value="">-- Chọn sạp đang trống --</option>
                        <option value="SẠP-A02">SẠP-A02 (Khu A - 15 m²)</option>
                        <option value="SẠP-C01">SẠP-C01 (Khu C - 25 m²)</option>
                    </select>
                </div>

                <!-- Tiền cọc -->
                <div class="form-group">
                    <label class="form-label" for="deposit" style="font-weight: 500;">Tiền đặt cọc (VNĐ) <span style="color: var(--red)">*</span></label>
                    <input type="number" id="deposit" name="deposit" class="form-control" placeholder="Nhập số tiền đặt cọc" required>
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

            <div class="form-group" style="margin-bottom: 24px; max-width: 300px;">
                <!-- Đơn giá thuê -->
                <label class="form-label" for="price" style="font-weight: 500;">Đơn giá thuê / tháng (VNĐ) <span style="color: var(--red)">*</span></label>
                <input type="number" id="price" name="price" class="form-control" placeholder="Nhập đơn giá thuê" required>
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
