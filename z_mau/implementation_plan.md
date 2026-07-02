# Kế hoạch Triển khai Dự án Quản lý Chợ (Cập nhật Cấu trúc Framework)

Dự án này sử dụng khung phát triển **Custom MVC Framework** do bạn cung cấp, giúp tối ưu hóa hiệu suất và kế thừa mã nguồn tối đa trên môi trường XAMPP.

---

## Cấu trúc Thư mục Dự án Thực tế

Chúng ta sẽ tổ chức các file theo cấu trúc của bạn như sau:

```text
quanly_cho/
│
├── application/                 # Các thư viện cốt lõi (Core Framework)
│   ├── database.class.php       # Kết nối DB qua PDO và các hàm helper
│   ├── router.class.php         # Xử lý định tuyến URL
│   ├── session.class.php        # Quản lý Session và Auth
│   ├── upload.class.php         # Xử lý upload ảnh, hợp đồng, giấy tờ
│   └── validator.class.php      # Validate dữ liệu form đầu vào
│
├── controller/                  # Lớp điều phối logic nghiệp vụ
│   ├── adminController.php      # Controller điều phối chung trang quản trị
│   ├── apiController.php        # Controller xử lý các request AJAX/API
│   └── homeController.php       # Controller trang chủ/giao diện ngoài
│
├── model/                       # Lớp xử lý cơ sở dữ liệu (Database Models)
│   ├── userModel.php            # Model quản lý người dùng/phân quyền
│   ├── merchantModel.php        # Model quản lý tiểu thương (thay cho candidate/employer cũ)
│   ├── stallModel.php           # Model quản lý sạp, lô, dãy, khu vực
│   ├── contractModel.php        # Model quản lý hợp đồng thuê sạp
│   ├── financeModel.php         # Model quản lý thu/chi, phiếu thu, phiếu chi
│   └── foodsafetyModel.php      # Model quản lý an toàn thực phẩm
│
├── template/                    # Lớp hiển thị giao diện (Views)
│   ├── backend/                 # Giao diện trang quản trị Ban quản lý chợ
│   │   ├── layouts/
│   │   │   ├── header.php
│   │   │   ├── sidebar.php
│   │   │   ├── navbar.php
│   │   │   └── footer.php
│   │   ├── dashboard/           # Trang chủ quản trị (biểu đồ doanh thu, thống kê nhanh)
│   │   │   └── index.php
│   │   ├── merchant/            # CRUD tiểu thương
│   │   ├── stall/               # Quản lý sạp và sơ đồ chợ
│   │   ├── contract/            # Quản lý hợp đồng, in hợp đồng
│   │   ├── finance/             # Ghi số điện nước, lập phiếu thu chi
│   │   ├── foodsafety/          # Quản lý chứng nhận ATTP, giấy khám sức khỏe
│   │   ├── user/                # Quản lý người dùng hệ thống
│   │   ├── auth/                # Trang đăng nhập, đổi mật khẩu
│   │   └── errors/              # Các trang lỗi (404, 403)
│   │
│   ├── frontend/                # Giao diện dành cho công chúng (nếu cần)
│   │   └── layouts/
│   └── emails/                  # Các mẫu email gửi đi (nếu có)
│
├── public/                      # Chứa tài nguyên tĩnh công khai
│   └── assets/
│       ├── css/                 # CSS tùy chỉnh (Tailwind / Bootstrap)
│       ├── js/                  # JS tương tác, biểu đồ Chart.js
│       ├── images/              # Logo, hình ảnh minh họa
│       └── vendor/              # Các thư viện bên thứ ba (SweetAlert2, Chart.js...)
│
├── uploads/                     # Thư mục lưu trữ file được tải lên
│   ├── avatar/                  # Ảnh đại diện tiểu thương/nhân viên
│   ├── contract/                # Bản scan hợp đồng dạng PDF/Ảnh
│   └── certificate/             # Giấy chứng nhận ATTP, giấy khám sức khỏe
│
├── database/                    # Quản lý cơ sở dữ liệu
│   ├── migration/               # File SQL cấu trúc bảng
│   │   └── schema.sql           # File khởi tạo cấu trúc DB
│   └── seed/                    # File dữ liệu mẫu
│       └── seed_data.sql        # Bộ dữ liệu mẫu ban đầu
│
├── config.php                   # File cấu hình kết nối DB và cấu hình chung
├── index.php                    # Front Controller (Điểm bắt đầu hệ thống)
└── .htaccess                    # Cấu hình rewrite URL cho router
```

---

## Lộ trình Phát triển theo Cấu trúc Mới

### Giai đoạn 1: Nền tảng Core & Danh mục Sạp, Tiểu thương
1.  **Cơ sở dữ liệu & Cấu hình:** Thiết lập file `config.php`, viết script tạo cơ sở dữ liệu trong `database/migration/schema.sql`.
2.  **Core Integration:** Kết nối và cấu hình `application/database.class.php`, `router.class.php`, `session.class.php` để đảm bảo hệ thống MVC chạy thông suốt.
3.  **Phân quyền & Tài khoản (Nhóm F):** Viết `userModel.php` và giao diện đăng nhập/quản lý tài khoản trong `template/backend/user/`.
4.  **Danh mục Sạp & Tiểu thương:** 
    *   Tạo `merchantModel.php`, `stallModel.php`.
    *   Tạo giao diện quản lý tiểu thương (`merchant/`) và quản lý sạp (`stall/`).

### Giai đoạn 2: Hợp đồng & Tài chính
1.  **Quản lý Hợp đồng (Nhóm C):** Tạo `contractModel.php` và giao diện lập hợp đồng, in ấn hợp đồng, theo dõi thời hạn hợp đồng.
2.  **Ghi số điện nước & Lập hóa đơn:** Thiết lập bảng nhập số điện, nước tiêu thụ hàng tháng của từng sạp.
3.  **Thu - Chi & Công nợ (Nhóm D):** Tạo `financeModel.php`, lập phiếu thu (tiền thuê, điện, nước, dịch vụ) và phiếu chi (chi lương, chi vận hành), theo dõi công nợ tiểu thương.

### Giai đoạn 3: ATTP & Nâng cao
1.  **Quản lý ATTP (Nhóm E):** Tạo `foodsafetyModel.php` và quản lý hạn giấy tờ, ghi nhận kiểm tra vệ sinh thực phẩm.
2.  **Sơ đồ chợ tương tác (B2):** Thiết kế bản đồ các sạp chợ trên giao diện giúp BQL click xem nhanh trạng thái sạp.
3.  **Audit Log & Báo cáo nâng cao:** Hoàn thiện nhật ký thao tác người dùng (`F10`) và các biểu đồ doanh thu tài chính.

---

## Các Câu hỏi cần Xác nhận (Open Questions)

> [!IMPORTANT]
> Hãy giúp tôi xác nhận các điểm sau để bắt đầu triển khai **Giai đoạn 1**:

1. **Giao diện quản trị:** Bạn muốn sử dụng **TailwindCSS** hay **Bootstrap 5** cho giao diện?
2. **Khởi tạo dữ liệu:** Bạn có muốn tôi viết trước file `schema.sql` (chứa các bảng `users`, `traders`, `stalls`, `areas`...) và cấu hình `config.php` để bạn bắt đầu cài đặt cơ sở dữ liệu không?
