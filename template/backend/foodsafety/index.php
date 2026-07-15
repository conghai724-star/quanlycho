<!-- Phân loại Tab & Nút thêm mới -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
    <!-- Nút chuyển đổi Tab -->
    <div class="segmented" role="radiogroup" style="max-width: 380px;">
        <label><input type="radio" name="fs-mode" value="docs" checked onclick="App.foodsafety.switchTab('docs')"><span>Giấy tờ & Chứng nhận</span></label>
        <label><input type="radio" name="fs-mode" value="inspections" onclick="App.foodsafety.switchTab('inspections')"><span>Thanh tra & Vi phạm</span></label>
    </div>
    
    <a href="<?php echo BASE_URL; ?>admin/foodsafety_add" class="btn btn-primary" style="height: 36px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; color: white;">
        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 14px; height: 14px;"><path d="M8 2v12M2 8h12"/></svg>
        Khai báo Chứng nhận mới
    </a>
</div>

<!-- TAB 1: GIẤY TỜ & CHỨNG NHẬN (ATTP, SỨC KHỎE, TẬP HUẤN) -->
<div id="fs-docs">
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; gap: 8px; align-items: center; width: 100%;">
            <form id="form-filter-certificates" action="<?php echo BASE_URL; ?>admin/foodsafety" method="GET" style="display: flex; gap: 8px; margin: 0; flex-wrap: wrap; width: 100%;">
                <input type="text" name="q" class="form-control" placeholder="Tìm số GCN, tên giấy tờ, tiểu thương..." style="width: 250px; height: 36px; font-size: 13px;">
                
                <select name="doc_type" class="form-control" style="width: 180px; height: 36px; font-size: 13px;">
                    <option value="">Tất cả loại giấy tờ</option>
                    <option value="ATTP">Giấy chứng nhận ATTP</option>
                    <option value="Health">Giấy khám sức khỏe</option>
                    <option value="Training">Giấy xác nhận tập huấn</option>
                </select>
                
                <select name="status" class="form-control" style="width: 160px; height: 36px; font-size: 13px;">
                    <option value="">Tất cả trạng thái</option>
                    <?php if (!empty($statuses)): ?>
                        <?php foreach ($statuses as $st): ?>
                            <option value="<?php echo htmlspecialchars($st['code']); ?>">
                                <?php echo htmlspecialchars($st['status_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                
                <button type="button" id="btn-filter-certificates" class="btn btn-outline" style="height: 36px; padding: 0 16px;">Lọc</button>
                <a href="<?php echo BASE_URL; ?>admin/foodsafety" class="btn btn-ghost" style="height: 36px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; padding: 0 12px; color: var(--text-muted);">Xóa bộ lọc</a>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
            <div class="card-title" style="font-size: 16px; font-weight: 600;">Hồ sơ Chứng nhận vệ sinh ATTP & Sức khỏe tiểu thương (<span id="filter-total-certificates"><?php echo count($certificates); ?></span>)</div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table" style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                            <th style="padding: 12px 16px;">Tiểu thương</th>
                            <th style="padding: 12px 16px;">Cơ sở kinh doanh</th>
                            <th style="padding: 12px 16px; width: 140px;">Loại giấy tờ</th>
                            <th style="padding: 12px 16px;">Chi tiết hồ sơ</th>
                            <th style="padding: 12px 16px; width: 150px;">Cơ quan cấp</th>
                            <th style="padding: 12px 16px; width: 100px;">Ngày cấp</th>
                            <th style="padding: 12px 16px; width: 100px;">Ngày hết hạn</th>
                            <th style="padding: 12px 16px; width: 110px; text-align: center;">Hạn còn lại</th>
                            <th style="padding: 12px 16px; width: 80px; text-align: center;">Tài liệu</th>
                            <th style="padding: 12px 16px; width: 110px;">Trạng thái</th>
                            <th style="padding: 12px 16px; text-align: right; width: 110px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="table-body-certificates">
                        <?php require DIR_TEMPLATE . '/backend/foodsafety/table_rows.php'; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- TAB 2: THANH TRA & VI PHẠM (Giữ nguyên cấu trúc ban đầu) -->
<div id="fs-inspections" class="card" style="display: none;">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Kế hoạch kiểm tra & Nhật ký vi phạm vệ sinh ATTP</div>
    </div>
    <div class="card-body" style="padding: 20px 0 0 0;">
        <!-- Kế hoạch thanh tra -->
        <div style="padding: 0 20px 20px 20px;">
            <h4 style="font-size: 14px; font-weight: 600; color: var(--text-heading); margin-bottom: 12px;"><i class="fa-solid fa-circle-check text-success me-2"></i> Kế hoạch kiểm tra định kỳ (E.6, E.7)</h4>
            <div class="table-responsive">
                <table class="table" style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                    <thead>
                        <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                            <th style="padding: 10px 12px;">Đợt kiểm tra</th>
                            <th style="padding: 10px 12px;">Đoàn kiểm tra</th>
                            <th style="padding: 10px 12px;">Ngày dự kiến</th>
                            <th style="padding: 10px 12px;">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 10px 12px; font-weight: 600;">Kiểm tra định kỳ quý 2/2026</td>
                            <td style="padding: 10px 12px; color: var(--text-muted);">Ban quản lý chợ + Phòng Y tế Quận</td>
                            <td style="padding: 10px 12px; color: var(--text-muted);">15/07/2026</td>
                            <td style="padding: 10px 12px;"><span class="status status-yellow">Chưa thực hiện</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Nhật ký Vi phạm -->
        <div style="padding: 20px 20px 20px 20px; border-top: 1px solid var(--border-color-light);">
            <h4 style="font-size: 14px; font-weight: 600; color: var(--text-heading); margin-bottom: 12px;"><i class="fa-solid fa-circle-exclamation text-danger me-2"></i> Biên bản Ghi nhận & Xử lý Vi phạm (E.8, E.9)</h4>
            <div class="table-responsive">
                <table class="table" style="width: 100%; border-collapse: collapse; font-size: 12.5px;">
                    <thead>
                        <tr style="text-align: left; background-color: var(--bg-surface-secondary); border-bottom: 1px solid var(--border-color);">
                            <th style="padding: 10px 12px; width: 120px;">Mã biên bản</th>
                            <th style="padding: 10px 12px;">Hộ kinh doanh vi phạm</th>
                            <th style="padding: 10px 12px;">Nội dung vi phạm</th>
                            <th style="padding: 10px 12px;">Hình thức xử lý (E.9)</th>
                            <th style="padding: 10px 12px; width: 140px;">Trạng thái xử lý</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td class="cell-mono" style="padding: 10px 12px; font-weight: 600;">BBVP-0089</td>
                            <td style="padding: 10px 12px; font-weight: 600;">Hộ kinh doanh Hoàng Thực Phẩm</td>
                            <td style="padding: 10px 12px; color: var(--text-muted);">Không đeo găng tay khi chế biến, bày thực phẩm chín không che đậy</td>
                            <td style="padding: 10px 12px; color: var(--red); font-weight: 600;">Phạt cảnh cáo, đình chỉ sạp 3 ngày</td>
                            <td style="padding: 10px 12px;"><span class="status status-green">Đã chấp hành xong</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>



<!-- CSRF Token phục vụ AJAX -->
<?php csrf_field(); ?>

<!-- Nạp JS xử lý AJAX & Form ATTP -->
<script>
$(document).ready(function() {
    window.App = window.App || {};
    window.App.foodsafety = {
        // 1. Chuyển đổi tab
        switchTab: function(mode) {
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
        },

        // 2. Xem ảnh/PDF giấy phép
        viewCertificate: function(traderName, certNo, fileUrl) {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            const swalBg = isDark ? '#1a2332' : '#ffffff';
            const swalColor = isDark ? '#ffffff' : '#0f1623';
            
            let html = `
                <div style="text-align: left; font-size: 13px;">
                    <p><strong>Tiểu thương:</strong> ${traderName}</p>
                    <p><strong>Số chứng nhận:</strong> ${certNo}</p>
                    <p><strong>Bản scan đính kèm:</strong></p>
                    <div style="text-align: center; margin-top: 10px;">
            `;

            if (fileUrl.endsWith('.pdf')) {
                html += `<iframe src="<?php echo BASE_URL; ?>${fileUrl}" style="width:100%; height:350px; border:none;"></iframe>`;
            } else {
                html += `<img src="<?php echo BASE_URL; ?>${fileUrl}" style="max-width:100%; max-height:350px; border-radius:4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);" />`;
            }
            html += '</div></div>';

            Swal.fire({
                title: 'Giấy chứng nhận ATTP',
                html: html,
                confirmButtonText: 'Đóng',
                confirmButtonColor: '#1ABB9C',
                width: 600,
                background: swalBg,
                color: swalColor
            });
        },

        // 3. Xóa giấy phép
        deleteCertificate: function(id, number) {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            const swalBg = isDark ? '#1a2332' : '#ffffff';
            const swalColor = isDark ? '#ffffff' : '#0f1623';

            Swal.fire({
                title: 'Xóa giấy chứng nhận?',
                text: "Bạn có chắc chắn muốn xóa giấy chứng nhận số '" + number + "' khỏi hệ thống?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Đồng ý xóa',
                cancelButtonText: 'Hủy bỏ',
                confirmButtonColor: '#EA4335',
                cancelButtonColor: '#a0aec0',
                background: swalBg,
                color: swalColor
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?php echo BASE_URL; ?>admin/foodsafety_delete/' + id;
                }
            });
        }
    };
});
</script>

