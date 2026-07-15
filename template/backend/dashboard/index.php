<!-- ── HÀNG CARD THỐNG KÊ (3 CỘT) ── -->
<div class="row col-3" style="margin-bottom: 24px;">
    <!-- Card 1: Tổng số sạp -->
    <div class="card">
        <div class="stat">
            <div class="stat-icon blue" style="background-color: rgba(66, 133, 244, 0.1); color: #4285F4;">
                <i class="fa-solid fa-border-all" style="font-size: 18px;"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Tổng số Sạp Chợ</div>
                <div class="stat-value-row">
                    <span class="stat-value"><?php echo $stats['total_stalls']; ?></span>
                </div>
                <div class="stat-subtext">Phân bố tại 3 khu vực chợ</div>
            </div>
            <!-- Sparkline giả lập bằng thanh nhỏ của Gentelella -->
            <div class="stat-spark">
                <div class="bar" style="height:40%; background-color: #4285F4;"></div>
                <div class="bar" style="height:55%; background-color: #4285F4;"></div>
                <div class="bar" style="height:45%; background-color: #4285F4;"></div>
                <div class="bar" style="height:60%; background-color: #4285F4;"></div>
                <div class="bar" style="height:50%; background-color: #4285F4;"></div>
                <div class="bar" style="height:70%; background-color: #4285F4;"></div>
            </div>
        </div>
    </div>

    <!-- Card 2: Sạp đang thuê -->
    <div class="card">
        <div class="stat">
            <div class="stat-icon green" style="background-color: rgba(52, 168, 83, 0.1); color: #34A853;">
                <i class="fa-solid fa-store" style="font-size: 18px;"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Sạp Đang Thuê</div>
                <div class="stat-value-row">
                    <span class="stat-value"><?php echo $stats['rented_stalls']; ?></span>
                    <span class="stat-change up" style="color: #34A853;"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V3M3 6l3-3 3 3"/></svg><?php echo round(($stats['rented_stalls'] / $stats['total_stalls']) * 100); ?>%</span>
                </div>
                <div class="stat-subtext">Đang hoạt động hợp đồng</div>
            </div>
            <div class="stat-spark">
                <div class="bar" style="height:50%; background-color: #34A853;"></div>
                <div class="bar" style="height:60%; background-color: #34A853;"></div>
                <div class="bar" style="height:70%; background-color: #34A853;"></div>
                <div class="bar" style="height:65%; background-color: #34A853;"></div>
                <div class="bar" style="height:80%; background-color: #34A853;"></div>
                <div class="bar" style="height:90%; background-color: #34A853;"></div>
            </div>
        </div>
    </div>

    <!-- Card 3: Sạp trống -->
    <div class="card">
        <div class="stat">
            <div class="stat-icon yellow" style="background-color: rgba(251, 188, 4, 0.1); color: #FBBC04;">
                <i class="fa-solid fa-circle-plus" style="font-size: 18px;"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Sạp Còn Trống</div>
                <div class="stat-value-row">
                    <span class="stat-value"><?php echo $stats['empty_stalls']; ?></span>
                </div>
                <div class="stat-subtext">Sẵn sàng để ký hợp đồng mới</div>
            </div>
            <div class="stat-spark">
                <div class="bar" style="height:80%; background-color: #FBBC04;"></div>
                <div class="bar" style="height:70%; background-color: #FBBC04;"></div>
                <div class="bar" style="height:60%; background-color: #FBBC04;"></div>
                <div class="bar" style="height:50%; background-color: #FBBC04;"></div>
                <div class="bar" style="height:40%; background-color: #FBBC04;"></div>
                <div class="bar" style="height:30%; background-color: #FBBC04;"></div>
            </div>
        </div>
    </div>
</div>

