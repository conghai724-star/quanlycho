    <!-- ================= HERO TIỂU THƯƠNG ================= -->
    <section class="hero" style="padding: 60px 0 80px;">
        <div class="hero-grid-pattern"></div>
        <div class="container hero-content" style="grid-template-columns: 1fr; text-align: center;">
            <div>
                <div class="hero-eyebrow" style="justify-content: center;"><span class="dot"></span> Danh bạ chợ</div>
                <h1>Danh sách Tiểu thương</h1>
                <p style="margin: 0 auto;">Tra cứu thông tin tiểu thương kinh doanh chính thức tại Chợ Trung Tâm.</p>
            </div>
        </div>
    </section>

    <!-- ================= DANH SÁCH & BỘ LỌC ================= -->
    <section style="padding: 60px 0;">
        <div class="container">
            <div style="max-width: 600px; margin: 0 auto 40px;">
                <div class="map-search" style="background: var(--gray-100); border: 1px solid var(--gray-300); padding: 6px; margin: 0;">
                    <input type="text" id="traderSearchInput" placeholder="Nhập tên tiểu thương, số điện thoại hoặc ngành hàng..." style="color: var(--gray-900);">
                    <button class="btn btn-primary btn-sm" style="border-radius: 10px;">Tìm kiếm</button>
                </div>
            </div>

            <?php if (empty($traders)): ?>
                <div style="text-align: center; padding: 40px; border: 1.5px dashed var(--gray-300); border-radius: var(--radius-md); color: var(--gray-600);">
                    Không có dữ liệu tiểu thương hoạt động.
                </div>
            <?php else: ?>
                <div class="stalls-grid" id="traderGrid">
                    <?php foreach ($traders as $t): ?>
                        <div class="stall-card trader-card" data-fullname="<?php echo htmlspecialchars(mb_strtolower($t['fullname'], 'UTF-8')); ?>" data-line="<?php echo htmlspecialchars(mb_strtolower($t['business_line_name'] ?? '', 'UTF-8')); ?>">
                            <div class="stall-top" style="margin-bottom: 8px;">
                                <div>
                                    <div class="stall-code" style="color: var(--blue-700);"><?php echo htmlspecialchars($t['fullname']); ?></div>
                                    <div class="stall-zone" style="margin-top: 4px; font-weight: 600;">Mã: <?php echo htmlspecialchars($t['trader_code']); ?></div>
                                </div>
                                <span class="badge" style="background: #E8F5E9; color: #2E7D32;">Hoạt động</span>
                            </div>
                            <div class="stall-meta" style="margin-top: 14px; margin-bottom: 0; font-size: 13.5px; border-top: 1px solid var(--gray-100); padding-top: 12px;">
                                <div style="margin-bottom: 6px;">Ngành hàng: <b><?php echo htmlspecialchars($t['business_line_name'] ?? 'Chưa cập nhật'); ?></b></div>
                                <div style="margin-bottom: 6px;">Điện thoại: <b><?php echo htmlspecialchars($t['phone']); ?></b></div>
                                <div>Địa chỉ: <b><?php echo htmlspecialchars($t['address'] ?? 'Tại chợ'); ?></b></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div style="text-align: center; margin-top: 40px;">
                <a href="<?php echo BASE_URL; ?>" class="btn btn-outline">Quay lại Trang chủ</a>
            </div>
        </div>
    </section>

    <script>
        const searchInput = document.getElementById('traderSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                const cards = document.querySelectorAll('.trader-card');
                
                cards.forEach(card => {
                    const fullname = card.getAttribute('data-fullname') || '';
                    const line = card.getAttribute('data-line') || '';
                    
                    if (fullname.includes(query) || line.includes(query)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
    </script>
