<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;
use App\Models\StudentAnswer;

class StudentModuleController extends Controller
{
    // ==========================================
    // 1. FUNGSI VERIFIKASI PIN (TETAP AMAN)
    // ==========================================
    public function verifyPin(Request $request, $id)
    {
        $module = Module::findOrFail($id);
        
        // Jika modul tidak punya PIN, langsung berikan akses
        if (!$module->access_pin || $module->access_pin === $request->pin) {
            // Simpan 'kunci' akses di session selama browser terbuka
            session(["module_access_{$id}" => true]);
            
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error', 'message' => 'PIN salah!']);
    }

    // ==========================================
    // 2. FUNGSI MENAMPILKAN MODUL (GABUNGAN PIN + AI ADAPTIF + RESUME)
    // ==========================================
    public function show($id)
    {
        $studentId = session('student_id');

        // Cari modul utama terlebih dahulu
        $module = Module::findOrFail($id);

        // A. KUNCI KEAMANAN PIN
        if (!empty($module->access_pin) && !session()->has("module_access_{$id}")) {
            return redirect()->route('student.dashboard')->with('error', 'Kamu harus memasukkan PIN modul dulu ya!');
        }

        // B. MESIN AI ADAPTIF (PENENTU LEVEL MURID)
        $isAdaptive = $module->is_adaptive;
        $tingkatMurid = 'rendah'; // Level default

        if ($isAdaptive) {
            // Hitung persentase jawaban benar secara keseluruhan (Win Rate)
            $totalJawaban = StudentAnswer::where('student_id', $studentId)->count();
            $totalBenar = StudentAnswer::where('student_id', $studentId)
                                         ->where('is_correct', true)
                                         ->count();

            if ($totalJawaban > 0) {
                $persentase = ($totalBenar / $totalJawaban) * 100;
                
                if ($persentase > 75) {
                    $tingkatMurid = 'sulit';   // Murid Pintar
                } elseif ($persentase >= 50) {
                    $tingkatMurid = 'sedang';  // Murid Menengah
                }
            }
        }

        // C. MUAT RELASI AKTIVITAS & SOAL (DENGAN FILTER ADAPTIF JIKA NYALA)
        $module->load(['activities' => function($query) use ($isAdaptive, $tingkatMurid) {
            $query->with(['questions' => function($q) use ($isAdaptive, $tingkatMurid) {
                if ($isAdaptive) {
                    // Hanya tarik soal yang sesuai dengan level kepintaran murid
                    $q->where('difficulty', $tingkatMurid);
                }
            }]);
        }]);

        // D. AMBIL JAWABAN LAMA UNTUK FITUR RESUME (Hanya dari soal yang di-load)
        $existingAnswers = StudentAnswer::where('student_id', $studentId)
            ->whereIn('question_id', $module->activities->flatMap->questions->pluck('id'))
            ->pluck('answer_value', 'question_id')
            ->toArray();

        // 👇 TAMBAHKAN LOGIKA INI 👇
        // E. CEK APAKAH MODUL SUDAH SELESAI
        $isCompleted = false;
        $totalQuestions = $module->activities->flatMap->questions->count();
        $answeredQuestions = count($existingAnswers);
        
        // Jika jumlah jawaban murid sudah sama atau lebih dari total soal, berarti selesai!
        if ($totalQuestions > 0 && $answeredQuestions >= $totalQuestions) {
            $isCompleted = true;
        }

        // Kirim $isCompleted ke Blade
        return view('student.module', compact('module', 'existingAnswers', 'tingkatMurid', 'isAdaptive', 'isCompleted'));
    }

    // ==========================================
    // 3. FUNGSI SIMPAN OTOMATIS (AJAX)
    // ==========================================
    public function saveActivity(Request $request, $module_id)
    {
        $studentId = session('student_id'); 
        
        if ($request->has('jawaban')) {
            foreach ($request->jawaban as $questionId => $answerValue) {
                StudentAnswer::updateOrCreate(
                    ['student_id' => $studentId, 'question_id' => $questionId],
                    ['answer_value' => is_array($answerValue) ? json_encode($answerValue) : $answerValue]
                );
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Tersimpan!']);
    }
}