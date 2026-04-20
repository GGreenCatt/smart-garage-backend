<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa Đơn Cửa Hàng - {{ $order->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #e2e8f0; }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 40px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .invoice-box { box-shadow: none; padding: 0; max-width: 100%; border: none; margin: 0; }
            .no-print { display: none !important; }
            .print-exact { color-adjust: exact; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body class="py-10">

<div class="invoice-box rounded-xl">
    
    <!-- Controls (No Print) -->
    <div class="no-print flex justify-end gap-3 mb-8">
        <button onclick="window.close()" class="px-4 py-2 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 font-bold transition">
            <i class="fas fa-times mr-1"></i> Đóng
        </button>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-bold shadow transition">
            <i class="fas fa-print mr-1"></i> In Hóa Đơn
        </button>
    </div>

    <!-- Header -->
    <div class="flex justify-between items-start mb-10 border-b-2 border-slate-100 pb-8">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 bg-gradient-to-br from-teal-500 to-blue-600 rounded-2xl flex items-center justify-center text-white text-3xl print-exact shadow-lg">
                <i class="fas fa-car-wrench"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">SMART GARAGE</h1>
                <p class="text-slate-500 text-sm font-medium mt-1">Dịch vụ sửa chữa & Bảo dưỡng xe chuyên nghiệp</p>
                <div class="mt-2 text-xs text-slate-400 space-y-0.5">
                    <p><i class="fas fa-map-marker-alt w-4 text-center"></i> 123 Đường Điện Biên Phủ, Quận Bình Thạnh, TP.HCM</p>
                    <p><i class="fas fa-phone-alt w-4 text-center"></i> Hotline: 1900 1234 56</p>
                    <p><i class="fas fa-envelope w-4 text-center"></i> contact@smartgarage.vn</p>
                </div>
            </div>
        </div>
        <div class="text-right">
            <h2 class="text-3xl font-black text-teal-600 tracking-tight uppercase print-exact">HÓA ĐƠN</h2>
            <p class="text-slate-500 font-bold text-lg">#{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</p>
            <div class="mt-4 text-sm text-slate-600 space-y-1">
                <p><span class="text-slate-400 font-medium">Ngày In:</span> {{ now()->format('d/m/Y') }}</p>
                <p><span class="text-slate-400 font-medium">Cố Vấn:</span> <span class="font-semibold">{{ $order->advisor->name ?? 'N/A' }}</span></p>
            </div>
        </div>
    </div>

    <!-- Customer & Vehicle Info -->
    <div class="grid grid-cols-2 gap-8 mb-10">
        <div class="bg-slate-50 p-5 rounded-xl border border-slate-100 print-exact">
            <h3 class="text-xs font-bold text-teal-600 uppercase tracking-widest mb-3"><i class="fas fa-user mr-1.5"></i>Khách Hàng</h3>
            <div class="space-y-1 text-sm text-slate-700">
                <p class="font-bold text-base text-slate-900 mb-2">{{ $order->vehicle->user->name ?? 'Khách Mới' }}</p>
                <p><i class="fas fa-phone-alt w-5 text-slate-400"></i>{{ $order->vehicle->owner_phone ?? $order->vehicle->user->phone ?? 'N/A' }}</p>
                @if($order->vehicle->user?->email)
                <p><i class="fas fa-envelope w-5 text-slate-400"></i>{{ $order->vehicle->user->email }}</p>
                @endif
            </div>
        </div>
        <div class="bg-slate-50 p-5 rounded-xl border border-slate-100 print-exact">
            <h3 class="text-xs font-bold text-blue-600 uppercase tracking-widest mb-3"><i class="fas fa-car mr-1.5"></i>Phương Tiện</h3>
            <div class="space-y-1 text-sm text-slate-700">
                <p class="font-black text-lg text-slate-900 tracking-wider font-mono mb-2">{{ $order->vehicle->license_plate }}</p>
                <p><span class="text-slate-400 w-16 inline-block">Dòng xe:</span> <span class="font-semibold">{{ $order->vehicle->brand }} {{ $order->vehicle->model }} ({{ $order->vehicle->year }})</span></p>
                <p><span class="text-slate-400 w-16 inline-block">Số VIN:</span> <span class="font-mono text-xs">{{ $order->vehicle->vin ?? 'N/A' }}</span></p>
            </div>
        </div>
    </div>

    <!-- Items Table -->
    <div class="mb-8">
        <h3 class="text-lg font-bold text-slate-800 mb-4 border-l-4 border-teal-500 pl-3">Chi tiết hạng mục</h3>
        <table class="w-full text-left text-sm border-collapse">
            <thead>
                <tr class="bg-slate-800 text-white rounded-lg overflow-hidden print-exact">
                    <th class="p-3 rounded-tl-lg w-12 text-center">STT</th>
                    <th class="p-3">Hạng mục sửa chữa / Phụ tùng</th>
                    <th class="p-3 text-center w-20">SL</th>
                    <th class="p-3 text-right w-32">Đơn giá (VNĐ)</th>
                    <th class="p-3 rounded-tr-lg text-right w-36">Thành tiền (VNĐ)</th>
                </tr>
            </thead>
            <tbody class="text-slate-700">
                @forelse($order->items as $index => $item)
                <tr class="border-b border-slate-200">
                    <td class="p-3 text-center text-slate-400 font-mono text-xs">{{ $index + 1 }}</td>
                    <td class="p-3">
                        <span class="font-semibold">{{ $item->name }}</span>
                        @if($item->sku)
                            <span class="block text-[10px] text-slate-400 font-mono mt-0.5">{{ $item->sku }}</span>
                        @endif
                    </td>
                    <td class="p-3 text-center font-medium">{{ $item->qty }}</td>
                    <td class="p-3 text-right tabular-nums">{{ number_format($item->price) }}</td>
                    <td class="p-3 text-right font-bold tabular-nums">{{ number_format($item->qty * $item->price) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-6 text-center text-slate-400 italic bg-slate-50">Không có hạng mục nào được ghi nhận.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Summary & QR -->
    <div class="flex justify-between items-end mt-12 bg-slate-50 p-6 rounded-2xl border border-slate-100 print-exact">
        <div class="flex items-center gap-6">
            @if(isset($qrUrl))
            <div class="text-center">
                <div class="bg-white p-2 rounded-xl border border-slate-200 shadow-sm inline-block">
                    <img src="{{ $qrUrl }}" alt="QR Code" class="w-28 h-28 object-contain">
                </div>
                <p class="text-[10px] font-bold text-slate-500 uppercase mt-2 tracking-wider">Quét mã VietQR</p>
            </div>
            <div class="text-sm text-slate-600 max-w-[200px]">
                <p class="font-bold text-slate-800 mb-1"><i class="fas fa-university text-blue-600 mr-2"></i>Chuyển Khoản Ngân Hàng</p>
                <div class="space-y-1 text-xs">
                    <p>Ngân hàng: <b>{{ \App\Models\Setting::get('bank_id', 'VietinBank') }}</b></p>
                    <p>STK: <b class="font-mono text-teal-600">{{ \App\Models\Setting::get('bank_account_no', '102875143924') }}</b></p>
                    <p>Chủ TK: <b>{{ urldecode(\App\Models\Setting::get('bank_account_name', 'NGO VAN DAN')) }}</b></p>
                </div>
            </div>
            @endif
        </div>
        
        <div class="w-72">
            <div class="space-y-3">
                <div class="flex justify-between text-slate-500 text-sm">
                    <span class="uppercase font-medium tracking-wide">Tạm tính:</span>
                    <span class="font-mono font-medium">{{ number_format($order->items->sum('total')) }} đ</span>
                </div>
                <div class="flex justify-between text-slate-500 text-sm">
                    <span class="uppercase font-medium tracking-wide">Thuế VAT (0%):</span>
                    <span class="font-mono font-medium">0 đ</span>
                </div>
                <div class="flex justify-between text-teal-600 text-xl font-black items-end pt-3 border-t-2 border-slate-200 border-dashed print-exact">
                    <span class="uppercase tracking-widest text-sm mb-0.5">Tổng Cộng:</span>
                    <span>{{ number_format($order->total_amount) }} đ</span>
                </div>
            </div>
            <p class="text-center text-[10px] text-slate-400 mt-6 pt-4 border-t border-slate-200 print-exact">Cảm ơn Quý Khách đã sử dụng dịch vụ định kỳ. Hẹn gặp lại!</p>
        </div>
    </div>
</div>

<script>
    // Auto print if requested via query param (optional)
    if (new URLSearchParams(window.location.search).has('print')) {
        setTimeout(() => window.print(), 500);
    }
</script>
</body>
</html>
