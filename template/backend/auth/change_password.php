<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Đổi mật khẩu tài khoản</div>
    </div>
    <div class="card-body" style="padding: 24px;">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px; padding: 12px 16px; background-color: rgba(234, 67, 53, 0.1); color: #EA4335; border: 1px solid rgba(234, 67, 53, 0.2); border-radius: 4px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" style="margin-bottom: 20px; padding: 12px 16px; background-color: rgba(26, 187, 156, 0.1); color: #1ABB9C; border: 1px solid rgba(26, 187, 156, 0.2); border-radius: 4px;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>admin/change_password" method="POST">
            <?php csrf_field(); ?>
            
            <!-- Mật khẩu hiện tại -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" for="old_password" style="font-weight: 500;">Mật khẩu hiện tại <span style="color: var(--red)">*</span></label>
                <input type="password" id="old_password" name="old_password" class="form-control" placeholder="••••••••" required>
            </div>

            <!-- Mật khẩu mới -->
            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label" for="new_password" style="font-weight: 500;">Mật khẩu mới (Tối thiểu 6 ký tự) <span style="color: var(--red)">*</span></label>
                <input type="password" id="new_password" name="new_password" class="form-control" placeholder="••••••••" required>
            </div>

            <!-- Xác nhận mật khẩu mới -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" for="confirm_password" style="font-weight: 500;">Xác nhận mật khẩu mới <span style="color: var(--red)">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="••••••••" required>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color-light); margin: 24px 0;">

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-key"></i> Cập nhật mật khẩu
                </button>
            </div>
        </form>
    </div>
</div>
