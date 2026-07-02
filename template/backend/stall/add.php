<!-- Form Khai báo Sạp mới -->
<div style="margin-bottom: 20px;">
    <a href="<?php echo BASE_URL; ?>admin/stalls" class="btn btn-outline" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
        <i class="fa-solid fa-arrow-left"></i> Quay lại danh sách sạp
    </a>
</div>

<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--border-color); padding: 16px 20px;">
        <div class="card-title" style="font-size: 16px; font-weight: 600;">Thông tin khai báo sạp chợ mới</div>
    </div>
    <div class="card-body" style="padding: 24px;">
        <form action="<?php echo BASE_URL; ?>admin/stall_add" method="POST">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Mã sạp -->
                <div class="form-group">
                    <label class="form-label" for="stall_code" style="font-weight: 500;">Mã sạp <span style="color: var(--red)">*</span></label>
                    <input type="text" id="stall_code" name="stall_code" class="form-control" placeholder="Ví dụ: SẠP-C02" required>
                </div>

                <!-- Phân khu -->
                <div class="form-group">
                    <label class="form-label" for="zone" style="font-weight: 500;">Phân khu chợ <span style="color: var(--red)">*</span></label>
                    <select id="zone" name="zone" class="form-control" required>
                        <option value="Khu A (Quần áo)">Khu A (Quần áo / Vải vóc)</option>
                        <option value="Khu B (Thực phẩm)">Khu B (Thực phẩm tươi sống)</option>
                        <option value="Khu C (Ăn uống)">Khu C (Ẩm thực / Ăn uống)</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Vị trí/Dãy -->
                <div class="form-group">
                    <label class="form-label" for="location" style="font-weight: 500;">Vị trí cụ thể (Dãy/Số)</label>
                    <input type="text" id="location" name="location" class="form-control" placeholder="Ví dụ: Dãy C, Số 02">
                </div>

                <!-- Diện tích -->
                <div class="form-group">
                    <label class="form-label" for="area" style="font-weight: 500;">Diện tích (m²) <span style="color: var(--red)">*</span></label>
                    <input type="number" id="area" name="area" class="form-control" placeholder="Nhập số mét vuông" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <!-- Đơn giá thuê -->
                <div class="form-group">
                    <label class="form-label" for="price" style="font-weight: 500;">Đơn giá thuê / tháng (VNĐ) <span style="color: var(--red)">*</span></label>
                    <input type="number" id="price" name="price" class="form-control" placeholder="Nhập số tiền thuê" required>
                </div>

                <!-- Trạng thái -->
                <div class="form-group">
                    <label class="form-label" for="status" style="font-weight: 500;">Trạng thái khởi tạo</label>
                    <select id="status" name="status" class="form-control">
                        <option value="empty">Đang trống (Sẵn sàng cho thuê)</option>
                        <option value="repairing">Đang sửa chữa / Bảo trì</option>
                    </select>
                </div>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color-light); margin: 24px 0;">

            <div style="display: flex; justify-content: flex-end; gap: 12px;">
                <a href="<?php echo BASE_URL; ?>admin/stalls" class="btn btn-outline" style="text-decoration: none;">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-check"></i> Lưu sạp chợ
                </button>
            </div>
        </form>
    </div>
</div>
