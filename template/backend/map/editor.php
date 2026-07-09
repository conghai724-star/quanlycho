<?php
/**
 * View template: Thiết lập Sơ đồ chợ tương tác (Admin Map Editor)
 */
?>

<!-- Nạp FontAwesome nếu chưa có (Sidebar đã nạp nhưng đảm bảo) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Tổng thể Editor layout */
    .map-editor-container {
        display: grid;
        grid-template-columns: 280px 1fr 300px;
        height: calc(100vh - 120px);
        margin: -15px; /* Phủ kín phần nội dung chính */
        background-color: var(--card-bg);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
    }

    /* Các cột Panel */
    .editor-panel {
        display: flex;
        flex-direction: column;
        background-color: var(--card-bg);
        border-right: 1px solid var(--border-color);
        height: 100%;
        overflow: hidden;
    }

    .editor-panel-right {
        border-right: none;
        border-left: 1px solid var(--border-color);
    }

    .panel-header {
        padding: 12px 16px;
        font-weight: 600;
        font-size: 14px;
        border-bottom: 1px solid var(--border-color);
        background-color: rgba(0, 0, 0, 0.02);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .panel-content {
        flex: 1;
        overflow-y: auto;
        padding: 16px;
    }

    /* Vùng Canvas chính */
    .editor-canvas-area {
        display: flex;
        flex-direction: column;
        height: 100%;
        background-color: var(--html-bg);
        position: relative;
        overflow: hidden;
    }

    /* Menu công cụ phía trên Canvas */
    .canvas-toolbar {
        height: 50px;
        background-color: var(--card-bg);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 16px;
        z-index: 10;
    }

    .toolbar-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Canvas cuộn và thu phóng */
    .canvas-viewport {
        flex: 1;
        overflow: auto;
        position: relative;
        cursor: grab;
    }
    .canvas-viewport:active {
        cursor: grabbing;
    }

    .canvas-grid {
        width: 2400px;
        height: 1800px;
        background-color: var(--card-bg);
        background-image: 
            linear-gradient(rgba(0,0,0,0.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(0,0,0,0.04) 1px, transparent 1px);
        background-size: 20px 20px; /* Kích thước ô lưới snap */
        position: relative;
        transform-origin: 0 0;
        transition: transform 0.1s ease-out;
    }

    [data-theme="dark"] .canvas-grid {
        background-image: 
            linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
    }

    /* Các khối vẽ (Map Elements) */
    .map-element {
        position: absolute;
        border: 2px solid #555;
        background-color: rgba(200, 200, 200, 0.8);
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 11px;
        color: #222;
        cursor: move;
        user-select: none;
        border-radius: 4px;
        padding: 4px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        transform-origin: center center;
    }

    .map-element.selected {
        border: 2px dashed var(--primary) !important;
        box-shadow: 0 0 0 3px rgba(26, 187, 156, 0.25);
    }

    .map-element i {
        display: block;
        font-size: var(--icon-size, 1.5em);
        line-height: 1;
        margin-bottom: 4px;
    }

    .map-element strong {
        display: block;
        font-size: 0.72em;
        line-height: 1.08;
        letter-spacing: 0.01em;
    }

    .map-element.is-icon-only {
        overflow: visible;
        padding: 0;
        background: transparent !important;
        border-color: transparent !important;
        box-shadow: none;
    }

    .map-element.is-icon-only i {
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

    .map-element.is-icon-only.map-element-stall strong {
        position: absolute;
        left: 50%;
        bottom: -18px;
        transform: translateX(-50%);
        width: max-content;
        max-width: 160px;
        color: #0d47a1;
        font-size: 12px;
        line-height: 1.1;
        white-space: nowrap;
        text-shadow: 0 1px 2px #fff;
    }

    /* Trạng thái sạp trên sơ đồ */
    .map-element-stall {
        background-color: #ffffff;
        border-color: #b0bec5;
    }
    .map-element-stall.status-green, .map-element-stall.status-rented {
        background-color: #e8f5e9;
        border-color: #2e7d32;
        color: #1b5e20;
    }
    .map-element-stall.status-white, .map-element-stall.status-empty {
        background-color: #e3f2fd;
        border-color: #1565c0;
        color: #0d47a1;
    }
    .map-element-stall.status-yellow, .map-element-stall.status-repairing {
        background-color: #fffde7;
        border-color: #fbc02d;
        color: #f57f17;
    }
    .map-element-stall.status-red, .map-element-stall.status-locked {
        background-color: #ffebee;
        border-color: #c62828;
        color: #b71c1c;
    }

    /* Các khối tiện ích trang trí */
    .map-element-gate {
        background-color: #ffe0b2;
        border-color: #ef6c00;
        color: #e65100;
    }
    .map-element-door {
        background-color: #d7ccc8;
        border-color: #4e342e;
        color: #3e2723;
    }
    .map-element-street {
        background-color: #eceff1;
        border-color: #cfd8dc;
        color: #37474f;
        border-radius: 0;
        border-style: dotted;
    }
    .map-element-utility {
        background-color: #e1bee7;
        border-color: #6a1b9a;
        color: #4a148c;
    }
    .map-element-office {
        background-color: #e0f7fa;
        border-color: #00838f;
        color: #006064;
    }

    .map-element-street-straight,
    .map-element-street-corner,
    .map-element-fence {
        overflow: hidden;
        padding: 0;
        box-shadow: none;
        border-radius: 2px;
    }

    .map-element-street-straight {
        background:
            linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0.00)),
            linear-gradient(90deg, #6f7781, #8d95a0);
        border-color: #5d6470;
        color: transparent;
        border-style: solid;
    }

    .map-element-street-straight::before {
        content: "";
        position: absolute;
        left: 8px;
        right: 8px;
        top: 50%;
        height: 4px;
        transform: translateY(-50%);
        border-radius: 999px;
        background:
            repeating-linear-gradient(90deg, rgba(255,255,255,0.92) 0 18px, transparent 18px 30px);
        opacity: 0.95;
    }

    .map-element-street-corner {
        background:
            linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0.00)),
            linear-gradient(90deg, #6f7781, #8d95a0);
        border-color: #5d6470;
        color: transparent;
        border-style: solid;
    }

    .map-element-street-corner::before,
    .map-element-street-corner::after {
        content: "";
        position: absolute;
        background: rgba(255,255,255,0.95);
        border-radius: 999px;
    }

    .map-element-street-corner::before {
        left: 9px;
        top: 50%;
        width: calc(50% + 2px);
        height: 4px;
        transform: translateY(-50%);
    }

    .map-element-street-corner::after {
        left: 50%;
        top: 9px;
        width: 4px;
        height: calc(50% + 2px);
        transform: translateX(-50%);
    }

    .map-element-fence {
        background:
            repeating-linear-gradient(90deg, #ddc9b0 0 8px, #f3eadf 8px 16px);
        border-color: #bfa98d;
        color: transparent;
        border-style: dashed;
    }

    .map-element-fence::before {
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

    .map-element-security-room {
        background:
            linear-gradient(180deg, rgba(255,255,255,0.18), rgba(255,255,255,0.04)),
            linear-gradient(135deg, rgba(12,17,29,0.04), rgba(12,17,29,0.00));
        border-style: solid;
        border-color: #94a3b8;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.18);
    }

    .map-element-security-room::before {
        content: "";
        position: absolute;
        inset: 12px 10px auto;
        height: 2px;
        background: rgba(255,255,255,0.72);
        border-radius: 999px;
    }

    .map-element-security-room::after {
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

    .map-element-street-straight,
    .map-element-street-corner,
    .map-element-fence {
        overflow: hidden;
        padding: 0;
        box-shadow: none;
        border-radius: 2px;
    }

    .map-element-street-straight {
        background:
            linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0.00)),
            linear-gradient(90deg, #6f7781, #8d95a0);
        border-color: #5d6470;
        color: transparent;
        border-style: solid;
    }

    .map-element-street-straight::before {
        content: "";
        position: absolute;
        left: 8px;
        right: 8px;
        top: 50%;
        height: 4px;
        transform: translateY(-50%);
        border-radius: 999px;
        background:
            repeating-linear-gradient(90deg, rgba(255,255,255,0.92) 0 18px, transparent 18px 30px);
        opacity: 0.95;
    }

    .map-element-street-corner {
        background:
            linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0.00)),
            linear-gradient(90deg, #6f7781, #8d95a0);
        border-color: #5d6470;
        color: transparent;
        border-style: solid;
    }

    .map-element-street-corner::before,
    .map-element-street-corner::after {
        content: "";
        position: absolute;
        background: rgba(255,255,255,0.95);
        border-radius: 999px;
    }

    .map-element-street-corner::before {
        left: 9px;
        top: 50%;
        width: calc(50% + 2px);
        height: 4px;
        transform: translateY(-50%);
    }

    .map-element-street-corner::after {
        left: 50%;
        top: 9px;
        width: 4px;
        height: calc(50% + 2px);
        transform: translateX(-50%);
    }

    .map-element-fence {
        background:
            repeating-linear-gradient(90deg, #ddc9b0 0 8px, #f3eadf 8px 16px);
        border-color: #bfa98d;
        color: transparent;
        border-style: dashed;
    }

    .map-element-fence::before {
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

    .map-element-security-room {
        background:
            linear-gradient(180deg, rgba(255,255,255,0.18), rgba(255,255,255,0.04)),
            linear-gradient(135deg, rgba(12,17,29,0.04), rgba(12,17,29,0.00));
        border-style: solid;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,0.18);
    }

    .map-element-security-room::before {
        content: "";
        position: absolute;
        inset: 12px 10px auto;
        height: 2px;
        background: rgba(255,255,255,0.72);
        border-radius: 999px;
    }

    .map-element-security-room::after {
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

    .map-element.is-icon-only,
    .map-element-gate,
    .map-element-door,
    .map-element-utility,
    .map-element-office,
    .map-element-security-room {
        background: transparent;
        border-color: transparent;
        box-shadow: none;
    }

    .map-element-gate { color: #e65100; }
    .map-element-door { color: #3e2723; }
    .map-element-utility { color: #4a148c; }
    .map-element-office { color: #006064; }
    .map-element-security-room { color: #1e293b; }

    .map-element-security-room::before,
    .map-element-security-room::after {
        display: none;
    }

    /* Nút kéo thả ở Toolbox */
    .toolbox-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        background: var(--html-bg);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        margin-bottom: 10px;
        cursor: grab;
        user-select: none;
        transition: all 0.2s;
        font-size: 13px;
        font-weight: 500;
    }

    .toolbox-item:hover {
        border-color: var(--primary);
        background: rgba(26, 187, 156, 0.05);
        transform: translateY(-1px);
    }

    .toolbox-item i {
        font-size: 16px;
        width: 20px;
        text-align: center;
    }

    .toolbox-preview {
        width: 24px;
        height: 18px;
        border-radius: 4px;
        flex: 0 0 auto;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.06);
        background: #eef2f7;
    }

    .toolbox-preview.street-straight::before {
        content: "";
        position: absolute;
        left: 2px;
        right: 2px;
        top: 50%;
        height: 4px;
        transform: translateY(-50%);
        border-radius: 999px;
        background: rgba(255,255,255,0.9);
        box-shadow: 0 -5px 0 rgba(255,255,255,0.5), 0 5px 0 rgba(255,255,255,0.5);
    }

    .toolbox-preview.street-corner::before,
    .toolbox-preview.street-corner::after {
        content: "";
        position: absolute;
        background: rgba(255,255,255,0.9);
        border-radius: 999px;
    }

    .toolbox-preview.street-corner::before {
        left: 3px;
        top: 3px;
        width: 18px;
        height: 4px;
    }

    .toolbox-preview.street-corner::after {
        left: 3px;
        top: 3px;
        width: 4px;
        height: 12px;
    }

    .toolbox-preview.fence {
        background: repeating-linear-gradient(90deg, #e9ddc9 0 4px, #f8f1e6 4px 8px);
    }

    .toolbox-preview.fence::before {
        content: "";
        position: absolute;
        left: 2px;
        right: 2px;
        top: 50%;
        height: 2px;
        transform: translateY(-50%);
        background: rgba(96, 76, 44, 0.8);
    }

    .toolbox-preview.security-room {
        background: linear-gradient(180deg, #dbeafe, #cbd5e1);
    }

    .toolbox-preview.security-room::before {
        content: "";
        position: absolute;
        left: 4px;
        right: 4px;
        top: 4px;
        height: 2px;
        background: rgba(255,255,255,0.85);
        border-radius: 999px;
    }

    .toolbox-preview.security-room::after {
        content: "";
        position: absolute;
        left: 5px;
        right: 5px;
        bottom: 3px;
        height: 6px;
        border: 1px solid rgba(255,255,255,0.55);
        border-radius: 3px;
    }

    .toolbox-preview {
        width: 24px;
        height: 18px;
        border-radius: 4px;
        flex: 0 0 auto;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.06);
        background: #eef2f7;
    }

    .toolbox-preview.street-straight::before {
        content: "";
        position: absolute;
        left: 2px;
        right: 2px;
        top: 50%;
        height: 4px;
        transform: translateY(-50%);
        border-radius: 999px;
        background: rgba(255,255,255,0.9);
        box-shadow: 0 -5px 0 rgba(255,255,255,0.5), 0 5px 0 rgba(255,255,255,0.5);
    }

    .toolbox-preview.street-corner::before,
    .toolbox-preview.street-corner::after {
        content: "";
        position: absolute;
        background: rgba(255,255,255,0.9);
        border-radius: 999px;
    }

    .toolbox-preview.street-corner::before {
        left: 3px;
        top: 3px;
        width: 18px;
        height: 4px;
    }

    .toolbox-preview.street-corner::after {
        left: 3px;
        top: 3px;
        width: 4px;
        height: 12px;
    }

    .toolbox-preview.fence {
        background: repeating-linear-gradient(90deg, #e9ddc9 0 4px, #f8f1e6 4px 8px);
    }

    .toolbox-preview.fence::before {
        content: "";
        position: absolute;
        left: 2px;
        right: 2px;
        top: 50%;
        height: 2px;
        transform: translateY(-50%);
        background: rgba(96, 76, 44, 0.8);
    }

    .toolbox-preview.security-room {
        background: linear-gradient(180deg, #dbeafe, #cbd5e1);
    }

    .toolbox-preview.security-room::before {
        content: "";
        position: absolute;
        left: 4px;
        right: 4px;
        top: 4px;
        height: 2px;
        background: rgba(255,255,255,0.85);
        border-radius: 999px;
    }

    .toolbox-preview.security-room::after {
        content: "";
        position: absolute;
        left: 5px;
        right: 5px;
        bottom: 3px;
        height: 6px;
        border: 1px solid rgba(255,255,255,0.55);
        border-radius: 3px;
    }

    /* Danh sách sạp chưa gán */
    .unmapped-search {
        padding: 6px 10px;
        font-size: 12px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        width: 100%;
        margin-bottom: 12px;
        background: var(--card-bg);
        color: var(--text-color);
    }

    .unmapped-stall-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 10px;
        background: var(--html-bg);
        border: 1px solid var(--border-color);
        border-radius: 4px;
        margin-bottom: 6px;
        font-size: 12px;
        cursor: grab;
        user-select: none;
    }

    .unmapped-stall-item:hover {
        border-color: var(--primary);
        background: rgba(26, 187, 156, 0.05);
    }

    /* Panel Thuộc tính */
    .property-group {
        margin-bottom: 14px;
    }

    .property-group label {
        display: block;
        font-size: 12px;
        font-weight: 500;
        margin-bottom: 5px;
        color: var(--text-muted);
    }

    .property-input {
        width: 100%;
        padding: 6px 10px;
        font-size: 13px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        background: var(--html-bg);
        color: var(--text-color);
    }

    .property-input:focus {
        border-color: var(--primary);
        outline: none;
    }

    /* Các nút điều khiển zoom */
    .zoom-badge {
        font-size: 12px;
        font-weight: 600;
        padding: 4px 8px;
        background-color: var(--html-bg);
        border: 1px solid var(--border-color);
        border-radius: 4px;
        min-width: 48px;
        text-align: center;
    }

    /* Nút lưu góc xoay hoặc điều chỉnh kích thước trực tiếp */
    .resize-handle {
        position: absolute;
        width: 10px;
        height: 10px;
        background-color: var(--primary);
        border: 1px solid #fff;
        border-radius: 50%;
        z-index: 5;
        display: none;
    }

    .resize-handle.handle-n { top: -4px; left: 50%; width: 28px; height: 8px; transform: translateX(-50%); cursor: n-resize; }
    .resize-handle.handle-e { top: 50%; right: -4px; width: 8px; height: 28px; transform: translateY(-50%); cursor: e-resize; }
    .resize-handle.handle-s { bottom: -4px; left: 50%; width: 28px; height: 8px; transform: translateX(-50%); cursor: s-resize; }
    .resize-handle.handle-w { top: 50%; left: -4px; width: 8px; height: 28px; transform: translateY(-50%); cursor: w-resize; }

    .rotate-handle {
        position: absolute;
        width: 12px;
        height: 12px;
        left: 50%;
        top: -26px;
        transform: translateX(-50%);
        border-radius: 50%;
        background: #ffffff;
        border: 2px solid var(--primary);
        z-index: 6;
        display: none;
        cursor: grab;
        box-shadow: 0 2px 6px rgba(0,0,0,0.12);
    }

    .rotate-handle::after {
        content: "";
        position: absolute;
        left: 50%;
        top: 100%;
        width: 2px;
        height: 14px;
        transform: translateX(-50%);
        background: var(--primary);
    }

    .map-element.selected .resize-handle,
    .map-element.selected .rotate-handle {
        display: block;
    }

    /* Badge trạng thái sạp trong panel thuộc tính */
    .badge-status-rented { background-color: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9; }
    .badge-status-empty { background-color: #e3f2fd; color: #0d47a1; border: 1px solid #bbdefb; }
    .badge-status-repairing { background-color: #fffde7; color: #f57f17; border: 1px solid #fff9c4; }
    .badge-status-locked { background-color: #ffebee; color: #b71c1c; border: 1px solid #ffcdd2; }
</style>

<div class="map-editor-container">
    <!-- PANEL TRÁI: HỘP CÔNG CỤ -->
    <div class="editor-panel">
        <div class="panel-header">
            <span><i class="fa-solid fa-toolbox"></i> Hộp Công Cụ</span>
        </div>
        <div class="panel-content">
            <!-- Các phần tử vẽ cơ bản -->
            <div style="font-weight: 600; font-size: 12px; margin-bottom: 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Phần tử cơ bản</div>
            
            <div class="toolbox-item" data-type="stall" draggable="true">
                <i class="fa-solid fa-store" style="color: #1565c0;"></i>
                <span>Sạp Chợ (Stall)</span>
            </div>
            <div class="toolbox-item" data-type="street" draggable="true">
                <span class="toolbox-preview street-straight"></span>
                <span>Đường thẳng</span>
            </div>
            <div class="toolbox-item" data-type="street-corner" draggable="true">
                <span class="toolbox-preview street-corner"></span>
                <span>Đường rẽ góc</span>
            </div>
            <div class="toolbox-item" data-type="gate" draggable="true">
                <i class="fa-solid fa-archway" style="color: #ef6c00;"></i>
                <span>Cổng chợ</span>
            </div>
            <div class="toolbox-item" data-type="door" draggable="true">
                <i class="fa-solid fa-door-open" style="color: #4e342e;"></i>
                <span>Cửa ra vào</span>
            </div>
            <div class="toolbox-item" data-type="utility" draggable="true">
                <i class="fa-solid fa-restroom" style="color: #6a1b9a;"></i>
                <span>Khu Vệ sinh / Tiện ích</span>
            </div>
            <div class="toolbox-item" data-type="fence" draggable="true">
                <span class="toolbox-preview fence"></span>
                <span>Hàng rào</span>
            </div>
            <div class="toolbox-item" data-type="security-room" draggable="true">
                <span class="toolbox-preview security-room"></span>
                <span>Phòng bảo vệ</span>
            </div>
            <div class="toolbox-item" data-type="office" draggable="true">
                <i class="fa-solid fa-building-user" style="color: #00838f;"></i>
                <span>Văn phòng BQL</span>
            </div>

            <!-- Danh sách sạp chưa gán lên sơ đồ -->
            <div style="font-weight: 600; font-size: 12px; margin: 20px 0 10px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; justify-content: space-between;">
                <span>Sạp chưa có trên sơ đồ</span>
                <span style="background: rgba(26, 187, 156, 0.1); color: var(--primary); padding: 2px 6px; border-radius: 10px; font-size: 10px;" id="unmapped-count"><?php echo count($unmappedStalls); ?></span>
            </div>
            <input type="text" id="unmapped-search" class="unmapped-search" placeholder="Tìm nhanh sạp...">
            <div id="unmapped-stalls-list" style="max-height: 250px; overflow-y: auto; padding-right: 4px;">
                <?php if (!empty($unmappedStalls)): ?>
                    <?php foreach ($unmappedStalls as $stall): ?>
                        <div class="unmapped-stall-item" data-stall-id="<?php echo $stall['id']; ?>" data-stall-code="<?php echo htmlspecialchars($stall['stall_code']); ?>" draggable="true">
                            <div>
                                <i class="fa-solid fa-store" style="margin-right: 6px; color: var(--primary);"></i>
                                <strong style="font-size: 12px;"><?php echo htmlspecialchars($stall['stall_code']); ?></strong>
                            </div>
                            <span style="font-size: 10px; color: var(--text-muted);"><?php echo $stall['area_size']; ?> m²</span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="font-size: 12px; color: var(--text-muted); text-align: center; margin-top: 10px;">Đã đưa tất cả sạp lên bản đồ!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- CANVAS CHÍNH (VÙNG LÀM VIỆC) -->
    <div class="editor-canvas-area">
        <!-- Thanh công cụ Canvas -->
        <div class="canvas-toolbar">
            <div class="toolbar-group">
                <button class="btn btn-outline btn-sm" id="btn-zoom-out" title="Thu nhỏ"><i class="fa-solid fa-minus"></i></button>
                <span class="zoom-badge" id="zoom-value">100%</span>
                <button class="btn btn-outline btn-sm" id="btn-zoom-in" title="Phóng to"><i class="fa-solid fa-plus"></i></button>
                <button class="btn btn-outline btn-sm" id="btn-zoom-reset" title="Thu phóng mặc định"><i class="fa-solid fa-rotate-left"></i></button>
                <span style="border-left: 1px solid var(--border-color); height: 20px; margin: 0 4px;"></span>
                <label style="font-size: 12px; display: flex; align-items: center; gap: 6px; cursor: pointer; user-select: none;">
                    <input type="checkbox" id="chk-snap-grid" checked> Snap to Grid (20px)
                </label>
            </div>

            <div class="toolbar-group">
                <button class="btn btn-outline btn-sm" id="btn-clear-map" style="color: var(--red);"><i class="fa-solid fa-trash-can"></i> Xóa Bản Đồ</button>
                <button class="btn btn-primary btn-sm" id="btn-save-map"><i class="fa-solid fa-floppy-disk"></i> Lưu Bản Đồ</button>
            </div>
        </div>

        <!-- Viewport cuộn -->
        <div class="canvas-viewport" id="canvas-viewport">
            <div class="canvas-grid" id="canvas-grid">
                <!-- Các phần tử vẽ động sẽ được render ở đây qua JS -->
            </div>
        </div>
    </div>

    <!-- PANEL PHẢI: THUỘC TÍNH PHẦN TỬ -->
    <div class="editor-panel editor-panel-right">
        <div class="panel-header">
            <span><i class="fa-solid fa-sliders"></i> Thuộc Tính</span>
        </div>
        <div class="panel-content" id="property-panel-content">
            <div style="text-align: center; color: var(--text-muted); padding: 40px 10px;" id="no-selection-msg">
                <i class="fa-solid fa-mouse-pointer" style="font-size: 32px; margin-bottom: 12px; opacity: 0.5;"></i>
                <p style="font-size: 13px;">Click chọn một phần tử trên sơ đồ để thiết lập thông số.</p>
            </div>

            <div id="selection-form" style="display: none;">
                <!-- Loại phần tử -->
                <div class="property-group">
                    <label>Loại phần tử</label>
                    <input type="text" id="prop-type-name" class="property-input" readonly style="background: rgba(0,0,0,0.03); font-weight: bold;">
                </div>

                <!-- Tên / Nhãn hiển thị -->
                <div class="property-group">
                    <label for="prop-name">Tên hiển thị / Nhãn</label>
                    <input type="text" id="prop-name" class="property-input" placeholder="Ví dụ: Lối đi số 1">
                </div>

                <!-- Chọn Sạp (Nếu là sạp) -->
                <div class="property-group" id="group-stall-binding" style="display: none;">
                    <label for="prop-stall-id">Liên kết Sạp chợ thật <span style="color: var(--red)">*</span></label>
                    <select id="prop-stall-id" class="property-input">
                        <option value="">-- Chọn Sạp chưa gán --</option>
                        <?php foreach ($stalls as $st): ?>
                            <option value="<?php echo $st['id']; ?>"><?php echo htmlspecialchars($st['stall_code']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tọa độ X, Y -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div class="property-group">
                        <label for="prop-x">Tọa độ X (px)</label>
                        <input type="number" id="prop-x" class="property-input" step="20">
                    </div>
                    <div class="property-group">
                        <label for="prop-y">Tọa độ Y (px)</label>
                        <input type="number" id="prop-y" class="property-input" step="20">
                    </div>
                </div>

                <!-- Chiều rộng & Chiều cao -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div class="property-group">
                        <label for="prop-w">Chiều rộng (px)</label>
                        <input type="number" id="prop-w" class="property-input" min="20" step="20">
                    </div>
                    <div class="property-group">
                        <label for="prop-h">Chiều cao (px)</label>
                        <input type="number" id="prop-h" class="property-input" min="20" step="20">
                    </div>
                </div>

                <!-- Góc xoay -->
                <div class="property-group">
                    <label for="prop-rotation">Góc xoay (Độ)</label>
                    <input type="number" id="prop-rotation" class="property-input" min="0" max="359" step="1" value="0">
                </div>

                <!-- Màu sắc (Nếu không phải sạp) -->
                <div class="property-group" id="group-color-picker">
                    <label for="prop-color">Màu nền tùy chọn</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="color" id="prop-color" class="property-input" style="width: 45px; padding: 2px; height: 32px; cursor: pointer;">
                        <input type="text" id="prop-color-hex" class="property-input" placeholder="#FFFFFF" style="flex: 1;">
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 20px 0;">

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <button class="btn btn-outline btn-block" id="btn-delete-element" style="color: var(--red); border-color: rgba(211, 47, 47, 0.3); background: rgba(211, 47, 47, 0.02);">
                        <i class="fa-solid fa-trash"></i> Xóa Phần Tử Này
                    </button>
                </div>

                <!-- Mục hiển thị thông tin sạp chi tiết khi chọn sạp -->
                <div id="stall-info-panel" style="margin-top: 20px; border-top: 1px dashed var(--border-color); padding-top: 16px; display: none;">
                    <h5 style="margin: 0 0 12px 0; font-size: 13px; font-weight: 600; color: var(--primary); display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-circle-info"></i> Thông tin Sạp liên kết
                    </h5>
                    <div style="font-size: 12.5px; line-height: 1.6; display: flex; flex-direction: column; gap: 8px; background: rgba(0,0,0,0.02); padding: 12px; border-radius: 6px; border: 1px solid var(--border-color);">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Loại sạp:</span>
                            <strong id="stall-info-type">--</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Khu vực:</span>
                            <strong id="stall-info-area-name">--</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Diện tích:</span>
                            <strong id="stall-info-area">--</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: var(--text-muted);">Giá cơ bản:</span>
                            <strong id="stall-info-price">--</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-muted);">Trạng thái:</span>
                            <span class="badge" id="stall-info-status" style="font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">--</span>
                        </div>
                        
                        <!-- Thông tin tiểu thương & hợp đồng thuê -->
                        <div id="stall-info-trader-row" style="display: none; flex-direction: column; gap: 6px; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 8px; margin-top: 4px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--text-muted);">Tiểu thương:</span>
                                <strong id="stall-info-trader" style="color: var(--primary);">--</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--text-muted);">Số điện thoại:</span>
                                <strong id="stall-info-phone">--</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--text-muted);">Số hợp đồng:</span>
                                <strong id="stall-info-contract">--</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--text-muted);">Hạn thuê:</span>
                                <strong id="stall-info-contract-end" style="color: var(--red);">--</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Nạp dữ liệu sạp từ DB vào JS -->
<script>
    window.DB_STALLS = <?php echo json_encode($stalls); ?>;
</script>

<!-- Script xử lý bản đồ -->
<script src="<?php echo BASE_URL; ?>public/assets/js/pages/admin/map-editor.js?v=<?php echo time(); ?>"></script>
