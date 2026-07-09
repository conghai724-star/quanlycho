    <!-- ================= HERO ĐĂNG KÝ ================= -->
    <section class="hero" style="padding: 60px 0 80px;">
        <div class="hero-grid-pattern"></div>
        <div class="container hero-content" style="grid-template-columns: 1fr; text-align: center;">
            <div>
                <div class="hero-eyebrow" style="justify-content: center;"><span class="dot"></span> Thuê sạp online</div>
                <h1>Đăng ký thuê sạp trực tuyến</h1>
                <p style="margin: 0 auto;">Nộp hồ sơ trực tuyến, Ban quản lý chợ sẽ xét duyệt và liên hệ lại trong vòng 24h làm việc.</p>
            </div>
        </div>
    </section>

    <!-- ================= FORM ĐĂNG KÝ ================= -->
    <section style="padding: 60px 0;">
        <div class="container" style="max-width: 700px;">
            <?php if ($success): ?>
                <div style="background: #E8F5E9; border: 1px solid #C8E6C9; padding: 30px; border-radius: var(--radius-lg); text-align: center; margin-bottom: 40px; box-shadow: var(--shadow-sm);">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: #2E7D32; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 30px; margin-bottom: 16px;">✓</div>
                    <h3 style="font-size: 22px; font-weight: 800; color: #2E7D32; margin-bottom: 8px;">Nộp hồ sơ thành công!</h3>
                    <p style="color: var(--gray-600); margin-bottom: 24px; font-size: 15px;">Hồ sơ của bạn đã được ghi nhận trên hệ thống. Ban quản lý sẽ thẩm định và liên hệ trực tiếp qua số điện thoại đăng ký trong vòng 24 giờ làm việc.</p>
                    <a href="<?php echo BASE_URL; ?>" class="btn btn-primary">Quay lại Trang chủ</a>
                </div>
            <?php else: ?>
                <div class="form-card" style="padding: 40px; border-radius: var(--radius-lg); border: 1px solid var(--gray-300);">
                    <h3 style="font-size: 20px; font-weight: 800; margin-bottom: 8px; color: var(--gray-900);">Thông tin đăng ký thuê sạp</h3>
                    <p style="color: var(--gray-600); font-size: 14.5px; margin-bottom: 30px;">Vui lòng điền chính xác thông tin để chúng tôi liên hệ hỗ trợ.</p>
                    
                    <?php if (!empty($error)): ?>
                        <div style="background: #FFEBEE; border: 1px solid #FFCDD2; color: #C62828; padding: 12px; border-radius: 6px; font-size: 13.5px; margin-bottom: 20px;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo BASE_URL; ?>home/register" method="POST">
                        <div class="form-row">
                            <div class="field">
                                <label>Họ và tên *</label>
                                <input type="text" name="fullname" placeholder="Nguyễn Văn A" required>
                            </div>
                            <div class="field">
                                <label>Số điện thoại *</label>
                                <input type="tel" name="phone" placeholder="09xx xxx xxx" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="field">
                                <label>Số CMND/CCCD *</label>
                                <input type="text" name="cccd" placeholder="Nhập số CCCD" required>
                            </div>
                            <div class="field">
                                <label>Email liên hệ</label>
                                <input type="email" name="email" placeholder="example@gmail.com">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="field">
                                <label>Khu vực mong muốn *</label>
                                <input type="text" name="zone" placeholder="Ví dụ: Khu A, Khu B..." required>
                            </div>
                            <div class="field">
                                <label>Diện tích yêu cầu (m²)</label>
                                <input type="number" name="area" placeholder="Ví dụ: 10, 15, 20..." min="4">
                            </div>
                        </div>

                        <div class="field">
                            <label>Mặt hàng kinh doanh dự kiến *</label>
                            <input type="text" name="business_line" placeholder="Ví dụ: Rau củ quả, Thời trang nam, Ăn vặt..." required>
                        </div>

                        <div class="field">
                            <label>Ghi chú hoặc câu hỏi dành cho BQL</label>
                            <textarea name="note" placeholder="Nhập yêu cầu đặc biệt của bạn nếu có..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" style="margin-top: 10px;">Gửi hồ sơ đăng ký</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const urlParams = new URLSearchParams(window.location.search);
            const zone = urlParams.get('zone');
            const area = urlParams.get('area');
            const stallCode = urlParams.get('stall_code');

            if (zone) {
                const zoneInput = document.querySelector('input[name="zone"]');
                if (zoneInput) zoneInput.value = zone;
            }
            if (area) {
                const areaInput = document.querySelector('input[name="area"]');
                if (areaInput) areaInput.value = area;
            }
            if (stallCode) {
                const noteTextarea = document.querySelector('textarea[name="note"]');
                if (noteTextarea) {
                    noteTextarea.value = "Tôi muốn đăng ký thuê sạp có mã hiệu: " + stallCode + ".";
                }
            }
        });
    </script>
