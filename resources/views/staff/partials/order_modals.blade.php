<!-- Add Item Modal -->
<div id="addItemModal" class="hidden fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm transition-opacity">
    <div onclick="event.stopPropagation()" class="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl p-6 shadow-2xl relative transform transition-all scale-100 mt-20">
        <button onclick="document.getElementById('addItemModal').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:text-slate-300 transition">
            <i class="fas fa-times text-xl"></i>
        </button>
        <h3 class="font-bold text-xl text-slate-800 dark:text-slate-100 mb-6 flex items-center gap-2">
            <i class="fas fa-plus-circle text-teal-500"></i> Thêm Vật Tư / Công Thợ
        </h3>
        
        <div class="space-y-5">
            <!-- Type Toggle -->
            <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-800/80 p-3 rounded-xl border border-slate-100 dark:border-slate-700">
                <span class="text-sm font-bold text-slate-700 dark:text-slate-200">Loại Vật Tư</span>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-slate-400" id="labelInternal">Trong Kho</span>
                    <button onclick="toggleItemType()" id="btnTypeToggle" class="w-12 h-6 bg-slate-300 rounded-full relative transition-colors duration-300">
                        <div class="absolute left-1 top-1 w-4 h-4 bg-white dark:bg-slate-800 rounded-full shadow-sm transition-transform duration-300" id="toggleCircle"></div>
                    </button>
                    <span class="text-xs font-bold text-slate-400" id="labelExternal">Mua Ngoài</span>
                </div>
            </div>
            
            <input type="hidden" id="isCustom" value="false">

            <!-- Internal Search -->
            <div id="internalSearchBlock">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2 tracking-wide">Tìm Kiếm Phụ Tùng</label>
                <div class="relative">
                     <i class="fas fa-search absolute left-4 top-3.5 text-slate-400"></i>
                     <input type="text" id="itemNameSearch" oninput="searchParts(this.value)" class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl focus:outline-none focus:border-teal-500 font-semibold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-800/80 focus:bg-white dark:bg-slate-800 transition" placeholder="Nhập tên phụ tùng hoặc mã...">
                     <div id="suggestions" class="hidden absolute top-full left-0 w-full bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-xl rounded-xl mt-2 max-h-60 overflow-y-auto z-20"></div>
                </div>
            </div>

            <!-- External Name Input (Hidden by default) -->
            <div id="externalNameBlock" class="hidden">
                <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2 tracking-wide">Tên Vật Tư / Công Việc</label>
                <input type="text" id="itemNameCustom" class="w-full px-4 py-3 border border-slate-200 rounded-xl font-semibold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-800/80 focus:bg-white dark:bg-slate-800 focus:border-teal-500 outline-none transition" placeholder="VD: Gương chiếu hậu phải Vios...">
            </div>
            
            <input type="hidden" id="itemSku">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2 tracking-wide">Số Lượng</label>
                    <input type="number" id="itemQty" value="1" min="1" oninput="calculatePrice()" class="w-full px-4 py-3 border border-slate-200 rounded-xl font-bold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-800/80 focus:bg-white dark:bg-slate-800 focus:border-teal-500 outline-none transition">
                </div>
                <!-- Logic for Price -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase mb-2 tracking-wide">Giá Bán (VNĐ)</label>
                    <input type="number" id="itemPrice" class="w-full px-4 py-3 border border-slate-200 rounded-xl font-bold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-800/80 focus:bg-white dark:bg-slate-800 focus:border-teal-500 outline-none transition">
                </div>
            </div>

            <!-- Cost & Fee for External (Hidden by default) -->
            <div id="externalCostBlock" class="hidden grid grid-cols-2 gap-4 bg-teal-50/50 p-4 rounded-xl border border-teal-100/50">
                <div>
                    <label class="block text-[10px] font-bold text-teal-600 uppercase mb-1 tracking-wide">Giá Nhập (Gốc)</label>
                    <input type="number" id="costPrice" oninput="calculatePrice()" class="w-full px-3 py-2 border-b border-teal-200 bg-transparent font-bold text-teal-800 text-sm focus:border-teal-500 outline-none placeholder-teal-300">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-teal-600 uppercase mb-1 tracking-wide">Phụ Phí (Lãi)</label>
                    <div class="relative">
                        <input type="number" id="serviceFee" value="50000" oninput="calculatePrice()" class="w-full px-3 py-2 border-b border-teal-200 bg-transparent font-bold text-teal-800 text-sm focus:border-teal-500 outline-none placeholder-teal-300">
                        <span class="absolute right-0 top-2 text-[10px] text-teal-500 font-bold">VNĐ</span>
                    </div>
                </div>
            </div>
            
            <button onclick="saveItem()" class="w-full bg-teal-500 text-white font-bold py-4 rounded-xl hover:bg-teal-600 transition shadow-lg shadow-teal-500/30 flex items-center justify-center gap-2 mt-2">
                <i class="fas fa-save"></i> Thêm Vào Job
            </button>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="hidden fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4 backdrop-blur-sm transition-opacity" data-total="0">
    <div onclick="event.stopPropagation()" class="bg-white dark:bg-slate-800 w-full max-w-lg rounded-2xl p-6 shadow-2xl relative transform transition-all scale-100 flex flex-col gap-6 mt-10">
        <button onclick="document.getElementById('paymentModal').classList.add('hidden')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:text-slate-300 transition">
            <i class="fas fa-times text-xl"></i>
        </button>
        <h3 class="font-black text-2xl text-slate-800 dark:text-slate-100 flex items-center gap-2">
            <i class="fas fa-wallet text-teal-500"></i> Thanh Toán
        </h3>

        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-xl p-4 border border-slate-100 dark:border-slate-700 flex justify-between items-center">
            <span class="text-slate-500 dark:text-slate-400 font-medium text-sm">Tổng cộng</span>
            <span class="text-2xl font-black text-teal-600" id="paymentModalTotal">0đ</span>
        </div>

        <div>
            <label for="paymentCouponCode" class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Mã giảm giá</label>
            <div class="relative">
                <input type="text" id="paymentCouponCode" class="w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 pr-10 text-slate-800 dark:text-slate-100 uppercase tracking-wider font-bold focus:ring-2 focus:ring-teal-500/20 focus:border-teal-500 outline-none" placeholder="Nhập mã nếu có">
                <i class="fas fa-ticket-alt absolute right-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Mã sẽ được kiểm tra và trừ trực tiếp khi xác nhận thanh toán.</p>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Phương thức thanh toán</label>
            <div class="grid grid-cols-2 gap-3">
                <button type="button" onclick="selectPaymentMethod('cash')" id="btnPmtCash" class="py-3 px-4 rounded-xl border-2 border-teal-500 bg-teal-50 dark:bg-teal-900/20 text-teal-700 dark:text-teal-400 font-bold transition flex items-center justify-center gap-2">
                    <i class="fas fa-money-bill-wave"></i> Tiền Mặt
                </button>
                <button type="button" onclick="selectPaymentMethod('transfer')" id="btnPmtTransfer" class="py-3 px-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 font-bold transition flex items-center justify-center gap-2">
                    <i class="fas fa-qrcode"></i> Chuyển Khoản / QR
                </button>
            </div>
            <input type="hidden" id="paymentMethodInput" value="cash">
        </div>

        <div id="qrPreviewArea" class="hidden text-center bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl border border-slate-200 dark:border-slate-700 relative min-h-[250px] flex items-center justify-center">
            <div id="qrLoading" class="text-slate-400 flex flex-col items-center">
                <i class="fas fa-circle-notch fa-spin text-3xl mb-2"></i>
                <span class="text-sm">Đang tạo mã QR...</span>
            </div>
            <img id="qrImage" src="" class="hidden w-48 h-48 mx-auto rounded-xl shadow-sm border p-1 bg-white">
        </div>

        <button onclick="confirmPayment()" id="btnConfirmPayment" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-teal-600/30 transition text-lg flex items-center justify-center gap-2">
            <i class="fas fa-check-circle"></i> Xác Nhận Đã Thu Khách
        </button>
    </div>
