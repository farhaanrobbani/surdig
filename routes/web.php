<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\DownloadItemController;
use App\Http\Controllers\Admin\KritikSaranController;
use App\Http\Controllers\Admin\KuaActivityThemeController;
use App\Http\Controllers\Admin\KuaDailyController;
use App\Http\Controllers\Admin\KuaSettingController;
use App\Http\Controllers\Admin\LetterController;
use App\Http\Controllers\Admin\LetterTemplateController;
use App\Http\Controllers\Admin\LetterTypeController;
use App\Http\Controllers\Admin\MarriageAnnouncementController;
use App\Http\Controllers\Admin\MarriageServiceController;
use App\Http\Controllers\Admin\NavbarController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ReligiousServiceController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\SubmissionAdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WakafServiceController;
use App\Http\Controllers\AnnouncementPublicController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeployController;
use App\Http\Controllers\DownloadPublicController;
use App\Http\Controllers\FaviconController;
use App\Http\Controllers\KritikSaranPublicController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\MarriageAnnouncementPublicController;
use App\Http\Controllers\MarriageServicePublicController;
use App\Http\Controllers\NavbarPageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReligiousServicePublicController;
use App\Http\Controllers\StaffActivityController;
use App\Http\Controllers\StaffExportController;
use App\Http\Controllers\StaffPublicController;
use App\Http\Controllers\StaffTemplateController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\WakafServicePublicController;
use App\Http\Controllers\WelcomeController;
use App\Http\Middleware\RedirectLegacyPengumuman;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::get('/cari-akta', [LayananController::class, 'cariAkta'])->name('layanan.cari-akta');
Route::get('/wakaf', [WakafServicePublicController::class, 'index'])->name('layanan.wakaf');
Route::get('/keagamaan', [ReligiousServicePublicController::class, 'index'])->name('layanan.keagamaan');

Route::get('/favicon.ico', FaviconController::class);

Route::middleware(RedirectLegacyPengumuman::class)->group(function () {
    Route::get('/pengumuman', [AnnouncementPublicController::class, 'index'])->name('pengumuman.index');
    Route::get('/pengumuman/{announcement}', [AnnouncementPublicController::class, 'show'])->name('pengumuman.show');
});

Route::get('/daftar-pegawai', [StaffPublicController::class, 'index'])->name('pegawai.index');

Route::get('/unduhan', [DownloadPublicController::class, 'index'])->name('unduhan.index');
Route::get('/unduhan/{downloadItem}/unduh', [DownloadPublicController::class, 'download'])->name('unduhan.unduh')->middleware('throttle:10,1');

Route::get('/pernikahan', [MarriageServicePublicController::class, 'index'])->name('pernikahan.index');

Route::get('/pengumuman-nikah/arsip', [MarriageAnnouncementPublicController::class, 'arsip'])->name('pengumuman-nikah.arsip');
Route::get('/pengumuman-nikah/{announcement}', [MarriageAnnouncementPublicController::class, 'show'])->name('pengumuman-nikah.show');
Route::get('/pengumuman-nikah', [MarriageAnnouncementPublicController::class, 'index'])->name('pengumuman-nikah.index');

Route::get('/permohonan', [SubmissionController::class, 'create'])->name('permohonan.create');
Route::post('/permohonan', [SubmissionController::class, 'store'])->name('permohonan.store')->middleware('throttle:5,1');
Route::get('/permohonan/unduh/{token}', [SubmissionController::class, 'download'])->name('permohonan.download')->middleware('throttle:10,1');
Route::get('/permohonan/track/{token}', [SubmissionController::class, 'track'])->name('permohonan.track');
Route::get('/permohonan/cek', [SubmissionController::class, 'cek'])->name('permohonan.cek');
Route::post('/permohonan/cek', [SubmissionController::class, 'cekSubmit'])->name('permohonan.cek.submit');
Route::get('/permohonan/sukses', [SubmissionController::class, 'sukses'])->name('permohonan.sukses');

Route::get('/kritik-saran', [KritikSaranPublicController::class, 'create'])->name('kritik-saran.create');
Route::post('/kritik-saran', [KritikSaranPublicController::class, 'store'])->name('kritik-saran.store')->middleware('throttle:5,1');

Route::post('/deploy', DeployController::class)->middleware('throttle:6,1');

