<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa Đơn Báo Giá - {{ $order->track_id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background: #f8fafc;
            color: #334155;
            line-height: 1.5;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 40px;
            background: #fff;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
        @media print {
            body {
                background: white;
            }
            .invoice-box {
                box-shadow: none;
                padding: 0;
                margin: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="py-8">

    <!-- Control bar -->
    <div class="max-w-[800px] mx-auto mb-6 flex justify-end gap-3 no-print px-4 sm:px-0">
        <button onclick="window.close()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium rounded-lg transition-colors flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Đóng
        </button>
        <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors shadow-sm flex items-center gap-2">
            <i class="fas fa-print"></i> In Hóa Đơn
        </button>
    </div>

    <!-- Invoice Content -->
    <div class="invoice-box rounded-xl border border-slate-200">
        
        <!-- Header -->
        <div class="flex justify-between items-start mb-10 pb-6 border-b-2 border-slate-100">
            <div>
                <h1 class="text-3xl font-extrabold text-indigo-700 tracking-tight mb-2">SMART GARAGE</h1>
                <p class="text-slate-500 text-sm mb-1"><i class="fas fa-map-marker-alt w-4"></i> 123 Đường 3/2, Q.10, TP.HCM</p>
                <p class="text-slate-500 text-sm mb-1"><i class="fas fa-phone w-4"></i> Hotline: 090 123 4567</p>
                <p class="text-slate-500 text-sm"><i class="fas fa-globe w-4"></i> smartgarage.vn</p>
            </div>
            <div class="text-right">
                <h2 class="text-3xl font-bold text-slate-300 uppercase tracking-widest mb-2">Hóa Đơn</h2>
                <p class="text-slate-800 font-semibold text-lg">Mã HĐ: <span class="text-indigo-600">{{ $order->track_id ?? 'RO-'.str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</span></p>
                <p class="text-slate-500 mt-1">Ngày in: {{ now()->format('d/m/Y H:i') }}</p>
                <p class="text-slate-500">Người lập: {{ $order->advisor->name ?? 'Admin' }}</p>
            </div>
        </div>

        <!-- Customer & Vehicle Info -->
        <div class="grid grid-cols-2 gap-8 mb-10">
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Thông Tin Khách Hàng</h3>
                <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                    <p class="font-bold text-slate-800 text-lg mb-1">{{ $order->vehicle->user->name ?? 'Khách Lẻ' }}</p>
                    <p class="text-slate-600 mb-1"><i class="fas fa-phone text-slate-400 w-5"></i> {{ $order->vehicle->user->phone ?? 'N/A' }}</p>
                    <p class="text-slate-600"><i class="fas fa-envelope text-slate-400 w-5"></i> {{ $order->vehicle->user->email ?? 'N/A' }}</p>
                </div>
            </div>
            <div>
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Thông Tin Xe</h3>
                <div class="bg-slate-50 p-4 rounded-lg border border-slate-100">
                    <p class="font-bold text-slate-800 text-lg mb-1">{{ $order->vehicle->license_plate }}</p>
                    <p class="text-slate-600 mb-1"><i class="fas fa-car text-slate-400 w-5"></i> Dòng xe: <span class="font-medium text-slate-800">{{ $order->vehicle->model }}</span></p>
                    <p class="text-slate-600"><i class="fas fa-tools text-slate-400 w-5"></i> Dịch vụ: <span class="font-medium text-slate-800">{{ $order->service_type ?? 'Bảo dưỡng / Sửa chữa' }}</span></p>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Chi Tiết Hạng Mục Sửa Chữa</h3>
        <div class="mb-8 rounded-lg overflow-hidden border border-slate-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 text-sm">
                        <th class="py-3 px-4 font-semibold w-12 text-center">STT</th>
                        <th class="py-3 px-4 font-semibold">Tên Dịch Vụ / Phụ Tùng</th>
                        <th class="py-3 px-4 font-semibold text-right">Tiền Công</th>
                        <th class="py-3 px-4 font-semibold text-right">Phụ Tùng</th>
                        <th class="py-3 px-4 font-semibold text-right text-indigo-700">Thành Tiền</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    @php 
                        $i = 1; 
                        $totalLabor = 0;
                        $totalParts = 0;
                    @endphp
                    @foreach($order->tasks->where('parent_id', '!=', null) as $task)
                        @if($task->status != 'rejected')
                            @php
                                $partCost = $task->items->sum('subtotal');
                                $rowTotal = $task->labor_cost + $partCost;
                                $totalLabor += $task->labor_cost;
                                $totalParts += $partCost;
                            @endphp
                            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                                <td class="py-3 px-4 text-center text-slate-400">{{ $i++ }}</td>
                                <td class="py-3 px-4">
                                    <p class="font-semibold text-slate-800">{{ $task->title }}</p>
                                    @if($task->items->count() > 0)
                                        <p class="text-xs text-slate-500 mt-1">Vật tư: {{ $task->items->pluck('part_name')->join(', ') }}</p>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right text-slate-600">{{ number_format($task->labor_cost) }} ₫</td>
                                <td class="py-3 px-4 text-right text-slate-600">{{ number_format($partCost) }} ₫</td>
                                <td class="py-3 px-4 text-right font-bold text-slate-800">{{ number_format($rowTotal) }} ₫</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals & Payment (Bottom section) -->
        <div class="flex justify-between items-start pt-6 border-t-2 border-slate-100">
            <!-- Payment QR -->
            <div class="w-1/2 pr-8">
                <div class="bg-indigo-50/50 p-5 rounded-xl border border-indigo-100/50 flex items-start gap-5">
                    <div class="w-32 h-32 bg-white p-2 rounded-lg border border-slate-200 shadow-sm flex-shrink-0">
                        <img src="{{ $qrUrl }}" alt="QR Code Thanh Toán" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h4 class="font-bold text-indigo-800 mb-1 leading-tight">Thanh Toán Quét Mã QR</h4>
                        <p class="text-xs text-slate-600 mb-2">Sử dụng ứng dụng ngân hàng để quét mã. Giao dịch sẽ được ghi nhận tự động.</p>
                        <div class="space-y-1">
                            <p class="text-xs text-slate-700">Ngân hàng: <span class="font-semibold">{{ strtoupper(\App\Models\Setting::get('bank_id', 'vietinbank')) }}</span></p>
                            <p class="text-xs text-slate-700">Chủ TK: <span class="font-semibold">{{ strtoupper(\App\Models\Setting::get('bank_account_name', 'NGO VAN DAN')) }}</span></p>
                            <p class="text-xs text-slate-700">Số TK: <span class="font-semibold">{{ \App\Models\Setting::get('bank_account_no', '102875143924') }}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Table -->
            <div class="w-1/2">
                <table class="w-full text-right text-sm">
                    <tr>
                        <td class="py-2 text-slate-500">Tổng Tiền Công:</td>
                        <td class="py-2 font-medium text-slate-800">{{ number_format($totalLabor) }} ₫</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-slate-500">Tổng Tiền Phụ Tùng:</td>
                        <td class="py-2 font-medium text-slate-800">{{ number_format($totalParts) }} ₫</td>
                    </tr>
                    <tr class="border-b border-slate-100">
                        <td class="py-2 text-slate-500">Cộng Tiền Hàng:</td>
                        <td class="py-2 font-medium text-slate-800">{{ number_format($totalLabor + $totalParts) }} ₫</td>
                    </tr>
                    <tr>
                        <td class="py-2 text-slate-500">Thuế GTGT (VAT 10%):</td>
                        <td class="py-2 font-medium text-slate-800">
                            @php $vat = ($totalLabor + $totalParts) * 0.1; @endphp
                            {{ number_format($vat) }} ₫
                        </td>
                    </tr>
                    <tr>
                        <td class="py-4 text-slate-800 font-bold text-lg uppercase">Tổng Cộng:</td>
                        <td class="py-4 font-extrabold text-indigo-700 text-2xl">
                            {{ number_format($order->total_amount) }} ₫
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- End Note -->
        <div class="mt-12 text-center text-sm text-slate-400 italic border-t border-slate-100 pt-6">
            <p>Cảm ơn Quý khách đã tin tưởng và sử dụng dịch vụ của Smart Garage!</p>
            <p>Xin vui lòng kiểm tra kỹ hóa đơn trước khi thanh toán.</p>
        </div>

    </div>

</body>
</html>
