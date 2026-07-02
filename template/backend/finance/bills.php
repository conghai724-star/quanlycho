<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div style="display: flex; gap: 8px;">
        <select class="form-control" style="width: 140px; height: 36px; font-size: 13px;">
            <option value="">Trạng thái</option>
            <option value="paid">Đã thanh toán</option>
            <option value="unpaid">Chưa thanh toán</option>
        </select>
        <input type="text" class="form-control" placeholder="Mã sạp, tên tiểu thương..." style="width: 250px; height: 36px; font-size: 13px;">
        <button class="btn btn-outline" style="height: 36px; padding: 0 16px;">Lọc</button>
    </div>
    
    <button class="btn btn-primary" onclick="simulateBillCalculation()" style="height: 36px; display: inline-flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-calculator"></i>
        Tổng hợp Hóa đơn tháng
    </button>
</div>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Hóa đơn thanh toán sạp chợ</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px; width: 120px;">Mã hóa đơn</th>
                        <th style="padding: 12px 16px; width: 100px;">Mã sạp</th>
                        <th style="padding: 12px 16px;">Tên tiểu thương</th>
                        <th style="padding: 12px 16px; width: 90px;">Kỳ thu</th>
                        <th style="padding: 12px 16px; width: 140px;">Tổng tiền</th>
                        <th style="padding: 12px 16px; width: 120px;">Hạn nộp tiền</th>
                        <th style="padding: 12px 16px; width: 130px;">Trạng thái</th>
                        <th style="padding: 12px 16px; text-align: right; width: 120px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($bills)): ?>
                        <?php foreach ($bills as $bill): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($bill['bill_code']); ?>
                                </td>
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600;">
                                    <?php echo htmlspecialchars($bill['stall_code']); ?>
                                </td>
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);">
                                    <?php echo htmlspecialchars($bill['trader_name']); ?>
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($bill['period']); ?>
                                </td>
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--primary);">
                                    <?php echo number_format($bill['total_amount'], 0, ',', '.'); ?> đ
                                </td>
                                <td style="padding: 14px 16px; color: var(--text-muted);">
                                    <?php echo htmlspecialchars($bill['due_date']); ?>
                                </td>
                                <td style="padding: 14px 16px;">
                                    <?php if ($bill['status'] === 'paid'): ?>
                                        <span class="status status-green">Đã thanh toán</span>
                                    <?php else: ?>
                                        <span class="status status-red">Chưa thanh toán</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <div style="display: flex; justify-content: flex-end; gap: 4px;">
                                        <!-- Chi tiết hóa đơn (Bóc tách D.1 -> D.5) -->
                                        <button class="btn btn-outline btn-sm" onclick="viewBillDetails('<?php echo htmlspecialchars($bill['bill_code']); ?>', '<?php echo htmlspecialchars($bill['stall_code']); ?>', '<?php echo htmlspecialchars($bill['trader_name']); ?>')" style="padding: 4px 6px;" title="Xem chi tiết hóa đơn">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <!-- Thu tiền chuyển hướng sang Form lập phiếu thu -->
                                        <a href="<?php echo BASE_URL; ?>admin/transaction_add?type=receipt" class="btn btn-outline btn-sm" style="padding: 4px 6px; text-decoration: none;" title="Lập Phiếu Thu">
                                            <i class="fa-solid fa-file-invoice-dollar"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="padding: 30px; text-align: center; color: var(--text-muted);">Không có dữ liệu hóa đơn.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Hàm xem chi tiết hóa đơn bóc tách các loại phí (Mục D.1 -> D.5)
    function viewBillDetails(code, stall, name) {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        
        Swal.fire({
            title: 'Chi tiết Hóa đơn ' + code,
            html: `<div style="text-align: left; font-size: 13.5px; line-height: 1.6;">
                    <p style="margin-bottom: 8px;"><strong>Mã sạp:</strong> ${stall} | <strong>Tiểu thương:</strong> ${name}</p>
                    <hr style="border: 0; border-top: 1px solid var(--border-color-light); margin: 8px 0;">
                    <div style="display: flex; justify-content: space-between;"><span>1. Tiền thuê sạp (D.1):</span> <strong>3.000.000 đ</strong></div>
                    <div style="display: flex; justify-content: space-between;"><span>2. Phí quản lý (D.2):</span> <strong>200.000 đ</strong></div>
                    <div style="display: flex; justify-content: space-between;"><span>3. Tiền điện & nước (D.3):</span> <strong>200.000 đ</strong></div>
                    <div style="display: flex; justify-content: space-between; padding-left: 15px; font-size: 12.5px; color: var(--text-muted);"><span>- Tiền điện (150 kWh):</span> <span>150.000 đ</span></div>
                    <div style="display: flex; justify-content: space-between; padding-left: 15px; font-size: 12.5px; color: var(--text-muted);"><span>- Tiền nước (15 m³):</span> <span>50.000 đ</span></div>
                    <div style="display: flex; justify-content: space-between;"><span>4. Phí vệ sinh (D.4):</span> <span>150.000 đ</span></div>
                    <div style="display: flex; justify-content: space-between;"><span>5. Phí bảo vệ (D.5):</span> <span>100.000 đ</span></div>
                    <hr style="border: 0; border-top: 1px solid var(--border-color-light); margin: 8px 0;">
                    <div style="display: flex; justify-content: space-between; font-size: 15px; font-weight: bold; color: var(--primary);">
                        <span>TỔNG CỘNG:</span> <span>3.650.000 đ</span>
                    </div>
                   </div>`,
            confirmButtonText: 'Đóng',
            confirmButtonColor: '#1ABB9C',
            background: isDark ? '#1a2332' : '#ffffff',
            color: isDark ? '#ffffff' : '#0f1623'
        });
    }

    // Hàm mô phỏng tính toán hóa đơn
    function simulateBillCalculation() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        
        Swal.fire({
            title: 'Đang tổng hợp hóa đơn...',
            text: 'Hệ thống đang quét chỉ số điện nước và tính tiền sạp kỳ 06/2026.',
            allowOutsideClick: false,
            background: isDark ? '#1a2332' : '#ffffff',
            color: isDark ? '#ffffff' : '#0f1623',
            didOpen: () => {
                Swal.showLoading();
                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Tổng hợp hoàn tất!',
                        text: 'Đã tạo thành công hóa đơn tháng cho toàn bộ các sạp đang thuê.',
                        confirmButtonColor: '#1ABB9C',
                        background: isDark ? '#1a2332' : '#ffffff',
                        color: isDark ? '#ffffff' : '#0f1623'
                    });
                }, 1500);
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
