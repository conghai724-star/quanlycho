(function () {
    // 1. Khai báo các biến trạng thái sơ đồ
    let elements = []; // Chứa toàn bộ các phần tử trên bản đồ
    let selectedElement = null; // Phần tử đang được chọn
    let zoomLevel = 1.0; // Tỷ lệ thu phóng mặc định
    const zoomStep = 0.1;
    const minZoom = 0.5;
    const maxZoom = 2.0;

    let isPanning = false;
    let startX = 0;
    let startY = 0;
    let scrollLeft = 0;
    let scrollTop = 0;

    // Lịch sử thao tác (Undo / Redo)
    let undoStack = [];
    let redoStack = [];
    const MAX_HISTORY_STATES = 40;

    // Các phần tử DOM cần thao tác
    const canvasGrid = document.getElementById('canvas-grid');
    const canvasViewport = document.getElementById('canvas-viewport');
    const zoomValueText = document.getElementById('zoom-value');
    const selectionForm = document.getElementById('selection-form');
    const noSelectionMsg = document.getElementById('no-selection-msg');
    const chkSnapGrid = document.getElementById('chk-snap-grid');

    // Các ô input trong panel thuộc tính
    const propTypeName = document.getElementById('prop-type-name');
    const propName = document.getElementById('prop-name');
    const propStallId = document.getElementById('prop-stall-id');
    const propX = document.getElementById('prop-x');
    const propY = document.getElementById('prop-y');
    const propW = document.getElementById('prop-w');
    const propH = document.getElementById('prop-h');
    const propRotation = document.getElementById('prop-rotation');
    const propColor = document.getElementById('prop-color');
    const propColorHex = document.getElementById('prop-color-hex');
    const groupStallBinding = document.getElementById('group-stall-binding');
    const groupColorPicker = document.getElementById('group-color-picker');
    const HANDLE_MIN_SIZE = 24;

    function isRoadType(type) {
        return type === 'street' || type === 'fence';
    }

    function isRoadLikeType(type) {
        return isRoadType(type);
    }

    function isIconOnlyType(type) {
        return !isRoadLikeType(type);
    }

    function getDefaultPreset(type) {
        switch (type) {
            case 'street':
                return { width: 240, height: 24, color: '#8d95a0' };
            case 'fence':
                return { width: 240, height: 16, color: '#ddc9b0' };
            case 'security-room':
                return { width: 40, height: 40, color: '#dbeafe' };
            case 'gate':
                return { width: 40, height: 40, color: '#ffe0b2' };
            case 'door':
                return { width: 40, height: 40, color: '#d7ccc8' };
            case 'utility':
                return { width: 40, height: 40, color: '#e1bee7' };
            case 'office':
                return { width: 40, height: 40, color: '#e0f7fa' };
            default:
                return { width: 40, height: 40, color: null };
        }
    }

    function getElementTypeClass(type) {
        switch (type) {
            case 'street':
                return 'map-element-street-svg';
            case 'fence':
                return 'map-element-fence-svg';
            case 'security-room':
                return 'map-element-security-room';
            default:
                return `map-element-${type}`;
        }
    }

    function buildElementLabel(item) {
        if (isRoadLikeType(item.element_type)) {
            return '';
        }

        if (item.element_type === 'stall') {
            return `<i class="fa-solid fa-store"></i><br><strong>${item.stall_code || item.element_name || 'SẠP'}</strong>`;
        }

        if (isIconOnlyType(item.element_type)) {
            return `<i class="${getIconForType(item.element_type)}"></i>`;
        }

        const icon = getIconForType(item.element_type);
        return `<i class="${icon}"></i><br>${item.element_name || ''}`;
    }

    function syncElementContent(div, item) {
        if (isRoadType(item.element_type)) {
            const isFence = item.element_type === 'fence';
            const strokeWidth = item.stroke_width || (isFence ? 16 : 24);
            const bbox = getStreetBoundingBox(item.waypoints, strokeWidth);
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
            

            div.dataset.contentHtml = isFence ? 'fence-svg' : 'street-svg';
            return;
        }

        const labelHtml = buildElementLabel(item);
        const currentHtml = div.dataset.contentHtml || '';
        if (currentHtml === labelHtml) {
            return;
        }

        const handles = Array.from(div.querySelectorAll('.resize-handle, .rotate-handle'));
        handles.forEach(handle => handle.remove());

        div.innerHTML = labelHtml;
        div.dataset.contentHtml = labelHtml;

        handles.forEach(handle => div.appendChild(handle));
    }

    // 2. Hàm Khởi tạo
    function init() {
        loadMapData();
        setupCanvasInteractions();
        setupDragAndDrop();
        setupPropertiesForm();
        setupToolbarActions();
        setupUnmappedStallsSearch();
        setupKeyboardShortcuts();
    }

    // 3. Gọi API lấy dữ liệu sơ đồ từ Server
    function loadMapData() {
        App.utils.ajaxRequest('GET', 'api/getMapElements', null, function (response) {
            if (response.status === 200) {
                elements = response.data || [];
                
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

                renderAllElements();
            } else {
                App.utils.showToast('Không thể tải sơ đồ chợ: ' + response.message, 'danger');
            }
        });
    }

    // 4. Render toàn bộ danh sách phần tử lên Canvas
    function renderAllElements() {
        // Xóa sạch canvas cũ
        const oldElements = canvasGrid.querySelectorAll('.map-element');
        oldElements.forEach(el => el.remove());

        // Vẽ từng phần tử mới
        elements.forEach(item => {
            renderElement(item);
        });

        updateUnmappedStallsBadge();
    }

    // Hàm vẽ một phần tử đơn lẻ lên canvas
    function renderElement(item) {
        const div = document.createElement('div');
        div.className = `map-element ${getElementTypeClass(item.element_type)}`;
        div.id = `el-${item.id || item.temp_id}`;
        
        // Gán trạng thái màu sắc nếu là sạp
        if (item.element_type === 'stall') {
            const colorClass = item.color_class || (item.status_code ? `status-${item.status_code}` : 'status-white');
            div.classList.add(colorClass);
        }
        div.classList.toggle('is-icon-only', isIconOnlyType(item.element_type));

        // Nội dung hiển thị bên trong hình vẽ
        syncElementContent(div, item);
        updateElementDOM(div, item);

        if (!isRoadType(item.element_type)) {
            // Tay nắm thay đổi kích thước theo 4 phía + xoay trực tiếp
            ['n', 'e', 's', 'w'].forEach(position => {
                const resizeHandle = document.createElement('div');
                resizeHandle.className = `resize-handle handle-${position}`;
                resizeHandle.dataset.resize = position;
                div.appendChild(resizeHandle);
                resizeHandle.addEventListener('mousedown', function (e) {
                    e.stopPropagation();
                    e.preventDefault();
                    startResizingElement(e, item, div, position);
                });
            });

            const rotateHandle = document.createElement('div');
            rotateHandle.className = 'rotate-handle';
            rotateHandle.dataset.rotate = 'true';
            div.appendChild(rotateHandle);
            rotateHandle.addEventListener('mousedown', function (e) {
                e.stopPropagation();
                e.preventDefault();
                startRotatingElement(e, item, div);
            });
        }

        // Sự kiện click để chọn phần tử
        div.addEventListener('mousedown', function (e) {
            // Nếu click trúng tay nắm trực tiếp, waypoint handle hoặc midpoint handle thì bỏ qua để handler riêng xử lý
            if (e.target.closest('.resize-handle') || e.target.closest('.rotate-handle') || e.target.closest('.waypoint-handle') || e.target.closest('.midpoint-handle')) {
                return;
            }
            e.stopPropagation();
            selectElement(item);
            startDraggingElement(e, item, div);
        });

        canvasGrid.appendChild(div);
    }

    // Cập nhật CSS hiển thị cho phần tử trong DOM
    function updateElementDOM(div, item) {
        if (isRoadType(item.element_type)) {
            const isFence = item.element_type === 'fence';
            const strokeWidth = item.stroke_width || (isFence ? 16 : 24);
            const bbox = getStreetBoundingBox(item.waypoints, strokeWidth);
            
            div.style.left = `${bbox.x}px`;
            div.style.top = `${bbox.y}px`;
            div.style.width = `${bbox.w}px`;
            div.style.height = `${bbox.h}px`;
            div.style.transform = '';
            div.style.transformOrigin = '';
            div.style.fontSize = '';
            
            const pad = strokeWidth / 2;
            const pointsStr = item.waypoints.map(pt => `${pt.x - bbox.minX + pad},${pt.y - bbox.minY + pad}`).join(' ');
            
            if (isFence) {
                const fenceBg = div.querySelector('.fence-bg');
                const fenceLine = div.querySelector('.fence-line');
                const fenceCore = div.querySelector('.fence-core');
                if (fenceBg) {
                    fenceBg.setAttribute('points', pointsStr);
                    fenceBg.setAttribute('stroke-width', strokeWidth);
                    fenceBg.setAttribute('stroke', item.color || '#64748b');
                }
                if (fenceLine) {
                    fenceLine.setAttribute('points', pointsStr);
                    fenceLine.setAttribute('stroke-width', Math.max(2, strokeWidth - 4));
                }
                if (fenceCore) {
                    fenceCore.setAttribute('points', pointsStr);
                }
            } else {
                const streetBg = div.querySelector('.street-bg');
                const streetLine = div.querySelector('.street-line');
                if (streetBg) {
                    streetBg.setAttribute('points', pointsStr);
                    streetBg.setAttribute('stroke-width', strokeWidth);
                    streetBg.setAttribute('stroke', item.color || '#8d95a0');
                }
                if (streetLine) {
                    streetLine.setAttribute('points', pointsStr);
                }
            }
            
            // Sync bounding box coordinates to model
            item.pos_x = bbox.x;
            item.pos_y = bbox.y;
            item.width = bbox.w;
            item.height = bbox.h;
            item.rotation = 0;
            return;
        }

        div.style.left = `${item.pos_x}px`;
        div.style.top = `${item.pos_y}px`;
        div.style.width = `${item.width}px`;
        div.style.height = `${item.height}px`;
        div.style.transform = `rotate(${item.rotation || 0}deg)`;
        div.style.transformOrigin = 'center center';
        div.style.fontSize = '';
        div.style.setProperty('--icon-size', '1em');
        div.style.setProperty('--icon-stretch-x', '1');
        div.style.setProperty('--icon-stretch-y', '1');
        
        // Màu sắc tự chọn
        if (isIconOnlyType(item.element_type)) {
            div.style.backgroundColor = 'transparent';
            div.style.borderColor = 'transparent';
            div.style.boxShadow = '';
            div.style.color = item.color ? adjustColorBrightness(item.color, -45) : '';
        } else if (item.element_type !== 'stall' && item.color) {
            div.style.backgroundColor = item.color;
            div.style.borderColor = adjustColorBrightness(item.color, -28);
            div.style.color = getContrastColor(item.color);
        } else if (!isRoadLikeType(item.element_type)) {
            div.style.backgroundColor = '';
            div.style.borderColor = '';
            div.style.boxShadow = '';
            div.style.color = '';
        }

        if (item.element_type === 'stall') {
            div.classList.remove('status-white', 'status-green', 'status-yellow', 'status-red', 'status-orange', 'status-blue', 'status-gray', 'status-rented', 'status-empty', 'status-repairing', 'status-locked');
            const colorClass = item.color_class || (item.status_code ? `status-${item.status_code}` : 'status-white');
            div.classList.add(colorClass);
        }

        syncElementContent(div, item);

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
    }

    // 5. Chọn và hiển thị thuộc tính
    function selectElement(item) {
        selectedElement = item;
        
        // Đánh dấu viền nét đứt cho phần tử được chọn
        canvasGrid.querySelectorAll('.map-element').forEach(el => el.classList.remove('selected'));
        const activeDom = document.getElementById(`el-${item.id || item.temp_id}`);
        if (activeDom) {
            activeDom.classList.add('selected');
        }

        // Hiển thị Panel thuộc tính bên phải
        noSelectionMsg.style.display = 'none';
        selectionForm.style.display = 'block';

        // Điền dữ liệu vào form thuộc tính
        propTypeName.value = getTypeNameVietnamese(item.element_type);
        propName.value = item.element_name || '';
        propX.value = Math.round(item.pos_x);
        propY.value = Math.round(item.pos_y);
        propW.value = Math.round(item.width);
        propH.value = Math.round(item.height);
        propRotation.value = item.rotation || 0;

        // Toggle panel inputs based on element type
        if (isRoadType(item.element_type)) {
            document.getElementById('group-size-dimensions').style.display = 'none';
            document.getElementById('group-rotation-container').style.display = 'none';
            document.getElementById('group-stroke-width').style.display = 'block';
            document.getElementById('prop-stroke-width').value = item.stroke_width || (item.element_type === 'fence' ? 16 : 24);
        } else {
            document.getElementById('group-size-dimensions').style.display = 'grid';
            document.getElementById('group-rotation-container').style.display = 'block';
            document.getElementById('group-stroke-width').style.display = 'none';
        }

        // Trạng thái hiển thị dropdown chọn sạp
        if (item.element_type === 'stall') {
            groupStallBinding.style.display = 'block';
            groupColorPicker.style.display = 'none';
            propStallId.value = item.stall_id || '';
            updateStallInfoCard(item);
        } else {
            groupStallBinding.style.display = 'none';
            groupColorPicker.style.display = 'block';
            propColor.value = item.color || '#eceff1';
            propColorHex.value = item.color || '#eceff1';
            updateStallInfoCard(null);
        }

        // Vẽ các waypoint handles của đường đi
        renderWaypointHandles(item);
    }

    // Bỏ chọn phần tử
    function deselectElement() {
        selectedElement = null;
        canvasGrid.querySelectorAll('.map-element').forEach(el => el.classList.remove('selected'));
        noSelectionMsg.style.display = 'block';
        selectionForm.style.display = 'none';
        updateStallInfoCard(null);
        renderWaypointHandles(null); // Xóa handles
    }

    // Cập nhật thẻ thông tin sạp dưới nút xóa
    function updateStallInfoCard(item) {
        const panel = document.getElementById('stall-info-panel');
        if (!panel) return;

        if (!item || item.element_type !== 'stall') {
            panel.style.display = 'none';
            return;
        }

        const stallId = item.stall_id;
        if (!stallId) {
            panel.style.display = 'none';
            return;
        }

        // Tìm thông tin sạp từ DB_STALLS (hoặc trực tiếp trong item nếu có sẵn)
        let details = null;
        if (window.DB_STALLS) {
            details = window.DB_STALLS.find(s => s.id == stallId);
        }
        
        // Fallback sang thông tin lưu trên item nếu không tìm thấy trong DB_STALLS
        if (!details) {
            details = {
                stall_type: item.stall_type,
                area_name: item.area_name,
                block: item.block,
                lot: item.lot,
                area_size: item.area_size,
                base_price: item.base_price,
                status_name: item.status_name || 'Còn trống',
                status_code: item.status_code || 'empty',
                trader_name: item.trader_name,
                trader_phone: item.trader_phone,
                contract_number: item.contract_number,
                contract_end_date: item.contract_end_date
            };
        }

        if (details) {
            panel.style.display = 'block';
            
            // Loại sạp & Vị trí
            document.getElementById('stall-info-type').textContent = details.stall_type || 'Quầy hàng';
            
            let location = details.area_name || '--';
            if (details.block) location += ' - Dãy ' + details.block;
            if (details.lot) location += ' - Lô ' + details.lot;
            document.getElementById('stall-info-area-name').textContent = location;

            document.getElementById('stall-info-area').textContent = (details.area_size || '--') + ' m²';
            
            const price = parseInt(details.base_price) || 0;
            document.getElementById('stall-info-price').textContent = price > 0 ? price.toLocaleString('vi-VN') + ' đ' : '--';
            
            const statusBadge = document.getElementById('stall-info-status');
            const statusCode = details.status_code || 'empty';
            statusBadge.className = `badge badge-status-${statusCode}`;
            statusBadge.textContent = details.status_name || 'Còn trống';
            
            const traderRow = document.getElementById('stall-info-trader-row');
            if (statusCode === 'rented' && details.trader_name) {
                traderRow.style.display = 'flex';
                document.getElementById('stall-info-trader').textContent = details.trader_name;
                document.getElementById('stall-info-phone').textContent = details.trader_phone || '--';
                document.getElementById('stall-info-contract').textContent = details.contract_number || '--';
                
                // Định dạng ngày thuê DD/MM/YYYY
                let dateStr = '--';
                if (details.contract_end_date) {
                    const parts = details.contract_end_date.split('-');
                    if (parts.length === 3) dateStr = `${parts[2]}/${parts[1]}/${parts[0]}`;
                    else dateStr = details.contract_end_date;
                }
                document.getElementById('stall-info-contract-end').textContent = dateStr;
            } else {
                traderRow.style.display = 'none';
            }
        } else {
            panel.style.display = 'none';
        }
    }

    // 6. Xử lý Kéo thả vẽ mới (Drag & Drop)
    function setupDragAndDrop() {
        // Thiết lập sự kiện dragstart cho các nút kéo thả ở Toolbox bên trái
        const toolboxItems = document.querySelectorAll('.toolbox-item');
        toolboxItems.forEach(item => {
            item.addEventListener('dragstart', function (e) {
                e.dataTransfer.setData('action', 'create-basic');
                e.dataTransfer.setData('type', item.getAttribute('data-type'));
            });
        });

        // Thiết lập sự kiện dragstart cho các sạp chưa gán ở panel trái
        const unmappedList = document.getElementById('unmapped-stalls-list');
        unmappedList.addEventListener('dragstart', function (e) {
            const stallItem = e.target.closest('.unmapped-stall-item');
            if (stallItem) {
                e.dataTransfer.setData('action', 'create-stall');
                e.dataTransfer.setData('stall-id', stallItem.getAttribute('data-stall-id'));
                e.dataTransfer.setData('stall-code', stallItem.getAttribute('data-stall-code'));
            }
        });

        // Cho phép kéo trên vùng canvas
        canvasGrid.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
        });

        // Xử lý khi thả xuống (Drop)
        canvasGrid.addEventListener('drop', function (e) {
            e.preventDefault();
            
            const action = e.dataTransfer.getData('action');
            if (!action) return;

            // Tính toán tọa độ thả chuẩn xác theo tỷ lệ thu phóng (Zoom)
            const rect = canvasGrid.getBoundingClientRect();
            let x = (e.clientX - rect.left) / zoomLevel;
            let y = (e.clientY - rect.top) / zoomLevel;

            // Hỗ trợ snap to grid nếu được tích hợp
            if (chkSnapGrid.checked) {
                x = Math.round(x / 20) * 20;
                y = Math.round(y / 20) * 20;
            }

            const tempId = 'temp-' + Date.now();
            let newElement = {
                temp_id: tempId,
                pos_x: x,
                pos_y: y,
                width: 40,
                height: 40,
                rotation: 0,
                color: null
            };

            if (action === 'create-basic') {
                const type = e.dataTransfer.getData('type');
                newElement.element_type = type;
                newElement.element_name = getTypeNameVietnamese(type);
                const preset = getDefaultPreset(type);
                
                if (isRoadType(type)) {
                    newElement.stroke_width = (type === 'fence') ? 16 : 24;
                    newElement.waypoints = [
                        { x: x, y: y },
                        { x: x + 120, y: y }
                    ];
                    const bbox = getStreetBoundingBox(newElement.waypoints, newElement.stroke_width);
                    newElement.pos_x = bbox.x;
                    newElement.pos_y = bbox.y;
                    newElement.width = bbox.w;
                    newElement.height = bbox.h;
                } else {
                    newElement.width = preset.width;
                    newElement.height = preset.height;
                }
                newElement.color = preset.color;
            } else if (action === 'create-stall') {
                const stallId = e.dataTransfer.getData('stall-id');
                const stallCode = e.dataTransfer.getData('stall-code');

                newElement.element_type = 'stall';
                newElement.stall_id = stallId;
                newElement.stall_code = stallCode;
                newElement.element_name = stallCode;

                // Đồng bộ thông tin sạp từ DB_STALLS vào newElement
                if (window.DB_STALLS) {
                    const details = window.DB_STALLS.find(s => s.id == stallId);
                    if (details) {
                        newElement.stall_type = details.stall_type;
                        newElement.area_name = details.area_name;
                        newElement.block = details.block;
                        newElement.lot = details.lot;
                        newElement.area_size = details.area_size;
                        newElement.base_price = details.base_price;
                        newElement.status_name = details.status_name;
                        newElement.status_code = details.status_code;
                        newElement.color_class = details.color_class;
                        newElement.trader_name = details.trader_name;
                        newElement.trader_phone = details.trader_phone;
                        newElement.contract_number = details.contract_number;
                        newElement.contract_end_date = details.contract_end_date;
                    }
                }

                // Xóa sạp khỏi danh sách chưa gán ở cột trái
                const stallDom = document.querySelector(`.unmapped-stall-item[data-stall-id="${stallId}"]`);
                if (stallDom) stallDom.remove();
            }

            recordState();
            elements.push(newElement);
            renderElement(newElement);
            selectElement(newElement);
            updateUnmappedStallsBadge();
        });
    }

    // 7. Xử lý di chuyển kéo lê phần tử (Drag Move) trên Grid Canvas
    function startDraggingElement(e, item, div) {
        e.preventDefault();
        
        const startClientX = e.clientX;
        const startClientY = e.clientY;
        const initialX = item.pos_x;
        const initialY = item.pos_y;
        const initialWaypoints = isRoadType(item.element_type) ? JSON.parse(JSON.stringify(item.waypoints)) : null;
        let hasRecorded = false;

        function onMouseMove(moveEvent) {
            if (!hasRecorded) {
                recordState();
                hasRecorded = true;
            }
            // Tính khoảng cách di dời dựa trên zoomLevel
            const dx = (moveEvent.clientX - startClientX) / zoomLevel;
            const dy = (moveEvent.clientY - startClientY) / zoomLevel;

            let newX = initialX + dx;
            let newY = initialY + dy;

            // Bắt dính lưới ô vuông (Snap-to-grid 20px)
            if (chkSnapGrid.checked) {
                newX = Math.round(newX / 20) * 20;
                newY = Math.round(newY / 20) * 20;
            }

            const actualDx = newX - initialX;
            const actualDy = newY - initialY;

            if (isRoadType(item.element_type) && initialWaypoints) {
                // Di chuyển toàn bộ các điểm của đường đi theo khoảng cách thực tế
                item.waypoints.forEach((pt, idx) => {
                    pt.x = initialWaypoints[idx].x + actualDx;
                    pt.y = initialWaypoints[idx].y + actualDy;
                });
                updateElementDOM(div, item);
                renderWaypointHandles(item);
            } else {
                // Cập nhật tọa độ vào dữ liệu gốc
                item.pos_x = newX;
                item.pos_y = newY;

                // Cập nhật giao diện
                div.style.left = `${newX}px`;
                div.style.top = `${newY}px`;
            }

            // Cập nhật ô nhập tọa độ nếu đang hiển thị thuộc tính của nó
            if (selectedElement === item) {
                propX.value = Math.round(item.pos_x);
                propY.value = Math.round(item.pos_y);
            }
        }

        function onMouseUp() {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        }

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    }



    // 8. Co giãn kích thước trực tiếp bằng 4 phía (Resize Element)
    function startResizingElement(e, item, div, handlePosition) {
        const startClientX = e.clientX;
        const startClientY = e.clientY;
        const initialWidth = item.width;
        const initialHeight = item.height;
        const initialX = item.pos_x;
        const initialY = item.pos_y;
        let hasRecorded = false;

        function onMouseMove(moveEvent) {
            if (!hasRecorded) {
                recordState();
                hasRecorded = true;
            }
            const dx = (moveEvent.clientX - startClientX) / zoomLevel;
            const dy = (moveEvent.clientY - startClientY) / zoomLevel;

            let newW = initialWidth;
            let newH = initialHeight;
            let newX = initialX;
            let newY = initialY;

            if (handlePosition === 'e') {
                newW = initialWidth + dx;
            }
            if (handlePosition === 's') {
                newH = initialHeight + dy;
            }
            if (handlePosition === 'w') {
                newW = initialWidth - dx;
                newX = initialX + dx;
            }
            if (handlePosition === 'n') {
                newH = initialHeight - dy;
                newY = initialY + dy;
            }

            // Giới hạn nhỏ nhất 24px
            if (newW < HANDLE_MIN_SIZE) {
                if (handlePosition === 'w') {
                    newX -= HANDLE_MIN_SIZE - newW;
                }
                newW = HANDLE_MIN_SIZE;
            }
            if (newH < HANDLE_MIN_SIZE) {
                if (handlePosition === 'n') {
                    newY -= HANDLE_MIN_SIZE - newH;
                }
                newH = HANDLE_MIN_SIZE;
            }

            if (chkSnapGrid.checked) {
                newW = Math.round(newW / 20) * 20;
                newH = Math.round(newH / 20) * 20;
                newX = Math.round(newX / 20) * 20;
                newY = Math.round(newY / 20) * 20;
            }

            item.pos_x = newX;
            item.pos_y = newY;
            if (chkSnapGrid.checked) {
                newW = Math.max(HANDLE_MIN_SIZE, newW);
                newH = Math.max(HANDLE_MIN_SIZE, newH);
            }

            item.width = newW;
            item.height = newH;

            updateElementDOM(div, item);

            if (selectedElement === item) {
                propX.value = newX;
                propY.value = newY;
                propW.value = newW;
                propH.value = newH;
            }
        }

        function onMouseUp() {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        }

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    }

    function startRotatingElement(e, item, div) {
        const rect = div.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;
        const startAngle = Math.atan2(e.clientY - centerY, e.clientX - centerX);
        const initialRotation = item.rotation || 0;
        let hasRecorded = false;

        function onMouseMove(moveEvent) {
            if (!hasRecorded) {
                recordState();
                hasRecorded = true;
            }
            const currentAngle = Math.atan2(moveEvent.clientY - centerY, moveEvent.clientX - centerX);
            let rotation = initialRotation + ((currentAngle - startAngle) * 180 / Math.PI);
            rotation = ((rotation % 360) + 360) % 360;
            rotation = Math.round(rotation);

            item.rotation = rotation;
            div.style.transform = `rotate(${rotation}deg)`;

            if (selectedElement === item) {
                propRotation.value = rotation;
            }
        }

        function onMouseUp() {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
        }

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    }

    // 9. Lắng nghe và thay đổi thuộc tính ở cột bên phải
    function setupPropertiesForm() {
        // Record state when inputs are focused (so we capture state before edit)
        const propStrokeWidth = document.getElementById('prop-stroke-width');
        [propName, propX, propY, propW, propH, propRotation, propColor, propColorHex, propStrokeWidth].forEach(input => {
            if (input) {
                input.addEventListener('focus', function () {
                    recordState();
                });
            }
        });

        if (propStrokeWidth) {
            propStrokeWidth.addEventListener('change', function () {
                if (selectedElement && isRoadType(selectedElement.element_type)) {
                    selectedElement.stroke_width = parseInt(propStrokeWidth.value) || (selectedElement.element_type === 'fence' ? 16 : 24);
                    const div = document.getElementById(`el-${selectedElement.id || selectedElement.temp_id}`);
                    if (div) updateElementDOM(div, selectedElement);
                }
            });
        }

        // Tên hiển thị thay đổi
        propName.addEventListener('input', function () {
            if (selectedElement) {
                selectedElement.element_name = propName.value;
                const div = document.getElementById(`el-${selectedElement.id || selectedElement.temp_id}`);
                if (div) updateElementDOM(div, selectedElement);
            }
        });

        // Liên kết Sạp thay đổi
        propStallId.addEventListener('change', function () {
            if (selectedElement && selectedElement.element_type === 'stall') {
                recordState();
                const newStallId = propStallId.value;
                
                // Trả lại sạp cũ nếu có về list chưa gán
                const oldStallId = selectedElement.stall_id;
                if (oldStallId && oldStallId !== newStallId) {
                    addStallBackToUnmapped(oldStallId, selectedElement.stall_code);
                }

                if (newStallId) {
                    selectedElement.stall_id = newStallId;
                    const opt = propStallId.options[propStallId.selectedIndex];
                    selectedElement.stall_code = opt.text;
                    selectedElement.element_name = opt.text;
                    
                    // Cập nhật thông tin sạp vào cache của selectedElement từ DB_STALLS
                    if (window.DB_STALLS) {
                        const details = window.DB_STALLS.find(s => s.id == newStallId);
                        if (details) {
                            selectedElement.stall_type = details.stall_type;
                            selectedElement.area_name = details.area_name;
                            selectedElement.block = details.block;
                            selectedElement.lot = details.lot;
                            selectedElement.area_size = details.area_size;
                            selectedElement.base_price = details.base_price;
                            selectedElement.status_name = details.status_name;
                            selectedElement.status_code = details.status_code;
                            selectedElement.trader_name = details.trader_name;
                            selectedElement.trader_phone = details.trader_phone;
                            selectedElement.contract_number = details.contract_number;
                            selectedElement.contract_end_date = details.contract_end_date;
                        }
                    }
                    
                    // Xóa sạp mới khỏi danh sách cột trái
                    const stallDom = document.querySelector(`.unmapped-stall-item[data-stall-id="${newStallId}"]`);
                    if (stallDom) stallDom.remove();
                } else {
                    selectedElement.stall_id = null;
                    selectedElement.stall_code = null;
                    selectedElement.element_name = 'SẠP';
                    selectedElement.stall_type = null;
                    selectedElement.area_name = null;
                    selectedElement.block = null;
                    selectedElement.lot = null;
                    selectedElement.area_size = null;
                    selectedElement.base_price = null;
                    selectedElement.status_name = null;
                    selectedElement.status_code = null;
                    selectedElement.trader_name = null;
                    selectedElement.trader_phone = null;
                    selectedElement.contract_number = null;
                    selectedElement.contract_end_date = null;
                }

                const div = document.getElementById(`el-${selectedElement.id || selectedElement.temp_id}`);
                if (div) updateElementDOM(div, selectedElement);
                updateUnmappedStallsBadge();
                updateStallInfoCard(selectedElement);
            }
        });

        // X, Y thay đổi
        [propX, propY].forEach(input => {
            input.addEventListener('change', function () {
                if (selectedElement) {
                    let val = parseInt(input.value) || 0;
                    if (chkSnapGrid.checked) {
                        val = Math.round(val / 20) * 20;
                        input.value = val;
                    }
                    if (input === propX) selectedElement.pos_x = val;
                    if (input === propY) selectedElement.pos_y = val;

                    const div = document.getElementById(`el-${selectedElement.id || selectedElement.temp_id}`);
                    if (div) updateElementDOM(div, selectedElement);
                }
            });
        });

        // W, H thay đổi
        [propW, propH].forEach(input => {
            input.addEventListener('change', function () {
                if (selectedElement) {
                    let val = parseInt(input.value) || 20;
                    val = Math.max(20, val);
                    if (chkSnapGrid.checked) {
                        val = Math.round(val / 20) * 20;
                        input.value = val;
                    }
                    if (input === propW) selectedElement.width = val;
                    if (input === propH) selectedElement.height = val;

                    const div = document.getElementById(`el-${selectedElement.id || selectedElement.temp_id}`);
                    if (div) updateElementDOM(div, selectedElement);
                }
            });
        });

        // Góc xoay thay đổi
        propRotation.addEventListener('change', function () {
            if (selectedElement) {
                selectedElement.rotation = parseInt(propRotation.value) || 0;
                const div = document.getElementById(`el-${selectedElement.id || selectedElement.temp_id}`);
                if (div) updateElementDOM(div, selectedElement);
            }
        });

        // Màu nền chọn bảng màu
        propColor.addEventListener('input', function () {
            if (selectedElement && selectedElement.element_type !== 'stall') {
                selectedElement.color = propColor.value;
                propColorHex.value = propColor.value;
                const div = document.getElementById(`el-${selectedElement.id || selectedElement.temp_id}`);
                if (div) updateElementDOM(div, selectedElement);
            }
        });

        // Màu nền tự gõ Hex code
        propColorHex.addEventListener('input', function () {
            if (selectedElement && selectedElement.element_type !== 'stall') {
                const hex = propColorHex.value;
                if (/^#[0-9A-F]{6}$/i.test(hex)) {
                    selectedElement.color = hex;
                    propColor.value = hex;
                    const div = document.getElementById(`el-${selectedElement.id || selectedElement.temp_id}`);
                    if (div) updateElementDOM(div, selectedElement);
                }
            }
        });

        // Xóa một phần tử khỏi bản đồ
        document.getElementById('btn-delete-element').addEventListener('click', function () {
            deleteSelectedElement(true);
        });
    }

    // 10. Toolbar thu phóng, pan và các nút chính
    function setupToolbarActions() {
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
            canvasViewport.scrollLeft = 0;
            canvasViewport.scrollTop = 0;
        });

        // Xóa sạch bản đồ
        document.getElementById('btn-clear-map').addEventListener('click', function () {
            if (confirm('LƯU Ý: Thao tác này sẽ xóa sạch toàn bộ sơ đồ hiện tại và giải phóng tất cả sạp. Bạn có chắc chắn muốn xóa hết?')) {
                // Đưa toàn bộ sạp trở lại list chưa gán
                elements.forEach(item => {
                    if (item.element_type === 'stall' && item.stall_id) {
                        addStallBackToUnmapped(item.stall_id, item.stall_code);
                    }
                });

                elements = [];
                renderAllElements();
                deselectElement();
            }
        });

        // Lưu sơ đồ lên server qua API
        document.getElementById('btn-save-map').addEventListener('click', function () {
            const dataToSave = {
                elements: elements.map(item => {
                    return {
                        element_type: item.element_type,
                        element_name: item.element_name,
                        stall_id: item.stall_id,
                        pos_x: Math.round(item.pos_x),
                        pos_y: Math.round(item.pos_y),
                        width: Math.round(item.width),
                        height: Math.round(item.height),
                        rotation: item.rotation,
                        color: item.color,
                        waypoints: item.waypoints ? JSON.stringify(item.waypoints) : null,
                        stroke_width: item.stroke_width || null
                    };
                })
            };

            App.utils.ajaxRequest('POST', 'api/saveMapElements', dataToSave, function (response) {
                if (response.status === 200) {
                    App.utils.showToast('Lưu sơ đồ chợ thành công!', 'success');
                    // Tải lại dữ liệu để nhận các ID thật từ database
                    loadMapData();
                } else {
                    App.utils.showToast('Lưu sơ đồ thất bại: ' + response.message, 'danger');
                }
            });
        });
    }

    // Áp dụng tỷ lệ thu phóng vào CSS transform
    function applyZoom() {
        canvasGrid.style.transform = `scale(${zoomLevel})`;
        zoomValueText.textContent = `${Math.round(zoomLevel * 100)}%`;
    }

    // Thiết lập tính năng kéo cuộn màn hình (Pan) trên canvas
    function setupCanvasInteractions() {
        canvasViewport.addEventListener('mousedown', function (e) {
            // Chỉ Pan khi click chuột giữa, hoặc click lên vùng trống của viewport/grid
            if (e.button === 1 || e.target === canvasViewport || e.target === canvasGrid) {
                isPanning = true;
                canvasViewport.style.cursor = 'grabbing';
                startX = e.pageX - canvasViewport.offsetLeft;
                startY = e.pageY - canvasViewport.offsetTop;
                scrollLeft = canvasViewport.scrollLeft;
                scrollTop = canvasViewport.scrollTop;
            }
        });

        document.addEventListener('mousemove', function (e) {
            if (!isPanning) return;
            e.preventDefault();
            const x = e.pageX - canvasViewport.offsetLeft;
            const y = e.pageY - canvasViewport.offsetTop;
            const walkX = (x - startX); // Tốc độ cuộn 1x
            const walkY = (y - startY);
            canvasViewport.scrollLeft = scrollLeft - walkX;
            canvasViewport.scrollTop = scrollTop - walkY;
        });

        document.addEventListener('mouseup', function () {
            isPanning = false;
            canvasViewport.style.cursor = 'grab';
        });

        // Click ra ngoài khoảng trống canvas để hủy chọn phần tử
        canvasViewport.addEventListener('click', function (e) {
            if (e.target === canvasViewport || e.target === canvasGrid) {
                deselectElement();
            }
        });
    }

    // 11. Các hàm Helper phụ trợ
    function addStallBackToUnmapped(stallId, stallCode) {
        // Kiểm tra xem đã có sẵn trong danh sách chưa gán chưa để tránh trùng lặp
        const exist = document.querySelector(`.unmapped-stall-item[data-stall-id="${stallId}"]`);
        if (exist) return;

        const list = document.getElementById('unmapped-stalls-list');
        
        // Xóa thông báo trống nếu có
        const emptyMsg = list.querySelector('p');
        if (emptyMsg) emptyMsg.remove();

        const div = document.createElement('div');
        div.className = 'unmapped-stall-item';
        div.setAttribute('data-stall-id', stallId);
        div.setAttribute('data-stall-code', stallCode);
        div.setAttribute('draggable', 'true');
        
        // Thử tìm diện tích trong window.DB_STALLS nếu có
        let area = 10;
        if (window.DB_STALLS) {
            const found = window.DB_STALLS.find(s => parseInt(s.id) === parseInt(stallId));
            if (found && found.area_size) area = found.area_size;
        }

        div.innerHTML = `
            <div>
                <i class="fa-solid fa-store" style="margin-right: 6px; color: var(--primary);"></i>
                <strong style="font-size: 12px;>${stallCode}</strong>
            </div>
            <span style="font-size: 10px; color: var(--text-muted);">${area} m²</span>
        `;
        list.appendChild(div);
    }

    // Cập nhật số lượng sạp chưa gán hiển thị ở góc
    function updateUnmappedStallsBadge() {
        const count = document.querySelectorAll('#unmapped-stalls-list .unmapped-stall-item').length;
        document.getElementById('unmapped-count').textContent = count;

        const list = document.getElementById('unmapped-stalls-list');
        if (count === 0 && !list.querySelector('p')) {
            list.innerHTML = `<p style="font-size: 12px; color: var(--text-muted); text-align: center; margin-top: 10px;">Đã đưa tất cả sạp lên bản đồ!</p>`;
        }
    }

    // Tìm kiếm sạp chưa gán nhanh
    function setupUnmappedStallsSearch() {
        const searchInput = document.getElementById('unmapped-search');
        searchInput.addEventListener('input', function () {
            const query = searchInput.value.toLowerCase().trim();
            const items = document.querySelectorAll('#unmapped-stalls-list .unmapped-stall-item');
            
            items.forEach(item => {
                const code = item.getAttribute('data-stall-code').toLowerCase();
                if (code.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }

    // Lấy icon FontAwesome cho từng loại phần tử vẽ
    function getIconForType(type) {
        switch (type) {
            case 'stall': return 'fa-solid fa-store';
            case 'street': return 'fa-solid fa-road';
            case 'gate': return 'fa-solid fa-archway';
            case 'door': return 'fa-solid fa-door-open';
            case 'utility': return 'fa-solid fa-restroom';
            case 'office': return 'fa-solid fa-building-user';
            case 'security-room': return 'fa-solid fa-shield-halved';
            default: return 'fa-solid fa-draw-polygon';
        }
    }

    // Đổi tên loại phần tử sang Tiếng Việt
    function getTypeNameVietnamese(type) {
        switch (type) {
            case 'stall': return 'Sạp Chợ';
            case 'street': return 'Đường đi';
            case 'gate': return 'Cổng Chợ';
            case 'door': return 'Cửa Ra Vào';
            case 'utility': return 'Nhà Vệ Sinh / Tiện ích';
            case 'fence': return 'Hàng rào';
            case 'security-room': return 'Phòng bảo vệ';
            case 'office': return 'Văn Phòng BQL';
            default: return 'Khác';
        }
    }

    // Hàm điều chỉnh độ sáng tối của mã HEX màu
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

    // Lấy màu chữ tương phản (Đen hoặc Trắng) dựa trên độ sáng của màu nền
    function getContrastColor(hexColor) {
        const r = parseInt(hexColor.substring(1, 3), 16);
        const g = parseInt(hexColor.substring(3, 5), 16);
        const b = parseInt(hexColor.substring(5, 7), 16);
        const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
        return (yiq >= 128) ? '#000000' : '#FFFFFF';
    }

    // Xóa một phần tử khỏi bản đồ
    function deleteSelectedElement(showConfirm = false) {
        if (!selectedElement) return;

        if (showConfirm && !confirm('Bạn chắc chắn muốn xóa phần tử này khỏi sơ đồ?')) {
            return;
        }

        recordState(); // Lưu lịch sử trước khi xóa

        // Nếu là sạp chợ, trả lại vào danh sách chưa gán
        if (selectedElement.element_type === 'stall' && selectedElement.stall_id) {
            addStallBackToUnmapped(selectedElement.stall_id, selectedElement.stall_code);
        }

        // Loại bỏ khỏi danh sách elements
        const index = elements.indexOf(selectedElement);
        if (index > -1) {
            elements.splice(index, 1);
        }

        // Xóa khỏi DOM
        const div = document.getElementById(`el-${selectedElement.id || selectedElement.temp_id}`);
        if (div) div.remove();

        deselectElement();
        updateUnmappedStallsBadge();
        App.utils.showToast('Đã xóa phần tử thành công!', 'info');
    }

    // Ghi lại trạng thái hiện tại vào Undo Stack trước khi thay đổi
    function recordState() {
        const stateCopy = JSON.parse(JSON.stringify(elements));
        undoStack.push(stateCopy);
        if (undoStack.length > MAX_HISTORY_STATES) {
            undoStack.shift();
        }
        redoStack = []; // Reset Redo khi có thao tác mới
    }

    // Hoàn tác hành động trước đó (Ctrl + Z)
    function undo() {
        if (undoStack.length === 0) {
            App.utils.showToast('Không có gì để hoàn tác!', 'warning');
            return;
        }

        const currentStateCopy = JSON.parse(JSON.stringify(elements));
        redoStack.push(currentStateCopy);

        elements = undoStack.pop();
        
        deselectElement();
        renderAllElements();
        App.utils.showToast('Đã Hoàn tác (Undo)', 'info');
    }

    // Làm lại hành động vừa hoàn tác (Ctrl + Y hoặc Ctrl + Shift + Z)
    function redo() {
        if (redoStack.length === 0) {
            App.utils.showToast('Không có gì để làm lại!', 'warning');
            return;
        }

        const currentStateCopy = JSON.parse(JSON.stringify(elements));
        undoStack.push(currentStateCopy);

        elements = redoStack.pop();

        deselectElement();
        renderAllElements();
        App.utils.showToast('Đã Làm lại (Redo)', 'info');
    }

    // Thiết lập phím tắt bàn phím
    function setupKeyboardShortcuts() {
        document.addEventListener('keydown', function (e) {
            // Tránh chạy phím tắt khi đang nhập liệu trong ô input/textarea/select
            const activeEl = document.activeElement;
            const isTyping = activeEl && (
                activeEl.tagName === 'INPUT' || 
                activeEl.tagName === 'TEXTAREA' || 
                activeEl.tagName === 'SELECT'
            );

            if (isTyping) return;

            // Phím Delete hoặc Backspace: Xóa phần tử đang chọn
            if (e.key === 'Delete' || e.key === 'Backspace') {
                if (selectedElement) {
                    e.preventDefault();
                    deleteSelectedElement(false); // Xóa trực tiếp không cần confirm nhờ đã có Undo
                }
            }

            // Ctrl + Z hoặc Cmd + Z: Undo
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z' && !e.shiftKey) {
                e.preventDefault();
                undo();
            }

            // Ctrl + Y / Cmd + Y hoặc Ctrl + Shift + Z: Redo
            if (
                ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'y') ||
                ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key.toLowerCase() === 'z')
            ) {
                e.preventDefault();
                redo();
            }
        });
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

    // Vẽ các nút nắm kéo dài (waypoint handles) lên canvas
    function renderWaypointHandles(item) {
        // Xóa các handle cũ (cả waypoint và midpoint)
        canvasGrid.querySelectorAll('.waypoint-handle, .midpoint-handle').forEach(h => h.remove());
        
        if (!item || !isRoadType(item.element_type) || !item.waypoints) return;
        
        // 1. Vẽ các nút nắm kéo tự do (waypoint handles) ở các góc/đầu mút
        item.waypoints.forEach((pt, idx) => {
            const handle = document.createElement('div');
            handle.className = 'waypoint-handle';
            handle.style.left = `${pt.x}px`;
            handle.style.top = `${pt.y}px`;
            handle.dataset.index = idx;
            
            // Container cho mũi tên bẻ hướng
            const arrows = document.createElement('div');
            arrows.className = 'waypoint-arrows';
            handle.appendChild(arrows);
            
            const isStart = (idx === 0);
            const isEnd = (idx === item.waypoints.length - 1);
            
            if (isStart || isEnd) {
                const neighbor = isStart ? item.waypoints[1] : item.waypoints[idx - 1];
                if (neighbor) {
                    const dx = pt.x - neighbor.x;
                    const dy = pt.y - neighbor.y;
                    
                    let straightDir, leftDir, rightDir;
                    let straightArrow, leftArrow, rightArrow;
                    
                    if (Math.abs(dx) >= Math.abs(dy)) {
                        if (dx >= 0) { // Heading Right
                            straightDir = 'right'; straightArrow = '→';
                            leftDir = 'up'; leftArrow = '↑';
                            rightDir = 'down'; rightArrow = '↓';
                        } else { // Heading Left
                            straightDir = 'left'; straightArrow = '←';
                            leftDir = 'down'; leftArrow = '↓';
                            rightDir = 'up'; rightArrow = '↑';
                        }
                    } else {
                        if (dy >= 0) { // Heading Down
                            straightDir = 'down'; straightArrow = '↓';
                            leftDir = 'right'; leftArrow = '→';
                            rightDir = 'left'; rightArrow = '←';
                        } else { // Heading Up
                            straightDir = 'up'; straightArrow = '↑';
                            leftDir = 'left'; leftArrow = '←';
                            rightDir = 'right'; rightArrow = '→';
                        }
                    }
                    
                    const dirs = [
                        { dir: straightDir, arrow: straightArrow, label: 'Đi Thẳng' },
                        { dir: leftDir, arrow: leftArrow, label: 'Rẽ Trái' },
                        { dir: rightDir, arrow: rightArrow, label: 'Rẽ Phải' }
                    ];
                    
                    dirs.forEach(d => {
                        const arrowBtn = document.createElement('div');
                        arrowBtn.className = `waypoint-arrow arrow-${d.dir}`;
                        arrowBtn.innerHTML = d.arrow;
                        arrowBtn.title = `Kéo dài ${d.label} (${d.arrow})`;
                        
                        arrowBtn.addEventListener('mousedown', function(e) {
                            e.stopPropagation();
                            e.preventDefault();
                            startExtendingWaypoint(e, item, idx, d.dir);
                        });
                        
                        arrows.appendChild(arrowBtn);
                    });
                }
            }
            
            // Double-click vào handle để xóa waypoint (chấm lớn)
            handle.addEventListener('dblclick', function(e) {
                e.stopPropagation();
                if (item.waypoints.length > 2) {
                    recordState();
                    
                    // Kiểm tra xem đây có phải là điểm đầu của nhánh rẽ (M -> C -> M) không
                    if (idx > 0 && idx < item.waypoints.length - 1) {
                        const prev = item.waypoints[idx - 1];
                        const next = item.waypoints[idx + 1];
                        if (prev.x === next.x && prev.y === next.y) {
                            // Xóa cả điểm nhánh rẽ C và điểm quay lại M
                            item.waypoints.splice(idx, 2);
                        } else {
                            item.waypoints.splice(idx, 1);
                        }
                    } else {
                        item.waypoints.splice(idx, 1);
                    }
                    
                    const div = document.getElementById(`el-${item.id || item.temp_id}`);
                    if (div) updateElementDOM(div, item);
                    renderWaypointHandles(item);
                } else {
                    App.utils.showToast('Đường đi phải có ít nhất 2 điểm!', 'warning');
                }
            });
            
            // Kéo thả handle để di chuyển waypoint tự do
            handle.addEventListener('mousedown', function(e) {
                // Nếu mousedown trúng mũi tên con thì không chạy logic kéo handle tự do
                if (e.target.closest('.waypoint-arrow')) return;
                
                e.stopPropagation();
                e.preventDefault();
                
                const startClientX = e.clientX;
                const startClientY = e.clientY;
                const initialX = pt.x;
                const initialY = pt.y;
                let hasRecorded = false;
                let isDragging = false;
                
                function onMouseMove(moveEvent) {
                    const dx = (moveEvent.clientX - startClientX) / zoomLevel;
                    const dy = (moveEvent.clientY - startClientY) / zoomLevel;
                    
                    if (!isDragging && Math.hypot(dx, dy) > 3) {
                        isDragging = true;
                    }
                    
                    if (isDragging) {
                        if (!hasRecorded) {
                            recordState();
                            hasRecorded = true;
                        }
                        
                        let newX = initialX + dx;
                        let newY = initialY + dy;
                        
                        if (chkSnapGrid.checked) {
                            newX = Math.round(newX / 20) * 20;
                            newY = Math.round(newY / 20) * 20;
                        }
                        
                        pt.x = newX;
                        pt.y = newY;
                        
                        handle.style.left = `${newX}px`;
                        handle.style.top = `${newY}px`;
                        
                        const div = document.getElementById(`el-${item.id || item.temp_id}`);
                        if (div) updateElementDOM(div, item);
                    }
                }
                
                function onMouseUp() {
                    document.removeEventListener('mousemove', onMouseMove);
                    document.removeEventListener('mouseup', onMouseUp);
                    if (isDragging) {
                        renderWaypointHandles(item);
                    }
                }
                
                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
            });
            
            canvasGrid.appendChild(handle);
        });

        // 2. Vẽ các nút tròn mờ ở chính giữa từng đoạn thẳng (midpoint handles)
        for (let i = 0; i < item.waypoints.length - 1; i++) {
            const p1 = item.waypoints[i];
            const p2 = item.waypoints[i + 1];
            
            const midX = (p1.x + p2.x) / 2;
            const midY = (p1.y + p2.y) / 2;
            
            const midHandle = document.createElement('div');
            midHandle.className = 'midpoint-handle';
            midHandle.style.left = `${midX}px`;
            midHandle.style.top = `${midY}px`;
            
            // Container cho mũi tên bẻ hướng của midpoint
            const arrows = document.createElement('div');
            arrows.className = 'waypoint-arrows';
            midHandle.appendChild(arrows);
            
            // Xác định phân đoạn ngang hay dọc để hiển thị 2 hướng vuông góc tương ứng
            const dx = p2.x - p1.x;
            const dy = p2.y - p1.y;
            const isHorizontalSegment = Math.abs(dx) >= Math.abs(dy);
            
            const dirs = isHorizontalSegment 
                ? [ { dir: 'up', arrow: '↑', label: 'Lên' }, { dir: 'down', arrow: '↓', label: 'Xuống' } ]
                : [ { dir: 'left', arrow: '←', label: 'Trái' }, { dir: 'right', arrow: '→', label: 'Phải' } ];
                
            dirs.forEach(d => {
                const arrowBtn = document.createElement('div');
                arrowBtn.className = `waypoint-arrow arrow-${d.dir}`;
                arrowBtn.innerHTML = d.arrow;
                arrowBtn.title = `Bẻ góc rẽ ${d.label} (${d.arrow})`;
                
                arrowBtn.addEventListener('mousedown', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    startExtendingMidpoint(e, item, i, d.dir);
                });
                
                arrows.appendChild(arrowBtn);
            });
            
            // Kéo tự do trên midpoint-handle vẫn hoạt động như cũ
            midHandle.addEventListener('mousedown', function(e) {
                if (e.target.closest('.waypoint-arrow')) return;
                
                e.stopPropagation();
                e.preventDefault();
                startDraggingMidpoint(e, item, i);
            });
            
            canvasGrid.appendChild(midHandle);
        }
    }

    // Kéo dài góc rẽ theo hướng ngang hoặc dọc thẳng hàng
    function startExtendingWaypoint(e, item, idx, direction) {
        const originalPt = item.waypoints[idx];
        const newPt = { x: originalPt.x, y: originalPt.y };
        
        const rect = canvasGrid.getBoundingClientRect();
        const startX = originalPt.x;
        const startY = originalPt.y;
        let hasInserted = false;
        
        const isHorizontal = (direction === 'horizontal' || direction === 'left' || direction === 'right');
        
        function onMouseMove(moveEvent) {
            let dragX = (moveEvent.clientX - rect.left) / zoomLevel;
            let dragY = (moveEvent.clientY - rect.top) / zoomLevel;
            
            if (chkSnapGrid.checked) {
                dragX = Math.round(dragX / 20) * 20;
                dragY = Math.round(dragY / 20) * 20;
            }
            
            const dist = isHorizontal ? Math.abs(dragX - startX) : Math.abs(dragY - startY);
            if (!hasInserted && dist > 5) {
                recordState();
                
                // Chèn điểm mới
                if (idx === 0) {
                    item.waypoints.unshift(newPt);
                } else if (idx === item.waypoints.length - 1) {
                    item.waypoints.push(newPt);
                } else {
                    item.waypoints.splice(idx + 1, 0, newPt);
                }
                
                hasInserted = true;
            }
            
            if (hasInserted) {
                if (isHorizontal) {
                    newPt.x = dragX;
                    newPt.y = originalPt.y;
                } else {
                    newPt.x = originalPt.x;
                    newPt.y = dragY;
                }
                
                const div = document.getElementById(`el-${item.id || item.temp_id}`);
                if (div) updateElementDOM(div, item);
            }
        }
        
        // onMouseUp extends waypoint
        function onMouseUp() {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
            if (hasInserted) {
                renderWaypointHandles(item);
            }
        }
        
        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    }

    // Kéo bẻ góc từ nút tròn mờ ở chính giữa đoạn thẳng
    function startDraggingMidpoint(e, item, segmentIdx) {
        const p1 = item.waypoints[segmentIdx];
        const p2 = item.waypoints[segmentIdx + 1];
        
        const midX = (p1.x + p2.x) / 2;
        const midY = (p1.y + p2.y) / 2;
        
        const newPt = { x: midX, y: midY };
        const rect = canvasGrid.getBoundingClientRect();
        
        const startX = midX;
        const startY = midY;
        let hasInserted = false;
        
        function onMouseMove(moveEvent) {
            let dragX = (moveEvent.clientX - rect.left) / zoomLevel;
            let dragY = (moveEvent.clientY - rect.top) / zoomLevel;
            
            if (chkSnapGrid.checked) {
                dragX = Math.round(dragX / 20) * 20;
                dragY = Math.round(dragY / 20) * 20;
            }
            
            const dist = Math.hypot(dragX - startX, dragY - startY);
            if (!hasInserted && dist > 5) {
                recordState();
                
                // Chèn điểm mới ở giữa đoạn segmentIdx và segmentIdx + 1
                item.waypoints.splice(segmentIdx + 1, 0, newPt);
                hasInserted = true;
            }
            
            if (hasInserted) {
                newPt.x = dragX;
                newPt.y = dragY;
                
                const div = document.getElementById(`el-${item.id || item.temp_id}`);
                if (div) updateElementDOM(div, item);
            }
        }
        
        function onMouseUp() {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
            if (hasInserted) {
                renderWaypointHandles(item);
            }
        }
        
        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    }

    // Kéo bẻ góc vuông góc cố định từ nút tròn mờ (Tạo nhánh rẽ trên chính con đường đó)
    function startExtendingMidpoint(e, item, segmentIdx, direction) {
        const p1 = item.waypoints[segmentIdx];
        const p2 = item.waypoints[segmentIdx + 1];
        
        const midX = (p1.x + p2.x) / 2;
        const midY = (p1.y + p2.y) / 2;
        
        // Chèn 3 điểm đại diện cho nhánh rẽ: M -> C -> M
        const M = { x: midX, y: midY };
        const C = { x: midX, y: midY };
        const M_dup = { x: midX, y: midY };
        
        item.waypoints.splice(segmentIdx + 1, 0, M, C, M_dup);
        
        // Gọi hàm kéo nhánh rẽ mới chèn (điểm C ở index segmentIdx + 2)
        startDraggingBranch(e, item, segmentIdx, segmentIdx + 2, direction);
    }

    // Kéo nhánh rẽ mới bẻ từ trung điểm dọc/ngang
    function startDraggingBranch(e, item, segmentIdx, branchIdx, direction) {
        const branchPt = item.waypoints[branchIdx];
        const rect = canvasGrid.getBoundingClientRect();
        const startX = branchPt.x;
        const startY = branchPt.y;
        
        const isHorizontal = (direction === 'horizontal' || direction === 'left' || direction === 'right');
        let hasRecorded = false;
        
        function onMouseMove(moveEvent) {
            let dragX = (moveEvent.clientX - rect.left) / zoomLevel;
            let dragY = (moveEvent.clientY - rect.top) / zoomLevel;
            
            if (chkSnapGrid.checked) {
                dragX = Math.round(dragX / 20) * 20;
                dragY = Math.round(dragY / 20) * 20;
            }
            
            const dist = isHorizontal ? Math.abs(dragX - startX) : Math.abs(dragY - startY);
            if (dist > 5) {
                if (!hasRecorded) {
                    recordState();
                    hasRecorded = true;
                }
                
                if (isHorizontal) {
                    branchPt.x = dragX;
                    branchPt.y = startY;
                } else {
                    branchPt.x = startX;
                    branchPt.y = dragY;
                }
                
                const div = document.getElementById(`el-${item.id || item.temp_id}`);
                if (div) updateElementDOM(div, item);
            }
        }
        
        function onMouseUp() {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
            if (hasRecorded) {
                renderWaypointHandles(item);
            } else {
                // Người dùng không kéo đủ xa, hoàn tác chèn 3 điểm nhánh rẽ
                item.waypoints.splice(segmentIdx + 1, 3);
                const div = document.getElementById(`el-${item.id || item.temp_id}`);
                if (div) updateElementDOM(div, item);
                renderWaypointHandles(item);
            }
        }
        
        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    }

    // Tìm phân đoạn đường gần điểm click nhất
    function findClosestSegment(pt, waypoints) {
        let minDistance = Infinity;
        let closestIndex = 0;
        
        for (let i = 0; i < waypoints.length - 1; i++) {
            const dist = getDistanceToSegment(pt, waypoints[i], waypoints[i+1]);
            if (dist < minDistance) {
                minDistance = dist;
                closestIndex = i;
            }
        }
        return closestIndex;
    }
    
    // Tính khoảng cách Euclid từ điểm P đến đoạn thẳng AB
    function getDistanceToSegment(p, a, b) {
        const x = p.x, y = p.y;
        const x1 = a.x, y1 = a.y;
        const x2 = b.x, y2 = b.y;
        
        const A = x - x1;
        const B = y - y1;
        const C = x2 - x1;
        const D = y2 - y1;
        
        const dot = A * C + B * D;
        const len_sq = C * C + D * D;
        let param = -1;
        if (len_sq != 0) param = dot / len_sq;
            
        let xx, yy;
        if (param < 0) {
            xx = x1;
            yy = y1;
        } else if (param > 1) {
            xx = x2;
            yy = y2;
        } else {
            xx = x1 + param * C;
            yy = y1 + param * D;
        }
        
        const dx = x - xx;
        const dy = y - yy;
        return Math.sqrt(dx * dx + dy * dy);
    }

    // Chạy khởi động khi tài liệu sẵn sàng
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
