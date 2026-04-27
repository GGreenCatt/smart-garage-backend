# Hướng Dẫn Test Thủ Công - Staff Core Flow

File này dùng để test thủ công luồng chính:

**Staff tiếp nhận xe -> kiểm tra 3D/VHC -> tạo báo giá -> khách duyệt -> thi công -> hoàn thành -> thanh toán**

## 1. Chuẩn Bị

### 1.1. Chạy Project

Tại thư mục `smart-garage-backend`, chạy:

```bash
php artisan migrate
php artisan serve
```

Nếu frontend asset cần build:

```bash
npm run dev
```

URL backend mặc định thường là:

```text
http://127.0.0.1:8000
```

### 1.2. Tài Khoản Cần Có

Cần ít nhất 3 tài khoản:

- 1 tài khoản Staff.
- 1 tài khoản Technician.
- 1 tài khoản Customer.

Nếu chưa có tài khoản Staff/Technician, tạo bằng màn hình Admin hoặc seed dữ liệu của project.

Lưu ý cho tài khoản Customer trong bài test:

- Khi Staff tiếp nhận xe bằng số điện thoại mới, hệ thống sẽ tự tạo tài khoản Customer theo số điện thoại đó.
- Vì vậy sau bước tiếp nhận xe, không đăng ký lại bằng cùng số điện thoại vì hệ thống sẽ báo số điện thoại đã có tài khoản.
- Với dữ liệu test bên dưới, đăng nhập Customer bằng:

```text
Số điện thoại: 0909123456
Mật khẩu: 12345678
```

Nếu bạn muốn test luồng đăng ký khách mới, hãy dùng một số điện thoại khác chưa từng được Staff tiếp nhận.

### 1.3. Dữ Liệu Test Dùng Chung

Dùng cùng một bộ dữ liệu để dễ kiểm tra duplicate:

```text
Customer name: Nguyễn Văn Test
Phone: 0909123456
Email: customer.test@example.com

Vehicle brand: Toyota
Vehicle model: Vios
Year: 2021
License plate lần 1: 51A-123.45
License plate lần 2 để test duplicate: 51A 12345
Mileage: 45000
```

## 2. Test Tiếp Nhận Xe

### Mục Tiêu

Kiểm tra Staff tiếp nhận xe tạo đúng customer, vehicle, repair order và task ban đầu.

### Bước Test

1. Đăng nhập bằng tài khoản Staff.
2. Mở màn Staff Dashboard:

```text
/staff/dashboard
```

3. Tìm form/chức năng tiếp nhận xe mới.
4. Nhập thông tin customer và xe theo bộ dữ liệu test.
5. Chọn có kiểm tra VHC/3D nếu màn hình có tùy chọn này.
6. Submit.

### Kết Quả Kỳ Vọng

- Tạo được repair order mới.
- Order có trạng thái ban đầu là `pending`.
- Order gắn đúng:
  - customer
  - vehicle
  - advisor/staff đang đăng nhập
- Có task kiểm tra ban đầu.
- Nếu chọn VHC, có task VHC/inspection liên quan.

### Test Duplicate Customer/Vehicle

1. Tiếp tục tạo một lần tiếp nhận xe mới.
2. Dùng cùng phone `0909123456`.
3. Dùng biến thể biển số `51A 12345`.
4. Submit.

### Kết Quả Kỳ Vọng

- Không tạo thêm customer mới nếu phone trùng.
- Không tạo thêm vehicle mới nếu biển số chỉ khác dấu gạch/khoảng trắng/dấu chấm.
- Order mới nếu được tạo phải trỏ về đúng customer và vehicle đã có.

## 3. Test Màn Hình Order Detail Staff

### Mục Tiêu

Kiểm tra Staff thấy đúng trạng thái, task, vật tư và các action hợp lệ.

### Bước Test

1. Từ Dashboard, mở order vừa tạo.
2. URL dạng:

```text
/staff/order/{order_id}
```

3. Kiểm tra thông tin khách, xe, biển số, task.
4. Thử đổi trạng thái order sang đang thi công nếu UI có nút bắt đầu.

### Kết Quả Kỳ Vọng

- Trang order load không lỗi.
- Hiển thị đúng customer/vehicle.
- Khi bắt đầu xử lý, order chuyển sang `in_progress`.
- Các action phù hợp với trạng thái hiện tại mới hiển thị.

