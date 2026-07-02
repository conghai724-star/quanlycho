</div> <!-- End page-wrapper from navbar -->
</main> <!-- End main-content from navbar -->

<!-- Footer -->
<footer class="footer border-top" style="margin-top: 40px; padding: 20px 0; font-size: 12.5px; color: var(--text-muted);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div>© 2026 <strong>Chợ Smart</strong> - Hệ thống quản lý thông minh (Gentelella v4).</div>
        <div style="font-size: 11px;">Thiết kế phẳng & Tối ưu hóa UI/UX.</div>
    </div>
</footer>

<!-- Tự động nạp động các tệp JavaScript chính đã build (giúp tránh lỗi đổi hash file) -->
<?php
$jsPath = DIR_ROOT . '/public/assets/js';
if (is_dir($jsPath)) {
    $jsFiles = glob($jsPath . '/*.js');
    if (!empty($jsFiles)) {
        // 1. Nạp file rolldown-runtime trước (nếu có)
        foreach ($jsFiles as $file) {
            $filename = basename($file);
            if (strpos($filename, 'rolldown-runtime') !== false) {
                echo '<script type="module" crossorigin src="' . BASE_URL . 'public/assets/js/' . $filename . '"></script>' . "\n";
            }
        }
        
        // 2. Nạp file toast và các file vendor khác
        foreach ($jsFiles as $file) {
            $filename = basename($file);
            if (strpos($filename, 'rolldown-runtime') === false && strpos($filename, 'gentelella') === false && strpos($filename, 'main-v4') === false) {
                echo '<script type="module" crossorigin src="' . BASE_URL . 'public/assets/js/' . $filename . '"></script>' . "\n";
            }
        }
        
        // 3. Nạp file logic chính (gentelella.js hoặc main-v4-*.js) ở cuối cùng
        foreach ($jsFiles as $file) {
            $filename = basename($file);
            if (strpos($filename, 'main-v4') !== false || strpos($filename, 'gentelella') !== false) {
                echo '<script type="module" crossorigin src="' . BASE_URL . 'public/assets/js/' . $filename . '"></script>' . "\n";
            }
        }
    }
}
?>

</body>
</html>
