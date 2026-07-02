<!-- Form Ghi số Điện & Nước mới -->
<div style="margin-bottom: 20px;">
    <a href="<?php echo BASE_URL; ?>admin/utilities" class="btn btn-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-arrow-left"></i> Quay lại chốt số
    </a>
</div>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Ghi chỉ số Tiêu thụ Điện & Nước hàng tháng</div>
    </div>
    <div class="card-body" style="padding: 24px;">
        <form action="<?php echo BASE_URL; ?>admin/utility_add" method="POST">
            <?php csrf_field(); ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Kỳ chốt số -->
                <div class="form-group">
                    <label class="form-label" for="period" style="font-weight: 500;">Kỳ thanh toán (Tháng/Năm) <span style="color: var(--red)">*</span></label>
                    <select id="period" name="period" class="form-control" required>
                        <option value="07/2026">Tháng 07/2026</option>
                        <option value="06/2026" selected>Tháng 06/2026</option>
                    </select>
                </div>

                <!-- Sạp chợ ghi nhận -->
                <div class="form-group">
                    <label class="form-label" for="stall_code" style="font-weight: 500;">Chọn Sạp chợ <span style="color: var(--red)">*</span></label>
                    <select id="stall_code" name="stall_code" class="form-control" onchange="updateOldValues(this.value)" required>
                        <option value="">-- Chọn sạp --</option>
                        <option value="SẠP-A01">SẠP-A01 (Khu A)</option>
                        <option value="SẠP-B01">SẠP-B01 (Khu B)</option>
                    </select>
                </div>
            </div>

            <!-- Khối thông tin Điện -->
            <div class="card" style="margin-bottom: 20px; border: 1px solid var(--border-color-light);">
                <div class="card-header" style="padding: 12px 16px; background-color: var(--bg-surface-secondary);"><i class="fa-solid fa-bolt text-warning me-1"></i> <strong>Chỉ số Điện (kWh)</strong></div>
                <div class="card-body" style="padding: 16px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <div class="form-group">
                        <label class="form-label" for="old_electric">Chỉ số cũ</label>
                        <input type="number" id="old_electric" name="old_electric" class="form-control" value="0" readonly style="background-color: var(--bg-surface-secondary);">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="new_electric">Chỉ số mới đầu kỳ <span style="color: var(--red)">*</span></label>
                        <input type="number" id="new_electric" name="new_electric" class="form-control" placeholder="Nhập chỉ số điện mới" required>
                    </div>
                </div>
            </div>

            <!-- Khối thông tin Nước -->
            <div class="card" style="margin-bottom: 24px; border: 1px solid var(--border-color-light);">
                <div class="card-header" style="padding: 12px 16px; background-color: var(--bg-surface-secondary);"><i class="fa-solid fa-droplet text-primary me-1"></i> <strong>Chỉ số Nước (m³)</strong></div>
                <div class="card-body" style="padding: 16px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                    <div class="form-group">
                        <label class="form-label" for="old_water">Chỉ số cũ</label>
                        <input type="number" id="old_water" name="old_water" class="form-control" value="0" readonly style="background-color: var(--bg-surface-secondary);">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="new_water">Chỉ số mới đầu kỳ <span style="color: var(--red)">*</span></label>
                        <input type="number" id="new_water" name="new_water" class="form-control" placeholder="Nhập chỉ số nước mới" required>
                    </div>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color-light); margin: 24px 0;">

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="<?php echo BASE_URL; ?>admin/utilities" class="btn btn-outline" style="text-decoration: none;">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-check"></i> Chốt chỉ số
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Hàm giả lập thay đổi chỉ số cũ dựa trên sạp được chọn để làm mockup như thật
    function updateOldValues(stallCode) {
        if (stallCode === 'SẠP-A01') {
            document.getElementById('old_electric').value = 1690;
            document.getElementById('old_water').value = 255;
        } else if (stallCode === 'SẠP-B01') {
            document.getElementById('old_electric').value = 3450;
            document.getElementById('old_water').value = 432;
        } else {
            document.getElementById('old_electric').value = 0;
            document.getElementById('old_water').value = 0;
        }
    }
</script>
