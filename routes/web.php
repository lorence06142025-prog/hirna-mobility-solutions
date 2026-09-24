<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FleetController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\FuelController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\CostAnalysisController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Authentication Routes (Public)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/verify-otp', [AuthController::class, 'showOtp'])->name('otp.show');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('otp.verify');
Route::post('/resend-otp', [AuthController::class, 'resendOtp'])->name('otp.resend');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/download/credentials-doc', function () {
    $filePath = public_path('Hirna_System_Credentials_and_Role_Restrictions.docx');
    if (file_exists($filePath)) {
        return response()->download($filePath);
    }
    return redirect()->back()->with('error', 'Credentials document file not found.');
})->name('credentials.download');

Route::get('/download/security-doc', function () {
    $filePath = public_path('Hirna_System_Security_Documentation.docx');
    if (file_exists($filePath)) {
        return response()->download($filePath, 'Hirna_System_Security_Documentation.docx');
    }
    return redirect()->back()->with('error', 'Security documentation file not found.');
})->name('security.download');

// All Protected Internal Routes (Require Active Login Session)
Route::middleware(['role'])->group(function () {

    // Dashboard & Data Import (Accessible to all authenticated roles)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/import-data', [DashboardController::class, 'importData'])->middleware('role:admin,operations')->name('import.data');
    Route::post('/import/csv', [\App\Http\Controllers\ImportController::class, 'importCsv'])->middleware('role:admin,operations')->name('import.csv');
    Route::get('/switch-role', [AuthController::class, 'switchRole'])->name('switch-role');

    // User Profile, Security & Avatar Settings (Accessible to all authenticated roles)
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile/update', [\App\Http\Controllers\ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::post('/profile/avatar', [\App\Http\Controllers\ProfileController::class, 'updateAvatar'])->name('profile.update-avatar');
    Route::delete('/profile/avatar', [\App\Http\Controllers\ProfileController::class, 'removeAvatar'])->name('profile.remove-avatar');

    // Superadmin Security & User Access Control Center (Admin Only)
    Route::get('/admin/security', [\App\Http\Controllers\SecurityController::class, 'index'])->middleware('role:admin')->name('admin.security.index');
    Route::post('/admin/security/unlock', [\App\Http\Controllers\SecurityController::class, 'unlockUser'])->middleware('role:admin')->name('admin.security.unlock');
    Route::post('/admin/security/users', [\App\Http\Controllers\SecurityController::class, 'storeUser'])->middleware('role:admin')->name('admin.security.users.store');
    Route::post('/admin/security/clear-logs', [\App\Http\Controllers\SecurityController::class, 'clearLogs'])->middleware('role:admin')->name('admin.security.clear-logs');

    // Fleet Management (FVM) (Fleet Manager & Admin Only)
    Route::get('/vehicles', [FleetController::class, 'index'])->middleware('role:admin,fleet_manager')->name('vehicles.index');
    Route::post('/vehicles', [FleetController::class, 'storeVehicle'])->middleware('role:admin,fleet_manager')->name('vehicles.store');
    Route::put('/vehicles/{vehicle}', [FleetController::class, 'updateVehicle'])->middleware('role:admin,fleet_manager')->name('vehicles.update');
    Route::post('/vehicles/{vehicle}/status', [FleetController::class, 'toggleStatus'])->middleware('role:admin,fleet_manager')->name('vehicles.toggle-status');
    Route::delete('/vehicles/{vehicle}', [FleetController::class, 'deleteVehicle'])->middleware('role:admin')->name('vehicles.destroy');
    Route::post('/fleet/assign-driver', [FleetController::class, 'assignDriver'])->middleware('role:admin,fleet_manager,dispatcher')->name('fleet.assign-driver');
    Route::post('/fleet/drivers', [FleetController::class, 'storeDriver'])->middleware('role:admin,fleet_manager')->name('fleet.drivers.store');

    // Vehicle Reservation & Dispatch System (VRDS) (Admin, Fleet Manager, Dispatcher, Operations)
    Route::get('/reservations', [ReservationController::class, 'index'])->middleware('role:admin,fleet_manager,dispatcher,operations')->name('reservations.index');
    Route::post('/reservations', [ReservationController::class, 'store'])->middleware('role:admin,dispatcher,operations')->name('reservations.store');
    Route::post('/reservations/{reservation}/status', [ReservationController::class, 'updateStatus'])->middleware('role:admin,dispatcher')->name('reservations.update-status');
    Route::get('/reservations/check-availability', [ReservationController::class, 'checkAvailability'])->middleware('role:admin,fleet_manager,dispatcher,operations')->name('reservations.check-availability');

    // Trip Scheduling & Dispatch / Telemetry Monitoring (Admin, Fleet Manager, Dispatcher, Finance, Operations, Driver)
    Route::get('/trips', [TripController::class, 'index'])->middleware('role:admin,fleet_manager,dispatcher,finance,operations,driver')->name('trips.index');
    Route::post('/trips/plan-preview', [TripController::class, 'planRoutePreview'])->middleware('role:admin,fleet_manager,dispatcher,operations')->name('trips.plan-preview');
    Route::post('/trips', [TripController::class, 'store'])->middleware('role:admin,fleet_manager,dispatcher')->name('trips.store');
    Route::post('/trips/{trip}/start', [TripController::class, 'startTrip'])->middleware('role:admin,fleet_manager,dispatcher,operations')->name('trips.start');
    Route::post('/trips/{trip}/complete', [TripController::class, 'completeTrip'])->middleware('role:admin,fleet_manager,dispatcher,operations,finance')->name('trips.complete');
    Route::post('/trips/complete-demo', [TripController::class, 'completeDemoTrip'])->middleware('role:admin,fleet_manager,dispatcher,operations,finance')->name('trips.complete-demo');
    Route::post('/trips/{trip}/simulate-gps', [TripController::class, 'simulateTelemetry'])->middleware('role:admin,fleet_manager,dispatcher,operations')->name('trips.simulate-gps');

    // Fuel Management & AI Predictions (Admin, Fleet Manager, Finance)
    Route::get('/fuel', [FuelController::class, 'index'])->middleware('role:admin,fleet_manager,finance')->name('fuel.index');
    Route::post('/fuel', [FuelController::class, 'store'])->middleware('role:admin,fleet_manager,finance')->name('fuel.store');
    Route::post('/fuel/predict', [FuelController::class, 'predict'])->middleware('role:admin,fleet_manager,finance')->name('fuel.predict');
    Route::post('/fuel/train', [FuelController::class, 'train'])->middleware('role:admin')->name('fuel.train');

    // Transport Cost Analysis & Optimization (TCAO) (Admin, Finance, Operations)
    Route::get('/cost-analysis', [CostAnalysisController::class, 'index'])->middleware('role:admin,finance,operations')->name('cost-analysis.index');
    Route::get('/cost-analysis/export-csv', [CostAnalysisController::class, 'exportCsv'])->middleware('role:admin,finance,operations')->name('cost-analysis.export-csv');
    Route::get('/cost-analysis/export-pdf', [CostAnalysisController::class, 'exportPdf'])->middleware('role:admin,finance,operations')->name('cost-analysis.export-pdf');

    // Maintenance Management (Admin, Fleet Manager)
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->middleware('role:admin,fleet_manager')->name('maintenance.index');
    Route::post('/maintenance', [MaintenanceController::class, 'store'])->middleware('role:admin,fleet_manager')->name('maintenance.store');
    Route::post('/maintenance/{record}/status', [MaintenanceController::class, 'updateStatus'])->middleware('role:admin,fleet_manager')->name('maintenance.update-status');

    // Route Planning & Optimization (Module 6) (Admin, Fleet Manager, Dispatcher, Operations)
    Route::get('/routes', [\App\Http\Controllers\RouteController::class, 'index'])->middleware('role:admin,fleet_manager,dispatcher,operations')->name('routes.index');
    Route::post('/routes/plan', [\App\Http\Controllers\RouteController::class, 'planRoute'])->middleware('role:admin,fleet_manager,dispatcher,operations')->name('routes.plan');

});
