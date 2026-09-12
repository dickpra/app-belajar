<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;
use App\Models\StudentAnswer;
use App\Models\Question; 
use App\Models\ActivitySubmission;


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
        
        // UBAH DEFAULT MENJADI 'easy' (sesuai database!)
        $tingkatMurid = 'easy'; 

        if ($isAdaptive) {
            // Hitung persentase jawaban benar secara keseluruhan (Win Rate)
            $totalJawaban = StudentAnswer::where('student_id', $studentId)->count();
            $totalBenar = StudentAnswer::where('student_id', $studentId)
                                         ->where('is_correct', true)
                                         ->count();

            if ($totalJawaban > 0) {
                $persentase = ($totalBenar / $totalJawaban) * 100;
                
                if ($persentase > 75) {
                    $tingkatMurid = 'hard';   // UBAH JADI 'hard'
                } elseif ($persentase >= 50) {
                    $tingkatMurid = 'medium'; // UBAH JADI 'medium'
                }
            }
        }

        // C. MUAT RELASI AKTIVITAS & SOAL (DENGAN SISTEM PENYELAMAT / FALLBACK)
        // 1. Muat SEMUA soal terlebih dahulu tanpa difilter di database
        $module->load('activities.questions');

        // 2. Filter menggunakan Collection PHP agar bisa melakukan Fallback
        if ($isAdaptive) {
            foreach ($module->activities as $activity) {
                // Coba cari soal sesuai level AI murid
                $soalFilter = $activity->questions->where('difficulty', $tingkatMurid);

                // JIKA KOSONG (Admin lupa input soal di level tersebut)
                if ($soalFilter->isEmpty()) {
                    
                    // Fallback 1: Coba cari yang 'medium'
                    $soalFilter = $activity->questions->where('difficulty', 'medium');
                    
                    // Fallback 2: Jika 'medium' juga tidak ada, ambil 'easy'
                    if ($soalFilter->isEmpty()) {
                        $soalFilter = $activity->questions->where('difficulty', 'easy');
                    }
                    
                    // Fallback 3: Jika admin bikin soal tapi levelnya aneh/tidak terdeteksi, ambil semua saja
                    if ($soalFilter->isEmpty()) {
                        $soalFilter = $activity->questions;
                    }
                }

                // Timpa daftar soal di aktivitas tersebut dengan soal yang sudah difilter/diselamatkan
                $activity->setRelation('questions', $soalFilter->values());
            }
        }

        // D. AMBIL JAWABAN LAMA UNTUK FITUR RESUME (Hanya dari soal yang di-load)
        $existingAnswers = StudentAnswer::where('student_id', $studentId)
            ->whereIn('question_id', $module->activities->flatMap->questions->pluck('id'))
            ->pluck('answer_value', 'question_id')
            ->toArray();

        // E. CEK APAKAH MODUL SUDAH SELESAI (Anti-Bug AI Adaptif)
        $isCompleted = false;
        
        // Cari aktivitas urutan paling terakhir di modul ini
        $lastActivity = $module->activities->last();
        
        if ($lastActivity) {
            // Modul selesai HANYA JIKA aktivitas terakhir sudah masuk ke meja guru (ActivitySubmission)
            $isCompleted = \App\Models\ActivitySubmission::where('student_id', $studentId)
                ->where('activity_id', $lastActivity->id)
                ->exists();
        }

        return view('student.module', compact('module', 'existingAnswers', 'tingkatMurid', 'isAdaptive', 'isCompleted'));
    }

   // ==========================================
    // 3. FUNGSI SIMPAN OTOMATIS + AUTO GRADING + SUBMISSION GURU
    // ==========================================
    public function saveActivity(Request $request, $module_id)
    {
        $studentId = session('student_id'); 
        $activityId = null; 
        
        if ($request->has('jawaban')) {
            foreach ($request->jawaban as $questionId => $answerValue) {
                
                $question = Question::find($questionId);
                if (!$question) continue;

                $activityId = $question->activity_id;
                $isCorrect = null;
                $score = null;

                // A. KOREKSI OTOMATIS: HANYA JALAN JIKA TOMBOL FINAL DITEKAN
                if ($request->is_final_submit == '1') {
                    if (in_array($question->answer_format, ['multiple_choice', 'true_false'])) {
                        $jawabanMuridBersih = trim(strtolower($answerValue));
                        $kunciJawabanBersih = trim(strtolower($question->correct_answer));
                        
                        if ($jawabanMuridBersih === $kunciJawabanBersih) {
                            $isCorrect = true;
                            $score = 100;
                        } else {
                            $isCorrect = false;
                            $score = 0;
                        }
                    }
                }

                // B. SIMPAN JAWABAN KE DATABASE
                $dataJawaban = [
                    'answer_value' => is_array($answerValue) ? json_encode($answerValue) : $answerValue,
                ];

                // Jika murid menekan tombol submit, barulah status Benar/Salah dikunci
                if ($request->is_final_submit == '1') {
                    $dataJawaban['is_correct'] = $isCorrect;
                    $dataJawaban['score'] = $score;
                }

                StudentAnswer::updateOrCreate(
                    ['student_id' => $studentId, 'question_id' => $questionId],
                    $dataJawaban
                );
            }
        }

        // C. BUAT "MAP TUGAS": HANYA JIKA TOMBOL FINAL DITEKAN
        if ($activityId && $request->is_final_submit == '1') {
            ActivitySubmission::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'activity_id' => $activityId,
                ],
                [
                    'status' => 'menunggu_koreksi', 
                ]
            );
        }

        return response()->json(['status' => 'success', 'message' => 'Tersimpan!']);
    }
}