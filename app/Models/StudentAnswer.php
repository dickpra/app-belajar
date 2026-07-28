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

    public function getIsCorrectAttribute()
    {
        $question = $this->question;
        if (!$question) return null;

        // Jika soal pilihan ganda, koreksi otomatis!
        if ($question->answer_format === 'multiple_choice') {
            $options = $question->options ?? [];
            $correctOption = collect($options)->firstWhere('is_correct', true);
            
            if ($correctOption) {
                return $correctOption['teks_pilihan'] === $this->answer_value;
            }
            return false;
        }
        
        // Jika soal isian/angka, kembalikan 'null' (tanda butuh cek manual)
        return null; 
    }
}
