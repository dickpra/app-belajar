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
    /* Background polkadot lucu untuk anak-anak */
    body { 
        font-family: 'Nunito', sans-serif; 
        background-color: #F0F9FF; 
        background-image: radial-gradient(#bae6fd 2.5px, transparent 2.5px);
        background-size: 30px 30px;
    }
    
    /* Kartu bergaya 3D tebal khas game edukasi */
    .bubbly-card { 
        border-radius: 2rem; 
        border: 4px solid #cbd5e1;
        box-shadow: 0 10px 0 #cbd5e1; 
    }
    
    .bounce-in { animation: bounceIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
    @keyframes bounceIn { 
        0% { transform: scale(0.7) translateY(30px); opacity: 0; } 
        100% { transform: scale(1) translateY(0); opacity: 1; } 
    }
    
    /* Tombol 3D Taktil */
    .btn-3d {
        transition: all 0.1s ease-in-out;
    }
    .btn-3d:active {
        transform: translateY(6px);
        box-shadow: none !important;
        border-bottom-width: 2px !important;
    }

    /* Konfigurasi Gambar Trix */
    .prose figure.attachment a {
        pointer-events: none !important; 
        text-decoration: none !important; 
        color: inherit !important; 
        cursor: default !important; 
    }
    
    /* Style Caption Custom (Hanya berlaku jika user mengetik caption sendiri) */
    .prose figure.attachment figcaption.attachment__caption--edited { 
        display: block !important; 
        text-align: center !important; 
        font-size: 0.85rem !important; 
        font-weight: 800 !important; 
        color: #64748b !important; 
        margin-top: 1rem !important; 
        padding: 0.5rem 1rem !important; 
        background-color: #f8fafc !important; 
        border-radius: 1rem !important; 
        border: 3px dashed #cbd5e1 !important; 
        width: max-content !important; 
        max-width: 90% !important; 
        margin-left: auto !important; 
        margin-right: auto !important; 
    }

    /* 👇 JURUS RAHASIA: Sembunyikan caption bawaan Trix (yang isinya PNG/KB) 👇 */
    .prose figure.attachment figcaption:not(.attachment__caption--edited) {
        display: none !important;
    }
    
    .prose img { 
        width: 100% !important; 
        max-width: 100% !important; 
        max-height: 250px !important; 
        height: auto !important; 
        object-fit: contain !important; 
        border-radius: 1.5rem !important; 
        margin: 0 auto !important; 
        border: 4px solid #e2e8f0; 
        box-shadow: 0 6px 0 #e2e8f0; 
    }
    
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="text-slate-800 antialiased h-screen flex flex-col overflow-hidden">
    
    <!-- TOAST NOTIFIKASI -->
    <div id="custom-toast" class="fixed top-10 left-1/2 transform -translate-x-1/2 z-[200] transition-all duration-500 ease-in-out opacity-0 -translate-y-20 pointer-events-none">
        <div class="bg-red-500 border-4 border-white text-white px-6 py-4 rounded-[2rem] shadow-[0_8px_0_#991b1b] flex items-center gap-4">
            <div class="text-4xl animate-bounce drop-shadow-md">🙀</div>
            <div>
                <h4 class="font-black text-xl leading-tight">Waduh!</h4>
                <p id="toast-message" class="font-bold text-sm text-red-100">Pesan akan muncul di sini</p>
            </div>
        </div>
    </div>

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
                    'berpikir'  => ['icon' => '🤔', 'text' => 'Pemantik', 'color' => 'bg-purple-400 text-purple-900 border-purple-500'],
                    'amati'     => ['icon' => '🔍', 'text' => 'Mengamati', 'color' => 'bg-blue-400 text-blue-900 border-blue-500'],
                    'mencoba'   => ['icon' => '🧪', 'text' => 'Mencoba', 'color' => 'bg-orange-400 text-orange-900 border-orange-500'],
                    default     => ['icon' => '📖', 'text' => 'Materi', 'color' => 'bg-yellow-400 text-yellow-900 border-yellow-500'],
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
    <header class="p-4 md:p-6 flex items-center justify-between gap-5 z-10 bg-white/90 backdrop-blur-md border-b-4 border-slate-200">
        <button onclick="window.location.href='{{ route('student.dashboard') }}'" class="text-slate-400 hover:text-red-500 font-black text-3xl transition-transform hover:scale-110 active:scale-95">✖</button>
        <div class="flex-1 bg-slate-200 h-6 rounded-full overflow-hidden border-4 border-slate-300 relative shadow-inner">
            <!-- Progress bar lebih tebal dengan efek highlight -->
            <div id="progress-bar" class="bg-green-400 h-full w-0 transition-all duration-500 ease-out rounded-full relative">
                <div class="absolute top-1 left-2 right-2 h-2 bg-white/30 rounded-full"></div>
            </div>
        </div>
    </header>

    <!-- AREA SLIDES -->
    <main class="flex-1 overflow-y-auto p-4 md:p-6 pb-48 no-scrollbar relative">
        <div class="max-w-2xl mx-auto min-h-full flex flex-col justify-start w-full">
            <form id="instant-form" class="w-full h-full pb-10">
                @foreach($slides as $index => $slide)
                    <!-- KARTU SOAL -->
                    <div id="slide-{{ $index }}" class="slide-card hidden w-full bubbly-card bg-white p-6 md:p-10 mb-8">
                        
                        <div class="text-center mb-8">
                            <span class="inline-block bg-slate-100 text-slate-500 font-black px-5 py-2 rounded-2xl text-sm uppercase tracking-widest border-4 border-slate-200 shadow-[0_4px_0_#e2e8f0]">
                                🎯 {{ $slide['activity_title'] }}
                            </span>
                        </div>

                        <!-- JIKA INI SLIDE MATERI -->
                        @if($slide['type'] === 'materi')
                            @php $style = getStageStyle($slide['data']['tipe_tahapan'] ?? 'materi'); @endphp
                            <div class="text-center mb-8">
                                <span class="{{ $style['color'] }} px-6 py-2 rounded-2xl font-black text-xl border-4 shadow-[0_4px_0_rgba(0,0,0,0.1)] inline-flex items-center gap-2">
                                    {{ $style['icon'] }} {{ $style['text'] }}
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center justify-center gap-4 mb-8">
                                <button type="button" onclick="bacakanTeks(`{{ strip_tags($slide['data']['konten_tahapan'] ?? '') }}`, this)" class="btn-3d bg-blue-100 text-blue-700 font-black py-3 px-6 rounded-2xl flex items-center gap-2 border-2 border-blue-300 border-b-[6px] shadow-sm">
                                    📢 Bacakan
                                </button>
                                
                                @if(!empty($slide['data']['voice_note']))
                                <button type="button" onclick="putarVoiceNote('{{ route('private.audio', ['path' => $slide['data']['voice_note']]) }}', this)" class="btn-3d bg-emerald-100 text-emerald-700 font-black py-3 px-6 rounded-2xl flex items-center gap-2 border-2 border-emerald-300 border-b-[6px] shadow-sm">
                                    🎙️ Pesan Suara Guru
                                </button>
                                @endif

                                @if(!empty($slide['data']['sign_language_video']))
                                    <button type="button" onclick="toggleVideo('materi-{{ $index }}')" class="btn-3d bg-purple-100 text-purple-700 font-black py-3 px-6 rounded-2xl flex items-center gap-2 border-2 border-purple-300 border-b-[6px] shadow-sm">
                                        🤟 Lihat Isyarat
                                    </button>
                                @endif
                            </div>

                            @if(!empty($slide['data']['sign_language_video']))
                                <div id="video-materi-{{ $index }}" class="hidden mb-8 relative rounded-[2rem] overflow-hidden border-4 border-purple-300 shadow-[0_8px_0_#d8b4fe] bg-black">
                                    <div class="bg-purple-100 px-5 py-3 flex justify-between items-center border-b-4 border-purple-300">
                                        <span class="font-black text-purple-800 text-base flex items-center gap-2">🤟 Panduan Bahasa Isyarat</span>
                                        <button type="button" onclick="toggleVideo('materi-{{ $index }}')" class="text-red-500 hover:text-red-700 font-black text-2xl hover:scale-110 transition-transform">✖</button>
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
                                <div class="mb-8 flex justify-center bg-slate-50 p-4 rounded-3xl border-4 border-slate-200">
                                    <img src="{{ route('private.image', ['path' => $question->image]) }}" class="max-h-72 object-contain rounded-2xl">
                                </div>
                            @endif

                            <div class="flex flex-wrap items-center justify-center gap-4 mb-8">
                                <button type="button" onclick="bacakanTeks(`{{ strip_tags($question->question_text) }}`, this)" class="btn-3d bg-blue-100 text-blue-700 font-black py-3 px-6 rounded-2xl flex items-center gap-2 border-2 border-blue-300 border-b-[6px] shadow-sm">
                                    📢 Bacakan Soal
                                </button>
                                
                                @if(!empty($question->voice_note))
                                <button type="button" onclick="putarVoiceNote('{{ route('private.audio', ['path' => $question->voice_note]) }}', this)" class="btn-3d bg-emerald-100 text-emerald-700 font-black py-3 px-6 rounded-2xl flex items-center gap-2 border-2 border-emerald-300 border-b-[6px] shadow-sm">
                                    🎙️ Pesan Suara Guru
                                </button>
                                @endif

                                @if(!empty($question->sign_language_video))
                                    <button type="button" onclick="toggleVideo('soal-{{ $index }}')" class="btn-3d bg-purple-100 text-purple-700 font-black py-3 px-6 rounded-2xl flex items-center gap-2 border-2 border-purple-300 border-b-[6px] shadow-sm">
                                        🤟 Lihat Isyarat
                                    </button>
                                @endif
                            </div>

                            @if(!empty($question->sign_language_video))
                                <div id="video-soal-{{ $index }}" class="hidden mb-8 relative rounded-[2rem] overflow-hidden border-4 border-purple-300 shadow-[0_8px_0_#d8b4fe] bg-black">
                                    <div class="bg-purple-100 px-5 py-3 flex justify-between items-center border-b-4 border-purple-300">
                                        <span class="font-black text-purple-800 text-base flex items-center gap-2">🤟 Panduan Bahasa Isyarat</span>
                                        <button type="button" onclick="toggleVideo('soal-{{ $index }}')" class="text-red-500 hover:text-red-700 font-black text-2xl hover:scale-110 transition-transform">✖</button>
                                    </div>
                                    <video id="player-soal-{{ $index }}" controls class="w-full aspect-video"><source src="{{ route('private.video', ['path' => $question->sign_language_video]) }}" type="video/mp4"></video>
                                </div>
                            @endif

                            <div class="prose prose-blue prose-2xl font-black text-slate-800 text-center mb-10 leading-relaxed mx-auto">
                                {!! renderPrivateImages($question->question_text) !!}
                            </div>

                            <div class="mt-6">
                                @includeIf('student.tipe_soal.' . $question->answer_format, ['question' => $question, 'existingAnswers' => [], 'isCompleted' => false])
                            </div>
                        @endif
                    </div>
                @endforeach
            </form>
        </div>
    </main>

    <!-- BOTTOM ACTION BAR -->
    <div id="bottom-bar" class="fixed bottom-0 left-0 w-full bg-white border-t-4 border-slate-200 p-5 md:p-8 z-50 transition-all duration-300">
        <div class="max-w-2xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
            <div id="feedback-area" class="hidden flex-1 flex flex-col justify-center w-full">
                <div class="flex items-center gap-4 mb-2">
                    <div id="feedback-icon" class="w-14 h-14 rounded-full flex items-center justify-center font-black text-3xl bg-white border-4 shadow-sm"></div>
                    <h3 id="feedback-title" class="text-2xl md:text-3xl font-black uppercase tracking-wider"></h3>
                </div>
                <p id="feedback-message" class="font-bold opacity-90 text-base md:text-lg ml-1"></p>
            </div>
            <button id="btn-action" class="btn-3d w-full md:w-auto min-w-[220px] text-white font-black text-2xl py-5 px-8 rounded-2xl uppercase tracking-wider border-2 border-transparent">
                Memuat...
            </button>
        </div>
    </div>

    <!-- MODAL SELESAI -->
    <div id="celebrationModal" class="fixed inset-0 bg-slate-900/60 z-[100] hidden flex items-center justify-center backdrop-blur-sm transition-opacity px-4">
        <div class="bg-white p-10 md:p-14 rounded-[3rem] max-w-lg w-full text-center border-8 border-yellow-400 shadow-[0_15px_0_#ca8a04] relative">
            <div class="text-8xl md:text-9xl mb-8 animate-bounce drop-shadow-xl">🏆</div>
            <h2 class="text-5xl font-black text-green-500 mb-4 tracking-wide">Yey, Selesai!</h2>
            <p class="text-2xl font-bold text-slate-500 mb-10">Kamu hebat banget! Nilaimu sudah tersimpan.</p>
            <button onclick="window.location.href='{{ route('student.dashboard') }}'" class="btn-3d w-full bg-blue-500 text-white font-black py-5 rounded-2xl border-2 border-blue-400 border-b-[8px] border-b-blue-700 text-2xl tracking-wider">
                Selesai Berpetualang ➔
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
            }, 3500);
        }
        
        const slidesData = [
            @foreach($slides as $slide)
                { type: '{{ $slide['type'] }}', id: {{ $slide['type'] == 'soal' ? $slide['data']->id : 'null' }} },
            @endforeach
        ];
        const totalSlides = {{ $totalSlides }};
        let currentIndex = {{ $startIndex }};
        let isChecking = false;

        document.addEventListener("DOMContentLoaded", function() {
            if (currentIndex >= totalSlides) akhiriLatihan();
            else showSlide(currentIndex);
        });

        let robotBicara = false;
        // Variabel global untuk menampung instance audio guru
        let guruAudio = null;

        function bacakanTeks(htmlTeks, btnElement) {
            if (!('speechSynthesis' in window)) {
                showToast("Yah, browsermu belum mendukung fitur suara ini.");
                return;
            }
            
            // Hentikan voice note guru jika sedang diputar
            if (guruAudio && !guruAudio.paused) {
                guruAudio.pause();
                guruAudio.currentTime = 0;
                document.querySelectorAll('button').forEach(btn => {
                    if (btn.innerText.includes('Hentikan Suara Guru')) {
                        btn.innerHTML = btn.innerHTML.replace('⏹️ Hentikan Suara Guru', '🎙️ Pesan Suara Guru');
                    }
                });
            }

            // 👇 FITUR STOP/BERHENTI 👇
            if (robotBicara) {
                window.speechSynthesis.cancel();
                robotBicara = false;
                if(btnElement) {
                    // Mengembalikan teks tombol sesuai dengan konteksnya
                    if(btnElement.innerHTML.includes('Soal')) {
                        btnElement.innerHTML = '📢 Bacakan Soal';
                    } else {
                        btnElement.innerHTML = '📢 Bacakan';
                    }
                }
                return;
            }

            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = htmlTeks;
            const sampah = tempDiv.querySelectorAll('.attachment__name, .attachment__size');
            sampah.forEach(el => el.remove());
            let teksBersih = tempDiv.innerText || tempDiv.textContent;

            teksBersih = teksBersih
                .replace(/[a-zA-Z0-9_-]+\.(png|jpg|jpeg|gif|webp|svg)(\s+\d+([.,]\d+)?\s*(KB|MB|GB))?/gi, '')
                .replace(/ /g, ' ').replace(/[_]/g, ' ').replace(/\s+/g, ' ')          
                .replace(/([.!?])\s*(?=[a-zA-Z])/g, '$1 ').trim();

            const robot = new SpeechSynthesisUtterance(teksBersih);
            robot.lang = 'id-ID'; 
            robot.rate = 0.9;  
            robot.pitch = 1.1; 
            
            // Kembalikan tombol saat suara selesai
            robot.onend = function() {
                robotBicara = false;
                if(btnElement) {
                     if(btnElement.innerHTML.includes('Soal')) {
                        btnElement.innerHTML = '📢 Bacakan Soal';
                    } else {
                        btnElement.innerHTML = '📢 Bacakan';
                    }
                }
            };

            if(btnElement) btnElement.innerHTML = '⏹️ Hentikan Suara';
            
            window.speechSynthesis.speak(robot);
            robotBicara = true;
        }

        function putarVoiceNote(urlAudio, btnElement) {
            // Hentikan suara robot TTS jika sedang bicara
            if (robotBicara && window.speechSynthesis) {
                window.speechSynthesis.cancel();
                robotBicara = false;
                // Kembalikan semua tombol TTS ke keadaan semula
                document.querySelectorAll('button').forEach(btn => {
                    if (btn.innerText.includes('Hentikan Suara') && !btn.innerText.includes('Guru')) {
                        if(btn.innerHTML.includes('Soal')) {
                            btn.innerHTML = btn.innerHTML.replace('⏹️ Hentikan Suara', '📢 Bacakan Soal');
                        } else {
                            btn.innerHTML = btn.innerHTML.replace('⏹️ Hentikan Suara', '📢 Bacakan');
                        }
                    }
                });
            }

            // Jika audio guru sedang diputar, hentikan
            if (guruAudio && !guruAudio.paused) {
                guruAudio.pause();
                guruAudio.currentTime = 0;
                if(btnElement) btnElement.innerHTML = '🎙️ Pesan Suara Guru';
                return;
            }

            // Buat instance audio baru dan putar
            guruAudio = new Audio(urlAudio);
            
            if(btnElement) {
                btnElement.innerHTML = '⏹️ Hentikan Suara Guru';
                
                guruAudio.onend = function() {
                    btnElement.innerHTML = '🎙️ Pesan Suara Guru';
                };
                
                guruAudio.onerror = function() {
                    showToast("Yah, gagal memuat pesan suara guru.");
                    btnElement.innerHTML = '🎙️ Pesan Suara Guru';
                };
            }
            
            guruAudio.play();
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

        function showSlide(index) {
            document.querySelectorAll('.slide-card').forEach(el => {
                el.classList.add('hidden');
                el.classList.remove('bounce-in');
            });
            const target = document.getElementById(`slide-${index}`);
            if(target) {
                target.classList.remove('hidden');
                void target.offsetWidth;
                target.classList.add('bounce-in');
            }
            document.getElementById('progress-bar').style.width = `${((index + 1) / totalSlides) * 100}%`;
            resetBottomBar(slidesData[index].type);
            
            // Hentikan semua audio jika berpindah slide
            if (robotBicara && window.speechSynthesis) {
                window.speechSynthesis.cancel();
                robotBicara = false;
            }
            if (guruAudio && !guruAudio.paused) {
                guruAudio.pause();
                guruAudio.currentTime = 0;
            }
             document.querySelectorAll('button').forEach(btn => {
                if (btn.innerText.includes('Hentikan Suara Guru')) {
                    btn.innerHTML = btn.innerHTML.replace('⏹️ Hentikan Suara Guru', '🎙️ Pesan Suara Guru');
                }
                 if (btn.innerText.includes('Hentikan Suara') && !btn.innerText.includes('Guru')) {
                        if(btn.innerHTML.includes('Soal')) {
                            btn.innerHTML = btn.innerHTML.replace('⏹️ Hentikan Suara', '📢 Bacakan Soal');
                        } else {
                            btn.innerHTML = btn.innerHTML.replace('⏹️ Hentikan Suara', '📢 Bacakan');
                        }
                    }
            });
        }

        function resetBottomBar(type) {
            isChecking = false;
            const bar = document.getElementById('bottom-bar');
            const feedback = document.getElementById('feedback-area');
            const btn = document.getElementById('btn-action');

            bar.className = 'fixed bottom-0 left-0 w-full bg-white border-t-4 border-slate-200 p-5 md:p-8 z-50 transition-all duration-300';
            feedback.classList.add('hidden');
            
            if (type === 'materi') {
                btn.innerHTML = 'Paham, Lanjut! ➔';
                btn.className = 'btn-3d w-full md:w-auto min-w-[220px] bg-blue-500 text-white font-black text-2xl py-5 px-8 rounded-2xl border-2 border-blue-400 border-b-[8px] border-b-blue-700 uppercase tracking-wider';
                btn.setAttribute('onclick', 'slideSelanjutnya()');
            } else {
                btn.innerHTML = 'Cek Jawaban 🔍';
                btn.className = 'btn-3d w-full md:w-auto min-w-[220px] bg-green-500 text-white font-black text-2xl py-5 px-8 rounded-2xl border-2 border-green-400 border-b-[8px] border-b-green-700 uppercase tracking-wider';
                btn.setAttribute('onclick', 'cekJawaban()');
            }
        }

        function cekJawaban() {
            if (isChecking) return;
            const currentSlide = slidesData[currentIndex];
            const form = document.getElementById('instant-form');
            const formData = new FormData(form);
            
            // 1. Ambil format jawaban standar (radio single, input angka, teks, matching)
            let jawabanTarget = formData.get(`jawaban[${currentSlide.id}]`);
            
            // 2. Ambil format array (isian rumpang)
            if (!jawabanTarget && formData.has(`jawaban[${currentSlide.id}][]`)) {
                jawabanTarget = formData.getAll(`jawaban[${currentSlide.id}][]`).join(' | ');
            }

            // 3. Ambil format objek (Benar/Salah)
            if (!jawabanTarget && formData.has(`jawaban[${currentSlide.id}][pilihan]`)) {
                let pilihan = formData.get(`jawaban[${currentSlide.id}][pilihan]`);
                let perbaikan = formData.get(`jawaban[${currentSlide.id}][perbaikan]`) || '';
                if (pilihan) {
                    jawabanTarget = { pilihan: pilihan, perbaikan: perbaikan };
                }
            }

            if (!jawabanTarget || jawabanTarget === '{}' || jawabanTarget === '') {
                showToast("Ayo, isi atau pilih jawabanmu dulu ya! 🤓");
                return;
            }

            const btn = document.getElementById('btn-action');
            btn.innerHTML = '⏳ Mengecek...';
            btn.disabled = true;

            fetch("{{ route('student.module.cek-instan') }}", {
                method: 'POST',
                headers: { 
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ question_id: currentSlide.id, jawaban: jawabanTarget })
            })
            .then(async res => {
                if (!res.ok) {
                    const errData = await res.json().catch(() => ({ message: 'Kesalahan server status ' + res.status }));
                    throw new Error(errData.message || 'Gagal memproses jawaban.');
                }
                return res.json();
            })
            .then(data => tampilkanHasil(data))
            .catch(err => { 
                console.error('Detail Error:', err);
                showToast(err.message || "Ups, Koneksi terputus."); 
                btn.innerHTML = 'Coba Lagi 🔍'; 
                btn.disabled = false; 
            });
        }

        function tampilkanHasil(data) {
            isChecking = true;
            const bar = document.getElementById('bottom-bar');
            const feedback = document.getElementById('feedback-area');
            const btn = document.getElementById('btn-action');
            
            feedback.classList.remove('hidden');
            bar.classList.remove('bg-white', 'border-slate-200');

            if (data.is_correct) {
                bar.classList.add('bg-green-100', 'border-green-400');
                document.getElementById('feedback-icon').className = 'w-14 h-14 rounded-full flex items-center justify-center font-black text-3xl bg-white border-4 border-green-200 text-green-500';
                document.getElementById('feedback-icon').innerHTML = '⭐';
                document.getElementById('feedback-title').className = 'text-2xl md:text-3xl font-black uppercase tracking-wider text-green-600';
                document.getElementById('feedback-title').innerText = 'Hebat Banget!';
                document.getElementById('feedback-message').className = 'font-bold text-green-700 opacity-90 text-base md:text-lg ml-1';
                document.getElementById('feedback-message').innerText = data.message;
                
                btn.className = 'btn-3d w-full md:w-auto min-w-[220px] bg-green-500 text-white font-black text-2xl py-5 px-8 rounded-2xl border-2 border-green-400 border-b-[8px] border-b-green-700 uppercase tracking-wider';
            } else {
                bar.classList.add('bg-red-100', 'border-red-400');
                document.getElementById('feedback-icon').className = 'w-14 h-14 rounded-full flex items-center justify-center font-black text-3xl bg-white border-4 border-red-200 text-red-500';
                document.getElementById('feedback-icon').innerHTML = '❌';
                document.getElementById('feedback-title').className = 'text-2xl md:text-3xl font-black uppercase tracking-wider text-red-600';
                document.getElementById('feedback-title').innerText = 'Hampir Benar';
                document.getElementById('feedback-message').className = 'font-bold text-red-700 opacity-90 text-base md:text-lg ml-1';
                document.getElementById('feedback-message').innerText = `Kunci: ${data.correct_answer || 'Tetap semangat, perhatikan lagi ya!'}`;
                
                btn.className = 'btn-3d w-full md:w-auto min-w-[220px] bg-red-500 text-white font-black text-2xl py-5 px-8 rounded-2xl border-2 border-red-400 border-b-[8px] border-b-red-700 uppercase tracking-wider';
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
            document.getElementById('progress-bar').style.width = `100%`;
            // 👇 PERHATIKAN PENAMBAHAN acak_id() DI BAWAH INI 👇
            fetch("{{ route('student.module.selesai-instan', acak_id($module->id)) }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Content-Type': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                confetti({ particleCount: 200, spread: 100, origin: { y: 0.6 } });
                document.getElementById('celebrationModal').classList.remove('hidden');
                document.getElementById('bottom-bar').classList.add('hidden');
            })
            .catch(err => {
                showToast("Gagal menyimpan status, cek koneksimu ya.");
            });
        }

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