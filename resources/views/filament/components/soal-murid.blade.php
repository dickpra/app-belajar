@php
    $isRow = in_array($question->layout_position, ['image_left', 'image_right']);

    // PERBAIKAN PENTING:
    // Jika layout disetel 'image_bottom', kita paksa gambar pindah ke bawah teks
    $flexClass = match($question->layout_position) {
        'image_right' => 'flex-col md:flex-row-reverse',
        'image_top' => 'flex-col',
        'image_bottom' => 'flex-col-reverse',
        default => 'flex-col md:flex-row', // image_left
    };

    // Sesuaikan lebar proporsional
    $imgWidth = $isRow
        ? 'w-full md:w-5/12'
        : 'w-full max-w-2xl mx-auto';

    $textWidth = ($isRow && !empty($question->image))
        ? 'w-full md:w-7/12 text-left'
        : 'w-full text-center';
@endphp

<div class="bg-white rounded-3xl p-6 md:p-8 border-[3px] border-slate-200 relative pt-12 shadow-sm mb-6">

    <!-- Nomor Soal -->
    <div class="absolute -top-6 left-6 w-14 h-14 bg-blue-500 text-white rounded-2xl flex items-center justify-center font-black text-2xl shadow-[0_4px_0_#1d4ed8] border-4 border-white transform -rotate-6 z-10">
        {{ $qIndex + 1 }}
    </div>

    <!-- ========================================== -->
    <!-- 1. TOMBOL BANTUAN (VERSI MOBILE COMPACT)   -->
    <!-- ========================================== -->
    <!-- Ubah gap menjadi lebih kecil di HP (gap-2) dan membesar di tablet/PC (md:gap-4) -->
    <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 md:gap-4 mb-6 border-b-2 border-slate-100 pb-4 md:pb-6">
        
        <button type="button" onclick="bacakanTeks(`{{ strip_tags($question->question_text) }}`, this)" 
            class="btn-3d bg-blue-100 text-blue-700 font-black text-xs md:text-base py-2 px-3 md:py-3 md:px-6 rounded-xl md:rounded-2xl flex items-center gap-1.5 md:gap-2 border-2 border-blue-300 border-b-[4px] md:border-b-[6px] shadow-sm">
            <span class="text-sm md:text-xl">📢</span> Bacakan
        </button>

        @if(!empty($question->voice_note))
            <button type="button" onclick="putarVoiceNote('{{ route('private.audio', ['path' => $question->voice_note]) }}', this)" 
                class="btn-3d bg-emerald-100 text-emerald-700 font-black text-xs md:text-base py-2 px-3 md:py-3 md:px-6 rounded-xl md:rounded-2xl flex items-center gap-1.5 md:gap-2 border-2 border-emerald-300 border-b-[4px] md:border-b-[6px] shadow-sm">
                <span class="text-sm md:text-xl">🎙️</span> Suara Guru
            </button>
        @endif

        @if(!empty($question->sign_language_video))
            <button type="button" onclick="toggleVideoSoal('{{ $question->id }}')" 
                class="btn-3d bg-purple-100 text-purple-700 font-black text-xs md:text-base py-2 px-3 md:py-3 md:px-6 rounded-xl md:rounded-2xl flex items-center gap-1.5 md:gap-2 border-2 border-purple-300 border-b-[4px] md:border-b-[6px] shadow-sm">
                <span class="text-sm md:text-xl">🤟</span> Isyarat
            </button>
        @endif

    </div>

    <!-- CONTAINER VIDEO (Tersembunyi by default) -->
    @if(!empty($question->sign_language_video))
        <div
            id="video-isyarat-{{ $question->id }}"
            class="hidden mb-8 relative rounded-2xl overflow-hidden border-4 border-purple-300 shadow-md bg-slate-900 transition-all duration-300 w-full max-w-2xl mx-auto">

            <div class="bg-purple-100 px-4 py-2 flex justify-between items-center border-b-2 border-purple-300">
                <span class="font-black text-purple-800 text-sm flex items-center gap-2">
                    🤟 Bantuan Bahasa Isyarat
                </span>

                <button
                    type="button"
                    onclick="toggleVideoSoal('{{ $question->id }}')"
                    class="text-red-500 hover:text-red-700 font-black text-xl hover:scale-110 transition-transform">
                    ✖
                </button>
            </div>

            <video
                id="player-{{ $question->id }}"
                controls
                class="w-full aspect-video bg-black">

                <source
                    src="{{ route('private.video', ['path' => $question->sign_language_video]) }}"
                    type="video/mp4">

            </video>
        </div>
    @endif

    <!-- ========================================== -->
    <!-- 2. KONTEN SOAL (GAMBAR & TEKS)             -->
    <!-- ========================================== -->
    <div class="flex {{ $flexClass }} gap-6 md:gap-10 items-center w-full mb-8">

        <!-- Area Gambar -->
        @if(!empty($question->image))
            <div class="{{ $imgWidth }} flex justify-center shrink-0">

                <img
                    src="{{ route('private.image', ['path' => $question->image]) }}"
                    class="max-h-[320px] w-auto object-contain rounded-2xl border-4 border-slate-200 shadow-sm bg-slate-50 p-2">

            </div>
        @endif

        <!-- Area Teks -->
        <div class="{{ $textWidth }} flex flex-col justify-center">

            <div class="prose prose-blue prose-xl md:prose-2xl font-black text-slate-800 w-full max-w-none leading-relaxed mx-auto">

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

            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- 3. AREA OPSI JAWABAN                       -->
    <!-- ========================================== -->
    <div class="mt-4 border-t-4 border-dashed border-slate-100 pt-8 max-w-3xl mx-auto w-full">

        {{-- TIPE A: PILIHAN GANDA --}}
        @if($question->answer_format === 'multiple_choice')

            @php
                $rawJawaban = $existingAnswers[$question->id] ?? '';

                $jawabanLama = is_string($rawJawaban)
                    ? trim(str_replace(['"', "'", '\\'], '', $rawJawaban))
                    : $rawJawaban;
            @endphp

            <div class="grid grid-cols-1 {{ count($question->options ?? []) > 2 ? 'md:grid-cols-2' : 'md:grid-cols-1' }} gap-4">

                @foreach($question->options as $opsi)

                    @php
                        $nilaiJawaban = !empty($opsi['teks_pilihan'])
                            ? $opsi['teks_pilihan']
                            : ($opsi['image_pilihan'] ?? 'gambar');
                    @endphp

                    <label class="relative cursor-pointer group h-full">

                        <input
                            type="radio"
                            name="jawaban[{{ $question->id }}]"
                            value="{{ $nilaiJawaban }}"
                            class="peer sr-only"
                            required
                            {{ ($jawabanLama === $nilaiJawaban) ? 'checked' : '' }}
                            {{ $isCompleted ? 'disabled' : '' }}>

                        <div class="h-full flex flex-col items-center justify-center gap-3 p-4 bg-white border-[3px] border-slate-200 rounded-2xl transition-all duration-200 ease-in-out hover:border-blue-300 hover:bg-blue-50 hover:-translate-y-1 peer-checked:border-blue-500 peer-checked:bg-blue-100 peer-checked:shadow-[0_4px_0_#3b82f6] peer-checked:-translate-y-1 opacity-100 peer-disabled:opacity-75 peer-disabled:cursor-not-allowed">

                            @if(!empty($opsi['image_pilihan']))
                                <img
                                    src="{{ route('private.image', ['path' => $opsi['image_pilihan']]) }}"
                                    class="max-h-32 object-contain rounded-lg pointer-events-none">
                            @endif

                            @if(!empty($opsi['teks_pilihan']))
                                <span class="text-lg font-black text-slate-600 peer-checked:text-blue-800 text-center">
                                    {{ $opsi['teks_pilihan'] }}
                                </span>
                            @endif

                            <div class="absolute top-3 right-3 w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center opacity-0 scale-50 transition-all peer-checked:opacity-100 peer-checked:scale-100">

                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24">

                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="3"
                                        d="M5 13l4 4L19 7">
                                    </path>

                                </svg>

                            </div>

                        </div>

                    </label>

                @endforeach

            </div>


        {{-- TIPE B: BENAR/SALAH + PERBAIKAN --}}
        @elseif($question->answer_format === 'true_false_correction')

            @php
                $ansData = json_decode(
                    $existingAnswers[$question->id] ?? '{}',
                    true
                );

                $pilihan = is_array($ansData)
                    ? ($ansData['pilihan'] ?? '')
                    : (is_string($ansData) ? $ansData : '');

                $perbaikan = is_array($ansData)
                    ? ($ansData['perbaikan'] ?? '')
                    : '';
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <input
                    type="radio"
                    name="jawaban[{{ $question->id }}][pilihan]"
                    id="benar-{{ $question->id }}"
                    value="Benar"
                    class="peer/benar sr-only"
                    required
                    {{ $pilihan === 'Benar' ? 'checked' : '' }}
                    {{ $isCompleted ? 'disabled' : '' }}>

                <label
                    for="benar-{{ $question->id }}"
                    class="h-full flex flex-col items-center justify-center gap-3 p-4 bg-white border-[3px] border-slate-200 rounded-2xl cursor-pointer transition-all duration-200 hover:border-blue-300 hover:bg-blue-50 hover:-translate-y-1 peer-checked/benar:border-blue-500 peer-checked/benar:bg-blue-100 peer-checked/benar:shadow-[0_4px_0_#3b82f6] peer-checked/benar:-translate-y-1 peer-disabled/benar:opacity-75 peer-disabled/benar:cursor-not-allowed">

                    <span class="text-xl font-black text-slate-600 peer-checked/benar:text-blue-800">
                        ✅ BENAR
                    </span>

                </label>


                <input
                    type="radio"
                    name="jawaban[{{ $question->id }}][pilihan]"
                    id="salah-{{ $question->id }}"
                    value="Salah"
                    class="peer/salah sr-only"
                    required
                    {{ $pilihan === 'Salah' ? 'checked' : '' }}
                    {{ $isCompleted ? 'disabled' : '' }}>

                <label
                    for="salah-{{ $question->id }}"
                    class="h-full flex flex-col items-center justify-center gap-3 p-4 bg-white border-[3px] border-slate-200 rounded-2xl cursor-pointer transition-all duration-200 hover:border-red-300 hover:bg-red-50 hover:-translate-y-1 peer-checked/salah:border-red-500 peer-checked/salah:bg-red-100 peer-checked/salah:shadow-[0_4px_0_#ef4444] peer-checked/salah:-translate-y-1 peer-disabled/salah:opacity-75 peer-disabled/salah:cursor-not-allowed">

                    <span class="text-xl font-black text-slate-600 peer-checked/salah:text-red-800">
                        ❌ SALAH
                    </span>

                </label>


                <div class="col-span-1 md:col-span-2 hidden peer-checked/salah:block mt-2 bg-red-50 p-5 rounded-2xl border-[3px] border-red-200 shadow-inner">

                    <label class="block text-sm font-black text-red-700 mb-2">
                        Tuliskan Perbaikannya:
                    </label>

                    <input
                        type="text"
                        name="jawaban[{{ $question->id }}][perbaikan]"
                        value="{{ htmlspecialchars($perbaikan) }}"
                        {{ $isCompleted ? 'disabled' : '' }}
                        placeholder="Ketik jawaban yang benar di sini..."
                        class="w-full px-4 py-3 font-bold text-lg text-slate-700 bg-white border-[3px] border-slate-200 rounded-xl focus:border-red-500 focus:bg-red-50 outline-none transition-colors">

                </div>

            </div>


        {{-- TIPE C: MENJODOHKAN (MATCHING ANTI NGACAK) --}}
        @elseif($question->answer_format === 'matching')

            @php
                $rawJawaban = $existingAnswers[$question->id] ?? '{}';
                if (is_array($rawJawaban)) { $rawJawaban = json_encode($rawJawaban); }
                elseif (empty($rawJawaban)) { $rawJawaban = '{}'; }

                // 1. BUAT KUNCI JAWABAN UNTUK PENILAIAN WARNA
                $kunciPasangan = [];
                foreach($question->options as $idx => $opsi) {
                    $kiri = !empty($opsi['teks_pilihan']) ? $opsi['teks_pilihan'] : ($opsi['image_pilihan'] ?? 'kiri-'.$idx);
                    $kanan = !empty($opsi['matching_right']) ? $opsi['matching_right'] : ($opsi['image_matching_right'] ?? 'kanan-'.$idx);
                    $kunciPasangan[$kiri] = $kanan;
                }

                // 2. AMBIL OPSI KANAN
                $opsiKanan = collect($question->options)->map(function($item, $idx) {
                    $item['orig_idx'] = $idx;
                    return $item;
                });

                // 3. 🧠 SOLUSI BUG ACAK: Gunakan Hash MD5 sebagai "Sidik Jari" agar urutan selalu konsisten per murid!
                $studentId = session('student_id') ?? auth()->id() ?? 1;
                $opsiKanan = $opsiKanan->sortBy(function($item) use ($question, $studentId) {
                    $val = !empty($item['matching_right']) ? $item['matching_right'] : ($item['image_matching_right'] ?? $item['orig_idx']);
                    return md5($question->id . '-' . $studentId . '-' . $val);
                })->values();
            @endphp

            <div class="text-sm font-black text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                <span>👆</span> Pasangkan kotak di sisi kiri dan kanan!
            </div>

            <div class="matching-wrapper relative bg-slate-50 p-4 md:p-8 rounded-3xl border-[3px] border-slate-200" id="match-wrap-{{ $question->id }}">
                <svg class="absolute inset-0 w-full h-full pointer-events-none z-10" id="svg-canvas-{{ $question->id }}"></svg>

                <div class="flex justify-between relative z-20 gap-8 md:gap-16">
                    
                    <!-- SISI KIRI -->
                    <div class="w-1/2 flex flex-col gap-4 relative">
                        @foreach($question->options as $idx => $opsi)
                            @php $nilaiKiri = !empty($opsi['teks_pilihan']) ? $opsi['teks_pilihan'] : ($opsi['image_pilihan'] ?? 'kiri-'.$idx); @endphp
                            <button type="button" {{ $isCompleted ? 'disabled' : 'onclick=pilihKiri(this,'.$question->id.')' }} data-nilai="{{ $nilaiKiri }}" class="btn-kiri-{{ $question->id }} p-4 bg-white border-[3px] border-slate-200 rounded-xl font-bold text-slate-700 hover:border-blue-400 hover:bg-blue-50 transition-all text-center relative shadow-sm z-20 flex flex-col items-center justify-center gap-3 disabled:opacity-100 disabled:cursor-not-allowed">
                                @if(!empty($opsi['image_pilihan']))
                                    <img src="{{ route('private.image', ['path' => $opsi['image_pilihan']]) }}" class="max-h-24 object-contain rounded-lg pointer-events-none">
                                @endif
                                @if(!empty($opsi['teks_pilihan']))
                                    <span class="text-sm md:text-base pointer-events-none">{{ $opsi['teks_pilihan'] }}</span>
                                @endif
                                <div class="absolute top-1/2 -right-3 md:-right-4 transform -translate-y-1/2 w-5 h-5 md:w-6 md:h-6 bg-slate-200 rounded-full border-4 border-white shadow-sm konektor-kiri"></div>
                            </button>
                        @endforeach
                    </div>

                    <!-- SISI KANAN -->
                    <div class="w-1/2 flex flex-col gap-4 relative">
                        @foreach($opsiKanan as $kanan)
                            @php $nilaiKanan = !empty($kanan['matching_right']) ? $kanan['matching_right'] : ($kanan['image_matching_right'] ?? 'kanan-'.$kanan['orig_idx']); @endphp
                            <button type="button" {{ $isCompleted ? 'disabled' : 'onclick=pilihKanan(this,'.$question->id.')' }} data-nilai="{{ $nilaiKanan }}" class="btn-kanan-{{ $question->id }} p-4 bg-white border-[3px] border-slate-200 rounded-xl font-bold text-slate-700 hover:border-blue-400 hover:bg-blue-50 transition-all text-center relative shadow-sm z-20 flex flex-col items-center justify-center gap-3 disabled:opacity-100 disabled:cursor-not-allowed">
                                <div class="absolute top-1/2 -left-3 md:-left-4 transform -translate-y-1/2 w-5 h-5 md:w-6 md:h-6 bg-slate-200 rounded-full border-4 border-white shadow-sm konektor-kanan"></div>
                                @if(!empty($kanan['image_matching_right']))
                                    <img src="{{ route('private.image', ['path' => $kanan['image_matching_right']]) }}" class="max-h-24 object-contain rounded-lg pointer-events-none">
                                @endif
                                @if(!empty($kanan['matching_right']))
                                    <span class="text-sm md:text-base pointer-events-none">{{ $kanan['matching_right'] }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                <div id="hidden-inputs-{{ $question->id }}">
                    <input type="hidden" id="ans-{{ $question->id }}" name="jawaban[{{ $question->id }}]" value="{{ $rawJawaban }}">
                </div>
            </div>

            <!-- 👇 FITUR BARU: MUNCULKAN KUNCI JAWABAN SAAT REVIEW 👇 -->
            @if($isCompleted)
                <div class="mt-5 p-5 bg-amber-50 border-[3px] border-amber-300 rounded-2xl shadow-inner">
                    <h4 class="font-black text-amber-800 mb-3 flex items-center gap-2">💡 Kunci Jawaban yang Benar:</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($kunciPasangan as $kiri => $kanan)
                            <div class="bg-white p-3 rounded-xl border-2 border-amber-200 flex items-center justify-between shadow-sm">
                                <span class="font-bold text-slate-700 text-sm w-5/12 text-center">{{ \Illuminate\Support\Str::limit($kiri, 30) }}</span>
                                <span class="text-amber-500 font-black w-2/12 text-center">➔</span>
                                <span class="font-bold text-green-600 text-sm w-5/12 text-center">{{ \Illuminate\Support\Str::limit($kanan, 30) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 🕵️‍♂️ SIHIR PELUKIS GARIS (DENGAN DETEKSI BENAR/SALAH) -->
            <script>
                window['kunciPasangan_' + {{ $question->id }}] = {!! json_encode($kunciPasangan) !!};

                document.addEventListener("DOMContentLoaded", function() {
                    const soalId = {{ $question->id }};
                    const isCompleted = {{ $isCompleted ? 'true' : 'false' }};
                    let rawSaved = {!! json_encode($rawJawaban) !!};
                    let kunciPasangan = {!! json_encode($kunciPasangan) !!};
                    let savedAns = {};
                    
                    try {
                        savedAns = typeof rawSaved === 'string' ? JSON.parse(rawSaved) : rawSaved;
                        if (typeof savedAns === 'string') savedAns = JSON.parse(savedAns); 
                    } catch(e) {
                        savedAns = {};
                    }

                    const container = document.getElementById(`match-wrap-${soalId}`);

                    if (Object.keys(savedAns).length > 0 && container) {
                        const observer = new IntersectionObserver((entries) => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    setTimeout(() => {
                                        for (let key in savedAns) {
                                            let val = savedAns[key];
                                            
                                            let btnKiri = Array.from(document.querySelectorAll(`.btn-kiri-${soalId}`)).find(el => el.dataset.nilai == key);
                                            let btnKanan = Array.from(document.querySelectorAll(`.btn-kanan-${soalId}`)).find(el => el.dataset.nilai == val);
                                            
                                            if (btnKiri && btnKanan) {
                                                // 🧠 CEK JAWABAN BENAR/SALAH KHUSUS MODE REVIEW
                                                let isBenar = kunciPasangan[key] === val;
                                                
                                                // Tentukan Warna (Jika belum dikumpulkan, selalu Hijau. Jika direview, cek kebenarannya)
                                                let colorBg = (isCompleted && !isBenar) ? 'bg-red-50' : 'bg-green-50';
                                                let colorBorder = (isCompleted && !isBenar) ? 'border-red-500' : 'border-green-500';
                                                let colorDot = (isCompleted && !isBenar) ? 'bg-red-500' : 'bg-green-500';
                                                let colorLine = (isCompleted && !isBenar) ? '#ef4444' : '#22c55e'; // red atau green hex
                                                
                                                // Warnai Kanan
                                                btnKanan.classList.add(colorBorder, colorBg, 'terjawab');
                                                btnKanan.querySelector('.konektor-kanan').classList.replace('bg-slate-200', colorDot);
                                                
                                                // Warnai Kiri
                                                btnKiri.classList.add(colorBorder, colorBg, 'terjawab');
                                                btnKiri.classList.remove('border-blue-500', 'bg-blue-50', 'ring-4', 'ring-blue-100');
                                                btnKiri.querySelector('.konektor-kiri').classList.replace('bg-slate-200', colorDot);
                                                btnKiri.querySelector('.konektor-kiri').classList.replace('bg-blue-500', colorDot);
                                                
                                                // Tarik Garis dengan Warna Spesifik
                                                gambarGarisSVGReview(btnKiri, btnKanan, soalId, colorLine);
                                            }
                                        }
                                    }, 200);
                                    observer.disconnect(); 
                                }
                            });
                        });
                        observer.observe(container);
                    }
                });

                // Fungsi khusus untuk menggambar garis dengan injeksi warna Merah/Hijau
                function gambarGarisSVGReview(elKiri, elKanan, soalId, colorCode) {
                    let svg = document.getElementById(`svg-canvas-${soalId}`);
                    let container = document.getElementById(`match-wrap-${soalId}`);
                    if (!svg || !container) return;

                    let cleanId = elKiri.dataset.nilai.replace(/[^a-zA-Z0-9]/g, '');
                    let lineId = `line-${soalId}-${cleanId}`;
                    let line = document.getElementById(lineId);
                    
                    if(!line) {
                        line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                        line.id = lineId;
                        line.setAttribute('stroke', colorCode); // Gunakan warna yang didapat
                        line.setAttribute('stroke-width', '6');
                        line.setAttribute('stroke-linecap', 'round');
                        line.style.strokeDasharray = "1000";
                        line.style.strokeDashoffset = "1000";
                        line.style.transition = "stroke-dashoffset 0.5s ease-out";
                        svg.appendChild(line);
                    } else {
                        line.setAttribute('stroke', colorCode);
                    }

                    let rectContainer = container.getBoundingClientRect();
                    let rectKiri = elKiri.querySelector('.konektor-kiri').getBoundingClientRect();
                    let rectKanan = elKanan.querySelector('.konektor-kanan').getBoundingClientRect();

                    line.setAttribute('x1', rectKiri.left + (rectKiri.width/2) - rectContainer.left);
                    line.setAttribute('y1', rectKiri.top + (rectKiri.height/2) - rectContainer.top);
                    line.setAttribute('x2', rectKanan.left + (rectKanan.width/2) - rectContainer.left);
                    line.setAttribute('y2', rectKanan.top + (rectKanan.height/2) - rectContainer.top);

                    setTimeout(() => { line.style.strokeDashoffset = "0"; }, 10);
                }
            </script>


        {{-- TIPE D: INPUT ANGKA BESAR --}}
        @elseif($question->answer_format === 'number_input')

            @php
                $rawJawaban = (string) ($existingAnswers[$question->id] ?? '');
                $jawabanLama = preg_replace('/[^0-9\.\-]/', '', $rawJawaban);
            @endphp

            <div class="flex justify-center">

                <input
                    type="number"
                    name="jawaban[{{ $question->id }}]"
                    value="{{ $jawabanLama }}"
                    placeholder="Ketik angka..."
                    required
                    class="w-full max-w-sm text-center text-4xl font-black text-blue-600 px-6 py-6 bg-slate-50 border-[3px] border-slate-200 rounded-2xl focus:border-blue-500 focus:bg-blue-100 shadow-inner outline-none transition-colors placeholder:text-slate-300"
                    {{ $isCompleted ? 'disabled' : '' }}>

            </div>


        {{-- TIPE E: INPUT TEKS --}}
        @elseif($question->answer_format === 'text_input')

            @php
                $rawJawaban = (string) ($existingAnswers[$question->id] ?? '');
                $jawabanLama = trim(str_replace(['"', '\\'], '', $rawJawaban));
            @endphp

            <textarea
                name="jawaban[{{ $question->id }}]"
                rows="2"
                placeholder="Ketik jawabanmu..."
                required
                class="w-full px-6 py-4 font-bold text-xl text-slate-700 bg-slate-50 border-[3px] border-slate-200 rounded-2xl focus:border-blue-500 focus:bg-blue-50 outline-none shadow-inner transition-colors placeholder:text-slate-300"
                {{ $isCompleted ? 'disabled' : '' }}>{{ htmlspecialchars($jawabanLama) }}</textarea>

        @endif

    </div>

</div>