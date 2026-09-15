<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentAuthController;
use App\Http\Controllers\StudentModuleController;
use App\Http\Middleware\CekLoginMurid;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

// ========================================================
// MESIN PENYANDI URL (FUNGSI HELPER GLOBAL KEBAL ERROR)
// ========================================================
if (!function_exists('acak_id')) {
    function acak_id($id) {
        // Mengubah ID 5 menjadi string hex keren: "4d3044554c35"
        return bin2hex('M0DUL' . $id); 
    }
}
if (!function_exists('buka_id')) {
    function buka_id($hash) {
        // 1. CEK KEAMANAN: Pastikan format hex valid dan panjangnya genap!
        if (!ctype_xdigit($hash) || strlen($hash) % 2 !== 0) {
            return null;
        }
        
        // 2. Buka sandinya
        $decoded = hex2bin($hash);
        
        // 3. Pastikan ada kata kunci rahasia 'M0DUL' di dalamnya
        if (strpos($decoded, 'M0DUL') === 0) {
            return (int) str_replace('M0DUL', '', $decoded);
        }
        
        return null; // Jika bukan buatan kita, tolak!
    }
}

// 🧠 SIHIR ROUTE BINDING: Setiap ada {hash_modul} di URL, otomatis diterjemahkan!
Route::bind('hash_modul', function ($value) {
    $realId = buka_id($value);
    
    // Jika tidak valid (misal ada yang iseng ketik /modul/5 atau ngasal), langsung lempar 404!
    if (!$realId) {
        abort(404, 'Hayo, jangan iseng ganti-ganti URL atau URL Anda sudah kadaluarsa! 🧐');
    }
    
    return $realId;
});

// 🧠 SIHIR ROUTE BINDING: Setiap ada {hash_modul} di URL, otomatis diterjemahkan jadi ID asli untuk Controller!
Route::bind('hash_modul', function ($value) {
    $realId = buka_id($value);
    if (!$realId) abort(404, 'Hayo, jangan iseng ganti-ganti URL ya! 🧐');
    return $realId;
});


// ========================================================
// AREA AUTENTIKASI MURID (Halaman Depan)
// ========================================================
Route::get('/', [StudentAuthController::class, 'showLogin'])->name('student.login');
Route::post('/login-process', [StudentAuthController::class, 'processLogin'])->name('student.login.process');
Route::get('/logout', [StudentAuthController::class, 'logout'])->name('student.logout');

// ========================================================
// RUTE RAHASIA UNTUK GAMBAR 
// ========================================================
Route::get('/private-image/{path}', function ($path) {
    if (!auth()->check() && !session()->has('student_id')) {
        abort(403, 'Akses Ditolak! Anda harus login untuk melihat aset ini.');
    }
    if (!Storage::disk('local')->exists($path)) { abort(404, 'Gambar tidak ditemukan.'); }

    $file = Storage::disk('local')->path($path);
    $type = File::mimeType($file);

    return Response::file($file, [
        'Content-Type' => $type,
        'Cache-Control' => 'no-cache, no-store, must-revalidate',
    ]);
})->where('path', '.*')->name('private.image');


// ========================================================
// AREA PEMBELAJARAN MURID (WAJIB LOGIN MURID)
// ========================================================
Route::prefix('ruang-belajar')->middleware([CekLoginMurid::class])->group(function () {
    
    Route::get('/dashboard', function () {
        $studentId = session('student_id');
        
        $modules = \App\Models\Module::with('activities')->where('is_active', true)->orderBy('sort_order', 'asc')->get();
        $completedModuleIds = [];
        $inProgressModuleIds = [];

        foreach ($modules as $module) {
            $lastActivity = $module->activities->last();
            $isCompleted = false;
            
            if ($lastActivity) {
                $isCompleted = \App\Models\ActivitySubmission::where('student_id', $studentId)
                    ->where('activity_id', $lastActivity->id)
                    ->exists();

                if ($isCompleted) { $completedModuleIds[] = $module->id; }
            }

            if (!$isCompleted) {
                $hasStarted = \App\Models\StudentAnswer::where('student_id', $studentId)
                    ->whereHas('question.activity', function($q) use ($module) {
                        $q->where('module_id', $module->id);
                    })->exists();

                if ($hasStarted) { $inProgressModuleIds[] = $module->id; }
            }
        }
        return view('student.dashboard', compact('modules', 'completedModuleIds', 'inProgressModuleIds'));
    })->name('student.dashboard');

    // 👇 SEMUA {id} DI BAWAH INI KITA GANTI JADI {hash_modul} 👇
    
    // Endpoint Verifikasi PIN Modul
    Route::post('/modul/{hash_modul}/verifikasi-pin', [StudentModuleController::class, 'verifyPin'])->name('student.verify_pin');

    // INSTANT ROUTE DUOLINGO (Ini tidak butuh ID di URL karena JSON body)
    Route::post('/modul/cek-instan', [StudentModuleController::class, 'cekJawabanInstan'])->name('student.module.cek-instan');

    // Selesai Instan
    Route::post('/modul/{hash_modul}/selesai-instan', [\App\Http\Controllers\StudentModuleController::class, 'selesaiInstan'])->name('student.module.selesai-instan');

    // Halaman Mengerjakan Modul Utama
    Route::get('/modul/{hash_modul}', [StudentModuleController::class, 'show'])->name('student.module');
    
    // Endpoint Menyimpan Jawaban (LKS)
    Route::post('/modul/{hash_modul}/simpan-aktivitas', [StudentModuleController::class, 'saveActivity'])->name('student.save_activity');

    // --------------------------------------------------------
    
    Route::get('/raporku', function () {
        $studentId = session('student_id') ?? auth()->id();
        $submissions = \App\Models\ActivitySubmission::with('activity.module')
            ->where('student_id', $studentId)->where('status', 'dinilai')
            ->orderBy('updated_at', 'desc')->get();
        return view('student.raporku', compact('submissions'));
    })->name('student.raporku');

    Route::get('/panduan', function () { return view('student.panduan'); })->name('student.panduan');
    Route::get('/profil', function () { return view('student.profil'); })->name('student.profil');
});

Route::get('/private-video/{path}', function ($path) {
    if (!Storage::disk('modul_rahasia')->exists($path)) { abort(404, 'Video tidak ditemukan.'); }
    
    $filePath = Storage::disk('modul_rahasia')->path($path);
    $mimeType = Storage::disk('modul_rahasia')->mimeType($path);

    return response()->file($filePath, ['Content-Type' => $mimeType, 'Accept-Ranges' => 'bytes']);
})->where('path', '.*')->name('private.video');