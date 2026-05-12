@extends('layouts.admin')

@section('title', 'Thông Báo')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 rounded-2xl border border-slate-800 bg-slate-900/70 p-6 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs font-black uppercase tracking-wider text-indigo-300">Hệ thống</p>
            <h2 class="mt-2 text-2xl font-black text-white">Thông Báo Admin</h2>
            <p class="mt-2 text-sm text-slate-400">{{ $unreadCount }} thông báo chưa đọc.</p>
        </div>

        <form action="{{ route('admin.notifications.readAll') }}" method="POST">
            @csrf
            <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-black text-white transition hover:bg-indigo-500">Đánh dấu đã đọc tất cả</button>
        </form>
    </div>

    <section class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900/70">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-800 bg-slate-950/50 text-xs font-black uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Nội dung</th>
                        <th class="px-6 py-4">Loại</th>
                        <th class="px-6 py-4">Thời gian</th>
                        <th class="px-6 py-4 text-right">Trạng thái</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($notifications as $notification)
                        @php
                            $data = $notification->data ?? [];
                            $isRead = !is_null($notification->read_at);
                            $type = $notification->type ?? '';
                            $iconLabel = 'Thông báo';
                            if (str_contains($type, 'Order')) $iconLabel = 'Lệnh sửa';
                            if (str_contains($type, 'Appointment')) $iconLabel = 'Lịch hẹn';
                            if (str_contains($type, 'Inventory')) $iconLabel = 'Kho';
                            if (str_contains($type, 'Payment')) $iconLabel = 'Thanh toán';
                        @endphp
                        <tr class="{{ $isRead ? 'bg-transparent' : 'bg-indigo-500/[0.04]' }}">
                            <td class="px-6 py-4">
                                <div class="font-bold text-white">{{ $data['title'] ?? 'Thông báo hệ thống' }}</div>
                                <div class="mt-1 text-sm text-slate-400">{{ $data['message'] ?? ($data['content'] ?? 'Không có nội dung chi tiết.') }}</div>
                                @if(!empty($data['action_url']))
                                    <a href="{{ $data['action_url'] }}" class="mt-2 inline-flex text-xs font-bold text-indigo-300 hover:text-indigo-200">Xem chi tiết</a>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-400">{{ $iconLabel }}</td>
                            <td class="px-6 py-4 text-slate-400">{{ $notification->created_at?->diffForHumans() }}</td>
                            <td class="px-6 py-4 text-right">
                                <span class="rounded-full px-3 py-1 text-xs font-black {{ $isRead ? 'bg-slate-500/10 text-slate-400' : 'bg-amber-500/10 text-amber-300' }}">
                                    {{ $isRead ? 'Đã đọc' : 'Chưa đọc' }}
                                </span>
                                @if(!$isRead)
                                    <button onclick="markAsRead('{{ $notification->id }}')" class="ml-3 text-xs font-bold text-indigo-300 hover:text-indigo-200">Đánh dấu</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-500">Chưa có thông báo nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($notifications->hasPages())
            <div class="border-t border-slate-800 p-6">
                {{ $notifications->links() }}
            </div>
        @endif
    </section>
</div>

<script>
    function markAsRead(id) {
        fetch(`{{ url('/admin/notifications') }}/${id}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => window.location.reload());
    }
</script>
@endsection
