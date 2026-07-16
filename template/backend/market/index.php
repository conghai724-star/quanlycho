<!-- Giao diện danh sách Chợ -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div style="font-size: 20px; font-weight: 700; color: var(--text-heading);">Quản Lý Danh Sách Chợ</div>
    
    <a href="<?php echo BASE_URL; ?>system/market_add" class="btn btn-primary" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: white;">
        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 14px; height: 14px;"><path d="M8 2v12M2 8h12"/></svg>
        Thêm Chợ Mới
    </a>
</div>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Danh sách chợ trong hệ thống</div>
        <form method="GET" action="<?php echo BASE_URL; ?>system/markets" style="display: flex; gap: 8px;" data-native-submit="true">
            <input type="text" name="q" placeholder="Tìm theo tên hoặc mã chợ..." value="<?php echo htmlspecialchars($search ?? ''); ?>" style="padding: 6px 12px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 13px; width: 220px;">
            <button type="submit" class="btn btn-outline" style="padding: 6px 12px; font-size: 13px;">Tìm kiếm</button>
        </form>
    </div>
    
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px; width: 120px;">Mã Chợ</th>
                        <th style="padding: 12px 16px;">Tên Chợ</th>
                        <th style="padding: 12px 16px; width: 150px;">Số Điện Thoại</th>
                        <th style="padding: 12px 16px; width: 180px;">Email</th>
                        <th style="padding: 12px 16px; width: 160px;">Trưởng Ban QL</th>
                        <th style="padding: 12px 16px; width: 130px;">Trạng Thái</th>
                        <th style="padding: 12px 16px; text-align: right; width: 100px;">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($markets)): ?>
                        <?php foreach ($markets as $m): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($m['market_code']); ?>
                                </td>
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($m['name']); ?>
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($m['phone'] ?: '—'); ?>
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($m['email'] ?: '—'); ?>
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($m['manager_name'] ?: '—'); ?>
                                </td>
                                <td style="padding: 14px 16px;">
                                    <?php if ($m['status_code'] === 'active'): ?>
                                        <span class="status status-green">Đang Hoạt Động</span>
                                    <?php else: ?>
                                        <span class="status status-red">Ngừng Hoạt Động</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <div style="display: flex; justify-content: flex-end; gap: 6px;">
                                        <a href="<?php echo BASE_URL; ?>system/market_edit/<?php echo $m['id']; ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 11px; text-decoration: none; color: inherit; display: inline-flex; align-items: center; justify-content: center;" title="Sửa thông tin">
                                            <i class="fa-solid fa-pen" style="margin-right: 4px;"></i> Sửa
                                        </a>
                                        <button class="btn btn-ghost btn-sm btn-open-delete-market" data-market-id="<?php echo $m['id']; ?>" data-market-name="<?php echo htmlspecialchars($m['name']); ?>" data-url="<?php echo BASE_URL; ?>api/deleteMarket" style="padding: 4px 8px; font-size: 11px; color: #EA4335; display: inline-flex; align-items: center; justify-content: center;" title="Xóa">
                                            <i class="fa-solid fa-trash-can" style="margin-right: 4px;"></i> Xóa
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 40px; text-align: center; color: var(--text-muted);">
                                Không tìm thấy chợ nào trong hệ thống.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php csrf_field(); ?>

<script>
$(document).ready(function() {
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var swalBg = isDark ? '#1a2332' : '#ffffff';
    var swalColor = isDark ? '#ffffff' : '#0f1623';

    // Xóa chợ qua AJAX
    $(document).on('click', '.btn-open-delete-market', function(e) {
        e.preventDefault();
        var btn = this;
        var id = $(btn).data('market-id');
        var name = $(btn).data('market-name') || '';
        var csrf = $('input[name="csrf_token"]').val() || '';

        Swal.fire({
            title: 'Xác nhận xóa',
            text: 'Bạn có chắc chắn muốn xóa chợ "' + name + '" không? Hành động này không thể hoàn tác.',
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
                                showConfirmButton: false,
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
});
</script>
