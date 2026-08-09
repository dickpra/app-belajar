<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function student()
    {
        // Sesuaikan dengan nama model murid Anda (misal: Student atau User)
        return $this->belongsTo(Student::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    // Di dalam app/Models/StudentAnswer.php

    // ==========================================
    // AUTO-KOREKSI PINTAR (MAGIC ACCESSOR)
    // ==========================================
    public function getIsCorrectAttribute()
    {
        $question = $this->question;
        if (!$question) return null;

        $format = $question->answer_format;
        $jawabanMurid = $this->answer_value;

        // 1. KOREKSI TIPE PILIHAN GANDA
        if ($format === 'multiple_choice') {
            $opsiBenar = collect($question->options)->where('is_correct', true)->first();
            if (!$opsiBenar) return false;
            
            $kunci = !empty($opsiBenar['teks_pilihan']) ? $opsiBenar['teks_pilihan'] : ($opsiBenar['image_pilihan'] ?? '');
            return $jawabanMurid === $kunci;
        }

        // 2. KOREKSI TIPE INPUT TEKS & ANGKA
        if (in_array($format, ['number_input', 'text_input'])) {
            // strtolower() dan trim() digunakan agar "Matahari" dan "matahari " dianggap sama persis (Toleransi Spasi & Huruf Besar)
            return strtolower(trim($jawabanMurid)) === strtolower(trim($question->correct_answer));
        }

        // 3. KOREKSI TIPE BENAR/SALAH + PERBAIKAN
        if ($format === 'true_false_correction') {
            // Pecah data JSON dari murid
            $data = json_decode($jawabanMurid, true);
            
            // Jika datanya rusak atau bukan JSON, salahkan
            if (!is_array($data)) return false;

            $pilihanMurid = $data['pilihan'] ?? '';
            $perbaikanMurid = strtolower(trim($data['perbaikan'] ?? ''));

            $kunciPilihan = $question->true_false_answer; // 'Benar' atau 'Salah' dari form Admin
            
            if ($kunciPilihan === 'Benar') {
                // Jika kuncinya BENAR, murid cukup jawab BENAR (tidak butuh kotak perbaikan)
                return $pilihanMurid === 'Benar';
            } else {
                // Jika kuncinya SALAH, murid wajib menekan SALAH + Teks perbaikannya harus pas!
                $kunciPerbaikan = strtolower(trim($question->correction_text ?? ''));
                return ($pilihanMurid === 'Salah') && ($perbaikanMurid === $kunciPerbaikan);
            }
        }

        // 4. UNTUK MATCHING (TARIK GARIS) DAN COMPLEX FILL (ISIAN RUMPANG BANYAK)
        // Kita biarkan mengembalikan NULL agar berstatus "Membutuhkan koreksi manual dari Guru"
        return null; 
    }
}
