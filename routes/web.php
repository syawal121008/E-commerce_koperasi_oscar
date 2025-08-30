<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\CartsController;
use App\Http\Controllers\TopupController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\QRController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OrderReceiptController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/shop');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('register', fn() => view('auth.register'))->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']); 
    Route::get('login', fn() => view('auth.login'))->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// form reset password (punya token di URL)
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
    ->name('password.reset');

// proses simpan password baru
Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->name('password.store');

    // Halaman form untuk request reset password (input email)
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
    ->name('password.request');

// Proses kirim email reset password
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->name('password.email');


// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');
    Route::get('/password', function () {
    return redirect()->route('profile.edit');
});

});

// Shop routes (public)
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/products/{product}', [ShopController::class, 'show'])->name('shop.products.show');

// Public review routes
Route::get('/products/{productId}/reviews', [ReviewController::class, 'index'])->name('products.reviews.index');

// Review routes (requires authentication)
Route::middleware(['auth'])->group(function () {
    Route::post('/products/{productId}/reviews', [ReviewController::class, 'store'])->name('products.reviews.store');
    Route::put('/reviews/{reviewId}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{reviewId}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/reviews/{reviewId}/helpful', [ReviewController::class, 'toggleHelpful'])->name('reviews.helpful');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/my-qr', [DashboardController::class, 'qrCode'])->name('qr-code');
    Route::post('/regenerate-qr', [DashboardController::class, 'regenerateQR'])->name('regenerate-qr');

    // Products - General
    Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
    Route::get('/products/{id}', [ProductsController::class, 'show'])->where('id', '[0-9a-f-]+')->name('products.show');
    
    // Transactions Export
    Route::get('/transactions/export/student/{user}', [TransactionController::class, 'exportStudentTransactions'])
        ->name('transactions.export.student');
        
    // Products Management - Admin only
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/transactions/export/all', [TransactionController::class, 'exportAllTransactions'])->name('transactions.export.all');
        Route::get('/products/create', [ProductsController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductsController::class, 'store'])->name('products.store');
        Route::get('/products/{id}/edit', [ProductsController::class, 'edit'])->where('id', '[0-9a-f-]+')->name('products.edit');
        Route::put('/products/{id}', [ProductsController::class, 'update'])->where('id', '[0-9a-f-]+')->name('products.update');
        Route::delete('/products/{id}', [ProductsController::class, 'destroy'])->where('id', '[0-9a-f-]+')->name('products.destroy');
        Route::post('/products/{id}/toggle-status', [ProductsController::class, 'toggleStatus'])->where('id', '[0-9a-f-]+')->name('products.toggle-status');
    });
    
    // Cart management
    Route::get('/carts', [CartsController::class, 'index'])->name('carts.index');
    Route::post('/carts', [CartsController::class, 'store'])->name('carts.store');
    Route::put('/carts/{cart}', [CartsController::class, 'update'])->name('carts.update');
    Route::delete('/carts/{cart}', [CartsController::class, 'destroy'])->name('carts.destroy');
    Route::post('/carts/bulk-delete', [CartsController::class, 'bulkDelete'])->name('carts.bulk-delete');
    Route::get('/carts/count', [CartsController::class, 'count'])->name('carts.count');
    Route::delete('/carts/clear', [CartsController::class, 'clear'])->name('carts.clear');
    Route::post('/carts/checkout', [CartsController::class, 'checkout'])->name('carts.checkout');
    
    // ======= UPDATED ORDERS ROUTES =======
    Route::prefix('orders')->group(function () {
        // Basic CRUD operations
        Route::get('/', [OrdersController::class, 'index'])->name('orders.index');
        Route::get('/create', [OrdersController::class, 'create'])->name('orders.create');
        Route::post('/', [OrdersController::class, 'store'])->name('orders.store');
        Route::get('/{id}', [OrdersController::class, 'show'])->where('id', '[0-9a-f-]+')->name('orders.show');
        Route::get('/{id}/edit', [OrdersController::class, 'edit'])->where('id', '[0-9a-f-]+')->name('orders.edit');
        Route::put('/{id}', [OrdersController::class, 'update'])->where('id', '[0-9a-f-]+')->name('orders.update');
        Route::delete('/{id}', [OrdersController::class, 'destroy'])->where('id', '[0-9a-f-]+')->name('orders.destroy');
        
        // Checkout operations
        Route::post('/checkout', [OrdersController::class, 'checkout'])->name('orders.checkout');
        
        // Payment operations
        Route::post('/{id}/pay-cash', [OrdersController::class, 'payCash'])->where('id', '[0-9a-f-]+')->name('orders.pay-cash');
        Route::post('/{id}/complete', [OrdersController::class, 'complete'])->where('id', '[0-9a-f-]+')->name('orders.complete');
        Route::post('/{id}/cancel', [OrdersController::class, 'cancel'])->where('id', '[0-9a-f-]+')->name('orders.cancel');
        
        // Status updates - available for admin and guru
        Route::patch('/{order}/update-status', [OrdersController::class, 'updateStatus'])
            ->middleware('role:admin,guru')
            ->name('orders.updateStatus');
        Route::put('/{order}/status', [OrdersController::class, 'updateStatus'])
            ->middleware('role:admin,guru')
            ->name('orders.updateStatus.alt');
        
        // QR-based payment operations
        Route::post('/show-for-payment', [OrdersController::class, 'showForPayment'])->name('orders.show-for-payment');
        Route::post('/pay', [OrdersController::class, 'pay'])->name('orders.pay');
        
        // Pending orders - Admin and Guru only
        Route::get('/pending/list', [OrdersController::class, 'pending'])
            ->middleware('role:admin,guru')
            ->name('orders.pending');
        
        // Legacy support - keeping old pending route for backward compatibility
        Route::middleware('role:admin')->group(function () {
            Route::get('/pending-orders', [OrdersController::class, 'pending'])->name('orders.pending.legacy');
        });
    });

    // POS (Point of Sale) Routes - Admin Only
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/pos', [OrdersController::class, 'pos'])->name('admin.pos');
        Route::post('/admin/pos/orders', [OrdersController::class, 'storePOS'])->name('admin.pos.store');
        Route::get('/admin/pos/reports', [OrdersController::class, 'posReport'])->name('admin.pos.reports');
        
        // POS API Routes for QR scanning and product management
        Route::post('/admin/pos/customer-qr', [OrdersController::class, 'getCustomerFromQR'])->name('admin.pos.customer-qr');
        Route::get('/admin/pos/products/{productId}/stock', [OrdersController::class, 'getProductStock'])
            ->where('productId', '[0-9a-f-]+')
            ->name('admin.pos.product-stock');
    });

    Route::middleware(['auth'])->group(function () {
    
    // Profit Reports Routes
    Route::get('/reports/profit', [OrdersController::class, 'profitReport'])
        ->name('supervisor.profit')
        ->middleware('role:admin,guru');
        Route::get('/supervisor/profit/reset', [OrdersController::class, 'resetProfitReport'])
    ->name('supervisor.profit.reset')->middleware('role:admin,guru');
        Route::get('/supervisor/profit/product-data', [OrdersController::class, 'getProductStatsData'])->name('supervisor.profit.product_data')->middleware('role:admin,guru');
    Route::get('/supervisor/profit/admin-data', [OrdersController::class, 'getAdminStatsData'])->name('supervisor.profit.admin_data')->middleware('role:admin,guru');

    Route::get('/reports/profit/export', [OrdersController::class, 'exportProfitReport'])
        ->name('supervisor.profit.export')
        ->middleware('role:admin,guru');
    
    Route::get('/reports/profit/summary', [OrdersController::class, 'profitSummary'])
        ->name('supervisor.profit.summary')
        ->middleware('role:admin,guru');

    // Dashboard API for real-time data
    Route::get('/api/profit-summary', [OrdersController::class, 'profitSummary'])
        ->name('api.profit.summary')
        ->middleware('role:admin,guru');
        Route::get('/profit/export-excel', [OrdersController::class, 'exportExcel'])->name('profit.exportExcel');
Route::get('/profit/print', [OrdersController::class, 'print'])->name('profit.print');
});

    // Order receipts
    Route::get('/orders/{id}/receipt', [OrderReceiptController::class, 'show'])->where('id', '[0-9a-f-]+')->name('orders.receipt.show');
    Route::get('/orders/{id}/receipt/download', [OrderReceiptController::class, 'download'])->where('id', '[0-9a-f-]+')->name('orders.receipt.download');
    Route::get('/orders/{id}/receipt/print', [OrderReceiptController::class, 'print'])->where('id', '[0-9a-f-]+')->name('orders.receipt.print');

    // Topup routes
    Route::get('/topup', [TopupController::class, 'index'])->name('topup.index');
    Route::get('/topup/qris', [TopupController::class, 'qris'])->name('topup.qris');
    Route::post('/topup', [TopupController::class, 'store'])->name('topup.store');
    Route::get('/topup/{topupId}', [TopupController::class, 'show'])
        ->where('topupId', '[0-9a-f\-]{36}')
        ->name('topup.show');
        
    // Topup receipts
    Route::get('/topup/{topupId}/receipt', [TopupController::class, 'receipt'])
        ->where('topupId', '[0-9a-f\-]{36}')
        ->name('topup.receipt');
    Route::get('/topup/{topupId}/receipt/download', [TopupController::class, 'downloadReceipt'])
        ->where('topupId', '[0-9a-f\-]{36}')
        ->name('topup.receipt.download');
    
    // Route untuk export Excel
    Route::get('/supervisor/topups/export', [TopupController::class, 'export'])
        ->name('supervisor.topups.export')
        ->middleware('role:admin,guru');
    
    // Topup - Admin/Guru
    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/topup/scan', [TopupController::class, 'scan'])->name('topup.scan');
        Route::post('/topup/by-qr', [TopupController::class, 'storeByQr'])->name('topup.storeByQr');
        Route::get('/supervisor/topups', [TopupController::class, 'supervisorView'])->name('supervisor.topups');
        Route::get('/topups/statistics', [TopupController::class, 'getStatistics'])->name('supervisor.topups.statistics');
    });

    // Topup - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/topups', [TopupController::class, 'pending'])->name('admin.topups');
        Route::post('/admin/topups/{topupId}/approve', [TopupController::class, 'approve'])->where('topupId', '[0-9a-f\-]{36}')->name('admin.topups.approve');
        Route::post('/admin/topups/{topupId}/reject', [TopupController::class, 'reject'])->where('topupId', '[0-9a-f\-]{36}')->name('admin.topups.reject');
    });
    
    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/{id}', [TransactionController::class, 'show'])->where('id', '[0-9a-f-]+')->name('transactions.show');
    Route::get('/transactions/statistics', [TransactionController::class, 'getStatistics'])->name('supervisor.transactions.statistics');
    Route::get('/transactions/all', [TransactionController::class, 'all'])->name('transactions.all');

    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/supervisor/transactions', [TransactionController::class, 'supervisorView'])->name('supervisor.transactions');
    });
    
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/transactions', [TransactionController::class, 'all'])->name('admin.transactions');
    });
    
    // QR Scanner
    Route::get('/qr/scanner', [QRController::class, 'scanner'])->name('qr.scanner');
    Route::get('/qr/payment-scanner/{orderId?}', [QRController::class, 'paymentScanner'])->where('orderId', '[0-9a-f-]+')->name('qr.payment-scanner');
    
    // QR Profile scanning - for POS system (Admin/Guru only)
    Route::post('/qr/scan/profile', [QRController::class, 'getProfileAndHistory'])
        ->middleware('role:admin,guru')
        ->name('api.qr.profile');   
    
    // Categories - Admin only
    Route::middleware('role:admin')->group(function () {
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->where('id', '[0-9a-f-]+')->name('categories.edit');
        Route::put('/categories/{id}', [CategoryController::class, 'update'])->where('id', '[0-9a-f-]+')->name('categories.update');
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->where('id', '[0-9a-f-]+')->name('categories.destroy');
    });

    // Route untuk guru - rekap saldo
    Route::get('/admin/saldo-recap', [DashboardController::class, 'saldoRecap'])
        ->middleware('role:guru')
        ->name('admin.saldo-recap');
    
    // Route untuk guru - rekap transaksi (jika belum ada)
    Route::get('/guru/transactions', [TransactionController::class, 'guruIndex'])
        ->middleware('role:guru')
        ->name('guru.transactions');

    // Users Management - Admin only
    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::post('/users', [UserController::class, 'store'])->name('admin.users.store');
        Route::get('/users/{id}', [UserController::class, 'show'])->where('id', '[0-9a-f-]+')->name('admin.users.show');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->where('id', '[0-9a-f-]+')->name('admin.users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->where('id', '[0-9a-f-]+')->name('admin.users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->where('id', '[0-9a-f-]+')->name('admin.users.destroy');
    });

    // Logout
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| API Routes (with Sanctum authentication)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    Route::post('/qr/scan', [QRController::class, 'scan']);
    Route::post('/qr/transactions', [QRController::class, 'showTransactions']);
    Route::post('/orders/pay', [OrdersController::class, 'pay']);
    Route::get('/orders/{id}/payment', [OrdersController::class, 'showForPayment'])->where('id', '[0-9a-f-]+');
    Route::post('/transactions/by-qr', [TransactionController::class, 'showByQR']);
    Route::get('/shop', [ShopController::class, 'index']);
    Route::get('/shop/{id}', [ShopController::class, 'show'])->where('id', '[0-9a-f-]+');
    Route::get('/categories', [CategoryController::class, 'index']);
    
    // POS API Routes
    Route::post('/qr/profile', [OrdersController::class, 'getCustomerFromQR']);
    Route::get('/products/{productId}/stock', [OrdersController::class, 'getProductStock']);
    Route::post('/pos/orders', [OrdersController::class, 'storePOS']);
});

Route::post('/email/verification-notification', function () {
    return back()->with('status', 'verification-link-sent');
})->name('verification.send');