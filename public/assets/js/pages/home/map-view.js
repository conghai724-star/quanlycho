(function () {
    // 1. Khai báo trạng thái
    const elements = window.MAP_ELEMENTS || [];
    let zoomLevel = 1.0;
    const zoomStep = 0.1;
    const minZoom = 0.5;
    const maxZoom = 2.0;

    let isPanning = false;
    let startX = 0;
    let startY = 0;
    let scrollLeft = 0;
    let scrollTop = 0;

    // DOM Elements
    const mapGrid = document.getElementById('map-grid');
    const mapViewport = document.getElementById('map-viewport');
    const zoomBadge = document.getElementById('zoom-badge');
    const mapTooltip = document.getElementById('map-tooltip');
    
    // Tooltip sub-elements
    const tooltipTitle = document.getElementById('tooltip-title');
    const tooltipStatusBadge = document.getElementById('tooltip-status-badge');
    const tooltipArea = document.getElementById('tooltip-area');
    const tooltipPrice = document.getElementById('tooltip-price');
    const tooltipTrader = document.getElementById('tooltip-trader');
    const tooltipTraderRow = document.getElementById('tooltip-trader-row');
    const tooltipActionRow = document.getElementById('tooltip-action-row');
    const tooltipBtnRegister = document.getElementById('tooltip-btn-register');

    const searchInput = document.getElementById('map-search-input');
    const btnSearchStall = document.getElementById('btn-search-stall');

    function isRoadType(type) {
        return type === 'street' || type === 'fence';
    }

    function isRoadLikeType(type) {
        return isRoadType(type);
    }

    function isIconOnlyType(type) {
        return !isRoadLikeType(type);
    }

    // Lấy class hiển thị của loại phần tử
    function getElementTypeClass(type) {
        switch (type) {
            case 'street':
                return 'type-street-svg';
            case 'fence':
                return 'type-fence-svg';
            case 'security-room':
                return 'type-security-room';
            default:
                return `type-${type}`;
        }
    }

    function buildElementLabel(item) {
        if (isRoadLikeType(item.element_type)) {
            return '';
        }

        if (item.element_type === 'stall') {
            return `<i class="fa-solid fa-store"></i><br>${item.stall_code || 'SẠP'}`;
        }

        if (isIconOnlyType(item.element_type)) {
            return `<i class="${getIconForType(item.element_type)}"></i>`;
        }

        const icon = getIconForType(item.element_type);
        return `<i class="${icon}"></i><br>${item.element_name || ''}`;
    }

    // 2. Khởi tạo
    function init() {
        // Chuẩn hóa dữ liệu đường đi (SVG / waypoints / backward compat)
        elements.forEach(item => {
            if (item.waypoints && typeof item.waypoints === 'string') {
                try {
                    item.waypoints = JSON.parse(item.waypoints);
                } catch (e) {
                    item.waypoints = [];
                }
            }

            if (item.element_type === 'street-corner') {
                item.element_type = 'street';
                item.element_name = 'Đường đi';
                item.stroke_width = 24;
                item.waypoints = [
                    { x: parseInt(item.pos_x), y: parseInt(item.pos_y) + (parseInt(item.height) || 120)/2 },
                    { x: parseInt(item.pos_x) + (parseInt(item.width) || 120)/2, y: parseInt(item.pos_y) + (parseInt(item.height) || 120)/2 },
                    { x: parseInt(item.pos_x) + (parseInt(item.width) || 120)/2, y: parseInt(item.pos_y) }
                ];
            } else if (isRoadType(item.element_type) && (!item.waypoints || item.waypoints.length === 0)) {
                // Chuyển đổi rect sang 2 waypoints
                const w = parseInt(item.width) || 120;
                const h = parseInt(item.height) || (item.element_type === 'fence' ? 16 : 24);
                const x = parseInt(item.pos_x) || 100;
                const y = parseInt(item.pos_y) || 100;
                
                if (w >= h) {
                    item.waypoints = [
                        { x: x, y: y + h/2 },
                        { x: x + w, y: y + h/2 }
                    ];
                    item.stroke_width = h;
                } else {
                    item.waypoints = [
                        { x: x + w/2, y: y },
                        { x: x + w/2, y: y + h }
                    ];
                    item.stroke_width = w;
                }
            }
        });

        renderElements();
        setupInteractions();
        setupSearch();
    }

    // 3. Render các phần tử bản đồ số lên Grid
    function renderElements() {
        mapGrid.innerHTML = ''; // Clear

        elements.forEach(item => {
            const div = document.createElement('div');
            div.className = `map-element-public ${getElementTypeClass(item.element_type)}`;
            div.id = `pub-el-${item.id}`;
            div.classList.toggle('is-icon-only', isIconOnlyType(item.element_type));
            
            if (isRoadType(item.element_type)) {
                const isFence = item.element_type === 'fence';
                const strokeWidth = item.stroke_width || (isFence ? 16 : 24);
                const bbox = getStreetBoundingBox(item.waypoints, strokeWidth);
                
                div.style.left = `${bbox.x}px`;
                div.style.top = `${bbox.y}px`;
                div.style.width = `${bbox.w}px`;
                div.style.height = `${bbox.h}px`;
                
                const pad = strokeWidth / 2;
                const pointsStr = item.waypoints.map(pt => `${pt.x - bbox.minX + pad},${pt.y - bbox.minY + pad}`).join(' ');
                
                if (isFence) {
                    div.innerHTML = `
                        <svg width="100%" height="100%" class="fence-svg-container" style="overflow: visible;">
                            <polyline class="fence-bg" points="${pointsStr}" stroke="${item.color || '#64748b'}" stroke-width="${strokeWidth}" fill="none" />
                            <polyline class="fence-line" points="${pointsStr}" stroke="#cbd5e1" stroke-width="${Math.max(2, strokeWidth - 4)}" stroke-dasharray="10 8" fill="none" />
                            <polyline class="fence-core" points="${pointsStr}" stroke="#ffffff" stroke-width="2" fill="none" />
                        </svg>
                    `;
                } else {
                    div.innerHTML = `
                        <svg width="100%" height="100%" class="street-svg-container" style="overflow: visible;">
                            <polyline class="street-bg" points="${pointsStr}" stroke="${item.color || '#8d95a0'}" stroke-width="${strokeWidth}" fill="none" />
                            <polyline class="street-line" points="${pointsStr}" stroke-width="2" fill="none" />
                        </svg>
                    `;
                }
            } else {
                // Gán tọa độ, kích thước, xoay
                div.style.left = `${item.pos_x}px`;
                div.style.top = `${item.pos_y}px`;
                div.style.width = `${item.width}px`;
                div.style.height = `${item.height}px`;
                div.style.transform = `rotate(${item.rotation || 0}deg)`;
                div.style.fontSize = '';
                div.style.setProperty('--icon-size', '1em');
                div.style.setProperty('--icon-stretch-x', '1');
                div.style.setProperty('--icon-stretch-y', '1');

                // Nếu là sạp, tô màu theo trạng thái thực tế từ CSDL
                if (item.element_type === 'stall') {
                    const colorClass = item.color_class || (item.status_code ? `status-${item.status_code}` : 'status-white');
                    div.classList.add(colorClass);
                    
                    // Nhãn sạp
                    div.innerHTML = buildElementLabel(item);
                    
                    // Click xem chi tiết sạp
                    div.addEventListener('click', function (e) {
                        e.stopPropagation();
                        showStallTooltip(item, div);
                    });
                } else {
                    // Nhãn các tiện ích trang trí
                    div.innerHTML = buildElementLabel(item);
                    
                    // Áp dụng màu sắc nếu có cấu hình tự chọn
                    if (isIconOnlyType(item.element_type)) {
                        div.style.backgroundColor = 'transparent';
                        div.style.borderColor = 'transparent';
                        div.style.boxShadow = 'none';
                        div.style.color = item.color ? adjustColorBrightness(item.color, -45) : '';
                    } else if (item.color && !isRoadLikeType(item.element_type)) {
                        div.style.backgroundColor = item.color;
                        div.style.borderColor = adjustColorBrightness(item.color, -20);
                        div.style.color = getContrastColor(item.color);
                    }
                }
            }

            if (!isRoadLikeType(item.element_type)) {
                const areaScale = Math.sqrt(Math.max(1, (item.width || 0) * (item.height || 0)));
                const labelSize = Math.min(28, Math.max(12, Math.round(areaScale * 0.05)));
                const iconSize = Math.min(120, Math.max(18, Math.round(areaScale * 0.22)));
                div.style.fontSize = `${labelSize}px`;
                div.style.setProperty('--icon-size', `${iconSize}px`);
            }

            if (isIconOnlyType(item.element_type)) {
                const baseSize = 100;
                const scaleX = Math.max(0.1, (item.width || baseSize) / baseSize);
                const scaleY = Math.max(0.1, (item.height || baseSize) / baseSize);
                div.style.fontSize = '0';
                div.style.setProperty('--icon-size', `${baseSize}px`);
                div.style.setProperty('--icon-stretch-x', scaleX.toFixed(3));
                div.style.setProperty('--icon-stretch-y', scaleY.toFixed(3));
            }

            mapGrid.appendChild(div);
        });
    }

    // 4. Hiển thị tooltip chi tiết của sạp
    function showStallTooltip(item, dom) {
        // Tên sạp
        tooltipTitle.childNodes[0].textContent = item.stall_code + ' ';

        // Thẩm định huy hiệu trạng thái
        const status = item.status_code || 'empty';
        const statusName = item.status_name || 'Còn trống';
        
        tooltipStatusBadge.className = `badge-status ${status}`;
        tooltipStatusBadge.textContent = statusName;

        // Điền diện tích
        tooltipArea.textContent = `${item.area_size || 0} m²`;

        // Định dạng giá tiền
        const price = parseInt(item.base_price) || 0;
        tooltipPrice.textContent = price.toLocaleString('vi-VN') + ' đ';

        // Điền chủ hộ kinh doanh (nếu có)
        if (status === 'rented' && item.trader_name) {
            tooltipTraderRow.style.display = 'block';
            tooltipTrader.textContent = item.trader_name;
        } else {
            tooltipTraderRow.style.display = 'none';
        }

        // Hiện nút đăng ký trực tuyến nếu sạp còn trống
        if (status === 'empty') {
            tooltipActionRow.style.display = 'block';
            tooltipBtnRegister.href = window.BASE_URL + `home/register?stall_code=${encodeURIComponent(item.stall_code)}&area=${item.area_size}`;
        } else {
            tooltipActionRow.style.display = 'none';
        }

        // Tính toán vị trí tooltip: căn ngay phía trên sạp
        const left = item.pos_x + (item.width / 2) - 125; // Chiều rộng tooltip là 250px
        const top = item.pos_y - 170; // Đẩy lên trên sạp

        mapTooltip.style.left = `${left}px`;
        mapTooltip.style.top = `${top}px`;
        mapTooltip.style.display = 'block';

        // Xóa highlight cũ, chỉ highlight sạp đang xem
        clearHighlights();
        dom.classList.add('highlighted');
    }

    // Ẩn tooltip
    function hideTooltip() {
        mapTooltip.style.display = 'none';
        clearHighlights();
    }

    // Xóa hiệu ứng chớp sáng trên sạp
    function clearHighlights() {
        document.querySelectorAll('.map-element-public').forEach(el => {
            el.classList.remove('highlighted');
        });
    }

    // 5. Tìm kiếm và định vị sạp
    function setupSearch() {
        function doSearch() {
            const query = searchInput.value.trim().toUpperCase();
            if (!query) return;

            // Tìm sạp khớp với từ khóa
            const foundStall = elements.find(item => {
                const stallCode = (item.stall_code || '').toString();
                return item.element_type === 'stall' && 
                       (stallCode.toUpperCase().includes(query) || 
                        stallCode.replace(/[^0-9a-zA-Z]/g, '').toUpperCase().includes(query));
            });

            if (foundStall) {
                const dom = document.getElementById(`pub-el-${foundStall.id}`);
                if (dom) {
                    // Hiển thị tooltip
                    showStallTooltip(foundStall, dom);

                    // Di chuyển viewport để căn giữa sạp tìm thấy
                    centerOnElement(foundStall);
                }
            } else {
                alert(`Không tìm thấy sạp nào khớp với mã "${query}"`);
            }
        }

        btnSearchStall.addEventListener('click', doSearch);
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                doSearch();
            }
        });
    }

    // Di chuyển khung hình cuộn (Pan) để đưa phần tử vào tâm
    function centerOnElement(item) {
        const viewportW = mapViewport.clientWidth;
        const viewportH = mapViewport.clientHeight;

        // Vị trí tâm của phần tử trên Grid đã nhân với zoomLevel
        const targetX = (item.pos_x + item.width / 2) * zoomLevel;
        const targetY = (item.pos_y + item.height / 2) * zoomLevel;

        // Khoảng cuộn để căn giữa
        const scrollX = targetX - (viewportW / 2);
        const scrollY = targetY - (viewportH / 2);

        mapViewport.scrollTo({
            left: Math.max(0, scrollX),
            top: Math.max(0, scrollY),
            behavior: 'smooth'
        });
    }

    // 6. Xử lý Zoom & Pan
    function setupInteractions() {
        // Thu nhỏ
        document.getElementById('btn-zoom-out').addEventListener('click', function () {
            if (zoomLevel > minZoom) {
                zoomLevel = Math.max(minZoom, zoomLevel - zoomStep);
                applyZoom();
            }
        });

        // Phóng to
        document.getElementById('btn-zoom-in').addEventListener('click', function () {
            if (zoomLevel < maxZoom) {
                zoomLevel = Math.min(maxZoom, zoomLevel + zoomStep);
                applyZoom();
            }
        });

        // Reset thu phóng
        document.getElementById('btn-zoom-reset').addEventListener('click', function () {
            zoomLevel = 1.0;
            applyZoom();
            mapViewport.scrollLeft = 0;
            mapViewport.scrollTop = 0;
        });

        // Kéo cuộn (Pan) bằng cách giữ và kéo chuột trái
        mapViewport.addEventListener('mousedown', function (e) {
            if (e.target === mapViewport || e.target === mapGrid || e.target.classList.contains('map-element-public')) {
                // Nếu click vào sạp thì bỏ qua việc bắt đầu Pan để tránh xung đột kéo chuột
                if (e.target.classList.contains('map-element-public') || e.target.closest('.map-element-public')) {
                    // Tuy nhiên ta vẫn cho phép nếu người dùng click và giữ rê chuột kéo
                }
                
                isPanning = true;
                mapViewport.style.cursor = 'grabbing';
                startX = e.pageX - mapViewport.offsetLeft;
                startY = e.pageY - mapViewport.offsetTop;
                scrollLeft = mapViewport.scrollLeft;
                scrollTop = mapViewport.scrollTop;
            }
        });

        document.addEventListener('mousemove', function (e) {
            if (!isPanning) return;
            e.preventDefault();
            const x = e.pageX - mapViewport.offsetLeft;
            const y = e.pageY - mapViewport.offsetTop;
            const walkX = (x - startX);
            const walkY = (y - startY);
            mapViewport.scrollLeft = scrollLeft - walkX;
            mapViewport.scrollTop = scrollTop - walkY;
        });

        document.addEventListener('mouseup', function () {
            isPanning = false;
            mapViewport.style.cursor = 'grab';
        });

        // Click ra ngoài để ẩn tooltip
        mapViewport.addEventListener('click', function (e) {
            if (e.target === mapViewport || e.target === mapGrid) {
                hideTooltip();
            }
        });
    }

    // Áp dụng thu phóng
    function applyZoom() {
        mapGrid.style.transform = `scale(${zoomLevel})`;
        zoomBadge.textContent = `${Math.round(zoomLevel * 100)}%`;
        hideTooltip(); // Ẩn tooltip khi zoom để tránh lệch tọa độ hiển thị
    }

    // Helper: FontAwesome icons
    function getIconForType(type) {
        switch (type) {
            case 'stall': return 'fa-solid fa-store';
            case 'gate': return 'fa-solid fa-archway';
            case 'door': return 'fa-solid fa-door-open';
            case 'utility': return 'fa-solid fa-restroom';
            case 'office': return 'fa-solid fa-building-user';
            case 'security-room': return 'fa-solid fa-shield-halved';
            default: return 'fa-solid fa-draw-polygon';
        }
    }

    function getTypeNameVietnamese(type) {
        switch (type) {
            case 'street': return 'Đường đi';
            case 'fence': return 'Hàng rào';
            case 'security-room': return 'Phòng bảo vệ';
            case 'gate': return 'Cổng chợ';
            case 'door': return 'Cửa ra vào';
            case 'utility': return 'Tiện ích';
            case 'office': return 'Văn phòng';
            default: return 'Khác';
        }
    }

    // Helper: Điều chỉnh độ sáng tối
    function adjustColorBrightness(hex, percent) {
        let R = parseInt(hex.substring(1, 3), 16);
        let G = parseInt(hex.substring(3, 5), 16);
        let B = parseInt(hex.substring(5, 7), 16);

        R = parseInt(R * (100 + percent) / 100);
        G = parseInt(G * (100 + percent) / 100);
        B = parseInt(B * (100 + percent) / 100);

        R = (R < 255) ? R : 255;
        G = (G < 255) ? G : 255;
        B = (B < 255) ? B : 255;

        R = (R > 0) ? R : 0;
        G = (G > 0) ? G : 0;
        B = (B > 0) ? B : 0;

        const rHex = R.toString(16).padStart(2, '0');
        const gHex = G.toString(16).padStart(2, '0');
        const bHex = B.toString(16).padStart(2, '0');

        return `#${rHex}${gHex}${bHex}`;
    }

    // Helper: Độ tương phản màu chữ
    function getContrastColor(hexColor) {
        const r = parseInt(hexColor.substring(1, 3), 16);
        const g = parseInt(hexColor.substring(3, 5), 16);
        const b = parseInt(hexColor.substring(5, 7), 16);
        const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
        return (yiq >= 128) ? '#000000' : '#FFFFFF';
    }

    // Tính toán bounding box của đường đi từ waypoints
    function getStreetBoundingBox(waypoints, strokeWidth = 24) {
        if (!waypoints || waypoints.length === 0) {
            return { x: 0, y: 0, w: 40, h: 40, minX: 0, maxX: 0, minY: 0, maxY: 0 };
        }
        let minX = Infinity, maxX = -Infinity, minY = Infinity, maxY = -Infinity;
        waypoints.forEach(pt => {
            const px = parseFloat(pt.x);
            const py = parseFloat(pt.y);
            if (px < minX) minX = px;
            if (px > maxX) maxX = px;
            if (py < minY) minY = py;
            if (py > maxY) maxY = py;
        });
        
        const pad = strokeWidth / 2;
        const x = minX - pad;
        const y = minY - pad;
        const w = (maxX - minX) + strokeWidth;
        const h = (maxY - minY) + strokeWidth;
        
        return { x, y, w, h, minX, maxX, minY, maxY };
    }

    // Khởi động
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
