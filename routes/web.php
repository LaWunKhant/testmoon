<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HouseController;
// Ensure this class exists in the specified namespace
use App\Http\Controllers\MaintenanceRequestController; // Ensure this class exists in the specified namespace
use App\Http\Controllers\RentPaymentController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

// Route for the dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Routes for houses
Route::get('/houses', [HouseController::class, 'index'])->name('houses.index');
Route::get('/houses/create', [HouseController::class, 'create'])->name('houses.create');
Route::post('/houses', [HouseController::class, 'store'])->name('houses.store');
Route::get('/houses/{house}', [HouseController::class, 'show'])->name('houses.show'); // Route model binding
Route::get('/houses/{house}/edit', [HouseController::class, 'edit'])->name('houses.edit'); // Route model binding
Route::put('/houses/{house}', [HouseController::class, 'update'])->name('houses.update'); // Route model binding
Route::delete('/houses/{house}', [HouseController::class, 'destroy'])->name('houses.destroy'); // Route model binding

// Routes for tenants
Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
Route::get('/tenants/{tenant}', [TenantController::class, 'show'])->name('tenants.show');
Route::get('/tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('tenants.edit');
Route::put('/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');

// Routes for rent payments
Route::get('/rent-payments', [RentPaymentController::class, 'index'])->name('rent_payments.index');
Route::get('/rent-payments/create', [RentPaymentController::class, 'create'])->name('rent_payments.create');
Route::post('/rent-payments', [RentPaymentController::class, 'store'])->name('rent_payments.store');
Route::get('/rent-payments/{rent_payment}', [RentPaymentController::class, 'show'])->name('rent_payments.show');
Route::get('/rent-payments/{rent_payment}/edit', [RentPaymentController::class, 'edit'])->name('rent_payments.edit');
Route::put('/rent-payments/{rent_payment}', [RentPaymentController::class, 'update'])->name('rent_payments.update');
Route::delete('/rent-payments/{rent_payment}', [RentPaymentController::class, 'destroy'])->name('rent_payments.destroy');

// Routes for maintenance requests
Route::get('/maintenance-requests', [MaintenanceRequestController::class, 'index'])->name('maintenance_requests.index'); // Ensure MaintenanceRequestController has an 'index' method
Route::get('/maintenance-requests/create', [MaintenanceRequestController::class, 'create'])->name('maintenance_requests.create');
Route::post('/maintenance-requests', [MaintenanceRequestController::class, 'store'])->name('maintenance_requests.store');
Route::get('/maintenance-requests/{maintenance_request}', [MaintenanceRequestController::class, 'show'])->name('maintenance_requests.show');
Route::get('/maintenance-requests/{maintenance_request}/edit', [MaintenanceRequestController::class, 'edit'])->name('maintenance_requests.edit');
Route::put('/maintenance-requests/{maintenance_request}', [MaintenanceRequestController::class, 'update'])->name('maintenance_requests.update');
Route::delete('/maintenance-requests/{maintenance_request}', [MaintenanceRequestController::class, 'destroy'])->name('maintenance_requests.destroy');

// Routes for bills (if you have them)
Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
Route::get('/bills/create', [BillController::class, 'create'])->name('bills.create');
Route::post('/bills', [BillController::class, 'store'])->name('bills.store');
Route::get('/bills/{bill}', [BillController::class, 'show'])->name('bills.show');
Route::get('/bills/{bill}/edit', [BillController::class, 'edit'])->name('bills.edit');
Route::put('/bills/{bill}', [BillController::class, 'update'])->name('bills.update');
Route::delete('/bills/{bill}', [BillController::class, 'destroy'])->name('bills.destroy');