## 4. Test VHC/3D Defect

### Mục Tiêu

Kiểm tra Staff lưu defect trên VHC và dữ liệu được đồng bộ với VHC report/task.

### Bước Test

1. Ở order detail, mở màn kiểm tra 3D/VHC.
2. URL dạng:

```text
/staff/vehicle/{vehicle_id}/inspection
```

hoặc route VHC của UI hiện có.

3. Thêm ít nhất 2 defect:
   - Defect 1: thân xe trước, mức độ `medium`, mô tả `Xước cản trước`.
   - Defect 2: cửa sau, mức độ `high`, mô tả `Móp cửa sau`.
4. Lưu VHC.
5. Reload lại màn hình.

### Kết Quả Kỳ Vọng

- Defect vẫn hiển thị sau khi reload.
- Không bị nhân đôi defect cũ.
- Trong order detail có task/child task tương ứng với defect nếu UI hiển thị.
- VHC report vẫn ở dạng draft trước khi gửi báo giá.

### Test Sửa Defect

1. Xóa hoặc sửa 1 defect cũ.
2. Lưu lại.
3. Reload.

### Kết Quả Kỳ Vọng

- Defect cũ không còn bị lệch với task.
- Số defect và task defect phù hợp với dữ liệu mới nhất.

## 5. Test Tạo Và Gửi Báo Giá Staff

### Mục Tiêu

Đảm bảo Staff dùng luồng báo giá mới qua `Staff\QuoteController`.

### Bước Test

1. Ở order detail, bấm nút tạo báo giá.
2. URL đúng dạng:

```text
/staff/order/{order_id}/quote/create
```

3. Kiểm tra danh sách task, defect/VHC và vật tư nếu có.
4. Thêm vật tư/công việc nếu UI cho phép.
5. Bấm gửi báo giá cho khách.

### Kết Quả Kỳ Vọng

- Request gửi báo giá đi qua route:

```text
POST /staff/order/{order_id}/send-quote
```

- Order chuyển sang `pending_approval`.
- `quote_status` là `sent`.
- `quote_sent_at` có giá trị.
- Nếu order có VHC report, VHC được publish.
- Customer nhận được notification hoặc thấy báo giá trong portal.

### Case Cần Chặn

1. Thử gửi báo giá khi order không có task và không có VHC.
2. Kết quả kỳ vọng: hệ thống báo lỗi, không gửi báo giá rỗng.

## 6. Test Customer Xem Và Duyệt Báo Giá

### Mục Tiêu

Kiểm tra customer chỉ thấy báo giá/order của mình và chỉ thấy VHC đã publish.

### Bước Test

1. Đăng xuất Staff.
2. Đăng nhập bằng tài khoản Customer có số điện thoại từ order.

Với bộ dữ liệu test trong file này, dùng:

```text
Số điện thoại: 0909123456
Mật khẩu: 12345678
```
3. Mở báo giá:

```text
/customer/order/{order_id}/quote
```

hoặc:

```text
/customer/quote/{order_id}
```

4. Kiểm tra task, vật tư, tổng tiền, VHC/3D embedded.
5. Duyệt một số task.
6. Từ chối một task bất kỳ nếu UI hỗ trợ.
7. Submit quyết định.

### Kết Quả Kỳ Vọng

- Customer xem được đúng order của mình.
- Customer không xem được order của người khác.
- VHC chỉ hiển thị khi đã published.
- Task được duyệt có `customer_approval_status = approved`.
- Task bị từ chối có `customer_approval_status = rejected`.
- Nếu có ít nhất một hạng mục được duyệt, order chuyển sang `approved` và `quote_status = approved`.
- Nếu khách từ chối toàn bộ hạng mục, order chuyển sang `cancelled` và `quote_status = rejected`.
- Sau khi đã phản hồi, nếu reload lại phiếu báo giá thì khách không gửi phản hồi lần hai được.
- Các route duyệt/từ chối cũ dạng `POST /customer/order/{order_id}/approve` và `POST /customer/order/{order_id}/reject` đã được gỡ bỏ; khách phải mở phiếu báo giá chi tiết để phản hồi từng hạng mục.

### Test Dashboard Customer Sau Khi Staff Gửi Báo Giá