Route::middleware(['auth', 'verified', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('letters', LetterController::class);
    Route::post('letters/{letter}/ajukan', [LetterController::class, 'ajukan'])->name('letters.ajukan');
    Route::post('letters/{letter}/setujui', [LetterController::class, 'setujui'])->name('letters.setujui')->middleware('role:kepala');
    Route::get('letters/{letter}/reject', [LetterController::class, 'reject'])->name('letters.reject')->middleware('role:kepala');
    Route::post('letters/{letter}/tolak', [LetterController::class, 'tolak'])->name('letters.tolak')->middleware('role:kepala');
    Route::post('letters/{letter}/terbitkan', [LetterController::class, 'terbitkan'])->name('letters.terbitkan');
    Route::get('letters/{letter}/pdf', [LetterController::class, 'pdf'])->name('letters.pdf');
    Route::get('letters/{letter}/preview', [LetterController::class, 'preview'])->name('letters.preview');

    Route::get('/submissions', [SubmissionAdminController::class, 'index'])->name('submissions.index');
    Route::get('/submissions/buat', [SubmissionAdminController::class, 'create'])->name('submissions.create');
    Route::post('/submissions', [SubmissionAdminController::class, 'store'])->name('submissions.store');
    Route::get('/submissions/{submission}', [SubmissionAdminController::class, 'show'])->name('submissions.show');
    Route::put('/submissions/{submission}', [SubmissionAdminController::class, 'updateStatus'])->name('submissions.update');
    Route::delete('/submissions/{submission}', [SubmissionAdminController::class, 'destroy'])->name('submissions.destroy');
    Route::post('/submissions/{submission}/buat-surat', [SubmissionAdminController::class, 'buatSurat'])->name('submissions.buat-surat');
    Route::get('/submissions/{submission}/cetak-permohonan', [SubmissionAdminController::class, 'cetakPermohonan'])->name('submissions.cetak-permohonan');

    Route::get('/kelola-kritik-saran', [KritikSaranController::class, 'index'])->name('kritik-saran.index');
    Route::get('/kelola-kritik-saran/{kritikSaran}', [KritikSaranController::class, 'show'])->name('kritik-saran.show');
    Route::delete('/kelola-kritik-saran/{kritikSaran}', [KritikSaranController::class, 'destroy'])->name('kritik-saran.destroy');

    Route::get('/lapkin/kegiatan', [StaffActivityController::class, 'index'])->name('kegiatan.index');
    Route::post('/lapkin/kegiatan', [StaffActivityController::class, 'store'])->name('kegiatan.store');
    Route::get('/lapkin/kegiatan/{kegiatan}/edit', [StaffActivityController::class, 'edit'])->name('kegiatan.edit');
    Route::put('/lapkin/kegiatan/{kegiatan}', [StaffActivityController::class, 'update'])->name('kegiatan.update');
    Route::delete('/lapkin/kegiatan/{kegiatan}', [StaffActivityController::class, 'destroy'])->name('kegiatan.destroy');
    Route::get('/lapkin/kegiatan/export/laporan-kinerja', [StaffExportController::class, 'laporanKinerja'])->name('kegiatan.export.laporan');
    Route::get('/lapkin/kegiatan/export/rekap', [StaffExportController::class, 'rekap'])->name('kegiatan.export.rekap');
    Route::get('/lapkin/export', [StaffExportController::class, 'index'])->name('kegiatan.export.index');
    Route::get('/lapkin/template-kalimat', [StaffTemplateController::class, 'index'])->name('kegiatan.templates.index');
    Route::post('/lapkin/template-kalimat', [StaffTemplateController::class, 'store'])->name('kegiatan.templates.store');
});

