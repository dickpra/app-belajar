<?php
namespace App\Http\Controllers;
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Module;
use App\Models\StudentAnswer;

class StudentModuleController extends Controller
{
    // Fungsi untuk memverifikasi PIN via AJAX dari Dashboard
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

    // Fungsi untuk menampilkan halaman belajar (Wizard)
    public function show($id)
    {
        $module = \App\Models\Module::with(['activities.questions'])->findOrFail($id);

        // KUNCI KEAMANAN PIN
        if (!empty($module->access_pin) && !session()->has("module_access_{$id}")) {
            return redirect()->route('student.dashboard')->with('error', 'Kamu harus memasukkan PIN modul dulu ya!');
        }

        // AMBIL JAWABAN YANG SUDAH TERSIMPAN SEBELUMNYA
        $studentId = session('student_id');
        $existingAnswers = \App\Models\StudentAnswer::where('student_id', $studentId)
            ->whereIn('question_id', $module->activities->flatMap->questions->pluck('id'))
            ->pluck('answer_value', 'question_id')
            ->toArray();

        return view('student.module', compact('module', 'existingAnswers'));
    }

    // Fungsi menyimpan aktivitas (Sudah kita buat sebelumnya)
    public function saveActivity(Request $request, $module_id)
    {
        $studentId = session('student_id'); 
        
        if ($request->has('jawaban')) {
            foreach ($request->jawaban as $questionId => $answerValue) {
                StudentAnswer::updateOrCreate(
                    ['student_id' => $studentId, 'question_id' => $questionId],
                    ['answer_value' => $answerValue]
                );
            }
        }

        return response()->json(['status' => 'success', 'message' => 'Tersimpan!']);
    }
}