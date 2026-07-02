<!-- Form Chỉnh sửa thông tin Tiểu Thương -->
<div style="margin-bottom: 20px;">
    <a href="<?php echo BASE_URL; ?>admin/traders" class="btn btn-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách
    </a>
</div>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Chỉnh sửa hồ sơ tiểu thương: <?php echo htmlspecialchars($trader['fullname']); ?></div>
    </div>
    <div class="card-body" style="padding: 24px;">
        
        <!-- Thông báo lỗi nếu có -->
        <?php if (!empty($error)): ?>
            <div style="background-color: rgba(211, 47, 47, 0.1); border: 1px solid rgba(211, 47, 47, 0.2); color: #d32f2f; padding: 12px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>admin/trader_edit/<?php echo $trader['id']; ?>" method="POST">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Mã tiểu thương -->
                <div class="form-group">
                    <label class="form-label" for="trader_code" style="font-weight: 500; color: var(--text-muted);">Mã tiểu thương (Không thể sửa)</label>
                    <input type="text" id="trader_code" class="form-control" value="<?php echo htmlspecialchars($trader['trader_code']); ?>" style="background-color: var(--bg-surface-secondary); cursor: not-allowed;" readonly>
                </div>

                <!-- Họ và tên -->
                <div class="form-group">
                    <label class="form-label" for="fullname" style="font-weight: 500;">Họ và tên chủ hộ <span style="color: var(--red)">*</span></label>
                    <input type="text" id="fullname" name="fullname" class="form-control" placeholder="Nhập đầy đủ họ tên" value="<?php echo htmlspecialchars($trader['fullname']); ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Số điện thoại -->
                <div class="form-group">
                    <label class="form-label" for="phone" style="font-weight: 500;">Số điện thoại <span style="color: var(--red)">*</span></label>
                    <input type="text" id="phone" name="phone" class="form-control" placeholder="Ví dụ: 0987654321" value="<?php echo htmlspecialchars($trader['phone']); ?>" required>
                </div>

                <!-- Số CCCD -->
                <div class="form-group">
                    <label class="form-label" for="cccd" style="font-weight: 500;">Số CCCD / Hộ chiếu <span style="color: var(--red)">*</span></label>
                    <input type="text" id="cccd" name="cccd" class="form-control" placeholder="Nhập 12 số CCCD" value="<?php echo htmlspecialchars($trader['cccd']); ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Ngành hàng kinh doanh -->
                <div class="form-group">
                    <label class="form-label" for="business_line" style="font-weight: 500;">Ngành hàng kinh doanh</label>
                    <input type="text" id="business_line" name="business_line" class="form-control" placeholder="Ví dụ: Quần áo thời trang" value="<?php echo htmlspecialchars($trader['business_line']); ?>">
                </div>

                <!-- Trạng thái hoạt động -->
                <div class="form-group">
                    <label class="form-label" for="status" style="font-weight: 500;">Trạng thái</label>
                    <select id="status" name="status" class="form-control">
                        <option value="active" <?php echo $trader['status'] === 'active' ? 'selected' : ''; ?>>Đang hoạt động</option>
                        <option value="suspended" <?php echo $trader['status'] === 'suspended' ? 'selected' : ''; ?>>Tạm ngừng kinh doanh</option>
                        <option value="closed" <?php echo $trader['status'] === 'closed' ? 'selected' : ''; ?>>Ngừng hoạt động hẳn</option>
                    </select>
                </div>
            </div>

            <!-- Địa chỉ thường trú -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" for="address" style="font-weight: 500;">Địa chỉ thường trú</label>
                <textarea id="address" name="address" class="form-control" rows="3" placeholder="Nhập địa chỉ..." style="resize: vertical; font-family: inherit; font-size: 13.5px;"><?php echo htmlspecialchars($trader['address']); ?></textarea>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color-light); margin: 24px 0;">

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="<?php echo BASE_URL; ?>admin/traders" class="btn btn-outline" style="text-decoration: none;">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-check"></i> Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>
