<?php
// Đọc theme từ cookie - được set bởi JS khi user chọn theme
// Dùng cookie thay vì localStorage vì PHP có thể đọc ở server-side
$theme = 'light';
if (isset($_COOKIE['app_theme']) && in_array($_COOKIE['app_theme'], ['light', 'dark'])) {
    $theme = $_COOKIE['app_theme'];
}
$htmlBg = $theme === 'dark' ? '#0f1623' : '#f5f7fb';
?>
<!DOCTYPE html>
<html lang="vi" data-theme="<?php echo $theme; ?>" style="background:<?php echo $htmlBg; ?>">
<head>
    <!-- Critical: Đặt màu nền cho cả HTML và BODY trước khi CSS ngoài load -->
    <style>
        html, body { background: <?php echo $htmlBg; ?> !important; }
    
        /* Khai báo View Transition */
        @view-transition { navigation: auto; }
        ::view-transition-old(root) { animation: none; }
        ::view-transition-new(root) { animation: 180ms ease both vt-enter; }
        @keyframes vt-enter { from { opacity: 0; } to { opacity: 1; } }
        @media (prefers-reduced-motion: reduce) {
            ::view-transition-new(root) { animation-duration: 0s; }
        }

        /* Top Loading Bar - tạo cảm giác tải trang cực mượt dạng SPA */
        #app-loading-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            background: #1ABB9C; /* Màu xanh Gentelella */
            z-index: 999999;
            width: 0;
            opacity: 1;
            transition: width 0.3s ease, opacity 0.3s ease;
            box-shadow: 0 0 8px rgba(26, 187, 156, 0.6);
        }
        #app-loading-spinner {
            position: fixed;
            top: 12px;
            right: 16px;
            z-index: 999999;
            width: 14px;
            height: 14px;
            border: 2px solid rgba(26, 187, 156, 0.2);
            border-top-color: #1ABB9C;
            border-radius: 50%;
            animation: app-spin 0.6s linear infinite;
            transition: opacity 0.3s ease;
        }
        @keyframes app-spin { to { transform: rotate(360deg); } }
    </style>



    <script>
        window.BASE_URL = '<?php echo BASE_URL; ?>';
        window.CSRF_TOKEN = '<?php echo security::getToken(); ?>';
        (function(){
            try {
                var stored = localStorage.getItem('theme');
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                var theme = stored || (prefersDark ? 'dark' : 'light');
                var html = document.documentElement;

                // Nếu PHP đã set data-theme đúng (từ cookie) thì chỉ cần sync không cần override
                var phpTheme = html.getAttribute('data-theme');
                if (phpTheme !== theme) {
                    html.setAttribute('data-theme', theme);
                    html.style.background = theme === 'dark' ? '#0f1623' : '#f5f7fb';
                }

                // Ghi cookie để PHP đọc ở request tiếp theo (path=/ để áp dụng toàn site)
                document.cookie = 'app_theme=' + theme + ';path=/;max-age=31536000;SameSite=Lax';

                // Lắng nghe thay đổi data-theme (khi user bấm toggle dark/light)
                // để cập nhật cookie ngay lập tức cho lần navigate tiếp theo
                new MutationObserver(function(mutations) {
                    mutations.forEach(function(m) {
                        if (m.attributeName === 'data-theme') {
                            var newTheme = html.getAttribute('data-theme');
                            localStorage.setItem('theme', newTheme);
                            document.cookie = 'app_theme=' + newTheme + ';path=/;max-age=31536000;SameSite=Lax';
                        }
                    });
                }).observe(html, { attributes: true, attributeFilter: ['data-theme'] });
            } catch(e) {}
        })();
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Quản Lý Chợ Smart'; ?> - Ban Quản Lý</title>
    
    <!-- Favicon & PWA meta -->
    <link rel="icon" href="<?php echo BASE_URL; ?>public/assets/images/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <meta name="theme-color" content="#1ABB9C" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#1a2332" media="(prefers-color-scheme: dark)">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="<?php echo BASE_URL; ?>public/assets/images/apple-touch-icon.svg">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 (Dùng cho thông báo popup) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Gentelella CSS chính thức -->
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>public/assets/css/gentelella.css">

    <!-- jQuery (cần cho inline script trong các view PHP dùng $().ready / $.ajax) -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
</head>
<?php
$successMsg = session::get('success_message');
if ($successMsg) {
    session::delete('success_message');
}
$errorMsg = session::get('error_message');
if ($errorMsg) {
    session::delete('error_message');
}
?>
<body data-shell="admin" data-page="dashboard" data-breadcrumb="Home > <?php echo $title ?? 'Dashboard'; ?>"
      data-flash-success="<?php echo htmlspecialchars($successMsg ?? '', ENT_QUOTES, 'UTF-8'); ?>"
      data-flash-error="<?php echo htmlspecialchars($errorMsg ?? '', ENT_QUOTES, 'UTF-8'); ?>">

<!-- Top Loading Bar: Hiển thị tiến trình tải trang mượt mà -->
<div id="app-loading-bar" style="width: 30%;"></div>
<div id="app-loading-spinner"></div>

<a class="skip-link" href="#main-content">Chuyển đến nội dung chính</a>

