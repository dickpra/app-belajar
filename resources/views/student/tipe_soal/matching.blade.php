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