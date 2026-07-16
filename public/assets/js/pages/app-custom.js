/**
 * App Custom JavaScript
 * Chứa toàn bộ các hàm JS dùng chung cho backend PHP của dự án Quản Lý Chợ.
 * Gom các hàm page-specific vào namespaces để tránh ô nhiễm môi trường global.
 */

window.App = Object.assign(window.App || {}, {
    // 0. Các hàm tiện ích dùng chung (Utilities)
    utils: {
        /**
         * Gọi AJAX nhẹ cho các page legacy đang dùng callback.
         * Hỗ trợ GET JSON và POST JSON, tự đính kèm CSRF token nếu có.
         */
        ajaxRequest(method, url, data, callback) {
            const opts = {
                method: method,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            };

            const fullUrl = window.BASE_URL + url;

            if (method === 'GET') {
                fetch(fullUrl, opts)
                    .then(res => res.json())
                    .then(callback)
                    .catch(error => callback({ status: 500, message: error.message }));
                return;
            }

            opts.headers['Content-Type'] = 'application/json';
            if (window.CSRF_TOKEN) {
                opts.headers['X-CSRF-TOKEN'] = window.CSRF_TOKEN;
            }
            opts.body = JSON.stringify(data ?? {});

            fetch(fullUrl, opts)
                .then(res => res.json())
                .then(callback)
                .catch(error => callback({ status: 500, message: error.message }));
        },

        /**
         * Gửi POST AJAX: loading → fetch → success callback / error alert
         * onSuccess: callback chạy sau khi API trả về status 200
         */
        apiPost(url, formData, { onSuccess } = {}) {
            App.alert.loading('Đang xử lý...', 'Vui lòng chờ.');
            fetch(url, { 
                method: 'POST', 
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(res => res.json())
                .then(data => {
                    Swal.close();
                    if (data.status === 200) {
                        App.alert.success(data.message).then(() => onSuccess?.());
                    } else {
                        App.alert.error('Lỗi', data.message);
                    }
                })
                .catch(() => App.alert.connectionError());
        },

        /**
         * Bắt submit form → validate HTML5 → gửi AJAX POST → redirect sau khi thành công
         * formId:      ID của <form>
         * redirectUrl: URL chuyển hướng sau khi thành công
         */
        handleFormSubmit(formId, redirectUrl) {
            const form = document.getElementById(formId);
            if (!form) return;

            // Khôi phục bản nháp lưu tạm trước đó nếu có (khi tải lại trang/quay lại)
            App.utils.restoreFormDraft(formId);

            form.addEventListener('submit', e => {
                e.preventDefault();
                if (!form.checkValidity()) { form.reportValidity(); return; }

                // Lưu nháp ngay trước khi gửi đề phòng sự cố
                App.utils.saveFormDraft(formId);

                App.utils.apiPost(form.action, new FormData(form), {
                    onSuccess: () => {
                        // Thành công -> Xóa bản nháp và chuyển hướng
                        App.utils.clearFormDraft(formId);
                        window.location.href = redirectUrl;
                    }
                });
            });
        },

        saveFormDraft(formId) {
            const form = document.getElementById(formId);
            if (!form) return;
            const formData = new FormData(form);
            const draft = {};
            formData.forEach((value, key) => {
                if (key !== 'csrf_token') {
                    draft[key] = value;
                }
            });
            sessionStorage.setItem('form_draft_' + formId, JSON.stringify(draft));
        },

        restoreFormDraft(formId) {
            const form = document.getElementById(formId);
            if (!form) return;
            const draftStr = sessionStorage.getItem('form_draft_' + formId);
            if (!draftStr) return;
            try {
                const draft = JSON.parse(draftStr);
                Object.keys(draft).forEach(key => {
                    const inputs = form.querySelectorAll(`[name="${key}"]`);
                    inputs.forEach(input => {
                        if (input.type === 'checkbox' || input.type === 'radio') {
                            input.checked = (input.value === draft[key]);
                        } else {
                            input.value = draft[key];
                        }
                    });
                });
            } catch (e) {
                console.error('Failed to restore form draft:', e);
            }
        },

        clearFormDraft(formId) {
            sessionStorage.removeItem('form_draft_' + formId);
        },

        /**
         * Kiểm tra trùng lặp thời gian thực (realtime check on blur)
         * inputId:   ID của ô nhập liệu (input)
         * apiUrl:    API để kiểm tra (ví dụ 'api/checkExists')
         * options.getParams: Hàm callback nhận giá trị input và trả về object chứa các tham số query
         * options.message:   Thông báo lỗi hiển thị dưới input
         */
        initRealtimeUniqueCheck(inputId, apiUrl, { getParams, message } = {}) {
            const input = document.getElementById(inputId);
            if (!input || input.readOnly) return;

            // Tạo thẻ hiển thị lỗi dưới input nếu chưa có
            let errorEl = input.parentNode.querySelector('.realtime-error-msg');
            if (!errorEl) {
                errorEl = document.createElement('small');
                errorEl.className = 'realtime-error-msg';
                errorEl.style.color = '#e74c3c';
                errorEl.style.fontSize = '11px';
                errorEl.style.marginTop = '4px';
                errorEl.style.display = 'none';
                input.parentNode.appendChild(errorEl);
            }

            input.addEventListener('blur', () => {
                const val = input.value.trim();
                if (!val) {
                    errorEl.style.display = 'none';
                    input.style.borderColor = '';
                    input.setCustomValidity('');
                    return;
                }

                const queryObj = getParams ? getParams(val) : { q: val };
                const params = new URLSearchParams(queryObj);
                
                fetch(window.BASE_URL + apiUrl + '?' + params.toString())
                    .then(res => res.json())
                    .then(data => {
                        if (data.exists) {
                            errorEl.textContent = message || 'Giá trị này đã tồn tại trên hệ thống.';
                            errorEl.style.display = 'block';
                            input.style.borderColor = '#e74c3c';
                            input.setCustomValidity(message || 'Giá trị đã tồn tại.');
                        } else {
                            errorEl.style.display = 'none';
                            input.style.borderColor = '';
                            input.setCustomValidity('');
                        }
                    })
                    .catch(err => console.error('[initRealtimeUniqueCheck] Error:', err));
            });

            // Khi người dùng gõ lại, tạm ẩn thông báo lỗi
            input.addEventListener('input', () => {
                errorEl.style.display = 'none';
                input.style.borderColor = '';
                input.setCustomValidity('');
            });
        },

        /**
         * Khởi tạo nút Xóa có confirm cho bất kỳ phân hệ nào
         * config.btnClass:    class CSS của nút xóa (vd: 'btn-open-delete-trader')
         * config.idAttr:      tên data attribute chứa ID (vd: 'traderId' → dataset.traderId)
         * config.nameAttr:    tên data attribute chứa tên hiển thị (vd: 'traderName')
         * config.label:       tên đối tượng để hiển thị trong confirm (vd: 'tiểu thương')
         * config.onSuccess:   'reload' | function — hành động sau khi xóa thành công
         */
        initDelete(config) {
            document.addEventListener('click', e => {
                const btn = e.target.closest('.' + config.btnClass);
                if (!btn) return;
                e.preventDefault();

                const id   = btn.dataset[config.idAttr];
                const name = btn.dataset[config.nameAttr] ?? '';
                const csrf = window.CSRF_TOKEN ?? '';

                App.alert.confirm(
                    'Xác nhận xóa',
                    `Bạn có chắc chắn muốn xóa ${config.label} "${name}" không? Hành động này không thể hoàn tác.`
                ).then(result => {
                    if (!result.isConfirmed) return;
                    const fd = new FormData();
                    fd.append('id', id);
                    fd.append('csrf_token', csrf);
                    App.utils.apiPost(btn.dataset.url, fd, {
                        onSuccess: () => typeof config.onSuccess === 'function'
                            ? config.onSuccess()
                            : location.reload()
                    });
                });
            });
        },

        initFilterForm(buttonId, redirectPath) {
            const btn = document.getElementById(buttonId);
            if (!btn) return;

            const form = btn.closest('form');
            if (!form) return;

            const inputs = form.querySelectorAll('input[name], select[name]');

            function doFilter() {
                const params = new URLSearchParams();
                inputs.forEach(input => {
                    const name = input.name;
                    const value = input.value.trim();
                    if (value !== '') {
                        params.set(name, value);
                    }
                });

                const query = params.toString();
                const url = window.BASE_URL + redirectPath + (query ? '?' + query : '');
                window.location.href = url;
            }

            btn.addEventListener('click', doFilter);

            form.querySelectorAll('input[type="text"]').forEach(input => {
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        doFilter();
                    }
                });
            });
        },

        initFilterFormAjax(config) {
            const btn = document.getElementById(config.buttonId);
            if (!btn) return;

            const form = btn.closest('form');
            if (!form) return;

            const inputs = form.querySelectorAll('input[name], select[name]');
            const tbody = document.getElementById(config.tbodyId);
            const totalEl = document.getElementById(config.totalId);
            const exportExcel = document.getElementById(config.exportExcelId);
            const exportPdf = document.getElementById(config.exportPdfId);

            function doFilter() {
                const params = new URLSearchParams();
                inputs.forEach(input => {
                    const name = input.name;
                    const value = input.value.trim();
                    if (value !== '') {
                        params.set(name, value);
                    }
                });

                const query = params.toString();

                if (tbody) {
                    tbody.style.opacity = '0.5';
                }

                // Gửi AJAX request
                fetch(window.BASE_URL + config.apiUrl + (query ? '?' + query : ''))
                    .then(response => response.json())
                    .then(data => {
                        if (tbody) {
                            tbody.innerHTML = data.html;
                            tbody.style.opacity = '1';
                        }
                        if (totalEl && typeof data.total !== 'undefined') {
                            totalEl.textContent = data.total;
                        }
                        
                        // Cập nhật link export file
                        if (exportExcel && typeof data.queryString !== 'undefined') {
                            exportExcel.href = window.BASE_URL + config.exportExcelPath + '?' + data.queryString;
                        }
                        if (exportPdf && typeof data.queryString !== 'undefined') {
                            exportPdf.href = window.BASE_URL + config.exportPdfPath + '?' + data.queryString;
                        }

                        // Cập nhật URL trình duyệt
                        const newUrl = window.BASE_URL + config.pagePath + (query ? '?' + query : '');
                        window.history.pushState({ path: newUrl }, '', newUrl);
                    })
                    .catch(err => {
                        console.error('Lỗi lọc dữ liệu qua AJAX:', err);
                        if (tbody) tbody.style.opacity = '1';
                    });
            }

            btn.addEventListener('click', doFilter);

            form.querySelectorAll('input[type="text"]').forEach(input => {
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        doFilter();
                    }
                });
            });
        }
    },

    // 1. Module Thống kê (Dashboard)
    dashboard: {
        initCharts() {
             const canvasRevenue = document.getElementById('revenueChart');
            const canvasStalls = document.getElementById('stallsPieChart');
            if (!canvasRevenue || !canvasStalls) return;

            // Nếu đã khởi tạo rồi (bằng inline script ở trang view) thì bỏ qua
            if (Chart.getChart && (Chart.getChart(canvasRevenue) || Chart.getChart(canvasStalls))) {
                return;
            }

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

    // 2. Module Tiểu thương (Trader)
    trader: {
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
                    App.utils.ajaxRequest('POST', 'system/user_toggle_status/' + id, {}, (res) => {
                        if (res.success) {
                            const statusCol = document.getElementById('status-col-' + id);
                            if (res.new_status === 1) {
                                statusCol.innerHTML = '<span class="status status-green">Hoạt động</span>';
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Đã kích hoạt lại tài khoản!',
                                    confirmButtonColor: '#1ABB9C',
                                    background: isDark ? '#1a2332' : '#ffffff',
                                    color: isDark ? '#ffffff' : '#0f1623'
                                });
                            } else {
                                statusCol.innerHTML = '<span class="status status-red">Bị khóa</span>';
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Đã khóa tài khoản!',
                                    confirmButtonColor: '#1ABB9C',
                                    background: isDark ? '#1a2332' : '#ffffff',
                                    color: isDark ? '#ffffff' : '#0f1623'
                                });
                            }
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Thao tác thất bại!',
                                text: res.message || 'Có lỗi xảy ra.',
                                confirmButtonColor: '#EA4335',
                                background: isDark ? '#1a2332' : '#ffffff',
                                color: isDark ? '#ffffff' : '#0f1623'
                            });
                        }
                    });
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
    },

    // 8. Module Tiện ích Thông báo & Xác nhận dùng chung (Alert & Notification Utilities)
    alert: {
        // Tự động lấy cấu hình màu nền & chữ theo theme
        _theme() {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            return {
                background: isDark ? '#1a2332' : '#ffffff',
                color: isDark ? '#ffffff' : '#0f1623'
            };
        },
        // Thông báo thành công (tự đóng sau timer ms)
        success(title, text = '', timer = 1500) {
            return Swal.fire({
                icon: 'success',
                title: title,
                text: text,
                timer: timer,
                showConfirmButton: false,
                ...this._theme()
            });
        },
        // Thông báo lỗi
        error(title, text = '') {
            return Swal.fire({
                icon: 'error',
                title: title,
                text: text,
                confirmButtonColor: '#d63939',
                ...this._theme()
            });
        },
        // Thông báo thông tin
        info(title, text = '') {
            return Swal.fire({
                icon: 'info',
                title: title,
                text: text,
                confirmButtonColor: '#1abb9c',
                ...this._theme()
            });
        },
        // Cảnh báo nhẹ
        warning(title, text = '') {
            return Swal.fire({
                icon: 'warning',
                title: title,
                text: text,
                confirmButtonColor: '#f59f00',
                ...this._theme()
            });
        },
        // Hộp thoại xác nhận (trả về Promise)
        confirm(title, text, confirmText = 'Đồng ý', cancelText = 'Hủy bỏ', confirmColor = '#d63939') {
            return Swal.fire({
                icon: 'warning',
                title: title,
                text: text,
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
                confirmButtonColor: confirmColor,
                cancelButtonColor: '#626d7d',
                ...this._theme()
            });
        },
        // Thông báo lỗi mất kết nối máy chủ/lỗi đường truyền toàn cục
        connectionError() {
            return this.error('Lỗi kết nối', 'Không thể kết nối đến máy chủ. Vui lòng thử lại sau.');
        },
        // Màn hình loading (phải gọi kết quả trả về .close() hoặc Swal.close() để ẩn)
        loading(title, text = 'Vui lòng đợi giây lát...') {
            return Swal.fire({
                title: title,
                text: text,
                allowOutsideClick: false,
                showConfirmButton: false,
                ...this._theme(),
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        }
    }
});

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
