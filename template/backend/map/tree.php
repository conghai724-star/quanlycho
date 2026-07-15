<?php
/**
 * View Sơ đồ Cây sạp chợ tương tác dành cho Admin
 */

$db = database::getInstance();
if (marketService::isSuperAdmin()) {
    $accessibleMarkets = $db->select("SELECT id, name FROM markets WHERE status_code = 'active' ORDER BY name ASC");
} else {
    $userId = session::get('user_id');
    $accessibleMarkets = $db->select("
        SELECT m.id, m.name 
        FROM user_markets um
        JOIN markets m ON um.market_id = m.id
        WHERE um.user_id = :user_id AND m.status_code = 'active'
        ORDER BY m.name ASC
    ", ['user_id' => $userId]);
}

$activeMarketId = marketService::currentMarketId();
?>

<!-- Style tùy chỉnh cho Sơ đồ cây sạp chợ -->
<style>
    .tree-map-container {
        display: flex;
        gap: 20px;
        margin-top: 20px;
        flex-wrap: wrap;
    }

    .tree-pane {
        flex: 1;
        min-width: 320px;
        background-color: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 20px;
        max-height: 750px;
        overflow-y: auto;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    }

    .detail-pane {
        flex: 2;
        min-width: 450px;
        background-color: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 24px;
        min-height: 500px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        position: sticky;
        top: 20px;
    }

    /* CSS Tree View */
    .stall-tree, .stall-tree ul {
        list-style: none;
        padding-left: 20px;
        margin: 0;
    }

    .stall-tree {
        padding-left: 0;
    }

    .stall-tree li {
        margin: 6px 0;
        position: relative;
    }

    .tree-node-toggle {
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 4px;
        transition: all 0.2s ease;
        font-weight: 500;
        color: var(--text-heading);
        width: 100%;
    }

    .tree-node-toggle:hover {
        background-color: var(--bg-surface-secondary);
    }

    .tree-node-toggle i.caret-icon {
        width: 12px;
        transition: transform 0.2s ease;
    }

    .tree-node-toggle.collapsed i.caret-icon {
        transform: rotate(-90deg);
    }

    .tree-node-children {
        transition: max-height 0.3s ease-out;
        overflow: hidden;
    }

    .tree-node-children.hidden {
        display: none;
    }

    /* Stall leaf node */
    .tree-stall-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 6px 12px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 13px;
        border: 1px solid transparent;
        margin-left: 24px;
    }

    .tree-stall-item:hover {
        background-color: var(--bg-surface-secondary);
        border-color: var(--border-color);
    }

    .tree-stall-item.active {
        background-color: rgba(26, 187, 156, 0.1);
        border-color: #1ABB9C;
        font-weight: 600;
        color: #15803d;
    }

    .status-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 6px;
    }

    /* Màu sắc trạng thái */
    .dot-rented { background-color: #22c55e; }     /* Đã thuê - xanh lá */
    .dot-empty { background-color: #3b82f6; }      /* Trống - xanh dương */
    .dot-repairing { background-color: #eab308; }  /* Sửa chữa - vàng */
    .dot-locked { background-color: #ef4444; }     /* Tạm khóa - đỏ */

    /* Empty state */
    .empty-detail-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: var(--text-muted);
        text-align: center;
        padding: 40px 0;
    }

    .empty-detail-state i {
        font-size: 48px;
        color: var(--border-color);
        margin-bottom: 16px;
    }

    /* Search highlight */
    .search-highlight {
        background-color: #fef08a;
        color: #000;
        font-weight: bold;
        border-radius: 2px;
    }

    /* Tab UI */
    .detail-tabs {
        display: flex;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 20px;
        gap: 16px;
    }
    .detail-tab-btn {
        padding: 8px 4px;
        border: none;
        background: none;
        color: var(--text-muted);
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        position: relative;
    }
    .detail-tab-btn.active {
        color: #1ABB9C;
    }
    .detail-tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 2px;
        background-color: #1ABB9C;
    }
    .tab-content-panel {
        display: none;
    }
    .tab-content-panel.active {
        display: block;
    }
</style>

<!-- TIÊU ĐỀ & CHỌN CHỢ -->
<div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 10px;">
    <div>
        <h2 style="margin: 0; font-size: 20px; font-weight: 600; color: var(--text-heading);">Sơ đồ Cây sạp chợ</h2>
        <p style="color: var(--text-muted); font-size: 13px; margin: 4px 0 0;">Tra cứu danh sách sạp trực quan theo mô hình phân cấp Khu - Dãy - Lô.</p>
    </div>
    
    <!-- Bộ chọn chợ cho Super Admin hoặc nhân viên có quyền truy cập nhiều chợ -->
    <?php if (count($accessibleMarkets) > 1): ?>
        <div style="display: flex; align-items: center; gap: 8px;">
            <label style="font-weight: 500; font-size: 13px; margin: 0; color: var(--text-heading);">Chọn Chợ:</label>
            <select id="market-scope-selector" class="form-control" style="width: 220px; height: 35px; font-size: 13px;" onchange="changeMarketScope(this.value)">
                <?php foreach ($accessibleMarkets as $m): ?>
                    <option value="<?php echo $m['id']; ?>" <?php echo $m['id'] == $activeMarketId ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($m['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>
</div>

<div class="tree-map-container">
    <!-- CỘT TRÁI: SEARCH VÀ TREE VIEW -->
    <div class="tree-pane">
        <div class="form-group" style="position: relative; margin-bottom: 16px;">
            <input type="text" id="tree-search-input" class="form-control" placeholder="Tìm theo Mã sạp hoặc Tên tiểu thương..." style="padding-left: 36px; height: 38px; font-size: 13px;">
            <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 12px; color: var(--text-muted);"></i>
        </div>

        <div id="stall-tree-root">
            <div style="text-align: center; padding: 20px 0; color: var(--text-muted);">
                <i class="fa-solid fa-spinner fa-spin"></i> Đang tải sơ đồ cây...
            </div>
        </div>
    </div>

    <!-- CỘT PHẢI: CHI TIẾT SẠP -->
    <div class="detail-pane" id="stall-detail-pane">
        <div class="empty-detail-state">
            <i class="fa-solid fa-store"></i>
            <h4>Thông tin chi tiết Sạp chợ</h4>
            <p>Vui lòng click vào một sạp cụ thể trên Sơ đồ cây để xem thông tin chi tiết đầy đủ của sạp, hợp đồng và tiểu thương đang kinh doanh.</p>
        </div>
    </div>
</div>

<!-- Nạp JS xử lý Sơ đồ cây -->
<script>
$(document).ready(function() {
    const CONFIG = {
        URL: {
            BASE: '<?php echo BASE_URL; ?>'
        },
        API: {
            GET_TREE: 'api/getStallTree',
            GET_DETAILS: 'api/getStallDetails'
        },
        SELECTOR: {
            TREE_ROOT: 'stall-tree-root',
            DETAIL_PANE: 'stall-detail-pane',
            SEARCH_INPUT: 'tree-search-input'
        }
    };

    let treeData = null;
    let activeStallId = null;
    const isDarkTheme = document.documentElement.getAttribute('data-theme') === 'dark';

    async function fetchStallTreeApi() {
        try {
            const res = await fetch(CONFIG.URL.BASE + CONFIG.API.GET_TREE);
            const data = await res.json();
            return {
                success: data && data.status === 200,
                data: data ? data.data : null
            };
        } catch (e) {
            return { success: false, error: e };
        }
    }

    async function fetchStallDetailsApi(stallId) {
        try {
            const res = await fetch(CONFIG.URL.BASE + CONFIG.API.GET_DETAILS + '?id=' + stallId);
            const data = await res.json();
            return {
                success: data && data.status === 200,
                data: data ? data.data : null
            };
        } catch (e) {
            return { success: false, error: e };
        }
    }

    async function loadTree() {
        const res = await fetchStallTreeApi();
        if (res.success && res.data) {
            treeData = res.data;
            renderTree(treeData);
        } else {
            document.getElementById(CONFIG.SELECTOR.TREE_ROOT).innerHTML = 
                `<div style="color: var(--red); padding: 10px;">Lỗi tải sơ đồ: Không có quyền truy cập hoặc mất kết nối.</div>`;
        }
    }

    async function showStallDetails(stallId) {
        activeStallId = stallId;
        
        document.querySelectorAll('.tree-stall-item').forEach(el => {
            el.classList.toggle('active', parseInt(el.dataset.id) === stallId);
        });

        const detailPane = document.getElementById(CONFIG.SELECTOR.DETAIL_PANE);
        detailPane.innerHTML = `<div style="text-align: center; padding: 40px 0;"><i class="fa-solid fa-spinner fa-spin"></i> Đang tải thông tin sạp...</div>`;

        const res = await fetchStallDetailsApi(stallId);
        if (res.success && res.data) {
            renderStallDetailsCard(res.data);
        } else {
            detailPane.innerHTML = `<div style="color: var(--red); padding: 20px;">Lỗi tải chi tiết sạp.</div>`;
        }
    }

    function renderTree(data, filterText = '') {
        const root = document.getElementById(CONFIG.SELECTOR.TREE_ROOT);
        if (!root) return;

        if (Object.keys(data).length === 0) {
            root.innerHTML = '<div style="color: var(--text-muted); font-style: italic; font-size: 13px;">Chưa có dữ liệu phân khu / sạp chợ nào.</div>';
            return;
        }

        const normalizedFilter = filterText.toLowerCase().trim();
        let html = '<ul class="stall-tree">';

        Object.keys(data).forEach((areaName, areaIdx) => {
            let areaHasMatch = false;
            let areaHtml = '';
            
            Object.keys(data[areaName]).forEach((blockName, blockIdx) => {
                let blockHasMatch = false;
                let blockHtml = '';

                Object.keys(data[areaName][blockName]).forEach((lotName, lotIdx) => {
                    let lotHasMatch = false;
                    let lotHtml = '';

                    const stalls = data[areaName][blockName][lotName];
                    stalls.forEach(stall => {
                        const codeMatch = stall.code.toLowerCase().includes(normalizedFilter);
                        const traderMatch = stall.trader_name.toLowerCase().includes(normalizedFilter);
                        
                        if (!normalizedFilter || codeMatch || traderMatch) {
                            lotHasMatch = true;
                            blockHasMatch = true;
                            areaHasMatch = true;
                            activeStallId = activeStallId || null;

                            let displayName = stall.code;
                            if (stall.trader_name) {
                                displayName += ` - ${stall.trader_name}`;
                            }

                            if (normalizedFilter) {
                                displayName = highlightText(displayName, normalizedFilter);
                            }

                            let dotClass = 'dot-empty';
                            if (stall.status_code === 'rented') dotClass = 'dot-rented';
                            else if (stall.status_code === 'repairing') dotClass = 'dot-repairing';
                            else if (stall.status_code === 'locked') dotClass = 'dot-locked';

                            const activeClass = activeStallId === stall.id ? 'active' : '';

                            lotHtml += `
                                <li>
                                    <div class="tree-stall-item ${activeClass}" data-id="${stall.id}">
                                        <span>
                                            <span class="status-dot ${dotClass}"></span>
                                            ${displayName}
                                        </span>
                                        <span class="badge badge-sm" style="font-size: 10px; background-color: var(--bg-surface-secondary); color: var(--text-muted);">${stall.type}</span>
                                    </div>
                                </li>
                            `;
                        }
                    });

                    if (lotHasMatch) {
                        const collapsed = normalizedFilter ? '' : 'collapsed';
                        const hidden = normalizedFilter ? '' : 'hidden';
                        lotHtml = `
                            <li>
                                <div class="tree-node-toggle ${collapsed}" data-type="lot">
                                    <i class="fa-solid fa-caret-down caret-icon"></i>
                                    <i class="fa-solid fa-map-location-dot" style="color: #64748b;"></i>
                                    <span>${lotName}</span>
                                </div>
                                <ul class="tree-node-children ${hidden}">${lotHtml}</ul>
                            </li>
                        `;
                        blockHtml += lotHtml;
                    }
                });

                if (blockHasMatch) {
                    const collapsed = normalizedFilter ? '' : 'collapsed';
                    const hidden = normalizedFilter ? '' : 'hidden';
                    blockHtml = `
                        <li>
                            <div class="tree-node-toggle ${collapsed}" data-type="block">
                                <i class="fa-solid fa-caret-down caret-icon"></i>
                                <i class="fa-solid fa-network-wired" style="color: #4b5563;"></i>
                                <span>${blockName}</span>
                            </div>
                            <ul class="tree-node-children ${hidden}">${blockHtml}</ul>
                        </li>
                    `;
                    areaHtml += blockHtml;
                }
            });

            if (areaHasMatch) {
                const collapsed = normalizedFilter ? '' : 'collapsed';
                const hidden = normalizedFilter ? '' : 'hidden';
                areaHtml = `
                    <li>
                        <div class="tree-node-toggle ${collapsed}" data-type="area">
                            <i class="fa-solid fa-caret-down caret-icon"></i>
                            <i class="fa-solid fa-folder" style="color: #eab308;"></i>
                            <span>${areaName}</span>
                        </div>
                        <ul class="tree-node-children ${hidden}">${areaHtml}</ul>
                    </li>
                `;
                html += areaHtml;
            }
        });

        html += '</ul>';
        root.innerHTML = html;

        bindTreeEvents();
    }

    function highlightText(text, search) {
        const index = text.toLowerCase().indexOf(search);
        if (index >= 0) {
            return text.substring(0, index) + 
                   `<span class="search-highlight">${text.substring(index, index + search.length)}</span>` + 
                   text.substring(index + search.length);
        }
        return text;
    }

    function renderStallDetailsCard(stall) {
        const pane = document.getElementById(CONFIG.SELECTOR.DETAIL_PANE);
        if (!pane) return;

        let badgeClass = 'badge-info';
        if (stall.status === 'rented') badgeClass = 'badge-success';
        else if (stall.status === 'repairing') badgeClass = 'badge-warning';
        else if (stall.status === 'locked') badgeClass = 'badge-danger';

        const size = parseFloat(stall.area_size).toLocaleString('vi-VN') + ' m²';
        const price = parseFloat(stall.base_price).toLocaleString('vi-VN') + ' đ / tháng';
        const deposit = stall.deposit ? parseFloat(stall.deposit).toLocaleString('vi-VN') + ' đ' : 'Chưa đóng cọc';

        let tabHeaders = `
            <div class="detail-tabs">
                <button class="detail-tab-btn active" data-tab="general">Thông tin Sạp</button>
                <button class="detail-tab-btn" data-tab="contract">Hợp đồng thuê</button>
                <button class="detail-tab-btn" data-tab="trader">Tiểu thương</button>
            </div>
        `;

        let tabGeneral = `
            <div class="tab-content-panel active" id="tab-general">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                    <h3 style="margin: 0; font-size: 22px; font-weight: 700; color: #1f2937;">${stall.stall_code}</h3>
                    <span class="badge ${badgeClass}" style="font-size: 13px; padding: 6px 12px; border-radius: 9999px;">
                        ${stall.status_name}
                    </span>
                </div>
                
                <table class="table" style="width: 100%; font-size: 13px;">
                    <tr>
                        <td style="font-weight: 600; width: 140px; color: var(--text-muted); border: none; padding: 8px 0;">Khu vực:</td>
                        <td style="border: none; padding: 8px 0; color: var(--text-heading); font-weight: 500;">${stall.area_name}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: var(--text-muted); border: none; padding: 8px 0;">Dãy (Block):</td>
                        <td style="border: none; padding: 8px 0; color: var(--text-heading); font-weight: 500;">${stall.block || 'Chưa định vị'}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: var(--text-muted); border: none; padding: 8px 0;">Lô (Lot):</td>
                        <td style="border: none; padding: 8px 0; color: var(--text-heading); font-weight: 500;">${stall.lot || 'Chưa định vị'}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: var(--text-muted); border: none; padding: 8px 0;">Đơn giá:</td>
                        <td style="border: none; padding: 8px 0; color: var(--text-heading); font-weight: 600;">${price}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: var(--text-muted); border: none; padding: 8px 0;">Diện tích:</td>
                        <td style="border: none; padding: 8px 0; color: var(--text-heading); font-weight: 500;">${size}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: var(--text-muted); border: none; padding: 8px 0;">Loại sạp:</td>
                        <td style="border: none; padding: 8px 0; color: var(--text-heading); font-weight: 500;">${stall.stall_type || 'Quầy hàng tiêu chuẩn'}</td>
                    </tr>
                </table>

                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-color); display: flex; gap: 12px;">
                    <a href="${CONFIG.URL.BASE}admin/stalls" class="btn btn-outline-secondary btn-sm" style="margin: 0; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-list"></i> Xem danh sách Sạp
                    </a>
                </div>
            </div>
        `;

        let tabContract = '';
        if (stall.status === 'rented' && stall.contract_number) {
            let fileLink = '';
            if (stall.contract_file) {
                fileLink = `
                    <div style="margin-top: 12px;">
                        <a href="${CONFIG.URL.BASE}uploads/contracts/${stall.contract_file}" target="_blank" class="btn btn-sm btn-outline-primary" style="margin: 0; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-file-pdf"></i> Tải file Hợp đồng đính kèm
                        </a>
                    </div>
                `;
            }

            tabContract = `
                <div class="tab-content-panel" id="tab-contract">
                    <h4 style="margin: 0 0 16px 0; font-size: 15px; font-weight: 600; color: var(--text-heading);">Hợp đồng thuê: ${stall.contract_number}</h4>
                    <table class="table" style="width: 100%; font-size: 13px;">
                        <tr>
                            <td style="font-weight: 600; width: 140px; color: var(--text-muted); border: none; padding: 8px 0;">Ngày ký hợp đồng:</td>
                            <td style="border: none; padding: 8px 0; color: var(--text-heading); font-weight: 500;">${formatDate(stall.start_date)}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; color: var(--text-muted); border: none; padding: 8px 0;">Ngày hết hạn:</td>
                            <td style="border: none; padding: 8px 0; color: var(--text-heading); font-weight: 600; color: var(--red);">${formatDate(stall.end_date)}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; color: var(--text-muted); border: none; padding: 8px 0;">Tiền đặt cọc:</td>
                            <td style="border: none; padding: 8px 0; color: var(--text-heading); font-weight: 600; color: var(--green);">${deposit}</td>
                        </tr>
                    </table>
                    ${fileLink}
                    <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-color); display: flex; gap: 12px;">
                        <a href="${CONFIG.URL.BASE}admin/contracts" class="btn btn-outline-primary btn-sm" style="margin: 0; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-file-signature"></i> Quản lý Hợp đồng
                        </a>
                    </div>
                </div>
            `;
        } else {
            tabContract = `
                <div class="tab-content-panel" id="tab-contract">
                    <div style="text-align: center; padding: 30px 10px; background-color: var(--bg-surface-secondary); border-radius: 8px; border: 1px dashed var(--border-color);">
                        <i class="fa-solid fa-file-invoice" style="font-size: 32px; color: var(--border-color); margin-bottom: 12px;"></i>
                        <p style="margin: 0 0 12px; font-size: 13px; color: var(--text-muted);">Sạp này hiện đang bỏ trống, chưa có hợp đồng cho thuê đang hoạt động.</p>
                        <a href="${CONFIG.URL.BASE}admin/contracts" class="btn btn-success btn-sm" style="margin: 0; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-plus"></i> Ký Hợp đồng thuê Sạp
                        </a>
                    </div>
                </div>
            `;
        }

        let tabTrader = '';
        if (stall.status === 'rented' && stall.trader_fullname) {
            tabTrader = `
                <div class="tab-content-panel" id="tab-trader">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background-color: rgba(26, 187, 156, 0.1); color: #1ABB9C; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <div>
                            <h4 style="margin: 0; font-size: 15px; font-weight: 600; color: var(--text-heading);">${stall.trader_fullname}</h4>
                            <span style="font-size: 12px; color: var(--text-muted);">${stall.business_line_name || 'Chưa xếp ngành hàng'}</span>
                        </div>
                    </div>
                    <table class="table" style="width: 100%; font-size: 13px;">
                        <tr>
                            <td style="font-weight: 600; width: 140px; color: var(--text-muted); border: none; padding: 8px 0;">Số điện thoại:</td>
                            <td style="border: none; padding: 8px 0; color: var(--text-heading); font-weight: 500;">${stall.trader_phone}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; color: var(--text-muted); border: none; padding: 8px 0;">Số CCCD:</td>
                            <td style="border: none; padding: 8px 0; color: var(--text-heading); font-weight: 500;">${stall.trader_cccd}</td>
                        </tr>
                        <tr>
                            <td style="font-weight: 600; color: var(--text-muted); border: none; padding: 8px 0;">Địa chỉ:</td>
                            <td style="border: none; padding: 8px 0; color: var(--text-heading); font-weight: 500; white-space: pre-wrap;">${stall.trader_address || 'Chưa cung cấp'}</td>
                        </tr>
                    </table>
                    <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-color); display: flex; gap: 12px;">
                        <a href="${CONFIG.URL.BASE}admin/traders" class="btn btn-outline-info btn-sm" style="margin: 0; font-size: 12px; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-address-book"></i> Quản lý Tiểu thương
                        </a>
                    </div>
                </div>
            `;
        } else {
            tabTrader = `
                <div class="tab-content-panel" id="tab-trader">
                    <div style="text-align: center; padding: 30px 10px; background-color: var(--bg-surface-secondary); border-radius: 8px; border: 1px dashed var(--border-color);">
                        <i class="fa-solid fa-users-slash" style="font-size: 32px; color: var(--border-color); margin-bottom: 12px;"></i>
                        <p style="margin: 0; font-size: 13px; color: var(--text-muted);">Sạp chưa được thuê nên không có dữ liệu hộ tiểu thương quản lý sạp.</p>
                    </div>
                </div>
            `;
        }

        pane.innerHTML = tabHeaders + tabGeneral + tabContract + tabTrader;
        bindTabEvents();
    }

    function formatDate(dateStr) {
        if (!dateStr) return 'Chưa ghi nhận';
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
        return dateStr;
    }

    function bindTreeEvents() {
        document.querySelectorAll('.tree-node-toggle').forEach(el => {
            el.onclick = function (e) {
                e.stopPropagation();
                const children = this.nextElementSibling;
                if (children) {
                    children.classList.toggle('hidden');
                    this.classList.toggle('collapsed');
                }
            };
        });

        document.querySelectorAll('.tree-stall-item').forEach(el => {
            el.onclick = function (e) {
                e.stopPropagation();
                const stallId = parseInt(this.dataset.id);
                showStallDetails(stallId);
            };
        });
    }

    function bindTabEvents() {
        document.querySelectorAll('.detail-tab-btn').forEach(btn => {
            btn.onclick = function () {
                const targetTab = this.dataset.tab;
                document.querySelectorAll('.detail-tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                document.querySelectorAll('.tab-content-panel').forEach(p => p.classList.remove('active'));
                const panel = document.getElementById('tab-' + targetTab);
                if (panel) panel.classList.add('active');
            };
        });
    }

    function handleSearchInput(e) {
        const val = e.target.value;
        if (treeData) {
            renderTree(treeData, val);
        }
    }

    // Đăng ký API handler vào namespace
    window.App = window.App || {};
    window.App.mapTree = {
        showStallDetails
    };

    const searchInput = document.getElementById(CONFIG.SELECTOR.SEARCH_INPUT);
    if (searchInput) {
        searchInput.addEventListener('input', handleSearchInput);
    }

    loadTree();
});

function changeMarketScope(marketId) {
    App.alert.loading('Đang chuyển đổi dữ liệu...');
    fetch(window.BASE_URL + 'api/changeMarketScope?id=' + marketId)
        .then(res => res.json())
        .then(data => {
            if (data.status === 200) {
                location.reload();
            } else {
                App.alert.error('Lỗi', data.message || 'Không thể chuyển đổi chợ.');
            }
        })
        .catch(() => App.alert.connectionError());
}
</script>

