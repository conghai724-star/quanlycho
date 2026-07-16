<!-- Phân loại Tab & Nút thêm mới -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <!-- Nút chuyển đổi Tab -->
    <div class="segmented" role="radiogroup" style="max-width: 380px;">
        <label><input type="radio" name="user-mode" value="accounts" checked onclick="App.user.switchTab('accounts')"><span>Tài khoản & Phân quyền</span></label>
        <label><input type="radio" name="user-mode" value="logs" onclick="App.user.switchTab('logs')"><span>Nhật ký hệ thống (Audit)</span></label>
    </div>
    
    <a href="<?php echo BASE_URL; ?>system/user_add" class="btn btn-primary" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: white;">
        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 14px; height: 14px;"><path d="M8 2v12M2 8h12"/></svg>
        Tạo tài khoản mới
    </a>
</div>

<!-- TAB 1: DANH SÁCH TÀI KHOẢN -->
<div id="user-accounts" class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Danh sách tài khoản nhân viên BQL</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px; width: 150px;">Tên đăng nhập</th>
                        <th style="padding: 12px 16px;">Họ tên nhân viên</th>
                        <th style="padding: 12px 16px;">Địa chỉ Email</th>
                        <th style="padding: 12px 16px; width: 180px;">Vai trò hệ thống</th>
                        <th style="padding: 12px 16px; width: 140px;">Trạng thái</th>
                        <th style="padding: 12px 16px; text-align: right; width: 120px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                        <?php foreach ($users as $user): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($user['username']); ?>
                                </td>
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($user['fullname']); ?>
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </td>
                                <td style="padding: 14px 16px;">
                                    <?php 
                                    $actorCode = $user['actor_code'] ?? 'admin';
                                    $actorName = $user['actor_name'] ?? 'Nhân viên';
                                    if ($actorCode === 'super_market'): 
                                    ?>
                                        <span class="chip" style="background-color: rgba(234, 67, 53, 0.1); color: #EA4335; border: 1px solid rgba(234, 67, 53, 0.2); font-weight: 600;"><?php echo htmlspecialchars($actorName); ?></span>
                                    <?php elseif ($actorCode === 'admin_market'): ?>
                                        <span class="chip" style="background-color: rgba(155, 89, 182, 0.1); color: #9b59b6; border: 1px solid rgba(155, 89, 182, 0.2); font-weight: 600;"><?php echo htmlspecialchars($actorName); ?></span>
                                    <?php else: ?>
                                        <span class="chip" style="background-color: rgba(66, 133, 244, 0.1); color: #4285F4; border: 1px solid rgba(66, 133, 244, 0.2); font-weight: 600;"><?php echo htmlspecialchars($actorName); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px;" id="status-col-<?php echo $user['id']; ?>">
                                    <?php if ($user['is_active'] == 1): ?>
                                        <span class="status status-green">Hoạt động</span>
                                    <?php else: ?>
                                        <span class="status status-red">Bị khóa</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <div style="display: flex; justify-content: flex-end; gap: 6px;">
                                        <!-- Sửa tài khoản -->
                                        <a href="<?php echo BASE_URL; ?>system/user_edit/<?php echo $user['id']; ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px; font-size: 11px; text-decoration: none; color: inherit; display: inline-flex; align-items: center; justify-content: center;" title="Sửa tài khoản">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <!-- Khóa/Mở khóa tài khoản (Mục F.8) -->
                                        <button class="btn btn-outline btn-sm" onclick="App.user.toggleLockUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['fullname']); ?>')" style="padding: 4px 8px; font-size: 11px;" title="Khóa / Mở khóa tài khoản">
                                            <i class="fa-solid fa-user-shield"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="padding: 30px; text-align: center; color: var(--text-muted);">Không có dữ liệu tài khoản.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TAB 2: NHẬT KÝ HỆ THỐNG (AUDIT LOG - Mục F.9, F.10) -->
