@extends('layouts.staff')

@section('title', 'Hỗ Trợ Khách Hàng')

@section('main_class', 'flex flex-1 h-screen overflow-hidden')

@section('content')
<div class="flex w-full h-full bg-slate-50 relative">
    <!-- Sidebar: Session List -->
    <div class="w-80 bg-white border-r border-slate-200 flex flex-col shadow-[2px_0_10px_rgba(0,0,0,0.02)] z-20">
        <div class="p-5 border-b border-slate-100 flex flex-col bg-gradient-to-r from-blue-600 to-indigo-600 text-white relative overflow-hidden">
            <div class="absolute -right-4 -top-4 w-20 h-20 bg-white opacity-10 rounded-full blur-2xl"></div>
            <div class="flex items-center justify-between z-10 mb-4">
                <h2 class="font-bold text-lg"><i class="fas fa-headset mr-2"></i> Trực Tuyến</h2>
                <span class="bg-white/20 px-2.5 py-1 rounded text-xs font-bold border border-white/10" id="session-count">{{ $sessions->count() }}</span>
            </div>
            
            <div class="flex bg-white/10 p-1 rounded-lg z-10 shadow-inner">
                <button onclick="switchMode('customer')" id="tab-customer" class="flex-1 py-1.5 text-sm font-bold rounded-md bg-white text-blue-600 transition shadow">Khách Hàng</button>
                <button onclick="switchMode('internal')" id="tab-internal" class="flex-1 py-1.5 text-sm font-bold rounded-md text-white hover:bg-white/20 transition">Nội Bộ</button>
            </div>
        </div>

        <!-- Customer View -->
        <div id="customer-view" class="flex-1 flex flex-col overflow-hidden">
            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200">
                <div class="relative">
                    <input type="text" id="customerSearchInput" onkeyup="searchCustomers(this.value)" placeholder="Tìm khách hàng (Số ĐT...)" 
                        class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition placeholder:text-slate-400">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px]"></i>
                </div>
                <div id="searchResults" class="hidden mt-2 bg-white border border-slate-200 rounded-lg shadow-lg absolute left-4 right-4 z-30 max-h-60 overflow-y-auto custom-scrollbar">
                    <!-- Search results injected here -->
                </div>
            </div>
            <div class="flex-1 overflow-y-auto custom-scrollbar bg-slate-50/50" id="session-list">
                <!-- Sessions injected via JS -->
            </div>
        </div>

        <!-- Internal View -->
        <div id="internal-view" class="hidden flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 overflow-y-auto custom-scrollbar bg-slate-50/50" id="contacts-list">
                <!-- Contacts injected via JS -->
            </div>
        </div>

    </div>

    <!-- Main Chat Area -->
    <div class="flex-1 flex flex-col bg-slate-50 relative" id="chatArea">
        <div class="p-5 bg-white border-b border-slate-200 flex justify-between items-center shadow-sm z-10 h-[73px]">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg hidden shadow-inner" id="chat-avatar">
                   <i class="fas fa-user"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-lg transition" id="currentInfo">Chọn một đoạn chat để bắt đầu</h3>
                    <div class="flex items-center gap-1.5 hidden mt-0.5" id="currentStatus">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        <p class="text-[11px] text-green-600 font-bold uppercase tracking-wider">Đang hoạt động</p>
                    </div>
                </div>
            </div>
            <div>
               <!-- Header controls if needed -->
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-4 custom-scrollbar bg-slate-50 relative" id="messagesContainer">
            <!-- decorative background pattern -->
            <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#3b82f6 1px, transparent 1px); background-size: 20px 20px;"></div>
            
            <div class="flex flex-col items-center justify-center h-full text-slate-400 relative z-10">
                <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mb-4 shadow-inner">
                    <i class="fas fa-comments text-4xl text-blue-200"></i>
                </div>
                <p class="font-medium text-slate-500">Chưa có cuộc trò chuyện nào được chọn</p>
                <p class="text-xs mt-2 opacity-70">Chọn khách hàng từ danh sách bên trái để phản hồi</p>
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
                
                <div class="flex-1 bg-slate-100 rounded-xl px-4 py-2 flex items-center border border-slate-200 focus-within:border-blue-400 focus-within:ring-4 focus-within:ring-blue-500/10 transition pb-0">
                    <textarea id="replyInput" rows="1" placeholder="Nhập tin nhắn... (Nhấn Enter để gửi)" class="bg-transparent border-none outline-none w-full text-slate-700 placeholder-slate-400 resize-none py-1.5 custom-scrollbar" disabled onkeydown="if(event.key==='Enter' && !event.shiftKey) { event.preventDefault(); handleReplySubmit(event); }"></textarea>
                </div>
                <button type="submit" id="sendBtn" class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white w-12 h-12 rounded-xl flex items-center justify-center font-bold hover:shadow-lg hover:shadow-blue-500/30 disabled:opacity-50 transition transform hover:scale-105 flex-shrink-0" disabled>
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let sessions = @json($sessions);
    let activeSessionId = null;
    
    // Internal chat state
    let chatMode = 'customer'; // 'customer' or 'internal'
    let contacts = [];
    let activeContactId = null;
    let activeContactName = '';
    
    let pollInterval = null;

    function handleReplySubmit(e) {
        if(e) e.preventDefault();
        if (chatMode === 'customer') sendStaffReply(e);
        else sendInternalReply(e);
    }

    function getEmptyState() {
        return `
            <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#3b82f6 1px, transparent 1px); background-size: 20px 20px;"></div>
            <div class="flex flex-col items-center justify-center h-full text-slate-400 relative z-10">
                <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mb-4 shadow-inner">
                    <i class="fas fa-comments text-4xl text-blue-200"></i>
                </div>
                <p class="font-medium text-slate-500">Chưa có cuộc trò chuyện nào được chọn</p>
                <p class="text-xs mt-2 opacity-70">Chọn hội thoại từ danh sách bên trái để phản hồi</p>
            </div>
        `;
    }

    function switchMode(mode) {
        chatMode = mode;
        const tabC = document.getElementById('tab-customer');
        const tabI = document.getElementById('tab-internal');
        const viewC = document.getElementById('customer-view');
        const viewI = document.getElementById('internal-view');

        document.getElementById('currentInfo').innerText = 'Chọn một đoạn chat để bắt đầu';
        document.getElementById('chat-avatar').classList.add('hidden');
        document.getElementById('currentStatus').classList.add('hidden');
        document.getElementById('messagesContainer').innerHTML = getEmptyState();
        document.getElementById('replyInput').disabled = true;
        document.getElementById('sendBtn').disabled = true;
        document.getElementById('attachBtn').disabled = true;
        
        if (mode === 'customer') {
            tabC.className = "flex-1 py-1.5 text-sm font-bold rounded-md bg-white text-blue-600 transition shadow";
            tabI.className = "flex-1 py-1.5 text-sm font-bold rounded-md text-white hover:bg-white/20 transition";
            viewC.classList.remove('hidden');
            viewC.classList.add('flex');
            viewI.classList.add('hidden');
            viewI.classList.remove('flex');
            document.getElementById('session-count').innerText = sessions.length;
            
            if(activeSessionId) loadSession(activeSessionId);
        } else {
            tabI.className = "flex-1 py-1.5 text-sm font-bold rounded-md bg-white text-blue-600 transition shadow";
            tabC.className = "flex-1 py-1.5 text-sm font-bold rounded-md text-white hover:bg-white/20 transition";
            viewI.classList.remove('hidden');
            viewI.classList.add('flex');
            viewC.classList.add('hidden');
            viewC.classList.remove('flex');
            document.getElementById('session-count').innerText = contacts.length;
            
            if(contacts.length === 0) fetchContacts();
            else if(activeContactId) loadContact(activeContactId, activeContactName);
        }
    }

    function playPing() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(600, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(1000, ctx.currentTime + 0.1);
            gain.gain.setValueAtTime(0, ctx.currentTime);
            gain.gain.linearRampToValueAtTime(0.3, ctx.currentTime + 0.05);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
            osc.start();
            osc.stop(ctx.currentTime + 0.3);
        } catch(e) {}
    }

    function renderSessionList() {
        const list = document.getElementById('session-list');
        document.getElementById('session-count').innerText = sessions.length;
        
        if (sessions.length === 0) {
            list.innerHTML = `<div class="p-8 text-center text-slate-400 flex flex-col items-center"><i class="fas fa-inbox text-3xl mb-3 text-slate-300"></i><span class="text-sm">Không có yêu cầu nào.</span></div>`;
            return;
        }
        
        list.innerHTML = '';
        sessions.forEach(session => {
            const name = session.customer ? session.customer.name : 'Khách #' + session.guest_session_id.slice(-4);
            const lastMsg = session.messages.length > 0 ? session.messages[session.messages.length - 1] : {message: 'Đã bắt đầu kết nối', is_staff: true};
            
            // Highlight logic
            let hasNew = false;
            // If the last message is from guest, and we're not currently viewing it, highlight it
            if (!lastMsg.is_staff && session.id !== activeSessionId) {
                hasNew = true;
            }

            const bgClass = activeSessionId === session.id ? 'bg-blue-50 border-l-4 border-l-blue-600' : 'bg-white border-b border-slate-100 hover:bg-slate-50 border-l-4 border-l-transparent';
            
            list.innerHTML += `
                <div onclick="loadSession(${session.id})" class="p-4 cursor-pointer transition ${bgClass} group">
                    <div class="flex justify-between items-start mb-1.5">
                        <span class="font-bold text-[13px] ${hasNew ? 'text-blue-700' : 'text-slate-700'} group-hover:text-blue-600 transition truncate pr-2">
                            ${name}
                        </span>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            ${hasNew ? '<span class="w-2 h-2 bg-red-500 rounded-full shadow-[0_0_5px_#ef4444] animate-pulse"></span>' : ''}
                            <span class="text-[9px] uppercase font-bold px-1.5 py-0.5 rounded ${session.status === 'open' ? 'bg-green-100 text-green-700' : 'bg-slate-200 text-slate-500'}">
                                ${session.status}
                            </span>
                        </div>
                    </div>
                    <p class="text-xs ${hasNew ? 'font-semibold text-slate-800' : 'text-slate-500'} truncate">
                        ${!lastMsg.is_staff ? '<span class="text-blue-500 mr-1"><i class="fas fa-user-circle"></i> KH:</span>' : '<span class="text-slate-400 mr-1"><i class="fas fa-headset"></i> Bạn:</span>'} ${lastMsg.message}
                    </p>
                </div>
            `;
        });
    }

    function renderActiveChat() {
        if (!activeSessionId) return;
        const session = sessions.find(s => s.id === activeSessionId);
        if(!session) return;

        const container = document.getElementById('messagesContainer');
        const wasScrolledToBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 50;

        // Render header
        const name = session.customer ? session.customer.name : 'Khách #' + session.guest_session_id.slice(-4);
        document.getElementById('currentInfo').innerText = name;
        document.getElementById('chat-avatar').classList.remove('hidden');
        document.getElementById('currentStatus').classList.remove('hidden');

        // Render messages
        container.innerHTML = `<div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#3b82f6 1px, transparent 1px); background-size: 20px 20px;"></div>`;
        
        session.messages.forEach(msg => {
            const isMe = msg.is_staff;
            const div = document.createElement('div');
            div.className = isMe ? 'flex justify-end relative z-10 animate-fade-in mb-3' : 'flex justify-start relative z-10 animate-fade-in mb-3';
            
            let msgContent = msg.message;
            if (msg.attachment_path) {
                msgContent += `<div class="mt-2"><img src="${msg.attachment_path}" class="max-w-full rounded-lg cursor-pointer hover:opacity-90 transition border border-slate-200 shadow-sm" onclick="window.open('${msg.attachment_path}', '_blank')"></div>`;
            }

            div.innerHTML = `
                <div class="${isMe ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-tr-none' : 'bg-white text-slate-800 border border-slate-200 rounded-tl-none'} px-4 py-2.5 rounded-2xl max-w-[75%] text-[13px] shadow-sm">
                    ${msgContent}
                </div>
            `;
            container.appendChild(div);
        });

        if (wasScrolledToBottom || session.messages.length > 0) {
            container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
        }
    }

    function loadSession(id) {
        activeSessionId = id;
        document.getElementById('currentSessionId').value = id;
        document.getElementById('replyInput').disabled = false;
        document.getElementById('sendBtn').disabled = false;
        document.getElementById('attachBtn').disabled = false;
        
        const replyInput = document.getElementById('replyInput');
        replyInput.focus();

        renderSessionList(); // Re-render to clear red dot
        renderActiveChat();  // Render chat
    }

    function previewStaffImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('staff-preview-img').src = e.target.result;
                document.getElementById('staff-image-preview').classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function clearStaffImage() {
        document.getElementById('staff-image-input').value = "";
        document.getElementById('staff-image-preview').classList.add('hidden');
        document.getElementById('staff-preview-img').src = "";
    }

    function sendStaffReply(e) {
        if(e) e.preventDefault();
        const input = document.getElementById('replyInput');
        const fileInput = document.getElementById('staff-image-input');
        const btn = document.getElementById('sendBtn');
        const msg = input.value.trim();
        const file = fileInput.files[0];

        if((!msg && !file) || !activeSessionId) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        // Optimistic rendering
        const container = document.getElementById('messagesContainer');
        const div = document.createElement('div');
        div.className = 'flex justify-end relative z-10 animate-fade-in mb-3';
        
        let optimisticContent = msg || '[Hình ảnh]';
        div.innerHTML = `<div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 py-2.5 rounded-2xl max-w-[75%] text-[13px] rounded-tr-none shadow-sm opacity-70 border border-blue-400">
            ${optimisticContent} <i class="fas fa-spinner fa-spin text-[10px] ml-2"></i>
        </div>`;
        container.appendChild(div);
        container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
        
        input.value = '';
        clearStaffImage();

        const formData = new FormData();
        formData.append('chat_session_id', activeSessionId);
        formData.append('message', msg);
        if (file) formData.append('image', file);

        fetch('{{ route("staff.chat.reply") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        })
        .then(res => res.json())
        .then(data => fetchSessions())
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        });
    }

    // INTERNAL CHAT logic
    function fetchContacts() {
        return fetch('{{ route("staff.internal_chat.contacts") }}')
            .then(res => res.json())
            .then(data => {
                let shouldPing = false;
                // Optional: detect unreads
                data.contacts.forEach(c => {
                    const oldC = contacts.find(old => old.id === c.id);
                    if(!oldC && c.unreads) shouldPing = true;
                    if(oldC && !oldC.unreads && c.unreads) shouldPing = true;
                });
                
                if (shouldPing && chatMode === 'internal') playPing();
                
                contacts = data.contacts;
                if(chatMode === 'internal') {
                    document.getElementById('session-count').innerText = contacts.length;
                    renderContactList();
                    if(activeContactId) fetchInternalMessages(); // refresh active view
                }
            });
    }

    function renderContactList() {
        const list = document.getElementById('contacts-list');
        if (contacts.length === 0) {
            list.innerHTML = `<div class="p-8 text-center text-slate-400 flex flex-col items-center"><i class="fas fa-users text-3xl mb-3 text-slate-300"></i><span class="text-sm">Không có liên hệ.</span></div>`;
            return;
        }
        
        list.innerHTML = '';
        contacts.forEach(user => {
            const hasNew = user.unreads;
            const bgClass = activeContactId === user.id ? 'bg-blue-50 border-l-4 border-l-blue-600' : 'bg-white border-b border-slate-100 hover:bg-slate-50 border-l-4 border-l-transparent';
            
            list.innerHTML += `
                <div onclick="loadContact(${user.id}, '${user.name.replace(/'/g, "\\'")}')" class="p-4 cursor-pointer transition ${bgClass} group">
                    <div class="flex justify-between items-start mb-1.5">
                        <span class="font-bold text-[13px] ${hasNew ? 'text-blue-700' : 'text-slate-700'} group-hover:text-blue-600 transition truncate pr-2">
                            ${user.name}
                        </span>
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            ${hasNew ? '<span class="w-2 h-2 bg-red-500 rounded-full shadow-[0_0_5px_#ef4444] animate-pulse"></span>' : ''}
                            <span class="text-[9px] uppercase font-bold px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700">
                                ${user.role}
                            </span>
                        </div>
                    </div>
                    <p class="text-xs ${hasNew ? 'font-semibold text-slate-800' : 'text-slate-500'} truncate">
                        ${user.last_message ? user.last_message.message : 'Chưa có tin nhắn'}
                    </p>
                </div>
            `;
        });
    }

    function loadContact(id, name) {
        activeContactId = id;
        activeContactName = name;
        
        document.getElementById('currentInfo').innerText = name;
        document.getElementById('chat-avatar').classList.remove('hidden');
        document.getElementById('currentStatus').classList.remove('hidden');
        
        document.getElementById('replyInput').disabled = false;
        document.getElementById('sendBtn').disabled = false;
        
        // Disable attach for now in internal chat
        document.getElementById('attachBtn').disabled = true; 
        
        document.getElementById('replyInput').focus();
        
        renderContactList();
        fetchInternalMessages();
    }

    function fetchInternalMessages() {
        if (!activeContactId) return;
        fetch(`{{ route('staff.internal_chat.messages') }}?user_id=${activeContactId}`)
            .then(res => res.json())
            .then(data => {
                renderInternalChat(data.messages);
            });
    }

    function renderInternalChat(messages) {
        const container = document.getElementById('messagesContainer');
        const wasScrolledToBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 50;

        container.innerHTML = `<div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: radial-gradient(#3b82f6 1px, transparent 1px); background-size: 20px 20px;"></div>`;
        
        messages.forEach(msg => {
            const isMe = msg.sender_id === {{ auth()->id() ?? '0' }};
            const div = document.createElement('div');
            div.className = isMe ? 'flex justify-end relative z-10 animate-fade-in mb-3' : 'flex justify-start relative z-10 animate-fade-in mb-3';
            
            div.innerHTML = `
                <div class="${isMe ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-tr-none' : 'bg-white text-slate-800 border border-slate-200 rounded-tl-none'} px-4 py-2.5 rounded-2xl max-w-[75%] text-[13px] shadow-sm">
                    ${msg.message}
                </div>
            `;
            container.appendChild(div);
        });

        if (wasScrolledToBottom || messages.length > 0) {
            container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
        }
    }

    function sendInternalReply(e) {
        if(e) e.preventDefault();
        const input = document.getElementById('replyInput');
        const btn = document.getElementById('sendBtn');
        const msg = input.value.trim();

        if(!msg || !activeContactId) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        // Optimistic rendering
        const container = document.getElementById('messagesContainer');
        const div = document.createElement('div');
        div.className = 'flex justify-end relative z-10 animate-fade-in mb-3';
        div.innerHTML = `<div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 py-2.5 rounded-2xl max-w-[75%] text-[13px] rounded-tr-none shadow-sm opacity-70 border border-blue-400">
            ${msg} <i class="fas fa-spinner fa-spin text-[10px] ml-2"></i>
        </div>`;
        container.appendChild(div);
        container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' });
        
        input.value = '';

        fetch('{{ route("staff.internal_chat.send") }}', {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                receiver_id: activeContactId,
                message: msg
            })
        })
        .then(res => res.json())
        .then(data => fetchContacts())
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        });
    }

    // End INTERNAL CHAT logic


    // Polling Logic
    function pollData() {
        // Only fetch Customer sessions to count unreads etc.
        fetch('{{ route("staff.chat.sessions") }}')
            .then(res => res.json())
            .then(data => {
                let shouldPing = false;
                data.sessions.forEach(newSession => {
                    const oldSession = sessions.find(s => s.id === newSession.id);
                    if (oldSession) {
                        const newCount = newSession.messages.length;
                        const oldCount = oldSession.messages.length;
                        if (newCount > oldCount && !newSession.messages[newCount - 1].is_staff) {
                            shouldPing = true;
                        }
                    } else if (newSession.messages.length > 0 && !newSession.messages[newSession.messages.length - 1].is_staff) {
                        shouldPing = true;
                    }
                });

                if (shouldPing && chatMode === 'customer') playPing();
                sessions = data.sessions;
                
                if (chatMode === 'customer') {
                    renderSessionList();
                    if (activeSessionId) renderActiveChat();
                }
            });

        // Also fetch Internal Contacts
        fetchContacts();
    }

    function fetchSessions() {
        pollData(); // Force an immediate poll update
    }

    function startPolling() {
        if (!pollInterval) {
            pollInterval = setInterval(pollData, 3000);
        }
    }

    // New functions for customer search and session initiation
    let searchTimeout = null;
    function searchCustomers(query) {
        if (searchTimeout) clearTimeout(searchTimeout);
        const resultsDiv = document.getElementById('searchResults');
        
        if (query.length < 2) {
            resultsDiv.classList.add('hidden');
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`{{ route('staff.chat.search') }}?query=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.customers.length === 0) {
                        resultsDiv.innerHTML = '<div class="p-3 text-xs text-slate-500 italic">Không tìm thấy khách hàng.</div>';
                    } else {
                        resultsDiv.innerHTML = '';
                        data.customers.forEach(user => {
                            resultsDiv.innerHTML += `
                                <div onclick="startNewChat(${user.id})" class="p-3 hover:bg-slate-50 cursor-pointer border-b border-slate-100 last:border-0">
                                    <div class="font-bold text-[13px] text-slate-700">${user.name}</div>
                                    <div class="text-[11px] text-slate-500">${user.phone || 'N/A'}</div>
                                </div>
                            `;
                        } );
                    }
                    resultsDiv.classList.remove('hidden');
                });
        }, 300);
    }

    function startNewChat(customerId) {
        document.getElementById('searchResults').classList.add('hidden');
        document.getElementById('customerSearchInput').value = '';
        
        fetch('{{ route('staff.chat.start') }}', {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ customer_id: customerId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Fetch all sessions to include the new one
                fetchSessions();
                // Select the new session
                setTimeout(() => loadSession(data.session_id), 500);
            }
        });
    }

    // Initialize
    renderSessionList();
    startPolling();

    // Close search results when clicking outside
    document.addEventListener('click', e => {
        if (!e.target.closest('#searchResults') && !e.target.closest('#customerSearchInput')) {
            document.getElementById('searchResults').classList.add('hidden');
        }
    });
</script>
@endpush
