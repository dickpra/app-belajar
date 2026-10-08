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
@laravelPWA
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
    <!-- 👇 POPUP HINT MELAYANG DARI ATAS 👇 -->
    <div id="hint-popup" class="fixed top-4 md:top-6 left-1/2 transform -translate-x-1/2 z-[250] transition-all duration-500 ease-out opacity-0 -translate-y-24 pointer-events-none w-[90%] max-w-sm">
        <div class="bg-amber-50 border-4 border-amber-300 px-5 py-4 rounded-3xl shadow-[0_8px_0_#fcd34d] flex items-start gap-3 relative pointer-events-auto">
            <!-- Tombol Tutup (Silang) -->
            <button onclick="hideHintPopup()" class="absolute -top-3 -right-3 w-8 h-8 bg-white border-4 border-amber-200 text-slate-400 font-black rounded-full flex items-center justify-center hover:bg-red-100 hover:text-red-500 hover:border-red-300 shadow-sm transition-colors cursor-pointer z-10 text-sm">✖</button>
            
            <div class="text-3xl animate-bounce drop-shadow-sm mt-1">💡</div>
            <div class="flex-1">
                <h4 class="font-black text-sm text-amber-500 uppercase tracking-widest mb-1">Petunjuk</h4>
                <!-- TEKS PUZZLE AKAN MASUK KE SINI -->
                <p id="hint-popup-text" class="font-bold text-amber-800 text-sm md:text-lg tracking-[0.1em] leading-tight"></p>
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
        <div class="max-w-4xl mx-auto min-h-full flex flex-col justify-start w-full">
            <form id="instant-form" class="w-full h-full pb-10">
                @foreach($slides as $index => $slide)
                    <!-- KARTU SOAL / MATERI -->
                    <!-- Pastikan ada class "relative" di div ini agar tombol bisa ditaruh di pojok -->
                    <div id="slide-{{ $index }}" class="slide-card hidden w-full bubbly-card bg-white p-6 md:p-10 mb-8 relative">
                        
                        <!-- 👇 TAMBAHKAN TOMBOL KEMBALI DI SINI 👇 -->
                        @if($index > 0)
                            <button type="button" onclick="slideMundur()" class="absolute top-6 left-5 md:left-8 text-slate-400 hover:text-blue-500 font-black text-sm md:text-base flex items-center gap-1.5 transition-all z-20 hover:-translate-x-1">
                                <span class="text-lg md:text-xl">⬅️</span> <span class="hidden md:inline">Kembali</span>
                            </button>
                        @endif
                        <!-- 👆 ================================ 👆 -->

                        <div class="text-center mb-8 mt-8 md:mt-0">
                            <span class="inline-block bg-slate-100 text-slate-500 font-black px-5 py-2 rounded-2xl text-sm uppercase tracking-widest border-4 border-slate-200 shadow-[0_4px_0_#e2e8f0]">
                                🎯 {{ $slide['activity_title'] }}
                            </span>
                        </div>

                        <!-- 👇 1. KOTAK HINT KECIL DI ATAS SOAL 👇 -->
                        <div id="hint-area-{{ $index }}" class="hidden mb-6 mx-auto max-w-sm bg-amber-50 border-2 border-amber-300 rounded-xl p-3 text-center shadow-sm transition-all duration-300">
                            <span class="text-[10px] font-black text-amber-500 uppercase tracking-widest block mb-1">💡 Petunjuk</span>
                            <span id="hint-text-{{ $index }}" class="text-amber-800 font-bold text-base md:text-lg tracking-widest"></span>
                        </div>
                        <!-- 👆 ================================== 👆 -->

                        <!-- ===================================== -->
                        <!-- JIKA INI SLIDE MATERI                 -->
                        <!-- ===================================== -->
                        @if($slide['type'] === 'materi')
                            @php $style = getStageStyle($slide['data']['tipe_tahapan'] ?? 'materi'); @endphp
                            <div class="text-center mb-8">
                                <span class="{{ $style['color'] }} px-6 py-2 rounded-2xl font-black text-xl border-4 shadow-[0_4px_0_rgba(0,0,0,0.1)] inline-flex items-center gap-2">
                                    {{ $style['icon'] }} {{ $style['text'] }}
                                </span>
                            </div>

                            <!-- TAMPILAN GAMBAR MATERI (FLEXBOX CENTER) -->
                            @if(!empty($slide['data']['image']))
                                <div class="mb-8 flex justify-center w-full">
                                    <img src="{{ route('private.image', ['path' => $slide['data']['image']]) }}" class="max-h-[350px] object-contain rounded-3xl border-4 border-slate-200 shadow-sm bg-slate-50 p-2">
                                </div>
                            @endif

                            <div class="prose prose-blue prose-base md:prose-xl text-slate-700 mx-auto leading-relaxed w-full max-w-2xl text-center mb-6 md:mb-8">
                                {!! renderPrivateImages($slide['data']['konten_tahapan'] ?? '') !!}
                            </div>

                           <!-- TOMBOL BANTUAN MATERI (VERSI MOBILE COMPACT) -->
                            <div class="flex flex-wrap items-center justify-center gap-2 md:gap-4 mb-4">
                                <button type="button" onclick="bacakanTeks(`{{ strip_tags($slide['data']['konten_tahapan'] ?? '') }}`, this)" 
                                    class="btn-3d bg-blue-100 text-blue-700 font-black text-xs md:text-base py-2 px-3 md:py-3 md:px-6 rounded-xl md:rounded-2xl flex items-center gap-1.5 md:gap-2 border-2 border-blue-300 border-b-[4px] md:border-b-[6px] shadow-sm">
                                    <span class="text-sm md:text-xl">📢</span> Bacakan
                                </button>

                                @if(!empty($slide['data']['voice_note']))
                                <button type="button" onclick="putarVoiceNote('{{ route('private.audio', ['path' => $slide['data']['voice_note']]) }}', this)" 
                                    class="btn-3d bg-emerald-100 text-emerald-700 font-black text-xs md:text-base py-2 px-3 md:py-3 md:px-6 rounded-xl md:rounded-2xl flex items-center gap-1.5 md:gap-2 border-2 border-emerald-300 border-b-[4px] md:border-b-[6px] shadow-sm">
                                    <span class="text-sm md:text-xl">🎙️</span> Suara Guru
                                </button>
                                @endif

                                @if(!empty($slide['data']['sign_language_video']))
                                    <button type="button" onclick="toggleVideo('materi-{{ $index }}')" 
                                        class="btn-3d bg-purple-100 text-purple-700 font-black text-xs md:text-base py-2 px-3 md:py-3 md:px-6 rounded-xl md:rounded-2xl flex items-center gap-1.5 md:gap-2 border-2 border-purple-300 border-b-[4px] md:border-b-[6px] shadow-sm">
                                        <span class="text-sm md:text-xl">🤟</span> Isyarat
                                    </button>
                                @endif
                            </div>

                            <!-- POPUP VIDEO ISYARAT MATERI -->
                            @if(!empty($slide['data']['sign_language_video']))
                                <div id="video-materi-{{ $index }}" class="hidden relative rounded-[2rem] overflow-hidden border-4 border-purple-300 shadow-[0_8px_0_#d8b4fe] bg-black max-w-2xl mx-auto mt-6">
                                    <div class="bg-purple-100 px-5 py-3 flex justify-between items-center border-b-4 border-purple-300">
                                        <span class="font-black text-purple-800 text-base flex items-center gap-2">🤟 Panduan Isyarat</span>
                                        <button type="button" onclick="toggleVideo('materi-{{ $index }}')" class="text-red-500 hover:text-red-700 font-black text-2xl hover:scale-110 transition-transform">✖</button>
                                    </div>
                                    <video id="player-materi-{{ $index }}" controls class="w-full aspect-video"><source src="{{ route('private.video', ['path' => $slide['data']['sign_language_video']]) }}" type="video/mp4"></video>
                                </div>
                            @endif


                        <!-- ===================================== -->
                        <!-- JIKA INI SLIDE SOAL                   -->
                        <!-- ===================================== -->
                        @elseif($slide['type'] === 'soal')
                            @php 
                                $question =$slide['data']; 
                                $layout =$question->layout_position ?? 'image_top';
                                
                                $flexClass = match($layout) {
                                    'image_left'   => 'flex-col md:flex-row',
                                    'image_right'  => 'flex-col md:flex-row-reverse',
                                    'image_bottom' => 'flex-col-reverse',
                                    default        => 'flex-col' // image_top
                                };

                                $isSideBySide = in_array($layout, ['image_left', 'image_right']);
                            @endphp

                            <!-- 1. TOMBOL MEDIA GURU SOAL (DIPINDAH KE PALING ATAS) -->
                            <!-- 1. TOMBOL MEDIA GURU SOAL (VERSI MOBILE COMPACT) -->
                            <div class="flex flex-wrap items-center justify-center gap-2 md:gap-4 mb-8 mt-2">
                                <button type="button" onclick="bacakanTeks(`{{ strip_tags($question->question_text) }}`, this)" 
                                    class="btn-3d bg-blue-100 text-blue-700 font-black text-xs md:text-base py-2 px-3 md:py-3 md:px-6 rounded-xl md:rounded-2xl flex items-center gap-1.5 md:gap-2 border-2 border-blue-300 border-b-[4px] md:border-b-[6px] shadow-sm">
                                    <span class="text-sm md:text-xl">📢</span> Bacakan
                                </button>

                                @if(!empty($question->voice_note))
                                <button type="button" onclick="putarVoiceNote('{{ route('private.audio', ['path' => $question->voice_note]) }}', this)" 
                                    class="btn-3d bg-emerald-100 text-emerald-700 font-black text-xs md:text-base py-2 px-3 md:py-3 md:px-6 rounded-xl md:rounded-2xl flex items-center gap-1.5 md:gap-2 border-2 border-emerald-300 border-b-[4px] md:border-b-[6px] shadow-sm">
                                    <span class="text-sm md:text-xl">🎙️️</span> Suara Guru
                                </button>
                                @endif

                                @if(!empty($question->sign_language_video))
                                    <button type="button" onclick="toggleVideo('soal-{{ $index }}')" 
                                        class="btn-3d bg-purple-100 text-purple-700 font-black text-xs md:text-base py-2 px-3 md:py-3 md:px-6 rounded-xl md:rounded-2xl flex items-center gap-1.5 md:gap-2 border-2 border-purple-300 border-b-[4px] md:border-b-[6px] shadow-sm">
                                        <span class="text-sm md:text-xl">🤟</span> Isyarat
                                    </button>
                                @endif
                            </div>

                            <!-- POPUP VIDEO ISYARAT SOAL -->
                            @if(!empty($question->sign_language_video))
                                <div id="video-soal-{{ $index }}" class="hidden mb-8 relative rounded-[2rem] overflow-hidden border-4 border-purple-300 shadow-[0_8px_0_#d8b4fe] bg-black max-w-2xl mx-auto">
                                    <div class="bg-purple-100 px-5 py-3 flex justify-between items-center border-b-4 border-purple-300">
                                        <span class="font-black text-purple-800 text-base flex items-center gap-2">🤟 Panduan Isyarat</span>
                                        <button type="button" onclick="toggleVideo('soal-{{ $index }}')" class="text-red-500 hover:text-red-700 font-black text-2xl hover:scale-110 transition-transform">✖</button>
                                    </div>
                                    <video id="player-soal-{{ $index }}" controls class="w-full aspect-video"><source src="{{ route('private.video', ['path' => $question->sign_language_video]) }}" type="video/mp4"></video>
                                </div>
                            @endif

                            <!-- 2. KONTEN SOAL (FLEXBOX MAGIC) -->
                            <div class="flex {{ $flexClass }} items-center justify-center gap-6 md:gap-10 mb-8 w-full">
                                
                                <!-- AREA GAMBAR UPLOAD -->
                                @if(!empty($question->image))
                                    <div class="w-full {{ $isSideBySide ? 'md:w-1/2' : 'max-w-2xl' }} flex justify-center shrink-0">
                                        <img src="{{ route('private.image', ['path' => $question->image]) }}" class="max-h-[320px] w-auto object-contain rounded-3xl border-4 border-slate-200 shadow-sm bg-slate-50 p-2">
                                    </div>
                                @endif

                                <!-- AREA TEKS SOAL (TRIX) -->
                                <div class="w-full {{ $isSideBySide && !empty($question->image) ? 'md:w-1/2 text-left' : 'max-w-2xl text-center' }} flex flex-col justify-center">
                                    
                                    @if($question->answer_format === 'complex_fill')
                                        {{-- 🎯 Panggil komponen Isian Rumpang dengan mengirim data riwayat jawaban --}}
                                        @include('student.tipe_soal.complex_fill', [
                                            'question' => $question,
                                            'existingAnswers' => $existingAnswers ?? [],
                                            'isCompleted' => $slide['is_answered'] ?? false
                                        ])
                                    @else
                                        <div class="prose prose-blue prose-lg md:prose-2xl text-slate-800 leading-relaxed mx-auto w-full font-medium">
                                            {!! renderPrivateImages($question->question_text) !!}
                                        </div>
                                    @endif
                                    
                                </div>

                            </div>

                            <!-- 3. JAWABAN (OPSI / ISIAN / MENJODOHKAN) -->
                            {{-- Sembunyikan area bawah khusus Isian Rumpang karena jawabannya sudah menyatu di atas --}}
                            @if($question->answer_format !== 'complex_fill')
                                <div class="mt-8 border-t-4 border-dashed border-slate-200 pt-8 max-w-2xl mx-auto w-full">
                                    @includeIf('student.tipe_soal.' . $question->answer_format, [
                                        'question' => $question, 
                                        'existingAnswers' => $existingAnswers ?? [], 
                                        'isCompleted' => $slide['is_answered'] ?? false
                                    ])
                                </div>
                            @endif

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

                <!-- 👇 2. TOMBOL LEWATI KECIL 👇 -->
                <button id="btn-lewati-kecil" type="button" onclick="lewatiSoal()" class="hidden text-xs md:text-sm font-bold text-slate-400 hover:text-slate-600 underline underline-offset-4 mt-2 text-left ml-1 w-max transition-colors">
                    ⏭️️ Nyerah dan lewati soal ini
                </button>
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
        
        // Membaca status jawaban dari database untuk dilempar ke otak JavaScript
        const slidesData = [
            @foreach($slides as $slide)
                { 
                    type: '{{ $slide['type'] }}', 
                    id: {{ $slide['type'] == 'soal' ? $slide['data']->id : 'null' }},
                    // Beritahu JS format soalnya apa
                    format: '{{ $slide['type'] == 'soal' ? ($slide['data']->answer_format ?? '') : '' }}',
                    is_answered: {{ (isset($slide['is_answered']) && $slide['is_answered']) ? 'true' : 'false' }} 
                },
            @endforeach
        ];
        const totalSlides = {{ $totalSlides }};
        let currentIndex = {{ $startIndex }};
        let isChecking = false;

        // 👇 TAMBAHAN UNTUK SMART HINT 👇
        const isHintEnabled = {{ $module->is_hint_enabled ?? 'false' }};
        let salahCountData = {}; // Menyimpan jumlah salah tiap-tiap soal

        // 👇 FUNGSI PENGGERAK POPUP 👇
        function showHintPopup(pesan) {
            const popup = document.getElementById('hint-popup');
            const text = document.getElementById('hint-popup-text');
            text.innerText = pesan;
            popup.classList.remove('opacity-0', '-translate-y-24', 'pointer-events-none');
            popup.classList.add('opacity-100', 'translate-y-0');
        }

        function hideHintPopup() {
            const popup = document.getElementById('hint-popup');
            popup.classList.remove('opacity-100', 'translate-y-0');
            popup.classList.add('opacity-0', '-translate-y-24', 'pointer-events-none');
        }

        // 👇 1. KITA PISAHKAN MESIN PUZZLE AGAR BISA DIPAKAI ULANG 👇
        // 👇 FUNGSI MESIN PUZZLE YANG LEBIH CERDAS 👇
        function buatTeksPuzzle(kunci, mistakes) {
            if (!kunci) return 'Ayo teliti lagi!';
            let kataArray = kunci.toString().toUpperCase().split(' ');
            
            let hasilPuzzle = kataArray.map(kata => {
                let chars = kata.split('');
                return chars.map((huruf, index) => {
                    // KESALAHAN KE-2: Selalu garis bawah semuanya
                    if (mistakes === 2) return '_';
                    
                    // KESALAHAN KE-3: Buka sedikit
                    if (mistakes === 3) {
                        if (chars.length === 1) return '_'; // Kalau cuma 1 digit (misal: "5"), tetap rahasiakan!
                        if (chars.length === 2 && index === 0) return huruf; // Kalau 2 digit (misal: "21"), buka depannya saja -> "2 _"
                        if (chars.length > 2 && (index === 0 || index === chars.length - 1)) return huruf; // Buka ujung & ujung
                        return '_';
                    }
                    
                    // KESALAHAN KE-4: Buka lebih banyak
                    if (mistakes >= 4) {
                        if (chars.length === 1) return '_'; // Tetap biarkan mikir untuk 1 digit
                        if (chars.length === 2 && index === 0) return huruf; // Tetap "2 _" agar digit terakhir ditebak sendiri
                        
                        // Untuk kata panjang, buka huruf depan, belakang, dan posisi genap
                        if (index === 0 || index === chars.length - 1 || index % 2 === 0) return huruf;
                        return '_';
                    }
                    
                    return '_';
                }).join(' '); 
            });

            return hasilPuzzle.join(' \u00A0\u00A0\u00A0 '); 
        }


        // 👇 2. FUNGSI HINT ROUTER UTAMA 👇
        function generateSmartHint(kunci, format, mistakes) {
            const tipePuzzle = ['text_input', 'number_input', 'complex_fill'];

            // JIKA TIPE ISIAN TEKS / ANGKA
            if (tipePuzzle.includes(format)) {
                return buatTeksPuzzle(kunci, mistakes);
            } 
            
            // JIKA TIPE BENAR/SALAH (TRUE FALSE CORRECTION)
            else if (format === 'true_false_correction') {
                // Cek apakah server membalas dengan adanya teks "(Perbaikan: ...)"
                if (kunci && kunci.includes('(Perbaikan:')) {
                    // Ambil teks asli yang ada di dalam kurung perbaikan
                    let teksPerbaikan = kunci.split('(Perbaikan: ')[1].replace(')', '').trim();
                    // Berikan puzzle huruf khusus untuk teks perbaikannya!
                    return "Pernyataan itu SALAH. Ketik perbaikannya: " + buatTeksPuzzle(teksPerbaikan, mistakes);
                } else {
                    return "Coba teliti lagi pernyataannya, apakah Benar atau Salah? 🤔";
                }
            } 
            
            // JIKA TIPE MENJODOHKAN
            else if (format === 'matching') {
                return "Periksa lagi arah garismu, ada kotak yang salah pasangan! 🧶";
            } 
            
            // JIKA TIPE PILIHAN GANDA
            else if (format === 'multiple_choice') {
                return "Coba eliminasi jawaban yang paling tidak mungkin! 🤔";
            } 
            
            else {
                return "Ayo semangat, baca soalnya pelan-pelan!";
            }
        }

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
                
                if(slidesData[index].type === 'soal') {
                    
                    if(slidesData[index].is_answered) {
                        kunciForm(index);
                    }

                    // 👇 1. KOSONGKAN CANVAS SVG LAMA 👇
                    let soalId = slidesData[index].id;
                    let svg = document.getElementById(`svg-canvas-${soalId}`);
                    if(svg) svg.innerHTML = ''; 

                    // 👇 2. TUNGGU ANIMASI SELESAI (750ms) BARU GAMBAR GARIS 👇
                    setTimeout(() => {
                        let hiddenInput = document.getElementById(`ans-${soalId}`);
                        if(hiddenInput && hiddenInput.value && hiddenInput.value !== '{}') {
                            try {
                                let rawVal = hiddenInput.value;
                                let ans = typeof rawVal === 'string' ? JSON.parse(rawVal) : rawVal;
                                if (typeof ans === 'string') ans = JSON.parse(ans); 
                                
                                for(let key in ans) {
                                    let btnKiri = Array.from(document.querySelectorAll(`.btn-kiri-${soalId}`)).find(el => el.dataset.nilai == key);
                                    let btnKanan = Array.from(document.querySelectorAll(`.btn-kanan-${soalId}`)).find(el => el.dataset.nilai == ans[key]);
                                    
                                    if(btnKiri && btnKanan) {
                                        btnKanan.classList.add('border-green-500', 'bg-green-50', 'terjawab');
                                        btnKanan.querySelector('.konektor-kanan').classList.replace('bg-slate-200', 'bg-green-500');
                                        
                                        btnKiri.classList.add('border-green-500', 'bg-green-50', 'terjawab');
                                        btnKiri.classList.remove('border-blue-500', 'bg-blue-50', 'ring-4', 'ring-blue-100');
                                        btnKiri.querySelector('.konektor-kiri').classList.replace('bg-slate-200', 'bg-green-500');
                                        
                                        gambarGarisSVG(btnKiri, btnKanan, soalId);
                                    }
                                }
                            } catch(e) {}
                        }
                    }, 750); // Waktu tunggu ekstra agar kordinat sempurna
                }
            }
            
            document.getElementById('progress-bar').style.width = `${((index + 1) / totalSlides) * 100}%`;
            resetBottomBar(slidesData[index].type);
            
            if (robotBicara && window.speechSynthesis) window.speechSynthesis.cancel();
            if (guruAudio && !guruAudio.paused) guruAudio.pause();
        }

        // FUNGSI BARU UNTUK MENGUNCI KLIK
        function kunciForm(index) {
            const slideArea = document.getElementById(`slide-${index}`);
            // Nonaktifkan semua input, textarea, dan radio
            slideArea.querySelectorAll('input, textarea').forEach(el => el.disabled = true);
            // Matikan fungsi klik pada tombol menjodohkan
            slideArea.querySelectorAll('.btn-kiri-' + slidesData[index].id + ', .btn-kanan-' + slidesData[index].id).forEach(btn => {
                btn.removeAttribute('onclick');
                btn.classList.add('cursor-not-allowed', 'opacity-80');
            });
        }

        function resetBottomBar(type) {
        isChecking = false;
        
        // 1. Pastikan popup hint selalu tertutup saat bar di-reset
        if (typeof hideHintPopup === 'function') {
            hideHintPopup();
        }

        const bar = document.getElementById('bottom-bar');
        const feedback = document.getElementById('feedback-area');
        const btn = document.getElementById('btn-action');

        // 👇 2. INI OBAT BUG-NYA: Kembalikan wujud tombol Nyerah seperti semula 👇
        const btnLewati = document.getElementById('btn-lewati-kecil');
        if (btnLewati) {
            btnLewati.classList.add('hidden'); // Sembunyikan lagi
            btnLewati.innerText = '⏭ Nyerah dan lewati soal ini'; // Balikkan teksnya
            btnLewati.disabled = false; // Buka kunciannya agar bisa diklik
        }
        // 👆 ================================================================== 👆

        bar.className = 'fixed bottom-0 left-0 w-full bg-white border-t-4 border-slate-200 p-5 md:p-8 z-50 transition-all duration-300';
        feedback.classList.add('hidden');
        
        if (type === 'materi') {
        btn.innerHTML = 'Paham, Lanjut! ➔';
        btn.className = 'btn-3d w-full md:w-auto min-w-[200px] bg-blue-500 text-white font-black text-lg md:text-2xl py-3 md:py-5 px-5 md:px-8 rounded-xl md:rounded-2xl border-2 border-blue-400 border-b-[6px] md:border-b-[8px] border-b-blue-700 uppercase tracking-wider';
        btn.setAttribute('onclick', 'slideSelanjutnya()');
        } else {
            if (slidesData[currentIndex] && slidesData[currentIndex].is_answered) {
                bar.classList.add('bg-slate-100', 'border-slate-300');
                btn.innerHTML = '✅ Lanjut ➔';
                btn.className = 'btn-3d w-full md:w-auto min-w-[200px] bg-slate-500 text-white font-black text-lg md:text-2xl py-3 md:py-5 px-5 md:px-8 rounded-xl md:rounded-2xl border-2 border-slate-400 border-b-[6px] md:border-b-[8px] border-b-slate-700 uppercase tracking-wider';
                btn.setAttribute('onclick', 'slideSelanjutnya()');
                kunciForm(currentIndex);
            } else {
                btn.innerHTML = 'Cek Jawaban 🔍';
                btn.className = 'btn-3d w-full md:w-auto min-w-[200px] bg-green-500 text-white font-black text-lg md:text-2xl py-3 md:py-5 px-5 md:px-8 rounded-xl md:rounded-2xl border-2 border-green-400 border-b-[6px] md:border-b-[8px] border-b-green-700 uppercase tracking-wider';
                btn.setAttribute('onclick', 'cekJawaban()');
            }
        }
    }

        function cekJawaban() {
            if (isChecking) return;
            
            // 👇 1. KUNCI LANGSUNG AGAR TIDAK BISA DI-SPAM KLIK 👇
            isChecking = true; 
            
            const currentSlide = slidesData[currentIndex];
            const form = document.getElementById('instant-form');
            const formData = new FormData(form);
            
            let jawabanTarget = formData.get(`jawaban[${currentSlide.id}]`);
            
            // 👇 2. PENANGANAN KHUSUS ISIAN RUMPANG (ARRAY) 👇
            if (!jawabanTarget && formData.has(`jawaban[${currentSlide.id}][]`)) {
                let values = formData.getAll(`jawaban[${currentSlide.id}][]`);
                
                // Cek apakah ada satu saja kotak yang dibiarkan kosong stringnya
                let adaKosong = values.some(val => val.trim() === '');
                
                if (adaKosong) {
                    showToast("Isi semua titik-titik yang kosong dulu ya! 🎯");
                    isChecking = false; // Buka kunci lagi
                    return;
                }
                
                // Ubah jadi format Array JSON String agar diproses sempurna oleh Controller
                jawabanTarget = JSON.stringify(values);
            }

            // 👇 3. PENANGANAN BENAR/SALAH 👇
            if (!jawabanTarget && formData.has(`jawaban[${currentSlide.id}][pilihan]`)) {
                let pilihan = formData.get(`jawaban[${currentSlide.id}][pilihan]`);
                let perbaikan = formData.get(`jawaban[${currentSlide.id}][perbaikan]`) || '';
                
                if (pilihan === 'Salah' && perbaikan.trim() === '') {
                    showToast("Jangan lupa ketik perbaikannya ya! 🤓");
                    isChecking = false;
                    return;
                }
                if (pilihan) jawabanTarget = { pilihan: pilihan, perbaikan: perbaikan };
            }

            // 🚨 4. VALIDASI FINAL UMUM 🚨
            if (!jawabanTarget || jawabanTarget === '{}' || jawabanTarget === '') {
                showToast("Ayo, isi atau pilih jawabanmu dulu ya! 🤓");
                isChecking = false; 
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
                    const errData = await res.json().catch(() => ({ message: 'Server sibuk' }));
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
                isChecking = false; 
            });
        }

        function tampilkanHasil(data) {
        isChecking = true;
        let currentQId = slidesData[currentIndex].id;
        let currentFormat = slidesData[currentIndex].format; // Ambil tipe soal
        
        const bar = document.getElementById('bottom-bar');
        const feedback = document.getElementById('feedback-area');
        const btn = document.getElementById('btn-action');
        const btnLewati = document.getElementById('btn-lewati-kecil');
        const hintArea = document.getElementById(`hint-area-${currentIndex}`);
        const hintText = document.getElementById(`hint-text-${currentIndex}`);
        
        feedback.classList.remove('hidden');
        bar.classList.remove('bg-white', 'border-slate-200');
        btnLewati.classList.add('hidden'); // Sembunyikan lewati secara default

        if (data.is_correct) {
            slidesData[currentIndex].is_answered = true; 
            kunciForm(currentIndex);

            if (currentFormat === 'matching') ubahWarnaMatching(currentQId); // 👈 Panggil tanpa parameter
            bar.classList.add('bg-green-100', 'border-green-400');
            document.getElementById('feedback-icon').innerHTML = '⭐';
            document.getElementById('feedback-icon').className = 'w-14 h-14 rounded-full flex items-center justify-center font-black text-3xl bg-white border-4 border-green-200 text-green-500';
            document.getElementById('feedback-title').innerText = 'Hebat Banget!';
            document.getElementById('feedback-title').className = 'text-xl md:text-3xl font-black uppercase tracking-wider text-green-600';            document.getElementById('feedback-message').innerText = data.message;
            document.getElementById('feedback-message').className = 'font-bold text-green-700 opacity-90 text-sm md:text-lg ml-1';            
            
            
            btn.innerHTML = 'Lanjut ➔';
            btn.className = 'btn-3d w-full md:w-auto min-w-[200px] bg-green-500 text-white font-black text-lg md:text-2xl py-3 md:py-5 px-5 md:px-8 rounded-xl md:rounded-2xl border-2 border-green-400 border-b-[6px] md:border-b-[8px] border-b-green-700 uppercase tracking-wider';
            btn.setAttribute('onclick', 'slideSelanjutnya()');
        } else {
            // --- JAWABAN SALAH ---
            if (currentFormat === 'matching') ubahWarnaMatching(currentQId);
            bar.classList.add('bg-red-100', 'border-red-400');
            document.getElementById('feedback-icon').innerHTML = '❌';
            document.getElementById('feedback-icon').className = 'w-14 h-14 rounded-full flex items-center justify-center font-black text-3xl bg-white border-4 border-red-200 text-red-500';
            document.getElementById('feedback-title').innerText = 'Hampir Benar';
            document.getElementById('feedback-title').className = 'text-xl md:text-3xl font-black uppercase tracking-wider text-red-600';
        
            let msgElement = document.getElementById('feedback-message');
            msgElement.className = 'font-bold text-red-700 opacity-90 text-sm md:text-lg ml-1';

            if (!salahCountData[currentQId]) salahCountData[currentQId] = 0;
            salahCountData[currentQId]++;
            let mistakes = salahCountData[currentQId];

            if (isHintEnabled) {
                msgElement.innerText = 'Tetap semangat, perhatikan lagi ya!';
                btn.innerHTML = 'Coba Lagi 🔄';
                btn.className = 'btn-3d w-full md:w-auto min-w-[200px] bg-amber-500 text-white font-black text-lg md:text-2xl py-3 md:py-5 px-5 md:px-8 rounded-xl md:rounded-2xl border-2 border-amber-400 border-b-[6px] md:border-b-[8px] border-b-amber-700 uppercase tracking-wider';
                btn.setAttribute('onclick', 'resetBottomBar("soal")');

                if (mistakes >= 5) {
                    
                    // Munculkan tombol lewati kecil
                    btnLewati.classList.remove('hidden');
                    msgElement.innerText = 'Kamu sudah mencoba keras! Mau coba lagi atau lewati?';
                    let teksHint = generateSmartHint(data.correct_answer, currentFormat, mistakes);
                    showHintPopup(teksHint);
                } else if (mistakes >= 2) {
                    // 👇 MUNCULKAN POPUP MELAYANG 👇
                    let teksHint = generateSmartHint(data.correct_answer, currentFormat, mistakes);
                    showHintPopup(teksHint);
                }
            } else {
                // Mode Tanpa Hint
                msgElement.innerText = `Kunci: ${data.correct_answer || 'Tetap semangat!'}`;
                btn.innerHTML = 'Lanjut ➔';
                btn.className = 'btn-3d w-full md:w-auto min-w-[200px] bg-red-500 text-white font-black text-lg md:text-2xl py-3 md:py-5 px-5 md:px-8 rounded-xl md:rounded-2xl border-2 border-red-400 border-b-[6px] md:border-b-[8px] border-b-red-700 uppercase tracking-wider';
                slidesData[currentIndex].is_answered = true; 
                kunciForm(currentIndex);
                btn.setAttribute('onclick', 'slideSelanjutnya()');
            }
        }
        btn.disabled = false;
    }

        function slideSelanjutnya() {
            if (currentIndex < totalSlides - 1) {
                currentIndex++;
                showSlide(currentIndex);
            } else {
                akhiriLatihan();
            }
        }

        function lewatiSoal() {
        let currentSlide = slidesData[currentIndex];
        const btnLewati = document.getElementById('btn-lewati-kecil');
        btnLewati.innerText = 'Menyimpan...';
        btnLewati.disabled = true;
        
        // 👇 1. AMBIL JAWABAN TERAKHIR MURID DARI FORM 👇
        const form = document.getElementById('instant-form');
        const formData = new FormData(form);
        
        let jawabanTarget = formData.get(`jawaban[${currentSlide.id}]`);
        
        if (!jawabanTarget && formData.has(`jawaban[${currentSlide.id}][]`)) {
            jawabanTarget = formData.getAll(`jawaban[${currentSlide.id}][]`).join(' | ');
        }
        if (!jawabanTarget && formData.has(`jawaban[${currentSlide.id}][pilihan]`)) {
            let pilihan = formData.get(`jawaban[${currentSlide.id}][pilihan]`);
            let perbaikan = formData.get(`jawaban[${currentSlide.id}][perbaikan]`) || '';
            if (pilihan) jawabanTarget = { pilihan: pilihan, perbaikan: perbaikan };
        }
        
        // 👇 2. TEMBAK API DENGAN JAWABAN ASLI & BENDERA "NYERAH" 👇
        fetch("{{ route('student.module.cek-instan') }}", {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                question_id: currentSlide.id, 
                jawaban: jawabanTarget || 'Kosong', // 👈 Kirim jawaban yang diketik murid
                is_nyerah: true // 🚩 Bendera untuk Controller
            })
        })
        .then(() => {
            slidesData[currentIndex].is_answered = true; 
            kunciForm(currentIndex);
            if (typeof hideHintPopup === 'function') hideHintPopup();
            slideSelanjutnya();
        })
        .catch(() => {
            showToast('Gagal melewati soal, cek internetmu ya.');
            btnLewati.innerText = '⏭ Nyerah dan lewati soal ini';
            btnLewati.disabled = false;
        });
    }

        // 👇 TAMBAHKAN FUNGSI MUNDUR INI 👇
        function slideMundur() {
            if (currentIndex > 0) {
                currentIndex--;
                showSlide(currentIndex);
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

        // 👇 FUNGSI BARU: UBAH WARNA MATCHING PER GARIS 👇
        function ubahWarnaMatching(soalId) {
            let kunciPasangan = window['kunciPasangan_' + soalId];
            let hiddenInput = document.getElementById(`ans-${soalId}`);
            
            if (!kunciPasangan || !hiddenInput) return;
            
            let currentAns = JSON.parse(hiddenInput.value || "{}");
            let svg = document.getElementById(`svg-canvas-${soalId}`);
            
            // Looping (Pengecekan) untuk setiap garis yang ditarik murid
            for (let key in currentAns) {
                let val = currentAns[key];
                
                // 🧠 Cek apakah pasangan spesifik ini benar atau salah?
                let isBenar = kunciPasangan[key] === val; 
                
                let classBatas = isBenar ? 'border-green-500' : 'border-red-500';
                let classBg    = isBenar ? 'bg-green-50' : 'bg-red-50';
                let classTitik = isBenar ? 'bg-green-500' : 'bg-red-500';
                let warnaGaris = isBenar ? '#22c55e' : '#ef4444';
                
                let hapusBatas = isBenar ? 'border-red-500' : 'border-green-500';
                let hapusBg    = isBenar ? 'bg-red-50' : 'bg-green-50';
                let hapusTitik = isBenar ? 'bg-red-500' : 'bg-green-500';

                // 1. Sulap warna kotak KIRI
                let btnKiri = document.querySelector(`.btn-kiri-${soalId}[data-nilai="${key}"]`);
                if (btnKiri) {
                    btnKiri.classList.remove(hapusBatas, hapusBg);
                    btnKiri.classList.add(classBatas, classBg);
                    let konektor = btnKiri.querySelector('.konektor-kiri');
                    if (konektor) konektor.className = konektor.className.replace(hapusTitik, classTitik);
                }
                
                // 2. Sulap warna kotak KANAN
                let btnKanan = document.querySelector(`.btn-kanan-${soalId}[data-nilai="${val}"]`);
                if (btnKanan) {
                    btnKanan.classList.remove(hapusBatas, hapusBg);
                    btnKanan.classList.add(classBatas, classBg);
                    let konektor = btnKanan.querySelector('.konektor-kanan');
                    if (konektor) konektor.className = konektor.className.replace(hapusTitik, classTitik);
                }

                // 3. Sulap warna GARISNYA
                if (svg) {
                    let cleanId = key.replace(/[^a-zA-Z0-9]/g, '');
                    let lineId = `line-${soalId}-${cleanId}`;
                    let line = document.getElementById(lineId);
                    if (line) line.setAttribute('stroke', warnaGaris);
                }
            }
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

                // 👇 PERBAIKAN: Hapus juga class merah (red-500 dan red-50) 👇
                if (btnKiri) {
                    btnKiri.classList.remove('border-green-500', 'bg-green-50', 'border-red-500', 'bg-red-50', 'terjawab');
                    btnKiri.querySelector('.konektor-kiri').className = btnKiri.querySelector('.konektor-kiri').className.replace(/bg-(green-500|red-500)/g, 'bg-slate-200');
                }
                if (btnKanan) {
                    btnKanan.classList.remove('border-green-500', 'bg-green-50', 'border-red-500', 'bg-red-50', 'terjawab');
                    btnKanan.querySelector('.konektor-kanan').className = btnKanan.querySelector('.konektor-kanan').className.replace(/bg-(green-500|red-500)/g, 'bg-slate-200');
                }
            }
        }
    </script>
</body>
</html>