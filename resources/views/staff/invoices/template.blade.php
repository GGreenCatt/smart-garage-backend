<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn - {{ $order->track_id }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0;
            font-size: 12px;
            color: #555;
        }
        .info-section {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-section td {
            vertical-align: top;
            padding: 5px;
        }
        .info-box {
            width: 48%;
            display: inline-block;
            vertical-align: top;
        }
        .info-title {
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
            color: #555;
            margin-bottom: 5px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 3px;
        }
        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.items th, table.items td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table.items th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 13px;
            text-align: center;
        }
        table.items td.text-right {
            text-align: right;
        }
        table.items td.text-center {
            text-align: center;
        }
        .totals {
            width: 40%;
            float: right;
        }
        .totals table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals td {
            padding: 5px 8px;
        }
        .totals .total-row td {
            font-weight: bold;
            border-top: 2px solid #333;
            font-size: 16px;
        }
        .footer {
            clear: both;
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        .signatures {
            width: 100%;
            margin-top: 40px;
        }
        .signatures table {
            width: 100%;
            text-align: center;
        }
        .signatures td {
            width: 50%;
            padding-top: 10px;
        }
        .signatures .sign-title {
            font-weight: bold;
        }
        .signatures .sign-space {
            height: 80px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>SMART GARAGE</h1>
        <p>Địa chỉ: 123 Đường B, Quận C, TP. D</p>
        <p>Điện thoại: 0123 456 789 | Email: contact@smartgarage.com</p>
        <h2 style="margin-top: 15px; font-size: 20px;">HÓA ĐƠN THANH TOÁN</h2>
        <p>Mã Hóa Đơn: #INV-{{ $order->track_id }}</p>
        <p>Ngày in: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info-section">
        <table width="100%">
            <tr>
                <td width="50%">
                    <div class="info-title">THÔNG TIN KHÁCH HÀNG</div>
                    <p><strong>Khách hàng:</strong> {{ $order->customer->name ?? 'Khách vãng lai' }}</p>
                    <p><strong>Số điện thoại:</strong> {{ $order->customer->phone ?? 'N/A' }}</p>
                </td>
                <td width="50%">
                    <div class="info-title">THÔNG TIN XE</div>
                    <p><strong>Biển số:</strong> {{ $order->vehicle->license_plate ?? 'N/A' }}</p>
                    <p><strong>Loại xe:</strong> {{ $order->vehicle->brand ?? '' }} {{ $order->vehicle->model ?? '' }}</p>
                    <p><strong>Số ODO:</strong> {{ number_format($order->odometer_reading ?? 0) }} km</p>
                </td>
            </tr>
        </table>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th width="5%">STT</th>
                <th width="45%">Mô tả (Dịch vụ / Phụ tùng)</th>
                <th width="10%">Số lượng</th>
                <th width="20%">Đơn giá (VNĐ)</th>
                <th width="20%">Thành tiền (VNĐ)</th>
            </tr>
        </thead>
        <tbody>
            @php $stt = 1; @endphp
            
            {{-- Loop Tasks as Services --}}
            @if($order->tasks && $order->tasks->count() > 0)
                <tr>
                    <td colspan="5" style="background-color: #f9f9f9; font-weight: bold;">I. Dịch vụ sửa chữa</td>
                </tr>
                @foreach($order->tasks as $task)
                    <tr>
                        <td class="text-center">{{ $stt++ }}</td>
                        <td>{{ $task->name }}</td>
                        <td class="text-center">1</td>
                        <td class="text-right">{{ number_format($task->labor_cost ?? 0) }}</td>
                        <td class="text-right">{{ number_format($task->labor_cost ?? 0) }}</td>
                    </tr>
                @endforeach
            @endif

            {{-- Loop Items as Parts --}}
            @if($order->items && $order->items->count() > 0)
                <tr>
                    <td colspan="5" style="background-color: #f9f9f9; font-weight: bold;">II. Phụ tùng thay thế</td>
                </tr>
                @foreach($order->items as $item)
                    <tr>
                        <td class="text-center">{{ $stt++ }}</td>
                        <td>{{ $item->product->name ?? 'Phụ tùng' }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->price) }}</td>
                        <td class="text-right">{{ number_format($item->total_price) }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>Cộng tiền hàng:</td>
                <td class="text-right">{{ number_format($order->subtotal ?? 0) }} VNĐ</td>
            </tr>
            @if($order->discount_amount > 0)
            <tr>
                <td>Chiết khấu/Giảm giá:</td>
                <td class="text-right">- {{ number_format($order->discount_amount) }} VNĐ</td>
            </tr>
            @endif
            @if($order->tax_amount > 0)
            <tr>
                <td>Thuế VAT:</td>
                <td class="text-right">{{ number_format($order->tax_amount) }} VNĐ</td>
            </tr>
            @endif
            <tr class="total-row">
                <td>Tổng thanh toán:</td>
                <td class="text-right">{{ number_format($order->total_amount ?? 0) }} VNĐ</td>
            </tr>
        </table>
    </div>

    <div class="signatures">
        <table>
            <tr>
                <td>
                    <div class="sign-title">Khách hàng</div>
                    <div style="font-size: 11px; color: #777;">(Ký, ghi rõ họ tên)</div>
                    <div class="sign-space"></div>
                </td>
                <td>
                    <div class="sign-title">Cố vấn dịch vụ / Thu ngân</div>
                    <div style="font-size: 11px; color: #777;">(Ký, ghi rõ họ tên)</div>
                    <div class="sign-space"></div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Cảm ơn quý khách đã sử dụng dịch vụ tại Smart Garage!</p>
        <p>Xin quý khách lưu ý giữ hóa đơn để đối chiếu khi cần thiết.</p>
    </div>

</body>
</html>
