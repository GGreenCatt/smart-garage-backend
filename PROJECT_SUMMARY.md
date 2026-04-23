# Báo Cáo Tổng Quan Dự Án: Hệ Thống Thông Tin Quản Lý Garage Sửa Xe Ô Tô Thông Minh Cùng Công Nghệ 3D 🚗

---

## 1. Giới thiệu tổng quan dự án (Project Overview)

Dự án **"Hệ thống thông tin quản lý Garage sửa xe ô tô thông minh tích hợp công nghệ 3D"** được phát triển nhằm mục tiêu chuyển đổi số toàn diện các quy trình vận hành và cung cấp dịch vụ tại garage, từ khâu quản lý lịch hẹn, theo dõi tiến độ đến chăm sóc khách hàng. Điểm sáng tạo then chốt của hệ thống là khả năng ứng dụng thư viện 3D web **Three.js** kết hợp thuật toán **Raycasting** vào quy trình kiểm tra sức khỏe xe (Vehicle Health Check - VHC). Thông qua đó, kỹ thuật viên có thể tương tác, click đánh dấu lỗi hư hỏng trực tiếp trên mô hình phương tiện 3D, tạo nên trải nghiệm báo giá trực quan, trực tuyến, đảm bảo tính minh bạch tuyệt đối để khách hàng an tâm xét duyệt.

## 2. Stack Công Nghệ (Tech Stack)

Nhằm đáp ứng trọn vẹn các bài toán về hiệu suất tải trang, bảo mật dữ liệu cũng như mang lại nền tảng xử lý tương tác không gian 3D mượt mà, hệ thống được thiết kế theo kiến trúc MVC hiện đại dựa trên các công nghệ lõi:

| Phân lớp (Layer) | Công nghệ / Framework | Vai trò thực thi trong hệ thống |
| :--- | :--- | :--- |
| **Backend & Database** | **Laravel (PHP), MySQL** | Nền tảng cốt lõi quản lý logic nghiệp vụ, xác thực phân quyền (RBAC), xây dựng API và truy vấn dữ liệu. Cơ sở dữ liệu MySQL đảm bảo toàn vẹn cho các dữ liệu giao dịch, tài chính và lịch hẹn. |
| **Frontend Engine** | **Blade Template, Vite** | Blade Template xử lý render HTML khép kín từ server cực kỳ ổn định. Vite đóng vai trò là build tool siêu tốc, tối ưu hoá quá trình đóng gói assets (js, css, model 3d) với cơ chế HMR vượt trội. |
| **UI/UX & Styling** | **Tailwind CSS** | Khung Utility-first CSS giúp đội ngũ linh hoạt thiết kế nên một hệ thống giao diện nhất quán, tốc độ phát triển nhanh và đáp ứng hoàn hảo yêu cầu Responsive trên cả PC lẫn Mobile. |
| **3D Rendering** | **Three.js (Raycasting)** | Điểm sáng của hệ thống (VHC). Thư viện tận dụng sức mạnh WebGL để hiển thị xe không gian 3 chiều. Thuật toán Raycasting bắt chính xác các tọa độ click chuột trên trình duyệt để ghi nhận lỗi/hỏng hóc. |

## 3. Các Tác nhân và Phân quyền (Roles & Permissions)

Kiến trúc bảo mật của dự án được vận hành trên nền tảng **Phân quyền dựa trên vai trò (RBAC - Role Based Access Control)**. Mô hình này khoanh vùng nghiêm ngặt phạm vi truy cập dữ liệu và chức năng, tối ưu hóa luồng công việc cho 3 nhóm đối tượng nòng cốt:

