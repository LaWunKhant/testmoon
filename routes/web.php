<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\MaintenanceRequestController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentReminderController;
use App\Http\Controllers\RentPaymentController;
use App\Http\Controllers\TenantController;
// Corrected use statement
use App\Jobs\SendRentReminderJob;
use App\Models\RentPayment;
use App\Models\Tenant;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome'); // *** Return the welcome view ***
});
// Route for the dashboard
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

// *** Add the route to handle the login form submission (POST) ***
// The login form's action in login.blade.php points to route('login'), so we use the same name here for the POST route
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['guest'])->group(function () {
    // Route to display the owner registration form
    Route::get('/owner/register', [AuthController::class, 'showOwnerRegistrationForm'])->name('owner.register'); // *** Add this GET route! ***

    Route::post('/owner/register', [AuthController::class, 'registerOwner'])->name('owner.register.post');
});

Route::middleware(['auth'])->group(function () {
    // Route for the owner dashboard
    Route::get('/owner/dashboard', [DashboardController::class, 'ownerDashboard'])->name('owner.dashboard');

});

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

Route::middleware('api')->group(function () {
    Route::resource('tenants', TenantController::class);
});

// Routes for rent payments
Route::get('/rent-payments', [RentPaymentController::class, 'index'])->name('rent_payments.index');
Route::get('/rent-payments/create', [RentPaymentController::class, 'create'])->name('rent_payments.create');
Route::post('/rent-payments', [RentPaymentController::class, 'store'])->name('rent_payments.store');
Route::get('/rent-payments/{rent_payment}', [RentPaymentController::class, 'show'])->name('rent_payments.show');
Route::get('/rent-payments/{rent_payment}/edit', [RentPaymentController::class, 'edit'])->name('rent_payments.edit');
Route::put('/rent-payments/{rent_payment}', [RentPaymentController::class, 'update'])->name('rent_payments.update');
Route::delete('/rent-payments/{rent_payment}', [RentPaymentController::class, 'destroy'])->name('rent_payments.destroy');
// Routes for payments

Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');

// Routes for maintenance requests
Route::get('/maintenance-requests', [MaintenanceRequestController::class, 'index'])->name('maintenance_requests.index');
Route::get('/maintenance-requests/create', [MaintenanceRequestController::class, 'create'])->name('maintenance_requests.create');
Route::post('/maintenance-requests', [MaintenanceRequestController::class, 'store'])->name('maintenance_requests.store');
Route::get('/maintenance-requests/{maintenance_request}', [MaintenanceRequestController::class, 'show'])->name('maintenance_requests.show');
Route::get('/maintenance-requests/{maintenance_request}/edit', [MaintenanceRequestController::class, 'edit'])->name('maintenance_requests.edit');
Route::put('/maintenance-requests/{maintenance_request}', [MaintenanceRequestController::class, 'update'])->name('maintenance_requests.update');
Route::delete('/maintenance_requests/{maintenance_request}', [MaintenanceRequestController::class, 'destroy'])->name('maintenance_requests.destroy');

// Routes for bills
Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
Route::get('/bills/create', [BillController::class, 'create'])->name('bills.create');
Route::post('/bills', [BillController::class, 'store'])->name('bills.store');
Route::get('/bills/{bill}', [BillController::class, 'show'])->name('bills.show');
Route::get('/bills/{bill}/edit', [BillController::class, 'edit'])->name('bills.edit');
Route::put('/bills/{bill}', [BillController::class, 'update'])->name('bills.update');
Route::delete('/bills/{bill}', [BillController::class, 'destroy'])->name('bills.destroy');

// Routes for owners houses
Route::get('/owner/houses/create', [App\Http\Controllers\HouseController::class, 'createForOwner'])->name('owner.houses.create');
Route::post('/owner/houses', [HouseController::class, 'storeForOwner'])->name('owner.houses.store');
Route::get('/owner/houses/{house}/edit', [HouseController::class, 'editForOwner'])->name('owner.houses.edit');
Route::put('/owner/houses/{house}', [HouseController::class, 'updateForOwner'])->name('owner.houses.update');
Route::delete('/owner/houses/{house}', [HouseController::class, 'destroyForOwner'])->name('owner.houses.destroy');

// Routes for owners tenants
Route::get('/owner/houses/{house}/tenants', [HouseController::class, 'showTenants'])->name('owner.houses.tenants.index');
Route::get('/owner/houses/{house}/tenants/create', [HouseController::class, 'createTenantForHouse'])->name('owner.houses.tenants.create');
Route::post('/owner/houses/{house}/tenants', [HouseController::class, 'storeTenantForHouse'])->name('owner.houses.tenants.store');
Route::get('/owner/tenants/{tenant}/edit', [HouseController::class, 'editTenant'])->name('owner.tenants.edit');
Route::put('/owner/tenants/{tenant}', [HouseController::class, 'updateTenant'])->name('owner.tenants.update');
Route::delete('/owner/tenants/{tenant}', [HouseController::class, 'destroyTenant'])->name('owner.tenants.destroy');
// Test email route
Route::get('/send-test-email', function () {
    // Try to find an existing tenant with the test email.
    $tenant = Tenant::where('email', 'test@example.com')->first();

    // If the tenant doesn't exist, create them.
    if (! $tenant) {
        $tenant = Tenant::factory()->create(['name' => 'Test Tenant', 'email' => 'test@example.com']);
    }

    // Create a dummy rent payment.
    $rentPayment = RentPayment::factory()->create(['tenant_id' => $tenant->id, 'amount' => 123.45, 'due_date' => now()->addDays(7)]);

    // Dispatch the job directly.
    dispatch(new SendRentReminderJob($tenant, $rentPayment));

    return 'Rent reminder job dispatched! Check your Mailtrap inbox.';
});

// Route to trigger monthly rent reminder job
Route::get('/dispatch-monthly-reminders-job', function () {
    // Get a House instance to pass to the job.
    // You'll need to adjust this to get the specific house you want.
    // For example, you might fetch the first house, or a house with a specific ID.
    $house = \App\Models\House::first(); // Or use a more specific query, like ->find(1)

    if ($house) {
        \App\Jobs\ProcessMonthlyRentReminders::dispatch($house);

        return 'Monthly rent reminder job dispatched for house: '.$house->id;
    } else {
        return 'No houses found to dispatch the monthly rent reminder job!';
    }
});

// Route for overdue payment reminders
Route::get('/send-overdue-reminders', [PaymentReminderController::class, 'sendOverdueReminders']);

Route::patch('/payments/{id}/status', [PaymentController::class, 'updatePaymentStatus']);

Route::get('/owner/tenants/{tenant}/compose-email', [HouseController::class, 'composeEmail'])->name('owner.tenants.compose-email');
Route::post('/owner/tenants/{tenant}/send-email', [HouseController::class, 'sendEmail'])->name('owner.tenants.send-email');
