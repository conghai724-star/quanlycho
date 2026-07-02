<header class="topbar">
    <div class="topbar-left">
        <!-- Nút toggle sidebar trên mobile -->
        <button class="sidebar-toggle" type="button" aria-label="Mở menu" aria-controls="sidebar" aria-expanded="false">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        
        <!-- Breadcrumb dẫn đường -->
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <a href="<?php echo BASE_URL; ?>admin/dashboard" class="bc-link">Home</a>
            <span class="separator">/</span>
            <span class="current" aria-current="page"><?php echo $title ?? 'Tổng quan'; ?></span>
        </nav>
    </div>

    <!-- Ô tìm kiếm trang hoặc chạy lệnh -->
    <div class="search-box">
        <svg class="s-icon" width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="7" cy="7" r="5"/><path d="M11 11l3.5 3.5"/></svg>
        <input type="text" placeholder="Tìm nhanh sạp hoặc tiểu thương..." aria-label="Tìm kiếm">
        <kbd>⌘K</kbd>
    </div>

    <!-- Actions bên phải -->
    <div class="topbar-right">
        <!-- Nút bật/tắt Dark Mode (Vanilla JS của Gentelella tự động xử lý lưu localStorage) -->
        <button class="tb-btn theme-toggle" type="button" title="Chuyển chế độ tối/sáng" aria-label="Toggle theme" aria-pressed="false">
            <svg class="theme-icon-light" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
            <svg class="theme-icon-dark" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
        </button>

        <!-- Dropdown Thông báo -->
        <button class="tb-btn tb-notifications" type="button" title="Thông báo" aria-label="Thông báo" aria-haspopup="dialog" aria-expanded="false" onclick="alert('Không có thông báo mới!')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M12 3a6 6 0 00-6 6c0 6-3 7-3 7h18s-3-1-3-7a6 6 0 00-6-6z"/><path d="M10.5 21a1.5 1.5 0 003 0"/></svg>
        </button>

        <!-- Dropdown Tin nhắn -->
        <button class="tb-btn tb-messages" type="button" title="Tin nhắn" aria-label="Tin nhắn" aria-haspopup="dialog" aria-expanded="false" onclick="alert('Không có tin nhắn mới!')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="M2 7l10 6 10-6"/></svg>
        </button>

        <!-- Avatar người dùng -->
        <button class="tb-avatar" type="button" aria-label="Menu tài khoản" aria-haspopup="menu" aria-expanded="false" style="background-color: var(--primary); color: white; font-weight: bold; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center;">
            <?php echo strtoupper(substr(session::get('username', 'Q'), 0, 1)); ?>
        </button>
    </div>
</header>

<!-- Main area of dashboard -->
<main id="main-content" tabindex="-1" class="main">
<div class="page-wrapper">

    <!-- Tiêu đề trang -->
    <div class="page-header" style="margin-bottom: 24px;">
        <div class="page-header-row">
            <div>
                <div class="page-pretitle">Hệ thống Quản lý Chợ Smart</div>
                <h1 class="page-title"><?php echo $title ?? 'Dashboard'; ?></h1>
            </div>
            <!-- Nút lối tắt -->
            <div class="page-actions">
                <a href="<?php echo BASE_URL; ?>admin/traders" class="btn btn-outline" style="text-decoration: none;">
                    <i class="fa-solid fa-users me-1" style="font-size: 11px;"></i> Tiểu thương
                </a>
                <a href="<?php echo BASE_URL; ?>admin/stalls" class="btn btn-primary" style="text-decoration: none; color: white;">
                    <i class="fa-solid fa-store me-1" style="font-size: 11px;"></i> Quản lý Sạp
                </a>
            </div>
        </div>
    </div>
