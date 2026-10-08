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
                return ''; 
        }
    }

    /**
     * 👇 FUNGSI BARU: Koreksi Terdas (Toleransi Koma, Spasi, dan Typo) 👇
     */
    public static function cekToleransiTeks($jawabanMurid, $kunciJawaban, $format)
    {
        // 1. Ubah jadi huruf kecil semua dan hapus karakter sisa JSON
        $murid = strtolower(trim(str_replace(['"', "'", '\\', '{', '}', '[', ']'], '', (string)$jawabanMurid)));
        $kunci = strtolower(trim(str_replace(['"', "'", '\\', '{', '}', '[', ']'], '', (string)$kunciJawaban)));

        if ($murid === '' || $kunci === '') return false;

        // 2. MAGIC WAND: Hapus SEMUA tanda baca (koma, titik, dll)
        // Hanya menyisakan huruf (a-z), angka (0-9), dan spasi
        $murid = preg_replace('/[^a-z0-9\s]/', '', $murid);
        $kunci = preg_replace('/[^a-z0-9\s]/', '', $kunci);

        // 3. Basmi spasi ganda menjadi spasi tunggal
        $murid = trim(preg_replace('/\s+/', ' ', $murid));
        $kunci = trim(preg_replace('/\s+/', ' ', $kunci));

        // 4. Cek Mutlak: Apakah setelah dibersihkan komanya, jawabannya sama persis?
        if ($murid === $kunci) {
            return true;
        }

        // 5. Cek Typo: HANYA untuk teks (bukan angka). 
        // Karena di matematika 100 dan 200 itu mirip tapi nilainya fatal jika disamakan.
        if ($format === 'text_input' && strlen($kunci) > 3) {
            similar_text($murid, $kunci, $persentase);
            
            // Lulus jika tingkat kemiripan huruf di atas 88%
            if ($persentase >= 88) {
                return true;
            }
        }

        return false;
    }

    /**
     * Membandingkan jawaban murid dengan kunci jawaban dan mengembalikan skor
     */
    public static function periksaSkor($format, $jawabanMurid, $question)
    {
        if (empty($jawabanMurid)) return 0;
        
        // HANYA Matching yang di-bypass (dikoreksi mandiri di Controller)
        // complex_fill SUDAH DICABUT DARI SINI
        if (in_array($format, ['matching'])) return null; 

        // ========================================================
        // 👇 KOREKSI KHUSUS ISIAN RUMPANG (Banyak Kotak) 👇
        // ========================================================
        if ($format === 'complex_fill') {
            // 1. Ubah format jawaban murid menjadi Array
            $jawabanArray = is_string($jawabanMurid) ? json_decode($jawabanMurid, true) : $jawabanMurid;
            
            // 2. Sedot Kunci Jawaban langsung dari kolom Repeater Options
            $options = is_string($question->options) ? json_decode($question->options, true) : ($question->options ?? []);
            $kunciArray = [];
            
            if (is_array($options)) {
                foreach ($options as $opt) {
                    $kunciArray[] = $opt['teks_pilihan'] ?? '';
                }
            }
            
            // 3. Mulai mengoreksi kotak per kotak
            if (is_array($jawabanArray)) {
                $benar = 0;
                $totalRumpang = count($kunciArray);
                
                foreach ($jawabanArray as $idx => $jwb) {
                    $kunciAsli = $kunciArray[$idx] ?? '';
                    
                    // Gunakan toleransi typo (seolah-olah ini text_input biasa)
                    if (self::cekToleransiTeks($jwb, $kunciAsli, 'text_input')) {
                        $benar++;
                    }
                }
                
                // Mode Instan: Semua kotak harus terisi dengan benar agar lulus
                if ($totalRumpang > 0 && $benar === $totalRumpang) {
                    return 100;
                }
                
                return 0; // Jika ada 1 saja yang salah, langsung salahkan semuanya
            }
        }

        // ========================================================
        // 👇 KOREKSI UNTUK SOAL TEKS / ANGKA / PILIHAN GANDA 👇
        // ========================================================
        
        // Ambil kunci mentah
        $kunciMentah = self::getKunciJawaban($format, $question);
        
        // Lempar ke mesin toleransi cerdas
        if (self::cekToleransiTeks($jawabanMurid, $kunciMentah, $format)) {
            return 100;
        }

        return 0;
    }
}