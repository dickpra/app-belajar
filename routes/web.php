<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentAuthController;
use App\Http\Controllers\StudentModuleController;
use App\Http\Middleware\CekLoginMurid; // Pastikan ini di-import!

// ========================================================
// AREA AUTENTIKASI MURID (Halaman Depan)
// ========================================================
Route::get('/', [StudentAuthController::class, 'showLogin'])->name('student.login');
Route::post('/login-process', [StudentAuthController::class, 'processLogin'])->name('student.login.process');
Route::get('/logout', [StudentAuthController::class, 'logout'])->name('student.logout');

// ========================================================
// AREA PEMBELAJARAN (WAJIB LOGIN)
// ========================================================
// Tambahkan ->middleware([CekLoginMurid::class]) di sini!
Route::prefix('ruang-belajar')->middleware([CekLoginMurid::class])->group(function () {
    
    // Halaman Utama Murid (Dashboard)
   Route::get('/dashboard', function () {
        $studentId = session('student_id');
        $modules = \App\Models\Module::where('is_active', true)->orderBy('created_at', 'desc')->get();
        
        // Cari tahu modul mana saja yang sudah pernah dijawab oleh murid ini
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