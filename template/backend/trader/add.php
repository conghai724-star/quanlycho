<!-- Form Thêm mới Tiểu Thương -->
<div style="margin-bottom: 20px;">
    <a href="<?php echo BASE_URL; ?>admin/traders" class="btn btn-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách
    </a>
</div>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Thông tin hồ sơ tiểu thương mới</div>
    </div>
    <div class="card-body" style="padding: 24px;">
        
        <!-- Thông báo lỗi nếu xảy ra lỗi validate -->
        <?php if (!empty($error)): ?>
            <div style="background-color: rgba(211, 47, 47, 0.1); border: 1px solid rgba(211, 47, 47, 0.2); color: #d32f2f; padding: 12px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form id="form-add-trader" action="<?php echo BASE_URL; ?>api/addTrader" method="POST" enctype="multipart/form-data">
            <?php csrf_field(); ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Mã tiểu thương -->
                <div class="form-group">
                    <label class="form-label" for="trader_code" style="font-weight: 500;">Mã tiểu thương <span style="color: var(--red)">*</span></label>
                    <input type="text" id="trader_code" name="trader_code" class="form-control" placeholder="Ví dụ: TT-0005" value="<?php echo htmlspecialchars($data['trader_code']); ?>" required>
                    <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px; display: block;">Mã duy nhất định danh hộ kinh doanh.</small>
                </div>

                <!-- Họ và tên -->
                <div class="form-group">
                    <label class="form-label" for="fullname" style="font-weight: 500;">Họ và tên chủ hộ <span style="color: var(--red)">*</span></label>
                    <input type="text" id="fullname" name="fullname" class="form-control" placeholder="Nhập đầy đủ họ tên" value="<?php echo htmlspecialchars($data['fullname']); ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Số điện thoại -->
                <div class="form-group">
                    <label class="form-label" for="phone" style="font-weight: 500;">Số điện thoại <span style="color: var(--red)">*</span></label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="Ví dụ: 0987654321" value="<?php echo htmlspecialchars($data['phone']); ?>" pattern="0(3|5|7|8|9)[0-9]{8}" title="Số điện thoại di động Việt Nam gồm 10 chữ số, bắt đầu bằng 03, 05, 07, 08 hoặc 09" required>
                </div>

                <!-- Số CCCD -->
                <div class="form-group">
                    <label class="form-label" for="cccd" style="font-weight: 500;">Số CCCD / Hộ chiếu <span style="color: var(--red)">*</span></label>
                    <input type="text" id="cccd" name="cccd" class="form-control" placeholder="Nhập 12 số CCCD" value="<?php echo htmlspecialchars($data['cccd']); ?>" pattern="[0-9]{12}" title="Số CCCD gồm đúng 12 chữ số" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Ngành hàng kinh doanh -->
                <div class="form-group">
                    <label class="form-label" for="business_line_id" style="font-weight: 500;">Ngành hàng kinh doanh</label>
                    <select id="business_line_id" name="business_line_id" class="form-control">
                        <option value="">-- Chọn ngành hàng --</option>
                        <?php if (!empty($business_lines)): ?>
                            <?php foreach ($business_lines as $bl): ?>
                                <option value="<?php echo $bl['id']; ?>" <?php echo ($data['business_line_id'] ?? '') == $bl['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($bl['line_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Trạng thái hoạt động -->
                <div class="form-group">
                    <label class="form-label" for="status" style="font-weight: 500;">Trạng thái</label>
                    <select id="status" name="status" class="form-control">
                        <?php if (!empty($statuses)): ?>
                            <?php foreach ($statuses as $st): ?>
                                <option value="<?php echo htmlspecialchars($st['id']); ?>" <?php echo ($data['status_id'] ?? 7) == $st['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($st['status_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <!-- Địa chỉ thường trú -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" for="address" style="font-weight: 500;">Địa chỉ thường trú</label>
                <textarea id="address" name="address" class="form-control" rows="2" placeholder="Nhập số nhà, tên đường, phường/xã, quận/huyện..." style="resize: vertical; font-family: inherit; font-size: 13px;"><?php echo htmlspecialchars($data['address']); ?></textarea>
            </div>

            <!-- Mô tả ngắn -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" for="description" style="font-weight: 500;">Mô tả ngắn</label>
                <textarea id="description" name="description" class="form-control" rows="2" placeholder="Thông tin tóm tắt về sạp hàng, mặt hàng kinh doanh chính..." style="resize: vertical; font-family: inherit; font-size: 13px;"><?php echo htmlspecialchars($data['description'] ?? ''); ?></textarea>
            </div>

            <!-- Hồ sơ đính kèm / Giấy phép KD -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" for="license_files" style="font-weight: 500;">Hồ sơ đính kèm (Có thể chọn nhiều tệp)</label>
                <input type="file" id="license_files" name="license_files[]" class="form-control" accept=".jpg,.jpeg,.png,.pdf" multiple style="font-size: 13px; padding: 6px 12px;">
                <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px; display: block;">Chọn một hoặc nhiều file. Hỗ trợ: JPG, JPEG, PNG, PDF. Dung lượng tối đa: 10MB/tệp.</small>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color-light); margin: 24px 0;">

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="<?php echo BASE_URL; ?>admin/traders" class="btn btn-outline" style="text-decoration: none;">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-check"></i> Thêm tiểu thương
                </button>
            </div>
        </form>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>public/assets/js/pages/admin/trader.js?v=<?php echo time(); ?>"></script>
