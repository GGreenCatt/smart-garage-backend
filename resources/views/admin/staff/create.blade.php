@extends('layouts.admin')

@section('title', 'Thêm Nhân Viên')

@section('content')
<div class="mx-auto max-w-3xl">
    <div class="mb-6 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.staff.index') }}" class="transition hover:text-indigo-300">Nhân sự</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-white">Thêm nhân viên</span>
    </div>

    <form action="{{ route('admin.staff.store') }}" method="POST" class="glass-panel rounded-2xl border border-slate-700/50 p-8">
        @csrf

        <div class="mb-6">
            <h2 class="text-2xl font-bold text-white">Tạo Tài Khoản Nhân Viên</h2>
            <p class="mt-1 text-sm text-slate-400">Chỉ chọn các chức vụ nội bộ. Tài khoản khách hàng và quản trị cao nhất không được tạo từ màn này.</p>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-xl border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-200">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Họ và tên</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-xl border border-slate-700 bg-slate-900/50 px-4 py-3 text-white transition focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" placeholder="VD: Nguyễn Văn A">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Email đăng nhập</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-xl border border-slate-700 bg-slate-900/50 px-4 py-3 text-white transition focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" placeholder="staff@smartgarage.com">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Số điện thoại</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded-xl border border-slate-700 bg-slate-900/50 px-4 py-3 text-white transition focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" placeholder="0909123456">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Mật khẩu</label>
                    <input type="password" name="password" required class="w-full rounded-xl border border-slate-700 bg-slate-900/50 px-4 py-3 text-white transition focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" placeholder="Tối thiểu 6 ký tự">
                </div>
            </div>

            <div>
                <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-400">Chức vụ</label>
                <select name="role_id" required class="w-full rounded-xl border border-slate-700 bg-slate-900/50 px-4 py-3 text-white transition focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                    <option value="">Chọn chức vụ</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="rounded-xl border border-blue-500/20 bg-blue-500/10 p-4">
                <div class="flex items-start gap-3">
                    <i class="fas fa-info-circle mt-0.5 text-blue-300"></i>
                    <div>
                        <h4 class="text-sm font-bold text-blue-200">Quyền thao tác đi theo chức vụ</h4>
                        <p class="mt-1 text-xs text-blue-100/80">Muốn thay đổi quyền của nhân viên, hãy chỉnh chức vụ trong mục Phân Quyền - Chức Vụ.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-700 pt-6">
                <a href="{{ route('admin.staff.index') }}" class="rounded-xl px-6 py-3 font-bold text-slate-400 transition hover:bg-slate-800 hover:text-white">Hủy</a>
                <button type="submit" class="rounded-xl bg-indigo-600 px-8 py-3 font-bold text-white shadow-lg shadow-indigo-500/25 transition hover:bg-indigo-500">
                    Tạo tài khoản
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