- 👑 **Admin (Quản trị viên / Quản lý điều hành):** Là cấp bậc sở hữu đặc quyền cao nhất. Đóng vai trò thiết lập khung tham số nền tảng (cấu hình garage), quản trị vòng đời nhân sự, rà soát bảng báo cáo hiệu suất tài chính toàn diện, quản lý danh mục vật tư/dịch vụ cốt lõi và kiểm soát phân quyền hệ thống.
- 👨‍🔧 **Staff (Nhân viên / Cố vấn dịch vụ / Kỹ thuật viên):** Lực lượng trực tiếp thực thi các tác vụ tại trạm. Nhóm Staff đóng vai trò xương sống trong việc tiếp nhận phương tiện, tiên phong ứng dụng không gian 3D/Raycasting để rà soát lỗi (VHC), thiết lập báo giá (Repair Order), cập nhật tiến độ thi công và trực hệ thống cứu hộ SOS di động.
- 👤 **Customer (Khách hàng người dùng):** Đối tượng thụ hưởng dịch vụ. Được phân quyền truy cập thông qua Customer Portal hiện đại: cho phép chủ động đặt lịch hẹn trực tuyến, giám sát tiến trình sửa xe theo thời gian thực, nghiệm thu báo giá 3D từ xa và tích hợp luồng phát tín hiệu cầu cứu (SOS Geolocation) chỉ với một thao tác.

---

## 4. Chi tiết Chức năng theo từng Role (Features by Role)

Dưới đây là bảng ma trận chức năng (Functional Matrix), đối chiếu trực tiếp quyền hạn và luồng hoạt động chuyên sâu của từng nhóm người dùng trong quá trình vận hành Garage:

| Role (Vai trò) | Tên chức năng (Feature) | Mô tả hoạt động (Description) |
| :--- | :--- | :--- |
| **Admin** <br>*(Quản trị)* | ⚙️ **Quản trị Cấu hình & Phân quyền** | Thiết lập thông số lõi của hệ thống (Settings). Kiểm soát toàn diện cây phân quyền (Role/Permission) để giới hạn luồng truy cập của cấp Quản lý và Nhân viên. |
| | 👥 **Bộ máy Nhân sự & Chấm công** | Cấp phát tài khoản ban đầu cho lực lượng Kỹ thuật / Tư vấn dịch vụ. Phân bổ ca làm việc (WorkShift) định kỳ và theo dõi chấm công. |
| | 📦 **Danh mục Dịch vụ & Vật tư** | Quản lý hệ thống kho bãi. Khởi tạo, cập nhật mã vật tư (Parts); cấu hình thang giá và định chuẩn các gói dịch vụ (Services) dùng chung toàn hệ thống. |
| | 📊 **Hệ thống Báo cáo Thống kê** | Giám sát sức khỏe doanh nghiệp thông qua hệ thống biểu đồ trực quan (Dashboards): Báo cáo doanh thu, lưu lượng xe, và hiệu suất làm việc của Garage. |
| **Staff** <br>*(Vận hành)* | 📅 **Tiếp nhận & Xử lý Lịch hẹn** | Dashboard kiểm duyệt Request đặt lịch từ Customer. Xác nhận khung giờ trống và cấp phép cho xe chính thức bước vào hàng đợi tiếp nhận dịch vụ. |
| | 👁️ **Kiểm tra đồ họa 3D (VHC)** | **[Chức năng Cốt Lõi]** Tương tác đa chiều trên mô hình xe 3D. Click điểm ảnh (Raycasting) để đánh dấu tọa độ lỗi, đính kèm hình ảnh thực tế và ghi chú chẩn đoán kỹ thuật. |
| | 🧾 **Thiết lập & Trình Báo giá** | Tổng hợp từ mảng VHC, tự động map lỗi với mã linh kiện tương ứng. Tạo dự toán tài chính chuẩn xác (Repair Order) và gửi lệnh báo giá số hóa lên Cổng Customer để chờ chốt đơn. |
| | 🔧 **Điều phối thi công & Kho** | Giao việc nội bộ qua các lệnh con (RepairTask); số hóa luồng đẩy yêu cầu xuất kho (MaterialRequest). Liên tục trượt trạng thái làm việc để báo Notification Real-time cho chủ xe. |
| | 🚑 **Kiểm soát SOS Khẩn cấp** | Trực màn hình SOS. Nhận thông báo khẩn cấp kèm luồng GPS từ khách hàng gặp nạn; tiến hành xác nhận tọa độ, gọi điện thẩm định và lập tức điều phối kỹ thuật viên ứng cứu. |
| **Customer** <br>*(Khách hàng)* | 📱 **Cổng thông tin & Lịch hẹn** | Cập nhật hồ sơ phương tiện cá nhân (My Vehicles). Giao diện cho phép book lịch hẹn chủ động, chọn trước ngày giờ dịch vụ để tối ưu thời gian chờ xếp hàng. |
| | 🔍 **Tracking Theo dõi Tiến độ** | Quan sát Real-time quá trình chăm sóc xe đang nằm trong Garage. Truy cập lại lịch sử chi tiết mọi phiếu sửa chữa cũng như hóa đơn qua các năm. |
| | ✅ **Xét duyệt Báo Giá 3D (Online)** | Mở Link, trực quan quan sát lại vết xước/lỗi trên View 3D mà Staff khoanh trên app; thao tác **"Phê duyệt thi công"** hoặc **"Từ chối"** giá trực tuyến ngay trên điện thoại di động thông minh. |
| | 🚨 **Kích hoạt SOS "One-Touch"** | Module riêng biệt cho sự cố dọc đường. Bấm nút báo động (Panic Button) gửi kèm định vị Geolocation Browser ngay lập tức về bảng điều khiển tổng của Garage gần nhất để xin hỗ trợ lưu động. |

