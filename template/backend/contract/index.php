<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div style="display: flex; gap: 8px;">
        <input type="text" class="form-control" placeholder="Tìm số hợp đồng, tiểu thương..." style="width: 250px; height: 36px; font-size: 13px;">
        <select class="form-control" style="width: 150px; height: 36px; font-size: 13px;">
            <option value="">Trạng thái hiệu lực</option>
            <option value="active">Còn hiệu lực</option>
            <option value="expired">Hết hạn</option>
        </select>
        <button class="btn btn-outline" style="height: 36px; padding: 0 16px;">Lọc</button>
    </div>
    
    <a href="<?php echo BASE_URL; ?>admin/contract_add" class="btn btn-primary" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: white;">
        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 14px; height: 14px;"><path d="M8 2v12M2 8h12"/></svg>
        Lập Hợp đồng mới
    </a>
</div>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Hồ sơ Hợp đồng thuê mặt bằng sạp</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px; width: 120px;">Số Hợp đồng</th>
                        <th style="padding: 12px 16px;">Tên tiểu thương</th>
                        <th style="padding: 12px 16px; width: 90px;">Mã sạp</th>
                        <th style="padding: 12px 16px; width: 100px;">Ngày ký</th>
                        <th style="padding: 12px 16px; width: 100px;">Ngày hết hạn</th>
                        <th style="padding: 12px 16px; width: 120px;">Đơn giá thuê</th>
                        <th style="padding: 12px 16px; width: 110px;">Đặt cọc</th>
                        <th style="padding: 12px 16px; width: 90px; text-align: center;">Phụ lục</th>
                        <th style="padding: 12px 16px; width: 110px;">Trạng thái</th>
                        <th style="padding: 12px 16px; text-align: right; width: 160px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($contracts)): ?>
                        <?php foreach ($contracts as $index => $contract): ?>
                            <!-- Giả lập số phụ lục (Mục C.5) -->
                            <?php 
                                $appendixCounts = [1, 2, 0];
                                $appCount = $appendixCounts[$index % count($appendixCounts)];
                            ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($contract['contract_code']); ?>
                                </td>
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($contract['trader_name']); ?>
                                </td>
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600;">
                                    <?php echo htmlspecialchars($contract['stall_code']); ?>
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($contract['start_date']); ?>
                                </td>
                                <td style="padding: 14px 16px; color: <?php echo $contract['status'] === 'expired' ? 'var(--red)' : 'var(--text-muted)'; ?>; font-weight: <?php echo $contract['status'] === 'expired' ? '600' : 'normal'; ?>;">
                                    <?php echo htmlspecialchars($contract['end_date']); ?>
                                </td>
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--primary);">
                                    <?php echo number_format($contract['price'], 0, ',', '.'); ?> đ
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted);">
                                    <?php echo number_format($contract['deposit'], 0, ',', '.'); ?> đ
                                </td>
                                <td style="padding: 14px 16px; text-align: center;">
                                    <?php if ($appCount > 0): ?>
                                        <button class="btn btn-ghost btn-sm" onclick="viewAppendix('<?php echo htmlspecialchars($contract['contract_code']); ?>')" style="padding: 4px; color: var(--primary);" title="Xem phụ lục hợp đồng">
                                            <i class="fa-solid fa-paperclip"></i> (<?php echo $appCount; ?>)
                                        </button>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 11px;">Không có</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px;">
                                    <?php if ($contract['status'] === 'active'): ?>
                                        <span class="status status-green">Còn hiệu lực</span>
                                    <?php else: ?>
                                        <span class="status status-red">Đã hết hạn</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <div style="display: flex; justify-content: flex-end; gap: 4px;">
                                        <!-- In hợp đồng (Mục C.8) -->
                                        <button class="btn btn-outline btn-sm" onclick="printContract('<?php echo htmlspecialchars($contract['contract_code']); ?>')" style="padding: 4px 6px;" title="In hợp đồng">
                                            <i class="fa-solid fa-print"></i>
                                        </button>
                                        <!-- Gia hạn hợp đồng (Mục C.2) -->
                                        <button class="btn btn-outline btn-sm" onclick="renewContract('<?php echo htmlspecialchars($contract['contract_code']); ?>')" style="padding: 4px 6px; color: var(--primary);" title="Gia hạn">
                                            <i class="fa-solid fa-calendar-plus"></i>
                                        </button>
                                        <!-- Chấm dứt / Thanh lý hợp đồng (Mục C.3, C.4) -->
                                        <button class="btn btn-outline btn-sm" onclick="terminateContract('<?php echo htmlspecialchars($contract['contract_code']); ?>')" style="padding: 4px 6px; color: var(--red);" title="Thanh lý / Chấm dứt">
                                            <i class="fa-solid fa-file-contract"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" style="padding: 30px; text-align: center; color: var(--text-muted);">Không có dữ liệu hợp đồng.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // 1. Quản lý phụ lục (Mục C.5)
    function viewAppendix(code) {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        Swal.fire({
            title: 'Phụ lục hợp đồng ' + code,
            html: `<div style="text-align: left; font-size: 13px;">
                    <p><strong>Phụ lục 01:</strong> Thay đổi đơn giá thuê sạp (Áp dụng từ 01/06/2026)</p>
                    <p><em>Mức tăng: +200.000 đ/tháng do cải tạo hệ thống thoát nước.</em></p>
                   </div>`,
            icon: 'info',
            confirmButtonText: 'Đóng',
            confirmButtonColor: '#1ABB9C',
            background: isDark ? '#1a2332' : '#ffffff',
            color: isDark ? '#ffffff' : '#0f1623'
        });
    }

    // 2. In hợp đồng theo mẫu (Mục C.8)
    function printContract(code) {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        Swal.fire({
            title: 'Tạo bản in Hợp đồng',
            text: 'Đang kết xuất PDF hợp đồng ' + code + ' theo mẫu chuẩn ban quản lý chợ...',
            timer: 1500,
            timerProgressBar: true,
            background: isDark ? '#1a2332' : '#ffffff',
            color: isDark ? '#ffffff' : '#0f1623',
            didOpen: () => {
                Swal.showLoading();
            }
        }).then(() => {
            Swal.fire({
                icon: 'success',
                title: 'Đã xuất file in!',
                text: 'Hợp đồng đã được xuất ra định dạng PDF thành công.',
                confirmButtonColor: '#1ABB9C',
                background: isDark ? '#1a2332' : '#ffffff',
                color: isDark ? '#ffffff' : '#0f1623'
            });
        });
    }

    // 3. Gia hạn hợp đồng (Mục C.2)
    function renewContract(code) {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        Swal.fire({
            title: 'Gia hạn Hợp đồng ' + code,
            text: 'Nhập thời gian gia hạn thêm (tháng):',
            input: 'number',
            inputValue: 12,
            inputAttributes: {
                min: 1,
                max: 60,
                step: 1
            },
            showCancelButton: true,
            confirmButtonText: 'Xác nhận gia hạn',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#1ABB9C',
            background: isDark ? '#1a2332' : '#ffffff',
            color: isDark ? '#ffffff' : '#0f1623'
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                Swal.fire({
                    icon: 'success',
                    title: 'Gia hạn thành công!',
                    text: 'Hợp đồng ' + code + ' đã được gia hạn thêm ' + result.value + ' tháng.',
                    confirmButtonColor: '#1ABB9C',
                    background: isDark ? '#1a2332' : '#ffffff',
                    color: isDark ? '#ffffff' : '#0f1623'
                });
            }
        });
    }

    // 4. Thanh lý / Chấm dứt hợp đồng (Mục C.3, C.4)
    function terminateContract(code) {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        Swal.fire({
            title: 'Thanh lý hoặc Chấm dứt trước hạn?',
            text: 'Chọn phương án xử lý cho hợp đồng ' + code,
            icon: 'warning',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: 'Thanh lý hợp đồng (Cơ bản)',
            denyButtonText: 'Chấm dứt trước hạn (Đột xuất)',
            cancelButtonText: 'Đóng',
            confirmButtonColor: '#1ABB9C',
            denyButtonColor: '#EA4335',
            background: isDark ? '#1a2332' : '#ffffff',
            color: isDark ? '#ffffff' : '#0f1623'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: 'Đã hoàn tất thanh lý!',
                    text: 'Hợp đồng ' + code + ' đã chuyển sang lưu trữ lưu hồ sơ.',
                    confirmButtonColor: '#1ABB9C',
                    background: isDark ? '#1a2332' : '#ffffff',
                    color: isDark ? '#ffffff' : '#0f1623'
                });
            } else if (result.isDenied) {
                Swal.fire({
                    title: 'Nhập lý do chấm dứt trước hạn:',
                    input: 'text',
                    inputPlaceholder: 'Ví dụ: Vi phạm quy định chợ, trả mặt bằng...',
                    showCancelButton: true,
                    confirmButtonText: 'Xác nhận chấm dứt',
                    confirmButtonColor: '#EA4335',
                    background: isDark ? '#1a2332' : '#ffffff',
                    color: isDark ? '#ffffff' : '#0f1623'
                }).then((termRes) => {
                    if (termRes.isConfirmed && termRes.value) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Đã chấm dứt hợp đồng!',
                            text: 'Hợp đồng ' + code + ' đã bị dừng trước hạn. Lý do: ' + termRes.value,
                            confirmButtonColor: '#1ABB9C',
                            background: isDark ? '#1a2332' : '#ffffff',
                            color: isDark ? '#ffffff' : '#0f1623'
                        });
                    }
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const toastConfig = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: isDark ? '#1a2332' : '#ffffff',
            color: isDark ? '#ffffff' : '#0f1623'
        });

        <?php if ($success = session::get('success_message')): session::delete('success_message'); ?>
            toastConfig.fire({
                icon: 'success',
                title: '<?php echo $success; ?>'
            });
        <?php endif; ?>
    });
</script>
