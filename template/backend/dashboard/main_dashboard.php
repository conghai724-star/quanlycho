<!-- Trang Tổng Quan Hợp Nhất Hệ Thống Chợ -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #3a7bd5 0%, #3a6073 100%);
        --accent-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        --purple-gradient: linear-gradient(135deg, #6441a5 0%, #2a0845 100%);
        --orange-gradient: linear-gradient(135deg, #ff9966 0%, #ff5e62 100%);
        --shadow-premium: 0 10px 30px rgba(0,0,0,0.08);
        --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .dashboard-container {
        font-family: 'Outfit', sans-serif;
        color: #2c3e50;
        background-color: #f4f7f6;
        padding: 20px;
        min-height: 100vh;
    }

    .dashboard-header {
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .dashboard-header h1 {
        font-size: 28px;
        font-weight: 700;
        margin: 0;
        background: linear-gradient(to right, #2c3e50, #3498db);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    /* KPI Grid */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .kpi-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--shadow-premium);
        border: 1px solid rgba(0,0,0,0.02);
        display: flex;
        align-items: center;
        gap: 20px;
        transition: var(--transition-smooth);
        position: relative;
        overflow: hidden;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: var(--primary-gradient);
        opacity: 0;
        transition: var(--transition-smooth);
    }

    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.12);
    }

    .kpi-card:hover::before {
        opacity: 1;
    }

    .kpi-icon-wrapper {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        font-size: 24px;
        flex-shrink: 0;
    }

    .kpi-info h3 {
        margin: 0 0 6px 0;
        font-size: 14px;
        color: #95a5a6;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        font-weight: 500;
    }

    .kpi-info .value {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: #2c3e50;
    }

    /* Charts Section */
    .charts-row {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 25px;
        margin-bottom: 35px;
    }

    @media (max-width: 992px) {
        .charts-row {
            grid-template-columns: 1fr;
        }
    }

    .chart-container-card {
        background: #ffffff;
        border-radius: 20px;
        padding: 24px;
        box-shadow: var(--shadow-premium);
        border: 1px solid rgba(0,0,0,0.02);
    }

    .chart-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #34495e;
        display: flex;
        align-items: center;
        gap: 10px;
        border-bottom: 1px solid #f1f2f6;
        padding-bottom: 12px;
    }

    /* Market Grid */
    .section-title {
        font-size: 20px;
        font-weight: 600;
        margin-bottom: 20px;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .markets-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 24px;
    }

    .market-card {
        background: #ffffff;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: var(--shadow-premium);
        border: 1px solid rgba(0,0,0,0.02);
        transition: var(--transition-smooth);
        display: flex;
        flex-direction: column;
    }

    .market-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }

    .market-card-header {
        background: var(--primary-gradient);
        color: #ffffff;
        padding: 24px;
        position: relative;
    }

    .market-card-header h2 {
        margin: 0;
        font-size: 20px;
        font-weight: 600;
        line-height: 1.3;
    }

    .market-card-header .code {
        font-size: 11px;
        background: rgba(255,255,255,0.2);
        padding: 4px 8px;
        border-radius: 4px;
        text-transform: uppercase;
        position: absolute;
        top: 24px;
        right: 24px;
        font-weight: 600;
    }

    .market-card-body {
        padding: 24px;
        flex-grow: 1;
    }

    .market-stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px dashed #f1f2f6;
    }

    .market-stat-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .market-stat-item .label {
        color: #7f8c8d;
        font-size: 14px;
    }

    .market-stat-item .val {
        font-weight: 600;
        color: #2c3e50;
        font-size: 15px;
    }

    .market-card-footer {
        padding: 0 24px 24px 24px;
    }

    .btn-access {
        width: 100%;
        background: var(--primary-gradient);
        color: white;
        border: none;
        padding: 12px;
        border-radius: 12px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: var(--transition-smooth);
        font-size: 15px;
    }

    .btn-access:hover {
        background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
        transform: translateY(-2px);
    }
</style>

