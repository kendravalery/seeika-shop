<?php

use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Admin\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\CustomerLoginController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Commerce\CartController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Commerce\CheckoutController;
use App\Http\Controllers\Admin\CollectionController;
use App\Http\Controllers\Shop\CustomerCollectionController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Shop\PageController;
use App\Http\Controllers\Commerce\PaymentController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Shop\SearchController;
use App\Http\Controllers\Shop\ProductController as ShopProductController;
use App\Http\Controllers\Customer\WishlistController;
use Illuminate\Http\Request;

Route::post('/timezone', function (Request $request) {
    $request->validate([
        'timezone' => ['required', 'timezone'],
    ]);

    session([
        'timezone' => $request->timezone,
    ]);

    if (auth()->check()) {
        auth()->user()->update([
                'timezone' => $request->timezone,
            ]);
    }
    return response()->json(['success' => true]);
})->name('timezone.update');

// SEARCH
Route::get('/search', [SearchController::class, 'index'])->name('search');

Route::get('/', [HomeController::class, 'index'])->name('home');

// SHOW PRODUCT
Route::get('/products/{slug}', [ShopProductController::class, 'show'])->name('products.show');

Route::get('/collections/new-items', [CustomerCollectionController::class, 'newItems'])->name('storefront.collections.new-items');

Route::get('/collections/{slug}', [CustomerCollectionController::class, 'show'])->name('storefront.collections.show');

Route::get('/collections', [CustomerCollectionController::class, 'index'])->name('storefront.collections');

//  MIDDLEWARE REDIRECT LOGINS
Route::middleware('redirect')->group(function () {
    Route::get('/login', [CustomerLoginController::class, 'loginForm'])->name('login');
    Route::post('/login', [CustomerLoginController::class, 'login']);

    Route::get('/register', [CustomerLoginController::class, 'registerForm'])->name('registerForm');
    Route::post('register', [CustomerLoginController::class, 'register'])->name('register');

    Route::get('/admin/login', [AdminLoginController::class, 'loginForm'])->name('admin.login');
    Route::post('/admin/login', [AdminLoginController::class, 'login'])->name('admin.login.post');
});
// ROLE CUSTOMER
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/my-account', [CustomerController::class, 'showAccount'])->name('customer.myAccount');

    Route::put('/address/update', [CustomerController::class, 'updateAddress'])->name('customer.address.update');

    Route::put('/profile/photo', [CustomerController::class, 'updatePhoto'])->name('customer.profile.update');

    Route::get('/manage-account', [CustomerController::class, 'manageAccount'])->name('customer.manage.account');

    Route::put('/manage-Address/update', [CustomerController::class, 'updateManageAccount'])->name('customer.manage.account.update');

    // Manage Address
    Route::post('/manage-account/store', [CustomerController::class, 'storeAddress'])->name('customer.Address.store');

    Route::patch('/manage-account/default/{id}', [CustomerController::class, 'defaultAddress'])->name('customer.Address.default');

    Route::delete('/manage-account/delete/{id}', [CustomerController::class, 'deleteAddress'])->name('customer.Address.delete');
    Route::put('/manage-account/update/{id}', [CustomerController::class, 'updateManageAddress'])->name('customer.Address.update');
    Route::get('/address', [CustomerController::class, 'showAddress'])->name('customer.address');

    // province - city -
    Route::get('/get-cities/{provinceCode}', [CustomerController::class, 'getCities']);
    Route::get('/get-districts/{cityCode}', [CustomerController::class, 'getDistricts']);
    Route::get('/get-villages/{districtCode}', [CustomerController::class, 'getVillages']);

    // Cart
    Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::patch('/cart/increase/{id}', [CartController::class, 'increase'])->name('cart.increase');
    Route::patch('/cart/decrease/{id}', [CartController::class, 'decrease'])->name('cart.decrease');

    // chekbox cart
    Route::patch('cart/select/{id}', [CartController::class, 'toggleSelect'])->name('cart.select');
    Route::patch('/cart/select-all', [CartController::class, 'selectAll'])->name('cart.selectAll');

    // checkout
    Route::Get('checkout', [CheckoutController::class, 'checkout'])->name('checkout');

    // Save checkout details before proceeding
    Route::post('/checkout/continue', [PaymentController::class, 'continueToPayment'])->name('checkout.continue');

    // payment
    Route::get('/payment', [PaymentController::class, 'index'])->name('payment.index');

    //  payment processing (add when implemented)
    // Route::post('/payment', [PaymentController::class, 'pay'])->name('payment.pay');

    // WISHLIST
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('Wishlist.index');
    Route::delete('/wishlist/delete/{id}', [WishlistController::class, 'wishlistDelete'])->name('wishlist.delete');
    Route::post('/wishlist/toggle/delete/{id}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
});

// FOOTER PART
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');

// ADMIN ROLE
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

        Route::resource('products', ProductController::class);

        Route::delete('product/image/{id}', [ProductController::class, 'deleteImage'])->name('products.image.delete');

        // CATEGORIES
        Route::resource('categories', CategoryController::class);

        // COLLECTIONS
        Route::resource('collections', CollectionController::class);

        // ORDERS (Just looking)
        Route::resource('orders', OrderController::class)->only(['index', 'show']);

        //  REVIEWS (moderasi)
        Route::resource('reviews', ReviewController::class)->only(['index', 'update', 'destroy']);

        Route::resource('banners', BannerController::class);
        Route::patch('banners/{id}/toggle/', [BannerController::class, 'toggle'])->name('banners.toggle');
    });

// 404
Route::fallback(function () {
    return view('error');
});

Route::post('logout', [CustomerLoginController::class, 'logout'])->name('logout');

// clean code makesure ,commit
