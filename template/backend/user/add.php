<!-- Form Tạo Tài Khoản Nhân Viên Mới -->
<div style="margin-bottom: 20px;">
    <a href="<?php echo BASE_URL; ?>admin/users" class="btn btn-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-arrow-left"></i> Quay lại tài khoản
    </a>
</div>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Thông tin tài khoản nhân viên BQL mới</div>
    </div>
    <div class="card-body" style="padding: 24px;">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px; padding: 12px 16px; background-color: rgba(234, 67, 53, 0.1); color: #EA4335; border: 1px solid rgba(234, 67, 53, 0.2); border-radius: 4px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>admin/user_add" method="POST">
            <?php csrf_field(); ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Tên đăng nhập -->
                <div class="form-group">
                    <label class="form-label" for="username" style="font-weight: 500;">Tên đăng nhập <span style="color: var(--red)">*</span></label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Ví dụ: ketoan_nga" value="<?php echo htmlspecialchars($data['username'] ?? ''); ?>" required>
                </div>

                <!-- Họ tên nhân viên -->
                <div class="form-group">
                    <label class="form-label" for="fullname" style="font-weight: 500;">Họ tên nhân viên <span style="color: var(--red)">*</span></label>
                    <input type="text" id="fullname" name="fullname" class="form-control" placeholder="Nhập đầy đủ họ tên" value="<?php echo htmlspecialchars($data['fullname'] ?? ''); ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Email -->
                <div class="form-group">
                    <label class="form-label" for="email" style="font-weight: 500;">Địa chỉ Email <span style="color: var(--red)">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="nga.lt@market.com" value="<?php echo htmlspecialchars($data['email'] ?? ''); ?>" required>
                </div>

                <!-- Mật khẩu khởi tạo -->
                <div class="form-group">
                    <label class="form-label" for="password" style="font-weight: 500;">Mật khẩu khởi tạo <span style="color: var(--red)">*</span></label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 24px;">
                <!-- Vai trò hệ thống -->
                <div class="form-group">
                    <label class="form-label" for="role" style="font-weight: 500;">Vai trò hệ thống <span style="color: var(--red)">*</span></label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="staff" <?php echo (($data['role'] ?? '') === 'staff') ? 'selected' : ''; ?>>Nhân viên (Thủ quỹ / Kiểm tra)</option>
                        <option value="accountant" <?php echo (($data['role'] ?? '') === 'accountant') ? 'selected' : ''; ?>>Kế toán viên</option>
                        <option value="admin" <?php echo (($data['role'] ?? '') === 'admin') ? 'selected' : ''; ?>>Quản trị viên (Admin)</option>
                    </select>
                </div>

                <!-- Trạng thái hoạt động -->
                <div class="form-group">
                    <label class="form-label" for="status" style="font-weight: 500;">Trạng thái kích hoạt</label>
                    <select id="status" name="status" class="form-control">
                        <option value="active" <?php echo (($data['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Kích hoạt hoạt động</option>
                        <option value="inactive" <?php echo (($data['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Khóa tạm thời</option>
                    </select>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color-light); margin: 24px 0;">

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="<?php echo BASE_URL; ?>admin/users" class="btn btn-outline" style="text-decoration: none;">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-user-plus"></i> Tạo tài khoản
                </button>
            </div>
        </form>
    </div>
</div>
