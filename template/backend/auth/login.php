<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập BQL Chợ Smart</title>
    
    <!-- Favicon & Fonts -->
    <link rel="icon" href="<?php echo BASE_URL; ?>public/assets/images/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- CSS chính thức của Gentelella -->
    <link rel="stylesheet" crossorigin href="<?php echo BASE_URL; ?>public/assets/css/gentelella.css">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Pre-paint Theme -->
    <script>
        (function(){
            try {
                var t = localStorage.getItem('theme');
                var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
                var theme = t || (d ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', theme);
            } catch(e) {}
        })();
    </script>
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
    </style>
</head>
<body>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-brand">
            <div class="brand-icon" style="background-color: var(--primary); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold;">C</div>
            <div class="brand-name">CHỢ SMART <small style="font-weight:400;color:var(--text-muted);font-size:13px;margin-left:2px">v4</small></div>
        </div>

        <div class="auth-title">Xin chào BQL</div>
        <div class="auth-subtitle">Đăng nhập để vào hệ thống điều hành Chợ Smart.</div>

        <!-- Hiển thị thông báo lỗi nếu có -->
        <?php if (!empty($error)): ?>
            <div class="error-alert">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <!-- Hướng dẫn tài khoản test -->
        <div style="background-color: var(--bg-surface-secondary); padding: 12px; border-radius: 6px; font-size: 11px; color: var(--text-muted); margin-bottom: 20px; border: 1px solid var(--border-color);">
            <strong style="color: var(--text-heading);"><i class="fa-solid fa-circle-info me-1"></i> Tài khoản chạy thử:</strong>
            <div style="margin-top: 4px;">User: <span style="color: var(--text-heading); font-weight: bold; font-family: monospace;">admin</span> | Pass: <span style="color: var(--text-heading); font-weight: bold; font-family: monospace;">admin123</span></div>
        </div>

        <form action="<?php echo BASE_URL; ?>home/login" method="POST">
            <?php csrf_field(); ?>
            <!-- Username Input -->
            <div class="form-group">
                <label class="form-label" for="username">Tên đăng nhập</label>
                <div class="input-group">
                    <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Tên tài khoản" required autofocus>
                </div>
            </div>

            <!-- Password Input -->
            <div class="form-group">
                <label class="form-label" for="password">Mật khẩu</label>
                <div class="input-group">
                    <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="7" width="10" height="7" rx="1.5"/><path d="M5 7V5a3 3 0 016 0v2"/></svg>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <div class="auth-actions">
                <label class="form-check">
                    <input type="checkbox" checked> Ghi nhớ đăng nhập
                </label>
                <a href="#">Quên mật khẩu?</a>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;height:38px">
                Đăng nhập
            </button>
        </form>
    </div>
</div>

</body>
</html>
