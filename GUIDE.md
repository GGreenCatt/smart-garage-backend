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

### 1. Dành cho Quản trị & Điều hành (Khu vực Admin/Manager)
Khu vực cấu hình và tổng quan: `/admin`

- **Với tài khoản Admin**: Bạn có quyền cao nhất để thay đổi cấu hình gốc. Mặc định chỉ hiển thị **Cấu hình hệ thống**, **Phân quyền** và **Bản đồ SOS**. Admin có một nút bật/tắt **"Chế độ Quản lý"** ở thanh công cụ góc phải trên cùng để chuyển đổi sang góc nhìn của Manager.
- **Với tài khoản Quản lý (Manager)**: Là người điều hành 1 cơ sở duy nhất, bạn sẽ thấy đầy đủ các menu về Vận hành:
  - Doanh thu, Nhân sự.
  - Khách hàng, Phương tiện.
  - Lệnh sửa chữa, Kho & Vật Tư, Phê Duyệt Vật Tư.
  - Nhật ký quản lý.

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
- **Bật/Tắt Thông báo & Chế độ Admin**: Admin có "Chế độ Quản Lý" tại Header. Để ẩn chuông thông báo cho toàn hệ thống, vào **Cấu hình hệ thống (Settings) -> Tab Vận Hành -> Thông Báo Hệ Thống**.
- **Chức Năng Hủy Nhận Xe**: Nếu đưa một lệnh sửa chữa (Lệnh Sửa Chữa) quay lại trạng thái "Chờ xử lý (Pending)", toàn bộ các nhiệm vụ con sẽ tự động được thu hồi kỹ thuật viên và đưa về trạng thái "pending" (Chưa làm).
- **Trải nghiệm Chat Cải Tiến**: Khi bạn mở một hội thoại liên quan đến xe đang sửa, trên tiêu đề đoạn chat sẽ tự động hiển thị Tên, SĐT, Biển số xe, cùng với một nút dẫn đi thẳng đến tiến độ đơn hàng để dễ dàng tra cứu.
- **Bản Đồ Cứu Hộ SOS**: Tính năng này không cần mã API Google Maps trong môi trường phát triển (Local), hệ thống mô phỏng chức năng tự cả tự động định tuyến đường đi ngắn nhất giữa Khách bị nạn và Kỹ thuật viên di động.
- **Tính năng Đặt Lịch Hẹn (Appointments)**: 
  - **Khách hàng** có thể đặt lịch mà không cần phải có sẵn xe lưu trong hệ thống (nhập tên xe, biển số, lý do tùy ý). Khách hàng cũng có thể tự hủy lịch khi đang ở trạng thái chờ (Pending).
  - **Quản lý (Manager) & Nhân viên (Staff)** đều có thể xem danh sách lịch hẹn mới (hiển thị thông tin xe, lý do, thời gian, SĐT khách), xác nhận lịch, hủy lịch hoặc chuyển đổi trực tiếp lịch hẹn thành **Lệnh Sửa Chữa (Repair Order)**. Lý do đặt lịch và ghi chú sẽ được chuyển thẳng vào phần "Chẩn đoán bệnh" của lệnh sửa chữa.