</div>

<script>
    // --- Add Item Modal Logic ---
    let searchPartTimeout = null;
    function searchParts(query) {
        if(query.length < 2) {
            document.getElementById('suggestions').classList.add('hidden');
            return;
        }
        
        clearTimeout(searchPartTimeout);
        searchPartTimeout = setTimeout(() => {
            fetch('{{ route("staff.inventory.search") }}?q=' + query)
                .then(res => res.json())
                .then(data => {
                    if(data.length === 0) {
                        document.getElementById('suggestions').classList.add('hidden');
                        return;
                    }
                    const html = data.map(p => `
                        <div onclick="selectPart('${p.name}', '${p.sku}', ${p.price})" class="p-3 hover:bg-slate-50 dark:bg-slate-800/80 hover:text-slate-800 cursor-pointer flex justify-between items-center border-b border-slate-50 last:border-0 transition text-left">
                            <div>
                                <p class="font-bold text-sm text-slate-700 dark:text-slate-200">${p.name}</p>
                                <p class="text-[10px] text-slate-400 font-mono inline-block bg-slate-100 dark:bg-slate-700 px-1 rounded">${p.sku}</p>
                            </div>
                            <span class="text-xs font-bold text-teal-600">${new Intl.NumberFormat('vi-VN').format(p.price)}đ</span>
                        </div>
                    `).join('');
                    const el = document.getElementById('suggestions');
                    el.innerHTML = html;
                    el.classList.remove('hidden');
                });
        }, 300);
    }

    function toggleItemType() {
        const isCustom = document.getElementById('isCustom').value === 'true';
        const toggle = !isCustom;
        
        document.getElementById('isCustom').value = toggle;
        
        const circle = document.getElementById('toggleCircle');
        const btn = document.getElementById('btnTypeToggle');
        
        if (toggle) {
            circle.style.transform = 'translateX(24px)';
            btn.classList.replace('bg-slate-300', 'bg-teal-500');
            document.getElementById('labelInternal').classList.replace('text-slate-700', 'text-slate-400');
            document.getElementById('labelInternal').classList.replace('dark:text-slate-200', 'text-slate-400');
            document.getElementById('labelExternal').classList.replace('text-slate-400', 'text-teal-600');
            
            document.getElementById('internalSearchBlock').classList.add('hidden');
            document.getElementById('externalNameBlock').classList.remove('hidden');
            document.getElementById('externalCostBlock').classList.remove('hidden');
            
            document.getElementById('itemPrice').readOnly = true;
            document.getElementById('itemPrice').classList.add('bg-slate-100', 'dark:bg-slate-700');
            calculatePrice();
        } else {
            circle.style.transform = 'translateX(0)';
            btn.classList.replace('bg-teal-500', 'bg-slate-300');
            document.getElementById('labelInternal').classList.replace('text-slate-400', 'text-slate-700');
            document.getElementById('labelInternal').classList.add('dark:text-slate-200');
            document.getElementById('labelExternal').classList.replace('text-teal-600', 'text-slate-400');
            
            document.getElementById('internalSearchBlock').classList.remove('hidden');
            document.getElementById('externalNameBlock').classList.add('hidden');
            document.getElementById('externalCostBlock').classList.add('hidden');
            
            document.getElementById('itemPrice').readOnly = false;
            document.getElementById('itemPrice').classList.remove('bg-slate-100', 'dark:bg-slate-700');
        }
    }

    function calculatePrice() {
        if(document.getElementById('isCustom').value !== 'true') return;
        const cost = parseFloat(document.getElementById('costPrice').value) || 0;
        const fee = parseFloat(document.getElementById('serviceFee').value) || 0;
        document.getElementById('itemPrice').value = cost + fee;
    }

    function selectPart(name, sku, price) {
        document.getElementById('itemNameSearch').value = name;
        document.getElementById('itemSku').value = sku;
        document.getElementById('itemPrice').value = price;
        document.getElementById('suggestions').classList.add('hidden');
    }

    function saveItem() {
        const orderId = getCurrentOrderId();
        if(!orderId) return;

        const btn = document.querySelector('#addItemModal button[onclick="saveItem()"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang lưu...';
        btn.disabled = true;

        const isCustom = document.getElementById('isCustom').value;
        const data = {
            is_custom: isCustom,
            qty: document.getElementById('itemQty').value,
            price: document.getElementById('itemPrice').value,
        };

        if(isCustom === 'true') {
            data.name = document.getElementById('itemNameCustom').value;
            data.cost_price = document.getElementById('costPrice').value;
        } else {
            data.name = document.getElementById('itemNameSearch').value;
            data.sku = document.getElementById('itemSku').value;
        }
        
        fetch('{{ route("staff.order.items.store", ":id") }}'.replace(':id', orderId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                if(data.pending_approval) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Đã gửi yêu cầu!',
                        text: data.message,
                        confirmButtonText: 'Đóng',
                        confirmButtonColor: '#0f172a'
                    }).then(() => {
                        document.getElementById('addItemModal').classList.add('hidden');
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    });
                } else {
                    document.getElementById('addItemModal').classList.add('hidden');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    loadOrder(orderId);
                }
            } else {
                Swal.fire('Lỗi!', 'Có lỗi xảy ra khi lưu!', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        })
        .catch(() => {
            Swal.fire('Lỗi!', 'Có lỗi xảy ra!', 'error');
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    // Close modal on outside click
    document.getElementById('addItemModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });

    // --- Payment Modal Logic ---
    function openPaymentModal(totalAmount) {
        totalAmount = parseInt(totalAmount) || 0;
        document.getElementById('paymentModalTotal').innerText = new Intl.NumberFormat('vi-VN').format(totalAmount) + 'đ';
        document.getElementById('btnConfirmPayment').innerHTML = `<i class="fas fa-check-circle"></i> Xác Nhận Đã Thu Khách ${new Intl.NumberFormat('vi-VN').format(totalAmount)}đ`;
        document.getElementById('paymentModal').dataset.total = totalAmount; // Store for select method
        document.getElementById('paymentCouponCode').value = '';
        
        // Reset to cash payment method
        selectPaymentMethod('cash');
        
        const modal = document.getElementById('paymentModal');
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.remove('opacity-0');
            modal.children[0].classList.remove('scale-95');
        }, 10);
    }

    // Close modal on outside click
    document.getElementById('paymentModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
    });

    function selectPaymentMethod(method) {
        document.getElementById('paymentMethodInput').value = method;
        const totalAmount = parseInt(document.getElementById('paymentModal').dataset.total || 0);
        
        const btnCash = document.getElementById('btnPmtCash');
        const btnTransfer = document.getElementById('btnPmtTransfer');
        const qrArea = document.getElementById('qrPreviewArea');
        const qrLoading = document.getElementById('qrLoading');
        const qrImage = document.getElementById('qrImage');
        const btnConfirm = document.getElementById('btnConfirmPayment');
        const orderId = getCurrentOrderId();
        
        // Reset styles
        btnCash.className = 'py-3 px-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 font-bold transition flex items-center justify-center gap-2';
        btnTransfer.className = 'py-3 px-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 text-slate-500 hover:bg-slate-50 dark:hover:bg-slate-800 font-bold transition flex items-center justify-center gap-2';
        
        if (method === 'cash') {
            btnCash.className = 'py-3 px-4 rounded-xl border-2 border-teal-500 bg-teal-50 dark:bg-teal-900/20 text-teal-700 dark:text-teal-400 font-bold transition flex items-center justify-center gap-2';
            qrArea.classList.add('hidden');
            btnConfirm.innerHTML = `<i class="fas fa-check-circle"></i> Xác Nhận Đã Thu Khách ${new Intl.NumberFormat('vi-VN').format(totalAmount)}đ (Tiền Mặt)`;
        } else {
            btnTransfer.className = 'py-3 px-4 rounded-xl border-2 border-teal-500 bg-teal-50 dark:bg-teal-900/20 text-teal-700 dark:text-teal-400 font-bold transition flex items-center justify-center gap-2';
            qrArea.classList.remove('hidden');
            qrLoading.classList.remove('hidden');
            qrImage.classList.add('hidden');
            btnConfirm.innerHTML = `<i class="fas fa-check-circle"></i> Xác Nhận Đã Nhận Chuyển Khoản`;
            
            // Fetch QR Code
            if(orderId) {
                const couponCode = document.getElementById('paymentCouponCode').value.trim();
                const qrUrl = `{{ route('staff.order.qr', ':id') }}`.replace(':id', orderId) + (couponCode ? `?coupon_code=${encodeURIComponent(couponCode)}` : '');
                fetch(qrUrl)
                    .then(r => r.json())
                    .then(d => {
                        if(d.success) {
                            qrImage.src = d.qr_url;
                            qrLoading.classList.add('hidden');
                            qrImage.classList.remove('hidden');
                            btnConfirm.innerHTML = `<i class="fas fa-check-circle"></i> Xác Nhận Đã Nhận Chuyển Khoản ${new Intl.NumberFormat('vi-VN').format(d.amount)}đ`;
                        } else {
                            qrLoading.innerHTML = `<span class="text-red-500"><i class="fas fa-exclamation-triangle"></i> ${d.message || 'Lỗi tạo QR'}</span>`;
                        }
                    })
                    .catch(() => {
                        qrLoading.innerHTML = '<span class="text-red-500"><i class="fas fa-wifi"></i> Lỗi kết nối</span>';
                    });
            }
        }
    }

    document.getElementById('paymentCouponCode').addEventListener('input', function () {
        if (document.getElementById('paymentMethodInput').value === 'transfer') {
            selectPaymentMethod('transfer');
        }
    });

    function confirmPayment() {
        const orderId = getCurrentOrderId();
        if(!orderId) return;

        const method = document.getElementById('paymentMethodInput').value;
        const couponCode = document.getElementById('paymentCouponCode').value.trim();
        const btn = document.getElementById('btnConfirmPayment');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Đang xử lý...';

        fetch(`{{ route('staff.order.pay', ':id') }}`.replace(':id', orderId), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ payment_method: method, coupon_code: couponCode })
        })
        .then(r => r.json())
        .then(d => {
            if(d.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Thanh Toán Thành Công!',
                    text: 'Hệ thống đã ghi nhận thanh toán cho đơn hàng này.',
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    document.getElementById('paymentModal').classList.add('hidden');
                    loadOrder(orderId);
                });
            } else {
                Swal.fire('Lỗi', d.message || 'Có lỗi xảy ra', 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Thử Lại';
            }
        })
        .catch(err => {
            Swal.fire('Lỗi', 'Lỗi kết nối mạng.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> Thử Lại';
        });
    }
</script>
