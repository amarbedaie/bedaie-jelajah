<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Penggerak;
use App\Http\Controllers\Peserta;
use App\Http\Controllers\Public\ApplicationController;
use App\Http\Controllers\Public\EventController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\MapController;
use App\Http\Controllers\Public\PageController;
use App\Http\Controllers\Public\RecordingController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Laman awam
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/peta-jelajah', [MapController::class, 'index'])->name('peta');
Route::get('/peta-jelajah/{state:slug}', [MapController::class, 'show'])->name('peta.negeri');

Route::get('/program', [EventController::class, 'index'])->name('program.index');
Route::get('/jejak-jelajah', [EventController::class, 'past'])->name('jejak');
Route::get('/pilihan-program', [PageController::class, 'categories'])->name('kategori');
Route::get('/galeri-impak', [PageController::class, 'gallery'])->name('galeri');
Route::get('/rakan-penaja', [PageController::class, 'partners'])->name('rakan');
Route::get('/tentang', [PageController::class, 'about'])->name('tentang');
Route::get('/polisi-privasi', [PageController::class, 'privacy'])->name('privasi');
Route::get('/terma-penggunaan', [PageController::class, 'terms'])->name('terma');

// Halaman program yang dijana automatik.
Route::get('/jelajah/{state:slug}/{event:slug}', [EventController::class, 'show'])->name('jelajah.show');
Route::get('/j/{shortCode}', [EventController::class, 'short'])->name('jelajah.short');
Route::get('/jelajah/{state:slug}/{event:slug}/daftar', [EventController::class, 'register'])
    ->name('jelajah.daftar');

// Permohonan "Jemput BeDaie" + permintaan kawasan.
Route::get('/jemput-bedaie', [ApplicationController::class, 'create'])->name('jemput');
Route::get('/permohonan/{application:public_id}/berjaya', [ApplicationController::class, 'success'])
    ->name('jemput.berjaya');
Route::get('/bawa-bedaie-ke-kawasan-saya', [ApplicationController::class, 'interest'])->name('minat');

// Tiket peserta (pautan selamat, tanpa log masuk).
Route::get('/tiket/{registration:public_token}', [TicketController::class, 'show'])->name('tiket.show');
Route::get('/tiket/{registration:public_token}/batal', [TicketController::class, 'cancelForm'])
    ->name('tiket.cancel');
Route::delete('/tiket/{registration:public_token}/batal', [TicketController::class, 'cancel'])
    ->name('tiket.cancel.submit')->middleware('throttle:10,1');
Route::get('/tiket/{registration:public_token}/kalendar', [TicketController::class, 'calendar'])
    ->name('tiket.kalendar');

// Maklum balas selepas program.
Route::get('/maklum-balas/{registration:public_token}', [FeedbackController::class, 'show'])
    ->name('maklum-balas.show');

// Semakan & muat turun sijil.
Route::get('/sijil/semak', [CertificateController::class, 'search'])->name('sijil.semak');
Route::get('/sijil/semak/{number}', [CertificateController::class, 'verify'])->name('sijil.semak.show');
Route::get('/sijil/{certificate:public_id}/muat-turun', [CertificateController::class, 'download'])
    ->name('sijil.muat-turun');

// Callback pembayaran (tanpa CSRF — lihat bootstrap/app.php).
Route::match(['get', 'post'], '/bayaran/callback/{gateway}', [PaymentController::class, 'callback'])
    ->name('bayaran.callback');
Route::get('/bayaran/{payment:public_id}', [PaymentController::class, 'show'])->name('bayaran.show');

Route::view('/luar-talian', 'public.offline')->name('luar-talian');

// Rakaman program — akses melalui token tiket, sama seperti sijil.
Route::get('/tiket/{token}/rakaman', [RecordingController::class, 'index'])->name('rakaman.index');
Route::get('/tiket/{token}/rakaman/{recording}', [RecordingController::class, 'show'])->name('rakaman.show');
Route::get('/manifest.webmanifest', [PageController::class, 'manifest'])->name('pwa.manifest');

