<!-- Giao diện Thêm Chợ Mới -->
<div style="max-width: 800px; margin: 0 auto; padding: 20px 0;">
    <div style="margin-bottom: 20px;">
        <a href="<?php echo BASE_URL; ?>system/markets" style="text-decoration: none; color: var(--text-muted); font-size: 13.5px; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách chợ
        </a>
    </div>

    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 20px 24px;">
            <h2 class="card-title" style="font-size: 18px; font-weight: 600; margin: 0; color: var(--text-heading);">
                <i class="fa-solid fa-store" style="color: var(--primary); margin-right: 8px;"></i>
                Thêm Chợ Mới Vào Hệ Thống
            </h2>
        </div>
        <div class="card-body" style="padding: 24px;">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="margin-bottom: 20px; padding: 12px 16px; background-color: rgba(234, 67, 53, 0.1); color: #EA4335; border: 1px solid rgba(234, 67, 53, 0.2); border-radius: 6px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>system/market_add">
                <?php csrf_field(); ?>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <!-- Tên chợ -->
                    <div>
                        <label style="display: block; font-weight: 500; font-size: 13.5px; margin-bottom: 6px; color: var(--text-heading);">Tên Chợ <span style="color: red;">*</span></label>
                        <input type="text" name="name" required placeholder="Ví dụ: Chợ Bình Tây" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 14px;">
                    </div>
                    <!-- Mã chợ -->
                    <div>
                        <label style="display: block; font-weight: 500; font-size: 13.5px; margin-bottom: 6px; color: var(--text-heading);">Mã Chợ (Viết liền không dấu) <span style="color: red;">*</span></label>
                        <input type="text" name="market_code" required placeholder="Ví dụ: CHO_BT" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 14px; text-transform: uppercase;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <!-- Điện thoại -->
                    <div>
                        <label style="display: block; font-weight: 500; font-size: 13.5px; margin-bottom: 6px; color: var(--text-heading);">Số Điện Thoại</label>
                        <input type="text" name="phone" placeholder="Số điện thoại BQL" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 14px;">
                    </div>
                    <!-- Email -->
                    <div>
                        <label style="display: block; font-weight: 500; font-size: 13.5px; margin-bottom: 6px; color: var(--text-heading);">Email liên hệ</label>
                        <input type="email" name="email" placeholder="Email BQL" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 14px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                    <!-- Trưởng BQL -->
                    <div>
                        <label style="display: block; font-weight: 500; font-size: 13.5px; margin-bottom: 6px; color: var(--text-heading);">Trưởng Ban Quản Lý</label>
                        <input type="text" name="manager_name" placeholder="Họ tên Trưởng ban" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 14px;">
                    </div>
                    <!-- Trạng thái -->
                    <div>
                        <label style="display: block; font-weight: 500; font-size: 13.5px; margin-bottom: 6px; color: var(--text-heading);">Trạng Thái</label>
                        <select name="status_code" style="width: 100%; padding: 8px 12px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 14px; background-color: white;">
                            <option value="active">Đang Hoạt Động</option>
                            <option value="inactive">Ngừng Hoạt Động</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                    <a href="<?php echo BASE_URL; ?>system/markets" class="btn btn-outline" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; height: 38px;">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary" style="height: 38px; display: inline-flex; align-items: center; justify-content: center;">Tạo Chợ Mới</button>
                </div>
            </form>
        </div>
    </div>
</div>
