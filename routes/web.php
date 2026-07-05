<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FlightController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\AirlineController;
use App\Http\Controllers\AirplaneController;
use App\Http\Controllers\FlightCrudController;
use App\Http\Controllers\StaffController;

// 1. Guest / General Flight Search Routes
Route::get('/', [FlightController::class, 'index'])->name('home');
Route::get('/flights', [FlightController::class, 'search'])->name('flights.search');
Route::get('/api/airports/search', [FlightController::class, 'searchAirports'])->name('api.airports.search');

// Midtrans Webhook (CSRF Exempt in bootstrap/app.php)
Route::post('/api/payments/webhook', [PaymentController::class, 'webhook'])->name('payments.webhook');

// 2. Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// 3. Email Verification Routes
Route::middleware('auth')->group(function () {
    Route::get('/verify-email', [AuthController::class, 'showVerify'])->name('verification.notice');
    Route::post('/verify-email', [AuthController::class, 'verify'])->name('verification.verify');
    Route::post('/verify-email/resend', [AuthController::class, 'resendVerify'])->name('verification.resend');
});

// 4. Customer Verified Routes (Booking, History, E-Ticket, Payment Checkout)
Route::middleware(['auth', 'verified_email', 'role:customer'])->group(function () {
    Route::get('/bookings/new/{flight}', [BookingController::class, 'create'])->name('bookings.new');
    Route::post('/bookings/store', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/history', [BookingController::class, 'history'])->name('bookings.history');
    Route::get('/bookings/{booking}/checkout', [PaymentController::class, 'checkout'])->name('bookings.checkout');
    Route::get('/bookings/{booking}/check-payment', [PaymentController::class, 'checkPaymentStatus'])->name('bookings.check_payment');
});

// Shared Boarding Pass Viewer (Auth required, custom authorization check inside controller)
Route::get('/eticket/{booking_code}', [BookingController::class, 'eticket'])->name('eticket.show')->middleware('auth');

// 5. Manager Dashboard Routes
Route::middleware(['auth', 'role:manager'])->prefix('manager')->group(function () {
    Route::get('/dashboard', [ManagerController::class, 'index'])->name('manager.dashboard');
    Route::get('/report', [ManagerController::class, 'report'])->name('manager.report');
});

// 6. Admin Panel Routes (CRUD Master Data)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::resource('airports', AirportController::class);
    Route::resource('airlines', AirlineController::class);
    Route::resource('airplanes', AirplaneController::class);
    Route::resource('flights', FlightCrudController::class);
});

// 7. Staff Panel Routes (Passenger Manifests & Confirmations)
Route::middleware(['auth', 'role:staff'])->prefix('staff')->group(function () {
    Route::get('/dashboard', [StaffController::class, 'index'])->name('staff.dashboard');
    Route::get('/flights/{flight}/manifest', [StaffController::class, 'manifest'])->name('staff.flight_manifest');
    Route::post('/bookings/{booking}/confirm', [StaffController::class, 'confirmBooking'])->name('staff.bookings.confirm');
    Route::post('/bookings/{booking}/cancel', [StaffController::class, 'cancelBooking'])->name('staff.bookings.cancel');
});