<div class="dashboard-container">
    <!-- Header -->
    <div class="dashboard-header">
        <div>
            <h1>Hệ Thống Quản Lý Chợ Smart - BQL</h1>
            <p style="color: #7f8c8d; margin: 4px 0 0 0; font-size: 14px;">Trang tổng quan hợp nhất số liệu thống kê toàn hệ thống</p>
        </div>
        <div style="display: flex; align-items: center; gap: 12px;">
            <?php if (marketService::isSuperAdmin()): ?>
            <a href="<?php echo BASE_URL; ?>system/market_add" style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; background: var(--accent-gradient); color: white; border-radius: 10px; text-decoration: none; font-size: 13px; font-weight: 600; box-shadow: 0 4px 12px rgba(17,153,142,0.3); transition: all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="fa-solid fa-plus"></i> Thêm Chợ
            </a>
            <a href="<?php echo BASE_URL; ?>system/markets" style="display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; background: #ffffff; color: #3a7bd5; border: 1.5px solid #3a7bd5; border-radius: 10px; text-decoration: none; font-size: 13px; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#3a7bd5';this.style.color='white'" onmouseout="this.style.background='#ffffff';this.style.color='#3a7bd5'">
                <i class="fa-solid fa-store"></i> Sửa / DS Chợ
            </a>
            <?php endif; ?>
            <div style="font-size: 14px; color: #7f8c8d; background: #ffffff; padding: 10px 16px; border-radius: 12px; box-shadow: var(--shadow-premium);">
                <i class="fa-regular fa-calendar-check" style="margin-right: 6px; color: #3a7bd5;"></i>
                Tháng hiện tại: <strong><?php echo date('m/Y'); ?></strong>
            </div>
        </div>
    </div>

    <!-- KPI Row -->
    <div class="kpi-grid">
        <!-- Chợ -->
        <div class="kpi-card">
            <div class="kpi-icon-wrapper" style="background: var(--primary-gradient);">
                <i class="fa-solid fa-store"></i>
            </div>
            <div class="kpi-info">
                <h3>Số lượng chợ</h3>
                <p class="value"><?php echo $stats['total_markets']; ?></p>
            </div>
        </div>

        <!-- Tổng số sạp -->
        <div class="kpi-card">
            <div class="kpi-icon-wrapper" style="background: var(--orange-gradient);">
                <i class="fa-solid fa-border-all"></i>
            </div>
            <div class="kpi-info">
                <h3>Tổng số Sạp Chợ</h3>
                <p class="value"><?php echo number_format($stats['total_stalls']); ?></p>
            </div>
        </div>

        <!-- Tỷ lệ lấp đầy -->
        <div class="kpi-card">
            <div class="kpi-icon-wrapper" style="background: var(--accent-gradient);">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <div class="kpi-info">
                <h3>Tỷ lệ lấp đầy</h3>
                <p class="value"><?php echo $stats['occupancy_rate']; ?>% <span style="font-size: 13px; font-weight: normal; color: #7f8c8d;">(<?php echo $stats['rented_stalls']; ?> đã thuê)</span></p>
            </div>
        </div>

        <!-- Doanh thu hợp nhất -->
        <div class="kpi-card">
            <div class="kpi-icon-wrapper" style="background: var(--purple-gradient);">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div class="kpi-info">
                <h3>Doanh thu tháng này</h3>
                <p class="value" style="color: #27ae60;"><?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?> đ</p>
            </div>
        </div>

        <!-- Tài khoản không hoạt động -->
        <a href="<?php echo BASE_URL; ?>system/users" style="text-decoration: none;">
        <div class="kpi-card" style="<?php echo ($stats['inactive_users'] > 0) ? 'border-left: 4px solid #e74c3c;' : ''; ?>">
            <div class="kpi-icon-wrapper" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                <i class="fa-solid fa-user-lock"></i>
            </div>
            <div class="kpi-info">
                <h3>TK không hoạt động</h3>
                <p class="value" style="color: <?php echo ($stats['inactive_users'] > 0) ? '#e74c3c' : '#27ae60'; ?>;">
                    <?php echo $stats['inactive_users']; ?>
                    <?php if ($stats['inactive_users'] > 0): ?>
                    <span style="font-size: 12px; font-weight: 500; color: #e74c3c; display: block; margin-top: 2px;">⚠ Cần xem xét</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
        </a>
    </div>

    <!-- Charts Row -->
    <div class="charts-row">
        <!-- Doanh thu từng chợ -->
        <div class="chart-container-card">
            <div class="chart-title">
                <i class="fa-solid fa-chart-bar" style="color: #3a7bd5;"></i>
                So sánh doanh thu thực tế giữa các Chợ (Tháng hiện tại)
            </div>
            <div style="height: 300px; position: relative;">
                <canvas id="revenueBarChart"></canvas>
            </div>
        </div>

        <!-- Phân bố lấp đầy -->
        <div class="chart-container-card">
            <div class="chart-title">
                <i class="fa-solid fa-building" style="color: #2ecc71;"></i>
                Tỷ lệ lấp đầy mặt bằng
            </div>
            <div style="height: 300px; position: relative; display: flex; justify-content: center; align-items: center;">
                <canvas id="occupancyPieChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Markets List Section -->
    <div class="section-title">
        <i class="fa-solid fa-list-check" style="color: #3a7bd5;"></i>
        Danh sách Chợ được quyền truy cập quản lý
    </div>

    <div class="markets-grid">
        <?php foreach ($markets as $m): ?>
            <?php 
                $marketOccupancy = $m['total_stalls'] > 0 ? round(($m['rented_stalls'] / $m['total_stalls']) * 100) : 0;
            ?>
            <div class="market-card">
                <div class="market-card-header">
                    <h2><?php echo htmlspecialchars($m['name']); ?></h2>
                    <span class="code"><?php echo htmlspecialchars($m['code']); ?></span>
                </div>
                <div class="market-card-body">
                    <div class="market-stat-item">
                        <span class="label">Tổng số sạp:</span>
                        <span class="val"><?php echo $m['total_stalls']; ?> sạp</span>
                    </div>
                    <div class="market-stat-item">
                        <span class="label">Sạp đã thuê:</span>
                        <span class="val" style="color: #27ae60;"><?php echo $m['rented_stalls']; ?> sạp (<?php echo $marketOccupancy; ?>%)</span>
                    </div>
                    <div class="market-stat-item">
                        <span class="label">Doanh thu tháng này:</span>
                        <span class="val" style="color: #2980b9;"><?php echo number_format($m['monthly_revenue'] ?? 0, 0, ',', '.'); ?> đ</span>
                    </div>
                </div>
                <div class="market-card-footer">
                    <button class="btn-access" data-id="<?php echo $m['id']; ?>">
                        <i class="fa-solid fa-arrow-right-to-bracket"></i>
                        Vào quản trị chợ
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    $(function() {
        // 1. Dữ liệu vẽ biểu đồ Doanh thu (Bar chart)
        var labels = [];
        var revenueData = [];
        var occupancyData = [];
        var totalStalls = 0;
        var rentedStalls = 0;

        <?php foreach ($markets as $m): ?>
            labels.push(<?php echo json_encode($m['name']); ?>);
            revenueData.push(<?php echo (float)($m['monthly_revenue'] ?? 0); ?>);
            occupancyData.push(<?php echo (int)$m['rented_stalls']; ?>);
            totalStalls += <?php echo (int)$m['total_stalls']; ?>;
            rentedStalls += <?php echo (int)$m['rented_stalls']; ?>;
        <?php endforeach; ?>

        if (typeof Chart !== 'undefined') {
            // Khởi tạo Bar Chart
            var ctxBar = document.getElementById('revenueBarChart').getContext('2d');
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Doanh thu thực tế (VNĐ)',
                        data: revenueData,
                        backgroundColor: [
                            'rgba(58, 123, 213, 0.7)',
                            'rgba(26, 187, 156, 0.7)',
                            'rgba(251, 188, 4, 0.7)',
                            'rgba(155, 89, 182, 0.7)'
                        ],
                        borderColor: [
                            '#3a7bd5',
                            '#1ABB9C',
                            '#FBBC04',
                            '#9b59b6'
                        ],
                        borderWidth: 1.5,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString() + ' đ';
                                }
                            }
                        }
                    }
                }
            });

            // Khởi tạo Pie Chart
            var ctxPie = document.getElementById('occupancyPieChart').getContext('2d');
            var emptyStalls = totalStalls - rentedStalls;
            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: ['Sạp đã thuê', 'Sạp còn trống'],
                    datasets: [{
                        data: [rentedStalls, emptyStalls],
                        backgroundColor: ['#2ecc71', '#bdc3c7'],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 15,
                                font: {
                                    family: "'Outfit', sans-serif",
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });
        }

        // 2. Click nút "Vào quản trị chợ"
        $('.btn-access').on('click', function() {
            var marketId = $(this).data('id');
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fa-solid fa-circle-notch fa-spin"></i> Đang tải...');
            
            $.ajax({
                url: '<?php echo BASE_URL; ?>api/changeMarketScope',
                type: 'GET',
                data: { id: marketId },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 200) {
                        window.location.href = '<?php echo BASE_URL; ?>admin/dashboard';
                    } else {
                        Swal.fire('Lỗi', res.message, 'error');
                        btn.prop('disabled', false).html('<i class="fa-solid fa-arrow-right-to-bracket"></i> Vào quản trị chợ');
                    }
                },
                error: function() {
                    Swal.fire('Lỗi', 'Không thể kết nối với máy chủ.', 'error');
                    btn.prop('disabled', false).html('<i class="fa-solid fa-arrow-right-to-bracket"></i> Vào quản trị chợ');
                }
            });
        });
    });
</script>
