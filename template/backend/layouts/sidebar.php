<aside class="sidebar" aria-label="Primary navigation">
    <div class="sidebar-brand">
        <div class="brand-icon" style="background-color: var(--primary); color: white; border-radius: 6px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; font-weight: bold;">C</div>
        <div class="brand-name">CHỢ SMART <small style="font-size: 10px; color: var(--text-muted)">BQL</small></div>
    </div>
    
    <nav class="sidebar-nav">
        <!-- Nhóm chung -->
        <div class="nav-group">
            <div class="nav-label">Chung</div>
            
            <!-- Dashboard -->
            <a class="nav-link <?php echo (isset($title) && $title === 'Bảng Điều Khiển') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/dashboard">
                <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="4" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="10" width="7" height="11" rx="1.5"/></svg>
                <span class="nav-text">Tổng quan (Dashboard)</span>
            </a>
        </div>
        
        <!-- Nhóm quản lý sạp & tiểu thương -->
        <div class="nav-group">
            <div class="nav-label">Mặt bằng & Con người</div>
            
            <!-- Sạp chợ -->
            <a class="nav-link" href="<?php echo BASE_URL; ?>admin/stalls">
                <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M9 10v9M15 10v9"/></svg>
                <span class="nav-text">Quản lý Sạp chợ</span>
            </a>

            <!-- Sơ đồ chợ -->
            <a class="nav-link <?php echo (isset($title) && $title === 'Thiết lập Sơ đồ chợ tương tác') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/map_editor">
                <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21"/><line x1="9" y1="3" x2="9" y2="18"/><line x1="15" y1="6" x2="15" y2="21"/></svg>
                <span class="nav-text">Thiết lập Sơ đồ chợ</span>
            </a>

            <!-- Tiểu thương -->
            <a class="nav-link" href="<?php echo BASE_URL; ?>admin/traders">
                <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                <span class="nav-text">Quản lý Tiểu thương</span>
            </a>

            <!-- Hợp đồng -->
            <a class="nav-link" href="<?php echo BASE_URL; ?>admin/contracts">
                <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="M2 7l10 6 10-6"/></svg>
                <span class="nav-text">Hợp đồng Thuê sạp</span>
            </a>
        </div>

        <!-- Nhóm dịch vụ & tài chính -->
        <div class="nav-group">
            <div class="nav-label">Vận hành & Tài chính</div>
            
            <!-- Chỉ số dịch vụ -->
            <a class="nav-link" href="<?php echo BASE_URL; ?>admin/utilities">
                <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span class="nav-text">Chỉ số Điện & Nước</span>
            </a>

            <!-- Hóa đơn -->
            <a class="nav-link" href="<?php echo BASE_URL; ?>admin/bills">
                <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 21V3h14v18l-3-2-3 2-3-2-3 2-2-2z"/><path d="M9 8h6M9 12h6M9 16h4"/></svg>
                <span class="nav-text">Hóa đơn Dịch vụ</span>
            </a>

            <!-- Thu Chi -->
            <a class="nav-link" href="<?php echo BASE_URL; ?>admin/transactions">
                <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="12" y1="2" x2="12" y2="22"/><path d="M16 6H9.5a3.5 3.5 0 100 7h5a3.5 3.5 0 010 7H7"/></svg>
                <span class="nav-text">Thu - Chi tài chính</span>
            </a>
        </div>

        <!-- Nhóm kiểm tra & hệ thống -->
        <div class="nav-group">
            <div class="nav-label">Hệ thống</div>
            
            <!-- An toàn thực phẩm -->
            <a class="nav-link" href="<?php echo BASE_URL; ?>admin/foodsafety">
                <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="12" cy="12" r="3"/></svg>
                <span class="nav-text">An toàn thực phẩm</span>
            </a>

            <!-- Tài khoản -->
            <a class="nav-link" href="<?php echo BASE_URL; ?>admin/users">
                <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span class="nav-text">Tài khoản & Quyền</span>
            </a>

            <!-- Tùy biến chủ đề -->
            <a class="nav-link <?php echo (isset($title) && $title === 'Tùy Biến Giao Diện') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/theme">
                <svg class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M19 11H5a2 2 0 00-2 2v2a2 2 0 002 2h2v3a1 1 0 001 1h3a1 1 0 001-1v-3h7a2 2 0 002-2v-2a2 2 0 00-2-2z"/><path d="M19 11V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v6"/></svg>
                <span class="nav-text">Trình tạo chủ đề</span>
            </a>
        </div>
    </nav>

    <!-- Thùng chứa user sidebar footer của Gentelella -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="avatar" style="background-color: var(--primary); color: white; font-weight: bold; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center;">
                <?php echo strtoupper(substr(session::get('username', 'Q'), 0, 1)); ?>
                <span class="online"></span>
            </div>
            <div class="sidebar-user-info">
                <div class="name" style="font-weight: 600; font-size: 13.5px;"><?php echo session::get('user_fullname', 'BQL Chợ'); ?></div>
                <div class="role" style="font-size: 11px;"><?php echo session::get('user_role') === 'admin' ? 'Ban Quản Lý' : 'Nhân viên'; ?></div>
            </div>
            <!-- Bấm để đổi mật khẩu -->
            <a href="<?php echo BASE_URL; ?>admin/change_password" class="more-btn" aria-label="Đổi mật khẩu" title="Đổi mật khẩu" style="display: flex; align-items: center; justify-content: center; color: var(--text-muted); margin-right: 8px;">
                <i class="fa-solid fa-key" style="font-size: 14px;"></i>
            </a>
            <!-- Bấm để đăng xuất -->
            <a href="<?php echo BASE_URL; ?>home/logout" class="more-btn" aria-label="Đăng xuất" title="Đăng xuất" style="display: flex; align-items: center; justify-content: center; color: var(--text-muted); hover: {color: var(--red)}">
                <i class="fa-solid fa-right-from-bracket" style="font-size: 14px;"></i>
            </a>
        </div>
    </div>
</aside>
