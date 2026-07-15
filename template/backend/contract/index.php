<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <div style="display: flex; gap: 8px; align-items: center;">
        <form id="form-filter-contracts" action="<?php echo BASE_URL; ?>admin/contracts" method="GET" style="display: flex; gap: 8px; margin: 0;">
            <input type="text" name="q" value="<?php echo htmlspecialchars($search ?? ''); ?>" class="form-control" placeholder="Tìm số HĐ, tên HĐ, tiểu thương..." style="width: 250px; height: 36px; font-size: 13px;">
            <select name="status" class="form-control" style="width: 180px; height: 36px; font-size: 13px;">
                <option value="">Tất cả trạng thái</option>
                <?php if (!empty($statuses)): ?>
                    <?php foreach ($statuses as $st): ?>
                        <option value="<?php echo htmlspecialchars($st['code']); ?>" <?php echo (($status_filter ?? '') === $st['code']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($st['status_name']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <button type="button" id="btn-filter-contracts" class="btn btn-outline" style="height: 36px; padding: 0 16px;">Lọc</button>
            <?php if (!empty($search) || !empty($status_filter)): ?>
                <a href="<?php echo BASE_URL; ?>admin/contracts" class="btn btn-ghost" style="height: 36px; padding: 0 12px; display: inline-flex; align-items: center; text-decoration: none; color: var(--text-muted);">Xóa bộ lọc</a>
            <?php endif; ?>
        </form>
    </div>
    
    <a href="<?php echo BASE_URL; ?>admin/contract_add" class="btn btn-primary" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: white;">
        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 14px; height: 14px;"><path d="M8 2v12M2 8h12"/></svg>
        Lập Hợp đồng mới
    </a>
</div>

<?php if (session::get('success_message')): ?>
    <div style="background-color: rgba(46, 125, 50, 0.1); border: 1px solid rgba(46, 125, 50, 0.2); color: #2e7d32; padding: 12px 16px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-circle-check"></i>
        <span><?php echo session::flash('success_message'); ?></span>
    </div>
<?php endif; ?>

<?php if (session::get('error_message')): ?>
    <div style="background-color: rgba(211, 47, 47, 0.1); border: 1px solid rgba(211, 47, 47, 0.2); color: #d32f2f; padding: 12px 16px; border-radius: 6px; font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-circle-exclamation"></i>
        <span><?php echo session::flash('error_message'); ?></span>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Hồ sơ Hợp đồng thuê mặt bằng sạp (<span id="filter-total-contracts"><?php echo count($contracts); ?></span>)</div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px; width: 120px;">Số Hợp đồng</th>
                        <th style="padding: 12px 16px;">Tên hợp đồng</th>
                        <th style="padding: 12px 16px;">Tiểu thương</th>
                        <th style="padding: 12px 16px; width: 90px;">Mã sạp</th>
                        <th style="padding: 12px 16px; width: 100px;">Ngày ký</th>
                        <th style="padding: 12px 16px; width: 100px;">Hạn hợp đồng</th>
                        <th style="padding: 12px 16px; width: 110px; text-align: center;">Sắp hết hạn</th>
                        <th style="padding: 12px 16px; width: 120px;">Giá thuê/tháng</th>
                        <th style="padding: 12px 16px; width: 110px;">Đặt cọc</th>
                        <th style="padding: 12px 16px; width: 80px; text-align: center;">File HĐ</th>
                        <th style="padding: 12px 16px; width: 95px; text-align: center;">Phụ lục</th>
                        <th style="padding: 12px 16px; width: 110px;">Trạng thái</th>
                        <th style="padding: 12px 16px; text-align: right; width: 180px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody id="table-body-contracts">
                    <?php require DIR_TEMPLATE . '/backend/contract/table_rows.php'; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal xem và quản lý phụ lục hợp đồng -->
<div id="modal-appendices" class="custom-modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div class="card" style="width: 90%; max-width: 700px; margin: auto; max-height: 85vh; display: flex; flex-direction: column;">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
            <div class="card-title" style="font-size: 15px; font-weight: 600;">Danh sách phụ lục hợp đồng: <span id="modal-contract-number" style="color: var(--primary);"></span></div>
            <button onclick="App.contract.closeAppendicesModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>
        <div class="card-body" style="padding: 20px; overflow-y: auto; flex-grow: 1;">
            
            <!-- Danh sách phụ lục hiện có -->
            <div id="appendices-list-container" style="margin-bottom: 24px;">
                <h5 style="margin-bottom: 10px; font-weight: 600; font-size: 13px;">Phụ lục đã ký</h5>
                <div id="appendices-list" style="display: flex; flex-direction: column; gap: 8px;">
                    <!-- Sẽ được điền bằng AJAX JS -->
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 20px 0;">

            <!-- Form thêm phụ lục mới -->
            <form id="form-add-appendix" enctype="multipart/form-data">
                <input type="hidden" name="contract_id" id="appendix-contract-id">
                <h5 style="margin-bottom: 14px; font-weight: 600; font-size: 13px; color: var(--text-heading);">Thêm phụ lục hợp đồng mới</h5>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 11px; font-weight: 500;">Số phụ lục *</label>
                        <input type="text" name="appendix_number" class="form-control" placeholder="Ví dụ: PL-SA01-2026-02" required style="height: 34px; font-size: 12px;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 11px; font-weight: 500;">Tên phụ lục *</label>
                        <input type="text" name="name" class="form-control" placeholder="Ví dụ: Phụ lục đổi diện tích" required style="height: 34px; font-size: 12px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 11px; font-weight: 500;">Ngày ký *</label>
                        <input type="date" name="sign_date" class="form-control" required style="height: 34px; font-size: 12px;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-size: 11px; font-weight: 500;">Ngày hiệu lực *</label>
                        <input type="date" name="effect_date" class="form-control" required style="height: 34px; font-size: 12px;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label class="form-label" style="font-size: 11px; font-weight: 500;">Nội dung chi tiết phụ lục *</label>
                    <textarea name="content" class="form-control" rows="3" placeholder="Nhập các điều khoản bổ sung, thay đổi đơn giá, thời gian..." required style="font-size: 12px; resize: vertical;"></textarea>
                </div>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" style="font-size: 11px; font-weight: 500;">Tài liệu đính kèm (Ảnh hoặc PDF)</label>
                    <input type="file" name="appendix_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf" style="font-size: 12px; padding: 4px 10px; height: 32px;">
                </div>

                <button type="submit" class="btn btn-primary btn-sm" style="width: 100%; height: 36px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: 12px;">
                    <i class="fa-solid fa-paperclip"></i> Ký phụ lục hợp đồng
                </button>
            </form>
        </div>
    </div>
</div>

<!-- CSRF Token phục vụ AJAX -->
<?php csrf_field(); ?>

<!-- Nạp JS xử lý AJAX & Form hợp đồng -->
<script>
$(document).ready(function() {
    const isDarkTheme = document.documentElement.getAttribute('data-theme') === 'dark';
    const swalBg = isDarkTheme ? '#1a2332' : '#ffffff';
    const swalColor = isDarkTheme ? '#ffffff' : '#0f1623';
    const csrfToken = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';

    window.App = window.App || {};
    window.App.contract = {
        // 1. Gia hạn hợp đồng
        renewContract: async function(contractId, contractNumber, currentEndDate) {
            if (!contractId) return;

            const result = await Swal.fire({
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
                background: swalBg,
                color: swalColor,
                preConfirm: () => {
                    const val = document.getElementById('swal-new-end-date').value;
                    if (!val) {
                        Swal.showValidationMessage('Bạn cần chọn ngày hết hạn mới!');
                        return false;
                    }
                    return val;
                }
            });

            if (result.isConfirmed && result.value) {
                App.alert.loading('Đang xử lý gia hạn...');
                const fd = new FormData();
                fd.append('contract_id', contractId);
                fd.append('new_end_date', result.value);
                fd.append('csrf_token', csrfToken);

                // App.utils.apiPost('<?php echo BASE_URL; ?>api/renewContract', fd, { onSuccess: () => { location.reload(); } });
                $.ajax({
                    type: "POST",
                    url: '<?php echo BASE_URL; ?>api/renewContract',
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
        },

        // 2. Thanh lý hợp đồng
        liquidateContract: async function(contractId, contractNumber) {
            if (!contractId) return;

            const result = await Swal.fire({
                title: 'Thanh lý hợp đồng ' + contractNumber + '?',
                text: "Xác nhận thực hiện thanh lý hợp đồng thuê sạp này? Toàn bộ công nợ liên quan phải được tất toán.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff9800',
                cancelButtonColor: '#a0aec0',
                confirmButtonText: 'Đồng ý thanh lý',
                cancelButtonText: 'Hủy bỏ',
                background: swalBg,
                color: swalColor
            });

            if (result.isConfirmed) {
                App.alert.loading('Đang xử lý thanh lý...');
                const fd = new FormData();
                fd.append('contract_id', contractId);
                fd.append('csrf_token', csrfToken);

                // App.utils.apiPost('<?php echo BASE_URL; ?>api/liquidateContract', fd, { onSuccess: () => { location.reload(); } });
                $.ajax({
                    type: "POST",
                    url: '<?php echo BASE_URL; ?>api/liquidateContract',
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
        },

        // 3. Chấm dứt trước hạn
        terminateContract: async function(contractId, contractNumber) {
            if (!contractId) return;

            const result = await Swal.fire({
                title: 'Chấm dứt hợp đồng ' + contractNumber + '?',
                text: "Xác nhận chấm dứt hợp đồng thuê sạp trước thời hạn? Trạng thái sạp sẽ được chuyển về Trống.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EA4335',
                cancelButtonColor: '#a0aec0',
                confirmButtonText: 'Xác nhận chấm dứt',
                cancelButtonText: 'Hủy bỏ',
                background: swalBg,
                color: swalColor
            });

            if (result.isConfirmed) {
                App.alert.loading('Đang chấm dứt hợp đồng...');
                const fd = new FormData();
                fd.append('contract_id', contractId);
                fd.append('csrf_token', csrfToken);

                // App.utils.apiPost('<?php echo BASE_URL; ?>api/terminateContract', fd, { onSuccess: () => { location.reload(); } });
                $.ajax({
                    type: "POST",
                    url: '<?php echo BASE_URL; ?>api/terminateContract',
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
        },

        // 4. Xóa mềm hợp đồng
        deleteContract: async function(contractId, contractNumber) {
            if (!contractId) return;

            const result = await Swal.fire({
                title: 'Xóa hợp đồng ' + contractNumber + '?',
                text: "Hợp đồng sẽ được ẩn đi (xóa mềm). Nếu hợp đồng đang hoạt động, sạp cũng sẽ được giải phóng về trạng thái 'Trống'.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Xác nhận xóa',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#ff5252',
                cancelButtonColor: '#a0aec0',
                background: swalBg,
                color: swalColor
            });

            if (result.isConfirmed) {
                App.alert.loading('Đang xử lý xóa...');
                const fd = new FormData();
                fd.append('contract_id', contractId);
                fd.append('csrf_token', csrfToken);

                // App.utils.apiPost('<?php echo BASE_URL; ?>api/deleteContract', fd, { onSuccess: () => { location.reload(); } });
                $.ajax({
                    type: "POST",
                    url: '<?php echo BASE_URL; ?>api/deleteContract',
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
        },

        // 5. Hiển thị modal quản lý phụ lục hợp đồng
        viewAppendices: function(contractId, contractNumber) {
            if (!contractId) return;

            document.getElementById('modal-contract-number').textContent = contractNumber;
            document.getElementById('appendix-contract-id').value = contractId;
            
            loadAppendices(contractId);
            
            const modal = document.getElementById('modal-appendices');
            if (modal) modal.style.display = 'flex';
        },

        // 6. Đóng modal phụ lục
        closeAppendicesModal: function() {
            const modal = document.getElementById('modal-appendices');
            if (modal) modal.style.display = 'none';
            location.reload();
        }
    };

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
        return dateStr;
    }

    async function loadAppendices(contractId) {
        const container = document.getElementById('appendices-list');
        if (!container) return;

        container.innerHTML = '<div style="color: var(--text-muted); font-size: 12px; padding: 10px 0;">Đang tải danh sách phụ lục...</div>';

        try {
            const res = await fetch('<?php echo BASE_URL; ?>api/getContractAppendices?contract_id=' + contractId);
            const resData = await res.json();
            
            if (resData && resData.status === 200 && Array.isArray(resData.data)) {
                const list = resData.data;
                if (list.length === 0) {
                    container.innerHTML = '<div style="color: var(--text-muted); font-size: 12px; padding: 10px 0; font-style: italic;">Chưa có phụ lục hợp đồng nào.</div>';
                    return;
                }

                let html = '';
                list.forEach(app => {
                    let fileLink = '';
                    if (app.file) {
                        fileLink = `<a href="<?php echo BASE_URL; ?>uploads/contracts/appendices/${app.file}" target="_blank" style="color: var(--primary); font-size: 12px; display: inline-flex; align-items: center; gap: 4px; text-decoration: none; margin-top: 4px;" title="Tải file phụ lục">
                                        <i class="fa-solid fa-file-arrow-down"></i> Tải tài liệu đính kèm
                                    </a>`;
                    }
                    
                    html += `
                        <div style="background-color: var(--bg-surface-secondary); border: 1px solid var(--border-color); padding: 12px; border-radius: 6px; font-size: 12px; margin-bottom: 8px;">
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
                container.innerHTML = '<div style="color: var(--red); font-size: 12px; padding: 10px 0;">Không thể tải danh sách phụ lục.</div>';
            }
        } catch (error) {
            container.innerHTML = '<div style="color: var(--red); font-size: 12px; padding: 10px 0;">Lỗi kết nối mạng.</div>';
        }
    }

    // Submit form Phụ lục
    $('#form-add-appendix').on('submit', function(e) {
        e.preventDefault();
        
        App.alert.loading('Đang xử lý thêm phụ lục...');
        const fd = new FormData(this);
        fd.append('csrf_token', csrfToken);

        // App.utils.apiPost('<?php echo BASE_URL; ?>api/addContractAppendix', fd, { ... });
        $.ajax({
            type: "POST",
            url: '<?php echo BASE_URL; ?>api/addContractAppendix',
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
                        text: 'Đã thêm phụ lục hợp đồng thành công!',
                        timer: 1500,
                        showConfirmButton: false,
                        background: swalBg,
                        color: swalColor
                    }).then(function() {
                        const contractId = document.getElementById('appendix-contract-id').value;
                        loadAppendices(contractId);
                        document.getElementById('form-add-appendix').reset();
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
    });

    // Lọc danh sách hợp đồng qua AJAX
    // App.utils.initFilterFormAjax({ buttonId: 'btn-filter-contracts', tbodyId: 'table-body-contracts', totalId: 'filter-total-contracts', apiUrl: 'api/filterContracts', pagePath: 'admin/contracts' });
    (function() {
        var btn = $('#btn-filter-contracts');
        if (!btn.length) return;
        var form = btn.closest('form');
        var inputs = form.find('input[name], select[name]');
        var tbody = $('#table-body-contracts');
        var totalEl = $('#filter-total-contracts');

        function doFilter() {
            var params = {};
            inputs.each(function() {
                var value = $(this).val().trim();
                if (value !== '') {
                    params[$(this).attr('name')] = value;
                }
            });
            var query = $.param(params);
            if (tbody.length) {
                tbody.css('opacity', '0.5');
            }
            $.ajax({
                type: 'GET',
                url: '<?php echo BASE_URL; ?>api/filterContracts',
                data: params,
                dataType: 'json',
                success: function(data) {
                    if (tbody.length) {
                        tbody.html(data.html).css('opacity', '1');
                    }
                    if (totalEl.length && typeof data.total !== 'undefined') {
                        totalEl.text(data.total);
                    }
                    var newUrl = '<?php echo BASE_URL; ?>admin/contracts' + (query ? '?' + query : '');
                    window.history.pushState({ path: newUrl }, '', newUrl);
                },
                error: function() {
                    if (tbody.length) tbody.css('opacity', '1');
                }
            });
        }

        btn.on('click', doFilter);
        form.find('input[type="text"]').on('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                doFilter();
            }
        });
    })();

    // Đóng modal khi bấm ra ngoài
    $(window).on('click', function(e) {
        const modal = document.getElementById('modal-appendices');
        if (e.target === modal) {
            App.contract.closeAppendicesModal();
        }
    });
});
</script>

