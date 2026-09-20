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
        /* 1. Latar Belakang Ceria & Font Bulat */
        body { 
            font-family: 'Nunito', sans-serif; 
            background-color: #E0F2FE; /* Biru langit sangat muda */
            background-image: radial-gradient(#bae6fd 2px, transparent 2px);
            background-size: 30px 30px; /* Motif polkadot samar */
        }
        
        /* 2. Kartu Bubbly ala Game */
        .bubbly-card { 
            border-radius: 32px; 
            box-shadow: 0 12px 0px #cbd5e1, 0 15px 20px rgba(0,0,0,0.05); 
            border: 4px solid white;
            background-color: #ffffff;
        }

        /* 3. Animasi Muncul yang Lebih Memantul */
        .bounce-in { animation: superBounce 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
        @keyframes superBounce { 
            0% { transform: scale(0.6) translateY(40px); opacity: 0; } 
            100% { transform: scale(1) translateY(0); opacity: 1; } 
        }

        /* 4. Efek Tombol 3D Khusus */
        .btn-3d {
            transition: all 0.1s;
        }
        .btn-3d:active {
            transform: translateY(6px);
            box-shadow: 0 0px 0px transparent !important;
        }
        
        /* ==============================================================
           SULAP UI/UX CAPTION GAMBAR (TRIX EDITOR)
           ============================================================== */
        .prose figure.attachment a { pointer-events: none !important; text-decoration: none !important; color: inherit !important; cursor: default !important; }
        .prose figure.attachment figcaption {
            display: block !important; text-align: center !important; font-size: 0.9rem !important; font-weight: 800 !important; color: #64748b !important;
            margin-top: 1rem !important; padding: 0.5rem 1.5rem !important; background-color: #f8fafc !important; border-radius: 1rem !important;
            border: 3px dashed #cbd5e1 !important; width: max-content !important; max-width: 90% !important; margin-left: auto !important; margin-right: auto !important;
        }
        .prose img { 
            width: 100% !important; max-width: 100% !important; max-height: 250px !important; height: auto !important; object-fit: contain !important; 
            border-radius: 1.5rem !important; margin: 0 auto !important; border: 4px solid #e2e8f0; box-shadow: 0 8px 0px #cbd5e1; 
        }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex flex-col overflow-hidden">

    <!-- 👇 TOAST NOTIFIKASI KHAS GAME 👇 -->
    <div id="custom-toast" class="fixed top-10 left-1/2 transform -translate-x-1/2 z-[200] transition-all duration-500 ease-in-out opacity-0 -translate-y-20 pointer-events-none">
        <div class="bg-orange-400 border-4 border-white text-white px-6 py-4 rounded-[2rem] shadow-[0_8px_0_#c2410c] flex items-center gap-4">
            <div class="text-4xl animate-bounce drop-shadow-md">🙈</div>
            <div>
                <h4 class="font-black text-xl leading-tight">Ups!</h4>
                <p id="toast-message" class="font-bold text-sm text-orange-100">Pesan akan muncul di sini</p>
            </div>
        </div>
    </div>

    @php
        // ... (Kode PHP Helper dan Logika Slide tetap SAMA PERSIS seperti milikmu) ...
        if (!function_exists('renderPrivateImages')) {
            function renderPrivateImages($htmlContent) {
                if (!$htmlContent) return '';
                return preg_replace('/src=".*?modul_private\/(.*?)"/i', 'src="' . url('/private-image/modul_private/$1') . '"', $htmlContent);
            }
        }
        if (!function_exists('getStageStyle')) {
            function getStageStyle($stage) {
                return match($stage) {
                    'berpikir'  => ['icon' => '🤔', 'text' => 'Pemantik', 'color' => 'bg-purple-400 text-white shadow-[0_4px_0_#7e22ce]'],
                    'amati'     => ['icon' => '🔍', 'text' => 'Mengamati', 'color' => 'bg-sky-400 text-white shadow-[0_4px_0_#0284c7]'],
                    'mencoba'   => ['icon' => '🧪', 'text' => 'Mencoba', 'color' => 'bg-orange-400 text-white shadow-[0_4px_0_#c2410c]'],
                    default     => ['icon' => '📖', 'text' => 'Materi', 'color' => 'bg-yellow-400 text-yellow-900 shadow-[0_4px_0_#ca8a04]'],
                };
            }
        }

        $slides = collect();
        foreach($module->activities as $act) {
            $stages = is_string($act->stages) ? json_decode($act->stages, true) : ($act->stages ?? []);
            if (empty($stages) && !empty($act->description)) {
                $slides->push(['type' => 'materi', 'data' => ['tipe_tahapan' => 'materi', 'konten_tahapan' => $act->description], 'activity_title' => $act->title]);
            } else {
                foreach($stages as $stage) {
                    $slides->push(['type' => 'materi', 'data' => $stage, 'activity_title' => $act->title]);
                }
            }
            foreach($act->questions as $q) {
                $isAnswered = isset($existingAnswers[$q->id]);
                $slides->push(['type' => 'soal', 'data' => $q, 'activity_title' => $act->title, 'is_answered' => $isAnswered]);
            }
        }
        $totalSlides = $slides->count();
        $lastAnsweredIndex = -1;
        foreach($slides as $i => $slide) {
            if ($slide['type'] === 'soal' && $slide['is_answered']) {
                $lastAnsweredIndex = $i;
            }
        }
        $startIndex = $lastAnsweredIndex + 1;
    @endphp

    <!-- HEADER & PROGRESS BAR -->
    <header class="p-4 md:p-6 flex items-center justify-between gap-4 z-10 bg-white border-b-[6px] border-slate-200">
        <button onclick="window.location.href='{{ route('student.dashboard') }}'" class="text-slate-300 hover:text-red-500 font-black text-2xl transition-transform hover:scale-125">✖</button>
        
        <!-- Progress Bar ala Candy/Game -->
        <div class="flex-1 bg-slate-100 h-6 rounded-full overflow-hidden border-[3px] border-slate-200 shadow-inner relative">
            <div id="progress-bar" class="bg-gradient-to-r from-green-400 to-green-500 h-full w-0 transition-all duration-700 ease-out rounded-full relative">
                <!-- Efek Mengkilap (Shine) -->
                <div class="absolute top-1 left-2 right-2 h-2 bg-white opacity-40 rounded-full"></div>
            </div>
        </div>
    </header>

    <!-- AREA SLIDES -->
    <main class="flex-1 overflow-y-auto p-4 md:p-8 pb-48 no-scrollbar relative">
        <div class="max-w-2xl mx-auto min-h-full flex flex-col justify-start w-full">
            <form id="instant-form" class="w-full h-full pb-10">
                @foreach($slides as $index => $slide)
                    <!-- KARTU KONTEN -->
                    <div id="slide-{{ $index }}" class="slide-card hidden w-full bubbly-card p-6 md:p-10 mb-8 mt-4 relative">
                        
                        <!-- Pita Judul Aktivitas -->
                        <div class="absolute -top-5 left-1/2 transform -translate-x-1/2">
                            <span class="inline-block bg-white text-slate-500 font-black px-6 py-2 rounded-full text-xs md:text-sm uppercase tracking-widest border-4 border-slate-200 shadow-sm">
                                {{ $slide['activity_title'] }}
                            </span>
                        </div>

                        <!-- JIKA INI SLIDE MATERI -->
                        @if($slide['type'] === 'materi')
                            @php $style = getStageStyle($slide['data']['tipe_tahapan'] ?? 'materi'); @endphp
                            <div class="text-center mb-8 mt-4">
                                <span class="{{ $style['color'] }} px-6 py-2 rounded-full font-black text-lg border-2 border-white inline-flex items-center gap-2">
                                    <span class="text-2xl">{{ $style['icon'] }}</span> {{ $style['text'] }}
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center justify-center gap-4 mb-8">
                                <button type="button" onclick="bacakanTeks(`{{ strip_tags($slide['data']['konten_tahapan'] ?? '') }}`)" class="btn-3d bg-sky-400 text-white font-black py-3 px-6 rounded-2xl flex items-center gap-2 shadow-[0_6px_0_#0284c7] border-2 border-white text-lg">
                                    🔊 Bacakan
                                </button>
                                @if(!empty($slide['data']['sign_language_video']))
                                    <button type="button" onclick="toggleVideo('materi-{{ $index }}')" class="btn-3d bg-purple-500 text-white font-black py-3 px-6 rounded-2xl flex items-center gap-2 shadow-[0_6px_0_#7e22ce] border-2 border-white text-lg">
                                        🤟 Isyarat
                                    </button>
                                @endif
                            </div>

                            @if(!empty($slide['data']['sign_language_video']))
                                <div id="video-materi-{{ $index }}" class="hidden mb-8 relative rounded-3xl overflow-hidden border-8 border-purple-200 shadow-[0_8px_0_#e9d5ff] bg-black">
                                    <div class="bg-purple-500 px-4 py-3 flex justify-between items-center">
                                        <span class="font-black text-white text-sm flex items-center gap-2">🤟 Bantuan Isyarat</span>
                                        <button type="button" onclick="toggleVideo('materi-{{ $index }}')" class="text-white hover:text-purple-200 font-black text-xl hover:scale-110 transition-transform">✖</button>
                                    </div>
                                    <video id="player-materi-{{ $index }}" controls class="w-full aspect-video"><source src="{{ route('private.video', ['path' => $slide['data']['sign_language_video']]) }}" type="video/mp4"></video>
                                </div>
                            @endif

                            <div class="prose prose-blue prose-xl font-bold text-slate-700 mx-auto leading-relaxed w-full max-w-full">
                                {!! renderPrivateImages($slide['data']['konten_tahapan'] ?? '') !!}
                            </div>

                        <!-- JIKA INI SLIDE SOAL -->
                        @elseif($slide['type'] === 'soal')
                            @php $question = $slide['data']; @endphp
                            
                            @if(!empty($question->image))
                                <div class="mb-8 mt-4 flex justify-center bg-slate-50 p-4 rounded-3xl border-4 border-slate-100 shadow-inner">
                                    <img src="{{ route('private.image', ['path' => $question->image]) }}" class="max-h-64 object-contain rounded-2xl">
                                </div>
                            @endif

                            <div class="flex flex-wrap items-center justify-center gap-4 mb-8 mt-4">
                                <button type="button" onclick="bacakanTeks(`{{ strip_tags($question->question_text) }}`)" class="btn-3d bg-sky-400 text-white font-black py-3 px-6 rounded-2xl flex items-center gap-2 shadow-[0_6px_0_#0284c7] border-2 border-white text-lg">
                                    🔊 Bacakan
                                </button>
                                @if(!empty($question->sign_language_video))
                                    <button type="button" onclick="toggleVideo('soal-{{ $index }}')" class="btn-3d bg-purple-500 text-white font-black py-3 px-6 rounded-2xl flex items-center gap-2 shadow-[0_6px_0_#7e22ce] border-2 border-white text-lg">
                                        🤟 Isyarat
                                    </button>
                                @endif
                            </div>

                            @if(!empty($question->sign_language_video))
                                <div id="video-soal-{{ $index }}" class="hidden mb-8 relative rounded-3xl overflow-hidden border-8 border-purple-200 shadow-[0_8px_0_#e9d5ff] bg-black">
                                    <div class="bg-purple-500 px-4 py-3 flex justify-between items-center">
                                        <span class="font-black text-white text-sm flex items-center gap-2">🤟 Bantuan Isyarat</span>
                                        <button type="button" onclick="toggleVideo('soal-{{ $index }}')" class="text-white hover:text-purple-200 font-black text-xl hover:scale-110 transition-transform">✖</button>
                                    </div>
                                    <video id="player-soal-{{ $index }}" controls class="w-full aspect-video"><source src="{{ route('private.video', ['path' => $question->sign_language_video]) }}" type="video/mp4"></video>
                                </div>
                            @endif

                            <div class="prose prose-blue prose-2xl font-black text-slate-800 text-center mb-10 leading-relaxed mx-auto">
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
    <div id="bottom-bar" class="fixed bottom-0 left-0 w-full bg-white border-t-[6px] border-slate-200 p-4 md:p-6 z-50 transition-all duration-300">
        <div class="max-w-2xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div id="feedback-area" class="hidden flex-1 flex flex-col justify-center w-full">
                <div class="flex items-center gap-4 mb-2">
                    <div id="feedback-icon" class="w-14 h-14 rounded-full flex items-center justify-center font-black text-3xl bg-white shadow-sm border-4"></div>
                    <h3 id="feedback-title" class="text-2xl md:text-3xl font-black uppercase tracking-wider"></h3>
                </div>
                <p id="feedback-message" class="font-bold opacity-90 text-base md:text-lg ml-[4.5rem]"></p>
            </div>
            
            <button id="btn-action" class="btn-3d w-full md:w-auto min-w-[200px] text-white font-black text-xl md:text-2xl py-4 px-8 rounded-2xl uppercase tracking-wide border-2 border-white">
                Memuat...
            </button>
        </div>
    </div>

    <!-- MODAL SELESAI -->
    <div id="celebrationModal" class="fixed inset-0 bg-sky-900 bg-opacity-90 z-[100] hidden flex items-center justify-center backdrop-blur-sm transition-opacity">
        <div class="bg-white p-8 md:p-12 rounded-[3rem] max-w-md w-full text-center shadow-2xl border-[12px] border-yellow-400 relative mx-4">
            <div class="absolute -top-16 left-1/2 transform -translate-x-1/2 text-8xl md:text-9xl mb-6 animate-bounce drop-shadow-xl">🏆</div>
            <h2 class="text-5xl font-black text-green-500 mb-4 mt-8">Horeee!</h2>
            <p class="text-xl font-bold text-slate-600 mb-8">Kamu berhasil menyelesaikan latihan ini dengan hebat!</p>
            <button onclick="window.location.href='{{ route('student.dashboard') }}'" class="btn-3d w-full bg-sky-400 hover:bg-sky-500 text-white font-black py-5 rounded-2xl shadow-[0_8px_0_#0284c7] text-xl border-4 border-white uppercase tracking-wide">
                Lanjut Berpetualang ➔
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        function showToast(pesan) {
            const toast = document.getElementById('custom-toast');
            const toastMsg = document.getElementById('toast-message');
            toastMsg.innerText = pesan;
            toast.classList.remove('opacity-0', '-translate-y-20');
            toast.classList.add('opacity-100', 'translate-y-0');
            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', '-translate-y-20');
            }, 3000);
        }

        const slidesData = [ @foreach($slides as $slide) { type: '{{ $slide['type'] }}', id: {{ $slide['type'] == 'soal' ? $slide['data']->id : 'null' }} }, @endforeach ];
        const totalSlides = {{ $totalSlides }};
        let currentIndex = {{ $startIndex }};
        let isChecking = false;

        document.addEventListener("DOMContentLoaded", function() {
            if (currentIndex >= totalSlides) { akhiriLatihan(); } else { showSlide(currentIndex); }
        });

        function bacakanTeks(teks) {
            if ('speechSynthesis' in window) {
                window.speechSynthesis.cancel(); 
                const robot = new SpeechSynthesisUtterance(teks.replace(/<[^>]*>?/gm, ''));
                robot.lang = 'id-ID'; robot.rate = 0.85; robot.pitch = 1.2; // Diperlambat sedikit & pitch dinaikkan agar terdengar bersahabat
                window.speechSynthesis.speak(robot);
            } else {
                showToast("Yah, browsermu belum mendukung fitur suara ini.");
            }
        }

        function toggleVideo(idMap) {
            const container = document.getElementById(`video-${idMap}`);
            const player = document.getElementById(`player-${idMap}`);
            if (container.classList.contains('hidden')) {
                container.classList.remove('hidden');
                // Auto-play agar anak tidak perlu klik tombol play lagi
                player.play();
            } else {
                container.classList.add('hidden');
                player.pause();
            }
        }

        function showSlide(index) {
            document.querySelectorAll('.slide-card').forEach(el => { el.classList.add('hidden'); el.classList.remove('bounce-in'); });
            const target = document.getElementById(`slide-${index}`);
            if(target) { target.classList.remove('hidden'); void target.offsetWidth; target.classList.add('bounce-in'); }
            document.getElementById('progress-bar').style.width = `${((index + 1) / totalSlides) * 100}%`;
            resetBottomBar(slidesData[index].type);
        }

        function resetBottomBar(type) {
            isChecking = false;
            const bar = document.getElementById('bottom-bar');
            const feedback = document.getElementById('feedback-area');
            const btn = document.getElementById('btn-action');

            bar.className = 'fixed bottom-0 left-0 w-full bg-white border-t-[6px] border-slate-200 p-4 md:p-6 z-50 transition-all duration-300';
            feedback.classList.add('hidden');
            
            if (type === 'materi') {
                btn.innerHTML = 'Paham, Lanjut! ➔';
                btn.className = 'btn-3d w-full md:w-auto min-w-[200px] bg-sky-400 text-white font-black text-xl md:text-2xl py-4 px-8 rounded-2xl shadow-[0_8px_0_#0284c7] uppercase tracking-wide border-2 border-white';
                btn.setAttribute('onclick', 'slideSelanjutnya()');
            } else {
                btn.innerHTML = 'Cek Jawaban 🔍';
                btn.className = 'btn-3d w-full md:w-auto min-w-[200px] bg-green-400 text-white font-black text-xl md:text-2xl py-4 px-8 rounded-2xl shadow-[0_8px_0_#16a34a] uppercase tracking-wide border-2 border-white';
                btn.setAttribute('onclick', 'cekJawaban()');
            }
        }

        function cekJawaban() {
            if (isChecking) return;
            const currentSlide = slidesData[currentIndex];
            const form = document.getElementById('instant-form');
            const formData = new FormData(form);
            
            let jawabanTarget = formData.get(`jawaban[${currentSlide.id}]`);
            if (!jawabanTarget && formData.has(`jawaban[${currentSlide.id}][]`)) {
                jawabanTarget = formData.getAll(`jawaban[${currentSlide.id}][]`).join(' | ');
            }

            if (!jawabanTarget || jawabanTarget === '{}') {
                showToast("Ayo, isi atau pilih jawabanmu dulu ya!");
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

            if (data.is_correct) {
                // UI Jika Benar
                bar.classList.replace('bg-white', 'bg-green-100');
                bar.classList.replace('border-slate-200', 'border-green-300');
                
                document.getElementById('feedback-icon').className = 'w-14 h-14 rounded-full flex items-center justify-center font-black text-3xl bg-white border-green-200 text-green-500 shadow-sm';
                document.getElementById('feedback-icon').innerHTML = '⭐';
                document.getElementById('feedback-title').className = 'text-2xl md:text-3xl font-black uppercase tracking-wider text-green-600';
                document.getElementById('feedback-title').innerText = 'Hebat Sekali!';
                document.getElementById('feedback-message').className = 'font-bold text-green-700 opacity-90 text-base md:text-lg ml-[4.5rem]';
                document.getElementById('feedback-message').innerText = data.message || 'Kamu pintar!';
                
                btn.className = 'btn-3d w-full md:w-auto min-w-[200px] bg-green-500 text-white font-black text-xl md:text-2xl py-4 px-8 rounded-2xl shadow-[0_8px_0_#15803d] uppercase tracking-wide border-2 border-green-300';
            } else {
                // UI Jika Salah
                bar.classList.replace('bg-white', 'bg-red-100');
                bar.classList.replace('border-slate-200', 'border-red-300');
                
                document.getElementById('feedback-icon').className = 'w-14 h-14 rounded-full flex items-center justify-center font-black text-3xl bg-white border-red-200 text-red-500 shadow-sm';
                document.getElementById('feedback-icon').innerHTML = '❌';
                document.getElementById('feedback-title').className = 'text-2xl md:text-3xl font-black uppercase tracking-wider text-red-600';
                document.getElementById('feedback-title').innerText = 'Ups, Kurang Tepat';
                document.getElementById('feedback-message').className = 'font-bold text-red-700 opacity-90 text-base md:text-lg ml-[4.5rem]';
                document.getElementById('feedback-message').innerText = `Kunci: ${data.correct_answer || 'Tetap semangat, coba lagi nanti.'}`;
                
                btn.className = 'btn-3d w-full md:w-auto min-w-[200px] bg-red-500 text-white font-black text-xl md:text-2xl py-4 px-8 rounded-2xl shadow-[0_8px_0_#b91c1c] uppercase tracking-wide border-2 border-red-300';
            }

            btn.innerHTML = 'Lanjut ➔';
            btn.disabled = false;
            btn.setAttribute('onclick', 'slideSelanjutnya()');
        }

        function slideSelanjutnya() {
            if (currentIndex < totalSlides - 1) { currentIndex++; showSlide(currentIndex); } else { akhiriLatihan(); }
        }

        function akhiriLatihan() {
            document.getElementById('progress-bar').style.width = `100%`;
            fetch("{{ route('student.module.selesai-instan', $module->id) }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' }
            }).then(res => res.json()).then(data => {
                confetti({ particleCount: 200, spread: 100, origin: { y: 0.6 }, colors: ['#facc15', '#4ade80', '#38bdf8', '#c084fc'] });
                document.getElementById('celebrationModal').classList.remove('hidden');
                document.getElementById('bottom-bar').classList.add('hidden');
            }).catch(err => { alert("Gagal menyimpan status, cek koneksimu ya."); });
        }

        // Script Tarik Garis Tetap Sama seperti milikmu sebelumnya...
        // [Tidak ada perubahan pada script JS logika Tarik Garis/Matching]
    </script>
</body>
</html>