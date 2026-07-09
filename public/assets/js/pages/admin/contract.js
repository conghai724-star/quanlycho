(function () {
    function init() {
        const redirectUrl = window.BASE_URL + 'admin/contracts';

        // 1. Thêm hợp đồng mới
        App.utils.handleFormSubmit('form-add-contract', redirectUrl);

        // Kiểm tra trùng số hợp đồng thời gian thực
        App.utils.initRealtimeUniqueCheck('contract_number', 'api/checkExists', {
            getParams: val => ({ type: 'contract_number', value: val }),
            message: 'Số hợp đồng này đã tồn tại trên hệ thống.'
        });

        // 2. Tính tiền cọc tự động khi chọn sạp
        const stallSelect = document.getElementById('stall_id');
        if (stallSelect) {
            stallSelect.addEventListener('change', function() {
                const selectedOpt = this.options[this.selectedIndex];
                if (selectedOpt && selectedOpt.value) {
                    const price = parseFloat(selectedOpt.dataset.price) || 0;
                    document.getElementById('rental_price_display').value = price.toLocaleString('vi-VN') + ' đ';
                    document.getElementById('deposit').value = price * 2;
                } else {
                    document.getElementById('rental_price_display').value = '0';
                    document.getElementById('deposit').value = '';
                }
            });
        }

        // 3. Đăng ký submit cho form Phụ lục
        const formAddAppendix = document.getElementById('form-add-appendix');
        if (formAddAppendix) {
            formAddAppendix.addEventListener('submit', function(e) {
                e.preventDefault();
                const fd = new FormData(this);
                fd.append('csrf_token', window.CSRF_TOKEN);

                App.alert.loading('Đang xử lý...');
                App.utils.apiPost(window.BASE_URL + 'api/addContractAppendix', fd, {
                    onSuccess: (res) => {
                        App.alert.success('Thành công', 'Đã thêm phụ lục hợp đồng thành công!');
                        // Load lại danh sách phụ lục trong modal
                        const contractId = document.getElementById('appendix-contract-id').value;
                        loadAppendices(contractId);
                        // Reset form
                        formAddAppendix.reset();
                    }
                });
            });
        }

        // 4. Lọc danh sách hợp đồng qua AJAX
        App.utils.initFilterFormAjax({
            buttonId:       'btn-filter-contracts',
            tbodyId:        'table-body-contracts',
            totalId:        'filter-total-contracts',
            apiUrl:         'api/filterContracts',
            pagePath:       'admin/contracts'
        });
    }

    // 4. Các hàm thao tác hợp đồng (gắn vào window)
    window.renewContract = function(contractId, contractNumber, currentEndDate) {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        Swal.fire({
            title: 'Gia hạn hợp đồng ' + contractNumber,
            html: `<div style="text-align: left;">
                     <p style="margin-bottom: 8px;">Hạn hiện tại: <strong>${currentEndDate}</strong></p>
                     <label style="font-weight: 500; font-size: 13px;">Chọn ngày hết hạn mới:</label>
                     <input type="date" id="swal-new-end-date" class="form-control" style="margin-top: 6px;">
                   </div>`,
            showCancelButton: true,
            confirmButtonText: 'Xác nhận gia hạn',
            cancelButtonText: 'Đóng',
            confirmButtonColor: '#1ABB9C',
            cancelButtonColor: '#a0aec0',
            background: isDark ? '#1a2332' : '#ffffff',
            color: isDark ? '#ffffff' : '#0f1623',
            preConfirm: () => {
                const newDate = document.getElementById('swal-new-end-date').value;
                if (!newDate) {
                    Swal.showValidationMessage('Bạn phải chọn ngày hết hạn mới!');
                }
                return newDate;
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const fd = new FormData();
                fd.append('contract_id', contractId);
                fd.append('new_end_date', result.value);
                fd.append('csrf_token', window.CSRF_TOKEN);

                App.utils.apiPost(window.BASE_URL + 'api/renewContract', fd, {
                    onSuccess: () => location.reload()
                });
            }
        });
    };

    window.liquidateContract = function(contractId, contractNumber) {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        Swal.fire({
            title: 'Thanh lý hợp đồng ' + contractNumber + '?',
            text: "Hành động này sẽ cập nhật trạng thái hợp đồng thành 'Thanh lý' và sạp thuê sẽ được chuyển về trạng thái 'Trống'.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xác nhận thanh lý',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#ff9800',
            cancelButtonColor: '#a0aec0',
            background: isDark ? '#1a2332' : '#ffffff',
            color: isDark ? '#ffffff' : '#0f1623'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('contract_id', contractId);
                fd.append('csrf_token', window.CSRF_TOKEN);

                App.utils.apiPost(window.BASE_URL + 'api/liquidateContract', fd, {
                    onSuccess: () => location.reload()
                });
            }
        });
    };

    window.terminateContract = function(contractId, contractNumber) {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        Swal.fire({
            title: 'Chấm dứt hợp đồng ' + contractNumber + ' trước hạn?',
            text: "Trạng thái hợp đồng sẽ chuyển thành 'Chấm dứt trước hạn' và sạp thuê sẽ trở về trạng thái 'Trống'.",
            icon: 'error',
            showCancelButton: true,
            confirmButtonText: 'Xác nhận chấm dứt',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#d32f2f',
            cancelButtonColor: '#a0aec0',
            background: isDark ? '#1a2332' : '#ffffff',
            color: isDark ? '#ffffff' : '#0f1623'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('contract_id', contractId);
                fd.append('csrf_token', window.CSRF_TOKEN);

                App.utils.apiPost(window.BASE_URL + 'api/terminateContract', fd, {
                    onSuccess: () => location.reload()
                });
            }
        });
    };

    window.deleteContract = function(contractId, contractNumber) {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        Swal.fire({
            title: 'Xóa hợp đồng ' + contractNumber + '?',
            text: "Hợp đồng sẽ được ẩn đi (xóa mềm). Nếu hợp đồng đang hoạt động, sạp cũng sẽ được giải phóng về trạng thái 'Trống'.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xác nhận xóa',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#ff5252',
            cancelButtonColor: '#a0aec0',
            background: isDark ? '#1a2332' : '#ffffff',
            color: isDark ? '#ffffff' : '#0f1623'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('contract_id', contractId);
                fd.append('csrf_token', window.CSRF_TOKEN);

                App.utils.apiPost(window.BASE_URL + 'api/deleteContract', fd, {
                    onSuccess: () => location.reload()
                });
            }
        });
    };

    // 5. Quản lý phụ lục (Modal)
    window.viewAppendices = function(contractId, contractNumber) {
        document.getElementById('modal-contract-number').textContent = contractNumber;
        document.getElementById('appendix-contract-id').value = contractId;
        
        loadAppendices(contractId);
        
        const modal = document.getElementById('modal-appendices');
        modal.style.display = 'flex';
    };

    window.closeAppendicesModal = function() {
        const modal = document.getElementById('modal-appendices');
        modal.style.display = 'none';
        // Reload page on modal close to refresh the appendix counts
        location.reload();
    };

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('modal-appendices');
        if (event.target === modal) {
            modal.style.display = 'none';
            location.reload();
        }
    };

    function loadAppendices(contractId) {
        const container = document.getElementById('appendices-list');
        container.innerHTML = '<div style="color: var(--text-muted); font-size: 12px; padding: 10px 0;">Đang tải danh sách phụ lục...</div>';

        fetch(window.BASE_URL + 'api/getContractAppendices?contract_id=' + contractId)
            .then(res => res.json())
            .then(res => {
                if (res.status === 200 && Array.isArray(res.data)) {
                    if (res.data.length === 0) {
                        container.innerHTML = '<div style="color: var(--text-muted); font-size: 12px; padding: 10px 0; font-style: italic;">Chưa có phụ lục hợp đồng nào.</div>';
                        return;
                    }

                    let html = '';
                    res.data.forEach(app => {
                        let fileLink = '';
                        if (app.file) {
                            fileLink = `<a href="${window.BASE_URL}uploads/contracts/appendices/${app.file}" target="_blank" style="color: var(--primary); font-size: 12px; display: inline-flex; align-items: center; gap: 4px; text-decoration: none; margin-top: 4px;" title="Tải file phụ lục">
                                            <i class="fa-solid fa-file-arrow-down"></i> Tải tài liệu đính kèm
                                        </a>`;
                        }
                        
                        html += `
                            <div style="background-color: var(--bg-surface-secondary); border: 1px solid var(--border-color); padding: 12px; border-radius: 6px; font-size: 12px;">
                                <div style="display: flex; justify-content: space-between; font-weight: 600; color: var(--text-heading); margin-bottom: 4px;">
                                    <span>${app.name} (${app.appendix_number})</span>
                                    <span style="color: var(--text-muted); font-weight: normal; font-size: 11px;">Ký: ${formatDate(app.sign_date)}</span>
                                </div>
                                <div style="color: var(--text-heading); margin-bottom: 6px;">
                                    Hiệu lực từ ngày: <strong>${formatDate(app.effect_date)}</strong>
                                </div>
                                <div style="color: var(--text-muted); background: var(--bg-surface); padding: 8px; border-radius: 4px; border: 1px solid var(--border-color-light); white-space: pre-wrap;">${app.content}</div>
                                ${fileLink}
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                } else {
                    container.innerHTML = `<div style="color: var(--red); font-size: 12px; padding: 10px 0;">Lỗi: ${res.message || 'Không thể tải dữ liệu'}</div>`;
                }
            })
            .catch(() => {
                container.innerHTML = '<div style="color: var(--red); font-size: 12px; padding: 10px 0;">Không thể kết nối mạng.</div>';
            });
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
        return dateStr;
    }

    // Initialize scripts
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
