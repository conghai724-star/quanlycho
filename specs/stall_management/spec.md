# Feature Specification: Quản lý Sạp chợ (Trống/Đã thuê)

**Feature Branch**: `stall-management`

**Created**: 2026-07-07

**Status**: Approved (Brainstormed with User)

## User Scenarios & Testing

### User Story 1 - Khai báo, chỉnh sửa và xóa Phân khu & Sạp (Priority: P1)
*   **Mô tả**: Nhân viên Ban quản lý có thể khai báo mới các Phân khu (Khu vực), cấu hình các sạp thuộc khu vực đó với đầy đủ thông tin: Mã sạp, Dãy, Lô, Loại sạp (Kiot, Quầy, Mặt bằng trống...), Diện tích, Đơn giá thuê nền, và Trạng thái ban đầu. Họ cũng có thể chỉnh sửa thông tin sạp hoặc xóa sạp khi không có hợp đồng thuê hoạt động.
*   **Tại sao độ ưu tiên này**: Đây là tính năng nền tảng thiết lập danh mục mặt bằng của chợ trước khi thực hiện các giao dịch cho thuê.
*   **Kiểm thử độc lập**: Tạo mới một sạp, xem nó hiển thị trong bảng danh sách, sửa thông tin diện tích/dãy/lô, kiểm tra thay đổi được lưu và sau đó xóa sạp đó đi.
*   **Kịch bản chấp nhận**:
    1.  **Given** BQL đã đăng nhập và đang ở trang quản lý sạp, **When** điền đầy đủ thông tin sạp mới (Khu vực A, Mã SẠP-A10, Dãy A, Lô 10, Diện tích 15m2, Giá 3.000.000đ, Trạng thái: Trống) và nhấn Lưu, **Then** hệ thống thông báo thành công và sạp SẠP-A10 xuất hiện trong danh sách.
    2.  **Given** Sạp SẠP-A10 có trạng thái trống, **When** nhấn nút Xóa sạp và xác nhận, **Then** sạp SẠP-A10 biến mất khỏi hệ thống.
    3.  **Given** Sạp SẠP-A01 đang được thuê (có hợp đồng active), **When** nhấn nút Xóa, **Then** hệ thống chặn hành động và báo lỗi sạp đang trong hợp đồng không được xóa.

### User Story 2 - Tìm kiếm và Lọc danh sách sạp qua AJAX (Priority: P1)
*   **Mô tả**: Nhân viên có thể tìm kiếm nhanh các sạp theo từ khóa (Mã sạp, Dãy, Lô) hoặc lọc theo Phân khu (Khu vực), trạng thái sạp (Trống, Đã thuê, Đang sửa chữa, Tạm khóa). Danh sách cập nhật tức thì qua AJAX không cần reload trang, tương tự như trang quản lý tiểu thương.
*   **Tại sao độ ưu tiên này**: Giúp nhân viên tra cứu nhanh tình trạng mặt bằng trong chợ để phục vụ tư vấn cho thuê hoặc sửa chữa.
*   **Kiểm thử độc lập**: Chọn bộ lọc "Trống", kiểm tra chỉ các sạp có trạng thái "Trống" hiển thị. Gõ từ khóa dãy/lô, kiểm tra kết quả tìm kiếm đúng.
*   **Kịch bản chấp nhận**:
    1.  **Given** danh sách sạp có 10 sạp trống và 5 sạp đã thuê, **When** chọn trạng thái lọc là "Trống" và bấm Lọc, **Then** bảng danh sách cập nhật ngay qua AJAX chỉ hiển thị 10 sạp trống, số lượng tổng hiển thị là 10.
    2.  **Given** người dùng chọn bộ lọc, **When** bấm Lọc, **Then** URL trình duyệt được cập nhật tương ứng (`admin/stalls?area_id=...&status=...`) để có thể bookmark/chia sẻ liên kết.

### User Story 3 - Gán sạp cho tiểu thương & Chuyển đổi sạp (Priority: P2)
*   **Mô tả**: 
    *   **Gán sạp**: Khi bấm vào nút "Gán sạp" đối với sạp đang Trống, hệ thống chuyển sang trang tạo Hợp đồng mới (`admin/contract_add`) với thông tin sạp đã được điền sẵn.
    *   **Chuyển đổi sạp**: Khi tiểu thương muốn chuyển đổi vị trí kinh doanh, BQL có thể chọn sạp hiện tại của họ, chọn chức năng "Chuyển sạp", hệ thống sẽ liệt kê các sạp trống. Khi xác nhận chuyển, hệ thống cập nhật hợp đồng hiện tại sang sạp mới và trả sạp cũ về trạng thái trống thông qua database transaction.
