<!-- Phân loại Tab -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <!-- Nút chuyển đổi Tab -->
    <div class="segmented" role="radiogroup" style="max-width: 650px;">
        <label><input type="radio" name="category-tab" value="area" checked onclick="App.category.switchTab('area')"><span>Phân khu / Khu vực</span></label>
        <label><input type="radio" name="category-tab" value="stall_type" onclick="App.category.switchTab('stall_type')"><span>Loại sạp chợ</span></label>
        <label><input type="radio" name="category-tab" value="business_line" onclick="App.category.switchTab('business_line')"><span>Ngành hàng kinh doanh</span></label>
        <label><input type="radio" name="category-tab" value="document_type" onclick="App.category.switchTab('document_type')"><span>Loại giấy tờ ATTP</span></label>
    </div>
</div>

<!-- TAB 1: PHÂN KHU / KHU VỰC -->
<div id="cat-area" class="card category-section">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Danh mục Phân khu / Khu vực chợ</div>
    </div>
    
    <!-- Form thêm mới dưới Header -->
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); background-color: var(--bg-surface-secondary);">
        <form id="form-add-area" action="<?php echo BASE_URL; ?>api/addCategory" method="POST" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            <input type="hidden" name="type" value="area">
            <?php csrf_field(); ?>
            <div class="form-group" style="margin: 0; min-width: 200px; flex: 1;">
                <label class="form-label" style="font-size: 12px; font-weight: 500; margin-bottom: 4px;">Tên khu vực <span style="color: var(--red)">*</span></label>
                <input type="text" name="area_name" class="form-control form-control-sm" placeholder="Ví dụ: Khu A" style="height: 34px; font-size: 13px;" required>
            </div>
            <div class="form-group" style="margin: 0; min-width: 120px; flex: 1;">
                <label class="form-label" style="font-size: 12px; font-weight: 500; margin-bottom: 4px;">Dãy (Block)</label>
                <input type="text" name="block" class="form-control form-control-sm" placeholder="Ví dụ: Dãy A1" style="height: 34px; font-size: 13px;">
            </div>
            <div class="form-group" style="margin: 0; min-width: 120px; flex: 1;">
                <label class="form-label" style="font-size: 12px; font-weight: 500; margin-bottom: 4px;">Lô (Lot)</label>
                <input type="text" name="lot" class="form-control form-control-sm" placeholder="Ví dụ: Lô 01-10" style="height: 34px; font-size: 13px;">
            </div>
            <div class="form-group" style="margin: 0; min-width: 250px; flex: 2;">
                <label class="form-label" style="font-size: 12px; font-weight: 500; margin-bottom: 4px;">Mô tả chi tiết</label>
                <input type="text" name="description" class="form-control form-control-sm" placeholder="Mô tả khu vực..." style="height: 34px; font-size: 13px;">
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="height: 34px; padding: 0 16px; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-plus"></i> Thêm mới
            </button>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px; width: 80px;">ID</th>
                        <th style="padding: 12px 16px; width: 220px;">Tên phân khu/Khu vực</th>
                        <th style="padding: 12px 16px; width: 140px;">Dãy (Block)</th>
                        <th style="padding: 12px 16px; width: 140px;">Lô (Lot)</th>
                        <th style="padding: 12px 16px;">Mô tả chi tiết</th>
                        <th style="padding: 12px 16px; text-align: right; width: 120px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($areas)): ?>
                        <?php foreach ($areas as $item): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 14px 16px; color: var(--text-muted); font-family: monospace;"><?php echo $item['id']; ?></td>
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);"><?php echo htmlspecialchars($item['area_name']); ?></td>
                                <td style="padding: 14px 16px;"><?php echo htmlspecialchars($item['block'] ?: '-'); ?></td>
                                <td style="padding: 14px 16px;"><?php echo htmlspecialchars($item['lot'] ?: '-'); ?></td>
                                <td style="padding: 14px 16px; color: var(--text-muted);"><?php echo htmlspecialchars($item['description'] ?: '-'); ?></td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <div style="display: flex; justify-content: flex-end; gap: 6px;">
                                        <button type="button" class="btn btn-outline btn-sm" onclick="App.category.openEditModal('area', <?php echo $item['id']; ?>)" style="padding: 4px 8px; font-size: 11px;" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline btn-sm text-danger btn-open-delete-cat" 
                                                data-cat-id="<?php echo $item['id']; ?>" 
                                                data-cat-name="<?php echo htmlspecialchars($item['area_name']); ?>" 
                                                data-url="<?php echo BASE_URL; ?>api/deleteCategory?type=area" 
                                                style="padding: 4px 8px; font-size: 11px;" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="padding: 30px; text-align: center; color: var(--text-muted);">Không có dữ liệu khu vực.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TAB 2: LOẠI SẠP CHỢ -->
