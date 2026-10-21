<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\FitnessController;
use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberPortalController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\OperationsController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TenantContextController;
use App\Http\Controllers\TrainerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
});

Route::middleware(['auth', 'tenant'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/tenant-context', [TenantContextController::class, 'update'])->name('tenant-context.update');
    Route::resource('branches', BranchController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('leads', LeadController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('/leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.status.update');
    Route::resource('members', MemberController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('/members/{member}/photo', [MemberController::class, 'updatePhoto'])->name('members.photo.update');
    Route::resource('trainers', TrainerController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('/trainers/{trainer}/status', [TrainerController::class, 'updateStatus'])->name('trainers.status.update');
    Route::post('/trainers/{trainer}/payments', [TrainerController::class, 'storePayment'])->name('trainers.payments.store');
    Route::resource('plans', PlanController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('batches', BatchController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/check-ins', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::post('/memberships', [MembershipController::class, 'store'])->name('memberships.store');
    Route::post('/membership-freezes', [MembershipController::class, 'freeze'])->name('memberships.freeze');
    Route::post('/membership-renewals', [MembershipController::class, 'renew'])->name('memberships.renew');
    Route::get('/member-portal', MemberPortalController::class)->name('member-portal');
    Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
    Route::get('/reports/finance', [ReportController::class, 'finance'])->name('reports.finance');
    Route::get('/exports/members.csv', [ReportController::class, 'membersCsv'])->name('exports.members');
    Route::post('/finance/invoices', [FinanceController::class, 'storeInvoice'])->name('finance.invoices.store');
    Route::post('/finance/payments', [FinanceController::class, 'storePayment'])->name('finance.payments.store');
    Route::get('/fitness', [FitnessController::class, 'index'])->name('fitness.index');
    Route::post('/fitness/goals', [FitnessController::class, 'storeGoal'])->name('fitness.goals.store');
    Route::post('/fitness/workouts', [FitnessController::class, 'storeWorkout'])->name('fitness.workouts.store');
    Route::post('/fitness/meal-plans', [FitnessController::class, 'storeMealPlan'])->name('fitness.meal-plans.store');
    Route::post('/fitness/events', [FitnessController::class, 'storeEvent'])->name('fitness.events.store');
    Route::get('/operations', [OperationsController::class, 'index'])->name('operations.index');
    Route::post('/operations', [OperationsController::class, 'store'])->name('operations.store');
    Route::post('/integrations/payment-intents', [IntegrationController::class, 'paymentIntent'])->name('integrations.payment-intents');
    Route::post('/integrations/refunds', [IntegrationController::class, 'refund'])->name('integrations.refunds');
    Route::post('/integrations/einvoice', [IntegrationController::class, 'eInvoice'])->name('integrations.einvoice');
    Route::post('/integrations/notifications', [IntegrationController::class, 'notification'])->name('integrations.notifications');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::post('/integrations/razorpay/webhook', [IntegrationController::class, 'paymentWebhook'])->name('integrations.razorpay.webhook');
