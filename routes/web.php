<?php


use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Auth\OtpLoginController;
use App\Http\Controllers\Auth\OtpRegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ContactRequestController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\RazorpayController; // Add RazorpayController

// Admin Controllers
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminBookingsController;
use App\Http\Controllers\Admin\HomepageBannerController;
use App\Http\Controllers\Admin\VenueTimeSlotController;
use App\Http\Controllers\Admin\VenueDetailsController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\CommunityMembersController;
use App\Http\Controllers\Admin\CommunityMomentsController;
use App\Http\Controllers\Admin\AdminContactRequestController;
use App\Http\Controllers\Admin\MoneyBackController;
use App\Http\Controllers\Admin\PolicyController;
use App\Http\Controllers\Admin\BookingItemController;
use App\Http\Controllers\Admin\ConfigurationController;



// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/venues', [HomeController::class, 'venues'])->name('venues');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [ContactRequestController::class, 'sendContact'])->name('contact.send');
Route::get('/policies', [HomeController::class, 'policies'])->name('policies');

// OTP-Based Login and Registration Routes
Route::get('/otp-login', [OtpLoginController::class, 'showLoginForm'])->name('otp.login.form');
Route::post('/otp-login/send', [OtpLoginController::class, 'sendLoginOtp'])->name('otp.login.send');
Route::post('/otp-login', [OtpLoginController::class, 'verifyOtp'])->name('otp.login');

// Route for sending OTP for registration
Route::post('/otp-register/send', [OtpRegisterController::class, 'sendRegisterOtp'])->name('otp.register.send');
Route::get('/otp-register', [OtpRegisterController::class, 'showRegistrationForm'])->name('otp.register.form');
Route::post('/otp-register', [OtpRegisterController::class, 'register'])->name('otp.register');

Route::post('/razorpay/order', [RazorpayController::class, 'createOrder']);
Route::post('/razorpay/success', [RazorpayController::class, 'paymentSuccess']); // Optional


// Authentication Routes
Route::middleware('auth:web')->group(function () {
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/phone/otp/send', [ProfileController::class, 'sendPhoneOtp'])
        ->name('profile.phone.otp.send');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');  // Profile update after OTP verification

    // Book Hall Routes
    Route::get('/book_hall', [HomeController::class, 'bookHall'])->name('book.hall');
    Route::get('/book_hall/slots/{venue}/{date}', [BookingController::class, 'getSlots'])->name('book.slots');
    Route::post('/book_hall/complete', [BookingController::class, 'completeBooking'])->name('book.complete');
    Route::get('/booking/invoice/{payment}', [BookingController::class, 'downloadInvoice'])->name('book.invoice');
});

// Admin Routes (only for admin users)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware(['auth:admin'])->group(function () {

        Route::get('/configurations', [ConfigurationController::class, 'index'])->name('configurations.index');
