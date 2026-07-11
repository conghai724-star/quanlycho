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
    <div class="card-body p-0">
        <form id="form-add-area" action="<?php echo BASE_URL; ?>api/addCategory" method="POST">
            <input type="hidden" name="type" value="area">
            <?php csrf_field(); ?>
            <div class="table-responsive">
                <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                            <th style="padding: 12px 16px; width: 80px;">ID</th>
                            <th style="padding: 12px 16px; width: 220px;">Tên phân khu/Khu vực <span style="color: var(--red)">*</span></th>
                            <th style="padding: 12px 16px; width: 140px;">Dãy (Block)</th>
                            <th style="padding: 12px 16px; width: 140px;">Lô (Lot)</th>
                            <th style="padding: 12px 16px;">Mô tả chi tiết</th>
                            <th style="padding: 12px 16px; text-align: right; width: 120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Inline Form Row for Add -->
                        <tr style="background-color: var(--bg-surface-secondary); border-bottom: 2px solid var(--border-color);">
                            <td style="padding: 10px 16px; color: var(--text-muted); font-style: italic; font-weight: 500;">Mới</td>
                            <td style="padding: 10px 16px;">
                                <input type="text" name="area_name" class="form-control form-control-sm" placeholder="Tên khu vực..." style="height: 30px; font-size: 13px;" required>
                            </td>
                            <td style="padding: 10px 16px;">
                                <input type="text" name="block" class="form-control form-control-sm" placeholder="Dãy (Block)" style="height: 30px; font-size: 13px;">
                            </td>
                            <td style="padding: 10px 16px;">
                                <input type="text" name="lot" class="form-control form-control-sm" placeholder="Lô (Lot)" style="height: 30px; font-size: 13px;">
                            </td>
                            <td style="padding: 10px 16px;">
                                <input type="text" name="description" class="form-control form-control-sm" placeholder="Mô tả ngắn..." style="height: 30px; font-size: 13px;">
                            </td>
                            <td style="padding: 10px 16px; text-align: right;">
                                <button type="submit" class="btn btn-primary btn-sm" style="height: 30px; padding: 0 16px; display: inline-flex; align-items: center; justify-content: center; gap: 4px; font-size: 12px; width: 100%;">
                                    <i class="fa-solid fa-plus"></i> Thêm
                                </button>
                            </td>
                        </tr>

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
        </form>
    </div>
</div>

<!-- TAB 2: LOẠI SẠP CHỢ -->
<div id="cat-stall_type" class="card category-section" style="display: none;">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Danh mục Loại sạp chợ</div>
    </div>
    <div class="card-body p-0">
        <form id="form-add-stall_type" action="<?php echo BASE_URL; ?>api/addCategory" method="POST">
            <input type="hidden" name="type" value="stall_type">
            <?php csrf_field(); ?>
            <div class="table-responsive">
                <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                            <th style="padding: 12px 16px; width: 80px;">ID</th>
                            <th style="padding: 12px 16px; width: 180px;">Mã loại sạp <span style="color: var(--red)">*</span></th>
                            <th style="padding: 12px 16px; width: 220px;">Tên loại sạp <span style="color: var(--red)">*</span></th>
                            <th style="padding: 12px 16px;">Mô tả chi tiết</th>
                            <th style="padding: 12px 16px; text-align: right; width: 120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Inline Form Row for Add -->
                        <tr style="background-color: var(--bg-surface-secondary); border-bottom: 2px solid var(--border-color);">
                            <td style="padding: 10px 16px; color: var(--text-muted); font-style: italic; font-weight: 500;">Mới</td>
                            <td style="padding: 10px 16px;">
                                <input type="text" name="type_code" class="form-control form-control-sm" placeholder="Ví dụ: kiot..." style="height: 30px; font-size: 13px;" required>
                            </td>
                            <td style="padding: 10px 16px;">
                                <input type="text" name="type_name" class="form-control form-control-sm" placeholder="Ví dụ: Kiot..." style="height: 30px; font-size: 13px;" required>
                            </td>
                            <td style="padding: 10px 16px;">
                                <input type="text" name="description" class="form-control form-control-sm" placeholder="Mô tả ngắn..." style="height: 30px; font-size: 13px;">
                            </td>
                            <td style="padding: 10px 16px; text-align: right;">
                                <button type="submit" class="btn btn-primary btn-sm" style="height: 30px; padding: 0 16px; display: inline-flex; align-items: center; justify-content: center; gap: 4px; font-size: 12px; width: 100%;">
                                    <i class="fa-solid fa-plus"></i> Thêm
                                </button>
                            </td>
                        </tr>

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
        </form>
    </div>
</div>