<div id="cat-stall_type" class="card category-section" style="display: none;">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Danh mục Loại sạp chợ</div>
    </div>

    <!-- Form thêm mới dưới Header -->
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); background-color: var(--bg-surface-secondary);">
        <form id="form-add-stall_type" action="<?php echo BASE_URL; ?>api/addCategory" method="POST" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            <input type="hidden" name="type" value="stall_type">
            <?php csrf_field(); ?>
            <div class="form-group" style="margin: 0; min-width: 180px; flex: 1;">
                <label class="form-label" style="font-size: 12px; font-weight: 500; margin-bottom: 4px;">Mã loại sạp <span style="color: var(--red)">*</span></label>
                <input type="text" name="type_code" class="form-control form-control-sm" placeholder="Ví dụ: kiot" style="height: 34px; font-size: 13px;" required>
            </div>
            <div class="form-group" style="margin: 0; min-width: 180px; flex: 1;">
                <label class="form-label" style="font-size: 12px; font-weight: 500; margin-bottom: 4px;">Tên loại sạp <span style="color: var(--red)">*</span></label>
                <input type="text" name="type_name" class="form-control form-control-sm" placeholder="Ví dụ: Kiot" style="height: 34px; font-size: 13px;" required>
            </div>
            <div class="form-group" style="margin: 0; min-width: 250px; flex: 2;">
                <label class="form-label" style="font-size: 12px; font-weight: 500; margin-bottom: 4px;">Mô tả chi tiết</label>
                <input type="text" name="description" class="form-control form-control-sm" placeholder="Mô tả loại sạp..." style="height: 34px; font-size: 13px;">
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="height: 34px; padding: 0 16px; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-plus"></i> Thêm mới
            </button>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px; width: 80px;">ID</th>
                        <th style="padding: 12px 16px; width: 180px;">Mã loại sạp</th>
                        <th style="padding: 12px 16px; width: 220px;">Tên loại sạp</th>
                        <th style="padding: 12px 16px;">Mô tả chi tiết</th>
                        <th style="padding: 12px 16px; text-align: right; width: 120px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($stallTypes)): ?>
                        <?php foreach ($stallTypes as $item): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 14px 16px; color: var(--text-muted); font-family: monospace;"><?php echo $item['id']; ?></td>
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);"><?php echo htmlspecialchars($item['type_code']); ?></td>
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);"><?php echo htmlspecialchars($item['type_name']); ?></td>
                                <td style="padding: 14px 16px; color: var(--text-muted);"><?php echo htmlspecialchars($item['description'] ?: '-'); ?></td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <div style="display: flex; justify-content: flex-end; gap: 6px;">
                                        <button type="button" class="btn btn-outline btn-sm" onclick="App.category.openEditModal('stall_type', <?php echo $item['id']; ?>)" style="padding: 4px 8px; font-size: 11px;" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline btn-sm text-danger btn-open-delete-cat" 
                                                data-cat-id="<?php echo $item['id']; ?>" 
                                                data-cat-name="<?php echo htmlspecialchars($item['type_name']); ?>" 
                                                data-url="<?php echo BASE_URL; ?>api/deleteCategory?type=stall_type" 
                                                style="padding: 4px 8px; font-size: 11px;" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding: 30px; text-align: center; color: var(--text-muted);">Không có dữ liệu loại sạp.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TAB 3: NGÀNH HÀNG KINH DOANH -->
