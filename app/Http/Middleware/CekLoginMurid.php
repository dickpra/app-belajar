<?php


namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Student;

class CekLoginMurid
{
    public function handle(Request $request, Closure $next): Response
    {
        $studentId = session('student_id');

        // 1. Cek apakah murid punya session login
        if (!$studentId) {
            return redirect()->route('student.login')->withErrors(['pesan' => 'Hayo, kamu harus masuk (login) dulu ya!']);
        }

        // 2. Tarik data murid dari database
        $student = Student::find($studentId);

        // 3. Jika akun terhapus (migrate:fresh / dihapus guru)
        if (!$student) {
            session()->flush(); 
            return redirect()->route('student.login')->withErrors(['pesan' => 'Data akunmu tidak ditemukan. Silakan login kembali ya!']);
        }

        // 4. KUNCI UTAMA: Cek apakah akun murid di-NONAKTIFKAN
        // (Pastikan nama kolom di database Anda adalah 'is_active'. Jika beda, sesuaikan namanya di sini)
        if (!$student->is_active) {
            session()->flush(); 
            return redirect()->route('student.login')->withErrors(['pesan' => 'Akun kamu sedang dinonaktifkan oleh guru. Coba lapor ke gurumu ya!']);
        }

        // Jika lolos semua hadangan di atas, silakan lewat!
        return $next($request);
    }
}