*   **Tại sao độ ưu tiên này**: Đáp ứng nghiệp vụ luân chuyển mặt bằng linh hoạt của tiểu thương trong chợ.
*   **Kiểm thử độc lập**: Chọn sạp A01 (Đang thuê), thực hiện chuyển sang sạp trống A02, kiểm tra sạp A01 trở về trạng thái Trống, sạp A02 chuyển thành Đã thuê, và hợp đồng thuê cập nhật liên kết sang A02.
*   **Kịch bản chấp nhận**:
    1.  **Given** sạp SẠP-A02 đang trống, **When** bấm "Gán sạp", **Then** hệ thống điều hướng đến form lập hợp đồng và sạp SẠP-A02 đã được chọn mặc định trong ô chọn sạp.
    2.  **Given** tiểu thương Nguyễn Văn A đang thuê sạp SẠP-A01, **When** thực hiện chuyển sang sạp trống SẠP-A02 thông qua hộp thoại chuyển đổi, **Then** sạp SẠP-A01 được cập nhật trạng thái trống, sạp SẠP-A02 cập nhật thành đã thuê, hợp đồng của Nguyễn Văn A được chuyển sang SẠP-A02, và lịch sử ghi nhận thông suốt.

### Sơ đồ mặt bằng (Map) - Tạm hoãn (Postponed)
*   Theo yêu cầu của khách hàng, tạm thời chưa cập nhật hoặc thay đổi sơ đồ map tương tác phức tạp. Giữ nguyên tab sơ đồ tĩnh như thiết kế giao diện ban đầu hoặc hiển thị đơn giản. Tập trung hoàn toàn vào việc triển khai CRUD, tìm kiếm, gán/chuyển sạp trên danh sách bảng dữ liệu thực tế dựa theo mẫu thiết kế của Quản lý Tiểu thương.

## Trạng thái và Màu sắc hiển thị
*   **Trống**: Màu trắng (Nền trắng, chữ xám/đen, viền mỏng xám `var(--border-color)`)
*   **Đã thuê**: Xanh lá cây (`status-green`)
*   **Đang sửa chữa**: Màu vàng (`status-yellow`)
*   **Tạm khóa**: Màu đỏ (`status-red`)

## Requirements

### Functional Requirements
*   **FR-001**: Hệ thống phải hỗ trợ thêm mới, chỉnh sửa, xóa sạp chợ (đối với sạp không có hợp đồng active).
*   **FR-002**: Mỗi sạp chợ phải lưu trữ các thông tin: Khu vực (foreign key), Mã sạp (duy nhất), Dãy (block), Lô (lot), Loại sạp, Diện tích (m2), Đơn giá thuê/tháng, Trạng thái.
*   **FR-003**: Cho phép tìm kiếm sạp theo từ khóa (mã sạp, dãy, lô) và lọc sạp theo khu vực, trạng thái.
*   **FR-004**: Quá trình lọc và tìm kiếm phải thực hiện qua AJAX, cập nhật dữ liệu bảng và URL lịch sử trình duyệt không cần tải lại toàn bộ trang (tương tự quản lý tiểu thương).
*   **FR-005**: Cho phép gán nhanh sạp trống cho tiểu thương bằng cách chuyển hướng đến trang tạo hợp đồng với thông số pre-filled.
*   **FR-006**: Cho phép chuyển đổi sạp nhanh giữa các tiểu thương (hoặc chuyển sang sạp trống khác) qua API an toàn sử dụng database transaction để đảm bảo tính toàn vẹn dữ liệu hợp đồng.
*   **FR-007**: Hệ thống phải tự động tính toán tỷ lệ lấp đầy sạp (số sạp đã thuê / tổng số sạp) trên các thẻ thống kê đầu trang quản lý sạp.

### Key Entities
*   **Area (Khu vực)**: Lưu trong bảng `areas`. Đại diện cho các phân khu chức năng (Khu ẩm thực, Khu quần áo, Khu đồ tươi sống...).
*   **Stall (Sạp chợ)**: Lưu trong bảng `stalls`. Có quan hệ thuộc về một Area. Được mở rộng thêm các cột `block` (dãy) và `lot` (lô).
*   **Trader (Tiểu thương)**: Lưu trong bảng `traders`.
*   **Contract (Hợp đồng)**: Liên kết giữa `Stall` và `Trader`, lưu trữ thời hạn và đơn giá thuê thực tế.

## Success Criteria

### Measurable Outcomes
*   **SC-001**: Toàn bộ thao tác thêm, sửa, xóa, tìm kiếm, lọc sạp chạy bằng dữ liệu thực kết nối MySQL qua PDO của framework.
*   **SC-002**: Tốc độ tải danh sách lọc AJAX dưới 200ms.
*   **SC-003**: Không xảy ra lỗi bất đồng bộ dữ liệu khi chuyển đổi sạp (hợp đồng cập nhật chính xác sạp mới, sạp cũ được trả tự do về trạng thái trống).
*   **SC-004**: Trạng thái sạp hiển thị đúng mã màu quy định trên giao diện ở cả hai chế độ sáng/tối (Light/Dark theme).

## Assumptions
*   Hệ thống chạy trên môi trường PHP 8.x + MySQL trên XAMPP đã cài đặt sẵn.
*   Dữ liệu liên quan đến hợp đồng và tiểu thương đã có cấu trúc sẵn sàng, chỉ cần liên kết logic.
*   Quyền truy cập sạp chợ tuân thủ cơ chế RBAC đã cấu hình (quyền `stall_view`, `stall_edit`).