<!-- TAB 3: NGÀNH HÀNG KINH DOANH -->
<div id="cat-business_line" class="card category-section" style="display: none;">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Danh mục Ngành hàng kinh doanh</div>
    </div>
    <div class="card-body p-0">
        <form id="form-add-business_line" action="<?php echo BASE_URL; ?>api/addCategory" method="POST">
            <input type="hidden" name="type" value="business_line">
            <?php csrf_field(); ?>
            <div class="table-responsive">
                <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                            <th style="padding: 12px 16px; width: 80px;">ID</th>
                            <th style="padding: 12px 16px; width: 180px;">Mã ngành hàng <span style="color: var(--red)">*</span></th>
                            <th style="padding: 12px 16px; width: 220px;">Tên ngành hàng <span style="color: var(--red)">*</span></th>
                            <th style="padding: 12px 16px;">Mô tả chi tiết</th>
                            <th style="padding: 12px 16px; text-align: right; width: 120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Inline Form Row for Add -->
                        <tr style="background-color: var(--bg-surface-secondary); border-bottom: 2px solid var(--border-color);">
                            <td style="padding: 10px 16px; color: var(--text-muted); font-style: italic; font-weight: 500;">Mới</td>
                            <td style="padding: 10px 16px;">
                                <input type="text" name="line_code" class="form-control form-control-sm" placeholder="Ví dụ: thoi_trang..." style="height: 30px; font-size: 13px;" required>
                            </td>
                            <td style="padding: 10px 16px;">
                                <input type="text" name="line_name" class="form-control form-control-sm" placeholder="Ví dụ: Thời trang..." style="height: 30px; font-size: 13px;" required>
                            </td>
                            <td style="padding: 10px 16px;">
                                <input type="text" name="description" class="form-control form-control-sm" placeholder="Mô tả ngắn..." style="height: 30px; font-size: 13px;">
                            </td>
                            <td style="padding: 10px 16px; text-align: right;">
                                <button type="submit" class="btn btn-primary btn-sm" style="height: 30px; padding: 0 16px; display: inline-flex; align-items: center; justify-content: center; gap: 4px; font-size: 12px; width: 100%;">
                                    <i class="fa-solid fa-plus"></i> Thêm
                                </button>
                            </td>
                        </tr>

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
        </form>
    </div>
</div>

<!-- TAB 4: LOẠI GIẤY TỜ ATTP -->
<div id="cat-document_type" class="card category-section" style="display: none;">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Danh mục Loại giấy tờ vệ sinh ATTP</div>
    </div>
    <div class="card-body p-0">
        <form id="form-add-document_type" action="<?php echo BASE_URL; ?>api/addCategory" method="POST">
            <input type="hidden" name="type" value="document_type">
            <?php csrf_field(); ?>
            <div class="table-responsive">
                <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                            <th style="padding: 12px 16px; width: 80px;">ID</th>
                            <th style="padding: 12px 16px; width: 180px;">Mã loại giấy tờ <span style="color: var(--red)">*</span></th>
                            <th style="padding: 12px 16px; width: 220px;">Tên loại giấy tờ <span style="color: var(--red)">*</span></th>
                            <th style="padding: 12px 16px;">Mô tả chi tiết</th>
                            <th style="padding: 12px 16px; text-align: right; width: 120px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Inline Form Row for Add -->
                        <tr style="background-color: var(--bg-surface-secondary); border-bottom: 2px solid var(--border-color);">
                            <td style="padding: 10px 16px; color: var(--text-muted); font-style: italic; font-weight: 500;">Mới</td>
                            <td style="padding: 10px 16px;">
                                <input type="text" name="type_code" class="form-control form-control-sm" placeholder="Ví dụ: attp..." style="height: 30px; font-size: 13px;" required>
                            </td>
                            <td style="padding: 10px 16px;">
                                <input type="text" name="type_name" class="form-control form-control-sm" placeholder="Ví dụ: Giấy chứng nhận..." style="height: 30px; font-size: 13px;" required>
                            </td>
                            <td style="padding: 10px 16px;">
                                <input type="text" name="description" class="form-control form-control-sm" placeholder="Mô tả ngắn..." style="height: 30px; font-size: 13px;">
                            </td>
                            <td style="padding: 10px 16px; text-align: right;">
                                <button type="submit" class="btn btn-primary btn-sm" style="height: 30px; padding: 0 16px; display: inline-flex; align-items: center; justify-content: center; gap: 4px; font-size: 12px; width: 100%;">
                                    <i class="fa-solid fa-plus"></i> Thêm
                                </button>
                            </td>
                        </tr>

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
        </form>
    </div>
</div>

<!-- Nạp script bổ sung -->
<script src="<?php echo BASE_URL; ?>public/assets/js/pages/admin/category.js"></script>