Route::middleware(['auth', 'verified', 'active', 'role:operator,kepala'])->group(function () {
    Route::resource('letter-types', LetterTypeController::class)->except('show');
    Route::get('letter-types/{letterType}/clone', [LetterTypeController::class, 'clone'])->name('letter-types.clone');
    Route::resource('letter-templates', LetterTemplateController::class)->except('show');
    Route::resource('staff', StaffController::class)->except('show');
    Route::resource('announcements', AnnouncementController::class)->except('show');
    Route::post('announcements/gambar', [AnnouncementController::class, 'uploadImage'])->name('announcements.gambar');
    Route::resource('marriage-announcements', MarriageAnnouncementController::class)->except('show');
    Route::resource('download-items', DownloadItemController::class)->except('show');

    Route::get('/lapkin/master', [KuaDailyController::class, 'index'])->name('kua-daily.index');
    Route::get('/lapkin/master/create', [KuaDailyController::class, 'create'])->name('kua-daily.create');
    Route::post('/lapkin/master', [KuaDailyController::class, 'store'])->name('kua-daily.store');
    Route::get('/lapkin/master/{kuaDaily}/edit', [KuaDailyController::class, 'edit'])->name('kua-daily.edit');
    Route::put('/lapkin/master/{kuaDaily}', [KuaDailyController::class, 'update'])->name('kua-daily.update');
    Route::delete('/lapkin/master/{kuaDaily}', [KuaDailyController::class, 'destroy'])->name('kua-daily.destroy');
    Route::get('/lapkin/tema-pekerjaan', [KuaActivityThemeController::class, 'index'])->name('kua-themes.index');
    Route::get('/lapkin/tema-pekerjaan/create', [KuaActivityThemeController::class, 'create'])->name('kua-themes.create');
    Route::post('/lapkin/tema-pekerjaan', [KuaActivityThemeController::class, 'store'])->name('kua-themes.store');
    Route::get('/lapkin/tema-pekerjaan/{kuaActivityTheme}/edit', [KuaActivityThemeController::class, 'edit'])->name('kua-themes.edit');
    Route::put('/lapkin/tema-pekerjaan/{kuaActivityTheme}', [KuaActivityThemeController::class, 'update'])->name('kua-themes.update');
    Route::delete('/lapkin/tema-pekerjaan/{kuaActivityTheme}', [KuaActivityThemeController::class, 'destroy'])->name('kua-themes.destroy');
    Route::post('/lapkin/tema-pekerjaan/{kuaActivityTheme}/move', [KuaActivityThemeController::class, 'move'])->name('kua-themes.move');
});

Route::middleware(['auth', 'verified', 'active', 'role:kepala'])->group(function () {
    Route::get('/navbar', [NavbarController::class, 'index'])->name('navbar.index');
    Route::get('/navbar/create', [NavbarController::class, 'create'])->name('navbar.create');
    Route::post('/navbar', [NavbarController::class, 'store'])->name('navbar.store');
    Route::get('/navbar/{navbarItem}/edit', [NavbarController::class, 'edit'])->name('navbar.edit');
    Route::put('/navbar/{navbarItem}', [NavbarController::class, 'update'])->name('navbar.update');
    Route::delete('/navbar/{navbarItem}', [NavbarController::class, 'destroy'])->name('navbar.destroy');
    Route::get('/navbar/{navbarItem}/sub/create', [NavbarController::class, 'createSub'])->name('navbar.sub.create');
    Route::post('/navbar/{navbarItem}/sub', [NavbarController::class, 'storeSub'])->name('navbar.sub.store');
    Route::get('/navbar/sub/{subItem}/edit', [NavbarController::class, 'editSub'])->name('navbar.sub.edit');
    Route::put('/navbar/sub/{subItem}', [NavbarController::class, 'updateSub'])->name('navbar.sub.update');
    Route::delete('/navbar/sub/{subItem}', [NavbarController::class, 'destroySub'])->name('navbar.sub.destroy');
    Route::resource('marriage-services', MarriageServiceController::class)->except(['index', 'show']);
    Route::resource('religious-services', ReligiousServiceController::class)->except(['index', 'show']);
    Route::resource('wakaf-services', WakafServiceController::class)->except(['index', 'show']);
    Route::get('/pages', [PageController::class, 'index'])->name('pages.index');
    Route::put('/pages/{key}', [PageController::class, 'update'])->name('pages.update')->where('key', '[a-z0-9-]+');
    Route::get('/kua-settings', [KuaSettingController::class, 'edit'])->name('kua-settings.edit');
    Route::put('/kua-settings', [KuaSettingController::class, 'update'])->name('kua-settings.update');
    Route::post('/kua-settings/test-telegram', [KuaSettingController::class, 'testTelegram'])->name('kua-settings.test-telegram');
    Route::resource('users', UserController::class)->except('show');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/employee', [ProfileController::class, 'updateEmployee'])->name('profile.employee.update');
});

require __DIR__.'/auth.php';

Route::match(['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE'], '{any}', [NavbarPageController::class, 'resolve'])
    ->where('any', '.*')
    ->fallback();
