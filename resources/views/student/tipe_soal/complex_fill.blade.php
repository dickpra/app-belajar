@php
    $teksSoal = renderPrivateImages($question->question_text);
    
    // 1. AMBIL DATA MENTAH DARI DATABASE
    $rawJawaban = $existingAnswers[$question->id] ?? null;
    
    // PELINDUNG BARU
    if (is_object($rawJawaban) || (is_array($rawJawaban) && isset($rawJawaban['answer_value']))) {
        $rawJawaban = $rawJawaban->answer_value ?? ($rawJawaban['answer_value'] ?? '[]');
    }

    $jawabanLama = [];
    
    // 2. EKSTRAKSI JSON AMAN
    if (is_string($rawJawaban)) {
        $decoded = json_decode($rawJawaban, true);
        if (is_array($decoded)) {
            $jawabanLama = $decoded;
        } else {
            $jawabanLama = [$rawJawaban];
        }
    } elseif (is_array($rawJawaban)) {
        $jawabanLama = $rawJawaban;
    }

    // 3. PENGOBATAN DATA LAMA
    if (count($jawabanLama) === 1 && str_contains($jawabanLama[0], '|')) {
        $jawabanLama = array_map('trim', explode('|', $jawabanLama[0]));
    }

    $fillIndex = 0;
    
    // Tangkap variabel isCompleted
    $isCompletedStatus = $isCompleted ?? false;
    
    // 4. SIHIR ISIAN RUMPANG AUTO-RESIZE
    $teksSoal = preg_replace_callback(
        '/_{3,}|\.{3,}/',
        function ($matches) use ($question, &$fillIndex, $jawabanLama, $isCompletedStatus) {
            
            // Ambil jawaban dan bersihkan
            $val = isset($jawabanLama[$fillIndex]) ? htmlspecialchars($jawabanLama[$fillIndex], ENT_QUOTES) : '';
            
            // 👇 HITUNG LEBAR AWAL KOTAK (Berdasarkan jumlah huruf) 👇
            $jumlahHuruf = mb_strlen($val);
            // Minimal lebar 6 huruf. Jika lebih dari 6, lebarkan sesuai jumlah huruf + 2 huruf untuk nafas/ruang ekstra.
            $lebarAwal = $jumlahHuruf > 6 ? ($jumlahHuruf + 2) : 8; 

            // Kunci jika soal ini sudah terjawab
            $disabled = $isCompletedStatus ? 'disabled' : '';
            
            // Pewarnaan
            $colorClass = $isCompletedStatus 
                ? 'bg-green-50 border-green-400 text-green-800 cursor-not-allowed opacity-100' 
                : 'bg-blue-50 border-blue-400 text-blue-800 focus:border-blue-600 focus:bg-blue-200';
            
            // 👇 SIHIR JS INLINE PADA ATRIBUT oninput 👇
            $jsAutoResize = "this.style.width = (this.value.length > 6 ? this.value.length + 2 : 8) + 'ch';";
            
            // Hapus w-24 md:w-32, ganti dengan min-w-[80px] max-w-full dan style width ch
            $html = '<input type="text" name="jawaban[' . $question->id . '][]" value="' . $val . '" ' . $disabled . ' 
                    style="width: ' . $lebarAwal . 'ch; max-width: 100%;" 
                    oninput="' . $jsAutoResize . '" 
                    class="input-rumpang inline-block min-w-[80px] mx-1 px-2 py-1 border-b-4 font-black text-center outline-none transition-colors rounded-t-md shadow-inner ' . $colorClass . '" 
                    placeholder="..." autocomplete="off" required>';
            
            $fillIndex++;
            return $html;
        },
        $teksSoal
    );
@endphp

<div class="prose prose-blue prose-lg md:prose-2xl text-slate-800 leading-relaxed mx-auto w-full font-medium text-center">
    {!! $teksSoal !!}
</div>