<div id="user-logs" class="card" style="display: none;">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Nhật ký Đăng nhập & Thao tác vận hành hệ thống</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px; width: 160px;">Thời gian</th>
                        <th style="padding: 12px 16px; width: 140px;">Tài khoản</th>
                        <th style="padding: 12px 16px; width: 140px;">Phân loại nhật ký</th>
                        <th style="padding: 12px 16px;">Hành động chi tiết (Audit Log)</th>
                        <th style="padding: 12px 16px; width: 130px;">Địa chỉ IP</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 12px 16px; color: var(--text-muted);">01/07/2026 17:58:34</td>
                        <td style="padding: 12px 16px; font-weight: 600;">admin</td>
                        <td style="padding: 12px 16px;"><span class="status status-green">Đăng nhập</span></td>
                        <td style="padding: 12px 16px; color: var(--text-heading);">Đăng nhập thành công vào hệ thống điều hành.</td>
                        <td style="padding: 12px 16px; color: var(--text-muted); font-family: monospace;">192.168.1.55</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 12px 16px; color: var(--text-muted);">01/07/2026 17:55:12</td>
                        <td style="padding: 12px 16px; font-weight: 600;">admin</td>
                        <td style="padding: 12px 16px;"><span class="status status-blue">Thao tác</span></td>
                        <td style="padding: 12px 16px; color: var(--text-heading);">Thêm mới tiểu thương <strong>Nguyễn Thị Thu Hà</strong> vào danh sách.</td>
                        <td style="padding: 12px 16px; color: var(--text-muted); font-family: monospace;">192.168.1.55</td>
                    </tr>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 12px 16px; color: var(--text-muted);">01/07/2026 17:51:20</td>
                        <td style="padding: 12px 16px; font-weight: 600;">ketoan_an</td>
                        <td style="padding: 12px 16px;"><span class="status status-blue">Thao tác</span></td>
                        <td style="padding: 12px 16px; color: var(--text-heading);">Lập phiếu thu tiền sạp <strong>SẠP-B01</strong> trị giá 5.480.000 đ.</td>
                        <td style="padding: 12px 16px; color: var(--text-muted); font-family: monospace;">192.168.1.102</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const swalBg = isDark ? '#1a2332' : '#ffffff';
    const swalColor = isDark ? '#ffffff' : '#0f1623';

    window.App = window.App || {};
    window.App.user = {
        // 1. Chuyển tab giữa Tài khoản và Nhật ký
        switchTab: function(mode) {
            const accounts = document.getElementById('user-accounts');
            const logs = document.getElementById('user-logs');
            if (!accounts || !logs) return;

            if (mode === 'accounts') {
                accounts.style.display = 'block';
                logs.style.display = 'none';
            } else {
                accounts.style.display = 'none';
                logs.style.display = 'block';
            }
        },

    // 2. Khóa / Mở khóa tài khoản
        toggleLockUser: function(id, name) {
            Swal.fire({
                title: 'Khóa/Mở khóa tài khoản?',
                text: "Xác nhận thay đổi trạng thái hoạt động của tài khoản '" + name + "'?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Đồng ý',
                cancelButtonText: 'Hủy bỏ',
                confirmButtonColor: '#EA4335',
                cancelButtonColor: '#a0aec0',
                background: swalBg,
                color: swalColor
            }).then((result) => {
                if (result.isConfirmed) {
                    // App.utils.ajaxRequest('POST', '<?php echo BASE_URL; ?>system/user_toggle_status/' + id, {}, (res) => { ... });
                    $.ajax({
                        type: 'POST',
                        url: '<?php echo BASE_URL; ?>system/user_toggle_status/' + id,
                        data: JSON.stringify({}),
                        contentType: 'application/json',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '<?php echo security::getToken(); ?>'
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success) {
                                const statusCol = document.getElementById('status-col-' + id);
                                if (res.new_status === 1) {
                                    statusCol.innerHTML = '<span class="status status-green">Hoạt động</span>';
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Đã kích hoạt lại tài khoản!',
                                        confirmButtonColor: '#1ABB9C',
                                        background: swalBg,
                                        color: swalColor
                                    });
                                } else {
                                    statusCol.innerHTML = '<span class="status status-red">Bị khóa</span>';
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Đã khóa tài khoản!',
                                        confirmButtonColor: '#1ABB9C',
                                        background: swalBg,
                                        color: swalColor
                                    });
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Thao tác thất bại!',
                                    text: res.message || 'Có lỗi xảy ra.',
                                    confirmButtonColor: '#EA4335',
                                    background: swalBg,
                                    color: swalColor
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Thao tác thất bại!',
                                text: 'Không thể kết nối đến máy chủ.',
                                confirmButtonColor: '#EA4335',
                                background: swalBg,
                                color: swalColor
                            });
                        }
                    });
                }
            });
        }
    };
});
</script>


