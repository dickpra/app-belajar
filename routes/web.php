<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentAuthController;
use App\Http\Controllers\StudentModuleController;
use App\Http\Middleware\CekLoginMurid;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

// ========================================================
// AREA AUTENTIKASI MURID (Halaman Depan)
// ========================================================
Route::get('/', [StudentAuthController::class, 'showLogin'])->name('student.login');
Route::post('/login-process', [StudentAuthController::class, 'processLogin'])->name('student.login.process');
Route::get('/logout', [StudentAuthController::class, 'logout'])->name('student.logout');

// ========================================================
// RUTE RAHASIA UNTUK GAMBAR (Bisa diakses Admin ATAU Murid)
// ========================================================
Route::get('/private-image/{path}', function ($path) {
    // 1. CEK KEAMANAN: Tolak jika BUKAN Admin Filament DAN BUKAN Murid yang login
    if (!auth()->check() && !session()->has('student_id')) {
        abort(403, 'Akses Ditolak! Anda harus login untuk melihat aset ini.');
    }

    // 2. Cek apakah file benar-benar ada di ruang rahasia (disk 'local')
    if (!Storage::disk('local')->exists($path)) {
        abort(404, 'Gambar tidak ditemukan.');
    }

    // 3. Ambil file utuh
    $file = Storage::disk('local')->path($path);
    $type = File::mimeType($file);

    // 4. Kirimkan file ke browser dengan sistem cache yang aman
    return Response::file($file, [
        'Content-Type' => $type,
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
    ]);
})->where('path', '.*')->name('private.image'); // '.*' penting agar garis miring di nama folder tidak error

// ========================================================
// AREA PEMBELAJARAN MURID (WAJIB LOGIN MURID)
// ========================================================
Route::prefix('ruang-belajar')->middleware([CekLoginMurid::class])->group(function () {
    
    Route::get('/dashboard', function () {
        $studentId = session('student_id');
        
        $modules = \App\Models\Module::with('activities')
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();
        
        $completedModuleIds = [];
        $inProgressModuleIds = []; // 👈 ARRAY BARU UNTUK STATUS 'LANJUT'

        foreach ($modules as $module) {
            $lastActivity = $module->activities->last();
            $isCompleted = false;
            
            // 1. Cek apakah sudah Selesai (Tamat)
            if ($lastActivity) {
                $isCompleted = \App\Models\ActivitySubmission::where('student_id', $studentId)
                    ->where('activity_id', $lastActivity->id)
                    ->exists();

                if ($isCompleted) {
                    $completedModuleIds[] = $module->id;
                }
            }

            // 2. Jika belum selesai, cek apakah "Sedang Dikerjakan" (Lanjut)
            if (!$isCompleted) {
                $hasStarted = \App\Models\StudentAnswer::where('student_id', $studentId)
                    ->whereHas('question.activity', function($q) use ($module) {
                        $q->where('module_id', $module->id);
                    })->exists();

                if ($hasStarted) {
                    $inProgressModuleIds[] = $module->id;
                }
            }
        }

        // Kirim $inProgressModuleIds ke file Blade
        return view('student.dashboard', compact('modules', 'completedModuleIds', 'inProgressModuleIds'));
    })->name('student.dashboard');

    // Endpoint Verifikasi PIN Modul
    Route::post('/modul/{id}/verifikasi-pin', [StudentModuleController::class, 'verifyPin'])->name('student.verify_pin');

    // Halaman Mengerjakan Modul
    Route::get('/modul/{id}', [StudentModuleController::class, 'show'])->name('student.module');
    
    // Endpoint Menyimpan Jawaban
    Route::post('/modul/{module_id}/simpan-aktivitas', [StudentModuleController::class, 'saveActivity'])->name('student.save_activity');

    // 2. Raporku
    Route::get('/raporku', function () {
        $studentId = session('student_id') ?? auth()->id();
        $submissions = \App\Models\ActivitySubmission::with('activity.module')
            ->where('student_id', $studentId)
            ->where('status', 'dinilai')
            ->orderBy('updated_at', 'desc')
            ->get();
            
        return view('student.raporku', compact('submissions'));
    })->name('student.raporku');

    // 3. Panduan
    Route::get('/panduan', function () {
        return view('student.panduan');
    })->name('student.panduan');

    // 4. Profil
    Route::get('/profil', function () {
        return view('student.profil');
    })->name('student.profil');
});

Route::get('/private-video/{path}', function ($path) {
    // 1. Cek apakah file videonya ada di disk 'modul_rahasia'
    if (!Storage::disk('modul_rahasia')->exists($path)) {
        abort(404, 'Video tidak ditemukan.');
    }

    // 2. Ambil path asli dari server
    $filePath = Storage::disk('modul_rahasia')->path($path);
    $mimeType = Storage::disk('modul_rahasia')->mimeType($path);

    // 3. Kembalikan sebagai "Stream File" agar video tidak buffering/patah-patah
    return response()->file($filePath, [
        'Content-Type' => $mimeType,
        'Accept-Ranges' => 'bytes'
    ]);
})->where('path', '.*')->name('private.video'); // where('.*') penting agar tanda miring (/) terbaca