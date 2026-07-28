<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;

class StudentAuthController extends Controller
{
    public function showLogin()
    {
        // Ambil semua data siswa yang aktif untuk ditampilkan di dropdown
        $students = Student::where('is_active', true)->get();
        return view('student.login', compact('students'));
    }

    public function processLogin(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'pin' => 'required|digits:4'
        ]);

        $student = Student::find($request->student_id);

        if ($student->pin === $request->pin) {
            // Simpan sesi murid
            session(['student_id' => $student->id, 'student_name' => $student->name]);
            return redirect()->route('student.dashboard');
        }

        return back()->withErrors(['pin' => 'PIN yang dimasukkan salah!']);
    }

    public function logout()
    {
        session()->forget(['student_id', 'student_name']);
        return redirect()->route('student.login');
    }
}