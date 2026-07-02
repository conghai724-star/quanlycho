/**
 * App Custom JavaScript
 * Chứa toàn bộ các hàm JS dùng chung cho backend PHP của dự án Quản Lý Chợ.
 * Gom các hàm page-specific vào namespaces để tránh ô nhiễm môi trường global.
 */

window.App = {
    // 1. Module Thống kê (Dashboard)
    dashboard: {
        initCharts() {
            const canvasRevenue = document.getElementById('revenueChart');
            const canvasStalls = document.getElementById('stallsPieChart');
            if (!canvasRevenue || !canvasStalls) return;

            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

            // 1.1. Lấy dữ liệu doanh thu qua AJAX
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

            // 1.2. Vẽ biểu đồ tròn phân bổ sạp chợ (đọc từ data-attributes)
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
        }
    },

    // 2. Module Tiểu thương (Merchant)
    merchant: {
        viewLicense(name) {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            Swal.fire({
                title: 'Hồ sơ Giấy phép Kinh doanh',
                text: 'Đang hiển thị Giấy phép hộ kinh doanh của tiểu thương: ' + name,
                imageUrl: 'https://images.unsplash.com/photo-1554415707-6e8cfc93fe23?w=500&auto=format&fit=crop',
                imageWidth: 400,
                imageHeight: 250,
                imageAlt: 'Giấy chứng nhận đăng ký hộ kinh doanh',
                confirmButtonText: 'Đóng',
                confirmButtonColor: '#1ABB9C',
                background: isDark ? '#1a2332' : '#ffffff',
                color: isDark ? '#ffffff' : '#0f1623'
            });
        },
        exportData(type) {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            Swal.fire({
                title: 'Đang trích xuất dữ liệu...',
                text: 'Hệ thống đang chuẩn bị xuất danh sách tiểu thương ra file ' + type.toUpperCase(),
                timer: 1500,
                timerProgressBar: true,
                background: isDark ? '#1a2332' : '#ffffff',
                color: isDark ? '#ffffff' : '#0f1623',
                didOpen: () => { Swal.showLoading(); }
            }).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Xuất file thành công!',
                    text: 'File ' + type.toUpperCase() + ' đã được tải về máy của bạn.',
                    confirmButtonColor: '#1ABB9C',
                    background: isDark ? '#1a2332' : '#ffffff',
                    color: isDark ? '#ffffff' : '#0f1623'
                });
            });
        },
        confirmDelete(id, name) {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            Swal.fire({
                title: 'Xác nhận xóa?',
                text: "Bạn có chắc chắn muốn xóa tiểu thương '" + name + "' khỏi hệ thống? Hành động này không thể hoàn tác!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EA4335',
                cancelButtonColor: '#a0aec0',
                confirmButtonText: 'Đồng ý xóa',
                cancelButtonText: 'Hủy bỏ',
                background: isDark ? '#1a2332' : '#ffffff',
                color: isDark ? '#ffffff' : '#0f1623'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = window.BASE_URL + 'admin/trader_delete/' + id;
                }
            });
        }
    },

    // 3. Module Hợp đồng (Contract)
    contract: {
        viewAppendix(code) {
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
        },
        printContract(code) {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            Swal.fire({
                title: 'Tạo bản in Hợp đồng',
                text: 'Đang kết xuất PDF hợp đồng ' + code + ' theo mẫu chuẩn ban quản lý chợ...',
                timer: 1500,
                timerProgressBar: true,
                background: isDark ? '#1a2332' : '#ffffff',
                color: isDark ? '#ffffff' : '#0f1623',
                didOpen: () => { Swal.showLoading(); }
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
        },
        renewContract(code) {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            Swal.fire({
                title: 'Gia hạn Hợp đồng ' + code,
                text: 'Nhập thời gian gia hạn thêm (tháng):',
                input: 'number',
                inputValue: 12,
                inputAttributes: { min: 1, max: 60, step: 1 },
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
        },
        terminateContract(code) {
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
                        text: 'Hợp đồng ' + code + ' đã chuyển sang trạng thái thanh lý.',
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
    },

    // 4. Module Tài khoản người dùng (User)
    user: {
        switchTab(mode) {
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
        toggleLockUser(id, name) {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            Swal.fire({
                title: 'Khóa/Mở khóa tài khoản?',
                text: "Xác nhận thay đổi trạng thái hoạt động của tài khoản '" + name + "'?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Đồng ý',
                cancelButtonText: 'Hủy bỏ',
                confirmButtonColor: '#EA4335',
                background: isDark ? '#1a2332' : '#ffffff',
                color: isDark ? '#ffffff' : '#0f1623'
            }).then((result) => {
                if (result.isConfirmed) {
                    const statusCol = document.getElementById('status-col-' + id);
                    if (statusCol.innerHTML.includes('Hoạt động')) {
                        statusCol.innerHTML = '<span class="status status-red">Bị khóa</span>';
                        Swal.fire({
                            icon: 'success',
                            title: 'Đã khóa tài khoản!',
                            confirmButtonColor: '#1ABB9C',
                            background: isDark ? '#1a2332' : '#ffffff',
                            color: isDark ? '#ffffff' : '#0f1623'
                        });
                    } else {
                        statusCol.innerHTML = '<span class="status status-green">Hoạt động</span>';
                        Swal.fire({
                            icon: 'success',
                            title: 'Đã kích hoạt lại tài khoản!',
                            confirmButtonColor: '#1ABB9C',
                            background: isDark ? '#1a2332' : '#ffffff',
                            color: isDark ? '#ffffff' : '#0f1623'
                        });
                    }
                }
            });
        }
    },

    // 5. Module Sạp Chợ (Stall)
    stall: {
        switchView(mode) {
            const table = document.getElementById('view-table');
            const map = document.getElementById('view-map');
            if (!table || !map) return;

            if (mode === 'table') {
                table.style.display = 'block';
                map.style.display = 'none';
            } else {
                table.style.display = 'none';
                map.style.display = 'block';
            }
        },
        clickStall(code, status, traderName, line) {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

            if (status === 'empty') {
                Swal.fire({
                    title: 'Quản lý ' + code,
                    text: 'Sạp này hiện đang trống. Bạn có muốn gán sạp này cho tiểu thương kinh doanh?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa-solid fa-user-plus me-1"></i> Gán tiểu thương',
                    cancelButtonText: 'Đóng',
                    confirmButtonColor: '#1ABB9C',
                    cancelButtonColor: '#a0aec0',
                    background: isDark ? '#1a2332' : '#ffffff',
                    color: isDark ? '#ffffff' : '#0f1623'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = window.BASE_URL + 'admin/contract_add';
                    }
                });
            } else if (status === 'rented') {
                Swal.fire({
                    title: code + ' - Đang kinh doanh',
                    html: `<div style="text-align: left; font-size: 13.5px;">
                            <p><strong>Tiểu thương:</strong> ${traderName}</p>
                            <p><strong>Ngành kinh doanh:</strong> ${line}</p>
                           </div>`,
                    icon: 'info',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonText: '<i class="fa-solid fa-right-left me-1"></i> Chuyển đổi sạp',
                    denyButtonText: 'Thanh lý hợp đồng',
                    cancelButtonText: 'Đóng',
                    confirmButtonColor: '#066fd1',
                    denyButtonColor: '#EA4335',
                    background: isDark ? '#1a2332' : '#ffffff',
                    color: isDark ? '#ffffff' : '#0f1623'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Chuyển đổi sạp ' + code,
                            text: 'Chọn sạp mới để chuyển tiểu thương ' + traderName + ' sang:',
                            input: 'select',
                            inputOptions: {
                                'SẠP-A02': 'SẠP-A02 (Khu A - Trống)',
                                'SẠP-B03': 'SẠP-B03 (Khu B - Trống)'
                            },
                            inputPlaceholder: '-- Chọn sạp trống --',
                            showCancelButton: true,
                            confirmButtonText: 'Xác nhận chuyển',
                            confirmButtonColor: '#1ABB9C',
                            background: isDark ? '#1a2332' : '#ffffff',
                            color: isDark ? '#ffffff' : '#0f1623'
                        }).then((swapRes) => {
                            if (swapRes.isConfirmed && swapRes.value) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Chuyển sạp thành công!',
                                    text: 'Tiểu thương ' + traderName + ' đã được chuyển sang ' + swapRes.value,
                                    confirmButtonColor: '#1ABB9C',
                                    background: isDark ? '#1a2332' : '#ffffff',
                                    color: isDark ? '#ffffff' : '#0f1623'
                                });
                            }
                        });
                    } else if (result.isDenied) {
                        Swal.fire({
                            title: 'Thanh lý hợp đồng?',
                            text: 'Xác nhận thanh lý hợp đồng thuê sạp ' + code + ' của tiểu thương ' + traderName + '?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#EA4335',
                            confirmButtonText: 'Đồng ý thanh lý',
                            background: isDark ? '#1a2332' : '#ffffff',
                            color: isDark ? '#ffffff' : '#0f1623'
                        }).then((termRes) => {
                            if (termRes.isConfirmed) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Đã thanh lý!',
                                    text: 'Hợp đồng sạp ' + code + ' đã chuyển sang trạng thái thanh lý.',
                                    confirmButtonColor: '#1ABB9C',
                                    background: isDark ? '#1a2332' : '#ffffff',
                                    color: isDark ? '#ffffff' : '#0f1623'
                                });
                            }
                        });
                    }
                });
            } else {
                Swal.fire({
                    title: code + ' - Đang bảo trì',
                    text: 'Sạp này đang bảo trì hệ thống hoặc cải tạo cơ sở vật chất.',
                    icon: 'warning',
                    confirmButtonText: 'Đóng',
                    confirmButtonColor: '#1ABB9C',
                    background: isDark ? '#1a2332' : '#ffffff',
                    color: isDark ? '#ffffff' : '#0f1623'
                });
            }
        }
    },

    // 6. Module An toàn thực phẩm (Food Safety)
    foodsafety: {
        switchTab(mode) {
            const docs = document.getElementById('fs-docs');
            const inspections = document.getElementById('fs-inspections');
            if (!docs || !inspections) return;

            if (mode === 'docs') {
                docs.style.display = 'block';
                inspections.style.display = 'none';
            } else {
                docs.style.display = 'none';
                inspections.style.display = 'block';
            }
        }
    },

    // 7. Module Tài chính (Finance)
    finance: {
        updateOldValues(stallCode) {
            const elElectric = document.getElementById('old_electric');
            const elWater = document.getElementById('old_water');
            if (!elElectric || !elWater) return;

            if (stallCode === 'SẠP-A01') {
                elElectric.value = 1690;
                elWater.value = 255;
            } else if (stallCode === 'SẠP-B01') {
                elElectric.value = 3450;
                elWater.value = 432;
            } else {
                elElectric.value = 0;
                elWater.value = 0;
            }
        },
        viewBillDetails(code, stall, name) {
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
        },
        simulateBillCalculation() {
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
    }
};

