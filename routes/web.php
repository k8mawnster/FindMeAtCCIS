<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;

// --- Guest Routes (Login, Register, Forgot Password) ---
Route::group([], function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot.password');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot.password.post');
    Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset.post');
});

// --- Logout ---
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- Student Routes ---
Route::middleware(['student', 'archived'])->prefix('student')->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
    Route::get('/report-lost', [StudentController::class, 'reportLost'])->name('student.report.lost');
    Route::post('/report-lost', [StudentController::class, 'storeReportLost'])->name('student.report.lost.post');
    Route::get('/report-found', [StudentController::class, 'reportFound'])->name('student.report.found');
    Route::post('/report-found', [StudentController::class, 'storeReportFound'])->name('student.report.found.post');
    Route::get('/search', [StudentController::class, 'search'])->name('student.search');
    Route::get('/my-activity', [StudentController::class, 'myActivity'])->name('student.activity');
    Route::get('/settings', [StudentController::class, 'settings'])->name('student.settings');
    Route::put('/settings', [StudentController::class, 'updateSettings'])->name('student.settings.update');
    Route::put('/settings/password', [StudentController::class, 'updatePassword'])->name('student.settings.password');
});

// --- Admin Routes ---
Route::middleware(['admin', 'archived'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/pending-posts', [AdminController::class, 'pendingPosts'])->name('admin.pending');
    Route::get('/approved-posts', [AdminController::class, 'approvedPosts'])->name('admin.approved');
    Route::get('/rejected-posts', [AdminController::class, 'rejectedPosts'])->name('admin.rejected');
    Route::get('/claim-forms', [AdminController::class, 'claimForms'])->name('admin.claims');
    Route::get('/to-be-claimed', [AdminController::class, 'toBeClaimed'])->name('admin.tobeclaimed');
    Route::get('/resolved-cases', [AdminController::class, 'resolvedCases'])->name('admin.resolved');
    Route::get('/user-management', [AdminController::class, 'userManagement'])->name('admin.users');
});

// --- API Routes (AJAX calls) ---
Route::middleware(['archived'])->prefix('api')->group(function () {
    Route::get('/notifications', [StudentController::class, 'notifications'])->name('api.notifications');
    Route::post('/posts/action', [AdminController::class, 'postAction'])->name('api.post.action');
    Route::post('/claims/action', [AdminController::class, 'claimAction'])->name('api.claim.action');
    Route::delete('/posts/{id}', [StudentController::class, 'cancelPost'])->name('api.post.cancel');
    Route::post('/claims/submit', [StudentController::class, 'submitClaim'])->name('api.claim.submit');
    Route::post('/users/action', [AdminController::class, 'userAction'])->name('api.user.action');
    Route::put('/profile', [StudentController::class, 'updateProfile'])->name('api.profile.update');
});

Route::post('/webhook/resend', [App\Http\Controllers\WebhookController::class, 'handle'])->name('webhook.resend');