---

## 5. Đặc tả các Luồng Nghiệp Vụ Cốt Lõi (Core Workflows)

Kiến trúc nghiệp vụ của hệ thống xoay quanh 3 luồng công việc (Workflows) cốt lõi. Đây là những quy trình làm nên sự khác biệt của dự án so với các hệ thống quản lý thủ công truyền thống:

### 🚀 Luồng 1: Kiểm tra xe VHC 3D & Báo giá số (3D Inspection & Digital Quotation)
Luồng thao tác định hình lại hoàn toàn trải nghiệm chẩn đoán và báo giá:
- **Bước 1: Khởi tạo phiên VHC (Vehicle Health Check):** Khi xe chính thức vào khoang sửa chữa, Nhân viên/KTV mở giao diện chẩn đoán. Hệ thống sẽ load file model 3D (`.gltf` / `.obj`) của phương tiện lên Browser Box dựa trên thông tin đời xe.
- **Bước 2: Bắt tọa độ lỗi (Raycasting):** KTV thao tác xoay (Orbit Controls) và zoom vào vị trí khả nghi trên mô hình. Khi click, sự kiện Raycast sẽ trả về chính xác tọa độ $(x, y, z)$ và định danh vùng Mesh (vd: Cản trước, Cửa trái).
- **Bước 3: Mapping & Lập định mức:** Cửa sổ Modal hiện lên yêu cầu KTV nhập tình trạng (vd: Xước sơn, Rách lốp). Hệ thống lập tức đối chiếu Data Dictionary để gợi ý "Mã Vật Tư (Parts)" hoặc "Gói Cứu Hộ (Services)" phù hợp và add vào giỏ.
- **Bước 4: Phê duyệt (Approval Flow):** Hệ thống tổng hợp các điểm click báo lỗi thành một `Repair Order` (Phiếu sửa chữa / Báo giá). Khách hàng nhận được Link -> Mở điện thoại xoay xem trực tiếp chiếc xe mô phỏng của mình -> Nhấn duyệt "Approve". 
- **Bước 5: Kích hoạt tác vụ:** Khi Khách hàng duyệt, trạng thái chuyển sang _In Progress_, kích hoạt lệnh xuất kho (MaterialRequest) và tiến hành thi công.

### 📅 Luồng 2: Booking Lịch hẹn & Tiếp nhận (Appointment & Reception Flow)
Số hóa quy trình phân luồng khách hàng từ trước khi xe đến trạm:
- **Bước 1: Submit Booking:** Khách hàng chủ động truy cập Customer Portal, khai báo thông tin xe, mô tả triệu chứng và dải khung giờ mong muốn mang xe qua xưởng. Request được lưu vào bảng `Appointments`.
- **Bước 2: Validation & Accept:** Hệ thống hiển thị lịch trống. Lễ tân / Cố vấn dịch vụ rà soát "Capacity" (sức tải của xưởng) trong khung giờ đó và bấm Nhận lịch. Notification tự động bắn về tài khoản Khách.
- **Bước 3: Check-in Thực tế:** Đến ngày giờ hẹn, Khách mang xe tới Garage. Nhân viên tiến hành mở Appointment, đối chiếu thông tin và sinh ra `Repair Order` mới để xe chính thức nhập xưởng, chấm dứt luồng đặt hẹn.

