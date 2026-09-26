<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $module->title }} - Ruang Belajar</title>
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; background-color: #F0F9FF; }
        .bubbly-card { border-radius: 24px; box-shadow: 0 8px 25px -5px rgba(59, 130, 246, 0.12); }
        
        /* Animasi Slide ala Duolingo */
        .slide-in-right { animation: slideInRight 0.4s forwards cubic-bezier(0.25, 1, 0.5, 1); }
        .slide-out-left { animation: slideOutLeft 0.4s forwards cubic-bezier(0.25, 1, 0.5, 1); }
        .slide-in-left { animation: slideInLeft 0.4s forwards cubic-bezier(0.25, 1, 0.5, 1); }
        
        @keyframes slideInRight { from { transform: translateX(30%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes slideOutLeft { from { transform: translateX(0); opacity: 1; } to { transform: translateX(-30%); opacity: 0; } }
        @keyframes slideInLeft { from { transform: translateX(-30%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        /* Styling Kompresi agar lega di HP */
        .prose ul { list-style-type: disc !important; padding-left: 1.25rem !important; margin-bottom: 0.5rem !important; }
        .prose ol { list-style-type: decimal !important; padding-left: 1.25rem !important; margin-bottom: 0.5rem !important; }
        .prose li { margin-bottom: 0.25rem !important; }
        .prose p { margin-bottom: 0.75rem !important; line-height: 1.6 !important; }
        .prose table { width: 100%; border-collapse: collapse; margin-bottom: 0.75rem; border-radius: 0.75rem; overflow: hidden; }
        .prose th, .prose td { border: 3px solid #e2e8f0; padding: 0.5rem; text-align: left; font-size: 0.95rem; }
        .prose th { background-color: #f8fafc; font-weight: 800; }
        .prose img { border-radius: 0.75rem; margin: 0.75rem auto; max-width: 100%; height: auto; border: 3px solid #e2e8f0; }
        
        /* ==============================================================
           PERBAIKAN GAMBAR & CAPTION TRIX EDITOR (Bawaan Filament)
           ============================================================== */
        .prose figure.attachment a {
            pointer-events: none !important; /* Matikan efek klik */
            text-decoration: none !important; /* Hilangkan garis bawah */
            color: inherit !important; /* Matikan warna biru link */
            cursor: default !important; /* Kembalikan kursor ke normal */
        }
        .prose figure.attachment .attachment__name,
        .prose figure.attachment .attachment__size {
            display: none !important; /* Sembunyikan teks ukuran/nama file */
        }
        .prose figure.attachment figcaption {
            text-align: center !important;
            color: #64748b !important; /* Warna abu-abu elegan (slate-500) */
            font-size: 0.875rem !important; /* Ukuran teks lebih kecil */
            font-style: italic; /* Cetak miring */
            margin-top: 0.5rem;
            font-weight: 600;
        }
        /* ==============================================================
   MEMAKSA GAMBAR TRIX RESPONSIVE & BERGAYA NATIVE ANDROID
   ============================================================== */
/* 1. Memastikan pembungkus gambar tidak melebar melebihi layar */
.prose figure.attachment {
    width: 100% !important;
    max-width: 100% !important;
    margin: 1rem 0 !important;
    padding: 0 !important;
    display: block;
}

/* 2. Memaksa gambar selalu pas di layar HP dengan efek kartu */
.prose img { 
    width: 100% !important; 
    max-width: 100% !important; 
    height: auto !important; 
    object-fit: contain !important; /* Mencegah gambar gepeng */
    border-radius: 1rem !important; /* Sudut melengkung khas Android Modern */
    margin: 0 auto !important; 
    border: 2px solid #e2e8f0;
    box-shadow: 0 4px 10px -2px rgba(59, 130, 246, 0.1); /* Efek melayang tipis biru */
}
        
        #sidebar { transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">
<div id="toast-warning" class="fixed top-10 left-1/2 transform -translate-x-1/2 z-[100] transition-all duration-500 ease-in-out opacity-0 -translate-y-20 pointer-events-none">
        <div class="bg-red-500 border-4 border-white text-white px-6 py-4 rounded-[2rem] shadow-[0_8px_0_#b91c1c] flex items-center gap-4">
            <div class="text-4xl animate-bounce drop-shadow-md">⚠️</div>
            <div>
                <h4 class="font-black text-xl leading-tight">Waduh! Ada yang terlewat!</h4>
                <p id="toast-message" class="font-bold text-sm text-red-100">Soal Nomor X belum kamu jawab nih.</p>
            </div>
        </div>
    </div>
    @php
        // PELINDUNG 1: Cek apakah fungsi sudah ada sebelum dibuat
        if (!function_exists('renderPrivateImages')) {
            function renderPrivateImages($htmlContent) {
                if (!$htmlContent) return '';
                return preg_replace('/src=".*?modul_private\/(.*?)"/i', 'src="' . url('/private-image/modul_private/$1') . '"', $htmlContent);
            }
        }

        // PELINDUNG 2: Cek apakah fungsi tahap sudah ada sebelum dibuat
        if (!function_exists('getStageStyle')) {
            function getStageStyle($stage) {
                return match($stage) {
                    'berpikir'  => ['icon' => '🤔', 'text' => 'Pemantik', 'color' => 'bg-purple-400 text-purple-900 border-purple-200'],
                    'amati'     => ['icon' => '🔍', 'text' => 'Mengamati', 'color' => 'bg-blue-400 text-blue-900 border-blue-200'],
                    'mencoba'   => ['icon' => '🧪', 'text' => 'Mencoba', 'color' => 'bg-orange-400 text-orange-900 border-orange-200'],
                    'diskusi'   => ['icon' => '💬', 'text' => 'Diskusi', 'color' => 'bg-pink-400 text-pink-900 border-pink-200'],
                    'simpulkan' => ['icon' => '💡', 'text' => 'Menyimpulkan', 'color' => 'bg-emerald-400 text-emerald-900 border-emerald-200'],
                    'berlatih'  => ['icon' => '📝', 'text' => 'Berlatih', 'color' => 'bg-red-400 text-red-900 border-red-200'],
                    default     => ['icon' => '📖', 'text' => 'Materi', 'color' => 'bg-yellow-400 text-yellow-900 border-yellow-200'],
                };
            }
        }
    @endphp

    <button id="floating-burger" onclick="toggleSidebar()" class="fixed top-4 left-4 z-[60] bg-blue-600 text-white p-2.5 rounded-xl shadow-[0_4px_0_#1d4ed8] border-2 border-white hover:bg-blue-500 transition-all hidden transform hover:scale-105 active:translate-y-[4px] active:shadow-none">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 6h16M4 12h16M4 18h16"></path></svg>
    </button>

    <nav id="sidebar" class="bg-white border-r-[3px] border-slate-200 h-full w-72 flex-shrink-0 flex flex-col z-50 absolute md:relative transform translate-x-0">
        <div class="p-4 md:p-5 bg-blue-500 text-white border-b-[3px] border-blue-700 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="bg-blue-600 rounded-full px-3 py-1 font-black text-xs shadow-inner tracking-wide uppercase">🔥 Modul</div>
                <div class="flex gap-2">
                    <button onclick="toggleSidebar()" class="text-blue-100 hover:text-white p-1.5 bg-blue-600 rounded-lg border-2 border-blue-400 transition-colors shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                    </button>
                    <button onclick="confirmExit()" class="text-white hover:text-red-200 font-black flex items-center justify-center bg-red-500 hover:bg-red-600 w-8 h-8 rounded-lg border-2 border-red-400 shadow-sm transition-colors">✖</button>
                </div>
            </div>
            <h1 class="text-xl font-black leading-tight">{{ $module->title }}</h1>
        </div>

        <div class="flex-1 overflow-y-auto no-scrollbar p-3 flex flex-col gap-3">
            @foreach($module->activities as $index => $activity)
                <button id="nav-btn-{{ $index }}" onclick="goToActivity({{ $index }})" 
                    class="nav-btn w-full text-left p-3 rounded-xl border-[3px] transition-all flex flex-col gap-1.5 opacity-50 cursor-not-allowed bg-slate-50 border-slate-200 text-slate-400">
                    <div class="flex items-center justify-between w-full">
                        <span class="text-[10px] font-black uppercase tracking-wider bg-white/60 px-2 py-0.5 rounded-md">Aktivitas {{ $index + 1 }}</span>
                        <span id="nav-icon-{{ $index }}" class="text-xl drop-shadow-sm">🔒</span>
                    </div>
                    <span class="font-extrabold text-sm leading-snug w-full block break-words">{{ $activity->title }}</span>
                </button>
            @endforeach
        </div>
        @if($isCompleted)
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 rounded-xl font-bold mb-6 flex items-center gap-3 shadow-sm">
                <span class="text-2xl">👀</span>
                <p>Kamu sedang dalam <strong>Mode Ulasan</strong>. Modul ini sudah diselesaikan dan jawaban tidak bisa diubah.</p>
            </div>
        @endif
    </nav>

    <main id="main-content" class="flex-1 h-full overflow-y-auto p-3 md:p-6 relative scroll-smooth transition-all duration-300 w-full">
        <div class="max-w-4xl mx-auto">
            
            @foreach($module->activities as $aIndex => $activity)
                <div id="activity-wrapper-{{ $aIndex }}" class="activity-section pb-20" style="display: none;">
                    
                    @php
                        $stages = is_string($activity->stages) ? json_decode($activity->stages, true) : ($activity->stages ?? []);
                        $totalStages = count($stages);
                    @endphp

                    @if($totalStages > 0)
                        @foreach($stages as $sIndex => $stage)
                            @php $stageStyle = getStageStyle($stage['tipe_tahapan'] ?? 'materi'); @endphp
                            
                            <div id="stage-phase-{{ $aIndex }}-{{ $sIndex }}" class="stage-slide bubbly-card bg-white p-5 md:p-8 border-[3px] border-blue-100 relative" style="display: {{ $sIndex == 0 ? 'block' : 'none' }};">
                                
                                @if($sIndex > 0)
                                    <button type="button" onclick="slideMundur({{ $aIndex }}, {{ $sIndex }})" class="absolute top-5 left-5 text-slate-400 hover:text-slate-600 font-bold text-sm flex items-center gap-1 transition-colors">
                                        <span>⬅️</span> Mundur
                                    </button>
                                @endif

                                <div class="text-center mb-5 mt-6 md:mt-0">
                                    <span class="{{ $stageStyle['color'] }} px-5 py-1.5 rounded-full font-black text-base md:text-lg shadow-sm border-[3px] border-white inline-block">
                                        {{ $stageStyle['icon'] }} {{ $stageStyle['text'] }}
                                    </span>
                                </div>
                                <h2 class="text-xl md:text-3xl font-black text-slate-800 text-center mb-6">{{ $activity->title }}</h2>

                                <div class="bg-slate-50 rounded-2xl p-4 md:p-6 border-[3px] border-slate-200 mb-6">
                                    <!-- ========================================== -->
                                <!-- TOMBOL BANTUAN MATERI (SUARA & ISYARAT) -->
                                <!-- ========================================== -->
                                @php 
                                    $videoMateri = $stage['sign_language_video'] ?? null; 
                                    $audioGuru = $stage['voice_note'] ?? null;
                                @endphp
                                
                                <div class="flex flex-wrap items-center justify-center gap-3 mb-6">
                                    <!-- Tombol Suara -->
                                    <button type="button" onclick="bacakanTeks(`{{ strip_tags($stage['konten_tahapan'] ?? '') }}`, this)" class="btn-3d bg-blue-100 text-blue-700 font-black py-3 px-6 rounded-2xl flex items-center gap-2 border-2 border-blue-300 border-b-[6px] shadow-sm">
                                            📢 Bacakan Soal
                                        </button>

                                    <!-- Tombol Video Isyarat -->
                                    @if(!empty($videoMateri))
                                        <button type="button" onclick="toggleVideoMateri({{ $aIndex }}, {{ $sIndex }})" class="bg-purple-100 hover:bg-purple-200 text-purple-800 font-bold py-2 px-4 rounded-full flex items-center gap-2 transition-all border-2 border-purple-300 shadow-sm active:translate-y-1">
                                            <span class="text-xl">🤟</span> Lihat Isyarat
                                        </button>
                                    @endif

                                    <!-- Tombol Suara Guru -->
                                    @if(!empty($audioGuru))
                                        <button type="button" onclick="putarVoiceNote('{{ route('private.audio', ['path' => $audioGuru]) }}', this)" class="bg-emerald-100 hover:bg-emerald-200 text-emerald-700 font-bold py-2 px-4 rounded-full flex items-center gap-2 transition-all border-2 border-emerald-300 shadow-sm active:translate-y-1">
                                            <span class="text-xl">🔊</span> Bacakan Pesan Suara
                                        </button>
                                    @endif
                                </div>

                                <!-- CONTAINER VIDEO MATERI (Tersembunyi) -->
                                @if(!empty($videoMateri))
                                    <div id="video-materi-{{ $aIndex }}-{{ $sIndex }}" class="hidden mb-6 mx-auto relative rounded-2xl overflow-hidden border-4 border-purple-300 shadow-md bg-slate-900 transition-all duration-300 w-full max-w-lg">
                                        <div class="bg-purple-100 px-4 py-2 flex justify-between items-center border-b-2 border-purple-300">
                                            <span class="font-black text-purple-800 text-sm flex items-center gap-2">🤟 Bantuan Isyarat Materi</span>
                                            <button type="button" onclick="toggleVideoMateri({{ $aIndex }}, {{ $sIndex }})" class="text-red-500 hover:text-red-700 font-black text-xl hover:scale-110 transition-transform">✖</button>
                                        </div>
                                        <video id="player-materi-{{ $aIndex }}-{{ $sIndex }}" controls class="w-full aspect-video bg-black">
                                            <source src="{{ route('private.video', ['path' => $videoMateri]) }}" type="video/mp4">
                                            Browsermu tidak mendukung pemutar video.
                                        </video>
                                    </div>
                                @endif

                                <!-- KOTAK ISI MATERI -->
                                <div class="bg-slate-50 rounded-2xl p-4 md:p-6 border-[3px] border-slate-200 mb-6">
                                    <div class="prose prose-blue text-slate-700 mx-auto font-bold leading-relaxed w-full max-w-full">
                                        {!! renderPrivateImages($stage['konten_tahapan'] ?? '') !!}
                                    </div>
                                </div>
                                </div>

                                <button onclick="slideLanjut({{ $aIndex }}, {{ $sIndex }}, {{ $totalStages }})" 
                                    class="w-full bg-blue-500 hover:bg-blue-400 text-white font-black text-xl py-3.5 rounded-2xl shadow-[0_5px_0_#1d4ed8] active:shadow-none active:translate-y-[5px] transition-all border-[3px] border-white">
                                    {{ $sIndex == $totalStages - 1 ? 'Mulai Berlatih! 🚀' : 'Lanjut ➔' }}
                                </button>
                            </div>
                        @endforeach
                    @else
                        <div id="stage-phase-{{ $aIndex }}-0" class="stage-slide bubbly-card bg-white p-5 md:p-8 border-[3px] border-blue-100" style="display: block;">
                            <div class="text-center mb-5"><span class="bg-yellow-400 text-yellow-900 px-5 py-1.5 rounded-full font-black shadow-sm border-[3px] border-white inline-block">📖 Materi</span></div>
                            <h2 class="text-xl font-black text-center mb-6">{{ $activity->title }}</h2>
                            <div class="prose text-slate-700 mb-6">{!! renderPrivateImages($activity->description ?? '') !!}</div>
                            <button onclick="slideLanjut({{ $aIndex }}, 0, 1)" class="w-full bg-blue-500 text-white font-black text-xl py-3.5 rounded-2xl shadow-[0_5px_0_#1d4ed8]">Mulai Berlatih! 🚀</button>
                        </div>
                    @endif

                    <div id="practice-phase-{{ $aIndex }}" style="display: none;" class="bubbly-card bg-white p-5 md:p-8 border-[3px] border-green-100 relative">
                        
                        <button type="button" onclick="kembaliKeMateri({{ $aIndex }}, {{ $totalStages }})" class="mb-6 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-sm py-2 px-4 rounded-full shadow-[0_3px_0_#cbd5e1] active:translate-y-[3px] transition-all border-2 border-slate-300 flex items-center gap-2">
                            <span>⬅️</span> Lihat Materi Lagi
                        </button>

                        @if($isCompleted)
                        @php
                            $submission = \App\Models\ActivitySubmission::where('student_id', auth()->id()) 
                                ->where('activity_id', $activity->id)
                                ->first();
                                
                            $skorAkhir = $submission ? ($submission->total_score ?? 0) : 0;
                            
                            // 👈 CEK STATUS PENILAIAN DARI DATABASE DI SINI
                            $isDinilai = $submission && $submission->status === 'dinilai';
                        @endphp

                        @if($isDinilai)
                            <!-- JIKA SUDAH DINILAI GURU: MUNCUL BANNER BIRU -->
                            <div class="mb-8 bg-gradient-to-r from-blue-500 to-cyan-400 p-6 rounded-[2rem] border-4 border-white shadow-[0_8px_0_#1d4ed8] text-white flex flex-col md:flex-row items-center justify-between gap-6 relative overflow-hidden">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-bl-full"></div>
                                <div class="flex items-center gap-4 relative z-10">
                                    <div class="text-6xl drop-shadow-md">
                                        {{ $skorAkhir >= 80 ? '🏆' : ($skorAkhir >= 60 ? '👍' : '💪') }}
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-black tracking-wide uppercase">Hasil Penilaian</h3>
                                        <p class="font-bold text-blue-100 mt-1">Pak/Bu Guru sudah memeriksa tugasmu!</p>
                                    </div>
                                </div>
                                <div class="bg-white text-blue-600 px-6 py-4 rounded-2xl border-4 border-blue-200 shadow-inner relative z-10 text-center min-w-[120px]">
                                    <span class="block text-xs font-black text-blue-400 uppercase tracking-widest mb-1">Skor Akhir</span>
                                    <span class="text-4xl font-black">{{ $skorAkhir }}</span>
                                </div>
                            </div>
                        @else
                            <!-- JIKA BELUM DINILAI: MUNCUL BANNER TUNGGU KUNING -->
                            <div class="mb-8 bg-amber-50 border-[3px] border-amber-300 p-6 rounded-[2rem] text-amber-800 flex items-center gap-4 shadow-sm relative overflow-hidden">
                                <div class="text-5xl animate-bounce drop-shadow-sm relative z-10">⏳</div>
                                <div class="relative z-10">
                                    <h3 class="text-2xl font-black uppercase tracking-wide">Tugas Terkirim!</h3>
                                    <p class="font-bold text-amber-700 mt-1">Pak/Bu Guru sedang mengoreksi tugasmu. Sabar ya!</p>
                                </div>
                                <div class="absolute -right-4 -top-4 text-8xl opacity-10 pointer-events-none">📝</div>
                            </div>
                        @endif
                    @endif
                        <!-- 👆 ==================================================== 👆 -->

                        <div class="flex items-center gap-3 mb-6 border-b-[3px] border-slate-100 pb-4">
                            <div class="bg-green-100 text-green-600 p-2.5 rounded-xl border-[3px] border-green-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <h2 class="text-xl md:text-2xl font-black text-slate-800">Saatnya Berlatih!</h2>
                        </div>

                        <form id="form-activity-{{ $aIndex }}">
                            <div class="space-y-8">
                                @foreach($activity->questions as $qIndex => $question)

                                    @include('filament.components.soal-murid', [
                                        'question' => $question,
                                        'qIndex' => $qIndex
                                    ])

                                    <!-- 👇 CATATAN GURU PER SOAL 👇 -->
                                    <!-- Bagian Looping Soal Anda... -->

                                    <!-- Cek juga apakah $isDinilai sudah diset true oleh sistem di atas -->
                                    @if($isCompleted && isset($isDinilai) && $isDinilai)
                                        @php
                                            $dbAnswer = \App\Models\StudentAnswer::where('student_id', auth()->id()) // Sesuaikan dengan cara Anda get ID murid
                                                ->where('question_id', $question->id)
                                                ->first();
                                                
                                            $scoreSoal = $dbAnswer ? ($dbAnswer->score ?? 0) : 0;
                                            $notesGuru = $dbAnswer ? ($dbAnswer->teacher_notes ?? '') : '';
                                        @endphp
                                        
                                        <div class="mt-4 bg-slate-50 border-[3px] border-slate-200 rounded-2xl p-4 md:p-5 flex flex-col md:flex-row gap-4 relative">
                                            <!-- Indikator Skor Per Soal -->
                                            <div class="flex-shrink-0 flex items-center justify-center bg-white border-[3px] {{ $scoreSoal > 0 ? 'border-green-400 text-green-500' : 'border-red-400 text-red-500' }} w-16 h-16 rounded-2xl shadow-sm">
                                                <span class="font-black text-xl">{{ $scoreSoal }}</span>
                                            </div>
                                            
                                            <!-- Kotak Pesan Guru -->
                                            <!-- Kotak Pesan Guru (Di file wrapper utama) -->
                                        <div class="flex-1">
                                            <span class="inline-block bg-slate-200 text-slate-600 font-black px-3 py-1 rounded-full text-[10px] uppercase tracking-widest mb-2">
                                                💬 Pesan Guru
                                            </span>
                                            <p class="text-slate-700 font-bold text-sm md:text-base leading-relaxed">
                                                @if(!empty($notesGuru))
                                                    {{ $notesGuru }}
                                                @else
                                                    <i class="text-slate-400 font-medium">Tidak ada catatan khusus untuk soal ini.</i>
                                                @endif
                                            </p>

                                            <!-- 👇 TAMBAHKAN BLOK KUNCI JAWABAN DI SINI 👇 -->
                                            @if($scoreSoal == 0 && !empty($question->correct_answer))
                                                <div class="mt-4 p-3 bg-amber-50 border-l-[4px] border-amber-400 rounded-r-xl inline-block w-full">
                                                    <span class="block text-[10px] font-black text-amber-600 uppercase tracking-widest mb-1">💡 Kunci Jawaban yang Benar:</span>
                                                    <span class="font-bold text-amber-900">{{ trim(str_replace(['"', '\\'], '', $question->correct_answer)) }}</span>
                                                </div>
                                            @endif
                                            <!-- 👆 ===================================== 👆 -->
                                        </div>
                                            
                                            <!-- Watermark -->
                                            <div class="absolute right-4 top-1/2 transform -translate-y-1/2 text-5xl opacity-10 pointer-events-none">
                                                {{ $scoreSoal > 0 ? '✅' : '❌' }}
                                            </div>
                                        </div>
                                    @endif
                                    <!-- 👆 ================================= 👆 -->

                                @endforeach
                            </div>

                            <div class="mt-8 pt-6 border-t-[3px] border-dashed border-slate-200">
                                @if($isCompleted)
                                    <a href="{{ route('student.dashboard') }}" class="block w-full text-center bg-slate-300 hover:bg-slate-400 text-slate-600 font-black py-4 md:py-5 rounded-2xl shadow-[0_6px_0_#94a3b8] transition-all active:translate-y-[6px] active:shadow-none text-xl border-4 border-white">
                                        ⬅️ Kembali ke Peta Perjalanan
                                    </a>
                                @else
                                    @if($aIndex < count($module->activities) - 1)
                                        <!-- 👇 TAMBAHKAN type="button" DI SINI 👇 -->
                                        <button type="button" onclick="simpanDanLanjut({{ $index }}, '{{ acak_id($module->id) }}')" class="w-full md:w-auto md:float-right bg-green-500 hover:bg-green-400 text-white font-black text-xl py-3.5 px-8 rounded-full shadow-[0_5px_0_#16a34a] active:shadow-none active:translate-y-[5px] transition-all border-[3px] border-white">
                                            Simpan Jawaban ➔
                                        </button>
                                    @else
                                        <!-- 👇 TAMBAHKAN type="button" DI SINI 👇 -->
                                        <button type="button" onclick="simpanDanSelesai({{ $index }}, '{{ acak_id($module->id) }}')" class="w-full bg-orange-500 hover:bg-orange-400 text-white font-black text-xl py-3.5 rounded-full shadow-[0_5px_0_#ea580c] active:shadow-none active:translate-y-[5px] transition-all border-[3px] border-white bubbly-button">
                                            ✨ Kumpulkan Tugas! ✨
                                        </button>
                                    @endif
                                @endif
                                <div class="clear-both"></div>
                            </div>
                        </form>
                    </div>

                </div>
            @endforeach

        </div>
    </main>

    <div id="celebrationModal" class="fixed inset-0 bg-blue-900 bg-opacity-80 z-[100] hidden flex items-center justify-center backdrop-blur-sm transition-opacity">
        <div class="bg-white p-8 md:p-12 rounded-[3rem] max-w-md w-full text-center shadow-2xl transform scale-75 transition-transform duration-500 ease-out border-8 border-yellow-400 relative" id="celebrationContent">
            <div class="absolute -top-6 -left-6 text-4xl animate-spin-slow">✨</div>
            <div class="absolute -bottom-6 -right-6 text-4xl animate-bounce">🌟</div>
            <div class="text-8xl md:text-9xl mb-6 animate-bounce origin-bottom drop-shadow-xl">🏆</div>
            <h2 class="text-4xl font-black text-green-500 mb-3">Luar Biasa!</h2>
            <p class="text-xl font-bold text-slate-600 mb-8 leading-relaxed">Kamu berhasil menyelesaikan level ini dengan hebat! Pak/Bu Guru akan segera memeriksa tugasmu.</p>
            <button onclick="window.location.href='{{ route('student.dashboard') }}?status=hore'" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-black py-4 rounded-2xl shadow-[0_6px_0_#2563eb] transition-all active:translate-y-[6px] active:shadow-none text-xl border-4 border-white">
                Lanjut Berpetualang ➔
            </button>
        </div>
    </div>

    <div id="mobile-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/40 z-40 hidden md:hidden backdrop-blur-sm transition-all"></div>


    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        const moduleId = {{ $module->id }};
        const totalActivities = {{ count($module->activities) }};
        const storageKey = `resume_module_${moduleId}_student_{{ session('student_id') }}`;
        
        // =======================================================
        // 🧠 KECERDASAN BARU: CEK DATABASE UNTUK MEMBUKA GEMBOK
        // =======================================================
        @php
            $dbUnlockedIndex = 0;
            // Looping untuk mencari tahu aktivitas mana yang sudah ada jawabannya
            foreach($module->activities as $idx => $act) {
                $terjawab = false;
                foreach($act->questions as $q) {
                    if(isset($existingAnswers[$q->id])) {
                        $terjawab = true; break;
                    }
                }
                // Jika aktivitas ini sudah dikerjakan, BUKA aktivitas berikutnya
                if($terjawab) {
                    $dbUnlockedIndex = $idx + 1; 
                }
            }
            // Batasi agar tidak melebih total aktivitas
            if($dbUnlockedIndex >= count($module->activities)) {
                $dbUnlockedIndex = count($module->activities) - 1;
            }
        @endphp

        // Setel gembok berdasarkan rekaman database yang valid
        let highestUnlockedIndex = {{ $dbUnlockedIndex }};
        let currentIndex = highestUnlockedIndex; // Otomatis diarahkan ke level terakhir yg terbuka
        let isSubmitting = false;

        document.addEventListener("DOMContentLoaded", function() {
            
            // 🧹 PEMBASMI HANTU LOCAL STORAGE: 
            // Jika database bilang murid ini di titik 0 (baru mulai atau di-reset admin),
            // paksa hapus ingatan browser agar centang hijau palsu hilang!
            if (highestUnlockedIndex === 0) {
                localStorage.removeItem(storageKey);
            } else {
                // Biarkan sisa logika fallback berjalan normal untuk jaga-jaga koneksi putus
                let savedUnlocked = localStorage.getItem(storageKey);
                if (savedUnlocked !== null && parseInt(savedUnlocked) > highestUnlockedIndex) {
                    highestUnlockedIndex = parseInt(savedUnlocked);
                    if(highestUnlockedIndex >= totalActivities) highestUnlockedIndex = totalActivities - 1;
                    currentIndex = highestUnlockedIndex;
                }
            }
            
            updateNavigationUI();
            showActivity(currentIndex);
            if(window.innerWidth < 768) toggleSidebar();
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

        // ==========================================
        // 🤟 PENGENDALI VIDEO ISYARAT (TOGGLE)
        // ==========================================
        function toggleVideoSoal(soalId) {
            const container = document.getElementById(`video-isyarat-${soalId}`);
            const player = document.getElementById(`player-${soalId}`);
            
            if (container.classList.contains('hidden')) {
                // BUKA: Munculkan kotaknya
                container.classList.remove('hidden');
                
                // (Opsional) Otomatis putar video saat dibuka
                // player.play(); 
            } else {
                // TUTUP: Sembunyikan dan PAUSE videonya agar suara tidak bocor!
                container.classList.add('hidden');
                player.pause(); 
            }
        }

        // ==========================================
        // 🤟 PENGENDALI VIDEO ISYARAT (MATERI/TAHAPAN)
        // ==========================================
        function toggleVideoMateri(aIndex, sIndex) {
            const container = document.getElementById(`video-materi-${aIndex}-${sIndex}`);
            const player = document.getElementById(`player-materi-${aIndex}-${sIndex}`);
            
            if (container.classList.contains('hidden')) {
                // Munculkan videonya
                container.classList.remove('hidden');
            } else {
                // Sembunyikan & Matikan putarannya
                container.classList.add('hidden');
                player.pause(); 
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const floatingBtn = document.getElementById('floating-burger');
            const overlay = document.getElementById('mobile-overlay');
            sidebar.classList.toggle('-translate-x-full');
            sidebar.classList.toggle('md:-ml-72'); 
            if (sidebar.classList.contains('-translate-x-full')) {
                floatingBtn.classList.remove('hidden');
                overlay.classList.add('hidden'); 
            } else {
                floatingBtn.classList.add('hidden');
                overlay.classList.remove('hidden'); 
            }
        }

        function updateNavigationUI() {
            for(let i = 0; i < totalActivities; i++) {
                const btn = document.getElementById(`nav-btn-${i}`);
                const icon = document.getElementById(`nav-icon-${i}`);
                btn.classList.remove('bg-blue-100', 'border-blue-500', 'text-blue-800', 'shadow-sm', 'scale-[1.02]');
                btn.classList.add('bg-slate-50', 'border-slate-200', 'text-slate-400');
                // Cek status akhir modul dari server (Blade)
                let isModuleSelesai = {{ $isCompleted ? 'true' : 'false' }};

                if (i <= highestUnlockedIndex) {
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    btn.classList.add('cursor-pointer', 'hover:bg-slate-100');
                    
                    // Jika modul keseluruhan sudah disubmit, atau bukan aktivitas terakhir: beri centang hijau!
                    if (isModuleSelesai || i < highestUnlockedIndex) {
                        icon.innerHTML = '✅'; 
                    } else {
                        // Jika modul masih dikerjakan dan ini aktivitas terakhir: beri bintang kuning!
                        icon.innerHTML = '⭐'; 
                    }
                } else {
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                    btn.classList.remove('cursor-pointer');
                    icon.innerHTML = '🔒';
                }
                if (i === currentIndex) {
                    btn.classList.remove('bg-slate-50', 'border-slate-200', 'text-slate-400');
                    btn.classList.add('bg-blue-100', 'border-blue-500', 'text-blue-800', 'shadow-sm', 'scale-[1.02]');
                }
            }
        }

        function showActivity(index) {
            document.querySelectorAll('.activity-section').forEach(el => el.style.display = 'none');
            const target = document.getElementById(`activity-wrapper-${index}`);
            if(target) {
                target.style.display = 'block';
                target.classList.remove('slide-out-left');
                target.classList.add('slide-in-right');
                document.querySelectorAll(`#activity-wrapper-${index} .stage-slide`).forEach(el => el.style.display = 'none');
                const firstStage = document.getElementById(`stage-phase-${index}-0`);
                if(firstStage) firstStage.style.display = 'block';
                document.getElementById(`practice-phase-${index}`).style.display = 'none';
            }
            currentIndex = index;
            updateNavigationUI();
            document.getElementById('main-content').scrollTo({ top: 0, behavior: 'smooth' });
        }

        function slideLanjut(aIndex, currentStageIndex, totalStages) {
            const currentStage = document.getElementById(`stage-phase-${aIndex}-${currentStageIndex}`);
            currentStage.style.display = 'none';
            if (currentStageIndex + 1 < totalStages) {
                const nextStage = document.getElementById(`stage-phase-${aIndex}-${currentStageIndex + 1}`);
                nextStage.style.display = 'block';
                nextStage.classList.remove('slide-in-left');
                nextStage.classList.add('slide-in-right');
            } else {
                const practice = document.getElementById(`practice-phase-${aIndex}`);
                practice.style.display = 'block';
                practice.classList.remove('slide-in-left');
                practice.classList.add('slide-in-right');
            }
            document.getElementById('main-content').scrollTo({ top: 0, behavior: 'smooth' });
        }

        function slideMundur(aIndex, currentStageIndex) {
            const currentStage = document.getElementById(`stage-phase-${aIndex}-${currentStageIndex}`);
            const prevStage = document.getElementById(`stage-phase-${aIndex}-${currentStageIndex - 1}`);
            currentStage.style.display = 'none';
            prevStage.style.display = 'block';
            prevStage.classList.remove('slide-in-right');
            prevStage.classList.add('slide-in-left');
            document.getElementById('main-content').scrollTo({ top: 0, behavior: 'smooth' });
        }

        function kembaliKeMateri(aIndex, totalStages) {
            const practice = document.getElementById(`practice-phase-${aIndex}`);
            const lastStage = document.getElementById(`stage-phase-${aIndex}-${totalStages - 1}`);
            practice.style.display = 'none';
            lastStage.style.display = 'block';
            lastStage.classList.remove('slide-in-right');
            lastStage.classList.add('slide-in-left');
            document.getElementById('main-content').scrollTo({ top: 0, behavior: 'smooth' });
        }

        function goToActivity(index) {
            if(index > highestUnlockedIndex) return; 
            showActivity(index);
            if(window.innerWidth < 768) {
                const sidebar = document.getElementById('sidebar');
                if(!sidebar.classList.contains('-translate-x-full')) toggleSidebar();
            }
        }

        // =========================================================
        // MESIN PENARIK GARIS SVG (BISA BOLAK BALIK: KIRI/KANAN BEBAS)
        // =========================================================
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

        // ==========================================
        // AJAX PENGAMAN DATA SEBELUM DISIMPAN
        // ==========================================
        function prepareSafeFormData(formElement) {
            let rawData = new FormData(formElement);
            let safeData = new FormData();
            let arrayValues = {};

            // Menggabungkan isian rumpang ganda (Array) menjadi 1 String agar tidak Crash di Controller
            for(let [key, value] of rawData.entries()) {
                if(key.endsWith('[]')) {
                    let cleanKey = key.slice(0, -2);
                    if(!arrayValues[cleanKey]) arrayValues[cleanKey] = [];
                    arrayValues[cleanKey].push(value);
                } else {
                    safeData.append(key, value);
                }
            }
            for(let key in arrayValues) {
                safeData.append(key, arrayValues[key].join(' | '));
            }
            return safeData;
        }

        function ambilToken() { return document.querySelector('meta[name="csrf-token"]').content; }

        // Fungsi pemanggil Toast Notifikasi
        function showWarningToast(pesan) {
            const toast = document.getElementById('toast-warning');
            const toastMsg = document.getElementById('toast-message');
            
            toastMsg.innerText = pesan;
            
            // Munculkan perlahan dari atas
            toast.classList.remove('opacity-0', '-translate-y-20');
            toast.classList.add('opacity-100', 'translate-y-0');

            // Sembunyikan otomatis setelah 4 detik
            setTimeout(() => {
                toast.classList.remove('opacity-100', 'translate-y-0');
                toast.classList.add('opacity-0', '-translate-y-20');
            }, 4000);
        }

        // ==========================================
        // 🕵️‍♂️ DETEKTIF VALIDASI PINTAR (ANTI SOAL KOSONG)
        // ==========================================
        function validasiForm(index) {
            const practicePhase = document.getElementById(`practice-phase-${index}`);
            if (!practicePhase) return true;

            // Tarik semua kotak soal di dalam aktivitas ini
            const questions = practicePhase.querySelectorAll('.space-y-8 > div');
            let adaYangKosong = false;
            let nomorSoalKosong = null;
            let elementKosong = null;

            questions.forEach((qEl, idx) => {
                if (adaYangKosong) return; // Jika sudah ketemu 1 yang belum diisi, stop loop

                const radioInputs = qEl.querySelectorAll('input[type="radio"]');
                const numberInput = qEl.querySelector('input[type="number"]');
                const textInput = qEl.querySelector('textarea');
                const fillInputs = qEl.querySelectorAll('input[name*="[]"]');
                const hiddenAns = qEl.querySelector('input[id^="ans-"]');

                // 👈 1. TAMBAHKAN DETEKTOR KOTAK KIRI DI SINI
                const konektorKiri = qEl.querySelectorAll('.konektor-kiri');

                let terjawab = false;

                // A. Tipe Pilihan Ganda & Benar/Salah
                if (radioInputs.length > 0) {
                    terjawab = Array.from(radioInputs).some(r => r.checked);
                    const salahChecked = qEl.querySelector('input[value="Salah"]:checked');
                    if (salahChecked) {
                        const inputPerbaikan = qEl.querySelector('input[name*="[perbaikan]"]');
                        if (inputPerbaikan && !inputPerbaikan.value.trim()) {
                            terjawab = false;
                        }
                    }
                } 
                // 👈 2. UBAH LOGIKA TIPE MENJODOHKAN (MATCHING) MENJADI SEPERTI INI:
                else if (konektorKiri.length > 0) {
                    // Jika murid sudah menarik minimal 1 garis (hiddenAns tercipta)
                    if (hiddenAns) {
                        try {
                            const val = JSON.parse(hiddenAns.value || '{}');
                            // Syarat terjawab: Jumlah garis yang ditarik HARUS SAMA dengan jumlah kotak di kiri
                            terjawab = Object.keys(val).length > 0 && Object.keys(val).length === konektorKiri.length;
                        } catch(e) {
                            terjawab = false;
                        }
                    } else {
                        // Jika hiddenAns belum ada sama sekali, berarti murid melewatinya!
                        terjawab = false; 
                    }
                }
                // C. Tipe Isian Rumpang (Complex Fill)
                else if (fillInputs.length > 0) {
                    terjawab = Array.from(fillInputs).every(inp => inp.value.trim() !== '');
                }
                // D. Tipe Input Angka
                else if (numberInput) {
                    terjawab = numberInput.value.trim() !== '';
                }
                // E. Tipe Input Teks
                else if (textInput) {
                    terjawab = textInput.value.trim() !== '';
                } else {
                    terjawab = true;
                }

                if (!terjawab) {
                    adaYangKosong = true;
                    nomorSoalKosong = idx + 1;
                    elementKosong = qEl;
                }
            });

            if (adaYangKosong) {
                // Meluncurkan layar ke soal yang belum dijawab & beri efek kilau merah
                elementKosong.scrollIntoView({ behavior: 'smooth', block: 'center' });
                elementKosong.classList.add('ring-4', 'ring-red-400', 'transition-all');
                setTimeout(() => elementKosong.classList.remove('ring-4', 'ring-red-400'), 3000);

                showWarningToast(`Lengkapi dulu Soal Nomor ${nomorSoalKosong} sebelum lanjut ya! 🎯`);
                return false;
            }

            return true;
        }

        function simpanDanLanjut(index, moduleId) {
            const form = document.getElementById(`form-activity-${index}`);
            if (!validasiForm(index)) return;

            const btn = form.querySelector('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = "Menyimpan... ⏳"; btn.disabled = true;

            // 👇 BUNGKUS DATA DAN TAMBAHKAN KUNCI FINAL 👇
            let dataKirim = prepareSafeFormData(form);
            dataKirim.append('is_final_submit', '1'); 

            fetch(`/ruang-belajar/modul/${moduleId}/simpan-aktivitas`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': ambilToken(), 'Accept': 'application/json' },
                body: dataKirim // <-- Gunakan dataKirim di sini
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    let nextIndex = index + 1;
                    if(nextIndex > highestUnlockedIndex) {
                        highestUnlockedIndex = nextIndex;
                        localStorage.setItem(storageKey, highestUnlockedIndex);
                    }
                    showActivity(nextIndex);
                }
            })
            .finally(() => { btn.innerHTML = originalText; btn.disabled = false; });
        }

        function simpanDanSelesai(index, moduleId) {
            const form = document.getElementById(`form-activity-${index}`);
            if (!validasiForm(index)) return;

            isSubmitting = true;
            const btn = form.querySelector('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = "Mengirim... 🚀"; btn.disabled = true;

            // 👇 BUNGKUS DATA DAN TAMBAHKAN KUNCI FINAL 👇
            let dataKirim = prepareSafeFormData(form);
            dataKirim.append('is_final_submit', '1'); 

            fetch(`/ruang-belajar/modul/${moduleId}/simpan-aktivitas`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': ambilToken(), 'Accept': 'application/json' },
                body: dataKirim // <-- Gunakan dataKirim di sini
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    localStorage.removeItem(storageKey);
                    
                    var duration = 3000;
                    var end = Date.now() + duration;
                    (function frame() {
                        confetti({ particleCount: 5, angle: 60, spread: 55, origin: { x: 0 }, colors: ['#26ccff', '#a25afd', '#ff5e7e', '#88ff5a', '#fcff42', '#ffa62d', '#ff36ff'] });
                        confetti({ particleCount: 5, angle: 120, spread: 55, origin: { x: 1 }, colors: ['#26ccff', '#a25afd', '#ff5e7e', '#88ff5a', '#fcff42', '#ffa62d', '#ff36ff'] });
                        if (Date.now() < end) requestAnimationFrame(frame);
                    }());

                    const modal = document.getElementById('celebrationModal');
                    const content = document.getElementById('celebrationContent');
                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        content.classList.remove('scale-75');
                        content.classList.add('scale-100');
                    }, 50);
                }
            })
            .catch(error => {
                showWarningToast("Gagal mengirim tugas. Cek koneksi internetmu ya!");
                btn.innerHTML = originalText; btn.disabled = false;
                isSubmitting = false;
            });
        }

        window.addEventListener('beforeunload', function (e) {
            if (!isSubmitting) { e.preventDefault(); e.returnValue = ''; }
        });

        function confirmExit() {
            if(confirm("Yakin ingin kembali ke beranda?")) {
                isSubmitting = true;
                window.location.href = "{{ route('student.dashboard') }}";
            }
        }
    </script>
</body>
</html>