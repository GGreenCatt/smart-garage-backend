<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function showRegisterForm()
    {
        if (view()->exists('auth.register')) {
            return view('auth.register');
        }
        return redirect()->route('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'], // Used as 'login' input (email OR phone)
            'password' => ['required'],
        ]);

        $loginInput = $request->input('email');
        $credentials = [
            'password' => $request->input('password')
        ];

        // Determine if input is email or phone
        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            $credentials['email'] = $loginInput;
        } else {
            // Assume phone number logic
            $credentials['phone'] = $loginInput;
        }

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            $user = Auth::user();

            \App\Models\ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'LOGIN',
                'details' => 'User logged in successfully',
                'ip_address' => $request->ip()
            ]);

            if ($user->role === 'admin') {
                return response()->json(['redirect' => route('admin.dashboard')]);
            } elseif ($user->role === 'staff' || $user->role === 'manager' || $user->role === 'technician') {
                return response()->json(['redirect' => route('staff.dashboard')]);
            } else {
                return response()->json(['redirect' => route('customer.dashboard')]);
            }
        }

        return response()->json([
            'message' => 'Thông tin đăng nhập không chính xác.',
        ], 401);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6'],
        ], [
            'phone.unique' => 'Số điện thoại này đã có tài khoản. Vui lòng đăng nhập bằng số điện thoại đó.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);

        try {
            // Email is optional/nullable now. We DO NOT generate fake emails.
            
            // Safer role retrieval
            $customerRoleId = \App\Models\Role::where('slug', 'customer')->value('id');

            $user = User::create([
                'name' => $validated['name'],
                'email' => null, // Explicitly null for clarity
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
                'role' => 'customer',
                'role_id' => $customerRoleId,
            ]);

            \App\Models\ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'REGISTER',
                'details' => 'New customer registration via Phone',
                'ip_address' => $request->ip()
            ]);

            Auth::login($user);
            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'redirect' => route('customer.dashboard')
            ]);
        } catch (\Exception $e) {
            Log::error('Registration Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'input' => $request->except('password')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Đã có lỗi xảy ra trong quá trình đăng ký. Vui lòng thử lại sau.',
                'error_debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            \App\Models\ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => 'LOGOUT',
                'details' => 'User logged out',
                'ip_address' => $request->ip()
            ]);
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/')->with('success', 'Đăng xuất thành công! Hẹn gặp lại.');
    }
}