1. Đăng nhập Customer.
2. Mở `/customer/dashboard`.
3. Kiểm tra khu vực `Báo giá chờ bạn duyệt`.
4. Bấm `Xem phiếu báo giá`.

### Kết Quả Kỳ Vọng

- Dashboard chỉ hiển thị thẻ tóm tắt báo giá, không cho đồng ý/từ chối trực tiếp.
- Có nút `Xem phiếu báo giá` rõ ràng.
- Trang chi tiết hiển thị từng hạng mục, chi phí, vật tư, VHC/3D nếu có.
- Tổng tiền thay đổi khi chọn `Đồng ý` hoặc `Từ chối` từng hạng mục.

## 7. Test Staff Thi Công Sau Khi Khách Duyệt

### Mục Tiêu

Đảm bảo Staff chỉ thi công task hợp lệ và không thao tác task bị reject.

### Bước Test

1. Đăng nhập lại bằng Staff.
2. Mở order đã được customer xử lý báo giá.
3. Kiểm tra trạng thái order và task.
4. Kiểm tra khối `Kết quả báo giá` trong màn order detail Staff.
5. Thử start/complete các task đã được approve.
6. Thử thao tác task bị reject.
7. Nếu order vẫn đang `pending_approval`, thử ép chuyển sang `in_progress`.

### Kết Quả Kỳ Vọng

- Staff thấy rõ số hạng mục khách đồng ý, từ chối, còn chờ phản hồi và tổng tiền khách đồng ý.
- Task approved có thể thi công.
- Task rejected không cho complete/toggle.
- Task rejected không được tính vào bộ đếm hoàn thành và tiến độ thi công.
- Nút/checkbox hoàn thành nhiệm vụ cha bị làm mờ và không bấm được nếu còn nhiệm vụ con chưa xong.
- Order chưa complete được nếu còn task pending/in_progress.
- Order đang `pending_approval` không thể bị ép sang `in_progress`; phải đợi khách phản hồi báo giá.

## 8. Test Hoàn Thành Order

### Mục Tiêu

Kiểm tra điều kiện chặn hoàn thành order.

### Bước Test

1. Ở order detail, để ít nhất 1 task approved chưa completed.
2. Thử bấm hoàn thành order.

### Kết Quả Kỳ Vọng

- Hệ thống chặn hoàn thành.
- Thông báo còn task chưa hoàn thành.
- Order không bị chuyển sang `completed`.

### Test Thành Công

1. Complete tất cả task approved.
2. Bấm hoàn thành order.

### Kết Quả Kỳ Vọng

- Order chuyển sang `completed`.
- Không còn cho sửa VHC/task/vật tư chính một cách tùy tiện.

## 9. Test Thanh Toán Và Invoice

### Mục Tiêu

Đảm bảo chỉ thanh toán khi order đã completed và sau khi paid thì khóa sửa vật tư/task quan trọng.

### Bước Test

1. Tạo một order chưa completed.
2. Thử thanh toán.

### Kết Quả Kỳ Vọng

- Hệ thống chặn thanh toán nếu order chưa `completed`.

### Test Thanh Toán Đúng

1. Dùng order đã `completed`.
2. Bấm thanh toán:

```text
POST /staff/order/{order_id}/pay
```

3. Nếu có mã giảm giá hợp lệ, nhập mã vào ô `Mã giảm giá` trước khi xác nhận thanh toán.
4. Mở invoice:

```text
/staff/order/{order_id}/invoice
```

### Kết Quả Kỳ Vọng

- Payment status chuyển sang `paid`.
- Invoice load được.
- Sau khi paid, Staff không sửa/xóa vật tư/task/order tùy tiện.
- Nếu nhập mã giảm giá hợp lệ, `discount_amount` và `total_amount` được cập nhật đúng, lịch sử thao tác ghi nhận mã giảm giá.
- Nếu nhập mã giảm giá sai/hết hạn/không áp dụng cho khách hoặc xe, hệ thống báo lỗi rõ ràng và chưa đánh dấu đơn là đã thanh toán.

## 10. Test Dashboard Filter Và Trạng Thái Mới

### Mục Tiêu

Kiểm tra Staff Dashboard đã dễ tìm order và tách đúng các nhóm trạng thái.

