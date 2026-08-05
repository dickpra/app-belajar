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
    
    // Halaman Utama Murid (Dashboard)
    Route::get('/dashboard', function () {
        $studentId = session('student_id');
        $modules = \App\Models\Module::where('is_active', true)->orderBy('created_at', 'desc')->get();
        
        $completedModuleIds = \App\Models\StudentAnswer::where('student_id', $studentId)
            ->join('questions', 'student_answers.question_id', '=', 'questions.id')
            ->join('activities', 'questions.activity_id', '=', 'activities.id')
            ->pluck('activities.module_id')
            ->unique()
            ->toArray();

        return view('student.dashboard', compact('modules', 'completedModuleIds'));
    })->name('student.dashboard');

    // Endpoint Verifikasi PIN Modul
    Route::post('/modul/{id}/verifikasi-pin', [StudentModuleController::class, 'verifyPin'])->name('student.verify_pin');

    // Halaman Mengerjakan Modul
    Route::get('/modul/{id}', [StudentModuleController::class, 'show'])->name('student.module');
    
    // Endpoint Menyimpan Jawaban
    Route::post('/modul/{module_id}/simpan-aktivitas', [StudentModuleController::class, 'saveActivity'])->name('student.save_activity');

});