Route::put('/configurations/update', [ConfigurationController::class, 'update'])->name('configurations.update');

        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');

        Route::get('/users', [AdminUserController::class, 'users'])->name('users');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'ShowProfile'])->name('users.edit');
        Route::post('/users/{user}/verify', [AdminUserController::class, 'verify'])->name('users.verify');
        Route::get('/users/export', [AdminUserController::class, 'export'])->name('users.export');


        Route::get('/bookings', [AdminBookingsController::class, 'bookings'])->name('bookings');
        Route::delete('/bookings/{booking}', [AdminBookingsController::class, 'destroy'])->name('bookings.destroy');
        Route::get('bookings/export', [AdminBookingsController::class, 'export'])->name('bookings.export');
        Route::prefix('bookings/{booking}/items')->name('bookings.items.')->group(function () {
            Route::get('/', [BookingItemController::class, 'index'])->name('index');

            Route::post('/bulk-upsert', [BookingItemController::class, 'bulkUpsert'])->name('bulk-upsert');

            Route::delete('/{item}', [BookingItemController::class, 'destroy'])->name('destroy');
        });
        Route::get('/venues', [VenueDetailsController::class, 'venues'])->name('venues');
        Route::get('/venues/create', [VenueDetailsController::class, 'create'])->name('venues.create');
        Route::post('/venues', [VenueDetailsController::class, 'store'])->name('venues.store');
        Route::get('/venues/{venue}/edit', [VenueDetailsController::class, 'edit'])->name('venues.edit');
        Route::put('/venues/{venue}', [VenueDetailsController::class, 'update'])->name('venues.update');
        Route::delete('/venues/{venue}', [VenueDetailsController::class, 'destroy'])->name('venues.destroy');

        // Scedule Routes
        Route::get('/schedule', [ScheduleController::class, 'index'])->name('schedule');
        Route::get('/schedule/events', [ScheduleController::class, 'events'])->name('schedule.events');
        Route::get('/schedule/slots', [ScheduleController::class, 'slots'])->name('schedule.slots');
        Route::post('/schedule', [ScheduleController::class, 'store'])->name('schedule.store');
        Route::get('/schedule/{booking}', [ScheduleController::class, 'show'])->name('schedule.show');
        Route::put('/schedule/{booking}', [ScheduleController::class, 'update'])->name('schedule.update');
        Route::delete('/schedule/{booking}', [ScheduleController::class, 'destroy'])->name('schedule.destroy');


        // Community members Routes 
        Route::get('/community-members', [CommunityMembersController::class, 'index'])->name('community-members');
        Route::get('/community-members/create', [CommunityMembersController::class, 'create'])->name('community-members.create');
        Route::post('/community-members', [CommunityMembersController::class, 'store'])->name('community-members.store');
        Route::get('/community-members/{member}/edit', [CommunityMembersController::class, 'edit'])->name('community-members.edit');
        Route::put('/community-members/{member}', [CommunityMembersController::class, 'update'])->name('community-members.update');
        Route::delete('/community-members/{member}', [CommunityMembersController::class, 'destroy'])->name('community-members.destroy');
        Route::post('/community-members/{member}/priority', [CommunityMembersController::class, 'updatePriority'])->name('community-members.priority');


        // Community Moments Routes
        Route::get('/community-moments', [CommunityMomentsController::class, 'index'])->name('community-moments');
        Route::get('/community-moments/create', [CommunityMomentsController::class, 'create'])->name('community-moments.create');
        Route::post('/community-moments', [CommunityMomentsController::class, 'store'])->name('community-moments.store');
        Route::get('/community-moments/{moment}/edit', [CommunityMomentsController::class, 'edit'])->name('community-moments.edit');
        Route::put('/community-moments/{moment}', [CommunityMomentsController::class, 'update'])->name('community-moments.update');
        Route::delete('/community-moments/{moment}', [CommunityMomentsController::class, 'destroy'])->name('community-moments.destroy');


        // Money Back Routes
        Route::get('/money-back', [MoneyBackController::class, 'index'])->name('money-back.index');
        Route::get('/money-back/create', [MoneyBackController::class, 'create'])->name('money-back.create');
        Route::post('/money-back', [MoneyBackController::class, 'store'])->name('money-back.store');
        Route::patch('/money-back/{id}/update-status', [MoneyBackController::class, 'updateStatus'])->name('money-back.update-status');


        // Contact Requests Routes
        Route::get('/contact-requests', [AdminContactRequestController::class, 'index'])->name('contact-requests');
        Route::get('/contact-requests/{request}', [AdminContactRequestController::class, 'show'])->name('contact-requests.show');
        Route::delete('/contact-requests/{request}', [AdminContactRequestController::class, 'destroy'])->name('contact-requests.destroy');


        // Policy Routes
        Route::get('/policy', [PolicyController::class, 'index'])->name('policy.index');
        Route::get('/policy/{id}/edit', [PolicyController::class, 'edit'])->name('policy.edit');
        Route::get('/policy/{id}', [PolicyController::class, 'show'])->name('policy.show');
        Route::put('/policy/{id}', [PolicyController::class, 'update'])->name('policy.update');

        Route::get('/homepage-banner', [HomepageBannerController::class, 'edit'])->name('banner.edit');
        Route::put('/homepage-banner', [HomepageBannerController::class, 'update'])->name('banner.update');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});

// User Routes (only for authenticated users with 'user' role)
Route::middleware(['auth', 'role:user'])->group(function () {
    // Additional routes for users with 'user' role (if any)
});

require __DIR__ . '/auth.php';
