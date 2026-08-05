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

    public function getIsCorrectAttribute()
    {
        $question = $this->question; // Asumsi ada relasi belongsTo Question

        // 1. Cek jika soal adalah Pilihan Ganda
        if (in_array($question->answer_format, ['multiple_choice', 'true_false_correction'])) {
            foreach ($question->options as $option) {
                // Jika teks jawaban murid cocok dengan teks opsi AND opsi itu is_correct = true
                if ($this->answer_value == $option['teks_pilihan'] && isset($option['is_correct']) && $option['is_correct']) {
                    return true;
                }
            }
            return false;
        }

        // 2. Cek jika soal adalah Input Angka / Teks
        if (in_array($question->answer_format, ['number_input', 'text_input'])) {
            // Cek apakah jawaban murid sama persis dengan kunci jawaban (mengabaikan huruf besar/kecil)
            return strtolower(trim($this->answer_value)) === strtolower(trim($question->correct_answer));
        }

        // Default jika tipe aneh atau butuh koreksi manual guru
        return null; 
    }
}