<!-- ── HÀNG TIẾN TRÌNH & DOANH THU (3 CỘT TIẾP) ── -->
<div class="row col-3" style="margin-bottom: 24px;">
    <!-- Card Doanh thu -->
    <div class="card">
        <div class="stat">
            <div class="stat-icon teal" style="background-color: rgba(26, 187, 156, 0.1); color: #1ABB9C;">
                <i class="fa-solid fa-money-bill-wave" style="font-size: 18px;"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Doanh thu tháng này</div>
                <div class="stat-value-row">
                    <span class="stat-value" style="font-size: 20px;"><?php echo number_format($stats['revenue_this_month'], 0, ',', '.'); ?> đ</span>
                    <span class="stat-change up" style="color: #1ABB9C;"><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V3M3 6l3-3 3 3"/></svg>8.2%</span>
                </div>
                <div class="stat-subtext">Thu từ sạp & điện nước dịch vụ</div>
            </div>
        </div>
        <div style="padding:0 16px 12px">
            <div class="progress-thin"><div class="bar" style="width:82%; background-color: #1ABB9C;"></div></div>
        </div>
    </div>

    <!-- Sạp sửa chữa -->
    <div class="card">
        <div class="stat">
            <div class="stat-icon red" style="background-color: rgba(234, 67, 53, 0.1); color: #EA4335;">
                <i class="fa-solid fa-wrench" style="font-size: 18px;"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Sạp đang sửa chữa</div>
                <div class="stat-value-row">
                    <span class="stat-value"><?php echo $stats['repairing_stalls']; ?> sạp</span>
                </div>
                <div class="stat-subtext">Cần hoàn thiện trước kỳ sau</div>
            </div>
        </div>
        <div style="padding:0 16px 12px">
            <div class="progress-thin"><div class="bar" style="width:15%; background-color: #EA4335;"></div></div>
        </div>
    </div>

    <!-- Tổng tiểu thương -->
    <div class="card">
        <div class="stat">
            <div class="stat-icon purple" style="background-color: rgba(138, 45, 160, 0.1); color: #8a2da0;">
                <i class="fa-solid fa-users" style="font-size: 18px;"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Tổng số Tiểu thương</div>
                <div class="stat-value-row">
                    <span class="stat-value"><?php echo $stats['total_traders']; ?> hộ</span>
                </div>
                <div class="stat-subtext">Đang kinh doanh hoạt động</div>
            </div>
        </div>
        <div style="padding:0 16px 12px">
            <div class="progress-thin"><div class="bar" style="width:68%; background-color: #8a2da0;"></div></div>
        </div>
    </div>
</div>

<!-- ── HÀNG BIỂU ĐỒ DOANH THU & PHÂN BỔ (CỘT 8-4) ── -->
<div class="row col-8-4" style="margin-bottom: 24px;">
    <!-- Biểu đồ thu chi (Chart.js) -->
    <div class="card">
        <div class="chart-header">
            <div class="chart-header-left">
                <div class="card-title" style="font-size: 16px; font-weight: 600;">Biểu đồ Thu - Chi tài chính</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top: 4px;">Thống kê doanh thu và chi phí 6 tháng gần nhất (Triệu VNĐ)</div>
            </div>
        </div>
        <!-- Thẻ canvas chứa biểu đồ -->
        <div class="chart-area" style="padding: 16px; height: 300px;">
            <canvas id="revenueChart" style="width: 100%; height: 100%;"></canvas>
        </div>
    </div>

    <!-- Phân bổ trạng thái sạp (Doughnut Chart.js) -->
    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px;">
            <div class="card-title" style="font-size: 16px; font-weight: 600;">Tỉ lệ Trạng thái Sạp</div>
        </div>
        <div class="card-body" style="padding: 16px; display: flex; flex-direction: column; justify-content: space-between; height: 300px;">
            <div style="height: 180px; display: flex; align-items: center; justify-content: center;">
                <canvas id="stallsPieChart" 
                        data-rented="<?php echo $stats['rented_stalls']; ?>" 
                        data-empty="<?php echo $stats['empty_stalls']; ?>" 
                        data-repairing="<?php echo $stats['repairing_stalls']; ?>"
                        style="width: 100%; height: 100%;"></canvas>
            </div>
            <div style="display: flex; justify-content: space-around; text-align: center; font-size: 11px; margin-top: 10px; border-top: 1px solid var(--border-color); padding-top: 10px;">
                <div>
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; bg-color: #34A853; background-color: #34A853; margin-right: 4px;"></span>
                    <span>Đã thuê (<?php echo $stats['rented_stalls']; ?>)</span>
                </div>
                <div>
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; bg-color: #FBBC04; background-color: #FBBC04; margin-right: 4px;"></span>
                    <span>Trống (<?php echo $stats['empty_stalls']; ?>)</span>
                </div>
                <div>
                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; bg-color: #EA4335; background-color: #EA4335; margin-right: 4px;"></span>
                    <span>Sửa (<?php echo $stats['repairing_stalls']; ?>)</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── HÀNG BẢNG THÔNG TIN CẢNH BÁO (CỘT 12) ── -->
