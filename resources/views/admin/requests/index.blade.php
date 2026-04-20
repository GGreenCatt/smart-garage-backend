@extends('layouts.admin')

@section('title', 'Quản Lý Yêu Cầu Vật Tư')

@section('content')
<!-- Custom Tailwind Config -->
<script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    primary: "#6366f1", // Indigo 500
                    "background-light": "#f3f4f6", // Gray 100
                    "background-dark": "#0f172a", // Slate 900
                    "surface-light": "#ffffff",
                    "surface-dark": "#1e293b", // Slate 800
                    "glass-dark": "rgba(30, 41, 59, 0.7)",
                    "glass-border": "rgba(255, 255, 255, 0.08)",
                    success: "#10b981",
                    danger: "#ef4444",
                    warning: "#f59e0b",
                },
                fontFamily: {
                    sans: ['Inter', 'sans-serif'],
                },
            },
        },
    };
</script>
<style>
    .glass-panel {
        background: rgba(30, 41, 59, 0.6);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #475569;
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #64748b;
    }
</style>

<div class="font-sans text-gray-100 antialiased min-h-screen">
    
    <!-- Header Section Removed -->

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @php
            $todayCount = $pendingRequests->where('created_at', '>=', now()->startOfDay())->count() + $historyRequests->where('updated_at', '>=', now()->startOfDay())->count();
        @endphp
        <!-- Card 1 -->
        <div class="bg-glass-dark border border-glass-border rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-400">Yêu Cầu Hôm Nay</p>
                    <h3 class="text-2xl font-bold text-white mt-1">{{ $todayCount }}</h3>
                </div>
                <div class="p-2 bg-blue-500/10 rounded-lg">
                    <span class="material-icons-round text-blue-500">assignment</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-success">
                <span class="material-icons-round text-sm mr-1">trending_up</span>
                <span>Active</span>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-glass-dark border border-glass-border rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-400">Chờ Duyệt (Pending)</p>
                    <h3 class="text-2xl font-bold text-white mt-1">{{ $pendingRequests->count() }}</h3>
                </div>
                <div class="p-2 bg-warning/10 rounded-lg">
                    <span class="material-icons-round text-warning">hourglass_empty</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-gray-400">
                <span>Cần xử lý ngay</span>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-glass-dark border border-glass-border rounded-xl p-5 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-400">Stock Alert</p>
                    <h3 class="text-2xl font-bold text-white mt-1">-</h3>
                </div>
                <div class="p-2 bg-danger/10 rounded-lg">
                    <span class="material-icons-round text-danger">warning</span>
                </div>
            </div>
            <div class="mt-4 flex items-center text-xs text-danger">
                <span>Low inventory</span>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold flex items-center gap-2 text-white w-full md:w-auto">
            <span class="material-icons-round text-primary">verified_user</span>
            Phê Duyệt Vật Tư
        </h2>
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-icons-round text-gray-400 group-focus-within:text-primary transition-colors">search</span>
                </div>
                <input class="block w-full sm:w-64 pl-10 pr-3 py-2 border border-gray-600 rounded-lg leading-5 bg-surface-dark/50 text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent sm:text-sm transition-all shadow-sm" placeholder="Tìm kiếm yêu cầu..." type="text"/>
            </div>
            <div class="flex bg-surface-dark rounded-lg p-1">
                <button class="px-4 py-1.5 rounded-md text-sm font-medium bg-gray-700 shadow-sm text-white transition-all">All</button>
            </div>
        </div>
    </div>

    <!-- Pending Requests Section -->
    <section class="mb-10">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <span class="material-icons-round text-warning text-lg animate-pulse">pending</span>
                <h3 class="text-lg font-semibold text-gray-200">Chờ Duyệt (Pending)</h3>
            </div>
            <span class="bg-warning/20 text-warning text-xs font-bold px-2.5 py-0.5 rounded-full border border-warning/20">{{ $pendingRequests->count() }} yêu cầu</span>
        </div>

        <div class="grid grid-cols-1 gap-4">
            @forelse($pendingRequests as $req)
            <div class="bg-surface-dark border border-glass-border rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 relative overflow-hidden group">
                <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-warning"></div>
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
                    <div class="flex items-start gap-4 flex-1">
                        <div class="h-12 w-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg text-white font-bold text-lg flex-shrink-0">
                            {{ substr($req->staff->name, 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h4 class="text-xl font-bold text-white">{{ $req->part_name }}</h4>
                                <span class="bg-primary/20 text-primary text-xs font-bold px-2 py-0.5 rounded border border-primary/20">x{{ $req->quantity }}</span>
                            </div>
                            <div class="flex items-center text-sm text-gray-400 mb-3 gap-2">
                                <span class="font-medium text-gray-300">{{ $req->staff->name }}</span>
                                <span class="w-1 h-1 rounded-full bg-gray-400"></span>
                                <span class="flex items-center gap-1"><span class="material-icons-round text-xs">schedule</span> {{ $req->created_at->diffForHumans() }}</span>
                            </div>
                            @if($req->reason)
                            <div class="bg-black/20 p-3 rounded-lg border border-white/5 inline-block max-w-2xl w-full">
                                <p class="text-gray-300 italic text-sm flex gap-2">
                                    <span class="material-icons-round text-gray-400 text-base">format_quote</span>
                                    {{ $req->reason }}
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-3 self-end lg:self-center">
                        <button onclick="approveRequest({{ $req->id }}, '{{ addslashes($req->part_name) }}')" class="h-12 w-12 rounded-full bg-white/5 border border-white/10 flex items-center justify-center group hover:bg-success hover:border-success transition-all duration-300 shadow-sm" title="Approve">
                            <span class="material-icons-round text-success text-2xl group-hover:text-white transition-colors">check</span>
                        </button>
                        <button onclick="rejectRequest({{ $req->id }}, '{{ addslashes($req->part_name) }}')" class="h-12 w-12 rounded-full bg-white/5 border border-white/10 flex items-center justify-center group hover:bg-danger hover:border-danger transition-all duration-300 shadow-sm" title="Reject">
                            <span class="material-icons-round text-danger text-2xl group-hover:text-white transition-colors">close</span>
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-10 text-gray-500 italic">Không có yêu cầu nào đang chờ duyệt.</div>
            @endforelse
        </div>
    </section>

    <!-- History Section -->
    <section>
        <div class="flex items-center gap-2 mb-4 mt-8">
            <span class="material-icons-round text-gray-400 text-lg">history</span>
            <h3 class="text-lg font-semibold text-gray-200 uppercase tracking-wide text-sm">Lịch sử xử lý</h3>
        </div>
        <div class="bg-surface-dark border border-glass-border rounded-xl shadow-sm overflow-hidden">
            <ul class="divide-y divide-gray-800">
                @foreach($historyRequests as $req)
                <li class="p-4 hover:bg-white/5 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 relative">
                                <div class="h-2.5 w-2.5 rounded-full {{ $req->status == 'approved' ? 'bg-success shadow-[0_0_8px_rgba(16,185,129,0.6)]' : 'bg-danger shadow-[0_0_8px_rgba(239,68,68,0.6)]' }}"></div>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-white">
                                    {{ $req->part_name }} <span class="text-gray-400 text-xs font-normal ml-1">(x{{ $req->quantity }})</span>
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1">
                                    <span class="material-icons-round text-[10px]">person</span> {{ $req->staff->name }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            @if($req->status == 'approved')
                            <span class="bg-success/10 text-success text-xs px-2 py-0.5 rounded mb-1 inline-block">Approved</span>
                            @else
                            <span class="bg-danger/10 text-danger text-xs px-2 py-0.5 rounded mb-1 inline-block">Rejected</span>
                            @endif
                            <p class="text-xs text-gray-400 font-mono">{{ $req->updated_at->format('d/m H:i') }}</p>
                            @if($req->admin_note)
                                <div class="text-[10px] text-gray-500 italic mt-1 max-w-[200px] truncate" title="{{ $req->admin_note }}">
                                    Note: {{ $req->admin_note }}
                                </div>
                            @endif
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </section>
</div>

<!-- Process Request Modal (Approve/Reject) -->
<dialog id="processModal" class="bg-surface-dark text-white border border-gray-700 rounded-3xl shadow-2xl p-0 w-full max-w-md backdrop:bg-black/80 overflow-hidden">
    <div class="relative">
        <div id="modalHeader" class="bg-success p-6 text-white">
            <h3 class="font-black text-xl flex items-center gap-2">
                <span id="modalIcon" class="material-icons-round">check_circle</span>
                <span id="modalTitle">Phê Duyệt Yêu Cầu</span>
            </h3>
            <p id="modalPartNameDisplay" class="text-white/80 text-sm mt-1 font-medium"></p>
        </div>
        
        <form id="processForm" method="POST" class="p-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="status" id="modalStatusInput">
            
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-400 mb-2 uppercase tracking-wider">Lời nhắn của bạn (Tùy chọn)</label>
                <textarea name="admin_note" id="admin_note" rows="4" 
                    class="w-full bg-background-dark border border-gray-600 rounded-2xl p-4 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition-all resize-none" 
                    placeholder="Nhập lời nhắn hoặc lý do gửi đến nhân viên..."></textarea>
            </div>

            <div class="flex gap-3 mt-4">
                <button type="button" onclick="document.getElementById('processModal').close()" 
                    class="flex-1 px-4 py-3 bg-white/5 hover:bg-white/10 text-gray-300 font-bold rounded-2xl transition-all border border-white/10">
                    Hủy Bỏ
                </button>
                <button id="modalSubmitBtn" class="flex-2 px-8 py-3 bg-success hover:bg-emerald-500 text-white font-bold rounded-2xl shadow-xl shadow-emerald-500/20 active:scale-95 transition-all">
                    Xác Nhận
                </button>
            </div>
        </form>
    </div>
</dialog>

<script>
    const processModal = document.getElementById('processModal');
    const processForm = document.getElementById('processForm');
    const modalHeader = document.getElementById('modalHeader');
    const modalIcon = document.getElementById('modalIcon');
    const modalTitle = document.getElementById('modalTitle');
    const modalPartNameDisplay = document.getElementById('modalPartNameDisplay');
    const modalStatusInput = document.getElementById('modalStatusInput');
    const modalSubmitBtn = document.getElementById('modalSubmitBtn');
    const adminNoteArea = document.getElementById('admin_note');

    function approveRequest(id, partName) {
        openModal(id, partName, 'approved');
    }

    function rejectRequest(id, partName) {
        openModal(id, partName, 'rejected');
    }

    function openModal(id, partName, status) {
        processForm.action = `/admin/material-requests/${id}`;
        modalStatusInput.value = status;
        modalPartNameDisplay.innerText = partName;
        
        if (status === 'approved') {
            modalHeader.className = 'bg-success p-6 text-white';
            modalIcon.innerText = 'check_circle';
            modalTitle.innerText = 'Phê Duyệt Yêu Cầu';
            modalSubmitBtn.className = 'flex-2 px-8 py-3 bg-success hover:bg-emerald-500 text-white font-bold rounded-2xl shadow-xl shadow-emerald-500/20 active:scale-95 transition-all';
            modalSubmitBtn.innerText = 'Phê Duyệt';
            adminNoteArea.placeholder = 'Nhập lời nhắn cho nhân viên (vd: Đã duyệt, hãy liên hệ kho)...';
            adminNoteArea.required = false;
        } else {
            modalHeader.className = 'bg-danger p-6 text-white';
            modalIcon.innerText = 'cancel';
            modalTitle.innerText = 'Từ Chối Yêu Cầu';
            modalSubmitBtn.className = 'flex-2 px-8 py-3 bg-danger hover:bg-red-600 text-white font-bold rounded-2xl shadow-xl shadow-red-500/20 active:scale-95 transition-all';
            modalSubmitBtn.innerText = 'Từ Chối';
            adminNoteArea.placeholder = 'Nhập lý do từ chối cụ thể để nhân viên nắm rõ...';
            adminNoteArea.required = true;
        }
        
        processModal.showModal();
    }
</script>
@endsection
