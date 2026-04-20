@extends('layouts.admin')

@section('title', 'Add New Staff')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Breadcrumb -->
    <div class="mb-6 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('admin.staff.index') }}" class="hover:text-indigo-400 transition">Staff</a>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-white">Create Profile</span>
    </div>

    <form action="{{ route('admin.staff.store') }}" method="POST" class="glass-panel p-8 rounded-2xl border border-slate-700/50 relative overflow-hidden">
        @csrf
        <div class="relative z-10">
            <h2 class="text-2xl font-bold text-white mb-6">Tạo Tài Khoản Mới</h2>

            @if($errors->any())
            <div class="mb-6 bg-red-500/10 border border-red-500/20 rounded-lg p-4 text-red-400 text-sm">
                <ul>
                    @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="space-y-6">
                <!-- Name & Email -->
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Họ và Tên</label>
                        <input type="text" name="name" required class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition" placeholder="VD: Nguyễn Văn A">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Địa chỉ Email</label>
                        <input type="email" name="email" required class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition" placeholder="staff@smartgarage.com">
                    </div>
                </div>

                <!-- Password & Role -->
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Mật Khẩu</label>
                        <input type="password" name="password" required class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition" placeholder="••••••••">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Phân Vai Trò (Role)</label>
                        <select name="role_id" required class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-indigo-500 appearance-none cursor-pointer">
                            <option value="">-- Chọn Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Permissions Note -->
                <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-blue-400 mt-0.5"></i>
                        <div>
                            <h4 class="text-sm font-bold text-blue-300">Permissions are managed via Roles</h4>
                            <p class="text-xs text-blue-400/80 mt-1">To change what this user can do, edit their assigned Role or create a new Role in the <a href="{{ route('admin.roles.index') }}" class="underline hover:text-white">Roles Management</a> section.</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-6 border-t border-slate-700 flex justify-end gap-3">
                    <a href="{{ route('admin.staff.index') }}" class="px-6 py-3 rounded-xl font-bold text-slate-400 hover:text-white hover:bg-slate-800 transition">Cancel</a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-500 hover:to-indigo-400 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/25 transition transform active:scale-95">
                        Create Account
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
