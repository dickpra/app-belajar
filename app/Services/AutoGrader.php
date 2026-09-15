<?php

namespace App\Services;

class AutoGrader 
{
    /**
     * Mengambil teks kunci jawaban dari database berdasarkan tipe soal
     */
    public static function getKunciJawaban($format, $question)
    {
        switch ($format) {
            case 'multiple_choice':
                $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
                if (is_array($options)) {
                    foreach ($options as $opt) {
                        if (isset($opt['is_correct']) && $opt['is_correct'] == true) {
                            return $opt['teks_pilihan'] ?? ($opt['image_pilihan'] ?? '');
                        }
                    }
                }
                return '';
                
            case 'number_input':
            case 'text_input':
                return $question->correct_answer ?? '';
                
            case 'true_false_correction':
                return $question->true_false_answer ?? '';
                
            default:
                return ''; // Matching & isian rumpang ditangani manual
        }
    }

    /**
     * Membandingkan jawaban murid dengan kunci jawaban dan mengembalikan skor
     */
    public static function periksaSkor($format, $jawabanMurid, $question)
    {
        if (empty($jawabanMurid)) return 0;
        
        // Tipe soal kompleks yang butuh mata guru
        if (in_array($format, ['matching', 'complex_fill'])) return null; 

        $kunciMentah = self::getKunciJawaban($format, $question);
        
        // Bersihkan dari spasi, huruf besar, dan simbol aneh JSON
        $muridBersih = strtolower(trim(str_replace(['"', "'", '\\', '{', '}', '[', ']'], '', (string)$jawabanMurid)));
        $kunciBersih = strtolower(trim(str_replace(['"', "'", '\\', '{', '}', '[', ']'], '', (string)$kunciMentah)));

        if ($kunciBersih !== '' && $muridBersih === $kunciBersih) {
            return 100;
        }

        return 0;
    }
}