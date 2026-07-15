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
<script>
$(document).ready(function() {
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var swalBg = isDark ? '#1a2332' : '#ffffff';
    var swalColor = isDark ? '#ffffff' : '#0f1623';

    // 1. Submit form bằng AJAX
    // App.utils.handleFormSubmit('form-add-cert', '<?php echo BASE_URL; ?>admin/foodsafety');
    $('#form-add-cert').on('submit', function(e) {
        e.preventDefault();
        var form = this;
        var $form = $(this);
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        Swal.fire({
            title: 'Đang lưu chứng nhận...',
            allowOutsideClick: false,
            background: swalBg,
            color: swalColor,
            didOpen: function() { Swal.showLoading(); }
        });

        App.utils.saveFormDraft('form-add-cert');

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
                        App.utils.clearFormDraft('form-add-cert');
                        window.location.href = '<?php echo BASE_URL; ?>admin/foodsafety';
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

    // 2. Kiểm tra trùng số chứng nhận thời gian thực
    // App.utils.initRealtimeUniqueCheck('doc_number', 'api/checkExists', { getParams: val => ({ type: 'doc_number', value: val }), message: 'Số giấy chứng nhận này đã tồn tại trên hệ thống.' });
    setupUniqueCheck('doc_number', 'doc_number', 'Số giấy chứng nhận này đã tồn tại trên hệ thống.');
});
</script>

