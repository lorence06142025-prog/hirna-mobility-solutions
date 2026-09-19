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
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// All Protected Internal Routes (Require Active Login Session)
Route::middleware(['role'])->group(function () {

    // Dashboard & Data Import
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/import-data', [DashboardController::class, 'importData'])->name('import.data');
    Route::post('/import/csv', [\App\Http\Controllers\ImportController::class, 'importCsv'])->name('import.csv');
    Route::get('/switch-role', [AuthController::class, 'switchRole'])->name('switch-role');

    // Superadmin Security & User Access Control Center
    Route::get('/admin/security', [\App\Http\Controllers\SecurityController::class, 'index'])->middleware('role:admin')->name('admin.security.index');
    Route::post('/admin/security/unlock', [\App\Http\Controllers\SecurityController::class, 'unlockUser'])->middleware('role:admin')->name('admin.security.unlock');
    Route::post('/admin/security/users', [\App\Http\Controllers\SecurityController::class, 'storeUser'])->middleware('role:admin')->name('admin.security.users.store');
    Route::post('/admin/security/clear-logs', [\App\Http\Controllers\SecurityController::class, 'clearLogs'])->middleware('role:admin')->name('admin.security.clear-logs');

    // Fleet Management (FVM)
    Route::get('/vehicles', [FleetController::class, 'index'])->name('vehicles.index');
    Route::post('/vehicles', [FleetController::class, 'storeVehicle'])->middleware('role:fleet_manager')->name('vehicles.store');
    Route::put('/vehicles/{vehicle}', [FleetController::class, 'updateVehicle'])->middleware('role:fleet_manager')->name('vehicles.update');
    Route::post('/vehicles/{vehicle}/status', [FleetController::class, 'toggleStatus'])->middleware('role:fleet_manager,admin')->name('vehicles.toggle-status');
    Route::delete('/vehicles/{vehicle}', [FleetController::class, 'deleteVehicle'])->middleware('role:admin')->name('vehicles.destroy');
    Route::post('/fleet/assign-driver', [FleetController::class, 'assignDriver'])->middleware('role:fleet_manager,dispatcher')->name('fleet.assign-driver');
    Route::post('/fleet/drivers', [FleetController::class, 'storeDriver'])->middleware('role:fleet_manager')->name('fleet.drivers.store');

    // Vehicle Reservation & Dispatch System (VRDS)
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::post('/reservations', [ReservationController::class, 'store'])->middleware('role:dispatcher,operations')->name('reservations.store');
    Route::post('/reservations/{reservation}/status', [ReservationController::class, 'updateStatus'])->middleware('role:dispatcher')->name('reservations.update-status');
    Route::get('/reservations/check-availability', [ReservationController::class, 'checkAvailability'])->name('reservations.check-availability');

    // Trip Scheduling & Dispatch / Telemetry Monitoring
    Route::get('/trips', [TripController::class, 'index'])->name('trips.index');
    Route::post('/trips/plan-preview', [TripController::class, 'planRoutePreview'])->name('trips.plan-preview');
    Route::post('/trips', [TripController::class, 'store'])->middleware('role:dispatcher,fleet_manager,admin')->name('trips.store');
    Route::post('/trips/{trip}/start', [TripController::class, 'startTrip'])->middleware('role:dispatcher,operations,fleet_manager,admin')->name('trips.start');
    Route::post('/trips/{trip}/complete', [TripController::class, 'completeTrip'])->middleware('role:dispatcher,operations,fleet_manager,admin,finance')->name('trips.complete');
    Route::post('/trips/complete-demo', [TripController::class, 'completeDemoTrip'])->name('trips.complete-demo');
    Route::post('/trips/{trip}/simulate-gps', [TripController::class, 'simulateTelemetry'])->name('trips.simulate-gps');

    // Fuel Management & AI Predictions
    Route::get('/fuel', [FuelController::class, 'index'])->name('fuel.index');
    Route::post('/fuel', [FuelController::class, 'store'])->middleware('role:finance,fleet_manager')->name('fuel.store');
    Route::post('/fuel/predict', [FuelController::class, 'predict'])->name('fuel.predict');
    Route::post('/fuel/train', [FuelController::class, 'train'])->middleware('role:admin')->name('fuel.train');

    // Transport Cost Analysis & Optimization (TCAO)
    Route::get('/cost-analysis', [CostAnalysisController::class, 'index'])->name('cost-analysis.index');
    Route::get('/cost-analysis/export-csv', [CostAnalysisController::class, 'exportCsv'])->name('cost-analysis.export-csv');
    Route::get('/cost-analysis/export-pdf', [CostAnalysisController::class, 'exportPdf'])->name('cost-analysis.export-pdf');

    // Maintenance Management
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('/maintenance', [MaintenanceController::class, 'store'])->middleware('role:fleet_manager')->name('maintenance.store');
    Route::post('/maintenance/{record}/status', [MaintenanceController::class, 'updateStatus'])->middleware('role:fleet_manager')->name('maintenance.update-status');

    // Route Planning & Optimization (Module 6)
    Route::get('/routes', [\App\Http\Controllers\RouteController::class, 'index'])->name('routes.index');
    Route::post('/routes/plan', [\App\Http\Controllers\RouteController::class, 'planRoute'])->name('routes.plan');

});
