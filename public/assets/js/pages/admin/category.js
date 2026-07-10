window.App = window.App || {};

App.category = (function () {
    let currentTab = 'area';

    function switchTab(tabName) {
        currentTab = tabName;
        
        // Cập nhật trạng thái active của radio buttons
        const radio = document.querySelector(`input[name="category-tab"][value="${tabName}"]`);
        if (radio) radio.checked = true;

        // Ẩn tất cả các sections
        document.querySelectorAll('.category-section').forEach(sec => {
            sec.style.display = 'none';
        });

        // Hiển thị section tương ứng
        const activeSec = document.getElementById(`cat-${tabName}`);
        if (activeSec) activeSec.style.display = 'block';
    }

    function getModalFields(type, data = null) {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        let html = '';
        let title = data ? 'Cập Nhật Danh Mục' : 'Thêm Danh Mục Mới';

        if (type === 'area') {
            title = data ? 'Sửa Khu Vực Chợ' : 'Thêm Khu Vực Chợ Mới';
            html = `
                <div style="text-align: left; font-size: 13px;">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" style="font-weight: 500;">Tên khu vực <span style="color: var(--red)">*</span></label>
                        <input type="text" id="swal-area_name" class="form-control" placeholder="Ví dụ: Khu A" value="${data ? data.area_name : ''}" required>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 500;">Dãy (Block)</label>
                            <input type="text" id="swal-block" class="form-control" placeholder="Ví dụ: Dãy A1" value="${data && data.block ? data.block : ''}">
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 500;">Lô số (Lot)</label>
                            <input type="text" id="swal-lot" class="form-control" placeholder="Ví dụ: Lô 01-10" value="${data && data.lot ? data.lot : ''}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 500;">Mô tả chi tiết</label>
                        <textarea id="swal-description" class="form-control" rows="3" placeholder="Nhập mô tả khu vực...">${data && data.description ? data.description : ''}</textarea>
                    </div>
                </div>
            `;
        } else {
            let codeLabel = '';
            let nameLabel = '';
            let codePlaceholder = '';
            let namePlaceholder = '';
            let codeVal = '';
            let nameVal = '';
            let descVal = data && data.description ? data.description : '';

            if (type === 'stall_type') {
                title = data ? 'Sửa Loại Sạp Chợ' : 'Thêm Loại Sạp Chợ Mới';
                codeLabel = 'Mã loại sạp';
                nameLabel = 'Tên loại sạp';
                codePlaceholder = 'Ví dụ: kiot, quay_hang';
                namePlaceholder = 'Ví dụ: Kiot, Quầy hàng';
                codeVal = data ? data.type_code : '';
                nameVal = data ? data.type_name : '';
            } else if (type === 'business_line') {
                title = data ? 'Sửa Ngành Hàng' : 'Thêm Ngành Hàng Mới';
                codeLabel = 'Mã ngành hàng';
                nameLabel = 'Tên ngành hàng';
                codePlaceholder = 'Ví dụ: thoi_trang, hai_san';
                namePlaceholder = 'Ví dụ: Thời trang, Hải sản';
                codeVal = data ? data.line_code : '';
                nameVal = data ? data.line_name : '';
            } else if (type === 'document_type') {
                title = data ? 'Sửa Loại Giấy Tờ' : 'Thêm Loại Giấy Tờ Mới';
                codeLabel = 'Mã loại giấy tờ';
                nameLabel = 'Tên loại giấy tờ';
                codePlaceholder = 'Ví dụ: attp, suc_khoe';
                namePlaceholder = 'Ví dụ: Giấy chứng nhận ATTP';
                codeVal = data ? data.type_code : '';
                nameVal = data ? data.type_name : '';
            }

            html = `
                <div style="text-align: left; font-size: 13px;">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" style="font-weight: 500;">${codeLabel} <span style="color: var(--red)">*</span></label>
                        <input type="text" id="swal-code" class="form-control" placeholder="${codePlaceholder}" value="${codeVal}" ${data ? 'readonly style="background-color: var(--bg-surface-secondary);"' : ''} required>
                        <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px; display: block;">Mã viết liền không dấu, viết thường, không khoảng trắng.</small>
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label class="form-label" style="font-weight: 500;">${nameLabel} <span style="color: var(--red)">*</span></label>
                        <input type="text" id="swal-name" class="form-control" placeholder="${namePlaceholder}" value="${nameVal}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 500;">Mô tả chi tiết</label>
                        <textarea id="swal-description" class="form-control" rows="3" placeholder="Nhập mô tả...">${descVal}</textarea>
                    </div>
                </div>
            `;
        }

        return { title, html };
    }

    function openAddModal() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const { title, html } = getModalFields(currentTab);

        Swal.fire({
            title: title,
            html: html,
            showCancelButton: true,
            confirmButtonText: 'Lưu lại',
            cancelButtonText: 'Hủy bỏ',
            confirmButtonColor: '#1ABB9C',
            cancelButtonColor: '#a0aec0',
            background: isDark ? '#1a2332' : '#ffffff',
            color: isDark ? '#ffffff' : '#0f1623',
            preConfirm: () => {
                const fd = new FormData();
                fd.append('type', currentTab);
                fd.append('csrf_token', window.CSRF_TOKEN);

                if (currentTab === 'area') {
                    const areaName = document.getElementById('swal-area_name').value.trim();
                    if (!areaName) {
                        Swal.showValidationMessage('Tên khu vực không được để trống.');
                        return false;
                    }
                    fd.append('area_name', areaName);
                    fd.append('block', document.getElementById('swal-block').value.trim());
                    fd.append('lot', document.getElementById('swal-lot').value.trim());
                    fd.append('description', document.getElementById('swal-description').value.trim());
                } else {
                    const code = document.getElementById('swal-code').value.trim();
                    const name = document.getElementById('swal-name').value.trim();
                    if (!code || !name) {
                        Swal.showValidationMessage('Vui lòng nhập đầy đủ thông tin bắt buộc.');
                        return false;
                    }
                    if (currentTab === 'stall_type') {
                        fd.append('type_code', code);
                        fd.append('type_name', name);
                    } else if (currentTab === 'business_line') {
                        fd.append('line_code', code);
                        fd.append('line_name', name);
                    } else if (currentTab === 'document_type') {
                        fd.append('type_code', code);
                        fd.append('type_name', name);
                    }
                    fd.append('description', document.getElementById('swal-description').value.trim());
                }
                return fd;
            }
        }).then(result => {
            if (result.isConfirmed && result.value) {
                App.utils.apiPost(window.BASE_URL + 'api/addCategory', result.value, {
                    onSuccess: () => location.reload()
                });
            }
        });
    }

    function openEditModal(type, id) {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        
        App.alert.loading('Đang tải thông tin danh mục...');
        fetch(`${window.BASE_URL}api/getCategoryDetail?type=${type}&id=${id}`)
            .then(res => res.json())
            .then(resData => {
                Swal.close();
                if (resData.status !== 200) {
                    App.alert.error('Lỗi', resData.message || 'Không thể tải thông tin.');
                    return;
                }

                const { title, html } = getModalFields(type, resData.data);

                Swal.fire({
                    title: title,
                    html: html,
                    showCancelButton: true,
                    confirmButtonText: 'Cập nhật',
                    cancelButtonText: 'Hủy bỏ',
                    confirmButtonColor: '#1ABB9C',
                    cancelButtonColor: '#a0aec0',
                    background: isDark ? '#1a2332' : '#ffffff',
                    color: isDark ? '#ffffff' : '#0f1623',
                    preConfirm: () => {
                        const fd = new FormData();
                        fd.append('id', id);
                        fd.append('type', type);
                        fd.append('csrf_token', window.CSRF_TOKEN);

                        if (type === 'area') {
                            const areaName = document.getElementById('swal-area_name').value.trim();
                            if (!areaName) {
                                Swal.showValidationMessage('Tên khu vực không được để trống.');
                                return false;
                            }
                            fd.append('area_name', areaName);
                            fd.append('block', document.getElementById('swal-block').value.trim());
                            fd.append('lot', document.getElementById('swal-lot').value.trim());
                            fd.append('description', document.getElementById('swal-description').value.trim());
                        } else {
                            const code = document.getElementById('swal-code').value.trim();
                            const name = document.getElementById('swal-name').value.trim();
                            if (!code || !name) {
                                Swal.showValidationMessage('Vui lòng nhập đầy đủ thông tin bắt buộc.');
                                return false;
                            }
                            if (type === 'stall_type') {
                                fd.append('type_code', code);
                                fd.append('type_name', name);
                            } else if (type === 'business_line') {
                                fd.append('line_code', code);
                                fd.append('line_name', name);
                            } else if (type === 'document_type') {
                                fd.append('type_code', code);
                                fd.append('type_name', name);
                            }
                            fd.append('description', document.getElementById('swal-description').value.trim());
                        }
                        return fd;
                    }
                }).then(result => {
                    if (result.isConfirmed && result.value) {
                        App.utils.apiPost(window.BASE_URL + 'api/editCategory', result.value, {
                            onSuccess: () => location.reload()
                        });
                    }
                });
            })
            .catch(() => App.alert.connectionError());
    }

    function deleteItem(type, id, name) {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

        Swal.fire({
            title: 'Xác nhận xóa?',
            text: `Bạn có chắc chắn muốn xóa danh mục "${name}"? Thao tác này không thể hoàn tác.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Đồng ý xóa',
            cancelButtonText: 'Hủy bỏ',
            confirmButtonColor: '#EA4335',
            cancelButtonColor: '#a0aec0',
            background: isDark ? '#1a2332' : '#ffffff',
            color: isDark ? '#ffffff' : '#0f1623'
        }).then(result => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('id', id);
                fd.append('type', type);
                fd.append('csrf_token', window.CSRF_TOKEN);

                App.utils.apiPost(window.BASE_URL + 'api/deleteCategory', fd, {
                    onSuccess: () => location.reload()
                });
            }
        });
    }

    return {
        switchTab,
        openAddModal,
        openEditModal,
        deleteItem
    };
})();
