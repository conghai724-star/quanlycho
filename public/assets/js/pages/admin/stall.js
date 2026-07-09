(function () {
    function init() {
        const redirectUrl = window.BASE_URL + 'admin/stalls';

        // 1. Thêm / Sửa sạp chợ
        App.utils.handleFormSubmit('form-add-stall',  redirectUrl);
        App.utils.handleFormSubmit('form-edit-stall', redirectUrl);

        // Kiểm tra trùng mã sạp thời gian thực (chỉ cho form thêm vì form sửa readonly)
        App.utils.initRealtimeUniqueCheck('stall_code', 'api/checkExists', {
            getParams: val => ({ type: 'stall_code', value: val }),
            message: 'Mã sạp này đã tồn tại trên hệ thống.'
        });

        // 2. Xóa sạp chợ
        App.utils.initDelete({
            btnClass:  'btn-open-delete-stall',
            idAttr:    'stallId',
            nameAttr:  'stallCode',
            label:     'sạp chợ'
        });

        // 3. Lọc danh sách sạp qua AJAX
        App.utils.initFilterFormAjax({
            buttonId:       'btn-filter-stalls',
            tbodyId:        'table-body-stalls',
            totalId:        'filter-total-stalls',
            apiUrl:         'api/filterStalls',
            pagePath:       'admin/stalls'
        });

        // 4. Gán sạp nhanh cho tiểu thương
        document.addEventListener('click', e => {
            const btn = e.target.closest('.btn-assign-stall-quick');
            if (!btn) return;
            e.preventDefault();

            const stallId = btn.dataset.stallId;
            const stallCode = btn.dataset.stallCode;
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

            // Gọi API lấy danh sách tiểu thương chưa thuê sạp
            App.alert.loading('Đang tải danh sách tiểu thương...');
            fetch(window.BASE_URL + 'api/getAvailableTraders')
                .then(res => res.json())
                .then(traders => {
                    Swal.close();
                    if (traders.error) {
                        App.alert.error('Lỗi', traders.error);
                        return;
                    }

                    if (traders.length === 0) {
                        App.alert.error('Thông báo', 'Hiện tại không có tiểu thương hoạt động nào chưa có sạp.');
                        return;
                    }

                    // Tạo danh sách chọn cho SweetAlert2
                    const inputOptions = {};
                    traders.forEach(t => {
                        inputOptions[t.id] = `${t.fullname} (${t.trader_code})`;
                    });

                    // Hiển thị hộp thoại chọn tiểu thương
                    Swal.fire({
                        title: `Gán sạp ${stallCode}`,
                        text: 'Chọn tiểu thương muốn gán cho sạp này:',
                        input: 'select',
                        inputOptions: inputOptions,
                        inputPlaceholder: '-- Chọn tiểu thương --',
                        showCancelButton: true,
                        confirmButtonText: 'Gán sạp',
                        cancelButtonText: 'Đóng',
                        confirmButtonColor: '#1ABB9C',
                        cancelButtonColor: '#a0aec0',
                        background: isDark ? '#1a2332' : '#ffffff',
                        color: isDark ? '#ffffff' : '#0f1623',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'Bạn cần chọn một tiểu thương!';
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed && result.value) {
                            const fd = new FormData();
                            fd.append('stall_id', stallId);
                            fd.append('trader_id', result.value);
                            fd.append('csrf_token', window.CSRF_TOKEN);

                            App.utils.apiPost(window.BASE_URL + 'api/assignStall', fd, {
                                onSuccess: () => location.reload()
                            });
                        }
                    });
                })
                .catch(() => App.alert.connectionError());
        });

        // 5. Chuyển đổi sạp cho tiểu thương
        document.addEventListener('click', e => {
            const btn = e.target.closest('.btn-transfer-stall-quick');
            if (!btn) return;
            e.preventDefault();

            const currentStallId = btn.dataset.stallId;
            const stallCode = btn.dataset.stallCode;
            const traderName = btn.dataset.traderName;
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

            // Gọi API lấy danh sách sạp khả dụng
            App.alert.loading('Đang tải danh sách sạp khả dụng...');
            fetch(window.BASE_URL + 'api/getAvailableStallsForTransfer?exclude_id=' + currentStallId)
                .then(res => res.json())
                .then(stalls => {
                    Swal.close();
                    if (stalls.error) {
                        App.alert.error('Lỗi', stalls.error);
                        return;
                    }

                    if (stalls.length === 0) {
                        App.alert.error('Thông báo', 'Hiện tại không còn sạp nào khả dụng để chuyển đổi.');
                        return;
                    }

                    // Tạo danh sách chọn sạp
                    const inputOptions = {};
                    stalls.forEach(s => {
                        if (s.trader_name) {
                            inputOptions[s.id] = `${s.stall_code} (${s.area_name}) - Đang thuê: ${s.trader_name}`;
                        } else {
                            inputOptions[s.id] = `${s.stall_code} (${s.area_name}) - ${s.status_name}`;
                        }
                    });

                    // Hiển thị hộp thoại chọn sạp chuyển đến
                    Swal.fire({
                        title: `Chuyển/Đổi sạp ${stallCode}`,
                        text: `Chọn sạp mới (trống hoặc đang được thuê bởi tiểu thương khác) để chuyển/đổi sạp cho tiểu thương "${traderName}":`,
                        input: 'select',
                        inputOptions: inputOptions,
                        inputPlaceholder: '-- Chọn sạp nhận chuyển đổi --',
                        showCancelButton: true,
                        confirmButtonText: 'Xác nhận chuyển đổi',
                        cancelButtonText: 'Đóng',
                        confirmButtonColor: '#066fd1',
                        cancelButtonColor: '#a0aec0',
                        background: isDark ? '#1a2332' : '#ffffff',
                        color: isDark ? '#ffffff' : '#0f1623',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'Bạn cần chọn một sạp trống!';
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed && result.value) {
                            const fd = new FormData();
                            fd.append('current_stall_id', currentStallId);
                            fd.append('new_stall_id', result.value);
                            fd.append('csrf_token', window.CSRF_TOKEN);

                            App.utils.apiPost(window.BASE_URL + 'api/transferStall', fd, {
                                onSuccess: () => location.reload()
                            });
                        }
                    });
                })
                .catch(() => App.alert.connectionError());
        });
    }

    // Thăm dò an toàn: đợi App và Swal sẵn sàng
    function safeInit() {
        if (typeof App === 'undefined' || typeof Swal === 'undefined') {
            setTimeout(safeInit, 50);
            return;
        }
        init();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', safeInit);
    } else {
        safeInit();
    }
})();