### Bước Test

1. Đăng nhập Staff.
2. Mở:

```text
/staff/dashboard
```

3. Tạo hoặc chuẩn bị ít nhất 4 order ở các trạng thái:
   - `pending`
   - `in_progress`
   - `pending_approval`
   - `approved`
   - nếu có thể, thêm `completed`
4. Kiểm tra các nhóm bên trái:
   - Danh sách chờ/tiếp nhận
   - Đang kiểm tra/lập báo giá
   - Chờ khách duyệt
   - Khách đã duyệt
   - Hoàn thành/chờ giao
5. Dùng ô lọc tìm kiếm:
   - nhập biển số
   - nhập số điện thoại
   - nhập tên khách
   - chọn status
   - chọn advisor
   - chọn `date_from`/`date_to`
6. Bấm `Lọc`.
7. Bấm `Xóa`.

### Kết Quả Kỳ Vọng

- Order nằm đúng nhóm theo status hiện tại.
- Lọc theo biển số/SĐT/tên khách/track id trả về đúng order.
- Lọc theo status chỉ hiện đúng nhóm/order của status đó.
- Lọc theo advisor chỉ hiện order của advisor được chọn.
- Lọc theo ngày chỉ hiện order trong khoảng ngày.
- Bấm `Xóa` reset về danh sách mặc định.

## 11. Test Timeline Lịch Sử Thao Tác

### Mục Tiêu

Kiểm tra order detail có ghi và hiển thị lịch sử thao tác quan trọng.

### Bước Test

1. Mở một order bất kỳ trong Staff Dashboard.
2. Tìm block `Lịch sử thao tác` trong panel bên phải.
3. Thực hiện lần lượt:
   - tiếp nhận xe
   - lưu VHC
   - thêm task
   - cập nhật/toggle task
   - thêm ghi chú
   - gửi báo giá
   - customer duyệt/từ chối task
   - hoàn thành order
   - thanh toán
4. Reload order detail sau mỗi vài thao tác.

### Kết Quả Kỳ Vọng

- Timeline hiện tên người thao tác hoặc `System`.
- Timeline hiện action, nội dung, thời gian.
- Các action mới nằm trên đầu.
- Các mốc quan trọng có log:
  - `STAFF_ORDER_INTAKE`
  - `STAFF_VHC_SAVED`
  - `STAFF_TASK_CREATED`
  - `STAFF_TASK_STATUS_UPDATED` hoặc `STAFF_TASK_TOGGLED`
  - `STAFF_QUOTE_SENT`
  - `CUSTOMER_QUOTE_REVIEWED`
  - `STAFF_PAYMENT_RECEIVED`

## 12. Test Cảnh Báo Trước Khi Gửi Báo Giá

### Mục Tiêu

Đảm bảo Staff thấy cảnh báo dữ liệu thiếu trước khi gửi báo giá, và backend chặn lỗi critical.

### Bước Test - Cảnh Báo Warning

1. Tạo order có task nhưng chưa nhập giá công/đề xuất đầy đủ.
2. Mở:

```text
/staff/order/{order_id}/quote/create
```

3. Quan sát block `Cần kiểm tra trước khi gửi`.
4. Bấm `Gửi Báo Giá`.

### Kết Quả Kỳ Vọng

- Màn tạo báo giá hiện warning như:
  - task chưa có giá công/đề xuất
  - vật tư chưa có giá bán
  - VHC task nhưng chưa có VHC report
- Khi warning không critical, UI hỏi xác nhận trước khi gửi.

### Bước Test - Critical

1. Tạo một order không có customer hoặc không có task/VHC.
2. Thử gửi báo giá.

### Kết Quả Kỳ Vọng

- UI hiện lỗi/cảnh báo critical.
- Backend không cho gửi.
- Order không chuyển sang `pending_approval`.
- `quote_status` không bị set thành `sent`.

## 13. Test Phân Quyền Technician

### Mục Tiêu

Đảm bảo technician chỉ làm việc kỹ thuật trên task hợp lệ, không thao tác nghiệp vụ advisor.

### Bước Test

1. Đăng nhập bằng tài khoản Technician.
2. Mở:

```text
/staff/dashboard
```