<div class="row" style="margin-bottom: 24px;">
    <div class="col-12" style="width: 100%;">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding: 16px;">
                <div>
                    <div class="card-title" style="font-size: 16px; font-weight: 600;">Hợp đồng sạp sắp hết hạn</div>
                    <div class="card-subtitle" style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">Cảnh báo các sạp cần gia hạn hợp đồng thuê trong tháng</div>
                </div>
                <a href="<?php echo BASE_URL; ?>admin/contracts" class="btn btn-outline btn-sm" style="text-decoration: none; font-size: 12px; padding: 6px 12px;">Xem Tất Cả →</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13.5px;">
                        <thead>
                            <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                                <th style="padding: 12px 16px;">Mã Sạp</th>
                                <th style="padding: 12px 16px;">Khu vực</th>
                                <th style="padding: 12px 16px;">Tiểu thương thuê</th>
                                <th style="padding: 12px 16px;">Ngày hết hạn</th>
                                <th style="padding: 12px 16px;">Số điện thoại</th>
                                <th style="padding: 12px 16px; text-align: right;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Row 1 -->
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600;">SẠP-A05</td>
                                <td style="padding: 14px 16px;"><span class="badge" style="background-color: rgba(66, 133, 244, 0.1); color: #4285F4; border-radius: 4px; padding: 4px 8px; font-size: 11px;">Khu A (Quần áo)</span></td>
                                <td style="padding: 14px 16px; font-weight: 600;">Nguyễn Thị Thu Hà</td>
                                <td style="padding: 14px 16px; color: #EA4335; font-weight: 600;">15/07/2026 (Còn 14 ngày)</td>
                                <td style="padding: 14px 16px; color: var(--text-muted)">0912.345.678</td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <button class="btn btn-outline btn-sm" style="font-size: 11.5px; padding: 4px 10px;">Gia hạn</button>
                                </td>
                            </tr>
                            <!-- Row 2 -->
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600;">SẠP-B12</td>
                                <td style="padding: 14px 16px;"><span class="badge" style="background-color: rgba(52, 168, 83, 0.1); color: #34A853; border-radius: 4px; padding: 4px 8px; font-size: 11px;">Khu B (Thực phẩm)</span></td>
                                <td style="padding: 14px 16px; font-weight: 600;">Trần Văn Hoàng</td>
                                <td style="padding: 14px 16px; color: #EA4335; font-weight: 600;">20/07/2026 (Còn 19 ngày)</td>
                                <td style="padding: 14px 16px; color: var(--text-muted)">0987.654.321</td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <button class="btn btn-outline btn-sm" style="font-size: 11.5px; padding: 4px 10px;">Gia hạn</button>
                                </td>
                            </tr>
                            <!-- Row 3 -->
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600;">SẠP-A02</td>
                                <td style="padding: 14px 16px;"><span class="badge" style="background-color: rgba(66, 133, 244, 0.1); color: #4285F4; border-radius: 4px; padding: 4px 8px; font-size: 11px;">Khu A (Quần áo)</span></td>
                                <td style="padding: 14px 16px; font-weight: 600;">Phạm Minh Tuấn</td>
                                <td style="padding: 14px 16px; color: #FBBC04; font-weight: 600;">05/08/2026 (Còn 35 ngày)</td>
                                <td style="padding: 14px 16px; color: var(--text-muted)">0905.112.233</td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <button class="btn btn-outline btn-sm" style="font-size: 11.5px; padding: 4px 10px;">Gia hạn</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Nạp thư viện Chart.js & Script vẽ biểu đồ -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    const canvasRevenue = document.getElementById('revenueChart');
    const canvasStalls = document.getElementById('stallsPieChart');
    if (!canvasRevenue || !canvasStalls) return;

    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

    // 1. Lấy dữ liệu doanh thu qua AJAX và vẽ biểu đồ cột
    fetch(window.BASE_URL + 'api/getRevenueData')
        .then(response => response.json())
        .then(data => {
            const revMillions = data.revenue.map(val => val / 1000000);
            const expMillions = data.expense.map(val => val / 1000000);
            const ctx = canvasRevenue.getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'Tổng thu',
                            data: revMillions,
                            backgroundColor: '#1ABB9C',
                            borderRadius: 4,
                            barPercentage: 0.5
                        },
                        {
                            label: 'Tổng chi',
                            data: expMillions,
                            backgroundColor: '#FBBC04',
                            borderRadius: 4,
                            barPercentage: 0.5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: isDark ? '#2a3649' : '#eceff1' },
                            ticks: { color: isDark ? '#a0aec0' : '#6b7280' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: isDark ? '#a0aec0' : '#6b7280' }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 15,
                                color: isDark ? '#e2e8f0' : '#111827',
                                font: { family: 'Inter', size: 12 }
                            }
                        }
                    }
                }
            });
        });

    // 2. Vẽ biểu đồ tròn phân bổ sạp chợ (đọc từ data-attributes)
    const rented = parseInt(canvasStalls.dataset.rented || 0, 10);
    const empty = parseInt(canvasStalls.dataset.empty || 0, 10);
    const repairing = parseInt(canvasStalls.dataset.repairing || 0, 10);

    new Chart(canvasStalls.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Đã thuê', 'Trống', 'Đang sửa'],
            datasets: [{
                data: [rented, empty, repairing],
                backgroundColor: ['#34A853', '#FBBC04', '#EA4335'],
                borderWidth: 2,
                borderColor: isDark ? '#1a2332' : '#ffffff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: { legend: { display: false } }
        }
    });
});
</script>



