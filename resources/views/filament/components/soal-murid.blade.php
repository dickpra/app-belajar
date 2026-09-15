@php
    $isRow = in_array($question->layout_position, ['image_left', 'image_right']);
    $flexClass = match($question->layout_position) {
        'image_right' => 'flex-col md:flex-row-reverse',
        'image_top' => 'flex-col',
        'image_bottom' => 'flex-col-reverse',
        default => 'flex-col md:flex-row', // image_left
    };

    $imgWidth = $isRow ? 'w-full md:w-5/12' : 'w-full md:w-3/4 mx-auto'; 
    $textWidth = ($isRow && !empty($question->image)) ? 'w-full md:w-7/12' : 'w-full';
@endphp

<div class="bg-white rounded-3xl p-6 md:p-8 border-[3px] border-slate-200 relative pt-12 shadow-sm mb-6">
    <!-- Nomor Soal -->
    <div class="absolute -top-6 left-6 w-14 h-14 bg-blue-500 text-white rounded-2xl flex items-center justify-center font-black text-2xl shadow-[0_4px_0_#1d4ed8] border-4 border-white transform -rotate-6">
        {{ $qIndex + 1 }}
    </div>

    <div class="flex {{ $flexClass }} gap-6 md:gap-8 items-start">
        
        <!-- Gambar Utama -->
        @if(!empty($question->image))
            <div class="{{ $imgWidth }} flex justify-center bg-slate-50 p-3 rounded-2xl border-[3px] border-slate-100 shadow-inner">
                <img src="{{ route('private.image', ['path' => $question->image]) }}" class="max-h-72 object-contain rounded-xl w-full">
            </div>
        @endif

        <div class="{{ $textWidth }} flex flex-col gap-6 w-full">
            <!-- ========================================== -->
            <!-- TOMBOL BANTUAN (SUARA & ISYARAT) -->
            <!-- ========================================== -->
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <button type="button" onclick="bacakanTeks(`{{ strip_tags($question->question_text) }}`)" class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold py-2 px-4 rounded-full inline-flex items-center gap-2 transition-all border-2 border-blue-300 shadow-sm active:translate-y-1">
                    <span class="text-xl animate-pulse">🔊</span> Bacakan Soal
                </button>

                <!-- Tombol Buka/Tutup Video Muncul Jika Ada Video -->
                @if(!empty($question->sign_language_video))
                    <button type="button" onclick="toggleVideoSoal({{ $question->id }})" class="bg-purple-100 hover:bg-purple-200 text-purple-800 font-bold py-2 px-4 rounded-full inline-flex items-center gap-2 transition-all border-2 border-purple-300 shadow-sm active:translate-y-1">
                        <span class="text-xl">🤟</span> Lihat Isyarat
                    </button>
                @endif
            </div>

            <!-- CONTAINER VIDEO (Tersembunyi by default) -->
            @if(!empty($question->sign_language_video))
                <div id="video-isyarat-{{ $question->id }}" class="hidden mb-6 relative rounded-2xl overflow-hidden border-4 border-purple-300 shadow-md bg-slate-900 transition-all duration-300 w-full max-w-lg">
                    <div class="bg-purple-100 px-4 py-2 flex justify-between items-center border-b-2 border-purple-300">
                        <span class="font-black text-purple-800 text-sm flex items-center gap-2">🤟 Bantuan Bahasa Isyarat</span>
                        <button type="button" onclick="toggleVideoSoal({{ $question->id }})" class="text-red-500 hover:text-red-700 font-black text-xl hover:scale-110 transition-transform">✖</button>
                    </div>
                    <!-- Tag Video -->
                    <video id="player-{{ $question->id }}" controls class="w-full aspect-video bg-black">
                        <source src="{{ route('private.video', ['path' => $question->sign_language_video]) }}" type="video/mp4">
                        Browsermu tidak mendukung pemutar video.
                    </video>
                </div>
            @endif

            <!-- Teks Pertanyaan -->
            <div class="prose prose-blue prose-lg font-bold text-slate-800 w-full max-w-full leading-loose">
                @php
                    $teksSoal = renderPrivateImages($question->question_text);
                    
                    // 1. PENANGANAN ISIAN RUMPANG (Bawaan Anda, dibiarkan saja karena mengatur Teks, bukan Kotak Opsi)
                    if($question->answer_format === 'complex_fill') {
                        $rawJawaban = $existingAnswers[$question->id] ?? '';
                        $jawabanStr = is_string($rawJawaban) ? trim(str_replace(['"', "'", '\\'], '', $rawJawaban)) : '';
                        $jawabanLamaFill = !empty($jawabanStr) ? explode(' | ', $jawabanStr) : [];
                        
                        $fillIndex = 0;
                        $teksSoal = preg_replace_callback('/_{3,}|\.{3,}/', function($matches) use (&$fillIndex, $jawabanLamaFill, $question, $isCompleted) {
                            $val = isset($jawabanLamaFill[$fillIndex]) ? $jawabanLamaFill[$fillIndex] : '';
                            $disabled = $isCompleted ? 'disabled' : '';
                            $fillIndex++;
                            return '<input type="text" name="jawaban['.$question->id.'][]" value="'.htmlspecialchars($val).'" '.$disabled.' class="inline-block w-24 md:w-32 mx-1 px-2 py-1 bg-blue-50 border-b-4 border-blue-400 text-blue-800 font-black text-center outline-none focus:border-blue-600 focus:bg-blue-200 transition-colors rounded-t-md shadow-inner" required placeholder="...">';
                        }, $teksSoal);
                    }
                @endphp
                {!! $teksSoal !!}
            </div>

            <!-- ============================================================= -->
            <!-- 🧩 PEMANGGIL KOMPONEN JAWABAN DINAMIS                         -->
            <!-- (Akan otomatis memanggil file dari resources/views/student/tipe_soal/) -->
            <!-- ============================================================= -->
            <div class="mt-2">
                @includeIf('student.tipe_soal.' . $question->answer_format, [
                    'question' => $question,
                    'existingAnswers' => $existingAnswers,
                    'isCompleted' => $isCompleted
                ])
            </div>
            <!-- ============================================================= -->

        </div>
    </div>
</div>