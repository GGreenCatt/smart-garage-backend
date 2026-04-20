<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Smart Garage Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-50 h-screen flex items-center justify-center">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="p-8">
            <div class="flex justify-center mb-6">
                <!-- Placeholder Logo -->
                <div class="w-12 h-12 bg-blue-600 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>
            
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">Chào mừng trở lại</h2>
            <p class="text-center text-gray-500 mb-8">Đăng nhập vào hệ thống Smart Garage</p>

            <form action="{{ route('login.post') }}" method="POST" class="space-y-6">
                @csrf
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email hoặc Số điện thoại</label>
                    <input type="text" id="email" name="email" required autofocus
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors outline-none"
                        placeholder="admin@example.com">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu</label>
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-500">Quên mật khẩu?</a>
                    </div>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors outline-none"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="remember" class="ml-2 text-sm text-gray-600">Ghi nhớ đăng nhập</label>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition-colors shadow-lg hover:shadow-blue-500/30">
                    Đăng Nhập
                </button>
            </form>
        </div>
        
        <div class="bg-gray-50 px-8 py-4 border-t border-gray-100">
            <p class="text-sm text-center text-gray-600">
                Chưa có tài khoản? 
                <a href="{{ route('register') }}" class="text-blue-600 font-medium hover:text-blue-500">Đăng ký ngay</a>
            </p>
        </div>
    </div>

    <script>
        // Simple JS to handle login response if form is submitted via AJAX, 
        // but for now we are using standard form submission for simplicity with the redirection logic in controller.
        // However, the controller returns JSON, so we should probably handle it via JS or change Controller to return Redirect.
        
        // Since strict JSON response is enforced in AuthController for API tutoring purposes, let's adapt the form to handle JSON.
        document.querySelector('form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = e.target;
            const button = form.querySelector('button[type="submit"]');
            const originalText = button.innerText;
            
            button.disabled = true;
            button.innerText = 'Đang xử lý...';

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(Object.fromEntries(new FormData(form)))
                });

                const data = await response.json();

                if (response.ok) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Đăng nhập thành công!',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    setTimeout(() => {
                         window.location.href = data.redirect || '/';
                    }, 1500); // Wait 1.5s
                } else {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: data.message || 'Đăng nhập thất bại',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    button.disabled = false;
                    button.innerText = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Lỗi kết nối máy chủ',
                    showConfirmButton: false,
                    timer: 3000
                });
                button.disabled = false;
                button.innerText = originalText;
            }
        });
    </script>
</body>
</html>
