@extends('layouts.admin')

@section('title', 'Thêm Chức Vụ')

@section('content')
<div class="mx-auto max-w-6xl space-y-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-slate-400 transition hover:text-white">
                <i class="fas fa-arrow-left text-xs"></i>
                Quay lại danh sách chức vụ
            </a>
            <h1 class="mt-3 text-3xl font-black text-white">Thêm chức vụ mới</h1>
            <p class="mt-2 text-sm text-slate-400">Tạo bộ quyền riêng cho từng nhóm nhân viên trong garage.</p>
        </div>
    </div>

    <form action="{{ route('admin.roles.store') }}" method="POST" class="grid gap-6 lg:grid-cols-[360px_1fr]">
        @csrf

        <section class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6 shadow-xl shadow-slate-950/20">
            <h2 class="text-lg font-black text-white">Thông tin chức vụ</h2>

            <div class="mt-5 space-y-5">
                <label class="block">
                    <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-400">Tên chức vụ</span>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Ví dụ: Cố vấn dịch vụ" class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm font-bold text-white outline-none transition focus:border-indigo-500">
                    @error('name')
                        <span class="mt-2 block text-xs font-bold text-red-400">{{ $message }}</span>
                    @enderror
                </label>

                <label class="block">
                    <span class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-400">Mô tả</span>
                    <textarea name="description" rows="5" placeholder="Mô tả trách nhiệm chính của chức vụ này..." class="w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm font-semibold text-white outline-none transition focus:border-indigo-500">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="mt-2 block text-xs font-bold text-red-400">{{ $message }}</span>
                    @enderror
                </label>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-800 bg-slate-900/70 p-6 shadow-xl shadow-slate-950/20">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-lg font-black text-white">Phân quyền thao tác</h2>
                    <p class="mt-1 text-sm text-slate-400">Chọn đúng quyền theo nhiệm vụ thực tế của nhân viên.</p>
                </div>
                <button type="button" id="selectAllBtn" class="rounded-xl border border-slate-700 px-4 py-2 text-sm font-black text-slate-300 transition hover:border-indigo-500 hover:text-white">
                    Chọn tất cả
                </button>
            </div>

            @error('permissions')
                <div class="mt-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-bold text-red-300">{{ $message }}</div>
            @enderror

            <div class="mt-6 grid gap-4 xl:grid-cols-2">
                @foreach($permissionGroups as $groupName => $permissions)
                    <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-5">
                        <h3 class="mb-4 flex items-center gap-2 text-sm font-black uppercase tracking-wider text-indigo-300">
                            <i class="fas fa-shield-halved text-xs"></i>
                            {{ $groupName }}
                        </h3>
                        <div class="space-y-3">
                            @foreach($permissions as $permission => $label)
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-800 bg-slate-900/70 p-3 transition hover:border-indigo-500/50 hover:bg-slate-900">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission }}" @checked(in_array($permission, old('permissions', []), true)) class="mt-1 rounded border-slate-600 bg-slate-800 text-indigo-500 focus:ring-indigo-500">
                                    <span>
                                        <span class="block text-sm font-bold text-slate-100">{{ $label }}</span>
                                        <span class="mt-0.5 block text-xs font-mono text-slate-500">{{ $permission }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-800 pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.roles.index') }}" class="rounded-xl border border-slate-700 px-6 py-3 text-center text-sm font-black text-slate-300 transition hover:bg-slate-800 hover:text-white">Hủy</a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-7 py-3 text-sm font-black text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-500">Tạo chức vụ</button>
            </div>
        </section>
    </form>
</div>

<script>
    document.getElementById('selectAllBtn').addEventListener('click', function () {
        const checkboxes = Array.from(document.querySelectorAll('input[name="permissions[]"]'));
        const allChecked = checkboxes.every(checkbox => checkbox.checked);
        checkboxes.forEach(checkbox => checkbox.checked = !allChecked);
        this.textContent = allChecked ? 'Chọn tất cả' : 'Bỏ chọn tất cả';
    });
</script>
@endsection
