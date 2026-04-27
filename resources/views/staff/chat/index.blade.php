@extends('layouts.staff')

@section('title', 'Tin Nhắn')

@push('styles')
<style>
    @media (max-width: 767px) {
        .staff-chat-shell { height: calc(100vh - 4rem); min-height: 0 !important; padding-bottom: 4.5rem; }
        .staff-chat-sidebar { width: 100% !important; height: 42%; min-height: 270px; border-right: 0 !important; border-bottom: 1px solid rgba(148, 163, 184, .24); }
        .staff-chat-panel { min-height: 0; height: 58%; }
        .staff-chat-header { height: auto !important; min-height: 4rem; padding: .75rem 1rem !important; }
        .staff-chat-messages { padding: 1rem !important; }
        .staff-chat-composer { padding: .75rem !important; }
        .staff-chat-composer form { gap: .5rem !important; }
        #order-link { display: none !important; }
        #messagesContainer .max-w-\[72\%\] { max-width: 88% !important; }
    }
</style>
@endpush

@section('full-width-content')
<div class="staff-chat-shell flex h-[calc(100vh-5rem)] min-h-[620px] flex-col bg-slate-100 dark:bg-slate-950 md:flex-row">
    <aside class="staff-chat-sidebar flex w-[360px] shrink-0 flex-col border-r border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <div class="border-b border-slate-200 p-5 dark:border-slate-800">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-indigo-500 dark:text-indigo-400">Trung tâm hỗ trợ</p>
                    <h1 class="mt-1 text-2xl font-black text-slate-900 dark:text-white">Tin nhắn</h1>
                </div>
                <button type="button" onclick="fetchPollData()" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-slate-500 transition hover:border-indigo-300 hover:text-indigo-600 dark:border-slate-700 dark:text-slate-300 dark:hover:border-indigo-500" title="Làm mới">
                    <i class="fas fa-rotate-right"></i>
                </button>
            </div>

            <div class="mt-5 grid grid-cols-2 rounded-xl bg-slate-100 p-1 dark:bg-slate-950">
                <button type="button" onclick="switchMode('customer')" id="tab-customer" class="rounded-lg bg-white px-3 py-2 text-xs font-black text-slate-900 shadow-sm transition dark:bg-slate-800 dark:text-white">
                    Khách hàng
                </button>
                <button type="button" onclick="switchMode('internal')" id="tab-internal" class="rounded-lg px-3 py-2 text-xs font-black text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                    Nội bộ
                </button>
            </div>
        </div>

        <div id="customer-view" class="flex min-h-0 flex-1 flex-col">
            <div class="border-b border-slate-200 px-5 py-3 dark:border-slate-800">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input id="session-search" type="text" oninput="renderSessionList()" placeholder="Tìm tên, SĐT, biển số..." class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-9 pr-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:focus:border-indigo-500">
                </div>
            </div>
            <div class="flex-1 overflow-y-auto" id="session-list"></div>
        </div>

        <div id="internal-view" class="hidden min-h-0 flex-1 flex-col">
            <div class="border-b border-slate-200 px-5 py-3 text-xs font-black uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:text-slate-400">
                Kênh trao đổi nội bộ
            </div>
            <div class="flex-1 overflow-y-auto" id="contacts-list"></div>
        </div>
    </aside>

    <section class="staff-chat-panel flex min-w-0 flex-1 flex-col">
        <header class="staff-chat-header flex h-20 items-center justify-between border-b border-slate-200 bg-white px-6 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex min-w-0 items-center gap-4">
                <div id="chat-avatar" class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-lg font-black text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                    <i class="fas fa-user"></i>
                </div>
                <div class="min-w-0">
                    <h2 id="currentInfo" class="truncate text-lg font-black text-slate-900 dark:text-white">Chọn một cuộc trò chuyện</h2>
                    <div id="currentStatus" class="mt-1 hidden flex-wrap items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
                        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-300">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Đang mở
                        </span>
                        <span id="customerPhoneWrap" class="hidden items-center gap-1">
                            <i class="fas fa-phone text-[10px]"></i>
                            <span id="customerPhone"></span>
                        </span>
                        <span id="customerVehicleWrap" class="hidden items-center gap-1">
                            <i class="fas fa-car text-[10px]"></i>
                            <span id="customerVehicle" class="rounded bg-slate-100 px-1.5 py-0.5 font-mono dark:bg-slate-800"></span>
                        </span>
                    </div>
                </div>
            </div>

            <a href="#" id="order-link" class="hidden rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-black text-indigo-700 transition hover:bg-indigo-100 dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-300">
                <span id="order-id-label"></span>
                <i class="fas fa-arrow-right ml-2 text-xs"></i>
            </a>
        </header>

        <div class="staff-chat-messages flex-1 overflow-y-auto bg-slate-50 p-6 dark:bg-slate-950" id="messagesContainer">
            <div class="flex h-full flex-col items-center justify-center text-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white text-3xl text-slate-300 shadow-sm dark:bg-slate-900 dark:text-slate-700">
                    <i class="fas fa-comments"></i>
                </div>
                <h3 class="mt-4 text-lg font-black text-slate-800 dark:text-white">Chưa chọn hội thoại</h3>
                <p class="mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">Chọn một khách hàng hoặc kênh nội bộ để xem và phản hồi tin nhắn.</p>
            </div>
        </div>

        <footer class="staff-chat-composer border-t border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <div id="staff-image-preview" class="mx-auto mb-3 hidden max-w-5xl">
                <div class="relative w-36">
                    <img src="" id="staff-preview-img" class="max-h-28 rounded-xl border border-slate-200 object-cover dark:border-slate-700" alt="Ảnh đính kèm">
                    <button type="button" onclick="clearStaffImage()" class="absolute -right-2 -top-2 flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-xs text-white shadow hover:bg-red-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <form onsubmit="handleReplySubmit(event)" class="mx-auto flex max-w-5xl items-end gap-3">
                <input type="hidden" id="currentSessionId">
                <input type="file" id="staff-image-input" accept="image/*" class="hidden" onchange="previewStaffImage(this)">

                <button type="button" onclick="document.getElementById('staff-image-input').click()" id="attachBtn" disabled class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 transition hover:border-indigo-300 hover:text-indigo-600 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-300">
                    <i class="fas fa-paperclip"></i>
                </button>

                <textarea id="replyInput" rows="1" placeholder="Nhập tin nhắn..." disabled oninput="autoResizeReply()" onkeydown="if(event.key==='Enter' && !event.shiftKey) { event.preventDefault(); handleReplySubmit(event); }" class="max-h-32 min-h-12 flex-1 resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 outline-none transition focus:border-indigo-400 focus:bg-white disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:focus:border-indigo-500"></textarea>

                <button type="submit" id="sendBtn" disabled class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-40">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </form>
        </footer>
    </section>
