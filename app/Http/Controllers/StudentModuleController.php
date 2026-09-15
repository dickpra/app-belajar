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
        $module = Module::findOrFail($id);

        // A. KUNCI KEAMANAN PIN
        if (!empty($module->access_pin) && !session()->has("module_access_{$id}")) {
            return redirect()->route('student.dashboard')->with('error', 'Kamu harus memasukkan PIN modul dulu ya!');
        }

        // B. MESIN AI ADAPTIF (PENENTU LEVEL MURID)
        $isAdaptive = $module->is_adaptive;
        $tingkatMurid = 'easy'; // Level default

        // 👇 PERBAIKAN BUG: CEK APAKAH MURID SUDAH PUNYA JAWABAN DI MODUL INI
        $aktivitasIds = $module->activities()->pluck('id');
        $jawabanTerdahulu = StudentAnswer::where('student_id', $studentId)
            ->whereHas('question', function($q) use ($aktivitasIds) {
                $q->whereIn('activity_id', $aktivitasIds);
            })->first();

        if ($jawabanTerdahulu) {
            // 🔒 KUNCI LEVEL AI: Gunakan level soal yang sudah telanjur dia kerjakan
            $tingkatMurid = $jawabanTerdahulu->question->difficulty ?? 'easy';
        } else {
            // 🤖 JIKA BARU MULAI: Biarkan AI memprediksi kemampuan murid
            if ($isAdaptive) {
                $totalJawaban = StudentAnswer::where('student_id', $studentId)->count();
                $totalBenar = StudentAnswer::where('student_id', $studentId)
                                             ->where('is_correct', true)
                                             ->count();

                if ($totalJawaban > 0) {
                    $persentase = ($totalBenar / $totalJawaban) * 100;
                    
                    if ($persentase > 75) {
                        $tingkatMurid = 'hard';
                    } elseif ($persentase >= 50) {
                        $tingkatMurid = 'medium';
                    }
                }
            }
        }

        // C. MUAT RELASI AKTIVITAS & SOAL (DENGAN SISTEM PENYELAMAT / FALLBACK)
        $module->load('activities.questions');

        if ($isAdaptive) {
            foreach ($module->activities as $activity) {
                $soalFilter = $activity->questions->where('difficulty', $tingkatMurid);

                // Jika admin lupa bikin soal untuk level tersebut, aktifkan parasut penyelamat!
                if ($soalFilter->isEmpty()) {
                    $soalFilter = $activity->questions->where('difficulty', 'medium');
                    
                    if ($soalFilter->isEmpty()) {
                        $soalFilter = $activity->questions->where('difficulty', 'easy');
                    }
                    
                    if ($soalFilter->isEmpty()) {
                        $soalFilter = $activity->questions;
                    }
                }

                $activity->setRelation('questions', $soalFilter->values());
            }
        }

        // D. AMBIL JAWABAN LAMA UNTUK FITUR RESUME
        $existingAnswers = StudentAnswer::where('student_id', $studentId)
            ->whereIn('question_id', $module->activities->flatMap->questions->pluck('id'))
            ->pluck('answer_value', 'question_id')
            ->toArray();

        // E. CEK APAKAH MODUL SUDAH SELESAI
        $isCompleted = false;
        $lastActivity = $module->activities->last();
        
        if ($lastActivity) {
            $isCompleted = \App\Models\ActivitySubmission::where('student_id', $studentId)
                ->where('activity_id', $lastActivity->id)
                ->exists();
        }

        // 👇 PENGATUR LALU LINTAS HALAMAN 👇
        if ($module->is_instant_mode) {
            
            // 🌟 IDE BRILIAN: Jika sudah selesai, kembalikan ke tampilan LKS (Mode Ulasan) agar bisa dibaca-baca!
            if ($isCompleted) {
                return view('student.module', compact('module', 'existingAnswers', 'tingkatMurid', 'isAdaptive', 'isCompleted'));
            }

            // Jika belum selesai, masuk ke arena bermain Duolingo!
            return view('student.module-instant', compact('module', 'existingAnswers', 'tingkatMurid', 'isAdaptive', 'isCompleted'));
        }

        // Jika modul ini adalah mode ujian biasa (sejak awal), arahkan ke file blade lama
        return view('student.module', compact('module', 'existingAnswers', 'tingkatMurid', 'isAdaptive', 'isCompleted'));

        // Jika mode ujian biasa, arahkan ke file blade lama
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
                    // Panggil Service AutoGrader Sentral
                    $skorHitung = \App\Services\AutoGrader::periksaSkor($question->answer_format, $answerValue, $question);
                    
                    if ($skorHitung !== null) {
                        $isCorrect = ($skorHitung == 100) ? true : false;
                        $score = $skorHitung;
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

    // ==========================================
    // 4. API UNTUK MODE DUOLINGO (CEK INSTAN)
    // ==========================================
    public function cekJawabanInstan(Request $request)
    {
        $questionId = $request->question_id;
        $jawabanMurid = $request->jawaban;
        $studentId = session('student_id') ?? auth()->id();

        $question = \App\Models\Question::find($questionId);
        
        if (!$question) {
            return response()->json(['status' => 'error', 'message' => 'Soal tidak ditemukan!']);
        }

        // 1. Panggil Otak AI (AutoGrader) kita!
        $skor = \App\Services\AutoGrader::periksaSkor($question->answer_format, $jawabanMurid, $question);
        $kunciJawaban = \App\Services\AutoGrader::getKunciJawaban($question->answer_format, $question);

        // 2. Tentukan status Benar/Salah
        $isCorrect = ($skor === 100);

        // 3. Simpan langsung ke database secara diam-diam (Background Save)
        \App\Models\StudentAnswer::updateOrCreate(
            ['student_id' => $studentId, 'question_id' => $question->id],
            [
                'answer_value' => is_array($jawabanMurid) ? json_encode($jawabanMurid) : $jawabanMurid,
                'is_correct' => $isCorrect,
                'score' => $skor ?? 0
            ]
        );

        // 4. Kembalikan respons ke Javascript (Frontend)
        return response()->json([
            'status' => 'success',
            'is_correct' => $isCorrect,
            'correct_answer' => $kunciJawaban,
            'message' => $isCorrect ? 'Hebat! Jawabanmu benar! 🎉' : 'Ups, kurang tepat!'
        ]);
    }
    

    // ==========================================
    // 5. API UNTUK MENYELESAIKAN MODE DUOLINGO
    // ==========================================
    public function selesaiInstan($id)
    {
        $studentId = session('student_id') ?? auth()->id();
        $module = \App\Models\Module::with('activities.questions')->findOrFail($id);

        foreach ($module->activities as $activity) {
            // Kita buatkan "Stempel Pengumpulan" untuk setiap aktivitas di modul ini
            \App\Models\ActivitySubmission::updateOrCreate(
                ['student_id' => $studentId, 'activity_id' => $activity->id],
                [
                    // Beri status menunggu koreksi agar guru tetap bisa mengecek di Buku Penilaian
                    'status' => 'menunggu_koreksi', 
                ]
            );
        }

        return response()->json(['status' => 'success']);
    }
}