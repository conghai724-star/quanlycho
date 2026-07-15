<?php
/**
 * View Sơ đồ Cây sạp chợ công khai dành cho Khách hàng / Hộ kinh doanh
 */

$db = database::getInstance();
// Lấy toàn bộ danh sách chợ hoạt động cho khách hàng chọn
$markets = $db->select("SELECT id, name, address, phone FROM markets WHERE status_code = 'active' ORDER BY name ASC");
$activeMarketId = marketService::currentMarketId();

// Lấy thông tin chợ hiện tại đang chọn
$currentMarket = null;
foreach ($markets as $m) {
    if ($m['id'] == $activeMarketId) {
        $currentMarket = $m;
        break;
    }
}
if (!$currentMarket && !empty($markets)) {
    $currentMarket = $markets[0];
}
?>

<!-- Style của Sơ đồ cây phía Khách hàng -->
<style>
    /* Bố cục trang chia cột hiện đại */
    .tree-map-public-grid {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 30px;
        margin-bottom: 60px;
    }

    @media (max-width: 991px) {
        .tree-map-public-grid {
            grid-template-columns: 1fr;
        }
    }

    .tree-pane-public {
        background: #ffffff;
        border: 1px solid var(--gray-200);
        border-radius: var(--radius-lg);
        padding: 24px;
        max-height: 750px;
        overflow-y: auto;
        box-shadow: var(--shadow-sm);
    }

    .detail-pane-public {
        background: #ffffff;
        border: 1px solid var(--gray-200);
        border-radius: var(--radius-lg);
        padding: 30px;
        min-height: 500px;
        box-shadow: var(--shadow-sm);
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
        margin: 8px 0;
        position: relative;
    }

    .tree-node-toggle {
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        border-radius: 6px;
        transition: all 0.2s ease;
        font-weight: 600;
        color: var(--gray-800);
        width: 100%;
        font-size: 14px;
    }

    .tree-node-toggle:hover {
        background-color: var(--gray-50);
    }

    .tree-node-toggle i.caret-icon {
        width: 12px;
        transition: transform 0.2s ease;
        color: var(--gray-400);
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
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 13.5px;
        border: 1px solid transparent;
        margin-left: 24px;
        color: var(--gray-700);
    }

    .tree-stall-item:hover {
        background-color: var(--gray-50);
        border-color: var(--gray-200);
    }

    .tree-stall-item.active {
        background-color: rgba(26, 187, 156, 0.08);
        border-color: rgba(26, 187, 156, 0.4);
        font-weight: 600;
        color: #15803d;
    }

    .status-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 8px;
    }

    .dot-rented { background-color: #22c55e; }     /* Đã thuê */
    .dot-empty { background-color: #3b82f6; }      /* Trống */
    .dot-repairing { background-color: #eab308; }  /* Sửa chữa */
    .dot-locked { background-color: #ef4444; }     /* Tạm khóa */

    .empty-detail-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: var(--gray-500);
        text-align: center;
        padding: 60px 0;
    }

    .empty-detail-state i {
        font-size: 64px;
        color: var(--gray-200);
        margin-bottom: 20px;
    }

    .search-highlight {
        background-color: #fef08a;
        color: #000;
        font-weight: bold;
        border-radius: 2px;
    }

    /* Tab UI */
    .detail-tabs {
        display: flex;
        border-bottom: 1px solid var(--gray-200);
        margin-bottom: 24px;
        gap: 20px;
    }
    .detail-tab-btn {
        padding: 10px 4px;
        border: none;
        background: none;
        color: var(--gray-500);
        font-weight: 600;
        font-size: 14.5px;
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
        animation: fadeIn 0.2s ease-out;
    }
    .tab-content-panel.active {
        display: block;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<!-- ================= HERO BẢN ĐỒ ================= -->
<section class="hero" style="padding: 60px 0 80px;">
    <div class="hero-grid-pattern"></div>
    <div class="container hero-content" style="grid-template-columns: 1fr; text-align: center;">
        <div>
            <div class="hero-eyebrow" style="justify-content: center;"><span class="dot"></span> Tra cứu trực quan</div>
            <h1>Sơ đồ Cây sạp chợ trực tuyến</h1>
            <p style="margin: 0 auto; max-width: 600px;">
                Tìm kiếm nhanh vị trí sạp, kiểm tra tình trạng sử dụng sạp và đăng ký thuê trực tuyến nhanh chóng tiện lợi.
            </p>
        </div>
    </div>
</section>

<!-- ================= GIAO DIỆN SƠ ĐỒ CÂY ================= -->
<section style="padding: 40px 0;">
    <div class="container">
        <!-- Bộ chọn chợ công khai -->
        <?php if (count($markets) > 1): ?>
            <div style="background: #ffffff; padding: 16px 24px; border-radius: var(--radius-lg); border: 1px solid var(--gray-200); margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; box-shadow: var(--shadow-sm);">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="fa-solid fa-map-location-dot" style="font-size: 24px; color: #1ABB9C;"></i>
                    <div>
                        <h4 style="margin: 0; font-size: 15px; font-weight: 700; color: var(--gray-800);">Đang tra cứu dữ liệu tại:</h4>
                        <span style="font-size: 13px; color: var(--gray-500);"><?php echo htmlspecialchars($currentMarket['address'] ?? ''); ?></span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <label style="font-weight: 600; font-size: 13.5px; margin: 0; color: var(--gray-700);">Thay đổi chợ:</label>
                    <select id="market-scope-selector" class="form-control" style="width: 240px; height: 38px; font-size: 13.5px;" onchange="changeMarketScope(this.value)">
                        <?php foreach ($markets as $m): ?>
                            <option value="<?php echo $m['id']; ?>" <?php echo $m['id'] == $activeMarketId ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($m['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        <?php endif; ?>

        <div class="tree-map-public-grid">
            <!-- CỘT TRÁI: TÌM KIẾM & BẢN ĐỒ CÂY -->
            <div class="tree-pane-public">
                <div class="form-group" style="position: relative; margin-bottom: 20px;">
                    <input type="text" id="tree-search-input" class="form-control" placeholder="Nhập số sạp hoặc tên tiểu thương..." style="padding-left: 38px; height: 42px; font-size: 13.5px; border-radius: 8px; border: 1px solid var(--gray-300);">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 14px; color: var(--gray-400);"></i>
                </div>

                <div id="stall-tree-root">
                    <div style="text-align: center; padding: 40px 0; color: var(--gray-400);">
                        <i class="fa-solid fa-spinner fa-spin"></i> Đang nạp dữ liệu sơ đồ cây...
                    </div>
                </div>
            </div>

            <!-- CỘT PHẢI: CHI TIẾT SẠP -->
            <div class="detail-pane-public" id="stall-detail-pane">
                <div class="empty-detail-state">
                    <i class="fa-solid fa-store"></i>
                    <h3>Thông tin chi tiết Sạp chợ</h3>
                    <p style="max-width: 400px; margin: 0 auto;">Vui lòng nhấn chọn một sạp trên Sơ đồ cây bên trái để xem diện tích, đơn giá nền và liên hệ ký hợp đồng thuê.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Nạp JS xử lý Sơ đồ cây -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                `<div style="color: red; padding: 10px;">Lỗi tải sơ đồ: Mất kết nối máy chủ.</div>`;
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
            detailPane.innerHTML = `<div style="color: red; padding: 20px;">Lỗi tải chi tiết sạp.</div>`;
        }
    }

    function renderTree(data, filterText = '') {
        const root = document.getElementById(CONFIG.SELECTOR.TREE_ROOT);
        if (!root) return;

        if (Object.keys(data).length === 0) {
            root.innerHTML = '<div style="color: var(--gray-500); font-style: italic; font-size: 13px;">Chưa có dữ liệu phân khu / sạp chợ nào.</div>';
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
                                        <span class="badge badge-sm" style="font-size: 10px; background-color: var(--gray-100); color: var(--gray-600);">${stall.type}</span>
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
                <button class="detail-tab-btn" data-tab="contract">Đăng ký thuê</button>
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
                        <td style="font-weight: 600; width: 140px; color: var(--gray-500); border: none; padding: 8px 0;">Khu vực:</td>
                        <td style="border: none; padding: 8px 0; color: var(--gray-800); font-weight: 500;">${stall.area_name}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: var(--gray-500); border: none; padding: 8px 0;">Dãy (Block):</td>
                        <td style="border: none; padding: 8px 0; color: var(--gray-800); font-weight: 500;">${stall.block || 'Chưa định vị'}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: var(--gray-500); border: none; padding: 8px 0;">Lô (Lot):</td>
                        <td style="border: none; padding: 8px 0; color: var(--gray-800); font-weight: 500;">${stall.lot || 'Chưa định vị'}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: var(--gray-500); border: none; padding: 8px 0;">Đơn giá:</td>
                        <td style="border: none; padding: 8px 0; color: var(--gray-850); font-weight: 600;">${price}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: var(--gray-500); border: none; padding: 8px 0;">Diện tích:</td>
                        <td style="border: none; padding: 8px 0; color: var(--gray-800); font-weight: 500;">${size}</td>
                    </tr>
                    <tr>
                        <td style="font-weight: 600; color: var(--gray-500); border: none; padding: 8px 0;">Loại sạp:</td>
                        <td style="border: none; padding: 8px 0; color: var(--gray-800); font-weight: 500;">${stall.stall_type || 'Quầy hàng tiêu chuẩn'}</td>
                    </tr>
                </table>
            </div>
        `;

        let tabContract = '';
        if (stall.status === 'rented') {
            tabContract = `
                <div class="tab-content-panel" id="tab-contract">
                    <div style="text-align: center; padding: 30px 10px; background-color: var(--gray-50); border-radius: 8px; border: 1px dashed var(--gray-200);">
                        <i class="fa-solid fa-store" style="font-size: 32px; color: var(--gray-300); margin-bottom: 12px;"></i>
                        <p style="margin: 0; font-size: 13px; color: var(--gray-500);">Sạp hiện tại đã có hộ tiểu thương đăng ký kinh doanh hoạt động.</p>
                    </div>
                </div>
            `;
        } else {
            tabContract = `
                <div class="tab-content-panel" id="tab-contract">
                    <div style="text-align: center; padding: 30px 10px; background-color: var(--gray-50); border-radius: 8px; border: 1px dashed var(--gray-200);">
                        <i class="fa-solid fa-file-signature" style="font-size: 32px; color: var(--gray-300); margin-bottom: 12px;"></i>
                        <p style="margin: 0 0 12px; font-size: 13px; color: var(--gray-500);">Sạp này còn trống, bạn có thể gửi yêu cầu đăng ký thuê vị trí sạp kinh doanh.</p>
                        <a href="${CONFIG.URL.BASE}home/register?stall_code=${stall.stall_code}" class="btn btn-primary btn-sm" style="margin: 0; font-size: 12px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none;">
                            <i class="fa-solid fa-plus"></i> Đăng ký thuê Sạp trực tuyến
                        </a>
                    </div>
                </div>
            `;
        }

        pane.innerHTML = tabHeaders + tabGeneral + tabContract;
        bindTabEvents();
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
    fetch(window.BASE_URL + 'api/changeMarketScope?id=' + marketId)
        .then(res => res.json())
        .then(data => {
            if (data.status === 200) {
                location.reload();
            } else {
                alert(data.message || 'Không thể chuyển đổi chợ.');
            }
        })
        .catch(() => alert('Lỗi kết nối mạng.'));
}
</script>

