<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa Đơn #{{ $repairOrder->track_id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-gray-100 p-8 text-gray-800">

    <!-- Print Controls -->
    <div class="max-w-3xl mx-auto mb-6 flex justify-between items-center no-print">
        <a href="{{ route('admin.repair_orders.show', $repairOrder->id) }}" class="text-indigo-600 hover:underline">&larr; Quay lại</a>
        <button onclick="window.print()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg font-bold shadow hover:bg-indigo-500">In Hóa Đơn</button>
    </div>

    <!-- Invoice Paper -->
    <div class="max-w-3xl mx-auto bg-white p-12 rounded-lg shadow-xl ring-1 ring-gray-900/5">
        
        <!-- Header -->
        <div class="flex justify-between items-start mb-12">
            <div>
                <h1 class="text-4xl font-black text-indigo-900 mb-2">INVOICE</h1>
                <p class="text-sm text-gray-500">Hóa Đơn Dịch Vụ Sửa Chữa</p>
            </div>
            <div class="text-right">
                <h2 class="font-bold text-xl text-gray-900">Smart Garage</h2>
                <p class="text-gray-500">123 Đường ABC, Quận XYZ</p>
                <p class="text-gray-500">Hà Nội, Việt Nam</p>
                <p class="text-gray-500">Hotline: 0912.345.678</p>
            </div>
        </div>

        <!-- Info Grid -->
        <div class="grid grid-cols-2 gap-12 mb-12">
            <div>
                <h3 class="font-bold text-gray-900 uppercase tracking-wider text-xs mb-4">Khách Hàng</h3>
                <div class="text-gray-600 space-y-1">
                    <p class="font-bold text-gray-800">{{ $repairOrder->customer->name }}</p>
                    <p>{{ $repairOrder->customer->phone }}</p>
                    @if($repairOrder->customer->address)
                        <p>{{ $repairOrder->customer->address }}</p>
                    @endif
                    @if($repairOrder->customer->email)
                        <p>{{ $repairOrder->customer->email }}</p>
                    @endif
                </div>
            </div>
            <div class="text-right">
                <h3 class="font-bold text-gray-900 uppercase tracking-wider text-xs mb-4">Thông Tin Phiếu</h3>
                <div class="space-y-1">
                    <p><span class="text-gray-500">Số Phiếu:</span> <span class="font-mono font-bold">{{ $repairOrder->track_id }}</span></p>
                    <p><span class="text-gray-500">Ngày Tạo:</span> {{ $repairOrder->created_at->format('d/m/Y') }}</p>
                    <p><span class="text-gray-500">Xe:</span> {{ $repairOrder->vehicle->license_plate }} ({{ $repairOrder->vehicle->model }})</p>
                    <p><span class="text-gray-500">Odo:</span> {{ number_format($repairOrder->odometer_reading) }} km</p>
                </div>
            </div>
        </div>

        <!-- Line Items -->
        <table class="w-full mb-12">
            <thead>
                <tr class="text-left border-b-2 border-indigo-100">
                    <th class="py-3 font-bold text-xs uppercase text-indigo-600">Mô Tả Dịch Vụ / Phụ Tùng</th>
                    <th class="py-3 font-bold text-xs uppercase text-indigo-600 text-right">SL</th>
                    <th class="py-3 font-bold text-xs uppercase text-indigo-600 text-right">Đơn Giá</th>
                    <th class="py-3 font-bold text-xs uppercase text-indigo-600 text-right">Thành Tiền</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @foreach($repairOrder->items as $item)
                <tr>
                    <td class="py-4 text-gray-800">
                        <span class="font-bold">{{ $item->itemable->name }}</span>
                        <div class="text-xs text-gray-500">{{ $item->itemable_type == 'App\Models\Part' ? 'Phụ tùng' : 'Dịch vụ' }}</div>
                    </td>
                    <td class="py-4 text-right text-gray-600">{{ $item->quantity }}</td>
                    <td class="py-4 text-right text-gray-600">{{ number_format($item->unit_price, 0) }} ₫</td>
                    <td class="py-4 text-right font-medium text-gray-900">{{ number_format($item->subtotal, 0) }} ₫</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="py-6 text-right font-bold text-gray-600">Tổng Cộng</td>
                    <td class="py-6 text-right font-black text-2xl text-indigo-600">{{ number_format($repairOrder->total_amount, 0) }} ₫</td>
                </tr>
            </tfoot>
        </table>

        <!-- Payment Info -->
        <div class="bg-gray-50 rounded-lg p-6 flex justify-between items-center mb-12 border border-gray-100">
            <div>
                <h3 class="font-bold text-gray-900 mb-1">Trạng Thái Thanh Toán</h3>
                @if($repairOrder->payment_status == 'paid')
                    <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold uppercase">Đã Thanh Toán</span>
                @elseif($repairOrder->payment_status == 'partial')
                     <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-bold uppercase">Thanh Toán Một Phần</span>
                @else
                     <span class="inline-block px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold uppercase">Chưa Thanh Toán</span>
                @endif
                @if($repairOrder->payment_method)
                    <p class="text-sm text-gray-500 mt-2">Phương thức: {{ ucfirst($repairOrder->payment_method) }}</p>
                @endif
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500 italic">Cảm ơn quý khách đã sử dụng dịch vụ!</p>
            </div>
        </div>

        <!-- Signature -->
        <div class="grid grid-cols-2 gap-12 mt-20 text-center">
            <div>
                <p class="font-bold text-gray-900 mb-20">Khách Hàng</p>
                <p class="text-xs text-gray-400">(Ký và ghi rõ họ tên)</p>
            </div>
             <div>
                <p class="font-bold text-gray-900 mb-20">Người Lập Phiếu</p>
                <p class="font-bold text-gray-800">{{ $repairOrder->advisor->name ?? 'Admin' }}</p>
            </div>
        </div>
    </div>

</body>
</html>
