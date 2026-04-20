@extends('layouts.admin')

@section('title', 'Inventory Logs')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.inventory.index') }}" class="hover:text-indigo-400 transition">Inventory</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-white">Transaction Logs</span>
    </div>

    <div class="glass-panel rounded-2xl border border-slate-700/50 overflow-hidden">
        <table class="w-full text-left text-sm text-slate-400">
            <thead class="bg-slate-900/50 text-xs uppercase font-bold text-slate-500">
                <tr>
                    <th class="px-6 py-4">Time</th>
                    <th class="px-6 py-4">Part</th>
                    <th class="px-6 py-4">Type</th>
                    <th class="px-6 py-4">Qty</th>
                    <th class="px-6 py-4">User</th>
                    <th class="px-6 py-4">Note</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @foreach($transactions as $log)
                <tr class="hover:bg-slate-800/30 transition">
                    <td class="px-6 py-4">{{ $log->created_at->format('M d, H:i') }}</td>
                    <td class="px-6 py-4 font-bold text-white">{{ $log->part->name ?? 'Deleted Part' }}</td>
                    <td class="px-6 py-4">
                        @if($log->type == 'in')
                            <span class="text-green-400 font-bold uppercase text-xs bg-green-500/10 px-2 py-1 rounded">IN</span>
                        @else
                            <span class="text-red-400 font-bold uppercase text-xs bg-red-500/10 px-2 py-1 rounded">OUT</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 font-mono text-white">{{ $log->quantity }}</td>
                    <td class="px-6 py-4">{{ $log->user->name ?? 'System' }}</td>
                    <td class="px-6 py-4 text-xs italic">{{ $log->note }} {{ $log->reference ? "($log->reference)" : '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-800">{{ $transactions->links() }}</div>
    </div>
</div>
@endsection
