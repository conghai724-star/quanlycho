<!-- Giao diện Phân Quyền Phân Hệ Nhân Viên -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
    .permissions-container {
        font-family: 'Outfit', sans-serif;
        color: #2c3e50;
        background-color: #f4f7f6;
        padding: 4px 0 20px 0;
    }

    .card {
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.02);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .card-header {
        border-bottom: 1px solid #f1f2f6;
        padding: 20px 24px;
        background-color: #ffffff;
    }

    .card-title {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .card-body {
        padding: 24px;
    }

    /* Table styling */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }

    .perm-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .perm-table th {
        background-color: #f8f9fa;
        color: #7f8c8d;
        font-weight: 600;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 16px 20px;
        border-bottom: 2px solid #e2e8f0;
    }

    .perm-table td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f2f6;
        font-size: 14.5px;
        vertical-align: middle;
    }

    .perm-table tr:hover {
        background-color: #fafbfb;
    }

    .staff-info {
        display: flex;
        flex-direction: column;
    }

    .staff-info .name {
        font-weight: 600;
        color: #2c3e50;
    }

    .staff-info .email {
        font-size: 12px;
        color: #95a5a6;
        margin-top: 2px;
    }

    .market-badge {
        display: inline-block;
        padding: 6px 12px;
        background-color: rgba(58, 123, 213, 0.08);
        color: #3a7bd5;
        border-radius: 8px;
        font-weight: 600;
        font-size: 12.5px;
    }

    /* Custom Checkbox Toggle Switch style */
    .switch-label {
        display: inline-flex;
        align-items: center;
        cursor: pointer;
        position: relative;
    }

    .switch-input {
        display: none;
    }

    .switch-toggle {
        width: 44px;
        height: 22px;
        background-color: #cbd5e1;
        border-radius: 11px;
        position: relative;
        transition: background-color 0.2s ease;
        margin-right: 8px;
    }

    .switch-toggle::before {
        content: '';
        position: absolute;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background-color: white;
        top: 2px;
        left: 2px;
        transition: transform 0.2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .switch-input:checked + .switch-toggle {
        background-color: #2ecc71;
    }

    .switch-input:checked + .switch-toggle::before {
        transform: translateX(22px);
    }

    .switch-text {
        font-size: 13.5px;
        color: #2c3e50;
    }
</style>

<div class="permissions-container">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fa-solid fa-user-shield" style="color: #3a7bd5;"></i>
                Danh sách Phân quyền Phân hệ cho Nhân viên
            </h2>
        </div>
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="padding: 12px 16px; background-color: rgba(234, 67, 53, 0.1); color: #EA4335; border: 1px solid rgba(234, 67, 53, 0.2); border-radius: 6px;">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php elseif (empty($staffList)): ?>
                <div style="text-align: center; padding: 40px; color: #7f8c8d;">
                    <i class="fa-regular fa-folder-open" style="font-size: 48px; margin-bottom: 12px; color: #bdc3c7;"></i>
                    <p style="font-size: 16px; margin: 0;">Không tìm thấy tài khoản nhân viên vận hành (Staff) nào thuộc các chợ bạn quản lý.</p>
                    <a href="<?php echo BASE_URL; ?>system/user_add" class="btn btn-primary" style="margin-top: 15px; text-decoration: none; color: white; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-user-plus"></i> Tạo nhân viên mới
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="perm-table">
                        <thead>
                            <tr>
                                <th>Nhân viên</th>
                                <th>Chợ áp dụng</th>
                                <th>Tiểu thương (`trader`)</th>
                                <th>Sạp chợ (`stall`)</th>
                                <th>Hợp đồng (`contract`)</th>
                                <th>Tài chính (`finance`)</th>
                                <th>An toàn TP (`foodsafety`)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            foreach ($staffList as $staff): 
                                foreach ($managedMarkets as $market):
                                    // Kiểm tra xem nhân viên này có liên kết với chợ này không
                                    $db = database::getInstance();
                                    $isLinked = $db->selectOne("
                                        SELECT 1 FROM user_markets 
                                        WHERE user_id = :u_id AND market_id = :m_id
                                    ", ['u_id' => $staff['id'], 'm_id' => $market['id']]);

                                    if (!$isLinked) continue;
                            ?>
                                <tr>
                                    <td>
                                        <div class="staff-info">
                                            <span class="name"><?php echo htmlspecialchars($staff['fullname']); ?></span>
                                            <span class="email">@<?php echo htmlspecialchars($staff['username']); ?> | <?php echo htmlspecialchars($staff['email']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="market-badge">
                                            <i class="fa-solid fa-store" style="margin-right: 4px;"></i>
                                            <?php echo htmlspecialchars($market['name']); ?>
                                        </span>
                                    </td>
                                    <!-- Module: trader -->
                                    <td>
                                        <label class="switch-label">
                                            <input type="checkbox" class="switch-input perm-toggle" 
                                                   data-user="<?php echo $staff['id']; ?>" 
                                                   data-market="<?php echo $market['id']; ?>" 
                                                   data-module="trader"
                                                   <?php echo isset($permissions[$staff['id']][$market['id']]['trader']) ? 'checked' : ''; ?>>
                                            <span class="switch-toggle"></span>
                                        </label>
                                    </td>
                                    <!-- Module: stall -->
                                    <td>
                                        <label class="switch-label">
                                            <input type="checkbox" class="switch-input perm-toggle" 
                                                   data-user="<?php echo $staff['id']; ?>" 
                                                   data-market="<?php echo $market['id']; ?>" 
                                                   data-module="stall"
                                                   <?php echo isset($permissions[$staff['id']][$market['id']]['stall']) ? 'checked' : ''; ?>>
                                            <span class="switch-toggle"></span>
                                        </label>
                                    </td>
                                    <!-- Module: contract -->
                                    <td>
                                        <label class="switch-label">
                                            <input type="checkbox" class="switch-input perm-toggle" 
                                                   data-user="<?php echo $staff['id']; ?>" 
                                                   data-market="<?php echo $market['id']; ?>" 
                                                   data-module="contract"
                                                   <?php echo isset($permissions[$staff['id']][$market['id']]['contract']) ? 'checked' : ''; ?>>
                                            <span class="switch-toggle"></span>
                                        </label>
                                    </td>
                                    <!-- Module: finance -->
                                    <td>
                                        <label class="switch-label">
                                            <input type="checkbox" class="switch-input perm-toggle" 
                                                   data-user="<?php echo $staff['id']; ?>" 
                                                   data-market="<?php echo $market['id']; ?>" 
                                                   data-module="finance"
                                                   <?php echo isset($permissions[$staff['id']][$market['id']]['finance']) ? 'checked' : ''; ?>>
                                            <span class="switch-toggle"></span>
                                        </label>
                                    </td>
                                    <!-- Module: foodsafety -->
                                    <td>
                                        <label class="switch-label">
                                            <input type="checkbox" class="switch-input perm-toggle" 
                                                   data-user="<?php echo $staff['id']; ?>" 
                                                   data-market="<?php echo $market['id']; ?>" 
                                                   data-module="foodsafety"
                                                   <?php echo isset($permissions[$staff['id']][$market['id']]['foodsafety']) ? 'checked' : ''; ?>>
                                            <span class="switch-toggle"></span>
                                        </label>
                                    </td>
                                </tr>
                            <?php 
                                endforeach;
                            endforeach; 
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    $(function() {
        $('.perm-toggle').on('change', function() {
            var input = $(this);
            var userId = input.data('user');
            var marketId = input.data('market');
            var module = input.data('module');
            var checked = input.is(':checked') ? 1 : 0;

            // Vô hiệu hóa tạm thời để tránh bấm loạn xạ khi đang gọi AJAX
            input.prop('disabled', true);

             $.ajax({
                url: '<?php echo BASE_URL; ?>system/save_permissions',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '<?php echo security::getToken(); ?>'
                },
                data: {
                    user_id: userId,
                    market_id: marketId,
                    module: module,
                    checked: checked
                },
                dataType: 'json',
                success: function(res) {
                    input.prop('disabled', false);
                    if (!res.success) {
                        // Trả lại trạng thái cũ nếu lỗi
                        input.prop('checked', !checked);
                        Swal.fire({
                            title: 'Lỗi phân quyền',
                            text: res.message,
                            icon: 'error',
                            confirmButtonText: 'Đã hiểu'
                        });
                    } else {
                        // Toast nhỏ góc màn hình thông báo thành công
                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true
                        });
                        Toast.fire({
                            icon: 'success',
                            title: res.message
                        });
                    }
                },
                error: function(xhr) {
                    input.prop('disabled', false);
                    input.prop('checked', !checked);
                    var msg = 'Không thể kết nối với máy chủ.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'Lỗi kết nối',
                        text: msg,
                        icon: 'error',
                        confirmButtonText: 'Đã hiểu'
                    });
                }
            });
        });
    });
</script>
