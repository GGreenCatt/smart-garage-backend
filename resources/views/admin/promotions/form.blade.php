@if($errors->any())
    <div class="rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-200">
        <ul class="list-disc space-y-1 pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Mã khuyến mãi</label>
        <input name="code" value="{{ old('code', $promotion?->code) }}" required class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 font-mono uppercase tracking-wider text-white focus:border-indigo-500 focus:outline-none" placeholder="VD: GARAGE10">
    </div>
    <div>
        <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Trạng thái</label>
        <label class="mt-2 inline-flex cursor-pointer items-center">
            <input type="checkbox" name="is_active" value="1" class="peer sr-only" @checked(old('is_active', $promotion?->is_active ?? true))>
            <div class="peer relative h-6 w-11 rounded-full bg-slate-700 after:absolute after:start-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:border-gray-300 after:bg-white after:transition-all after:content-[''] peer-checked:bg-indigo-600 peer-checked:after:translate-x-full peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-800"></div>
            <span class="ms-3 text-sm font-medium text-slate-300">Cho phép sử dụng</span>
        </label>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Loại giảm giá</label>
        <select name="type" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
            <option value="fixed" @selected(old('type', $promotion?->type) === 'fixed')>Số tiền cố định</option>
            <option value="percent" @selected(old('type', $promotion?->type) === 'percent')>Phần trăm</option>
        </select>
    </div>
    <div>
        <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Giá trị giảm</label>
        <input type="number" name="value" value="{{ old('value', $promotion?->value) }}" min="0" step="0.01" required class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none" placeholder="VD: 50000 hoặc 10">
    </div>
</div>

<div>
    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Mô tả / ghi chú</label>
    <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none" placeholder="Mô tả ngắn về chương trình">{{ old('description', $promotion?->description) }}</textarea>
</div>

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Ngày bắt đầu</label>
        <input type="datetime-local" name="start_date" value="{{ old('start_date', $promotion?->start_date?->format('Y-m-d\TH:i')) }}" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
    </div>
    <div>
        <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Ngày kết thúc</label>
        <input type="datetime-local" name="end_date" value="{{ old('end_date', $promotion?->end_date?->format('Y-m-d\TH:i')) }}" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
    </div>
</div>

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
    <div>
        <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Giới hạn lượt dùng</label>
        <input type="number" name="usage_limit" value="{{ old('usage_limit', $promotion?->usage_limit) }}" min="1" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none" placeholder="Để trống nếu không giới hạn">
        @if($promotion)
            <p class="mt-1 text-[11px] text-slate-500">Đã dùng: {{ $promotion->used_count }} lần</p>
        @endif
    </div>
    <div>
        <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Chỉ áp dụng cho khách</label>
        <input name="customer_phone" value="{{ old('customer_phone', $promotion?->customer?->phone) }}" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none" placeholder="Nhập SĐT khách cụ thể nếu cần">
        <p class="mt-1 text-[11px] text-slate-500">Để trống để mọi khách đều có thể dùng mã.</p>
    </div>
</div>
