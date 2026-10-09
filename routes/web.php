<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\PenangananController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ClientExitDocumentController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ArchiveRevisionController;

// Lihat daftar klien — boleh diakses semua role
Route::middleware(['auth', 'role:petugas-piket|peksos|admin-rps|kepala-rps'])->group(function () {
    Route::get('/clients', [ClientController::class, 'index'])->name('clients.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/notifications/{id}/click', [NotificationController::class, 'click'])->name('notifications.click');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});

Route::middleware(['auth', 'role:kepala-rps'])->group(function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])
        ->name('users.toggle-active');
});

// Registrasi klien baru — khusus petugas-piket
// Route statis harus didefinisikan sebelum /clients/{client} agar tidak
// diproses sebagai parameter client bernilai "create".
Route::middleware(['auth', 'role:petugas-piket'])->group(function () {
    Route::get('/clients/create', [ClientController::class, 'create'])->name('clients.create');
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::get('/clients/{client}/edit', [ClientController::class, 'edit'])->name('clients.edit');
    Route::patch('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
});

// Detail klien — boleh diakses semua role
Route::middleware(['auth', 'role:petugas-piket|peksos|admin-rps|kepala-rps'])->group(function () {
    Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
    Route::get('/clients/{client}/foto', [ClientController::class, 'photo'])->name('clients.photo');
    Route::get('/clients/{client}/dokumen', [ClientController::class, 'document'])->name('clients.document');
});

Route::middleware(['auth', 'role:admin-rps'])->group(function () {
    Route::get('/penanganan', [PenangananController::class, 'dashboard'])->name('penanganan.dashboard');
    Route::get('/clients/{client}/penanganan', [PenangananController::class, 'index'])->name('penanganan.index');
    Route::post('/clients/{client}/penanganan', [PenangananController::class, 'store'])->name('penanganan.store');
    Route::post('/clients/{client}/exit-document', [ClientExitDocumentController::class, 'store'])
        ->name('exit-documents.store');
    Route::get('/exit-documents/mine', [ClientExitDocumentController::class, 'mine'])
        ->name('exit-documents.mine');
    Route::get('/exit-documents/{document}/edit', [ClientExitDocumentController::class, 'edit'])
        ->name('exit-documents.edit');
    Route::patch('/exit-documents/{document}/resubmit', [ClientExitDocumentController::class, 'resubmit'])
        ->name('exit-documents.resubmit');
});

Route::middleware(['auth', 'role:admin-rps|kepala-rps'])->group(function () {
    Route::get('/exit-documents/{document}/pdf', [ClientExitDocumentController::class, 'downloadPdf'])
        ->name('exit-documents.pdf');
});

Route::middleware(['auth', 'role:kepala-rps'])->group(function () {
    Route::get('/clients/{client}/revisi', [ArchiveRevisionController::class, 'edit'])->name('clients.revisi.edit');
    Route::put('/clients/{client}/revisi', [ArchiveRevisionController::class, 'update'])->name('clients.revisi.update');
});

Route::middleware(['auth', 'role:kepala-rps'])->group(function () {
    Route::get('/exit-documents/{document}/print', [ClientExitDocumentController::class, 'print'])
        ->name('exit-documents.print');
});

Route::middleware(['auth', 'role:kepala-rps'])->group(function () {
    Route::get('/exit-documents', [ClientExitDocumentController::class, 'index'])->name('exit-documents.index');
    Route::patch('/exit-documents/{document}/approve', [ClientExitDocumentController::class, 'approve'])
        ->name('exit-documents.approve');
    Route::patch('/exit-documents/{document}/reject', [ClientExitDocumentController::class, 'reject'])
        ->name('exit-documents.reject');
});

Route::middleware(['auth', 'role:kepala-rps'])->group(function () {
    Route::get('/exit-documents/{document}', [ClientExitDocumentController::class, 'show'])
        ->name('exit-documents.show');
});

Route::middleware(['auth', 'role:peksos'])->group(function () {
    Route::get('/assessment', [AssessmentController::class, 'index'])->name('assessment.index');
    Route::get('/clients/{client}/assessment', [AssessmentController::class, 'create'])->name('assessment.create');
    Route::post('/clients/{client}/assessment', [AssessmentController::class, 'store'])->name('assessment.store');
    Route::get('/clients/{client}/assessment/edit', [AssessmentController::class, 'edit'])->name('assessment.edit');
    Route::put('/clients/{client}/assessment', [AssessmentController::class, 'update'])->name('assessment.update');
});

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', function () {
    if (auth()->user()->hasRole('kepala-rps')) {
        return redirect()->route('statistik.index');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:kepala-rps'])->group(function () {
    Route::get('/statistik', [StatistikController::class, 'index'])->name('statistik.index');
    Route::get('/statistik/export', [StatistikController::class, 'export'])->name('statistik.export');
});

require __DIR__.'/auth.php';