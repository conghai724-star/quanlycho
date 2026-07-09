<!-- Form Chỉnh sửa Sạp chợ -->
<div style="margin-bottom: 20px;">
    <a href="<?php echo BASE_URL; ?>admin/stalls" class="btn btn-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách sạp
    </a>
</div>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Chỉnh sửa sạp chợ: <?php echo htmlspecialchars($stall['stall_code']); ?></div>
    </div>
    <div class="card-body" style="padding: 24px;">
        <!-- Thông báo lỗi nếu xảy ra lỗi validate (Dành cho Fallback Submit) -->
        <?php if (!empty($error)): ?>
            <div style="background-color: rgba(211, 47, 47, 0.1); border: 1px solid rgba(211, 47, 47, 0.2); color: #d32f2f; padding: 12px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form id="form-edit-stall" action="<?php echo BASE_URL; ?>api/editStall" method="POST">
            <?php csrf_field(); ?>
            <!-- Hidden ID -->
            <input type="hidden" name="id" value="<?php echo $stall['id']; ?>">

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Mã sạp -->
                <div class="form-group">
                    <label class="form-label" for="stall_code" style="font-weight: 500; color: var(--text-muted);">Mã sạp (Không thể sửa)</label>
                    <input type="text" id="stall_code" name="stall_code" class="form-control" value="<?php echo htmlspecialchars($stall['stall_code']); ?>" style="background-color: var(--bg-surface-secondary); cursor: not-allowed;" readonly>
                </div>

                <!-- Phân khu -->
                <div class="form-group">
                    <label class="form-label" for="area_id" style="font-weight: 500;">Phân khu chợ (Khu vực) <span style="color: var(--red)">*</span></label>
                    <select id="area_id" name="area_id" class="form-control" required>
                        <option value="">-- Chọn khu vực --</option>
                        <?php if (!empty($areas)): ?>
                            <?php foreach ($areas as $a): ?>
                                <option value="<?php echo $a['id']; ?>" <?php echo $stall['area_id'] == $a['id'] ? 'selected' : ''; ?>>
                                    <?php 
                                    $displayText = $a['area_name'];
                                    if (!empty($a['block'])) $displayText .= ' - ' . $a['block'];
                                    if (!empty($a['lot'])) $displayText .= ' - ' . $a['lot'];
                                    echo htmlspecialchars($displayText); 
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>



            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Loại sạp -->
                <div class="form-group">
                    <label class="form-label" for="stall_type" style="font-weight: 500;">Loại sạp chợ</label>
                    <select id="stall_type" name="stall_type" class="form-control">
                        <option value="Quầy hàng" <?php echo $stall['stall_type'] === 'Quầy hàng' ? 'selected' : ''; ?>>Quầy hàng</option>
                        <option value="Kiot" <?php echo $stall['stall_type'] === 'Kiot' ? 'selected' : ''; ?>>Kiot</option>
                        <option value="Mặt bằng trống" <?php echo $stall['stall_type'] === 'Mặt bằng trống' ? 'selected' : ''; ?>>Mặt bằng trống</option>
                        <option value="Khác" <?php echo $stall['stall_type'] === 'Khác' ? 'selected' : ''; ?>>Khác</option>
                    </select>
                </div>

                <!-- Diện tích -->
                <div class="form-group">
                    <label class="form-label" for="area_size" style="font-weight: 500;">Diện tích (m²) <span style="color: var(--red)">*</span></label>
                    <input type="number" step="0.01" min="0.01" id="area_size" name="area_size" class="form-control" placeholder="Nhập diện tích" value="<?php echo htmlspecialchars($stall['area_size']); ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Đơn giá thuê -->
                <div class="form-group">
                    <label class="form-label" for="base_price" style="font-weight: 500;">Đơn giá thuê / tháng (VNĐ) <span style="color: var(--red)">*</span></label>
                    <input type="number" min="0" id="base_price" name="base_price" class="form-control" placeholder="Nhập giá cho thuê mỗi tháng" value="<?php echo htmlspecialchars($stall['base_price']); ?>" required>
                </div>

                <!-- Trạng thái -->
                <div class="form-group">
                    <label class="form-label" for="status" style="font-weight: 500;">Trạng thái sạp</label>
                    <select id="status" name="status" class="form-control">
                        <?php if (!empty($statuses)): ?>
                            <?php foreach ($statuses as $st): ?>
                                <option value="<?php echo htmlspecialchars($st['id']); ?>" <?php echo $stall['status_id'] == $st['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($st['status_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color-light); margin: 24px 0;">

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="<?php echo BASE_URL; ?>admin/stalls" class="btn btn-outline" style="text-decoration: none;">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-check"></i> Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>

<script src="<?php echo BASE_URL; ?>public/assets/js/pages/admin/stall.js?v=<?php echo time(); ?>"></script>
