<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\EntryLogController;
use App\Http\Controllers\Admin\MonthlyLogController;

// -------------------------------------------------------
// PUBLIC ROUTE — QR Scanner (no login needed)
// -------------------------------------------------------
Route::get('/', [ScannerController::class, 'index'])->name('scanner.index');
Route::post('/scan', [ScannerController::class, 'scan'])->name('scanner.scan');

// -------------------------------------------------------
// ADMIN ROUTES — protected by auth middleware
// -------------------------------------------------------
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // -------------------------------------------------------
    // STATIC member routes MUST come before {member} routes
    // to prevent Laravel from treating them as a model ID
    // -------------------------------------------------------
    Route::get('/members/export', [MemberController::class, 'export'])->name('members.export');
    Route::get('/members/sample-template', [MemberController::class, 'sampleTemplate'])->name('members.sampleTemplate');
    Route::get('/members/print-all-cards', [MemberController::class, 'printAllCards'])->name('members.printAllCards');
    Route::get('/members/download-all-cards/palace', [MemberController::class, 'downloadPalaceCards'])->name('members.downloadPalaceCards');
    Route::get('/members/download-all-cards/pavilion', [MemberController::class, 'downloadPavilionCards'])->name('members.downloadPavilionCards');    Route::post('/members/import', [MemberController::class, 'import'])->name('members.import');
    Route::post('/members/clear-import-session', [MemberController::class, 'clearImportSession'])->name('members.clearImportSession');

    // Members — full CRUD
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::get('/members/create', [MemberController::class, 'create'])->name('members.create');
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');

    // Members — single member routes (must come after static routes)
    Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');
    Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
    Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');
    Route::patch('/members/{member}/toggle', [MemberController::class, 'toggle'])->name('members.toggle');
    Route::patch('/members/{member}/regenerate-qr', [MemberController::class, 'regenerateQr'])->name('members.regenerateQr');
    Route::post('/members/{member}/override-limit', [MemberController::class, 'overrideLimit'])->name('members.overrideLimit');
    Route::get('/members/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
    Route::get('/members/{member}/print-card', [MemberController::class, 'printCard'])->name('members.printCard');
    Route::get('/members/{member}/download-qr', [MemberController::class, 'downloadQr'])->name('members.downloadQr');

    // Entry logs
    Route::get('/entry-logs', [EntryLogController::class, 'index'])->name('entry-logs.index');

    // Monthly logs
    Route::get('/monthly-logs', [MonthlyLogController::class, 'index'])->name('monthly-logs.index');
    Route::get('/monthly-logs/export', [MonthlyLogController::class, 'export'])->name('monthly-logs.export');
});

// Breeze auth routes (login, logout, register etc.)
require __DIR__.'/auth.php';