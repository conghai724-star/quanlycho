<!-- Form Khai báo Chứng nhận ATTP mới -->
<div style="margin-bottom: 20px;">
    <a href="<?php echo BASE_URL; ?>admin/foodsafety" class="btn btn-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-arrow-left"></i> Quay lại kiểm tra ATTP
    </a>
</div>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Khai báo chứng nhận An toàn thực phẩm hộ kinh doanh</div>
    </div>
    <div class="card-body" style="padding: 24px;">
        <form action="<?php echo BASE_URL; ?>admin/foodsafety_add" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo security::getToken(); ?>">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Chủ hộ kinh doanh -->
                <div class="form-group">
                    <label class="form-label" for="trader_name" style="font-weight: 500;">Chủ hộ kinh doanh <span style="color: var(--red)">*</span></label>
                    <select id="trader_name" name="trader_name" class="form-control" required>
                        <option value="">-- Chọn tiểu thương --</option>
                        <option value="Trần Văn Hoàng">Trần Văn Hoàng (TT-0002)</option>
                        <option value="Lê Thị Mai">Lê Thị Mai (TT-0004)</option>
                    </select>
                </div>

                <!-- Tên cơ sở kinh doanh -->
                <div class="form-group">
                    <label class="form-label" for="shop_name" style="font-weight: 500;">Tên cơ sở kinh doanh <span style="color: var(--red)">*</span></label>
                    <input type="text" id="shop_name" name="shop_name" class="form-control" placeholder="Ví dụ: Hộ kinh doanh Hoàng Thực Phẩm" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Số giấy chứng nhận -->
                <div class="form-group">
                    <label class="form-label" for="cert_code" style="font-weight: 500;">Số giấy chứng nhận ATTP <span style="color: var(--red)">*</span></label>
                    <input type="text" id="cert_code" name="cert_code" class="form-control" placeholder="Ví dụ: 123/2026/ATTP-HN" required>
                </div>

                <!-- Tổ chức cấp phép -->
                <div class="form-group">
                    <label class="form-label" for="issuer" style="font-weight: 500;">Cơ quan cấp phép</label>
                    <input type="text" id="issuer" name="issuer" class="form-control" placeholder="Ví dụ: Chi cục ATTP Hà Nội">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 24px;">
                <!-- Ngày cấp -->
                <div class="form-group">
                    <label class="form-label" for="issue_date" style="font-weight: 500;">Ngày cấp phép <span style="color: var(--red)">*</span></label>
                    <input type="date" id="issue_date" name="issue_date" class="form-control" required>
                </div>

                <!-- Ngày hết hạn -->
                <div class="form-group">
                    <label class="form-label" for="expire_date" style="font-weight: 500;">Ngày hết hạn hiệu lực <span style="color: var(--red)">*</span></label>
                    <input type="date" id="expire_date" name="expire_date" class="form-control" required>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color-light); margin: 24px 0;">

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="<?php echo BASE_URL; ?>admin/foodsafety" class="btn btn-outline" style="text-decoration: none;">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-check"></i> Lưu chứng nhận
                </button>
            </div>
        </form>
    </div>
</div>