<div id="cat-business_line" class="card category-section" style="display: none;">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Danh mục Ngành hàng kinh doanh</div>
    </div>

    <!-- Form thêm mới dưới Header -->
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); background-color: var(--bg-surface-secondary);">
        <form id="form-add-business_line" action="<?php echo BASE_URL; ?>api/addCategory" method="POST" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            <input type="hidden" name="type" value="business_line">
            <?php csrf_field(); ?>
            <div class="form-group" style="margin: 0; min-width: 180px; flex: 1;">
                <label class="form-label" style="font-size: 12px; font-weight: 500; margin-bottom: 4px;">Mã ngành hàng <span style="color: var(--red)">*</span></label>
                <input type="text" name="line_code" class="form-control form-control-sm" placeholder="Ví dụ: thoi_trang" style="height: 34px; font-size: 13px;" required>
            </div>
            <div class="form-group" style="margin: 0; min-width: 180px; flex: 1;">
                <label class="form-label" style="font-size: 12px; font-weight: 500; margin-bottom: 4px;">Tên ngành hàng <span style="color: var(--red)">*</span></label>
                <input type="text" name="line_name" class="form-control form-control-sm" placeholder="Ví dụ: Thời trang" style="height: 34px; font-size: 13px;" required>
            </div>
            <div class="form-group" style="margin: 0; min-width: 250px; flex: 2;">
                <label class="form-label" style="font-size: 12px; font-weight: 500; margin-bottom: 4px;">Mô tả chi tiết</label>
                <input type="text" name="description" class="form-control form-control-sm" placeholder="Mô tả ngành hàng..." style="height: 34px; font-size: 13px;">
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="height: 34px; padding: 0 16px; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-plus"></i> Thêm mới
            </button>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px; width: 80px;">ID</th>
                        <th style="padding: 12px 16px; width: 180px;">Mã ngành hàng</th>
                        <th style="padding: 12px 16px; width: 220px;">Tên ngành hàng</th>
                        <th style="padding: 12px 16px;">Mô tả chi tiết</th>
                        <th style="padding: 12px 16px; text-align: right; width: 120px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($businessLines)): ?>
                        <?php foreach ($businessLines as $item): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 14px 16px; color: var(--text-muted); font-family: monospace;"><?php echo $item['id']; ?></td>
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);"><?php echo htmlspecialchars($item['line_code']); ?></td>
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);"><?php echo htmlspecialchars($item['line_name']); ?></td>
                                <td style="padding: 14px 16px; color: var(--text-muted);"><?php echo htmlspecialchars($item['description'] ?: '-'); ?></td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <div style="display: flex; justify-content: flex-end; gap: 6px;">
                                        <button type="button" class="btn btn-outline btn-sm" onclick="App.category.openEditModal('business_line', <?php echo $item['id']; ?>)" style="padding: 4px 8px; font-size: 11px;" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline btn-sm text-danger btn-open-delete-cat" 
                                                data-cat-id="<?php echo $item['id']; ?>" 
                                                data-cat-name="<?php echo htmlspecialchars($item['line_name']); ?>" 
                                                data-url="<?php echo BASE_URL; ?>api/deleteCategory?type=business_line" 
                                                style="padding: 4px 8px; font-size: 11px;" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding: 30px; text-align: center; color: var(--text-muted);">Không có dữ liệu ngành hàng.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- TAB 4: LOẠI GIẤY TỜ ATTP -->
