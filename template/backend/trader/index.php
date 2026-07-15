<!-- Thanh tìm kiếm, xuất file và nút thêm mới -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <!-- Bộ lọc nhanh -->
    <form action="<?php echo BASE_URL; ?>admin/traders" method="GET" style="display: flex; gap: 8px; flex-wrap: wrap; margin: 0;">
        <input type="text" name="q" class="form-control" placeholder="Tìm tên, SĐT, CCCD, Mã..." value="<?php echo htmlspecialchars($search ?? ''); ?>" style="width: 240px; height: 36px; font-size: 13px;">
        <select name="business_line" class="form-control" style="width: 160px; height: 36px; font-size: 13px;">
            <option value="">Tất cả ngành hàng</option>
            <?php if (!empty($business_lines)): ?>
                <?php foreach ($business_lines as $line): ?>
                    <option value="<?php echo $line['id']; ?>" <?php echo ($business_line_filter ?? '') == $line['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($line['line_name']); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <select name="status" class="form-control" style="width: 160px; height: 36px; font-size: 13px;">
            <option value="">Tất cả trạng thái</option>
            <?php if (!empty($statuses)): ?>
                <?php foreach ($statuses as $st): ?>
                    <option value="<?php echo htmlspecialchars($st['code']); ?>" <?php echo ($status_filter ?? '') === $st['code'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($st['status_name']); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
        <button type="button" id="btn-filter-traders" class="btn btn-outline" style="height: 36px; padding: 0 16px;">Lọc</button>
        <?php if (!empty($search) || !empty($business_line_filter) || !empty($status_filter)): ?>
            <a href="<?php echo BASE_URL; ?>admin/traders" class="btn btn-ghost" style="height: 36px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; padding: 0 12px; color: var(--text-muted);">Xóa bộ lọc</a>
        <?php endif; ?>
    </form>
    
    <!-- Xuất file Excel/PDF và Thêm mới -->
    <?php 
    $queryString = http_build_query([
        'q' => $search ?? '',
        'business_line' => $business_line_filter ?? '',
        'status' => $status_filter ?? ''
    ]);
    ?>
    <div style="display: flex; gap: 8px;">
        <a href="<?php echo BASE_URL; ?>admin/trader_export_excel?<?php echo $queryString; ?>" id="btn-export-excel-traders" class="btn btn-outline" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;" title="Xuất dữ liệu ra file Excel">
            <i class="fa-regular fa-file-excel text-success"></i> Xuất Excel
        </a>
        <a href="<?php echo BASE_URL; ?>admin/trader_export_pdf?<?php echo $queryString; ?>" id="btn-export-pdf-traders" target="_blank" class="btn btn-outline" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;" title="Xuất dữ liệu ra file PDF">
            <i class="fa-regular fa-file-pdf text-danger"></i> Xuất PDF
        </a>
        <a href="<?php echo BASE_URL; ?>admin/trader_add" class="btn btn-primary" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: white;">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 14px; height: 14px;"><path d="M8 2v12M2 8h12"/></svg>
            Thêm Tiểu Thương
        </a>
    </div>
</div>

<!-- Bảng danh sách Tiểu thương -->
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Hồ sơ & Công nợ Tiểu thương hoạt động (<span id="filter-total-traders"><?php echo count($traders); ?></span>)</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px; width: 110px;">Mã tiểu thương</th>
                        <th style="padding: 12px 16px;">Họ và tên</th>
                        <th style="padding: 12px 16px; width: 110px;">Điện thoại</th>
                        <th style="padding: 12px 16px; width: 110px;">Số CCCD</th>
                        <th style="padding: 12px 16px;">Địa chỉ</th>
                        <th style="padding: 12px 16px; width: 120px;">Ngành hàng</th>
                        <th style="padding: 12px 16px; width: 120px;">Công nợ</th>
                        <th style="padding: 12px 16px; width: 100px; text-align: center;">Giấy phép</th>
                        <th style="padding: 12px 16px; width: 120px;">Trạng thái</th>
                        <th style="padding: 12px 16px; text-align: right; width: 110px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="table-body-traders">
                    <?php require DIR_TEMPLATE . '/backend/trader/table_rows.php'; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- CSRF Token phục vụ AJAX xóa -->
<?php csrf_field(); ?>

<!-- Nạp JS xử lý riêng cho trang tiểu thương -->
<script>
$(document).ready(function() {
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var swalBg = isDark ? '#1a2332' : '#ffffff';
    var swalColor = isDark ? '#ffffff' : '#0f1623';

    // Khởi tạo tính năng xóa tiểu thương
    // App.utils.initDelete({ btnClass: 'btn-open-delete-trader', idAttr: 'traderId', nameAttr: 'traderName', label: 'hồ sơ tiểu thương' });
    $(document).on('click', '.btn-open-delete-trader', function(e) {
        e.preventDefault();
        var btn = this;
        var id = $(btn).data('trader-id');
        var name = $(btn).data('trader-name') || '';
        var csrf = $('input[name="csrf_token"]').val() || '';

        Swal.fire({
            title: 'Xác nhận xóa',
            text: 'Bạn có chắc chắn muốn xóa hồ sơ tiểu thương "' + name + '" không? Hành động này không thể hoàn tác.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy bỏ',
            confirmButtonColor: '#d63939',
            cancelButtonColor: '#626d7d',
            background: swalBg,
            color: swalColor
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Đang xử lý...',
                    allowOutsideClick: false,
                    background: swalBg,
                    color: swalColor,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                $.ajax({
                    type: 'POST',
                    url: $(btn).data('url'),
                    data: { id: id, csrf_token: csrf },
                    dataType: 'json',
                    success: function(data) {
                        Swal.close();
                        if (data.status === 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Thành công',
                                text: data.message,
                                timer: 1500,
                                background: swalBg,
                                color: swalColor
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Thất bại', text: data.message, background: swalBg, color: swalColor });
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire({ icon: 'error', title: 'Lỗi', text: 'Có lỗi xảy ra khi kết nối máy chủ.', background: swalBg, color: swalColor });
                    }
                });
            }
        });
    });

    // Khởi tạo bộ lọc tìm kiếm bằng AJAX
    // App.utils.initFilterFormAjax({ ... });
    (function() {
        var btn = $('#btn-filter-traders');
        if (!btn.length) return;
        var form = btn.closest('form');
        var inputs = form.find('input[name], select[name]');
        var tbody = $('#table-body-traders');
        var totalEl = $('#filter-total-traders');
        var exportExcel = $('#btn-export-excel-traders');
        var exportPdf = $('#btn-export-pdf-traders');

        function doFilter() {
            var params = {};
            inputs.each(function() {
                var value = $(this).val().trim();
                if (value !== '') {
                    params[$(this).attr('name')] = value;
                }
            });
            var query = $.param(params);
            if (tbody.length) {
                tbody.css('opacity', '0.5');
            }
            $.ajax({
                type: 'GET',
                url: '<?php echo BASE_URL; ?>api/filterTraders',
                data: params,
                dataType: 'json',
                success: function(data) {
                    if (tbody.length) {
                        tbody.html(data.html).css('opacity', '1');
                    }
                    if (totalEl.length && typeof data.total !== 'undefined') {
                        totalEl.text(data.total);
                    }
                    if (exportExcel.length && typeof data.queryString !== 'undefined') {
                        exportExcel.attr('href', '<?php echo BASE_URL; ?>admin/trader_export_excel?' + data.queryString);
                    }
                    if (exportPdf.length && typeof data.queryString !== 'undefined') {
                        exportPdf.attr('href', '<?php echo BASE_URL; ?>admin/trader_export_pdf?' + data.queryString);
                    }
                    var newUrl = '<?php echo BASE_URL; ?>admin/traders' + (query ? '?' + query : '');
                    window.history.pushState({ path: newUrl }, '', newUrl);
                },
                error: function() {
                    if (tbody.length) tbody.css('opacity', '1');
                }
            });
        }

        btn.on('click', doFilter);
        form.find('input[type="text"]').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                doFilter();
            }
        });
    })();
});
</script>




