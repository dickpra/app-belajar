<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $module->title }} - Latihan Instan</title>
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; background-color: #F0F9FF; }
        .bubbly-card { border-radius: 24px; box-shadow: 0 8px 25px -5px rgba(59, 130, 246, 0.12); }
        .bounce-in { animation: bounceIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
        @keyframes bounceIn { 0% { transform: scale(0.8) translateY(20px); opacity: 0; } 100% { transform: scale(1) translateY(0); opacity: 1; } }
        
        /* ==============================================================
           PERBAIKAN GAMBAR & CAPTION TRIX EDITOR (Bawaan Filament)
           ============================================================== */
        .prose figure.attachment {
            width: 100% !important;
            max-width: 100% !important;
            margin: 1rem 0 !important;
            padding: 0 !important;
            display: block;
        }
        
        /* 1. Sembunyikan Link & Nama File Bawaan Trix */
        .prose figure.attachment a {
            pointer-events: none !important; 
            text-decoration: none !important; 
            color: inherit !important; 
            cursor: default !important; 
        }
        .prose figure.attachment .attachment__name,
        .prose figure.attachment .attachment__size {
            display: none !important; /* HILANGKAN TEKS "image.png 39 KB" */
        }
        .prose figure.attachment figcaption {
            display: none !important; /* Sembunyikan seluruh caption bawaan */
        }

        /* 2. Batasi Tinggi Gambar Agar Pas di Layar HP (Responsive) */
        .prose img { 
            width: 100% !important; 
            max-width: 100% !important; 
            max-height: 220px !important; /* 👈 Kunci tinggi maksimal agar tidak raksasa */
            height: auto !important; 
            object-fit: contain !important; /* Pastikan gambar tidak gepeng */
            border-radius: 1rem !important; 
            margin: 0 auto !important; 
            border: 3px solid #e2e8f0; 
            box-shadow: 0 4px 10px -2px rgba(59, 130, 246, 0.1); 
        }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex flex-col overflow-hidden">
<!-- 👇 TOAST NOTIFIKASI KHAS GAME 👇 -->
    <div id="custom-toast" class="fixed top-10 left-1/2 transform -translate-x-1/2 z-[200] transition-all duration-500 ease-in-out opacity-0 -translate-y-20 pointer-events-none">
        <div class="bg-red-500 border-4 border-white text-white px-6 py-4 rounded-[2rem] shadow-[0_8px_0_#b91c1c] flex items-center gap-4">
            <div class="text-4xl animate-bounce drop-shadow-md">⚠️</div>
            <div>
                <h4 class="font-black text-xl leading-tight">Waduh!</h4>
                <p id="toast-message" class="font-bold text-sm text-red-100">Pesan akan muncul di sini</p>
            </div>
        </div>
    </div>
    <!-- 👆 ============================= 👆 -->
    @php
        if (!function_exists('renderPrivateImages')) {
            function renderPrivateImages($htmlContent) {
                if (!$htmlContent) return '';
                return preg_replace('/src=".*?modul_private\/(.*?)"/i', 'src="' . url('/private-image/modul_private/$1') . '"', $htmlContent);
            }
        }
        if (!function_exists('getStageStyle')) {
            function getStageStyle($stage) {
                return match($stage) {
                    'berpikir'  => ['icon' => '🤔', 'text' => 'Pemantik', 'color' => 'bg-purple-400 text-purple-900'],
                    'amati'     => ['icon' => '🔍', 'text' => 'Mengamati', 'color' => 'bg-blue-400 text-blue-900'],
                    'mencoba'   => ['icon' => '🧪', 'text' => 'Mencoba', 'color' => 'bg-orange-400 text-orange-900'],
                    default     => ['icon' => '📖', 'text' => 'Materi', 'color' => 'bg-yellow-400 text-yellow-900'],
                };
            }
        }

        // ==============================================================
        // 1. GABUNGKAN MATERI & SOAL MENJADI "SLIDES" (SUDAH DIPERBAIKI)
        // ==============================================================
        $slides = collect();

        foreach($module->activities as $act) {
            $stages = is_string($act->stages) ? json_decode($act->stages, true) : ($act->stages ?? []);
            
            // Masukkan Materi (Dengan sistem Penyelamat jika data lama hanya pakai Deskripsi)
            if (empty($stages) && !empty($act->description)) {
                $slides->push(['type' => 'materi', 'data' => ['tipe_tahapan' => 'materi', 'konten_tahapan' => $act->description], 'activity_title' => $act->title]);
            } else {
                foreach($stages as $stage) {
                    $slides->push(['type' => 'materi', 'data' => $stage, 'activity_title' => $act->title]);
                }
            }
            
            // Masukkan Soal
            foreach($act->questions as $q) {
                $isAnswered = isset($existingAnswers[$q->id]);
                $slides->push(['type' => 'soal', 'data' => $q, 'activity_title' => $act->title, 'is_answered' => $isAnswered]);
            }
        }
        
        $totalSlides = $slides->count();

        // ==============================================================
        // 🧠 LOGIKA PELOMPAT PINTAR (BUG FIXED!)
        // ==============================================================
        $lastAnsweredIndex = -1;
        foreach($slides as $i => $slide) {
            if ($slide['type'] === 'soal' && $slide['is_answered']) {
                $lastAnsweredIndex = $i; // Catat urutan soal terakhir yang punya jawaban
            }
        }
        
        // Kita mulai aplikasi SATU LANGKAH SETELAH soal terakhir yang dijawab.
        // Jika murid belum jawab apa-apa (-1), dia akan mulai dari indeks 0 (Materi Pertama!)
        $startIndex = $lastAnsweredIndex + 1;
    @endphp

    <!-- HEADER & PROGRESS BAR -->
    <header class="p-4 md:p-6 flex items-center justify-between gap-4 z-10 bg-white border-b-2 border-slate-100 shadow-sm">
        <button onclick="window.location.href='{{ route('student.dashboard') }}'" class="text-slate-400 hover:text-slate-600 font-black text-2xl transition-transform hover:scale-110">✖</button>
        <div class="flex-1 bg-slate-100 h-5 rounded-full overflow-hidden border-2 border-slate-200 shadow-inner relative">
            <div id="progress-bar" class="bg-blue-500 h-full w-0 transition-all duration-500 ease-out shadow-[inset_0_-4px_0_rgba(0,0,0,0.1)] rounded-full"></div>
        </div>
    </header>

    <!-- AREA SLIDES -->
    <main class="flex-1 overflow-y-auto p-4 md:p-6 pb-40 no-scrollbar relative">
        
        <!-- 👇 PERBAIKAN BUG SCROLL: Ubah h-full menjadi min-h-full dan pastikan w-full 👇 -->
        <div class="max-w-2xl mx-auto min-h-full flex flex-col justify-start w-full">
            
            <form id="instant-form" class="w-full h-full pb-10">
                
                @foreach($slides as $index => $slide)
                    <!-- KARTU SOAL -->
                    <div id="slide-{{ $index }}" class="slide-card hidden w-full bubbly-card bg-white p-6 md:p-8 border-[3px] border-slate-200 mb-8">
                        
                        <div class="text-center mb-6">
                            <span class="inline-block bg-slate-100 text-slate-600 font-black px-4 py-1.5 rounded-full text-xs uppercase tracking-widest border-2 border-slate-200">
                                {{ $slide['activity_title'] }}
                            </span>
                        </div>

                        <!-- JIKA INI SLIDE MATERI -->
                        @if($slide['type'] === 'materi')
                            @php $style = getStageStyle($slide['data']['tipe_tahapan'] ?? 'materi'); @endphp
                            <div class="text-center mb-6">
                                <span class="{{ $style['color'] }} px-5 py-1.5 rounded-full font-black text-lg border-2 border-white shadow-sm">
                                    {{ $style['icon'] }} {{ $style['text'] }}
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center justify-center gap-3 mb-6">
                                <button type="button" onclick="bacakanTeks(`{{ strip_tags($slide['data']['konten_tahapan'] ?? '') }}`)" class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold py-2 px-4 rounded-full flex items-center gap-2 border-2 border-blue-300 shadow-sm active:translate-y-1 transition-all">
                                    🔊 Bacakan Materi
                                </button>
                                @if(!empty($slide['data']['sign_language_video']))
                                    <button type="button" onclick="toggleVideo('materi-{{ $index }}')" class="bg-purple-100 hover:bg-purple-200 text-purple-800 font-bold py-2 px-4 rounded-full flex items-center gap-2 border-2 border-purple-300 shadow-sm active:translate-y-1 transition-all">
                                        🤟 Lihat Isyarat
                                    </button>
                                @endif
                            </div>

                            @if(!empty($slide['data']['sign_language_video']))
                                <div id="video-materi-{{ $index }}" class="hidden mb-6 relative rounded-2xl overflow-hidden border-4 border-purple-300 shadow-md bg-black">
                                    <div class="bg-purple-100 px-4 py-2 flex justify-between items-center border-b-2 border-purple-300">
                                        <span class="font-black text-purple-800 text-sm flex items-center gap-2">🤟 Bantuan Isyarat</span>
                                        <button type="button" onclick="toggleVideo('materi-{{ $index }}')" class="text-red-500 hover:text-red-700 font-black text-xl hover:scale-110 transition-transform">✖</button>
                                    </div>
                                    <video id="player-materi-{{ $index }}" controls class="w-full aspect-video"><source src="{{ route('private.video', ['path' => $slide['data']['sign_language_video']]) }}" type="video/mp4"></video>
                                </div>
                            @endif

                            <div class="prose prose-blue prose-lg font-bold text-slate-700 mx-auto leading-relaxed w-full max-w-full">
                                {!! renderPrivateImages($slide['data']['konten_tahapan'] ?? '') !!}
                            </div>

                        <!-- JIKA INI SLIDE SOAL -->
                        @elseif($slide['type'] === 'soal')
                            @php $question = $slide['data']; @endphp
                            
                            @if(!empty($question->image))
                                <div class="mb-6 flex justify-center bg-slate-50 p-3 rounded-2xl border-2 border-slate-100">
                                    <img src="{{ route('private.image', ['path' => $question->image]) }}" class="max-h-64 object-contain rounded-xl">
                                </div>
                            @endif

                            <div class="flex flex-wrap items-center justify-center gap-3 mb-6">
                                <button type="button" onclick="bacakanTeks(`{{ strip_tags($question->question_text) }}`)" class="bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold py-2 px-4 rounded-full flex items-center gap-2 border-2 border-blue-300 shadow-sm active:translate-y-1 transition-all">
                                    🔊 Bacakan Soal
                                </button>
                                @if(!empty($question->sign_language_video))
                                    <button type="button" onclick="toggleVideo('soal-{{ $index }}')" class="bg-purple-100 hover:bg-purple-200 text-purple-800 font-bold py-2 px-4 rounded-full flex items-center gap-2 border-2 border-purple-300 shadow-sm active:translate-y-1 transition-all">
                                        🤟 Lihat Isyarat
                                    </button>
                                @endif
                            </div>

                            @if(!empty($question->sign_language_video))
                                <div id="video-soal-{{ $index }}" class="hidden mb-6 relative rounded-2xl overflow-hidden border-4 border-purple-300 shadow-md bg-black">
                                    <div class="bg-purple-100 px-4 py-2 flex justify-between items-center border-b-2 border-purple-300">
                                        <span class="font-black text-purple-800 text-sm flex items-center gap-2">🤟 Bantuan Isyarat</span>
                                        <button type="button" onclick="toggleVideo('soal-{{ $index }}')" class="text-red-500 hover:text-red-700 font-black text-xl hover:scale-110 transition-transform">✖</button>
                                    </div>
                                    <video id="player-soal-{{ $index }}" controls class="w-full aspect-video"><source src="{{ route('private.video', ['path' => $question->sign_language_video]) }}" type="video/mp4"></video>
                                </div>
                            @endif

                            <div class="prose prose-blue prose-xl font-black text-slate-800 text-center mb-8 leading-relaxed mx-auto">
                                {!! renderPrivateImages($question->question_text) !!}
                            </div>

                            <div class="mt-4">
                                @includeIf('student.tipe_soal.' . $question->answer_format, ['question' => $question, 'existingAnswers' => [], 'isCompleted' => false])
                            </div>
                        @endif

                    </div>
                @endforeach
            </form>
        </div>
    </main>

    <!-- BOTTOM ACTION BAR -->
    <div id="bottom-bar" class="fixed bottom-0 left-0 w-full bg-white border-t-[3px] border-slate-200 p-4 md:p-6 z-50 transition-all duration-300 shadow-[0_-4px_15px_rgba(0,0,0,0.05)]">
        <div class="max-w-2xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div id="feedback-area" class="hidden flex-1 flex flex-col justify-center w-full">
                <div class="flex items-center gap-3 mb-1">
                    <div id="feedback-icon" class="w-12 h-12 rounded-full flex items-center justify-center font-black text-2xl bg-white shadow-sm border-2"></div>
                    <h3 id="feedback-title" class="text-xl md:text-2xl font-black uppercase tracking-wide"></h3>
                </div>
                <p id="feedback-message" class="font-bold opacity-90 text-sm md:text-base"></p>
            </div>
            <button id="btn-action" class="w-full md:w-auto min-w-[200px] text-white font-black text-xl py-4 px-8 rounded-2xl shadow-[0_6px_0_rgba(0,0,0,0.2)] active:shadow-none active:translate-y-[6px] transition-all uppercase tracking-wide border-2 border-white">
                Memuat...
            </button>
        </div>
    </div>

    <!-- MODAL SELESAI -->
    <div id="celebrationModal" class="fixed inset-0 bg-blue-900 bg-opacity-80 z-[100] hidden flex items-center justify-center backdrop-blur-sm transition-opacity">
        <div class="bg-white p-8 md:p-12 rounded-[3rem] max-w-md w-full text-center shadow-2xl border-8 border-yellow-400 relative">
            <div class="text-8xl md:text-9xl mb-6 animate-bounce drop-shadow-xl">🏆</div>
            <h2 class="text-4xl font-black text-green-500 mb-3">Luar Biasa!</h2>
            <p class="text-xl font-bold text-slate-600 mb-8">Latihan telah diselesaikan. Nilaimu sudah tersimpan di sistem.</p>
            <button onclick="window.location.href='{{ route('student.dashboard') }}'" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-black py-4 rounded-2xl shadow-[0_6px_0_#2563eb] active:translate-y-[6px] active:shadow-none text-xl border-4 border-white">
                Selesai Berpetualang ➔
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>

        // ==========================================
        // FITUR TOAST NOTIFIKASI (PENGGANTI ALERT)
        // ==========================================
        function showToast(pesan) {
            const toast = document.getElementById('custom-toast');
            const toastMsg = document.getElementById('toast-message');
            
            toastMsg.innerText = pesan;
            
            // Animasi masuk (turun dari atas)
            toast.classList.remove('opacity-0', '-translate-y-20');
            toast.classList.add('opacity-100', 'translate-y-0');

            // Animasi keluar otomatis setelah 3 detik
            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', '-translate-y-20');
            }, 3000);
        }
        const slidesData = [
            @foreach($slides as $slide)
                { type: '{{ $slide['type'] }}', id: {{ $slide['type'] == 'soal' ? $slide['data']->id : 'null' }} },
            @endforeach
        ];
        const totalSlides = {{ $totalSlides }};
        let currentIndex = {{ $startIndex }}; // 👈 Logika pelompat pintar sudah dipakai!
        let isChecking = false;

        document.addEventListener("DOMContentLoaded", function() {
            if (currentIndex >= totalSlides) {
                akhiriLatihan();
            } else {
                showSlide(currentIndex);
            }
        });

        // ==========================================
        // FITUR SUARA & VIDEO ISYARAT
        // ==========================================
        function bacakanTeks(teks) {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel(); 
                const robot = new SpeechSynthesisUtterance(teks.replace(/<[^>]*>?/gm, ''));
                robot.lang = 'id-ID'; robot.rate = 0.9; robot.pitch = 1.1;
                window.speechSynthesis.speak(robot);
            } else {
                alert("Yah, browsermu belum mendukung fitur suara ini.");
            }
        }
        function toggleVideo(idMap) {
            const container = document.getElementById(`video-${idMap}`);
            const player = document.getElementById(`player-${idMap}`);
            if (container.classList.contains('hidden')) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
                player.pause();
            }
        }

        // ==========================================
        // MESIN NAVIGASI SLIDE & PENILAI DUOLINGO
        // ==========================================
        function showSlide(index) {
            document.querySelectorAll('.slide-card').forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('bounce-in');
            });
            const target = document.getElementById(`slide-${index}`);
            if(target) {
                target.classList.remove('hidden');
                // Trigger animasi css ulang
                void target.offsetWidth;
                target.classList.add('bounce-in');
            }

            document.getElementById('progress-bar').style.width = `${(index / totalSlides) * 100}%`;
            resetBottomBar(slidesData[index].type);
        }

        function resetBottomBar(type) {
            isChecking = false;
            const bar = document.getElementById('bottom-bar');
            const feedback = document.getElementById('feedback-area');
            const btn = document.getElementById('btn-action');

            bar.className = 'fixed bottom-0 left-0 w-full bg-white border-t-[3px] border-slate-200 p-4 md:p-6 z-50 transition-all duration-300 shadow-[0_-4px_15px_rgba(0,0,0,0.05)]';
            feedback.classList.add('hidden');
            
            if (type === 'materi') {
                btn.innerHTML = 'Paham, Lanjut! ➔';
                btn.className = 'w-full md:w-auto min-w-[200px] bg-blue-500 hover:bg-blue-400 text-white font-black text-xl py-4 px-8 rounded-2xl shadow-[0_6px_0_#1d4ed8] active:shadow-none active:translate-y-[6px] transition-all uppercase tracking-wide border-2 border-white';
                btn.setAttribute('onclick', 'slideSelanjutnya()');
            } else {
                btn.innerHTML = 'Cek Jawaban 🔍';
                btn.className = 'w-full md:w-auto min-w-[200px] bg-green-500 hover:bg-green-400 text-white font-black text-xl py-4 px-8 rounded-2xl shadow-[0_6px_0_#16a34a] active:shadow-none active:translate-y-[6px] transition-all uppercase tracking-wide border-2 border-white';
                btn.setAttribute('onclick', 'cekJawaban()');
            }
        }

        function cekJawaban() {
            if (isChecking) return;
            const currentSlide = slidesData[currentIndex];
            const form = document.getElementById('instant-form');
            const formData = new FormData(form);
            
            // Mengakomodasi semua tipe jawaban (String, Array Rumpang, JSON Matching)
            let jawabanTarget = formData.get(`jawaban[${currentSlide.id}]`);
            if (!jawabanTarget && formData.has(`jawaban[${currentSlide.id}][]`)) {
                jawabanTarget = formData.getAll(`jawaban[${currentSlide.id}][]`).join(' | ');
            }

            if (!jawabanTarget || jawabanTarget === '{}') {
                showToast("Ayo, isi atau pilih jawabanmu dulu!");
                return;
            }

            const btn = document.getElementById('btn-action');
            btn.innerHTML = '⏳ Mengecek...';
            btn.disabled = true;

            fetch("{{ route('student.module.cek-instan') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' },
                body: JSON.stringify({ question_id: currentSlide.id, jawaban: jawabanTarget })
            })
            .then(res => res.json())
            .then(data => tampilkanHasil(data))
            .catch(() => { showToast("Ups, Koneksi terputus."); btn.innerHTML = 'Coba Lagi'; btn.disabled = false; });
        }

        function tampilkanHasil(data) {
            isChecking = true;
            const bar = document.getElementById('bottom-bar');
            const feedback = document.getElementById('feedback-area');
            const btn = document.getElementById('btn-action');
            
            feedback.classList.remove('hidden');
            bar.classList.remove('bg-white');

            if (data.is_correct) {
                bar.classList.add('bg-green-100', 'border-green-300');
                document.getElementById('feedback-icon').className = 'w-12 h-12 rounded-full flex items-center justify-center font-black text-2xl bg-white border-green-200 text-green-500 shadow-sm';
                document.getElementById('feedback-icon').innerHTML = '⭐';
                document.getElementById('feedback-title').className = 'text-xl md:text-2xl font-black uppercase tracking-wide text-green-600';
                document.getElementById('feedback-title').innerText = 'Tepat Sekali!';
                document.getElementById('feedback-message').className = 'font-bold text-green-700 opacity-90 text-sm md:text-base';
                document.getElementById('feedback-message').innerText = data.message;
                
                btn.className = 'w-full md:w-auto min-w-[200px] bg-green-500 hover:bg-green-600 text-white font-black text-xl py-4 px-8 rounded-2xl shadow-[0_6px_0_#15803d] active:shadow-none active:translate-y-[6px] transition-all uppercase tracking-wide';
            } else {
                bar.classList.add('bg-red-100', 'border-red-300');
                document.getElementById('feedback-icon').className = 'w-12 h-12 rounded-full flex items-center justify-center font-black text-2xl bg-white border-red-200 text-red-500 shadow-sm';
                document.getElementById('feedback-icon').innerHTML = '❌';
                document.getElementById('feedback-title').className = 'text-xl md:text-2xl font-black uppercase tracking-wide text-red-600';
                document.getElementById('feedback-title').innerText = 'Kurang Tepat';
                document.getElementById('feedback-message').className = 'font-bold text-red-700 opacity-90 text-sm md:text-base';
                document.getElementById('feedback-message').innerText = `Kunci: ${data.correct_answer || 'Tetap semangat, perhatikan lagi.'}`;
                
                btn.className = 'w-full md:w-auto min-w-[200px] bg-red-500 hover:bg-red-600 text-white font-black text-xl py-4 px-8 rounded-2xl shadow-[0_6px_0_#b91c1c] active:shadow-none active:translate-y-[6px] transition-all uppercase tracking-wide';
            }

            btn.innerHTML = 'Lanjut ➔';
            btn.disabled = false;
            btn.setAttribute('onclick', 'slideSelanjutnya()');
        }

        function slideSelanjutnya() {
            if (currentIndex < totalSlides - 1) {
                currentIndex++;
                showSlide(currentIndex);
            } else {
                akhiriLatihan();
            }
        }

        function akhiriLatihan() {
            // 1. Mentokkan Progress bar
            document.getElementById('progress-bar').style.width = `100%`;
            
            // 2. Beritahu Server bahwa modul ini resmi selesai!
            fetch("{{ route('student.module.selesai-instan', $module->id) }}", {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 
                    'Content-Type': 'application/json' 
                }
            })
            .then(res => res.json())
            .then(data => {
                // 3. Panggil tembakan confetti setelah server mengonfirmasi!
                confetti({ particleCount: 150, spread: 80, origin: { y: 0.6 } });
                
                // 4. Munculkan Modal Pemenang
                document.getElementById('celebrationModal').classList.remove('hidden');
                document.getElementById('bottom-bar').classList.add('hidden');
            })
            .catch(err => {
                alert("Gagal menyimpan status selesai, cek koneksimu.");
            });
        }

        // ==========================================
        // SIHIR TARIK GARIS (MATCHING COMPONENT)
        // ==========================================
        // ==========================================
        // SIHIR TARIK GARIS & GUNTING GARIS (MATCHING)
        // ==========================================
        let aktifSisi = {};
        
        function pilihKiri(btn, soalId) {
            // 👇 FITUR BARU: BATALKAN JODOH JIKA KOTAK SUDAH HIJAU
            if (btn.classList.contains('terjawab')) {
                lepasJodoh(btn, 'kiri', soalId);
                aktifSisi[soalId] = null; // Matikan seleksi aktif
                return;
            }

            let aktif = aktifSisi[soalId];
            if (!aktif || aktif.sisi === 'kiri') {
                document.querySelectorAll(`.btn-kiri-${soalId}:not(.terjawab)`).forEach(el => { el.classList.remove('border-blue-500', 'bg-blue-50', 'ring-4'); el.querySelector('.konektor-kiri').classList.replace('bg-blue-500', 'bg-slate-200'); });
                btn.classList.add('border-blue-500', 'bg-blue-50', 'ring-4'); btn.querySelector('.konektor-kiri').classList.replace('bg-slate-200', 'bg-blue-500');
                aktifSisi[soalId] = { sisi: 'kiri', btn: btn };
            } else if (aktif.sisi === 'kanan') { eksekusiJodoh(btn, aktif.btn, soalId); aktifSisi[soalId] = null; }
        }

        function pilihKanan(btn, soalId) {
            // 👇 FITUR BARU: BATALKAN JODOH JIKA KOTAK SUDAH HIJAU
            if (btn.classList.contains('terjawab')) {
                lepasJodoh(btn, 'kanan', soalId);
                aktifSisi[soalId] = null; // Matikan seleksi aktif
                return;
            }

            let aktif = aktifSisi[soalId];
            if (!aktif || aktif.sisi === 'kanan') {
                document.querySelectorAll(`.btn-kanan-${soalId}:not(.terjawab)`).forEach(el => { el.classList.remove('border-blue-500', 'bg-blue-50', 'ring-4'); el.querySelector('.konektor-kanan').classList.replace('bg-blue-500', 'bg-slate-200'); });
                btn.classList.add('border-blue-500', 'bg-blue-50', 'ring-4'); btn.querySelector('.konektor-kanan').classList.replace('bg-slate-200', 'bg-blue-500');
                aktifSisi[soalId] = { sisi: 'kanan', btn: btn };
            } else if (aktif.sisi === 'kiri') { eksekusiJodoh(aktif.btn, btn, soalId); aktifSisi[soalId] = null; }
        }

        function eksekusiJodoh(btnKiri, btnKanan, soalId) {
            [btnKiri, btnKanan].forEach(btn => { btn.classList.remove('border-blue-500', 'bg-blue-50', 'ring-4'); btn.classList.add('border-green-500', 'bg-green-50', 'terjawab'); });
            btnKiri.querySelector('.konektor-kiri').className = btnKiri.querySelector('.konektor-kiri').className.replace(/bg-(slate-200|blue-500)/g, 'bg-green-500');
            btnKanan.querySelector('.konektor-kanan').className = btnKanan.querySelector('.konektor-kanan').className.replace(/bg-(slate-200|blue-500)/g, 'bg-green-500');

            let hiddenInput = document.getElementById(`ans-${soalId}`);
            if(!hiddenInput) {
                let container = document.getElementById(`hidden-inputs-${soalId}`);
                hiddenInput = document.createElement('input'); hiddenInput.type = 'hidden'; hiddenInput.id = `ans-${soalId}`; hiddenInput.name = `jawaban[${soalId}]`; hiddenInput.value = "{}";
                container.appendChild(hiddenInput);
            }
            let currentAns = JSON.parse(hiddenInput.value); currentAns[btnKiri.dataset.nilai] = btnKanan.dataset.nilai; hiddenInput.value = JSON.stringify(currentAns);
            gambarGarisSVG(btnKiri, btnKanan, soalId);
        }

        function gambarGarisSVG(elKiri, elKanan, soalId) {
            let svg = document.getElementById(`svg-canvas-${soalId}`); let container = document.getElementById(`match-wrap-${soalId}`);
            if (!svg || !container) return;
            let cleanId = elKiri.dataset.nilai.replace(/[^a-zA-Z0-9]/g, ''); let lineId = `line-${soalId}-${cleanId}`; let line = document.getElementById(lineId);
            if(!line) {
                line = document.createElementNS('http://www.w3.org/2000/svg', 'line'); line.id = lineId; line.setAttribute('stroke', '#22c55e'); line.setAttribute('stroke-width', '6'); line.setAttribute('stroke-linecap', 'round'); line.style.strokeDasharray = "1000"; line.style.strokeDashoffset = "1000"; line.style.transition = "stroke-dashoffset 0.5s ease-out"; svg.appendChild(line);
            }
            let rectContainer = container.getBoundingClientRect(); let rectKiri = elKiri.querySelector('.konektor-kiri').getBoundingClientRect(); let rectKanan = elKanan.querySelector('.konektor-kanan').getBoundingClientRect();
            line.setAttribute('x1', rectKiri.left + (rectKiri.width/2) - rectContainer.left); line.setAttribute('y1', rectKiri.top + (rectKiri.height/2) - rectContainer.top); line.setAttribute('x2', rectKanan.left + (rectKanan.width/2) - rectContainer.left); line.setAttribute('y2', rectKanan.top + (rectKanan.height/2) - rectContainer.top);
            setTimeout(() => { line.style.strokeDashoffset = "0"; }, 10);
        }

        // 👇 FUNGSI BARU: SIHIR GUNTING GARIS 👇
        function lepasJodoh(btn, sisi, soalId) {
            let hiddenInput = document.getElementById(`ans-${soalId}`);
            if (!hiddenInput) return;

            let currentAns = JSON.parse(hiddenInput.value || "{}");
            let nilaiKlik = btn.dataset.nilai;
            let nilaiKiri = null;
            let nilaiKanan = null;

            // 1. Cari tahu siapa pasangannya
            if (sisi === 'kiri') {
                nilaiKiri = nilaiKlik;
                nilaiKanan = currentAns[nilaiKiri];
            } else {
                nilaiKanan = nilaiKlik;
                for (let key in currentAns) {
                    if (currentAns[key] === nilaiKanan) {
                        nilaiKiri = key;
                        break;
                    }
                }
            }

            if (nilaiKiri && nilaiKanan) {
                // 2. Hapus memori dari JSON Input
                delete currentAns[nilaiKiri];
                hiddenInput.value = JSON.stringify(currentAns);

                // 3. Hapus Garis SVG
                let cleanId = nilaiKiri.replace(/[^a-zA-Z0-9]/g, '');
                let lineId = `line-${soalId}-${cleanId}`;
                let line = document.getElementById(lineId);
                if (line) line.remove();

                // 4. Kembalikan warna kotak ke abu-abu (Default)
                let btnKiri = document.querySelector(`.btn-kiri-${soalId}[data-nilai="${nilaiKiri}"]`);
                let btnKanan = document.querySelector(`.btn-kanan-${soalId}[data-nilai="${nilaiKanan}"]`);

                if (btnKiri) {
                    btnKiri.classList.remove('border-green-500', 'bg-green-50', 'terjawab');
                    btnKiri.querySelector('.konektor-kiri').className = btnKiri.querySelector('.konektor-kiri').className.replace('bg-green-500', 'bg-slate-200');
                }
                if (btnKanan) {
                    btnKanan.classList.remove('border-green-500', 'bg-green-50', 'terjawab');
                    btnKanan.querySelector('.konektor-kanan').className = btnKanan.querySelector('.konektor-kanan').className.replace('bg-green-500', 'bg-slate-200');
                }
            }
        }
    </script>
</body>
</html>