<div id="cat-document_type" class="card category-section" style="display: none;">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Danh mục Loại giấy tờ vệ sinh ATTP</div>
    </div>

    <!-- Form thêm mới dưới Header -->
    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color); background-color: var(--bg-surface-secondary);">
        <form id="form-add-document_type" action="<?php echo BASE_URL; ?>api/addCategory" method="POST" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
            <input type="hidden" name="type" value="document_type">
            <?php csrf_field(); ?>
            <div class="form-group" style="margin: 0; min-width: 180px; flex: 1;">
                <label class="form-label" style="font-size: 12px; font-weight: 500; margin-bottom: 4px;">Mã loại giấy tờ <span style="color: var(--red)">*</span></label>
                <input type="text" name="type_code" class="form-control form-control-sm" placeholder="Ví dụ: attp" style="height: 34px; font-size: 13px;" required>
            </div>
            <div class="form-group" style="margin: 0; min-width: 180px; flex: 1;">
                <label class="form-label" style="font-size: 12px; font-weight: 500; margin-bottom: 4px;">Tên loại giấy tờ <span style="color: var(--red)">*</span></label>
                <input type="text" name="type_name" class="form-control form-control-sm" placeholder="Ví dụ: Giấy chứng nhận..." style="height: 34px; font-size: 13px;" required>
            </div>
            <div class="form-group" style="margin: 0; min-width: 250px; flex: 2;">
                <label class="form-label" style="font-size: 12px; font-weight: 500; margin-bottom: 4px;">Mô tả chi tiết</label>
                <input type="text" name="description" class="form-control form-control-sm" placeholder="Mô tả loại giấy tờ..." style="height: 34px; font-size: 13px;">
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="height: 34px; padding: 0 16px; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-plus"></i> Thêm mới
            </button>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                        <th style="padding: 12px 16px; width: 80px;">ID</th>
                        <th style="padding: 12px 16px; width: 180px;">Mã loại giấy tờ</th>
                        <th style="padding: 12px 16px; width: 220px;">Tên loại giấy tờ</th>
                        <th style="padding: 12px 16px;">Mô tả chi tiết</th>
                        <th style="padding: 12px 16px; text-align: right; width: 120px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($documentTypes)): ?>
                        <?php foreach ($documentTypes as $item): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 14px 16px; color: var(--text-muted); font-family: monospace;"><?php echo $item['id']; ?></td>
                                <td class="cell-mono" style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);"><?php echo htmlspecialchars($item['type_code']); ?></td>
                                <td style="padding: 14px 16px; font-weight: 600; color: var(--text-heading);"><?php echo htmlspecialchars($item['type_name']); ?></td>
                                <td style="padding: 14px 16px; color: var(--text-muted);"><?php echo htmlspecialchars($item['description'] ?: '-'); ?></td>
                                <td style="padding: 14px 16px; text-align: right;">
                                    <div style="display: flex; justify-content: flex-end; gap: 6px;">
                                        <button type="button" class="btn btn-outline btn-sm" onclick="App.category.openEditModal('document_type', <?php echo $item['id']; ?>)" style="padding: 4px 8px; font-size: 11px;" title="Sửa">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline btn-sm text-danger btn-open-delete-cat" 
                                                data-cat-id="<?php echo $item['id']; ?>" 
                                                data-cat-name="<?php echo htmlspecialchars($item['type_name']); ?>" 
                                                data-url="<?php echo BASE_URL; ?>api/deleteCategory?type=document_type" 
                                                style="padding: 4px 8px; font-size: 11px;" title="Xóa">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding: 30px; text-align: center; color: var(--text-muted);">Không có dữ liệu loại giấy tờ.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Nạp script bổ sung -->
