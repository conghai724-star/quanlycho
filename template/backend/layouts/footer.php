</div> <!-- End page-wrapper from navbar -->
</main> <!-- End main-content from navbar -->

<!-- Footer -->
<footer class="footer border-top" style="margin-top: 40px; padding: 20px 0; font-size: 12.5px; color: var(--text-muted);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div>© 2026 <strong>Chợ Smart</strong> - Hệ thống quản lý thông minh (Gentelella v4).</div>
        <div style="font-size: 11px;">Thiết kế phẳng & Tối ưu hóa UI/UX.</div>
    </div>
</footer>

<?php
/**
 * Chỉ nạp các file JS CỐT LÕI:
 *  - rolldown-runtime: polyfill module cần thiết để ES module chạy được
 *  - toast: thông báo toast dùng chung toàn site
 *  - theme: dark/light toggle handler
 *  - main-v4: entry point chính, lazy-import các chunk trang-cụ thể khi cần
 *
 * KHÔNG load tất cả JS vì các file page-specific (chat, faq, invoice...) 
 * tìm DOM element của trang riêng mình → gây lỗi null trên các trang khác.
 */
$jsDir  = DIR_ROOT . '/public/assets/js';
$jsBase = BASE_URL . 'public/assets/js/';

// Helper: tìm file JS theo prefix tên, bỏ qua .map
function findJsFile(string $dir, string $prefix): ?string {
    $files = glob($dir . '/' . $prefix . '-*.js');
    if (!$files) return null;
    // Bỏ qua .map, lấy file đầu tiên hợp lệ
    foreach ($files as $f) {
        if (!str_ends_with($f, '.map')) return basename($f);
    }
    return null;
}

$coreFiles = [
    findJsFile($jsDir, 'rolldown-runtime'),
    findJsFile($jsDir, 'toast'),
    findJsFile($jsDir, 'main-v4'),
];

foreach ($coreFiles as $file) {
    if ($file) {
        echo '<script type="module" crossorigin src="' . $jsBase . $file . '"></script>' . "\n";
    }
}
?>

<!-- Nạp SweetAlert2 & App Custom JS dùng chung cho toàn bộ các view PHP -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?php echo BASE_URL; ?>public/assets/js/app-custom.js"></script>

</body>
</html>
