<?php
$theme = 'light';
if (isset($_COOKIE['app_theme']) && in_array($_COOKIE['app_theme'], ['light', 'dark'])) {
    $theme = $_COOKIE['app_theme'];
}
$htmlBg = $theme === 'dark' ? '#0f1623' : '#f5f7fb';
?>
<!DOCTYPE html>
<html lang="vi" data-theme="<?php echo $theme; ?>" style="background:<?php echo $htmlBg; ?>">
<head>
    <style>
        html, body { background: <?php echo $htmlBg; ?> !important; }
        @view-transition { navigation: auto; }
        ::view-transition-old(root) { animation: none; }
        ::view-transition-new(root) { animation: 180ms ease both vt-enter; }
        @keyframes vt-enter { from{opacity:0} to{opacity:1} }
    </style>
    <!-- Pre-paint Theme + cookie sync -->
    <script>
        (function(){
            try {
                var stored = localStorage.getItem('theme');
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                var theme = stored || (prefersDark ? 'dark' : 'light');
                var html = document.documentElement;
                if (html.getAttribute('data-theme') !== theme) {
                    html.setAttribute('data-theme', theme);
                    html.style.background = theme === 'dark' ? '#0f1623' : '#f5f7fb';
                }
                document.cookie = 'app_theme=' + theme + ';path=/;max-age=31536000;SameSite=Lax';
            } catch(e) {}
        })();
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - Chợ Smart</title>
    
    <!-- Favicon & Fonts -->
    <link rel="icon" href="<?php echo BASE_URL; ?>public/assets/images/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS chính thức của Gentelella -->
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>public/assets/css/gentelella.css">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .error-alert {
            background-color: rgba(211, 47, 47, 0.1);
            border: 1px solid rgba(211, 47, 47, 0.2);
            color: #d32f2f;
            padding: 12px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        [data-theme="dark"] .error-alert {
            background-color: rgba(244, 67, 54, 0.15);
            border-color: rgba(244, 67, 54, 0.25);
            color: #ff8a80;
        }
        .success-alert {
            background-color: rgba(26, 187, 156, 0.1);
            border: 1px solid rgba(26, 187, 156, 0.2);
            color: #1ABB9C;
            padding: 12px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        [data-theme="dark"] .success-alert {
            background-color: rgba(26, 187, 156, 0.15);
            border-color: rgba(26, 187, 156, 0.25);
        }
    </style>
</head>
<body>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-brand">
            <div class="brand-icon" style="background-color: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold;">C</div>
            <div class="brand-name">CHỢ SMART</div>
        </div>

        <div class="auth-title">Đặt lại mật khẩu</div>
        <div class="auth-subtitle">Vui lòng nhập mật khẩu mới cho tài khoản của bạn.</div>

        <!-- Hiển thị thông báo lỗi nếu có -->
        <?php if (!empty($error)): ?>
            <div class="error-alert">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <!-- Hiển thị thông báo thành công nếu có -->
        <?php if (!empty($success)): ?>
            <div class="success-alert">
                <i class="fa-solid fa-circle-check"></i>
                <span><?php echo $success; ?></span>
            </div>
        <?php endif; ?>

        <?php if (empty($success)): ?>
            <form action="<?php echo BASE_URL; ?>home/reset_password/<?php echo htmlspecialchars($token); ?>" method="POST">
                <?php csrf_field(); ?>
                
                <!-- Mật khẩu mới -->
                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" for="new_password">Mật khẩu mới (Tối thiểu 6 ký tự)</label>
                    <div class="input-group">
                        <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="7" width="10" height="7" rx="1.5"/><path d="M5 7V5a3 3 0 016 0v2"/></svg>
                        <input type="password" id="new_password" name="new_password" class="form-control" placeholder="••••••••" required autofocus>
                    </div>
                </div>

                <!-- Xác nhận mật khẩu mới -->
                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label" for="confirm_password">Xác nhận mật khẩu mới</label>
                    <div class="input-group">
                        <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="7" width="10" height="7" rx="1.5"/><path d="M5 7V5a3 3 0 016 0v2"/></svg>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;height:38px;">
                    Đặt lại mật khẩu
                </button>
            </form>
        <?php else: ?>
            <div style="text-align: center; margin-top: 16px;">
                <a href="<?php echo BASE_URL; ?>home/login" class="btn btn-primary" style="width:100%;justify-content:center;height:38px;text-decoration:none;display:inline-flex;align-items:center;">
                    Đăng nhập ngay
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
