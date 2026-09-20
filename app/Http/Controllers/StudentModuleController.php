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
        // ID sudah berupa angka asli berkat Route::bind di web.php!
        $module = Module::findOrFail($id);
        
        if (!$module->access_pin || $module->access_pin === $request->pin) {
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
        $studentId = session('student_id') ?? auth()->id();
        
        // ID sudah berupa angka asli berkat Route::bind di web.php!
        $module = Module::findOrFail($id);

        // ==============================================================
        // 🛑 SATPAM 1: CEK STATUS AKTIF
        // ==============================================================
        if (isset($module->is_active) && !$module->is_active) {
            return redirect()->route('student.dashboard')->with('error', 'Maaf, Modul ini belum diaktifkan oleh Pak/Bu Guru.');
        }

        // ==============================================================
        // 🛑 SATPAM 2: ANTI LOMPAT MODUL (HARUS BERURUTAN)
        // ==============================================================
        $modulSebelumnya = Module::where('sort_order', '<', $module->sort_order)
                            ->where('is_active', true)
                            ->orderBy('sort_order', 'desc')
                            ->first();
        
        if ($modulSebelumnya) {
            $sudahSelesai = ActivitySubmission::where('student_id', $studentId)
                                ->whereHas('activity', function($q) use ($modulSebelumnya) {
                                    $q->where('module_id', $modulSebelumnya->id);
                                })
                                ->exists();
            
            if (!$sudahSelesai) {
                return redirect()->route('student.dashboard')->with('error', 'Ups! Kamu harus menyelesaikan modul sebelumnya terlebih dahulu ya! 🔒');
            }
        }

        // A. KUNCI KEAMANAN PIN
        if (!empty($module->access_pin) && !session()->has("module_access_{$id}")) {
            return redirect()->route('student.dashboard')->with('error', 'Kamu harus memasukkan PIN modul dulu ya!');
        }

        // B. MESIN AI ADAPTIF
        $isAdaptive = $module->is_adaptive;
        $tingkatMurid = 'easy'; 

        $aktivitasIds = $module->activities()->pluck('id');
        $jawabanTerdahulu = StudentAnswer::where('student_id', $studentId)
            ->whereHas('question', function($q) use ($aktivitasIds) {
                $q->whereIn('activity_id', $aktivitasIds);
            })->first();

        if ($jawabanTerdahulu) {
            $tingkatMurid = $jawabanTerdahulu->question->difficulty ?? 'easy';
        } else {
            if ($isAdaptive) {
                $totalJawaban = StudentAnswer::where('student_id', $studentId)->count();
                $totalBenar = StudentAnswer::where('student_id', $studentId)->where('is_correct', true)->count();

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

        // C. MUAT RELASI AKTIVITAS & SOAL
        $module->load('activities.questions');

        if ($isAdaptive) {
            foreach ($module->activities as $activity) {
                $soalFilter = $activity->questions->where('difficulty', $tingkatMurid);

                if ($soalFilter->isEmpty()) {
                    $soalFilter = $activity->questions->where('difficulty', 'medium');
                    if ($soalFilter->isEmpty()) $soalFilter = $activity->questions->where('difficulty', 'easy');
                    if ($soalFilter->isEmpty()) $soalFilter = $activity->questions;
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
            $isCompleted = ActivitySubmission::where('student_id', $studentId)
                ->where('activity_id', $lastActivity->id)
                ->exists();
        }

        // 👇 PENGATUR LALU LINTAS HALAMAN 👇
        if ($module->is_instant_mode) {
            if ($isCompleted) {
                return view('student.module', compact('module', 'existingAnswers', 'tingkatMurid', 'isAdaptive', 'isCompleted'));
            }
            return view('student.module-instant', compact('module', 'existingAnswers', 'tingkatMurid', 'isAdaptive', 'isCompleted'));
        }

        return view('student.module', compact('module', 'existingAnswers', 'tingkatMurid', 'isAdaptive', 'isCompleted'));
    }

   // ==========================================
    // 3. FUNGSI SIMPAN OTOMATIS
    // ==========================================
    public function saveActivity(Request $request, $module_id)
    {
        $studentId = session('student_id') ?? auth()->id(); 
        $activityId = null; 
        
        if ($request->has('jawaban')) {
            foreach ($request->jawaban as $questionId => $answerValue) {
                $question = Question::find($questionId);
                if (!$question) continue;

                $activityId = $question->activity_id;
                $isCorrect = null;
                $score = null;

                if ($request->is_final_submit == '1') {
                    $skorHitung = \App\Services\AutoGrader::periksaSkor($question->answer_format, $answerValue, $question);
                    if ($skorHitung !== null) {
                        $isCorrect = ($skorHitung == 100) ? true : false;
                        $score = $skorHitung;
                    }
                }

                $dataJawaban = [
                    'answer_value' => is_array($answerValue) ? json_encode($answerValue) : $answerValue,
                ];

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

        if ($activityId && $request->is_final_submit == '1') {
            ActivitySubmission::updateOrCreate(
                ['student_id' => $studentId, 'activity_id' => $activityId],
                ['status' => 'menunggu_koreksi']
            );
        }

        return response()->json(['status' => 'success', 'message' => 'Tersimpan!']);
    }

    // ==========================================
    // 4. API UNTUK MODE DUOLINGO (CEK INSTAN - AMAN ERROR)
    // ==========================================
    public function cekJawabanInstan(Request $request)
    {
        $questionId = $request->question_id;
        $jawabanMurid = $request->jawaban;
        $studentId = session('student_id') ?? auth()->id();

        $question = \App\Models\Question::find($questionId);
        
        if (!$question) {
            return response()->json(['status' => 'error', 'message' => 'Soal tidak ditemukan!'], 404);
        }

        $isCorrect = false;
        $kunciJawaban = '';
        $skor = 0;

        // A. Penanganan Khusus Tipe Benar / Salah (True False Correction)
        if ($question->answer_format === 'true_false_correction') {
            $pilihanMurid = is_array($jawabanMurid) ? ($jawabanMurid['pilihan'] ?? '') : $jawabanMurid;
            $perbaikanMurid = is_array($jawabanMurid) ? trim($jawabanMurid['perbaikan'] ?? '') : '';

            $kunciPilihan = $question->true_false_answer; // 'Benar' atau 'Salah'
            $kunciPerbaikan = trim($question->correction_text ?? '');

            if ($kunciPilihan === 'Benar') {
                $isCorrect = (strtolower($pilihanMurid) === 'benar');
                $kunciJawaban = 'Pernyataan BENAR';
            } else {
                $pilihanBenar = (strtolower($pilihanMurid) === 'salah');
                // Jika guru mengisi teks perbaikan, cocokkan perbaikannya
                $perbaikanBenar = empty($kunciPerbaikan) || (strcasecmp($perbaikanMurid, $kunciPerbaikan) === 0);
                $isCorrect = $pilihanBenar && $perbaikanBenar;
                $kunciJawaban = 'Pernyataan SALAH' . ($kunciPerbaikan ? " (Perbaikan: {$kunciPerbaikan})" : '');
            }
            $skor = $isCorrect ? 100 : 0;
        } else {
            // B. Tipe Soal Lainnya (Dilindungi Try-Catch agar tidak menimbulkan 500 error)
            try {
                $skor = \App\Services\AutoGrader::periksaSkor($question->answer_format, $jawabanMurid, $question);
                $isCorrect = ($skor === 100);
            } catch (\Throwable $e) {
                $skor = 0;
                $isCorrect = false;
            }

            try {
                $kunciJawaban = \App\Services\AutoGrader::getKunciJawaban($question->answer_format, $question);
            } catch (\Throwable $e) {
                $kunciJawaban = $question->correct_answer ?? 'Perhatikan kembali materi.';
            }
        }

        // Simpan jawaban siswa ke database
        \App\Models\StudentAnswer::updateOrCreate(
            ['student_id' => $studentId, 'question_id' => $question->id],
            [
                'answer_value' => is_array($jawabanMurid) ? json_encode($jawabanMurid) : $jawabanMurid,
                'is_correct' => $isCorrect,
                'score' => $skor ?? 0
            ]
        );

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
        
        // ID sudah berupa angka asli berkat Route::bind di web.php!
        $module = Module::with('activities.questions')->findOrFail($id);

        foreach ($module->activities as $activity) {
            ActivitySubmission::updateOrCreate(
                ['student_id' => $studentId, 'activity_id' => $activity->id],
                ['status' => 'menunggu_koreksi']
            );
        }

        return response()->json(['status' => 'success']);
    }
}