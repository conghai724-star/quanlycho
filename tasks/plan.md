# Implementation Plan: User Management and Authentication

## Overview
Xây dựng bộ chức năng quản trị và xác thực tài khoản gồm quản lý người dùng, đăng nhập/đăng xuất, đổi mật khẩu, và khôi phục mật khẩu. Kế hoạch đi theo thứ tự nền tảng trước, luồng đăng nhập sau, rồi đến các luồng đổi và đặt lại mật khẩu để giữ hệ thống luôn ở trạng thái chạy được sau mỗi bước.

## Architecture Decisions
- Dùng một cơ chế xác thực thống nhất cho toàn bộ ứng dụng để tránh mỗi luồng tự xử lý theo cách riêng.
- Tách rõ phần quản trị người dùng với phần tự phục vụ của tài khoản để dễ phân quyền và dễ kiểm thử.
- Khôi phục mật khẩu nên đi qua một luồng có thời hạn rõ ràng, có thể thu hồi, và ghi nhận trạng thái token.

## Task List

### Phase 1: Foundation
- Xác định lại mô hình dữ liệu người dùng, vai trò, trạng thái tài khoản, và các trường cần cho xác thực.
- Rà soát các route, middleware, session/cookie, và cách lưu trạng thái đăng nhập hiện tại.
- Thiết kế quy ước lỗi, thông báo UI, và các rule validate dùng chung cho toàn bộ luồng auth.

### Checkpoint: Foundation
- Xác nhận mô hình dữ liệu và contract API đã rõ.
- Xác nhận luồng auth cốt lõi có thể mở rộng cho 4 tính năng bên dưới.

### Phase 2: Quản lý người dùng
- Làm màn hình/danh sách quản lý người dùng.
- Bổ sung tạo mới, cập nhật, khóa/mở khóa, và xem chi tiết người dùng nếu cần.
- Thêm validate dữ liệu đầu vào và phân quyền cho các thao tác quản trị.

### Phase 3: Đăng nhập / Đăng xuất
- Làm form đăng nhập và xử lý xác thực.
- Tạo phiên đăng nhập, chuyển hướng sau đăng nhập, và đăng xuất an toàn.
- Thêm xử lý lỗi sai mật khẩu, tài khoản bị khóa, và giới hạn trạng thái đăng nhập.

### Checkpoint: Auth Core
- Đăng nhập thành công và đăng xuất hoạt động đúng.
- Trang yêu cầu đăng nhập được bảo vệ đúng.

### Phase 4: Đổi mật khẩu
- Làm màn hình đổi mật khẩu cho người dùng đã đăng nhập.
- Kiểm tra mật khẩu hiện tại, mật khẩu mới, và xác nhận mật khẩu mới.
- Sau khi đổi thành công, cập nhật mật khẩu và buộc phiên hiện tại xử lý đúng theo chính sách bảo mật.

### Phase 5: Khôi phục mật khẩu
- Làm luồng quên mật khẩu.
- Tạo token đặt lại mật khẩu có thời hạn và màn hình nhập mật khẩu mới.
- Xác thực token, cập nhật mật khẩu, và vô hiệu hóa token sau khi dùng xong.

### Checkpoint: Complete
- 4 luồng chức năng chạy end-to-end.
- Có kiểm tra lỗi cơ bản, phân quyền, và thông báo trạng thái rõ ràng.
- Sẵn sàng review và đưa vào test tích hợp.

## Risks and Mitigations
| Risk | Impact | Mitigation |
|------|--------|------------|
| Luồng reset mật khẩu không có email/đường gửi token rõ ràng | High | Chốt cơ chế gửi token sớm, hoặc chuyển sang cách sinh link nội bộ/admin nếu chưa có mail service |
| Phân quyền user/admin chưa rõ | High | Chốt role matrix trước khi code UI và API |
| Session hoặc cookie xử lý không nhất quán | Medium | Dùng một middleware và một nơi quản lý session duy nhất |
| Quên vô hiệu hóa token reset sau khi dùng | High | Gắn test cho trạng thái token và thời hạn hết hiệu lực |

## Open Questions
- Reset mật khẩu sẽ gửi qua email thật hay do admin tạo link thủ công?
- Hệ thống có những vai trò nào ngoài admin và user thường?
- Có cần ghi log/audit cho các thay đổi tài khoản không?
