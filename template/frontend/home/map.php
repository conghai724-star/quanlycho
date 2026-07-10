<!-- ================= HERO BẢN ĐỒ ================= -->
<section class="hero" style="padding: 60px 0 80px;">
    <div class="hero-grid-pattern"></div>
    <div class="container hero-content" style="grid-template-columns: 1fr; text-align: center;">
        <div>
            <div class="hero-eyebrow" style="justify-content: center;"><span class="dot"></span> Bản đồ số</div>
            <h1>Sơ đồ & Bản đồ số Chợ</h1>
            <p style="margin: 0 auto;">Tra cứu vị trí sạp trực tuyến, tình trạng sử dụng sạp và đăng ký thuê trực tiếp.</p>
        </div>
    </div>
</section>

<!-- CSS của Sơ đồ chợ hiển thị phía khách hàng -->
<style>
    .map-container-public {
        background-color: var(--gray-50);
        border: 1px solid var(--gray-200);
        border-radius: var(--radius-lg);
        overflow: hidden;
        position: relative;
        height: 600px;
        display: flex;
        flex-direction: column;
    }

    /* Toolbar của map */
    .map-toolbar-public {
        height: 50px;
        background-color: #fff;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        z-index: 10;
        box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    }

    .map-viewport-public {
        flex: 1;
        overflow: auto;
        position: relative;
        background-color: #f8fafc;
        cursor: grab;
    }
    .map-viewport-public:active {
        cursor: grabbing;
    }

    .map-grid-public {
        width: 2400px;
        height: 1800px;
        background-color: #ffffff;
        background-image: 
            linear-gradient(rgba(0,0,0,0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(0,0,0,0.03) 1px, transparent 1px);
        background-size: 20px 20px;
        position: relative;
        transform-origin: 0 0;
        transition: transform 0.1s ease-out;
    }

    /* Các khối sạp / tiện ích */
    .map-element-public {
        position: absolute;
        border: 2px solid #64748b;
        background-color: rgba(226, 232, 240, 0.85);
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 11px;
        color: #1e293b;
        border-radius: 4px;
        padding: 4px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        cursor: pointer;
        transition: all 0.2s;
        transform-origin: center center;
    }

    .map-element-public:hover {
        transform: scale(1.03);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        z-index: 20;
    }

    /* Trạng thái sạp */
    .map-element-public.status-green, .map-element-public.status-rented {
        background-color: #dcfce7;
        border-color: #22c55e;
        color: #15803d;
     }
    .map-element-public.status-white, .map-element-public.status-empty {
        background-color: #dbeafe;
        border-color: #3b82f6;
        color: #1d4ed8;
     }
    .map-element-public.status-yellow, .map-element-public.status-repairing {
        background-color: #fef9c3;
        border-color: #eab308;
        color: #a16207;
     }
    .map-element-public.status-red, .map-element-public.status-locked {
        background-color: #fee2e2;
        border-color: #ef4444;
        color: #b91c1c;
     }

    /* Các khối trang trí */
    .map-element-public.type-gate {
        background-color: #ffedd5;
        border-color: #f97316;
        color: #c2410c;
        cursor: default;
    }
    .map-element-public.type-door {
        background-color: #f5f5f4;
        border-color: #78716c;
        color: #44403c;
        cursor: default;
    }
    .map-element-public.type-street {
        background-color: #f1f5f9;
        border-color: #cbd5e1;
        color: #475569;
        border-radius: 0;
        border-style: dotted;
        cursor: default;
    }
    .map-element-public.type-street-straight,
    .map-element-public.type-fence {
        overflow: hidden;
        padding: 0;
        box-shadow: none;
        border-radius: 2px;
        cursor: default;
    }

    .map-element-public.type-street-svg,
    .map-element-public.type-fence-svg {
        position: absolute;
        overflow: visible;
        pointer-events: none;
        z-index: 1;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
    }
    
    .map-element-public.type-street-svg polyline,
    .map-element-public.type-fence-svg polyline {
        cursor: default;
        pointer-events: auto;
    }
    
    .map-element-public.type-street-svg .street-bg,
    .map-element-public.type-fence-svg .fence-bg {
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    
    .map-element-public.type-street-svg .street-line,
    .map-element-public.type-fence-svg .fence-line,
    .map-element-public.type-fence-svg .fence-core {
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .map-element-public.type-street-svg .street-line {
        stroke-dasharray: 10 15;
        stroke: rgba(255, 255, 255, 0.85);
        opacity: 0.95;
    }
    .map-element-public.type-fence {
        background:
            repeating-linear-gradient(90deg, #ddc9b0 0 8px, #f3eadf 8px 16px);
        border-color: #bfa98d;
        color: transparent;
        border-style: dashed;
    }
    .map-element-public.type-fence::before {
        content: "";
        position: absolute;
        left: 6px;
        right: 6px;
        top: 50%;
        height: 3px;
        transform: translateY(-50%);
        background: rgba(109, 83, 44, 0.55);
        border-radius: 999px;
        box-shadow:
            0 -8px 0 0 rgba(109, 83, 44, 0.28),
            0 8px 0 0 rgba(109, 83, 44, 0.28);
    }
    .map-element-public.type-security-room {
        background:
            linear-gradient(180deg, rgba(255,255,255,0.18), rgba(255,255,255,0.04)),
            linear-gradient(135deg, rgba(12,17,29,0.04), rgba(12,17,29,0.00));
        border-color: #94a3b8;
        color: #1e293b;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.18);
        cursor: default;
    }
    .map-element-public.type-security-room::before {
        content: "";
        position: absolute;
        inset: 12px 10px auto;
        height: 2px;
        background: rgba(255,255,255,0.72);
        border-radius: 999px;
    }
    .map-element-public.type-security-room::after {
        content: "";
        position: absolute;
        left: 12px;
        right: 12px;
        bottom: 10px;
        height: 18px;
        border-radius: 4px;
        border: 1px solid rgba(255,255,255,0.35);
        background: rgba(255,255,255,0.12);
    }
    .map-element-public.type-utility {
        background-color: #f3e8ff;
        border-color: #a855f7;
        color: #7e22ce;
        cursor: default;
    }
    .map-element-public.type-office {
        background-color: #e0f2fe;
        border-color: #0ea5e9;
        color: #0369a1;
        cursor: default;
    }

    .map-element-public.is-icon-only,
    .map-element-public.type-gate,
    .map-element-public.type-door,
    .map-element-public.type-utility,
    .map-element-public.type-office,
    .map-element-public.type-security-room {
        background: transparent;
        border-color: transparent;
        box-shadow: none;
        cursor: default;
    }

    .map-element-public.type-gate { color: #c2410c; }
    .map-element-public.type-door { color: #44403c; }
    .map-element-public.type-utility { color: #7e22ce; }
    .map-element-public.type-office { color: #0369a1; }
    .map-element-public.type-security-room { color: #1e293b; }

    .map-element-public.type-security-room::before,
    .map-element-public.type-security-room::after {
        display: none;
    }

    .map-element-public i {
        display: block;
        font-size: var(--icon-size, 1.5em);
        line-height: 1;
        margin-bottom: 4px;
    }

    .map-element-public strong {
        display: block;
        font-size: 0.72em;
        line-height: 1.08;
        letter-spacing: 0.01em;
    }

    .map-element-public.is-icon-only {
        overflow: visible;
        padding: 0;
        background: transparent !important;
        border-color: transparent !important;
        box-shadow: none !important;
    }

    .map-element-public.is-icon-only i {
        position: absolute;
        left: 50%;
        top: 50%;
        width: 1em;
        height: 1em;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        font-size: var(--icon-size, 100px);
        transform: translate(-50%, -50%) scale(var(--icon-stretch-x, 1), var(--icon-stretch-y, 1));
        transform-origin: center center;
    }

    /* Highlighting sạp được tìm thấy */
    @keyframes pulse-highlight {
        0% { box-shadow: 0 0 0 0px rgba(59, 130, 246, 0.7); border-color: #3b82f6; }
        50% { box-shadow: 0 0 0 15px rgba(59, 130, 246, 0); border-color: #1d4ed8; transform: scale(1.08); }
        100% { box-shadow: 0 0 0 0px rgba(59, 130, 246, 0); border-color: #3b82f6; }
    }
    .map-element-public.highlighted {
        animation: pulse-highlight 1.5s infinite;
        z-index: 99;
        border-width: 3px !important;
    }

    /* CSS Tooltip thông tin */
    .map-tooltip-public {
        position: absolute;
        background: #ffffff;
        border: 1px solid var(--gray-300);
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-lg);
        padding: 16px;
        width: 250px;
        z-index: 1000;
        display: none;
        pointer-events: auto; /* Cho phép bấm nút trong tooltip */
        font-family: inherit;
    }

    .map-tooltip-public h4 {
        margin: 0 0 8px;
        font-size: 15px;
        font-weight: 800;
        color: var(--gray-900);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .tooltip-meta-item {
        font-size: 12.5px;
        margin-bottom: 6px;
        color: var(--gray-600);
    }
    .tooltip-meta-item strong {
        color: var(--gray-900);
    }

    /* Badge trạng thái sạp */
    .badge-status {
        font-size: 10px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
    }
    .badge-status.rented { background-color: #dcfce7; color: #15803d; }
    .badge-status.empty { background-color: #dbeafe; color: #1d4ed8; }
    .badge-status.repairing { background-color: #fef9c3; color: #a16207; }
    .badge-status.locked { background-color: #fee2e2; color: #b91c1c; }

    /* Zoom controls widget */
    .zoom-widget {
        display: flex;
        gap: 4px;
        background: rgba(255,255,255,0.9);
        border: 1px solid var(--gray-300);
        border-radius: var(--radius-md);
        padding: 4px;
    }

    .zoom-widget button {
        background: transparent;
        border: none;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border-radius: 4px;
        color: var(--gray-700);
        transition: background 0.2s;
    }

    .zoom-widget button:hover {
        background: var(--gray-100);
        color: var(--gray-900);
    }
</style>

<!-- ================= BẢN ĐỒ CHI TIẾT ================= -->
<section class="map-section" style="background: none; color: inherit; padding: 60px 0;">
    <div class="container">
        <div class="map-grid" style="grid-template-columns: 1.3fr 0.7fr; gap: 40px; align-items: start;">
            <!-- Bản đồ tương tác bên trái -->
            <div>
                <div class="map-container-public">
                    <!-- Toolbar điều khiển sơ đồ -->
                    <div class="map-toolbar-public">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-weight: 700; font-size: 13.5px; color: var(--gray-900);"><i class="fa-solid fa-map-location-dot" style="color: var(--primary);"></i> Sơ đồ tương tác</span>
                            <span style="font-size: 11.5px; color: var(--gray-500);">(Giữ chuột trái để di chuyển sơ đồ)</span>
                        </div>
                        <div class="zoom-widget">
                            <button id="btn-zoom-out" title="Thu nhỏ"><i class="fa-solid fa-minus"></i></button>
                            <span id="zoom-badge" style="font-size: 11px; font-weight: 700; min-width: 36px; text-align: center; line-height: 28px; color: var(--gray-800);">100%</span>
                            <button id="btn-zoom-in" title="Phóng to"><i class="fa-solid fa-plus"></i></button>
                            <button id="btn-zoom-reset" title="Mặc định"><i class="fa-solid fa-arrows-to-eye"></i></button>
                        </div>
                    </div>

                    <!-- Viewport sơ đồ -->
                    <div class="map-viewport-public" id="map-viewport">
                        <div class="map-grid-public" id="map-grid">
                            <!-- Các phần tử sơ đồ động được render bởi JS -->
                        </div>

                        <!-- Tooltip thông tin khi chọn phần tử -->
                        <div class="map-tooltip-public" id="map-tooltip">
                            <h4 id="tooltip-title">SẠP-A01 <span class="badge-status" id="tooltip-status-badge">Trống</span></h4>
                            <div class="tooltip-meta-item">Diện tích: <strong id="tooltip-area">9 m²</strong></div>
                            <div class="tooltip-meta-item">Đơn giá thuê: <strong id="tooltip-price">1.500.000 đ</strong>/tháng</div>
                            <div class="tooltip-meta-item" id="tooltip-trader-row">Tiểu thương: <strong id="tooltip-trader">Chưa thuê</strong></div>
                            <div id="tooltip-action-row" style="margin-top: 12px; border-top: 1px solid var(--gray-200); padding-top: 12px; display: none;">
                                <a href="#" id="tooltip-btn-register" class="btn btn-primary btn-sm btn-block" style="text-align: center; text-decoration: none; padding: 6px 12px; font-size: 12px;">Đăng ký thuê ngay</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bảng tra cứu thông tin bên phải -->
            <div style="background: #ffffff; padding: 30px; border-radius: var(--radius-lg); border: 1px solid var(--gray-300); box-shadow: var(--shadow-sm);">
                <h3 style="font-size: 19px; font-weight: 800; margin-bottom: 8px; color: var(--gray-900);">Tra cứu vị trí sạp</h3>
                <p style="color: var(--gray-600); font-size: 13.5px; margin-bottom: 20px;">Nhập mã hiệu sạp (ví dụ: SẠP-A01) để định vị và nhấp nháy làm nổi bật sạp đó trên sơ đồ chợ.</p>
                
                <div class="map-search" style="background: #fff; border: 1px solid var(--gray-300); margin: 0 0 24px 0; padding: 6px; display: flex; border-radius: 8px;">
                    <input type="text" id="map-search-input" placeholder="Nhập mã sạp (ví dụ: A01)..." style="color: var(--gray-900); border: none; padding: 8px; flex: 1; outline: none; font-size: 13.5px;">
                    <button class="btn btn-primary btn-sm" id="btn-search-stall" style="border-radius: 6px; font-size: 12px; padding: 0 16px;">Tìm kiếm</button>
                </div>

                <h4 style="font-size: 14.5px; font-weight: 800; margin-bottom: 14px; color: var(--gray-900); border-bottom: 1px solid var(--gray-200); padding-bottom: 8px;">Chú giải bản đồ</h4>
                <div class="legend" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;">
                    <div class="legend-item" style="color: var(--gray-900); display: flex; align-items: center; gap: 10px; font-size: 13px;">
                        <span class="legend-dot" style="background:#dcfce7; border: 1.5px solid #22c55e; width: 14px; height: 14px; border-radius: 4px; display: inline-block;"></span>
                        Sạp đã được thuê
                    </div>
                    <div class="legend-item" style="color: var(--gray-900); display: flex; align-items: center; gap: 10px; font-size: 13px;">
                        <span class="legend-dot" style="background:#dbeafe; border: 1.5px solid #3b82f6; width: 14px; height: 14px; border-radius: 4px; display: inline-block;"></span>
                        Sạp còn trống (Sẵn sàng thuê)
                    </div>
                    <div class="legend-item" style="color: var(--gray-900); display: flex; align-items: center; gap: 10px; font-size: 13px;">
                        <span class="legend-dot" style="background:#fef9c3; border: 1.5px solid #eab308; width: 14px; height: 14px; border-radius: 4px; display: inline-block;"></span>
                        Sạp đang bảo trì, sửa chữa
                    </div>
                    <div class="legend-item" style="color: var(--gray-900); display: flex; align-items: center; gap: 10px; font-size: 13px;">
                        <span class="legend-dot" style="background:#fee2e2; border: 1.5px solid #ef4444; width: 14px; height: 14px; border-radius: 4px; display: inline-block;"></span>
                        Sạp tạm khóa
                    </div>
                </div>

                <div style="background-color: var(--gray-100); border: 1px solid var(--gray-200); border-radius: var(--radius-md); padding: 16px; font-size: 12.5px; color: var(--gray-600);">
                    <i class="fa-solid fa-circle-info" style="color: var(--primary); margin-right: 6px;"></i>
                    Bấm trực tiếp vào từng sạp chợ trên sơ đồ để xem diện tích, đơn giá và bấm vào nút đăng ký thuê online tiện lợi.
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= DANH SÁCH SẠP TRỐNG ================= -->
<section class="bg-gray" style="padding: 60px 0;">
    <div class="container">
        <div style="margin-bottom: 30px;">
            <div class="eyebrow">Cơ hội kinh doanh</div>
            <h2 class="section-title" style="margin-bottom:6px;">Danh sách sạp còn trống hiện tại</h2>
            <p style="color:var(--gray-600); font-size:15px;">Chọn sạp phù hợp trên sơ đồ hoặc danh sách bên dưới và gửi yêu cầu đăng ký thuê trực tuyến</p>
        </div>
        
        <div class="stalls-grid">
            <?php 
            $emptyStalls = array_filter($elements, function($e) {
                return $e['element_type'] === 'stall' && $e['status_code'] === 'empty';
            });
            ?>

            <?php if (!empty($emptyStalls)): ?>
                <?php foreach ($emptyStalls as $st): ?>
                    <div class="stall-card" style="box-shadow: var(--shadow-sm); border: 1px solid var(--gray-200); background: #ffffff; padding: 24px; border-radius: var(--radius-lg); display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s;">
                        <div class="stall-top" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                            <div>
                                <div class="stall-code" style="font-size: 18px; font-weight: 800; color: var(--gray-900);"><?php echo htmlspecialchars($st['stall_code']); ?></div>
                                <div class="stall-zone" style="font-size: 12px; color: var(--gray-500); margin-top: 4px;">Quầy hàng diện tích chuẩn</div>
                            </div>
                            <span class="badge badge-vacant" style="background-color: #dbeafe; color: #1d4ed8; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px;">Còn trống</span>
                        </div>
                        <div class="stall-meta" style="border-top: 1px solid var(--gray-200); border-bottom: 1px solid var(--gray-200); padding: 12px 0; margin-bottom: 16px; font-size: 13.5px; color: var(--gray-600);">
                            <div style="margin-bottom: 8px;">Diện tích: <b style="color: var(--gray-900);"><?php echo $st['area_size']; ?> m²</b></div>
                            <div>Đơn giá: <b style="color: var(--gray-900);"><?php echo number_format($st['base_price'], 0, ',', '.'); ?> đ</b>/tháng</div>
                        </div>
                        <a href="<?php echo BASE_URL; ?>home/register?stall_code=<?php echo urlencode($st['stall_code']); ?>&area=<?php echo $st['area_size']; ?>" class="btn btn-outline btn-block btn-sm" style="text-align: center; text-decoration: none;">Đăng ký thuê</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: #ffffff; border-radius: var(--radius-lg); border: 1px dashed var(--gray-300);">
                    <i class="fa-solid fa-store-slash" style="font-size: 40px; color: var(--gray-400); margin-bottom: 12px;"></i>
                    <p style="color: var(--gray-600); font-size: 14.5px;">Hiện tại không còn sạp trống nào trên sơ đồ. Xin vui lòng liên hệ trực tiếp BQL để được hỗ trợ.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Truyền dữ liệu sơ đồ từ PHP sang JS -->
<script>
    window.MAP_ELEMENTS = <?php echo json_encode($elements); ?>;
</script>

<!-- Script tương tác bản đồ ngoài trang chủ -->
<script src="<?php echo BASE_URL; ?>public/assets/js/pages/home/map-view.js?v=<?php echo time(); ?>"></script>
