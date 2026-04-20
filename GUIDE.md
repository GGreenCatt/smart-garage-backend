# 📘 Hướng Dẫn Sử Dụng & Tài Khoản Truy Cập

Hệ thống **Smart Garage Management** được triển khai tại:
- **URL**: [http://smart-garage-backend.test/](http://smart-garage-backend.test/)
- **Môi trường**: Laragon / Local Development

---

## 🔐 Danh Sách Tài Khoản Mặc Định (Roles)

Bản mẫu (Seed) dữ liệu cung cấp các tài khoản sau để kiểm tra các tính năng:

| Vai trò (Role) | Email | Mật khẩu | Ghi chú |
| :--- | :--- | :--- | :--- |
| **Quản trị viên (Admin)** | `admin@smartgarage.com` | `password` | Quyền cao nhất, quản lý cài đặt, nhân sự, kho. |
| **Quản lý (Manager)** | `manager@smartgarage.com` | `password` | Điều hành gara, phê duyệt vật tư, báo cáo. |
| **Nhân viên (Staff)** | `staff@smartgarage.com` | `password` | Tiếp nhận xe, tạo phiếu sửa chữa, quản lý khách hàng. |
| **Kỹ thuật viên (Tech)** | `tech@smartgarage.com` | `password` | Xem lịch làm việc, cập nhật tiến độ công việc. |
| **Khách hàng (Customer)** | `customer@smartgarage.com` | `password` | Xem tiến độ sửa chữa, duyệt báo giá (SĐT: `0909999999`). |
| **Khách hàng Test** | `0987654321` (Phone) | `password` | Tài khoản test đăng ký bằng số điện thoại. |

---

## 🔗 Các Đường Dẫn Quan Trọng

### 1. Dành cho Quản trị viên (Admin)
- **Tổng quan**: `/admin`
- **Cấu hình hệ thống**: `/admin/settings` (Nơi có nút bật/tắt thông báo)
- **Quản lý nhân sự**: `/admin/staff`
- **Phân quyền (RBAC)**: `/admin/roles`

### 2. Dành cho Nhân viên (Staff)
- **Bảng công việc**: `/staff/dashboard`
- **Quản lý khách hàng**: `/staff/customers`
- **Kho & Vật tư**: `/staff/inventory`
- **Tin nhắn (Hỗ trợ khách)**: `/staff/chat`
- **Thông báo**: `/staff/notifications`

### 3. Dành cho Khách hàng (Customer)
- **Cổng khách hàng**: `/customer/dashboard`
- **Phiếu sửa chữa của tôi**: `/customer/orders`
- **Xe của tôi**: `/customer/vehicles`
- **Đặt lịch hẹn**: `/customer/appointments/book`
- **Cứu hộ SOS (Khách vãng lai)**: `/customer/sos`

---

## 💡 Lưu ý Vận Hành
- **Bật/Tắt Thông báo**: Để ẩn toàn bộ chuông thông báo, truy cập **Admin -> Cấu hình hệ thống (Settings) -> Tab Vận Hành -> Thông Báo Hệ Thống**.
- **Chat Nội Bộ**: Tất cả nhân viên gara sẽ tự động được vào một nhóm chat chung tại mục **Tin Nhắn**.
- **Chat Sửa Xe**: Khi một phiếu sửa chữa bắt đầu (`In Progress`), một nhóm chat riêng với khách hàng sẽ được tạo và sẽ tự động ẩn sau khi hoàn thành.