<script>
$(document).ready(function() {
    const isDarkTheme = document.documentElement.getAttribute('data-theme') === 'dark';
    const csrfToken = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';

    // Đăng ký namespace App.category
    window.App = window.App || {};
    window.App.category = {
        switchTab: function(tabName) {
            $('input[name="category-tab"][value="' + tabName + '"]').prop('checked', true);
            $('.category-section').hide();
            $('#cat-' + tabName).show();
        },
        openEditModal: async function(type, id) {
            if (!type || !id) return;

            App.alert.loading('Đang tải thông tin danh mục...');
            
            try {
                const response = await fetch('<?php echo BASE_URL; ?>api/getCategoryDetail?type=' + type + '&id=' + id);
                const resData = await response.json();
                Swal.close();

                if (!resData || resData.status !== 200) {
                    App.alert.error('Lỗi', resData ? resData.message : 'Không thể tải thông tin.');
                    return;
                }

                const data = resData.data;
                const { title, html } = getModalFields(type, data);

                const result = await Swal.fire({
                    title: title,
                    html: html,
                    showCancelButton: true,
                    confirmButtonText: 'Cập nhật',
                    cancelButtonText: 'Hủy bỏ',
                    confirmButtonColor: '#1ABB9C',
                    cancelButtonColor: '#a0aec0',
                    background: isDarkTheme ? '#1a2332' : '#ffffff',
                    color: isDarkTheme ? '#ffffff' : '#0f1623',
                    preConfirm: () => {
                        const fd = new FormData();
                        fd.append('id', id);
                        fd.append('type', type);
                        fd.append('csrf_token', csrfToken);

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
                });

                if (result.isConfirmed && result.value) {
                    App.alert.loading('Đang lưu thay đổi...');
                    // App.utils.apiPost('<?php echo BASE_URL; ?>api/editCategory', result.value, { onSuccess: () => { location.reload(); } });
                    $.ajax({
                        type: "POST",
                        url: '<?php echo BASE_URL; ?>api/editCategory',
                        data: result.value,
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
            } catch (error) {
                Swal.close();
                App.alert.error('Lỗi', error.message || 'Lỗi kết nối mạng.');
            }
        }
    };

    function getModalFields(type, data = null) {
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
                        <input type="text" id="swal-code" class="form-control" placeholder="${codePlaceholder}" value="${codeVal}" readonly style="background-color: var(--bg-surface-secondary);" required>
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

    // Đăng ký tự động thêm mới bằng hàm dùng chung handleFormSubmit
    // ['area', 'stall_type', 'business_line', 'document_type'].forEach(function(type) { App.utils.handleFormSubmit('form-add-' + type, '<?php echo BASE_URL; ?>admin/categories'); });
    ['area', 'stall_type', 'business_line', 'document_type'].forEach(function(type) {
        $('#form-add-' + type).on('submit', function(e) {
            e.preventDefault();
            var form = this;
            var $form = $(this);
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            Swal.fire({
                title: 'Đang lưu danh mục...',
                allowOutsideClick: false,
                background: swalBg,
                color: swalColor,
                didOpen: function() { Swal.showLoading(); }
            });

            App.utils.saveFormDraft('form-add-' + type);

            $.ajax({
                type: "POST",
                url: $form.attr('action'),
                data: new FormData(form),
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
                            App.utils.clearFormDraft('form-add-' + type);
                            window.location.href = '<?php echo BASE_URL; ?>admin/categories';
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
    });

    // Đăng ký tự động xóa bằng hàm dùng chung initDelete
    // App.utils.initDelete({ btnClass: 'btn-open-delete-cat', idAttr: 'catId', nameAttr: 'catName', label: 'danh mục', onSuccess: () => location.reload() });
    $(document).on('click', '.btn-open-delete-cat', function(e) {
        e.preventDefault();
        var btn = this;
        var id = $(btn).data('cat-id');
        var name = $(btn).data('cat-name') || '';
        var csrf = $('input[name="csrf_token"]').val() || '';

        Swal.fire({
            title: 'Xác nhận xóa',
            text: 'Bạn có chắc chắn muốn xóa danh mục "' + name + '" không? Hành động này không thể hoàn tác.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Hủy bỏ',
            confirmButtonColor: '#d63939',
            cancelButtonColor: '#626d7d',
            background: swalBg,
            color: swalColor
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Đang xử lý...',
                    allowOutsideClick: false,
                    background: swalBg,
                    color: swalColor,
                    didOpen: () => { Swal.showLoading(); }
                });
                
                $.ajax({
                    type: 'POST',
                    url: $(btn).data('url'),
                    data: { id: id, csrf_token: csrf },
                    dataType: 'json',
                    success: function(data) {
                        Swal.close();
                        if (data.status === 200) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Thành công',
                                text: data.message,
                                timer: 1500,
                                background: swalBg,
                                color: swalColor
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Thất bại', text: data.message, background: swalBg, color: swalColor });
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire({ icon: 'error', title: 'Lỗi', text: 'Có lỗi xảy ra khi kết nối máy chủ.', background: swalBg, color: swalColor });
                    }
                });
            }
        });
    });
});
</script>

