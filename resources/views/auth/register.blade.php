<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - Smart Garage Management</title>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </div>
            </div>
            
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">Tạo tài khoản mới</h2>
            <p class="text-center text-gray-500 mb-8">Trải nghiệm dịch vụ Smart Garage ngay hôm nay</p>

            <form action="{{ route('register.post') }}" method="POST" class="space-y-5">
                @csrf
                
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Họ và tên</label>
                    <input type="text" id="name" name="name" required autofocus
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors outline-none"
                        placeholder="Nguyễn Văn A">
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Số điện thoại</label>
                    <input type="text" id="phone" name="phone" required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors outline-none"
                        placeholder="0912345678">
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mật khẩu</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors outline-none"
                        placeholder="••••••••">
                    <p class="text-xs text-gray-500 mt-1">Tối thiểu 6 ký tự</p>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition-colors shadow-lg hover:shadow-blue-500/30">
                    Đăng Ký
                </button>
            </form>
        </div>
        
        <div class="bg-gray-50 px-8 py-4 border-t border-gray-100">
            <p class="text-sm text-center text-gray-600">
                Đã có tài khoản? 
                <a href="{{ route('login') }}" class="text-blue-600 font-medium hover:text-blue-500">Đăng nhập</a>
            </p>
        </div>
    </div>

    <script>
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
                        title: 'Đăng ký thành công!',
                        text: 'Đang chuyển hướng...',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                    setTimeout(() => {
                         window.location.href = data.redirect || '/';
                    }, 1500);
                } else {
                    // Validation errors
                    let msg = data.message || 'Đăng ký thất bại';
                    if (data.errors) {
                        msg = Object.values(data.errors).flat().join('\n');
                    }
                    
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: 'Lỗi đăng ký',
                        text: msg,
                        showConfirmButton: false,
                        timer: 4000
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
