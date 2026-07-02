<!-- Bảng điều khiển Tùy biến Chủ đề -->
<div class="banner banner-info" style="margin-bottom: 24px;">
    <svg class="banner-icon" width="18" height="18" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 5v.01M8 7v4"/></svg>
    <div class="banner-body">
        <strong>Chọn một màu sắc chủ đạo</strong> hoặc thay đổi bo góc, kích thước font chữ dưới đây để cảm nhận sự thay đổi trực tiếp của hệ thống.
        Khi bạn ưng ý, hãy nhấn <strong>Sao chép SCSS</strong> hoặc <strong>Tải về</strong> để áp dụng.
    </div>
</div>

<div class="theme-layout">

    <!-- Thanh công cụ tùy chỉnh (Bên trái) -->
    <aside class="theme-controls">
        <!-- 1. Màu chủ đạo -->
        <div class="card">
            <div class="card-header"><div class="card-title">Màu chủ đạo (Primary)</div></div>
            <div class="card-body">
                <div class="theme-swatches" id="primary-swatches">
                    <button type="button" class="theme-swatch active" data-primary="#1ABB9C" data-primary-dk="#169f85" style="background:#1ABB9C" title="Teal (Mặc định)"></button>
                    <button type="button" class="theme-swatch" data-primary="#066fd1" data-primary-dk="#054ea0" style="background:#066fd1" title="Xanh dương"></button>
                    <button type="button" class="theme-swatch" data-primary="#4263eb" data-primary-dk="#2747c4" style="background:#4263eb" title="Chàm"></button>
                    <button type="button" class="theme-swatch" data-primary="#ae3ec9" data-primary-dk="#8628a0" style="background:#ae3ec9" title="Tím"></button>
                    <button type="button" class="theme-swatch" data-primary="#d6336c" data-primary-dk="#a82054" style="background:#d6336c" title="Hồng"></button>
                    <button type="button" class="theme-swatch" data-primary="#d63939" data-primary-dk="#a82b2b" style="background:#d63939" title="Đỏ"></button>
                    <button type="button" class="theme-swatch" data-primary="#f76707" data-primary-dk="#c25204" style="background:#f76707" title="Cam"></button>
                    <button type="button" class="theme-swatch" data-primary="#f59f00" data-primary-dk="#c27d00" style="background:#f59f00" title="Vàng"></button>
                    <button type="button" class="theme-swatch" data-primary="#2fb344" data-primary-dk="#1f8a30" style="background:#2fb344" title="Xanh lá"></button>
                    <button type="button" class="theme-swatch" data-primary="#17a2b8" data-primary-dk="#107a8a" style="background:#17a2b8" title="Cyan"></button>
                    <button type="button" class="theme-swatch" data-primary="#0f1623" data-primary-dk="#000" style="background:#0f1623" title="Đen"></button>
                </div>
                <div class="form-group" style="margin-top:14px; margin-bottom:0">
                    <label class="form-label" for="custom-color">Hoặc chọn màu riêng</label>
                    <div style="display:flex; gap:8px">
                        <input type="color" id="custom-color-picker" value="#1ABB9C" aria-label="Màu tự chọn" style="width:42px; height:36px; padding:0; border:1px solid var(--border-color); border-radius:var(--radius-sm); cursor:pointer">
                        <input type="text" id="custom-color" class="form-control" value="#1ABB9C" style="flex:1; font-family:var(--font-mono); font-size:12.5px">
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Kiểu Sidebar -->
        <div class="card">
            <div class="card-header"><div class="card-title">Màu sắc Sidebar</div></div>
            <div class="card-body">
                <label class="form-label">Chọn kiểu nền:</label>
                <div class="segmented" id="sidebar-style" role="radiogroup">
                    <label><input type="radio" name="sidebar-bg" value="#1a2332" checked><span>Dark</span></label>
                    <label><input type="radio" name="sidebar-bg" value="#0f1623"><span>Black</span></label>
                    <label><input type="radio" name="sidebar-bg" value="#ffffff"><span>Light</span></label>
                    <label><input type="radio" name="sidebar-bg" value="primary"><span>Brand</span></label>
                </div>
            </div>
        </div>

        <!-- 3. Cấu hình hình học Layout -->
        <div class="card">
            <div class="card-header"><div class="card-title">Bố cục & Kích thước</div></div>
            <div class="card-body" style="display:flex; flex-direction:column; gap:14px">
                <div>
                    <label class="form-label" style="display:flex; justify-content:space-between">Độ bo góc sạp/card <span style="color:var(--text-muted)" id="radius-display">6 px</span></label>
                    <input type="range" id="radius-slider" class="slider" min="0" max="16" step="1" value="6" aria-label="Độ bo góc">
                </div>
                <div>
                    <label class="form-label" style="display:flex; justify-content:space-between">Chiều rộng Sidebar <span style="color:var(--text-muted)" id="sidebar-w-display">252 px</span></label>
                    <input type="range" id="sidebar-w-slider" class="slider" min="200" max="320" step="4" value="252" aria-label="Chiều rộng Sidebar">
                </div>
                <div>
                    <label class="form-label" style="display:flex; justify-content:space-between">Cỡ chữ hệ thống <span style="color:var(--text-muted)" id="font-size-display">14 px</span></label>
                    <input type="range" id="font-size-slider" class="slider" min="13" max="16" step="0.5" value="14" aria-label="Cỡ chữ">
                </div>
            </div>
        </div>

        <!-- 4. Chế độ -->
        <div class="card">
            <div class="card-header"><div class="card-title">Chế độ sáng tối</div></div>
            <div class="card-body">
                <div class="segmented" id="theme-mode" role="radiogroup">
                    <label><input type="radio" name="mode" value="light"><span>Sáng (Light)</span></label>
                    <label><input type="radio" name="mode" value="dark"><span>Tối (Dark)</span></label>
                </div>
            </div>
        </div>
    </aside>

    <!-- Khu vực Xem trước (Preview) - Bên phải -->
    <section class="theme-preview">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Khu vực hiển thị trực quan (Live Preview)</div>
                    <div class="card-subtitle">Mọi thay đổi của bạn sẽ áp dụng ngay lập tức lên các nút bấm, trạng thái, form và biểu đồ mẫu</div>
                </div>
            </div>
            <div class="card-body" style="display:flex; flex-direction:column; gap:18px">

                <!-- 1. Mẫu nút -->
                <div>
                    <div class="theme-section-label">Buttons (Nút bấm)</div>
                    <div style="display:flex; flex-wrap:wrap; gap:8px">
                        <button class="btn btn-primary">Primary Button</button>
                        <button class="btn btn-outline">Outline Button</button>
                        <button class="btn btn-ghost">Ghost Button</button>
                        <button class="btn btn-danger">Danger Button</button>
                    </div>
                </div>

                <!-- 2. Nhãn trạng thái (Thích hợp cho trạng thái sạp chợ) -->
                <div>
                    <div class="theme-section-label">Status (Trạng thái)</div>
                    <div style="display:flex; flex-wrap:wrap; gap:8px">
                        <span class="status status-green">Đã thuê (Green)</span>
                        <span class="status status-yellow">Đang chờ (Yellow)</span>
                        <span class="status status-red">Sửa chữa (Red)</span>
                        <span class="chip">Kiot A</span>
                        <span class="chip active">Đang chọn</span>
                    </div>
                </div>

                <!-- 3. Mẫu Form -->
                <div>
                    <div class="theme-section-label">Form Input (Bộ gõ dữ liệu)</div>
                    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:12px">
                        <input class="form-control" placeholder="Gõ thử chữ..." value="Hệ thống Quản lý chợ" aria-label="Input mẫu">
                        <select class="form-control" aria-label="Dropdown mẫu"><option>Chọn khu vực sạp...</option></select>
                        <label class="switch"><input type="checkbox" checked><span class="track"></span><span class="switch-label">Bật tính năng</span></label>
                    </div>
                </div>

                <!-- 4. Biểu đồ mẫu -->
                <div>
                    <div class="theme-section-label">Chart (Biểu đồ)</div>
                    <div class="chart-area" style="height:200px; border:1px solid var(--border-color-light); border-radius:var(--radius-sm); padding:8px">
                        <div data-chart="revenue-line" style="width:100%; height:100%"></div>
                    </div>
                </div>

                <!-- 5. Bảng mẫu -->
                <div>
                    <div class="theme-section-label">Table (Bảng biểu mẫu)</div>
                    <table class="table" style="border:1px solid var(--border-color-light); border-radius:var(--radius); overflow:hidden">
                        <thead><tr><th>Tên sạp</th><th>Loại</th><th>Trạng thái</th></tr></thead>
                        <tbody>
                            <tr><td>SẠP-A01</td><td><span class="chip">Kiot</span></td><td><span class="status status-green">Đã thuê</span></td></tr>
                            <tr><td>SẠP-B02</td><td><span class="chip">Quầy tiêu chuẩn</span></td><td><span class="status status-yellow">Đang trống</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Khối chứa mã CSS/SCSS Token -->
        <div class="card" style="margin-top:16px">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div class="card-title">CSS Variables / Tokens đã tạo</div>
                <button type="button" class="btn btn-outline btn-sm" id="copy-tokens-2">Sao chép</button>
            </div>
            <pre class="pg-code" id="tokens-output" style="margin:0; border-top:1px solid var(--border-color-light); max-height:340px; overflow-y: auto; padding: 12px; font-family: monospace; font-size: 12px;"></pre>
        </div>
    </section>

</div>

<style>
.theme-layout {
    display: grid;
    grid-template-columns: 320px minmax(0, 1fr);
    gap: 16px;
    align-items: start;
    margin-top: 16px;
}
.theme-controls { display: flex; flex-direction: column; gap: 14px; }
.theme-section-label {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    margin-bottom: 8px;
}
.theme-swatches {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(36px, 1fr));
    gap: 8px;
}
.theme-swatch {
    width: 100%;
    aspect-ratio: 1;
    border-radius: 50%;
    border: 2px solid transparent;
    cursor: pointer;
    transition: transform 100ms;
    position: relative;
}
.theme-swatch:hover { transform: scale(1.08); }
.theme-swatch.active {
    border-color: var(--bg-surface);
    box-shadow: 0 0 0 2px var(--text);
}
.theme-swatch.active::after {
    content: '';
    position: absolute;
    top: 50%; left: 50%;
    width: 10px; height: 10px;
    transform: translate(-50%, -55%) rotate(45deg);
    border-right: 2px solid white;
    border-bottom: 2px solid white;
}
@media (max-width: 1100px) {
    .theme-layout { grid-template-columns: 1fr; }
}
</style>
