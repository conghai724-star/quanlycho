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
            <button onclick="closeAppendicesModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--text-muted);">&times;</button>
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

<script src="<?php echo BASE_URL; ?>public/assets/js/pages/admin/contract.js?v=<?php echo time(); ?>"></script>
