    <!-- ================= HEADER ================= -->
    <header id="siteHeader">
        <div class="container header-inner">
            <div class="brand">
                <div class="brand-mark">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M3 9L12 3L21 9V20C21 20.55 20.55 21 20 21H4C3.45 21 3 20.55 3 20V9Z" stroke="white"
                            stroke-width="1.8" stroke-linejoin="round" />
                        <path d="M9 21V13H15V21" stroke="white" stroke-width="1.8" stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="brand-name">
                    <strong>CHỢ TRUNG TÂM THÀNH PHỐ</strong>
                    <span>Ban Quản lý Chợ</span>
                </div>
            </div>
            <nav class="main-nav">
                <a href="<?php echo BASE_URL; ?>" class="<?php echo ($activePage ?? 'home') === 'home' ? 'active' : ''; ?>">Trang chủ</a>
                <a href="<?php echo BASE_URL; ?>home/about" class="<?php echo ($activePage ?? 'home') === 'about' ? 'active' : ''; ?>">Giới thiệu</a>
                <a href="<?php echo BASE_URL; ?>home/map" class="<?php echo ($activePage ?? 'home') === 'map' ? 'active' : ''; ?>">Sơ đồ chợ (SVG)</a>
                <a href="<?php echo BASE_URL; ?>home/map_tree" class="<?php echo ($activePage ?? 'home') === 'map_tree' ? 'active' : ''; ?>">Tra cứu Sạp (Cây)</a>
                <a href="<?php echo BASE_URL; ?>home/traders" class="<?php echo ($activePage ?? 'home') === 'traders' ? 'active' : ''; ?>">Tiểu thương</a>
                <a href="<?php echo BASE_URL; ?>home/register" class="<?php echo ($activePage ?? 'home') === 'register' ? 'active' : ''; ?>">Đăng ký thuê sạp</a>
                <a href="<?php echo BASE_URL; ?>#tintuc">Tin tức</a>
                <a href="<?php echo BASE_URL; ?>#thuvien">Thư viện</a>
                <a href="<?php echo BASE_URL; ?>#lienhe">Liên hệ</a>
            </nav>
            <div class="header-right">
                <a href="<?php echo BASE_URL; ?>home/login" class="login-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M12 12C14.21 12 16 10.21 16 8C16 5.79 14.21 4 12 4C9.79 4 8 5.79 8 8C8 10.21 9.79 12 12 12Z"
                            stroke="white" stroke-width="1.8" />
                        <path d="M4 20C4 16.69 7.58 14 12 14C16.42 14 20 16.69 20 20" stroke="white" stroke-width="1.8"
                            stroke-linecap="round" />
                    </svg>
                    <span>Đăng nhập</span>
                </a>
                <button class="menu-toggle" id="menuToggle" aria-label="Mở menu"
                    aria-expanded="false"><span></span><span></span><span></span></button>
            </div>
        </div>
        <div class="mobile-nav" id="mobileNav">
            <nav class="mobile-nav-panel">
                <a href="<?php echo BASE_URL; ?>" class="<?php echo ($activePage ?? 'home') === 'home' ? 'active' : ''; ?>">Trang chủ</a>
                <a href="<?php echo BASE_URL; ?>home/about" class="<?php echo ($activePage ?? 'home') === 'about' ? 'active' : ''; ?>">Giới thiệu</a>
                <a href="<?php echo BASE_URL; ?>home/map" class="<?php echo ($activePage ?? 'home') === 'map' ? 'active' : ''; ?>">Sơ đồ chợ (SVG)</a>
                <a href="<?php echo BASE_URL; ?>home/map_tree" class="<?php echo ($activePage ?? 'home') === 'map_tree' ? 'active' : ''; ?>">Tra cứu Sạp (Cây)</a>
                <a href="<?php echo BASE_URL; ?>home/traders" class="<?php echo ($activePage ?? 'home') === 'traders' ? 'active' : ''; ?>">Tiểu thương</a>
                <a href="<?php echo BASE_URL; ?>home/register" class="<?php echo ($activePage ?? 'home') === 'register' ? 'active' : ''; ?>">Đăng ký thuê sạp</a>
                <a href="<?php echo BASE_URL; ?>#tintuc">Tin tức</a>
                <a href="<?php echo BASE_URL; ?>#thuvien">Thư viện</a>
                <a href="<?php echo BASE_URL; ?>#lienhe">Liên hệ</a>
                <a href="<?php echo BASE_URL; ?>home/login" class="mobile-login">Đăng nhập</a>
            </nav>
        </div>
    </header>