/*
|--------------------------------------------------------------------------
| Pengesahan
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/log-masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/log-masuk', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::get('/daftar-akaun', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar-akaun', [AuthController::class, 'register'])->middleware('throttle:6,1');

    Route::get('/lupa-kata-laluan', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/lupa-kata-laluan', [PasswordResetController::class, 'email'])
        ->name('password.email')->middleware('throttle:6,1');
    Route::get('/set-semula-kata-laluan/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/set-semula-kata-laluan', [PasswordResetController::class, 'update'])
        ->name('password.update')->middleware('throttle:6,1');
});

Route::post('/log-keluar', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Ruang Penggerak Jelajah
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:penggerak,admin'])
    ->prefix('penggerak')->name('penggerak.')->group(function () {
        Route::get('/', [Penggerak\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/permohonan', [Penggerak\ApplicationController::class, 'index'])->name('permohonan');
        Route::get('/permohonan/{application}', [Penggerak\ApplicationController::class, 'show'])
            ->name('permohonan.show');

        Route::get('/program', [Penggerak\EventController::class, 'index'])->name('program');
        Route::get('/program/{event}', [Penggerak\EventController::class, 'show'])->name('program.show');
        Route::get('/program/{event}/poster', [Penggerak\EventController::class, 'poster'])->name('program.poster');
        Route::get('/program/{event}/qr', [Penggerak\EventController::class, 'qr'])->name('program.qr');

        Route::get('/peserta', [Penggerak\ParticipantController::class, 'index'])->name('peserta');
        Route::get('/sijil-laporan', [Penggerak\ReportController::class, 'index'])->name('sijil');
        Route::view('/panduan', 'penggerak.guide')->name('panduan');
    Route::get('/profil', [Penggerak\ProfileController::class, 'edit'])->name('profil');
        Route::put('/profil', [Penggerak\ProfileController::class, 'update'])->name('profil.update');
        Route::get('/notifikasi', [Penggerak\NotificationController::class, 'index'])->name('notifikasi');
    });

/*
|--------------------------------------------------------------------------
| Ruang Peserta — Pasport Ilmu BeDaie
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->prefix('saya')->name('peserta.')->group(function () {
    Route::get('/', [Peserta\PassportController::class, 'index'])->name('dashboard');
    Route::get('/program', [Peserta\PassportController::class, 'events'])->name('program');
    Route::get('/sijil', [Peserta\PassportController::class, 'certificates'])->name('sijil');
    Route::get('/profil', [Peserta\ProfileController::class, 'edit'])->name('profil');
    Route::put('/profil', [Peserta\ProfileController::class, 'update'])->name('profil.update');
    Route::get('/notifikasi', [Peserta\NotificationController::class, 'index'])->name('notifikasi');
});

/*
|--------------------------------------------------------------------------
| Check-in QR — admin & penggerak bertugas
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,penggerak'])->group(function () {
    Route::get('/check-in/{event}', [CheckInController::class, 'scanner'])->name('checkin.scanner');
});

/*
|--------------------------------------------------------------------------
| Panel Admin
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/permohonan', [Admin\ApplicationController::class, 'index'])->name('permohonan');
    Route::get('/permohonan/{application}', [Admin\ApplicationController::class, 'show'])->name('permohonan.show');

    Route::get('/program', [Admin\EventController::class, 'index'])->name('program');
    Route::get('/program/{event}', [Admin\EventController::class, 'show'])->name('program.show');
    Route::get('/kalendar', [Admin\EventController::class, 'calendar'])->name('kalendar');

    Route::get('/penggerak', [Admin\PeopleController::class, 'mobilizers'])->name('penggerak');
    Route::get('/penggerak/{user}', [Admin\PeopleController::class, 'mobilizer'])->name('penggerak.show');
    Route::get('/peserta', [Admin\PeopleController::class, 'participants'])->name('peserta');

    Route::get('/kehadiran', [Admin\AttendanceController::class, 'index'])->name('kehadiran');
    Route::get('/kehadiran/{event}', [Admin\AttendanceController::class, 'show'])->name('kehadiran.show');

    Route::get('/sijil', [Admin\CertificateController::class, 'index'])->name('sijil');
    Route::get('/pembayaran', [Admin\CertificateController::class, 'payments'])->name('pembayaran');

    // Aliran keluar: sasaran yang dikejar oleh pasukan BeDaie sendiri.
    Route::get('/sasaran', [Admin\OutreachController::class, 'index'])->name('sasaran');
    Route::get('/sasaran/{target}', [Admin\OutreachController::class, 'show'])->name('sasaran.show');

    Route::get('/negeri', [Admin\CatalogController::class, 'states'])->name('negeri');
    Route::get('/permintaan-kawasan', [Admin\CatalogController::class, 'demand'])->name('permintaan');
    Route::get('/penceramah', [Admin\CatalogController::class, 'speakers'])->name('penceramah');
    Route::get('/kategori', [Admin\CatalogController::class, 'categories'])->name('kategori');

    Route::get('/galeri', [Admin\ContentController::class, 'gallery'])->name('galeri');
    Route::get('/rakan', [Admin\ContentController::class, 'partners'])->name('rakan');
    Route::get('/kandungan', [Admin\ContentController::class, 'pages'])->name('kandungan');

    Route::get('/laporan', [Admin\ReportController::class, 'index'])->name('laporan');
    Route::get('/laporan/program/{event}', [Admin\ReportController::class, 'event'])->name('laporan.program');
    Route::get('/laporan/eksport/{event}', [Admin\ReportController::class, 'export'])
        ->name('laporan.eksport')->middleware('can:export-participants');

    Route::get('/template-notifikasi', [Admin\SettingController::class, 'templates'])->name('template');
    Route::get('/log-notifikasi', [Admin\SettingController::class, 'notificationLog'])->name('log-notifikasi');
    Route::get('/tetapan', [Admin\SettingController::class, 'index'])->name('tetapan');
});
