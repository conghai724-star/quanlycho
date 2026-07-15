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
    
    <div style="display: flex; gap: 8px;">
        <a href="<?php echo BASE_URL; ?>admin/bill_add" class="btn btn-outline" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
            <i class="fa-solid fa-file-invoice"></i> Lập hóa đơn mới
        </a>
        <button class="btn btn-primary" onclick="App.finance.simulateBillCalculation()" style="height: 36px; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-calculator"></i>
            Tổng hợp Hóa đơn tháng
        </button>
    </div>
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
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600; color: var(--text-heading); display: flex; align-items: center; gap: 6px;">
                                    <?php echo htmlspecialchars($bill['bill_code']); ?>
                                    <?php if (!empty($bill['attachment_path'])): ?>
                                        <a href="<?php echo BASE_URL; ?>uploads/finance/<?php echo htmlspecialchars($bill['attachment_path']); ?>" target="_blank" style="color: var(--primary);" title="Xem tệp đính kèm">
                                            <i class="fa-solid fa-paperclip"></i>
                                        </a>
                                    <?php endif; ?>
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
                                        <button class="btn btn-outline btn-sm" onclick="App.finance.viewBillDetails('<?php echo htmlspecialchars($bill['bill_code']); ?>', '<?php echo htmlspecialchars($bill['stall_code']); ?>', '<?php echo htmlspecialchars($bill['trader_name']); ?>')" style="padding: 4px 6px;" title="Xem chi tiết hóa đơn">
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

<script>
$(document).ready(function() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const swalBg = isDark ? '#1a2332' : '#ffffff';
    const swalColor = isDark ? '#ffffff' : '#0f1623';

    // Hàm phụ định dạng tiền tệ cục bộ bằng JS thuần
    function formatCurrency(val) {
        return Number(val).toLocaleString('vi-VN');
    }

    window.App = window.App || {};
    window.App.finance = {
        // 1. Tính toán hóa đơn tự động hàng loạt
        simulateBillCalculation: function() {
            App.alert.loading('Đang tính toán hóa đơn...');
            
            // App.utils.ajaxRequest('POST', '<?php echo BASE_URL; ?>api/simulateBills', {}, (res) => { ... });
            $.ajax({
                type: 'POST',
                url: '<?php echo BASE_URL; ?>api/simulateBills',
                data: JSON.stringify({}),
                contentType: 'application/json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '<?php echo security::getToken(); ?>'
                },
                dataType: 'json',
                success: function(res) {
                    Swal.close();
                    if (res.status === 200) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Đã lập hóa đơn thành công!',
                            text: res.message,
                            confirmButtonColor: '#1ABB9C',
                            background: swalBg,
                            color: swalColor
                        }).then(() => { location.reload(); });
                    } else {
                        App.alert.error('Thất bại', res.message || 'Lỗi lập hóa đơn.');
                    }
                },
                error: function() {
                    Swal.close();
                    App.alert.error('Lỗi', 'Không thể kết nối đến máy chủ.');
                }
            });
        },

        // 2. Xem chi tiết hóa đơn và thanh toán
        viewBillDetails: function(billCode, stallCode, traderName) {
            App.alert.loading('Đang tải chi tiết hóa đơn...');
            
            // App.utils.ajaxRequest('GET', '<?php echo BASE_URL; ?>api/getBillDetails?code=' + billCode, {}, (res) => { ... });
            $.ajax({
                type: 'GET',
                url: '<?php echo BASE_URL; ?>api/getBillDetails?code=' + billCode,
                dataType: 'json',
                success: function(res) {
                    Swal.close();
                    if (res.error) {
                        App.alert.error('Lỗi', res.error);
                        return;
                    }

                    const d = res.data;
                    const totalText = formatCurrency(d.total_amount) + ' đ';

                    const html = `
                        <div style="text-align: left; font-size: 13px;">
                            <p><strong>Mã hóa đơn:</strong> ${billCode}</p>
                            <p><strong>Sạp thuê:</strong> ${stallCode} - <strong>Khách hàng:</strong> ${traderName}</p>
                            <table style="width:100%; border-collapse:collapse; margin-top:10px; font-size:12px;">
                                <thead>
                                    <tr style="background-color: var(--bg-surface-secondary); text-align: left;">
                                        <th style="padding:6px; border:1px solid var(--border-color);">Khoản mục</th>
                                        <th style="padding:6px; border:1px solid var(--border-color); text-align:right;">Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="padding:6px; border:1px solid var(--border-color);">Tiền thuê sạp</td>
                                        <td style="padding:6px; border:1px solid var(--border-color); text-align:right;">${formatCurrency(d.rent_amount)} đ</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:6px; border:1px solid var(--border-color);">Tiền điện (${d.electricity_usage} kWh)</td>
                                        <td style="padding:6px; border:1px solid var(--border-color); text-align:right;">${formatCurrency(d.electricity_amount)} đ</td>
                                    </tr>
                                    <tr>
                                        <td style="padding:6px; border:1px solid var(--border-color);">Tiền nước (${d.water_usage} m³)</td>
                                        <td style="padding:6px; border:1px solid var(--border-color); text-align:right;">${formatCurrency(d.water_amount)} đ</td>
                                    </tr>
                                    <tr style="font-weight:bold;">
                                        <td style="padding:6px; border:1px solid var(--border-color);">Tổng cộng</td>
                                        <td style="padding:6px; border:1px solid var(--border-color); text-align:right; color: var(--primary);">${totalText}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    `;

                    Swal.fire({
                        title: 'Chi tiết Hóa đơn',
                        html: html,
                        confirmButtonText: d.status === 'unpaid' ? 'Thanh toán hóa đơn này' : 'Đóng',
                        showCancelButton: d.status === 'unpaid',
                        cancelButtonText: 'Hủy',
                        confirmButtonColor: d.status === 'unpaid' ? '#1ABB9C' : '#626d7d',
                        cancelButtonColor: '#a0aec0',
                        background: swalBg,
                        color: swalColor
                    }).then((r) => {
                        if (r.isConfirmed && d.status === 'unpaid') {
                            App.alert.loading('Đang xử lý thanh toán...');
                            const fd = new FormData();
                            fd.append('bill_id', d.id);
                            fd.append('csrf_token', '<?php echo $_SESSION['csrf_token'] ?? ''; ?>');
                            
                            // App.utils.apiPost('<?php echo BASE_URL; ?>api/payBill', fd, { onSuccess: () => { location.reload(); } });
                            $.ajax({
                                type: "POST",
                                url: '<?php echo BASE_URL; ?>api/payBill',
                                data: fd,
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
                                            location.reload();
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
                        }
                    });
                },
                error: function() {
                    Swal.close();
                    App.alert.error('Lỗi', 'Không thể kết nối đến máy chủ.');
                }
            });
        }
    };
});
</script>



