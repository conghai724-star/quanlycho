<!DOCTYPE html>
<html lang="vi">
<head>
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

    <!-- Pre-paint Theme script: ngăn việc nhấp nháy chế độ sáng/tối (Dark/Light) khi tải trang -->
    <script>
        window.BASE_URL = '<?php echo BASE_URL; ?>';
        (function(){
            try {
                var t = localStorage.getItem('theme');
                var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
                var theme = t || (d ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
            } catch(e) {}
        })();
    </script>

    <!-- Gentelella CSS chính thức -->
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>public/assets/css/gentelella.css">
</head>
<body data-shell="admin" data-page="dashboard" data-breadcrumb="Home > <?php echo $title ?? 'Dashboard'; ?>">

<a class="skip-link" href="#main-content">Chuyển đến nội dung chính</a>
