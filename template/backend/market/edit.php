<!-- Giao diện Sửa Thông Tin Chợ -->
<div style="margin-bottom: 20px;">
    <a href="<?php echo BASE_URL; ?>system/markets" class="btn btn-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách chợ
    </a>
</div>

<div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
            <div class="card-title" style="font-size: 16px; font-weight: 600;">Sửa Thông Tin Chợ: <?php echo htmlspecialchars($market['name']); ?></div>
        </div>
        <div class="card-body" style="padding: 24px;">
            <?php if (!empty($error)): ?>
                <div style="background-color: rgba(211, 47, 47, 0.1); border: 1px solid rgba(211, 47, 47, 0.2); color: #d32f2f; padding: 12px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <form id="form-edit-market" method="POST" action="<?php echo BASE_URL; ?>api/editMarket" data-native-submit="true">
                <?php csrf_field(); ?>
                <input type="hidden" name="id" value="<?php echo $market['id']; ?>">
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <!-- Tên chợ -->
                    <div class="form-group">
                        <label class="form-label" for="name" style="font-weight: 500;">Tên Chợ <span style="color: var(--red)">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($market['name']); ?>" required>
                    </div>
                    <!-- Mã chợ -->
                    <div class="form-group">
                        <label class="form-label" for="market_code" style="font-weight: 500;">Mã Chợ (Viết liền không dấu) <span style="color: var(--red)">*</span></label>
                        <input type="text" id="market_code" name="market_code" class="form-control" value="<?php echo htmlspecialchars($market['market_code']); ?>" style="text-transform: uppercase;" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <!-- Điện thoại -->
                    <div class="form-group">
                        <label class="form-label" for="phone" style="font-weight: 500;">Số Điện Thoại</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="<?php echo htmlspecialchars($market['phone'] ?? ''); ?>">
                    </div>
                    <!-- Email -->
                    <div class="form-group">
                        <label class="form-label" for="email" style="font-weight: 500;">Email liên hệ</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($market['email'] ?? ''); ?>">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 24px;">
                    <!-- Trưởng BQL -->
                    <div class="form-group">
                        <label class="form-label" for="manager_name" style="font-weight: 500;">Trưởng Ban Quản Lý</label>
                        <input type="text" id="manager_name" name="manager_name" class="form-control" value="<?php echo htmlspecialchars($market['manager_name'] ?? ''); ?>">
                    </div>
                    <!-- Trạng thái -->
                    <div class="form-group">
                        <label class="form-label" for="status_code" style="font-weight: 500;">Trạng Thái</label>
                        <select id="status_code" name="status_code" class="form-control">
                            <option value="active" <?php echo $market['status_code'] === 'active' ? 'selected' : ''; ?>>Đang Hoạt Động</option>
                            <option value="inactive" <?php echo $market['status_code'] === 'inactive' ? 'selected' : ''; ?>>Ngừng Hoạt Động</option>
                        </select>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--border-color-light); margin: 24px 0;">

                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <a href="<?php echo BASE_URL; ?>system/markets" class="btn btn-outline" style="text-decoration: none; display: inline-flex; align-items: center; justify-content: center; height: 38px;">Hủy bỏ</a>
                    <button type="submit" class="btn btn-primary" style="height: 38px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                        <i class="fa-solid fa-check"></i> Cập Nhật
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
$(document).ready(function() {
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var swalBg = isDark ? '#1a2332' : '#ffffff';
    var swalColor = isDark ? '#ffffff' : '#0f1623';

    $('#form-edit-market').on('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var form = this;
        var $form = $(this);
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        Swal.fire({
            title: 'Đang lưu thông tin...',
            allowOutsideClick: false,
            background: swalBg,
            color: swalColor,
            didOpen: function() { Swal.showLoading(); }
        });

        $.ajax({
            type: "POST",
            url: $form.attr('action'),
            data: new FormData(form),
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(data) {
                Swal.close();
                if (data.status === 200) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false,
                        background: swalBg,
                        color: swalColor
                    }).then(function() {
                        window.location.href = '<?php echo BASE_URL; ?>system/markets';
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Thất bại', text: data.message, background: swalBg, color: swalColor });
                }
            },
            error: function() {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Lỗi', text: 'Có lỗi xảy ra trong quá trình xử lý.', background: swalBg, color: swalColor });
            }
        });
    });
});
</script>
