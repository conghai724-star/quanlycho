<div style="margin-bottom: 20px;">
    <a href="<?php echo BASE_URL; ?>admin/foodsafety" class="btn btn-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-arrow-left"></i> Quay lại hồ sơ ATTP
    </a>
</div>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Khai báo Giấy tờ & Chứng nhận An toàn thực phẩm mới</div>
    </div>
    <div class="card-body" style="padding: 24px;">
        <form id="form-add-certificate" action="<?php echo BASE_URL; ?>api/addCertificate" method="POST" enctype="multipart/form-data">
            <?php csrf_field(); ?>
            <!-- Chủ hộ kinh doanh -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label" for="trader_id" style="font-weight: 500;">Tiểu thương / Hộ kinh doanh <span style="color: var(--red)">*</span></label>
                    <select id="trader_id" name="trader_id" class="form-control" required>
                        <option value="">-- Chọn tiểu thương --</option>
                        <?php if (!empty($traders)): ?>
                            <?php foreach ($traders as $trader): ?>
                                <option value="<?php echo $trader['id']; ?>">
                                    <?php echo htmlspecialchars($trader['fullname']); ?> (<?php echo htmlspecialchars($trader['trader_code']); ?><?php echo !empty($trader['description']) ? ' - ' . htmlspecialchars($trader['description']) : ''; ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="doc_type_id" style="font-weight: 500;">Loại giấy tờ <span style="color: var(--red)">*</span></label>
                    <select id="doc_type_id" name="doc_type_id" class="form-control" required>
                        <option value="">-- Chọn loại giấy tờ --</option>
                        <?php if (!empty($documentTypes)): ?>
                            <?php foreach ($documentTypes as $dt): ?>
                                <option value="<?php echo $dt['id']; ?>">
                                    <?php echo htmlspecialchars($dt['type_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <!-- Tên giấy tờ & Số GCN -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label" for="name" style="font-weight: 500;">Tên giấy tờ / chứng nhận <span style="color: var(--red)">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="Ví dụ: Giấy chứng nhận vệ sinh ATTP cửa hàng giò chả" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="doc_number" style="font-weight: 500;">Số quyết định / Số GCN <span style="color: var(--red)">*</span></label>
                    <input type="text" id="doc_number" name="doc_number" class="form-control" placeholder="Ví dụ: 123/2026/GCNATTP-QLC" required>
                </div>
            </div>

            <!-- Cơ quan cấp phép & File đính kèm -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label" for="issuer" style="font-weight: 500;">Cơ quan cấp phép</label>
                    <input type="text" id="issuer" name="issuer" class="form-control" placeholder="Ví dụ: Chi cục ATTP Hà Nội / UBND Quận">
                </div>

                <div class="form-group">
                    <label class="form-label" for="certificate_file" style="font-weight: 500;">Tài liệu đính kèm (Ảnh hoặc PDF)</label>
                    <input type="file" id="certificate_file" name="certificate_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf" style="padding: 4px 12px; height: 38px;">
                </div>
            </div>

            <!-- Ngày cấp & Ngày hết hạn -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label class="form-label" for="issue_date" style="font-weight: 500;">Ngày cấp phép / Ngày hiệu lực bắt đầu <span style="color: var(--red)">*</span></label>
                    <input type="date" id="issue_date" name="issue_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="expiry_date" style="font-weight: 500;">Ngày hết hạn hiệu lực <span style="color: var(--red)">*</span></label>
                    <input type="date" id="expiry_date" name="expiry_date" class="form-control" required>
                </div>
            </div>

            <!-- Mô tả ngắn -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" for="description" style="font-weight: 500;">Mô tả ngắn / Ghi chú</label>
                <textarea id="description" name="description" class="form-control" rows="3" placeholder="Nhập thêm chi tiết về phạm vi được cấp phép, điều kiện kèm theo..."></textarea>
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

<!-- CSRF Token phục vụ AJAX -->
<?php csrf_field(); ?>

<!-- Nạp JS xử lý AJAX & Form ATTP -->
<script src="<?php echo BASE_URL; ?>public/assets/js/pages/admin/foodsafety.js?v=<?php echo time(); ?>"></script>
