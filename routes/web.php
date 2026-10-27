<?php

use App\Http\Controllers\ArchiveRevisionController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ClientExitDocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PenangananController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/notifications/{id}/click', [NotificationController::class, 'click'])->name('notifications.click');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])
        ->name('notifications.read-all');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin|super-admin'])->group(function () {
    Route::get('/dashboard', [StatistikController::class, 'index'])
        ->middleware('verified')
        ->name('dashboard');

    Route::get('/statistik', fn () => redirect()->route('dashboard'))->name('statistik.index');
    Route::get('/statistik/export', [StatistikController::class, 'export'])->name('statistik.export');

    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::patch('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('/clients/{client}/foto', [ClientController::class, 'photo'])->name('clients.photo');
    Route::get('/clients/{client}/dokumen', [ClientController::class, 'document'])->name('clients.document');

    Route::get('/assessment', [AssessmentController::class, 'index'])->name('assessment.index');
    Route::get('/clients/{client}/assessment', [AssessmentController::class, 'create'])->name('assessment.create');
    Route::post('/clients/{client}/assessment', [AssessmentController::class, 'store'])->name('assessment.store');
    Route::get('/clients/{client}/assessment/edit', [AssessmentController::class, 'edit'])->name('assessment.edit');
    Route::put('/clients/{client}/assessment', [AssessmentController::class, 'update'])->name('assessment.update');

    Route::get('/penanganan', [PenangananController::class, 'dashboard'])->name('penanganan.dashboard');
    Route::get('/clients/{client}/penanganan', [PenangananController::class, 'index'])->name('penanganan.index');
    Route::post('/clients/{client}/penanganan', [PenangananController::class, 'store'])->name('penanganan.store');

    Route::post('/clients/{client}/exit-document', [ClientExitDocumentController::class, 'store'])
        ->name('exit-documents.store');
    Route::get('/exit-documents/mine', [ClientExitDocumentController::class, 'mine'])->name('exit-documents.mine');
    Route::get('/exit-documents/{document}/edit', [ClientExitDocumentController::class, 'edit'])
        ->name('exit-documents.edit');
    Route::patch('/exit-documents/{document}/resubmit', [ClientExitDocumentController::class, 'resubmit'])
        ->name('exit-documents.resubmit');
    Route::get('/exit-documents/{document}/pdf', [ClientExitDocumentController::class, 'downloadPdf'])
        ->name('exit-documents.pdf');
    Route::get('/exit-documents/{document}/print', [ClientExitDocumentController::class, 'print'])
        ->name('exit-documents.print');
    Route::get('/exit-documents/{document}', [ClientExitDocumentController::class, 'show'])
        ->name('exit-documents.show');

    Route::get('/clients/{client}/revisi', [ArchiveRevisionController::class, 'edit'])->name('clients.revisi.edit');
    Route::put('/clients/{client}/revisi', [ArchiveRevisionController::class, 'update'])->name('clients.revisi.update');

});

Route::middleware(['auth', 'role:super-admin'])->group(function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])
        ->name('users.toggle-active');
});

require __DIR__.'/auth.php';