3. Kiểm tra nút tiếp nhận/thêm xe mới.
4. Mở order `in_progress`.
5. Thử các thao tác:
   - tạo order/tiếp nhận xe mới
   - tạo/gửi báo giá
   - thêm task báo giá
   - thêm vật tư vào báo giá
   - cập nhật status order
   - thanh toán
   - xóa order
6. Thử thao tác task kỹ thuật hợp lệ:
   - nhận task
   - toggle/complete task approved hoặc task đang được phép làm

### Kết Quả Kỳ Vọng

- Technician không thấy hoặc không thao tác được nút tiếp nhận xe mới.
- Technician bị chặn khi tạo/gửi báo giá.
- Technician bị chặn khi thêm task/vật tư nghiệp vụ, cập nhật order, thanh toán, xóa order.
- Technician vẫn có thể thao tác task kỹ thuật hợp lệ nếu order/task cho phép.

## 14. Test Khóa Action Theo Trạng Thái

### Mục Tiêu

Đảm bảo UI và backend khớp nhau: nút sai trạng thái bị ẩn/khóa, không chỉ báo lỗi sau khi bấm.

### Bảng Kỳ Vọng

```text
pending:
- Chờ tiếp nhận xe.
- Chưa cho làm task/thanh toán/gửi báo giá.

in_progress:
- Cho lưu VHC.
- Cho thêm task/vật tư/đề xuất.
- Cho tạo và gửi báo giá.

pending_approval:
- Khóa sửa task/VHC/vật tư chính.
- Cho xem/copy báo giá.
- Không cho hoàn thành order.

approved:
- Cho thi công task đã duyệt.
- Task rejected bị khóa.
- Chỉ hoàn thành order khi task hợp lệ đã completed.

completed:
- Cho thanh toán/invoice.
- Khóa sửa task/VHC/vật tư chính.

paid:
- Khóa sửa nghiệp vụ chính.
- Cho xem/in invoice.
```

## 15. Checklist Nhanh Khi Test

- [ ] Staff tiếp nhận xe mới thành công.
- [ ] Không duplicate customer theo phone.
- [ ] Không duplicate vehicle theo biển số đã normalize.
- [ ] Repair order có `customer_id`, `vehicle_id`, `advisor_id`, `track_id`.
- [ ] Có task kiểm tra ban đầu.
- [ ] VHC defect lưu và reload đúng.
- [ ] Sửa defect không để lại task/defect cũ bị lệch.
- [ ] Gửi báo giá set `pending_approval`, `quote_status = sent`, `quote_sent_at`.
- [ ] VHC được publish khi gửi báo giá.
- [ ] Customer chỉ xem được order của mình.
- [ ] Customer duyệt/từ chối task đúng.
- [ ] Staff thấy nhiệm vụ cha bị làm mờ/khóa nếu còn nhiệm vụ con chưa xong.
- [ ] Task bị khách từ chối không làm lệch bộ đếm hoàn thành, ví dụ các task hợp lệ xong hết thì không còn kẹt ở 4/5.
- [ ] Staff không complete order khi còn task chưa xong.
- [ ] Staff không thao tác task/order đã `completed` hoặc `cancelled`.
- [ ] Chỉ thanh toán khi order `completed`.
- [ ] Sau khi paid, vật tư/task chính bị khóa sửa hợp lý.
- [ ] Dashboard lọc được theo biển số/SĐT/tên khách/status/advisor/ngày.
- [ ] Order vào đúng nhóm `pending`, `in_progress`, `pending_approval`, `approved`, `completed`.
- [ ] Timeline hiện lịch sử thao tác mới nhất.
- [ ] Màn quote create hiện warning khi dữ liệu thiếu.
- [ ] Backend chặn gửi quote nếu thiếu dữ liệu critical.
- [ ] Technician bị chặn các thao tác advisor/payment/quote.
- [ ] Technician vẫn thao tác được task kỹ thuật hợp lệ.

## 16. Ghi Nhận Lỗi Khi Test

Khi gặp lỗi, ghi lại theo format:

```text
Thời điểm:
Tài khoản:
URL:
Order ID:
Vehicle ID:
Bước đang làm:
Kết quả thực tế:
Kết quả mong đợi:
Ảnh chụp màn hình:
Log console/browser:
Log Laravel:
```

Log Laravel xem tại:

```text
storage/logs/laravel.log
```
