<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RapotController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CleaningKinerjaController;
use App\Http\Controllers\SecurityPatroliController;
use App\Http\Controllers\AdminDocumentationController;

// ======================================================================
// PUBLIC ROUTES
// ======================================================================
Route::get('/', fn() => view('welcome'))->name('welcome');
Route::get('/home', fn() => view('home'))->name('home');
Route::get('/login-pegawai', fn() => view('login-pegawai'))->name('login.pegawai');

// ======================================================================
// AUTH ROUTES (GUEST)
// ======================================================================
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

// ======================================================================
// AUTH ROUTES (AUTHENTICATED)
// ======================================================================
Route::middleware('auth')->group(function () {
    // ======================================================================
    // AUTH UTILITIES
    // ======================================================================
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // ======================================================================
    // DASHBOARD
    // ======================================================================
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    // ======================================================================
    // PROFILE
    // ======================================================================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ======================================================================
    // ATTENDANCE
    // ======================================================================
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::post('/attendance/checkout', [AttendanceController::class, 'checkout'])->name('attendance.checkout');
    Route::get('/attendance/history', [AttendanceController::class, 'history'])->name('attendance.history');

    // ======================================================================
    // USER DASHBOARDS (BASED ON ROLE)
    // ======================================================================
    Route::prefix('user')->group(function () {
        Route::get('/dashboard', [HomeController::class, 'userDashboard'])->name('user.dashboard');
        Route::get('/riwayat', [HomeController::class, 'userRiwayat'])->name('user.riwayat');
    });

   Route::prefix('security')->group(function () {

    Route::get('/dashboard', [HomeController::class, 'securityDashboard'])
        ->name('security.dashboard');

    // HALAMAN PATROLI
    Route::get('/patroli', [SecurityPatroliController::class, 'index'])
        ->name('security.patroli');

    // SIMPAN PATROLI
    Route::post('/patroli/store', [SecurityPatroliController::class, 'store'])
        ->name('security.patroli.store');

});

Route::prefix('cleaning')->group(function () {

    Route::get('/dashboard', [HomeController::class, 'cleaningDashboard'])
        ->name('cleaning.dashboard');

    Route::get('/kinerja', [CleaningKinerjaController::class, 'index'])
        ->name('cleaning.kinerja');

    Route::post('/kinerja', [CleaningKinerjaController::class, 'store'])
        ->name('cleaning.kinerja.store');
});

    Route::prefix('kantoran')->group(function () {
        Route::get('/dashboard', [HomeController::class, 'kantoranDashboard'])->name('kantoran.dashboard');
    });

    // ======================================================================
    // MANAGER ROUTES
    // ======================================================================
    Route::prefix('manager')->name('manager.')->group(function () {
        Route::get('/dashboard', [HomeController::class, 'managerDashboard'])->name('dashboard');
        Route::get('/laporan', [HomeController::class, 'laporanManager'])->name('laporan');
        Route::get('/log', [HomeController::class, 'managerLog'])->name('log');

        // LOG ABSENSI untuk Manager
        Route::get('/log-absen', [AttendanceController::class, 'logAbsensiManager'])->name('log-absen');
        Route::get('/log-absen/data', [AttendanceController::class, 'getLogAbsensiData'])->name('log-absen.data');
        Route::get('/log-absen/export', [AttendanceController::class, 'exportLogAbsensi'])->name('log-absen.export');

        // Pegawai Management
        Route::get('/pegawai', [HomeController::class, 'managerPegawai'])->name('pegawai');
        Route::post('/pegawai', [HomeController::class, 'managerTambahPegawai'])->name('pegawai.tambah');
        Route::put('/pegawai/{id}', [HomeController::class, 'managerUpdatePegawai'])->name('pegawai.update');
        Route::delete('/pegawai/{id}', [HomeController::class, 'managerHapusPegawai'])->name('pegawai.hapus');

        // Hapus pegawai dengan riwayat
        Route::delete('/pegawai/force-delete/{id}', [HomeController::class, 'managerForceDeletePegawai'])->name('pegawai.force-delete');

        // Pengumuman Management
        Route::get('/pengumuman', [AnnouncementController::class, 'indexManager'])->name('pengumuman');
        Route::post('/pengumuman', [AnnouncementController::class, 'storeManager'])->name('pengumuman.store');
        Route::delete('/pengumuman/{id}', [AnnouncementController::class, 'destroyManager'])->name('pengumuman.delete');
    });

    // ======================================================================
    // ADMIN ROUTES
    // ======================================================================
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        // Dashboard & Laporan
        Route::get('/dashboard', [HomeController::class, 'adminDashboard'])->name('dashboard');
        Route::get('/pegawai', [HomeController::class, 'kelolaPegawai'])->name('pegawai');
        Route::get('/laporan', [HomeController::class, 'laporanAdmin'])->name('laporan');

        // LOG ABSENSI untuk Admin
        Route::get('/log-absen', [AttendanceController::class, 'logAbsensiAdmin'])->name('log-absen');
        Route::get('/log-absen/data', [AttendanceController::class, 'getLogAbsensiData'])->name('log-absen.data');
        Route::get('/log-absen/export', [AttendanceController::class, 'exportLogAbsensi'])->name('log-absen.export');

        // ======================================================================
        // RAPOT MANAGEMENT
        // ======================================================================
        Route::prefix('rapot')->name('rapot.')->group(function () {
            // List pegawai untuk evaluasi
            Route::get('/', [RapotController::class, 'index'])->name('index');

            // Form evaluasi kinerja
            Route::get('/evaluasi/{user}', [RapotController::class, 'create'])->name('evaluasi.create');

            // Simpan evaluasi kinerja
            Route::post('/evaluasi/{user}', [RapotController::class, 'store'])->name('evaluasi.store');

            // Tampilkan detail evaluasi
            Route::get('/evaluasi/show/{rapot}', [RapotController::class, 'showEvaluasi'])->name('evaluasi.show');

            // Generate rapot otomatis (standar)
            Route::post('/generate/{user}', [RapotController::class, 'generateRapot'])->name('generate');

            // Detail rapot umum
            Route::get('/{rapot}', [RapotController::class, 'show'])->name('show');

            // Edit rapot
            Route::get('/{rapot}/edit', [RapotController::class, 'edit'])->name('edit');

            // Update rapot
            Route::put('/{rapot}', [RapotController::class, 'update'])->name('update');

            // Hapus rapot
            Route::delete('/{rapot}', [RapotController::class, 'destroy'])->name('delete');

            // Export PDF
            Route::get('/{rapot}/export-pdf', [RapotController::class, 'exportPDF'])->name('export.pdf');
        });

        // ======================================================================
        // PENGUMUMAN MANAGEMENT
        // ======================================================================
        Route::prefix('pengumuman')->name('pengumuman.')->group(function () {
            Route::get('/', [AnnouncementController::class, 'indexAdmin'])->name('index');
            Route::post('/', [AnnouncementController::class, 'store'])->name('store');
            Route::delete('/{id}', [AnnouncementController::class, 'destroy'])->name('delete');
        });

        // Alias untuk route admin.pengumuman
        Route::get('/pengumuman', [AnnouncementController::class, 'indexAdmin'])->name('pengumuman');

        // ======================================================================
        // DOKUMENTASI CLEANING & SECURITY
        // ======================================================================
        Route::get('/dokumentasi', [AdminDocumentationController::class, 'index'])->name('dokumentasi');
    });

    // ======================================================================
    // SHARED USER ROUTES (Semua user bisa akses)
    // ======================================================================

    // RAPOT USER
    Route::get('/rapot-saya', [RapotController::class, 'indexUser'])->name('rapot.saya');
    Route::get('/rapot-user', [RapotController::class, 'indexUser'])->name('rapot.user');

    // Detail rapot untuk user
    Route::get('/rapot/{rapot}', [RapotController::class, 'show'])->name('rapot.show');
    Route::get('/rapot-evaluasi/{rapot}', [RapotController::class, 'showEvaluasi'])->name('rapot.evaluasi.show');

    // PENGUMUMAN USER
    Route::get('/pengumuman', [AnnouncementController::class, 'showToUsers'])->name('pengumuman.user');

    // ======================================================================
    // EXPORT ROUTES (Admin & Manager only)
    // ======================================================================
    Route::middleware(['auth'])->prefix('export')->group(function () {
        // Cek hak akses akan dilakukan di Controller
        Route::get('/all', [HomeController::class, 'exportAllCsv'])->name('export.all');
        Route::get('/all-data', [HomeController::class, 'exportAllCsvAllTime'])->name('export.all.everything');
        Route::get('/sheet-absen', [HomeController::class, 'exportSheetAbsen'])->name('export.sheet.absen');
    });

    // Test route
    Route::get('/test-perhitungan', [RapotController::class, 'testPerhitungan']);
});