// Global DOM Events & Loading Bar Lifecycle
(function() {
    var loadingBar = document.getElementById('app-loading-bar');
    var loadingSpinner = document.getElementById('app-loading-spinner');

    // 1. Khi script này load (Footer) -> Đã tải xong DOM, set bar lên 80%
    if (loadingBar) {
        loadingBar.style.width = '80%';
    }

    // 2. Khi toàn bộ tài nguyên (CSS, Ảnh...) load xong -> Hoàn tất bar lên 100% và biến mất
    window.addEventListener('load', function() {
        if (loadingBar) {
            loadingBar.style.width = '100%';
            setTimeout(function() {
                loadingBar.style.opacity = '0';
                if (loadingSpinner) loadingSpinner.style.opacity = '0';
                setTimeout(function() {
                    if (loadingBar) loadingBar.remove();
                    if (loadingSpinner) loadingSpinner.remove();
                }, 300);
            }, 150);
        }
    }, { once: true });

    // 3. Khi bấm chuyển trang -> Kích hoạt hiệu ứng tải của trang mới
    document.addEventListener('click', function(e) {
        var anchor = e.target.closest('a[href]');
        if (!anchor) return;
        var href = anchor.getAttribute('href');

        // Bỏ qua các link ngoài, hash, target blank, v.v.
        if (!href || href.startsWith('#') || href.startsWith('javascript')
            || href.startsWith('http') && !href.startsWith(window.location.origin)
            || anchor.target === '_blank'
            || anchor.hasAttribute('download')) return;
        if (e.defaultPrevented || e.ctrlKey || e.shiftKey || e.metaKey || e.altKey) return;

        // Tạo lại loading bar nếu chưa có và chạy hiệu ứng
        if (!document.getElementById('app-loading-bar')) {
            var bar = document.createElement('div');
            var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            bar.id = 'app-loading-bar';
            bar.style.width = '15%';
            document.body.appendChild(bar);
            
            var spinner = document.createElement('div');
            spinner.id = 'app-loading-spinner';
            document.body.appendChild(spinner);

            // Chạy tiến trình ảo tăng dần lên 90%
            var width = 15;
            var interval = setInterval(function() {
                if (width < 90) {
                    width += (90 - width) * 0.15;
                    bar.style.width = width + '%';
                } else {
                    clearInterval(interval);
                }
            }, 250);
        }
    });


    // 4. Quản lý thông báo flash & biểu đồ
    document.addEventListener('DOMContentLoaded', function() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const body = document.body;
        const success = body.getAttribute('data-flash-success');
        const error = body.getAttribute('data-flash-error');

        if (success || error) {
            const toastConfig = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: isDark ? '#1a2332' : '#ffffff',
                color: isDark ? '#ffffff' : '#0f1623'
            });

            if (success) {
                toastConfig.fire({ icon: 'success', title: success });
            } else if (error) {
                toastConfig.fire({ icon: 'error', title: error });
            }
        }

        // Tự động khởi tạo Chart.js trên dashboard nếu có canvas
        if (typeof Chart !== 'undefined') {
            window.App.dashboard.initCharts();
        }
    });
})();

