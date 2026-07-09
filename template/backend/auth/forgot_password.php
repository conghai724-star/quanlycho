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
    <title>Khôi phục mật khẩu - Chợ Smart</title>
    
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
            flex-direction: column;
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

        <div class="auth-title">Quên mật khẩu?</div>
        <div class="auth-subtitle">Nhập email liên kết với tài khoản của bạn để khôi phục mật khẩu.</div>

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
                <div>
                    <i class="fa-solid fa-circle-check"></i>
                    <span><strong>Yêu cầu thành công!</strong></span>
                </div>
                <div style="font-size: 12px; color: var(--text-muted);">
                    Link khôi phục mật khẩu đã được tạo. Vui lòng kiểm tra file <strong>debug.txt</strong> trong mã nguồn dự án để lấy link khôi phục mật khẩu.
                </div>
                <?php if (isset($reset_link)): ?>
                    <div style="margin-top: 8px; padding: 10px; background-color: var(--bg-surface-secondary); border-radius: 4px; border: 1px solid var(--border-color);">
                        <a href="<?php echo $reset_link; ?>" style="font-weight: 600; font-family: monospace; font-size: 11px; word-break: break-all; color: var(--primary);">
                            Link Reset mật khẩu ở đây để test nhanh
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>home/forgot_password" method="POST">
            <?php csrf_field(); ?>
            
            <!-- Email Input -->
            <div class="form-group" style="margin-bottom: 24px;">
                <label class="form-label" for="email">Địa chỉ Email</label>
                <div class="input-group">
                    <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 4v8c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2H3c-1.1 0-2 .9-2 2zm12 .5L8 8 3 4.5V3l5 3.5 5-3.5v1.5z"/></svg>
                    <input type="email" id="email" name="email" class="form-control" placeholder="nga.lt@market.com" required autofocus>
                </div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;height:38px;margin-bottom: 16px;">
                Gửi yêu cầu khôi phục
            </button>

            <div style="text-align: center; font-size: 13px;">
                <a href="<?php echo BASE_URL; ?>home/login" style="text-decoration: none; font-weight: 500;">
                    <i class="fa-solid fa-arrow-left" style="font-size: 11px; margin-right: 4px;"></i> Quay lại Đăng nhập
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
