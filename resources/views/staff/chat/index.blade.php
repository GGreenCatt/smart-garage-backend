@extends('layouts.staff')

@section('title', 'Hỗ Trợ Khách Hàng')

@section('main_class', 'flex flex-1 h-screen overflow-hidden')

@section('content')
<div class="flex w-full h-full bg-slate-50 relative">
    <!-- Sidebar: Session List -->
    <div class="w-80 bg-white border-r border-slate-200 flex flex-col shadow-[2px_0_10px_rgba(0,0,0,0.02)] z-20">
        <div class="p-5 border-b border-slate-100 flex flex-col bg-gradient-to-r from-slate-800 to-slate-900 text-white relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-white opacity-10 rounded-full blur-2xl"></div>
            <div class="flex items-center justify-between z-10 mb-4">
                <h2 class="font-bold text-lg"><i class="fas fa-comments mr-2"></i> Tin Nhắn</h2>
            </div>
            
            <div class="flex bg-white/10 p-1 rounded-lg z-10 shadow-inner">
                <button onclick="switchMode('customer')" id="tab-customer" class="flex-1 py-1.5 text-xs font-bold rounded-md bg-white text-slate-800 transition shadow">CÔNG VIỆC</button>
                <button onclick="switchMode('internal')" id="tab-internal" class="flex-1 py-1.5 text-xs font-bold rounded-md text-white hover:bg-white/20 transition">PHÒNG CHUNG</button>
            </div>
        </div>

        <!-- Customer/Job View -->
        <div id="customer-view" class="flex-1 flex flex-col overflow-hidden">
            <div class="px-4 py-2 bg-slate-50 border-b border-slate-200">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Đang sửa chữa</p>
            </div>
            <div class="flex-1 overflow-y-auto custom-scrollbar bg-slate-50/50" id="session-list">
                <!-- Sessions injected via JS -->
            </div>
        </div>

        <!-- Internal View -->
        <div id="internal-view" class="hidden flex-1 flex flex-col overflow-hidden">
            <div class="px-4 py-2 bg-slate-50 border-b border-slate-200">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Nội bộ Garage</p>
            </div>
            <div class="flex-1 overflow-y-auto custom-scrollbar bg-slate-50/50" id="contacts-list">
                <!-- Single Group Item injected via JS -->
            </div>
        </div>

    </div>

    <!-- Main Chat Area -->
    <div class="flex-1 flex flex-col bg-slate-50 relative" id="chatArea">
        <div class="p-5 bg-white border-b border-slate-200 flex justify-between items-center shadow-sm z-10 h-[73px]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center font-bold text-lg hidden shadow-inner border border-slate-200" id="chat-avatar">
                   <i class="fas fa-user"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-lg transition flex items-center gap-2" id="currentInfo">Chọn một đoạn chat để bắt đầu</h3>
                    <div class="flex items-center gap-2 hidden mt-0.5" id="currentStatus">
                        <div class="flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                            <p class="text-[10px] text-green-600 font-bold uppercase tracking-wider">Trực tuyến</p>
                        </div>
                        <span class="text-slate-300 text-xs">|</span>
                        <div class="flex items-center gap-1 text-slate-500 text-xs font-medium">
                            <i class="fas fa-phone-alt text-[10px]"></i> <span id="customerPhone"></span>
                        </div>
                        <span class="text-slate-300 text-xs">|</span>
                        <div class="flex items-center gap-1 text-slate-500 text-xs font-medium">
                            <i class="fas fa-car text-[10px]"></i> <span id="customerVehicle" class="bg-slate-100 px-1 rounded font-mono"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div id="job-info-badge" class="hidden">
                <a href="#" id="order-link" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 px-3 py-1.5 rounded-lg text-xs font-bold border border-indigo-200 flex items-center gap-2 transition" title="Đi tới Tiến độ sửa chữa">
                    <span id="order-id-label"></span>
                    <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar bg-slate-50 relative" id="messagesContainer">
            <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#3b82f6 1px, transparent 1px); background-size: 20px 20px;"></div>
            
            <div class="flex flex-col items-center justify-center h-full text-slate-400 relative z-10">
                <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-4 shadow-inner">
                    <i class="fas fa-message text-4xl text-slate-300"></i>
                </div>
                <p class="font-medium text-slate-500 text-sm">Chưa có nội dung hội thoại</p>
            </div>
        </div>

        <div class="p-4 bg-white border-t border-slate-200 shadow-[0_-4px_10px_-1px_rgba(0,0,0,0.03)] z-10">
            <!-- Image Preview Area -->
            <div id="staff-image-preview" class="hidden mb-3 relative max-w-[150px] ml-12">
                <img src="" id="staff-preview-img" class="w-full h-auto rounded-lg border border-slate-300 shadow-sm" alt="Preview">
                <button onclick="clearStaffImage()" class="absolute -top-2 -right-2 w-6 h-6 bg-white text-slate-800 rounded-full flex items-center justify-center hover:bg-slate-100 hover:text-red-500 transition shadow border border-slate-200">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>

            <form onsubmit="handleReplySubmit(event)" class="flex gap-3 relative max-w-5xl mx-auto">
                <input type="hidden" id="currentSessionId">
                <input type="file" id="staff-image-input" accept="image/*" class="hidden" onchange="previewStaffImage(this)">
                
                <button type="button" onclick="document.getElementById('staff-image-input').click()" class="bg-slate-100 text-slate-500 hover:text-blue-600 hover:bg-blue-50 w-12 h-12 rounded-xl flex items-center justify-center font-bold transition transform flex-shrink-0 border border-slate-200" title="Đính kèm ảnh" id="attachBtn" disabled>
                    <i class="fas fa-paperclip"></i>
                </button>
                
                <div class="flex-1 bg-slate-100 rounded-xl px-4 py-2 flex items-center border border-slate-200 focus-within:border-slate-400 focus-within:ring-4 focus-within:ring-slate-500/5 transition">
                    <textarea id="replyInput" rows="1" placeholder="Nhập tin nhắn..." class="bg-transparent border-none outline-none w-full text-slate-700 placeholder-slate-400 resize-none py-1.5 custom-scrollbar" disabled onkeydown="if(event.key==='Enter' && !event.shiftKey) { event.preventDefault(); handleReplySubmit(event); }"></textarea>
                </div>
                <button type="submit" id="sendBtn" class="bg-slate-800 text-white w-12 h-12 rounded-xl flex items-center justify-center font-bold hover:bg-black disabled:opacity-50 transition transform hover:scale-105 flex-shrink-0" disabled>
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let sessions = [];
    let activeSessionId = null;
    let chatMode = 'customer'; 
    let internalMessages = [];
    let pollInterval = null;

    function handleReplySubmit(e) {
        if(e) e.preventDefault();
        if (chatMode === 'customer') sendStaffReply(e);
        else sendInternalReply(e);
    }

    function switchMode(mode) {
        chatMode = mode;
        const tabC = document.getElementById('tab-customer');
        const tabI = document.getElementById('tab-internal');
        const viewC = document.getElementById('customer-view');
        const viewI = document.getElementById('internal-view');
        const badge = document.getElementById('job-info-badge');

        // Reset view
        document.getElementById('currentInfo').innerText = 'Chọn một đoạn chat để bắt đầu';
        document.getElementById('chat-avatar').classList.add('hidden');
        document.getElementById('currentStatus').classList.add('hidden');
        badge.classList.add('hidden');
        document.getElementById('messagesContainer').innerHTML = `<div class="flex flex-col items-center justify-center h-full text-slate-400 relative z-10"><div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mb-4 shadow-inner"><i class="fas fa-message text-4xl text-slate-300"></i></div><p class="font-medium text-slate-500 text-sm">Chưa có nội dung hội thoại</p></div>`;
        document.getElementById('replyInput').disabled = true;
        document.getElementById('sendBtn').disabled = true;
        document.getElementById('attachBtn').disabled = true;
        
        if (mode === 'customer') {
            tabC.className = "flex-1 py-1.5 text-xs font-bold rounded-md bg-white text-slate-800 transition shadow";
            tabI.className = "flex-1 py-1.5 text-xs font-bold rounded-md text-white hover:bg-white/20 transition";
            viewC.classList.remove('hidden');
            viewI.classList.add('hidden');
            renderSessionList();
            if(activeSessionId) loadSession(activeSessionId);
        } else {
            tabI.className = "flex-1 py-1.5 text-xs font-bold rounded-md bg-white text-slate-800 transition shadow";
            tabC.className = "flex-1 py-1.5 text-xs font-bold rounded-md text-white hover:bg-white/20 transition";
            viewI.classList.remove('hidden');
            viewC.classList.add('hidden');
            renderInternalGroupItem();
            loadInternalGroup();
        }
    }

    function renderSessionList() {
        const list = document.getElementById('session-list');
        if (sessions.length === 0) {
            list.innerHTML = `<div class="p-8 text-center text-slate-400 flex flex-col items-center"><i class="fas fa-inbox text-3xl mb-3 text-slate-300"></i><span class="text-xs uppercase font-bold tracking-widest">Không có công việc</span></div>`;
            return;
        }
        
        list.innerHTML = '';
        sessions.forEach(session => {
            const customerName = session.customer ? session.customer.name : 'Khách vãng lai';
            const plate = session.repair_order && session.repair_order.vehicle ? session.repair_order.vehicle.plate_number : 'N/A';
            const lastMsg = session.messages.length > 0 ? session.messages[session.messages.length - 1].message : 'Bắt đầu chat...';
            
            const isActive = activeSessionId === session.id;
            const bgClass = isActive ? 'bg-blue-50 border-l-4 border-l-slate-800 shadow-sm' : 'bg-white border-b border-slate-100 hover:bg-slate-50 border-l-4 border-l-transparent';
            
            list.innerHTML += `
                <div onclick="loadSession(${session.id})" class="p-4 cursor-pointer transition ${bgClass} group">
                    <div class="flex justify-between items-start mb-1">
                        <span class="font-bold text-[13px] text-slate-800 truncate pr-2">${customerName}</span>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-600 uppercase">#${session.repair_order_id}</span>
                    </div>
                    <div class="flex items-center gap-1.5 mb-2">
                        <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-1.5 rounded">${plate}</span>
                    </div>
                    <p class="text-[11px] text-slate-500 truncate italic">"${lastMsg}"</p>
                </div>
            `;
        });
    }

    function loadSession(id) {
        activeSessionId = id;
        const session = sessions.find(s => s.id === id);
        if(!session) return;

        document.getElementById('replyInput').disabled = false;
        document.getElementById('sendBtn').disabled = false;
        document.getElementById('attachBtn').disabled = false;
        
        // Extract Info
        const customerName = session.customer ? session.customer.name : 'Khách hàng';
        const customerPhone = session.customer && session.customer.phone ? session.customer.phone : 'N/A';
        const plate = session.repair_order && session.repair_order.vehicle ? session.repair_order.vehicle.plate_number : '';
        const model = session.repair_order && session.repair_order.vehicle ? session.repair_order.vehicle.model : '';

        // Header info update
        document.getElementById('currentInfo').innerText = customerName;
        document.getElementById('customerPhone').innerText = customerPhone;
        document.getElementById('customerVehicle').innerText = plate ? `${plate} (${model})` : 'N/A';
        
        document.getElementById('chat-avatar').classList.remove('hidden');
        document.getElementById('currentStatus').classList.remove('hidden');
        
        const badge = document.getElementById('job-info-badge');
        badge.classList.remove('hidden');
        document.getElementById('order-id-label').innerText = 'Lệnh Sửa Chữa #' + session.repair_order_id;
        document.getElementById('order-link').href = `{{ route('staff.dashboard') }}?order_id=${session.repair_order_id}`;

        renderMessages(session.messages);
        renderSessionList();
    }

    function renderMessages(messages, isGroup = false) {
        const container = document.getElementById('messagesContainer');
        const wasScrolledToBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 100;

        container.innerHTML = `<div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#3b82f6 1px, transparent 1px); background-size: 20px 20px;"></div>`;
        
        messages.forEach(msg => {
            const isMe = isGroup ? (msg.sender_id === {{ auth()->id() ?? 0 }}) : msg.is_staff;
            const div = document.createElement('div');
            div.className = isMe ? 'flex justify-end mb-4' : 'flex justify-start mb-4';
            
            let nameTag = '';
            if (isGroup && !isMe && msg.sender) {
                nameTag = `<p class="text-[10px] font-bold text-slate-400 mb-1 ml-1">${msg.sender.name}</p>`;
            }

            let content = msg.message;
            if (msg.attachment_path) {
                content += `<div class="mt-2"><img src="${msg.attachment_path}" class="rounded-lg max-w-sm cursor-zoom-in" onclick="window.open('${msg.attachment_path}')"></div>`;
            }

            div.innerHTML = `
                <div class="max-w-[70%]">
                    ${nameTag}
                    <div class="${isMe ? 'bg-slate-800 text-white rounded-tr-none' : 'bg-white text-slate-800 border border-slate-200 rounded-tl-none'} px-4 py-2.5 rounded-2xl shadow-sm text-sm">
                        ${content}
                    </div>
                    <p class="text-[9px] text-slate-400 mt-1 ${isMe ? 'text-right' : 'text-left'} uppercase font-bold">${new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</p>
                </div>
            `;
            container.appendChild(div);
        });

        if (wasScrolledToBottom) container.scrollTop = container.scrollHeight;
    }

    function renderInternalGroupItem() {
        const list = document.getElementById('contacts-list');
        list.innerHTML = `
            <div onclick="loadInternalGroup()" class="p-5 cursor-pointer bg-blue-50 border-l-4 border-l-slate-800 shadow-sm transition">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-slate-800 text-white flex items-center justify-center shadow-lg transform rotate-3">
                        <i class="fas fa-users-viewfinder text-xl"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm">NHÓM CHUNG GARAGE</h4>
                        <p class="text-[10px] text-blue-600 font-bold uppercase tracking-wider">Tất cả nhân viên</p>
                    </div>
                </div>
            </div>
        `;
    }

    function loadInternalGroup() {
        document.getElementById('currentInfo').innerText = 'Nhóm Chung Garage';
        document.getElementById('chat-avatar').classList.remove('hidden');
        document.getElementById('currentStatus').classList.remove('hidden');
        document.getElementById('job-info-badge').classList.add('hidden');
        
        document.getElementById('replyInput').disabled = false;
        document.getElementById('sendBtn').disabled = false;
        document.getElementById('attachBtn').disabled = true; // No image for group yet

        fetchInternalMessages();
    }

    function fetchInternalMessages() {
        fetch('{{ route('staff.internal_chat.messages') }}')
            .then(res => res.json())
            .then(data => {
                internalMessages = data.messages;
                if(chatMode === 'internal') renderMessages(internalMessages, true);
            });
    }

    function sendStaffReply(e) {
        const input = document.getElementById('replyInput');
        const msg = input.value.trim();
        const file = document.getElementById('staff-image-input').files[0];
        if(!msg && !file) return;

        const formData = new FormData();
        formData.append('chat_session_id', activeSessionId);
        formData.append('message', msg);
        if(file) formData.append('image', file);

        input.value = '';
        clearStaffImage();

        fetch('{{ route("staff.chat.reply") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        }).then(() => fetchPollData());
    }

    function sendInternalReply(e) {
        const input = document.getElementById('replyInput');
        const msg = input.value.trim();
        if(!msg) return;

        input.value = '';

        fetch('{{ route("staff.internal_chat.send") }}', {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ message: msg })
        }).then(() => fetchPollData());
    }

    function fetchPollData() {
        fetch('{{ route("staff.chat.sessions") }}')
            .then(res => res.json())
            .then(data => {
                sessions = data.sessions;
                if (chatMode === 'customer') {
                    renderSessionList();
                    if(activeSessionId) {
                        const s = sessions.find(x => x.id === activeSessionId);
                        if(s) renderMessages(s.messages);
                    }
                }
            });
        
        fetchInternalMessages();
    }

    // Helper preview stuff
    function previewStaffImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('staff-preview-img').src = e.target.result;
                document.getElementById('staff-image-preview').classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    function clearStaffImage() {
        document.getElementById('staff-image-input').value = "";
        document.getElementById('staff-image-preview').classList.add('hidden');
    }

    // Start
    fetchPollData();
    setInterval(fetchPollData, 4000);
</script>
@endpush
