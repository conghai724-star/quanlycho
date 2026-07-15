<!-- Định nghĩa style tùy biến cho trạng thái Trống (Màu trắng) -->
<style>
    .status-white {
        color: var(--text-muted);
    }
    .status-white:before {
        background: #fff;
        border: 1px solid var(--border-color);
    }
    /* Style tối ưu giao diện sáng tối cho status-white */
    [data-theme=dark] .status-white:before {
        background: #1a2332;
        border-color: rgba(255, 255, 255, 0.15);
    }
</style>

<!-- Thống kê tỷ lệ lấp đầy sạp chợ thực tế -->
<?php
$totalStalls = $stats['total'] ?? 0;
$rentedStalls = $stats['rented'] ?? 0;
$emptyStalls = $stats['empty'] ?? 0;
$repairingStalls = $stats['repairing'] ?? 0;

$rentedPercent = $totalStalls > 0 ? round(($rentedStalls / $totalStalls) * 100) : 0;
$emptyPercent = $totalStalls > 0 ? round(($emptyStalls / $totalStalls) * 100) : 0;
?>
<div class="row col-3" style="margin-bottom: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
    <!-- Sạp đã thuê -->
    <div class="card" style="margin-bottom: 0;">
        <div class="stat" style="padding: 16px;">
            <div class="stat-icon green" style="background-color: rgba(52, 168, 83, 0.1); color: #34A853;"><i class="fa-solid fa-store"></i></div>
            <div class="stat-content">
                <div class="stat-label">Sạp đã cho thuê</div>
                <div class="stat-value-row">
                    <span class="stat-value"><?php echo $rentedStalls; ?> / <?php echo $totalStalls; ?></span>
                    <span class="stat-change up" style="color: #34A853;"><?php echo $rentedPercent; ?>% lấp đầy</span>
                </div>
            </div>
        </div>
    </div>
    <!-- Sạp trống -->
    <div class="card" style="margin-bottom: 0;">
        <div class="stat" style="padding: 16px;">
            <div class="stat-icon yellow" style="background-color: rgba(251, 188, 4, 0.1); color: #FBBC04;"><i class="fa-solid fa-circle-plus"></i></div>
            <div class="stat-content">
                <div class="stat-label">Sạp trống sẵn sàng</div>
                <div class="stat-value-row">
                    <span class="stat-value"><?php echo $emptyStalls; ?> / <?php echo $totalStalls; ?></span>
                    <span class="stat-change up" style="color: #FBBC04;"><?php echo $emptyPercent; ?>% trống</span>
                </div>
            </div>
        </div>
    </div>
    <!-- Sạp đang bảo trì -->
    <div class="card" style="margin-bottom: 0;">
        <div class="stat" style="padding: 16px;">
            <div class="stat-icon red" style="background-color: rgba(234, 67, 53, 0.1); color: #EA4335;"><i class="fa-solid fa-wrench"></i></div>
            <div class="stat-content">
                <div class="stat-label">Sạp đang bảo trì</div>
                <div class="stat-value-row">
                    <span class="stat-value"><?php echo $repairingStalls; ?> sạp</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bộ lọc, Tab chọn & Nút thêm mới -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <!-- Form Lọc AJAX chuẩn mẫu tiểu thương -->
    <form action="<?php echo BASE_URL; ?>admin/stalls" method="GET" style="display: flex; gap: 8px; flex-wrap: wrap; margin: 0;">
        <input type="text" name="q" class="form-control" placeholder="Tìm mã sạp, dãy, lô..." value="<?php echo htmlspecialchars($search ?? ''); ?>" style="width: 240px; height: 36px; font-size: 13px;">
        
        <select name="area_id" class="form-control" style="width: 160px; height: 36px; font-size: 13px;">
            <option value="">Tất cả phân khu</option>
            <?php if (!empty($areas)): ?>
                <?php foreach ($areas as $a): ?>
                    <option value="<?php echo $a['id']; ?>" <?php echo ($area_filter ?? '') == $a['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($a['area_name']); ?>
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
        
        <button type="button" id="btn-filter-stalls" class="btn btn-outline" style="height: 36px; padding: 0 16px;">Lọc</button>
        <?php if (!empty($search) || !empty($area_filter) || !empty($status_filter)): ?>
            <a href="<?php echo BASE_URL; ?>admin/stalls" class="btn btn-ghost" style="height: 36px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; padding: 0 12px; color: var(--text-muted);">Xóa bộ lọc</a>
        <?php endif; ?>
    </form>
    
    <div style="display: flex; gap: 8px;">
        <a href="<?php echo BASE_URL; ?>admin/stall_add" class="btn btn-primary" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: white;">
            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 14px; height: 14px;"><path d="M8 2v12M2 8h12"/></svg>
            Khai báo Sạp mới
        </a>
    </div>
</div>

<!-- TAB 1: HIỂN THỊ DẠNG BẢNG (Dữ liệu thực) -->
<div id="view-table" class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Danh sách Sạp chợ & Mặt bằng (<span id="filter-total-stalls"><?php echo count($stalls); ?></span>)</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px; width: 120px;">Mã sạp</th>
                        <th style="padding: 12px 16px; width: 160px;">Phân khu</th>
                        <th style="padding: 12px 16px; width: 160px;">Vị trí cụ thể</th>
                        <th style="padding: 12px 16px; width: 110px;">Diện tích</th>
                        <th style="padding: 12px 16px; width: 160px;">Đơn giá thuê / tháng</th>
                        <th style="padding: 12px 16px;">Tiểu thương thuê</th>
                        <th style="padding: 12px 16px; width: 120px;">Trạng thái</th>
                        <th style="padding: 12px 16px; text-align: right; width: 130px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="table-body-stalls">
                    <?php require DIR_TEMPLATE . '/backend/stall/table_rows.php'; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- CSRF Token phục vụ AJAX -->
<?php csrf_field(); ?>

<!-- Nạp JS xử lý AJAX & Form sạp chợ -->
<script>
$(document).ready(function() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const swalBg = isDark ? '#1a2332' : '#ffffff';
    const swalColor = isDark ? '#ffffff' : '#0f1623';
    const csrfToken = $('input[name="csrf_token"]').val();

    // Khởi tạo tính năng xóa sạp
    // App.utils.initDelete({ btnClass: 'btn-open-delete-stall', idAttr: 'stallId', nameAttr: 'stallCode', label: 'sạp chợ / mặt bằng' });
    $(document).on('click', '.btn-open-delete-stall', function(e) {
        e.preventDefault();
        var btn = this;
        var id = $(btn).data('stall-id');
        var code = $(btn).data('stall-code') || '';

        Swal.fire({
            title: 'Xác nhận xóa',
            text: 'Bạn có chắc chắn muốn xóa sạp chợ / mặt bằng "' + code + '" không? Hành động này không thể hoàn tác.',
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
                    data: { id: id, csrf_token: csrfToken },
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
    // App.utils.initFilterFormAjax({ buttonId: 'btn-filter-stalls', tbodyId: 'table-body-stalls', totalId: 'filter-total-stalls', apiUrl: 'api/filterStalls', pagePath: 'admin/stalls' });
    (function() {
        var btn = $('#btn-filter-stalls');
        if (!btn.length) return;
        var form = btn.closest('form');
        var inputs = form.find('input[name], select[name]');
        var tbody = $('#table-body-stalls');
        var totalEl = $('#filter-total-stalls');

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
                url: '<?php echo BASE_URL; ?>api/filterStalls',
                data: params,
                dataType: 'json',
                success: function(data) {
                    if (tbody.length) {
                        tbody.html(data.html).css('opacity', '1');
                    }
                    if (totalEl.length && typeof data.total !== 'undefined') {
                        totalEl.text(data.total);
                    }
                    var newUrl = '<?php echo BASE_URL; ?>admin/stalls' + (query ? '?' + query : '');
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

    // 1. Luồng gán sạp nhanh
    $("#table-body-stalls").on('click', '.btn-assign-stall-quick', function(e) {
        e.preventDefault();
        var stallId = $(this).attr("data-stall-id");
        var stallCode = $(this).attr("data-stall-code");

        Swal.fire({
            title: 'Đang tải danh sách tiểu thương...',
            allowOutsideClick: false,
            background: swalBg,
            color: swalColor,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            type: "GET",
            url: "<?php echo BASE_URL; ?>api/getAvailableTraders",
            dataType: 'json',
            success: function(res) {
                Swal.close();
                if (res.error) {
                    Swal.fire({ icon: 'error', title: 'Lỗi', text: res.error, background: swalBg, color: swalColor });
                    return;
                }

                if (!res || res.length === 0) {
                    Swal.fire({ icon: 'info', title: 'Thông báo', text: 'Không có tiểu thương hoạt động nào chưa có sạp.', background: swalBg, color: swalColor });
                    return;
                }

                var inputOptions = {};
                res.forEach(function(t) {
                    inputOptions[t.id] = t.fullname + ' (' + t.trader_code + ')';
                });

                Swal.fire({
                    title: 'Gán sạp ' + stallCode,
                    text: 'Chọn tiểu thương muốn gán vào sạp này:',
                    input: 'select',
                    inputOptions: inputOptions,
                    inputPlaceholder: '-- Chọn tiểu thương --',
                    showCancelButton: true,
                    confirmButtonText: 'Gán sạp',
                    cancelButtonText: 'Hủy bỏ',
                    confirmButtonColor: '#1ABB9C',
                    cancelButtonColor: '#a0aec0',
                    background: swalBg,
                    color: swalColor,
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Bạn cần chọn một tiểu thương!';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        Swal.fire({
                            title: 'Đang thực hiện gán sạp...',
                            allowOutsideClick: false,
                            background: swalBg,
                            color: swalColor,
                            didOpen: () => { Swal.showLoading(); }
                        });

                        $.ajax({
                            type: "POST",
                            url: "<?php echo BASE_URL; ?>api/assignStall",
                            data: {
                                stall_id: stallId,
                                trader_id: result.value,
                                csrf_token: csrfToken
                            },
                            dataType: 'json',
                            success: function(data) {
                                Swal.close();
                                if (data.status === 200) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Gán sạp thành công!',
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
                                Swal.fire({ icon: 'error', title: 'Lỗi', text: 'Lỗi kết nối hoặc xử lý phía máy chủ.', background: swalBg, color: swalColor });
                            }
                        });
                    }
                });
            },
            error: function() {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Lỗi', text: 'Không thể tải danh sách tiểu thương.', background: swalBg, color: swalColor });
            }
        });
    });

    // 2. Luồng chuyển đổi sạp nhanh
    $("#table-body-stalls").on('click', '.btn-transfer-stall-quick', function(e) {
        e.preventDefault();
        var currentStallId = $(this).attr("data-stall-id");
        var stallCode = $(this).attr("data-stall-code");
        var traderName = $(this).attr("data-trader-name");

        Swal.fire({
            title: 'Đang tải danh sách sạp khả dụng...',
            allowOutsideClick: false,
            background: swalBg,
            color: swalColor,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            type: "GET",
            url: "<?php echo BASE_URL; ?>api/getAvailableStalls?exclude_id=" + currentStallId,
            dataType: 'json',
            success: function(res) {
                Swal.close();
                if (res.error) {
                    Swal.fire({ icon: 'error', title: 'Lỗi', text: res.error, background: swalBg, color: swalColor });
                    return;
                }

                if (!res || res.length === 0) {
                    Swal.fire({ icon: 'info', title: 'Thông báo', text: 'Hiện tại không còn sạp nào khả dụng để chuyển đổi.', background: swalBg, color: swalColor });
                    return;
                }

                var inputOptions = {};
                res.forEach(function(s) {
                    if (s.trader_name) {
                        inputOptions[s.id] = s.stall_code + ' (' + s.area_name + ') - Đang thuê: ' + s.trader_name;
                    } else {
                        inputOptions[s.id] = s.stall_code + ' (' + s.area_name + ') - Trống';
                    }
                });

                Swal.fire({
                    title: `Chuyển/Đổi sạp ${stallCode}`,
                    text: `Chọn sạp mới (trống hoặc đang được thuê) để chuyển/đổi sạp cho tiểu thương "${traderName}":`,
                    input: 'select',
                    inputOptions: inputOptions,
                    inputPlaceholder: '-- Chọn sạp nhận chuyển đổi --',
                    showCancelButton: true,
                    confirmButtonText: 'Xác nhận chuyển đổi',
                    cancelButtonText: 'Đóng',
                    confirmButtonColor: '#066fd1',
                    cancelButtonColor: '#a0aec0',
                    background: swalBg,
                    color: swalColor,
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Bạn cần chọn một sạp nhận chuyển đổi!';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        Swal.fire({
                            title: 'Đang thực hiện chuyển đổi sạp...',
                            allowOutsideClick: false,
                            background: swalBg,
                            color: swalColor,
                            didOpen: () => { Swal.showLoading(); }
                        });

                        $.ajax({
                            type: "POST",
                            url: "<?php echo BASE_URL; ?>api/transferStall",
                            data: {
                                current_stall_id: currentStallId,
                                new_stall_id: result.value,
                                csrf_token: csrfToken
                            },
                            dataType: 'json',
                            success: function(data) {
                                Swal.close();
                                if (data.status === 200) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Chuyển đổi sạp thành công!',
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
                                Swal.fire({ icon: 'error', title: 'Lỗi', text: 'Lỗi kết nối hoặc xử lý phía máy chủ.', background: swalBg, color: swalColor });
                            }
                        });
                    }
                });
            },
            error: function() {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Lỗi', text: 'Không thể tải danh sách sạp.', background: swalBg, color: swalColor });
            }
        });
    });
});
</script>

