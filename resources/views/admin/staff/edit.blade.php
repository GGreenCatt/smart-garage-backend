@extends('layouts.admin')

@section('title', 'Chỉnh Sửa Nhân Viên')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="glass-panel overflow-hidden rounded-2xl border border-slate-700/50">
        <div class="flex flex-col gap-4 border-b border-slate-700 p-6 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-lg font-bold text-white">Chỉnh sửa: {{ $staff->name }}</h2>
                <p class="text-sm text-slate-400">Cập nhật thông tin, chức vụ, trạng thái và bảo mật tài khoản.</p>
            </div>
            @can('manage_staff')
                <form action="{{ route('admin.staff.destroy', $staff) }}" method="POST" onsubmit="return confirm('Bạn muốn xóa hoặc ngừng hoạt động tài khoản nhân viên này?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-red-500/20 bg-red-500/10 px-3 py-2 text-sm font-bold text-red-300 transition hover:bg-red-500/20">
                        <i class="fas fa-trash"></i>
                        Xóa / ngừng hoạt động
                    </button>
                </form>
            @endcan
        </div>

        <form action="{{ route('admin.staff.update', $staff) }}" method="POST" class="space-y-6 p-6">
            @csrf
            @method('PUT')

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
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Họ và tên</label>
                    <input type="text" name="name" value="{{ old('name', $staff->name) }}" required class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Chức vụ</label>
                    <select name="role_id" required class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id', $staff->role_id) == $role->id)>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Email</label>
                    <input type="email" name="email" value="{{ old('email', $staff->email) }}" required class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Số điện thoại</label>
                    <input type="text" name="phone" value="{{ old('phone', $staff->phone) }}" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
                </div>

                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Trạng thái</label>
                    <select name="status" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none">
                        <option value="active" @selected(old('status', $staff->status ?? 'active') === 'active')>Đang hoạt động</option>
                        <option value="inactive" @selected(old('status', $staff->status) === 'inactive')>Ngừng hoạt động</option>
                        <option value="banned" @selected(old('status', $staff->status) === 'banned')>Đã khóa</option>
                    </select>
                </div>
            </div>

            <div class="border-t border-slate-700 pt-6">
                <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Nhãn phân loại</label>
                <p class="mb-2 text-xs text-slate-400">Nhập các nhãn cách nhau bằng dấu phẩy. Ví dụ: Kỹ thuật trưởng, Ca tối.</p>
                <input type="text" name="tags" value="{{ old('tags', $staff->tags ? implode(', ', $staff->tags) : '') }}" class="w-full rounded-lg border border-slate-700 bg-slate-800 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none" placeholder="VD: Kỹ thuật trưởng, Ca tối">
            </div>

            <div class="border-t border-slate-700 pt-6">
                <h3 class="mb-4 flex items-center gap-2 text-sm font-bold text-white">
                    <i class="fas fa-lock text-indigo-400"></i>
                    Đổi mật khẩu
                </h3>
                <div class="rounded-xl border border-slate-700/50 bg-slate-800/50 p-4">
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Mật khẩu mới</label>
                    <input type="password" name="password" class="w-full rounded-lg border border-slate-700 bg-slate-900 px-4 py-3 text-white focus:border-indigo-500 focus:outline-none" placeholder="Để trống nếu không đổi mật khẩu">
                    <p class="mt-2 text-xs text-slate-500">Tối thiểu 6 ký tự. Chỉ nhập khi cần đặt lại mật khẩu cho nhân viên.</p>
                </div>
            </div>

            <div class="flex justify-end gap-4 border-t border-slate-700 pt-4">
                <a href="{{ route('admin.staff.index') }}" class="rounded-lg bg-slate-800 px-6 py-3 font-bold text-white transition hover:bg-slate-700">Hủy</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-6 py-3 font-bold text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-500">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>
@endsection