### 🚨 Luồng 3: Hệ thống Cứu hộ Khẩn cấp (SOS Rescue Workflow)
Quy trình "One-Touch" dành riêng cho các sự cố hỏng hóc giữa đường:
- **Bước 1: Kích hoạt SOS:** Khách hàng (hoặc Khách vãng lai) gặp sự cố kéo vào module SOS. Trình duyệt tự động xin quyền và lấy tọa độ GPS Vĩ độ / Kinh độ hiện tại qua Geolocation API.
- **Bước 2: Alert System:** Yêu cầu được Insert vào `SosRequests` kèm trạng thái (Status: _Pending_). Ngay lập tức, màn hình Admin/Manager tại Garage sẽ Reo chuông và hiển thị điểm Đỏ nhấp nháy trên bản đồ bản đồ nội bộ.
- **Bước 3: Dispatch (Điều độ ứng cứu):** Một cố vấn/nhân viên sẽ ấn "Nhận Case" để xe khác không bị trùng. Cố vấn gọi điện trực tiếp thẩm định tình hình (Xe hết bình, thủng lốp, v.v.).
- **Bước 4: Executing:** Phân công Xe kéo trực tiếp tới tọa độ mục tiêu. Toàn bộ chu trình sẽ làm khóa (Lock) đến khi khách hàng được cứu thành công (Status -> _Resolved_).

---

## 6. Cấu trúc Cơ sở dữ liệu cốt lõi (Core Database Entities)

Để hệ thống vận hành trơn tru và duy trì tính toàn vẹn dữ liệu cho hàng ngàn giao dịch sửa chữa, sơ đồ CSDL quan hệ (Relational Database) được thiết kế tối ưu xoay quanh các thực thể (Entities) mũi nhọn sau:

- 👤 **`Users` & `Roles` (Nhân khẩu & Phân quyền):** Bảng trung tâm lưu trữ thông tin Accounts (Staff, Admin, Customer). `Roles` vận hành thông qua Pivot Table để định tuyến quyền (RBAC) linh hoạt.
- 🚙 **`Vehicles` (Phương tiện):** Quản lý định danh tài sản của khách hàng. Lưu trữ thông số kỹ thuật, biển số, số khung (VIN). Được nối bằng khóa ngoại (`user_id`) trỏ thẳng về chủ sở hữu.
- 📅 **`Appointments` (Lịch hẹn):** Lưu vết mọi giao dịch Booking. Liên kết với Customer và `Vehicles`, tích hợp trường TimeSlot giúp kiểm duyệt khung thời gian tránh rủi ro "Over-capacity".
- 🧾 **`RepairOrders` & `RepairTasks` (Lệnh Sửa Chữa):** Cặp bảng trái tim của vận hành tài chính và tiến độ. 
  - `RepairOrders`: Lệnh tổng chứa tổng giá trị, VAT, trạng thái hóa đơn và mức chiết khấu.
  - `RepairTasks`: Bản ghi con, chia nhỏ Task, assign đích danh cho từng KTV để tracking tiến độ.
- 🎯 **`VhcDefects` & `VhcReports` (Dữ liệu 3D VHC):** Lưu trữ Data từ sự kiện Raycasting của Three.js. Bao gồm các trường định vị không gian 3 chiều: `camera_position`, `mesh_id`, `coordinates (x, y, z)` nhằm tái tạo chính xác điểm móp/xước khi render lại View 3D cho khách duyệt.
- 🔩 **`Services` & `Parts` (Dịch vụ & Tồn kho):** Nền tảng Master Data. `Parts` (Vật tư, linh kiện) và `Services` (Gói công thợ) liên kết thông qua quan hệ nhiều-nhiều (N-N) tới biểu mẫu của `RepairOrders`.
- 🚑 **`SosRequests` (Cứu hộ GPS):** Table luồng ưu tiên cao, lưu trữ số liệu tọa độ Vĩ độ (`lat`), Kinh độ (`lng`), Contact KH và Status (Pending / Dispatching / Resolved).
- 💬 **`ChatSessions` & `InternalMessages` (Cổng giao tiếp):** Bể chứa Log chat liên lạc nội bộ và hỗ trợ Khách hàng. Đảm bảo mọi hội chẩn bệnh lý xe đều có thể rà soát truy vết (Traceability).