</div>
@endsection

@push('scripts')
<script>
    let sessions = [];
    let activeSessionId = null;
    let chatMode = 'customer';
    let internalMessages = [];
    const currentUserId = {{ auth()->id() ?? 0 }};

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char]));
    }

    function escapeAttribute(value) {
        return escapeHtml(value).replace(/`/g, '&#096;');
    }

    function formatTime(value) {
        if (!value) return '';
        return new Date(value).toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit' });
    }

    function handleReplySubmit(e) {
        if (e) e.preventDefault();
        if (chatMode === 'customer') sendStaffReply();
        else sendInternalReply();
    }

    function setComposerEnabled(enabled, allowAttachment = true) {
        document.getElementById('replyInput').disabled = !enabled;
        document.getElementById('sendBtn').disabled = !enabled;
        document.getElementById('attachBtn').disabled = !enabled || !allowAttachment;
    }

    function resetConversation() {
        document.getElementById('currentInfo').innerText = 'Chọn một cuộc trò chuyện';
        document.getElementById('chat-avatar').classList.add('hidden');
        document.getElementById('currentStatus').classList.add('hidden');
        document.getElementById('order-link').classList.add('hidden');
        document.getElementById('messagesContainer').innerHTML = `
            <div class="flex h-full flex-col items-center justify-center text-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white text-3xl text-slate-300 shadow-sm dark:bg-slate-900 dark:text-slate-700"><i class="fas fa-comments"></i></div>
                <h3 class="mt-4 text-lg font-black text-slate-800 dark:text-white">Chưa chọn hội thoại</h3>
                <p class="mt-1 max-w-sm text-sm text-slate-500 dark:text-slate-400">Chọn một khách hàng hoặc kênh nội bộ để xem và phản hồi tin nhắn.</p>
            </div>`;
        setComposerEnabled(false);
    }

    function switchMode(mode) {
        chatMode = mode;
        activeSessionId = mode === 'customer' ? activeSessionId : null;

        document.getElementById('tab-customer').className = mode === 'customer'
            ? 'rounded-lg bg-white px-3 py-2 text-xs font-black text-slate-900 shadow-sm transition dark:bg-slate-800 dark:text-white'
            : 'rounded-lg px-3 py-2 text-xs font-black text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white';
        document.getElementById('tab-internal').className = mode === 'internal'
            ? 'rounded-lg bg-white px-3 py-2 text-xs font-black text-slate-900 shadow-sm transition dark:bg-slate-800 dark:text-white'
            : 'rounded-lg px-3 py-2 text-xs font-black text-slate-500 transition hover:text-slate-900 dark:text-slate-400 dark:hover:text-white';

        document.getElementById('customer-view').classList.toggle('hidden', mode !== 'customer');
        document.getElementById('internal-view').classList.toggle('hidden', mode !== 'internal');

        if (mode === 'customer') {
            resetConversation();
            renderSessionList();
        } else {
            renderInternalGroupItem();
            loadInternalGroup();
        }
    }

    function renderSessionList() {
        const list = document.getElementById('session-list');
        const keyword = (document.getElementById('session-search')?.value || '').toLowerCase().trim();
        const filtered = sessions.filter(session => {
            const customerName = session.customer?.name || 'Khách vãng lai';
            const phone = session.customer?.phone || '';
            const plate = session.repair_order?.vehicle?.license_plate || '';
            const model = session.repair_order?.vehicle?.model || '';
            return `${customerName} ${phone} ${plate} ${model}`.toLowerCase().includes(keyword);
        });

        if (filtered.length === 0) {
            list.innerHTML = `
                <div class="p-8 text-center text-slate-400">
                    <i class="fas fa-inbox mb-3 text-3xl text-slate-300 dark:text-slate-700"></i>
                    <p class="text-sm font-bold">Không có hội thoại phù hợp</p>
                </div>`;
            return;
        }

        list.innerHTML = filtered.map(session => {
            const customerName = session.customer?.name || 'Khách vãng lai';
            const phone = session.customer?.phone || 'Chưa có SĐT';
            const plate = session.repair_order?.vehicle?.license_plate || 'Chat chung';
            const model = session.repair_order?.vehicle?.model || '';
            const last = session.messages?.length ? session.messages[session.messages.length - 1] : null;
            const lastText = last?.attachment_path ? 'Đã gửi ảnh' : (last?.message || 'Chưa có tin nhắn');
            const isActive = activeSessionId === session.id;
            const typeLabel = session.repair_order_id ? `Lệnh #${session.repair_order_id}` : 'Hỗ trợ chung';

            return `
                <button type="button" onclick="loadSession(${session.id})" class="w-full border-b border-slate-100 px-5 py-4 text-left transition dark:border-slate-800 ${isActive ? 'bg-indigo-50 dark:bg-indigo-500/10' : 'hover:bg-slate-50 dark:hover:bg-slate-800/60'}">
                    <div class="flex items-start gap-3">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl ${session.repair_order_id ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'} text-sm font-black">
                            ${escapeHtml(customerName.substring(0, 1).toUpperCase())}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <div class="truncate text-sm font-black text-slate-900 dark:text-white">${escapeHtml(customerName)}</div>
                                <span class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-black text-slate-500 dark:bg-slate-800 dark:text-slate-400">${escapeHtml(typeLabel)}</span>
                            </div>
                            <div class="mt-1 flex items-center gap-2 text-xs font-bold text-slate-500 dark:text-slate-400">
                                <span class="truncate">${escapeHtml(phone)}</span>
                                <span>•</span>
                                <span class="truncate font-mono">${escapeHtml(plate)}</span>
                            </div>
                            ${model ? `<div class="mt-1 truncate text-xs text-slate-400">${escapeHtml(model)}</div>` : ''}
                            <p class="mt-2 truncate text-xs font-medium text-slate-500 dark:text-slate-400">${escapeHtml(lastText)}</p>
                        </div>
                    </div>
                </button>`;
        }).join('');
    }

    function loadSession(id) {
        activeSessionId = id;
        const session = sessions.find(s => s.id === id);
        if (!session) return;

        const customerName = session.customer?.name || 'Khách hàng';
        const customerPhone = session.customer?.phone || '';
        const plate = session.repair_order?.vehicle?.license_plate || '';
        const model = session.repair_order?.vehicle?.model || '';

        document.getElementById('currentInfo').innerText = customerName;
        document.getElementById('chat-avatar').classList.remove('hidden');
        document.getElementById('chat-avatar').classList.add('flex');
        document.getElementById('currentStatus').classList.remove('hidden');
        document.getElementById('currentStatus').classList.add('flex');

        document.getElementById('customerPhoneWrap').classList.toggle('hidden', !customerPhone);
        document.getElementById('customerPhoneWrap').classList.toggle('flex', !!customerPhone);
        document.getElementById('customerPhone').innerText = customerPhone;

        document.getElementById('customerVehicleWrap').classList.toggle('hidden', !plate);
        document.getElementById('customerVehicleWrap').classList.toggle('flex', !!plate);
        document.getElementById('customerVehicle').innerText = plate ? `${plate}${model ? ' - ' + model : ''}` : '';

        const orderLink = document.getElementById('order-link');
        if (session.repair_order_id) {
            orderLink.href = `/staff/order/${session.repair_order_id}`;
            document.getElementById('order-id-label').innerText = `Lệnh #${session.repair_order_id}`;
            orderLink.classList.remove('hidden');
        } else {
            orderLink.classList.add('hidden');
        }

        setComposerEnabled(true, true);
        renderMessages(session.messages || []);
        renderSessionList();
    }

    function renderMessages(messages, isGroup = false) {
        const container = document.getElementById('messagesContainer');
        const shouldStickBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 120;

        if (!messages.length) {
            container.innerHTML = `
                <div class="flex h-full flex-col items-center justify-center text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-2xl text-slate-300 shadow-sm dark:bg-slate-900 dark:text-slate-700"><i class="fas fa-message"></i></div>
                    <p class="mt-4 text-sm font-bold text-slate-500 dark:text-slate-400">Chưa có tin nhắn nào</p>
                </div>`;
            return;
        }

        container.innerHTML = messages.map(msg => {
            const isMe = isGroup ? Number(msg.sender_id) === currentUserId : Boolean(msg.is_staff);
            const senderName = isGroup && !isMe && msg.sender ? msg.sender.name : (isMe ? 'Bạn' : 'Khách hàng');
            const text = escapeHtml(msg.message || '').replace(/\n/g, '<br>');
            const image = msg.attachment_path
                ? `<button type="button" onclick="window.open('${escapeAttribute(msg.attachment_path)}', '_blank')" class="mt-2 block"><img src="${escapeAttribute(msg.attachment_path)}" class="max-h-64 max-w-full rounded-xl border ${isMe ? 'border-white/20' : 'border-slate-200 dark:border-slate-700'} object-contain" alt="Ảnh đính kèm"></button>`
                : '';

            return `
                <div class="mb-4 flex ${isMe ? 'justify-end' : 'justify-start'}">
                    <div class="max-w-[72%]">
                        ${!isMe ? `<div class="mb-1 ml-1 text-xs font-bold text-slate-400">${escapeHtml(senderName)}</div>` : ''}
                        <div class="rounded-2xl px-4 py-3 text-sm shadow-sm ${isMe ? 'rounded-tr-md bg-indigo-600 text-white' : 'rounded-tl-md border border-slate-200 bg-white text-slate-800 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100'}">
                            ${text || (image ? '' : '<span class="text-slate-400">Tin nhắn trống</span>')}
                            ${image}
                        </div>
                        <div class="mt-1 text-[10px] font-bold uppercase text-slate-400 ${isMe ? 'text-right' : 'text-left'}">${formatTime(msg.created_at)}</div>
                    </div>
                </div>`;
        }).join('');

        if (shouldStickBottom) container.scrollTop = container.scrollHeight;
    }

    function renderInternalGroupItem() {
        document.getElementById('contacts-list').innerHTML = `
            <button type="button" onclick="loadInternalGroup()" class="w-full border-b border-slate-100 bg-indigo-50 px-5 py-5 text-left transition hover:bg-indigo-100 dark:border-slate-800 dark:bg-indigo-500/10 dark:hover:bg-indigo-500/15">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-600 text-white">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-slate-900 dark:text-white">Nhóm chung garage</h3>
                        <p class="mt-1 text-xs font-bold text-slate-500 dark:text-slate-400">Trao đổi giữa nhân viên</p>
                    </div>
                </div>
            </button>`;
    }

    function loadInternalGroup() {
        document.getElementById('currentInfo').innerText = 'Nhóm chung garage';
        document.getElementById('chat-avatar').classList.remove('hidden');
        document.getElementById('chat-avatar').classList.add('flex');
        document.getElementById('currentStatus').classList.remove('hidden');
        document.getElementById('currentStatus').classList.add('flex');
        document.getElementById('customerPhoneWrap').classList.add('hidden');
        document.getElementById('customerVehicleWrap').classList.add('hidden');
        document.getElementById('order-link').classList.add('hidden');
        setComposerEnabled(true, false);
        fetchInternalMessages();
    }

    function fetchInternalMessages() {
        fetch('{{ route('staff.internal_chat.messages') }}')
            .then(res => res.json())
            .then(data => {
                internalMessages = data.messages || [];
                if (chatMode === 'internal') renderMessages(internalMessages, true);
            });
    }

    function sendStaffReply() {
        if (!activeSessionId) return;
        const input = document.getElementById('replyInput');
        const msg = input.value.trim();
        const file = document.getElementById('staff-image-input').files[0];
        if (!msg && !file) return;

        const formData = new FormData();
        formData.append('chat_session_id', activeSessionId);
        formData.append('message', msg);
        if (file) formData.append('image', file);

        input.value = '';
        autoResizeReply();
        clearStaffImage();

        fetch('{{ route("staff.chat.reply") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: formData
        }).then(() => fetchPollData());
    }

    function sendInternalReply() {
        const input = document.getElementById('replyInput');
        const msg = input.value.trim();
        if (!msg) return;

        input.value = '';
        autoResizeReply();

        fetch('{{ route("staff.internal_chat.send") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ message: msg })
        }).then(() => fetchInternalMessages());
    }

    function fetchPollData() {
        fetch('{{ route("staff.chat.sessions") }}')
            .then(res => res.json())
            .then(data => {
                sessions = data.sessions || [];
                if (chatMode === 'customer') {
                    renderSessionList();
                    if (activeSessionId) {
                        const session = sessions.find(item => item.id === activeSessionId);
                        if (session) renderMessages(session.messages || []);
                    }
                }
            });

        if (chatMode === 'internal') fetchInternalMessages();
    }

    function previewStaffImage(input) {
        if (!input.files || !input.files[0]) return;
        const reader = new FileReader();
        reader.onload = event => {
            document.getElementById('staff-preview-img').src = event.target.result;
            document.getElementById('staff-image-preview').classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }

    function clearStaffImage() {
        document.getElementById('staff-image-input').value = '';
        document.getElementById('staff-image-preview').classList.add('hidden');
        document.getElementById('staff-preview-img').src = '';
    }

    function autoResizeReply() {
        const input = document.getElementById('replyInput');
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 128) + 'px';
    }

    fetchPollData();
    setInterval(fetchPollData, 4000);
</script>
@endpush
