<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\StaffController;
use App\Http\Controllers\CustomerController;

Route::get('/', [CustomerController::class, 'index'])->name('home');

// Auth Routes
Route::middleware('auth')->group(function () {
    Route::patch('/profile', [StaffController::class, 'updateProfile'])->name('profile.update');
    Route::put('/password', [StaffController::class, 'updatePassword'])->name('password.update');
});

Route::get('/login', [App\Http\Controllers\AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'login'])->name('login.post');

Route::get('/register', [App\Http\Controllers\AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'register'])->name('register.post');

Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

// Temp Logout for User
// Temp Logout for User (DISABLED: CSRF Risk. Use POST /logout)
// Route::get('/force-logout', function() {
//     \Illuminate\Support\Facades\Auth::logout();
//     request()->session()->invalidate();
//     request()->session()->regenerateToken();
//     return redirect('/');
// });

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');
    Route::get('/vehicle/{id}/3d', [CustomerController::class, 'vehicleDetail'])->name('vehicle.3d');
    Route::get('/vehicle/{id}/inspection-data', [CustomerController::class, 'getVehicleInspection'])->name('vehicle.inspection.data');
    Route::get('/order/{repairOrder}/quote', [App\Http\Controllers\Customer\QuoteController::class, 'show'])->name('order.quote.show');
    Route::post('/order/{repairOrder}/quote/tasks', [App\Http\Controllers\Customer\QuoteController::class, 'approveRejectTasks'])->name('order.quote.tasks');
    
    // Modern Quote Flow
    Route::get('/quote/{repairOrder}', [App\Http\Controllers\Customer\QuoteController::class, 'show'])->name('quote.show');
    Route::post('/quote/{repairOrder}', [App\Http\Controllers\Customer\QuoteController::class, 'approveRejectTasks'])->name('quote.action');

    Route::post('/order/{id}/approve', [CustomerController::class, 'approveQuote'])->name('order.approve');
    Route::post('/order/{id}/reject', [CustomerController::class, 'rejectQuote'])->name('order.reject');
    
    // My Orders & Payment
    Route::get('/orders', [CustomerController::class, 'myOrders'])->name('orders.index');
    Route::get('/orders/{id}', [CustomerController::class, 'showOrder'])->name('orders.show');
    Route::post('/orders/{id}/coupon', [CustomerController::class, 'applyCoupon'])->name('orders.coupon');

    // My Vehicles
    Route::get('/vehicles', [CustomerController::class, 'myVehicles'])->name('vehicles.index');

    // Profile
    Route::get('/profile', [CustomerController::class, 'profile'])->name('profile');

    // Appointments
    Route::get('/appointments/book', [App\Http\Controllers\Customer\AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [App\Http\Controllers\Customer\AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments', [App\Http\Controllers\Customer\AppointmentController::class, 'index'])->name('appointments.index');

    // Notifications
    Route::get('notifications', [App\Http\Controllers\Customer\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [App\Http\Controllers\Customer\NotificationController::class, 'read'])->name('notifications.read');

});

// Guest-accessible Customer Routes
Route::prefix('customer')->name('customer.')->group(function () {
    // Cứu Hộ SOS
    Route::get('/sos', [App\Http\Controllers\Customer\SosController::class, 'index'])->name('sos.index');
    Route::post('/sos', [App\Http\Controllers\Customer\SosController::class, 'store'])->name('sos.store');
    Route::post('/sos/{id}/cancel', [App\Http\Controllers\Customer\SosController::class, 'cancel'])->name('sos.cancel');
});

// Guest-accessible signed routes for Quotes
Route::middleware(['signed'])->group(function () {
    Route::get('/guest/quote/{repairOrder}', [App\Http\Controllers\Customer\QuoteController::class, 'show'])->name('guest.quote.show');
    Route::post('/guest/quote/{repairOrder}', [App\Http\Controllers\Customer\QuoteController::class, 'approveRejectTasks'])->name('guest.quote.action');
    
    // Dependencies for 3D Visualizer on Quote
    Route::get('/guest/vehicle/{id}/3d', [CustomerController::class, 'vehicleDetail'])->name('guest.vehicle.3d');
    Route::get('/guest/vehicle/{id}/inspection-data', [CustomerController::class, 'getVehicleInspection'])->name('guest.vehicle.inspection.data');
});

Route::middleware(['auth', 'verified', 'staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('dashboard');
    
    // Notifications
    Route::get('notifications', [App\Http\Controllers\Staff\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [App\Http\Controllers\Staff\NotificationController::class, 'read'])->name('notifications.read');

    Route::post('/vehicle/store', [StaffController::class, 'storeVehicle'])->name('vehicle.store');
    Route::get('/vehicle/{id}/inspection', [StaffController::class, 'inspection'])->name('vehicle.inspection');
    Route::get('/vhc/fetch/{id}', [StaffController::class, 'fetchVhcData'])->name('vhc.fetch');
    Route::get('/vehicle/{id}/vhc', [StaffController::class, 'getVhcData'])->name('vehicle.vhc.data');
    Route::post('/vehicle/{id}/vhc', [StaffController::class, 'saveVhcData'])->name('vhc.save');
    
    // Repair Order Details
    // Repair Order Details
    Route::get('/order/{id}', [StaffController::class, 'showOrder'])->name('order.show');
    Route::post('/order/{id}/update-status', [StaffController::class, 'updateOrderStatus'])->name('order.update-status');
    Route::post('/order/{id}/tasks', [StaffController::class, 'storeTask'])->name('order.tasks.store');
    Route::post('/order/{id}/add-note', [StaffController::class, 'addNote'])->name('order.add-note');
    Route::post('/order/{id}/quick-item', [StaffController::class, 'storeQuickItem'])->name('order.quick-item');
    Route::post('/order/{id}/request-support', [StaffController::class, 'requestSupport'])->name('order.request-support');
    Route::post('/order/{id}/delete', [StaffController::class, 'deleteOrder'])->name('order.delete');
    
    Route::post('/task/{id}/update', [StaffController::class, 'updateTaskStatus'])->name('task.update');
    Route::post('/task/{id}/toggle', [StaffController::class, 'toggleTask'])->name('task.toggle');
    Route::post('/task/{id}/assign', [StaffController::class, 'assignTask'])->name('task.assign');
    Route::post('/task/{id}/unassign', [StaffController::class, 'unassignTask'])->name('task.unassign');
    
    Route::get('/task/{id}', [StaffController::class, 'showTask'])->name('task.show');
    Route::post('/task/{id}/details', [StaffController::class, 'updateTaskDetails'])->name('task.update-details');
    Route::post('/task/{id}/delete', [StaffController::class, 'deleteTask'])->name('task.delete');

    Route::get('/inventory', [StaffController::class, 'inventory'])->name('inventory.index');
    
    // Customer Management
    Route::get('/customers/check-phone', [StaffController::class, 'checkCustomer'])->name('customers.check');
    Route::resource('customers', StaffController::class); // Replaces previous customer routes
    
    // Vehicles Edit
    Route::get('/vehicles/{id}/edit', [StaffController::class, 'editVehicle'])->name('vehicles.edit');
    Route::put('/vehicles/{id}', [StaffController::class, 'updateVehicle'])->name('vehicles.update');
    Route::delete('/vehicles/{id}', [StaffController::class, 'destroyVehicle'])->name('vehicles.destroy');

    Route::get('/customers/{id}/vehicles-json', [StaffController::class, 'getVehiclesJson']); // Helper if needed
    
    Route::get('/profile', [StaffController::class, 'profile'])->name('profile');

    // Quotation
    Route::get('/inventory-search', [StaffController::class, 'searchParts'])->name('inventory.search');
    Route::post('/order/{id}/items', [StaffController::class, 'storeItem'])->name('order.items.store');
    
    // Quote Flow
    Route::get('/order/{id}/quote/create', [App\Http\Controllers\Staff\QuoteController::class, 'create'])->name('quote.create');
    Route::post('/order/{repairOrder}/send-quote', [App\Http\Controllers\Staff\QuoteController::class, 'sendQuote'])->name('order.send-quote');
    Route::get('/order/{repairOrder}/quote/show', [App\Http\Controllers\Staff\QuoteController::class, 'show'])->name('quote.show');

    // Payment Flow
    Route::post('/order/{id}/pay', [StaffController::class, 'processPayment'])->name('order.pay');
    Route::get('/order/{id}/qr', [StaffController::class, 'generateQrCode'])->name('order.qr');
    Route::get('/order/{id}/invoice', [StaffController::class, 'printInvoice'])->name('order.invoice');

    // Internal Chat
    Route::get('/order/{id}/comments', [StaffController::class, 'getComments'])->name('order.comments');
    Route::post('/order/{id}/comments', [StaffController::class, 'storeComment'])->name('order.comments.store');

    // Notifications
    Route::get('/notifications', [StaffController::class, 'getNotifications'])->name('notifications.index');
    Route::post('/notifications/read-all', [StaffController::class, 'markAllNotificationsRead'])->name('notifications.readAll');
    Route::post('/notifications/{id}/read', [StaffController::class, 'markNotificationAsRead'])->name('notifications.read');

    // Personal Schedule
    Route::get('/schedule', [StaffController::class, 'schedule'])->name('schedule.index');

    // Staff Chat (Customer Support)
    Route::get('/chat', [App\Http\Controllers\StaffChatController::class, 'index'])->name('chat.index');
    Route::get('/chat/sessions', [App\Http\Controllers\StaffChatController::class, 'getSessions'])->name('chat.sessions');
    Route::get('/chat/search', [App\Http\Controllers\StaffChatController::class, 'searchCustomer'])->name('chat.search');
    Route::post('/chat/start', [App\Http\Controllers\StaffChatController::class, 'startSession'])->name('chat.start');
    Route::post('/chat/reply', [App\Http\Controllers\StaffChatController::class, 'reply'])->name('chat.reply');
    
    // Internal Staff Chat
    Route::get('/internal-chat/contacts', [App\Http\Controllers\InternalChatController::class, 'getContacts'])->name('internal_chat.contacts');
    Route::get('/internal-chat/messages', [App\Http\Controllers\InternalChatController::class, 'getMessages'])->name('internal_chat.messages');
    Route::post('/internal-chat/send', [App\Http\Controllers\InternalChatController::class, 'sendMessage'])->name('internal_chat.send');
    
    // Material Requests
    Route::get('/requests', [App\Http\Controllers\Staff\MaterialRequestController::class, 'index'])->name('requests.index');
    Route::post('/requests', [App\Http\Controllers\Staff\MaterialRequestController::class, 'store'])->name('requests.store');

    // SOS Requests
    Route::get('/sos', [App\Http\Controllers\Staff\SosController::class, 'index'])->name('sos.index');
    Route::get('/sos/{id}', [App\Http\Controllers\Staff\SosController::class, 'show'])->name('sos.show');
    Route::post('/sos/{id}/accept', [App\Http\Controllers\Staff\SosController::class, 'accept'])->name('sos.accept');
    Route::post('/sos/{id}/status', [App\Http\Controllers\Staff\SosController::class, 'updateStatus'])->name('sos.status');
    Route::post('/sos/{id}/unassign', [App\Http\Controllers\Staff\SosController::class, 'unassign'])->name('sos.unassign');
});

// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('staff', App\Http\Controllers\Admin\StaffController::class);
    Route::get('/logs', [App\Http\Controllers\Admin\StaffController::class, 'logs'])->name('staff.logs');
    Route::resource('customers', App\Http\Controllers\Admin\CustomerController::class);
    Route::resource('vehicles', App\Http\Controllers\Admin\VehicleController::class);
    Route::get('vehicles/{vehicle}/3d', [App\Http\Controllers\Admin\VehicleController::class, 'view3d'])->name('vehicles.3d');
    
    // Repair Order Tasks
    Route::post('/repair-orders/{repairOrder}/tasks', [App\Http\Controllers\Admin\RepairOrderController::class, 'storeTask'])->name('repair_orders.tasks.store');
    Route::patch('/repair-tasks/{task}', [App\Http\Controllers\Admin\RepairOrderController::class, 'updateTaskStatus'])->name('repair_tasks.update');
    
    // Inventory
    Route::resource('suppliers', App\Http\Controllers\Admin\SupplierController::class)->except(['create', 'show', 'edit']);
    Route::resource('inventory', App\Http\Controllers\Admin\InventoryController::class);
    Route::post('inventory/{id}/stock', [App\Http\Controllers\Admin\InventoryController::class, 'updateStock'])->name('inventory.stock');
    Route::get('inventory-transactions', [App\Http\Controllers\Admin\InventoryController::class, 'transactions'])->name('inventory.transactions');
    // Services & Repair Orders
    Route::resource('services', App\Http\Controllers\Admin\ServiceController::class);
    Route::resource('repair_orders', App\Http\Controllers\Admin\RepairOrderController::class);
    Route::get('repair_orders/{repair_order}/invoice', [App\Http\Controllers\Admin\RepairOrderController::class, 'invoice'])->name('repair_orders.invoice');
    Route::post('repair_orders/{repair_order}/items', [App\Http\Controllers\Admin\RepairOrderController::class, 'storeItem'])->name('repair_orders.items.store');
    Route::delete('repair_orders/{repair_order}/items/{item}', [App\Http\Controllers\Admin\RepairOrderController::class, 'destroyItem'])->name('repair_orders.items.destroy');
    Route::post('repair_orders/{repair_order}/status', [App\Http\Controllers\Admin\RepairOrderController::class, 'updateStatus'])->name('repair_orders.status');
    Route::post('repair_orders/{repair_order}/coupon', [App\Http\Controllers\Admin\RepairOrderController::class, 'applyCoupon'])->name('repair_orders.coupon');
    Route::delete('repair_orders/{repair_order}/coupon', [App\Http\Controllers\Admin\RepairOrderController::class, 'removeCoupon'])->name('repair_orders.coupon.remove');
    Route::get('customers/{id}/vehicles-json', [App\Http\Controllers\Admin\CustomerController::class, 'getVehiclesJson'])->name('customers.vehicles.json');

    // Roles (RBAC)
    Route::resource('roles', App\Http\Controllers\Admin\RoleController::class);
    
    // Promotions (Marketing)
    Route::resource('promotions', App\Http\Controllers\Admin\PromotionController::class);

    // SOS & Map
    Route::get('sos', [App\Http\Controllers\Admin\SosController::class, 'index'])->name('sos.index');
    Route::get('api/sos/map-data', [App\Http\Controllers\Admin\SosController::class, 'getMapData'])->name('api.sos.map-data');

    // Appointments
    Route::post('appointments/{appointment}/convert', [App\Http\Controllers\Admin\AppointmentController::class, 'convertToRo'])->name('appointments.convert');
    Route::resource('appointments', App\Http\Controllers\Admin\AppointmentController::class);

    // Settings
    Route::get('settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

    // Material Requests (Admin Approval)
    Route::resource('material-requests', App\Http\Controllers\Admin\MaterialRequestController::class)->names('requests')->only(['index', 'update']);
});

// Staff Routes
Route::post('/chat/send', [App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
Route::get('/chat/messages', [App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
