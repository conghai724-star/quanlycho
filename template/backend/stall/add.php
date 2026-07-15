<!-- Form Khai báo Sạp mới -->
<div style="margin-bottom: 20px;">
    <a href="<?php echo BASE_URL; ?>admin/stalls" class="btn btn-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách sạp
    </a>
</div>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Thông tin khai báo sạp chợ mới</div>
    </div>
    <div class="card-body" style="padding: 24px;">
        <!-- Thông báo lỗi nếu xảy ra lỗi validate (Dành cho Fallback Submit) -->
        <?php if (!empty($error)): ?>
            <div style="background-color: rgba(211, 47, 47, 0.1); border: 1px solid rgba(211, 47, 47, 0.2); color: #d32f2f; padding: 12px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form id="form-add-stall" action="<?php echo BASE_URL; ?>api/addStall" method="POST">
            <?php csrf_field(); ?>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Mã sạp -->
                <div class="form-group">
                    <label class="form-label" for="stall_code" style="font-weight: 500;">Mã sạp <span style="color: var(--red)">*</span></label>
                    <input type="text" id="stall_code" name="stall_code" class="form-control" placeholder="Ví dụ: SẠP-C02" value="<?php echo htmlspecialchars($data['stall_code'] ?? ''); ?>" required>
                    <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px; display: block;">Mã sạp duy nhất (Ví dụ: SẠP-A05).</small>
                </div>

                <!-- Phân khu -->
                <div class="form-group">
                    <label class="form-label" for="area_id" style="font-weight: 500;">Phân khu chợ (Khu vực) <span style="color: var(--red)">*</span></label>
                    <select id="area_id" name="area_id" class="form-control" required>
                        <option value="">-- Chọn khu vực --</option>
                        <?php if (!empty($areas)): ?>
                            <?php foreach ($areas as $a): ?>
                                <option value="<?php echo $a['id']; ?>" <?php echo ($data['area_id'] ?? '') == $a['id'] ? 'selected' : ''; ?>>
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
                    <label class="form-label" for="stall_type_id" style="font-weight: 500;">Loại sạp chợ <span style="color: var(--red)">*</span></label>
                    <select id="stall_type_id" name="stall_type_id" class="form-control" required>
                        <option value="">-- Chọn loại sạp --</option>
                        <?php if (!empty($stallTypes)): ?>
                            <?php foreach ($stallTypes as $st): ?>
                                <option value="<?php echo $st['id']; ?>" <?php echo ($data['stall_type_id'] ?? '') == $st['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($st['type_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Diện tích -->
                <div class="form-group">
                    <label class="form-label" for="area_size" style="font-weight: 500;">Diện tích (m²) <span style="color: var(--red)">*</span></label>
                    <input type="number" step="0.01" min="0.01" id="area_size" name="area_size" class="form-control" placeholder="Nhập diện tích" value="<?php echo htmlspecialchars($data['area_size'] ?? ''); ?>" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Đơn giá thuê -->
                <div class="form-group">
                    <label class="form-label" for="base_price" style="font-weight: 500;">Đơn giá thuê / tháng (VNĐ) <span style="color: var(--red)">*</span></label>
                    <input type="number" min="0" id="base_price" name="base_price" class="form-control" placeholder="Nhập giá cho thuê mỗi tháng" value="<?php echo htmlspecialchars($data['base_price'] ?? ''); ?>" required>
                </div>

                <!-- Trạng thái -->
                <div class="form-group">
                    <label class="form-label" for="status" style="font-weight: 500;">Trạng thái khởi tạo</label>
                    <select id="status" name="status" class="form-control">
                        <?php if (!empty($statuses)): ?>
                            <?php foreach ($statuses as $st): ?>
                                <!-- Chỉ cho phép khởi tạo Trống, Sửa chữa hoặc Khóa (không được khởi tạo Đã thuê khi chưa lập hợp đồng) -->
                                <?php if ($st['code'] !== 'rented'): ?>
                                    <option value="<?php echo htmlspecialchars($st['id']); ?>" <?php echo ($data['status_id'] ?? 3) == $st['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($st['status_name']); ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color-light); margin: 24px 0;">

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="<?php echo BASE_URL; ?>admin/stalls" class="btn btn-outline" style="text-decoration: none;">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-check"></i> Lưu sạp chợ
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

    // 1. Submit form bằng AJAX
    // App.utils.handleFormSubmit('form-add-stall', '<?php echo BASE_URL; ?>admin/stalls');
    $('#form-add-stall').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        var $form = $(this);
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        Swal.fire({
            title: 'Đang lưu sạp chợ...',
            allowOutsideClick: false,
            background: swalBg,
            color: swalColor,
            didOpen: function() { Swal.showLoading(); }
        });

        App.utils.saveFormDraft('form-add-stall');

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
                        App.utils.clearFormDraft('form-add-stall');
                        window.location.href = '<?php echo BASE_URL; ?>admin/stalls';
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

    // Hàm phụ phục vụ kiểm tra trùng lặp thời gian thực bằng jQuery
    function setupUniqueCheck(inputId, type, message) {
        var input = document.getElementById(inputId);
        if (!input || input.readOnly) return;
        var $input = $(input);
        var $errorEl = $input.parent().find('.realtime-error-msg');
        if (!$errorEl.length) {
            $errorEl = $('<small class="realtime-error-msg" style="color: #e74c3c; font-size: 11px; margin-top: 4px; display: none;"></small>');
            $input.parent().append($errorEl);
        }

        $input.on('blur', function() {
            var val = $input.val().trim();
            if (!val) {
                $errorEl.hide();
                input.style.borderColor = '';
                input.setCustomValidity('');
                return;
            }

            $.ajax({
                type: 'GET',
                url: '<?php echo BASE_URL; ?>api/checkExists',
                data: { type: type, value: val },
                dataType: 'json',
                success: function(data) {
                    if (data.exists) {
                        $errorEl.text(message).show();
                        input.style.borderColor = '#e74c3c';
                        input.setCustomValidity(message);
                    } else {
                        $errorEl.hide();
                        input.style.borderColor = '';
                        input.setCustomValidity('');
                    }
                }
            });
        });

        $input.on('input', function() {
            $errorEl.hide();
            input.style.borderColor = '';
            input.setCustomValidity('');
        });
    }

    // 2. Kiểm tra trùng mã sạp thời gian thực
    // App.utils.initRealtimeUniqueCheck('stall_code', 'api/checkExists', { getParams: val => ({ type: 'stall_code', value: val }), message: 'Mã sạp này đã tồn tại trên hệ thống.' });
    setupUniqueCheck('stall_code', 'stall_code', 'Mã sạp này đã tồn tại trên hệ thống.');
});
</script>

