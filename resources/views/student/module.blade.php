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
        
        #sidebar { transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1); }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="text-slate-800 antialiased h-screen flex overflow-hidden bg-slate-50">

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
                                    <div class="prose prose-blue text-slate-700 mx-auto font-bold leading-relaxed w-full max-w-full">
                                        {!! renderPrivateImages($stage['konten_tahapan'] ?? '') !!}
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

                                @endforeach
                            </div>

                            <div class="mt-8 pt-6 border-t-[3px] border-dashed border-slate-200">
                                @if($aIndex < count($module->activities) - 1)
                                    <button type="button" onclick="simpanDanLanjut({{ $aIndex }}, {{ $module->id }})" class="w-full md:w-auto md:float-right bg-green-500 hover:bg-green-400 text-white font-black text-xl py-3.5 px-8 rounded-full shadow-[0_5px_0_#16a34a] active:shadow-none active:translate-y-[5px] transition-all border-[3px] border-white">
                                        Simpan Jawaban ➔
                                    </button>
                                @else
                                    <button type="button" onclick="simpanDanSelesai({{ $aIndex }}, {{ $module->id }})" class="w-full bg-orange-500 hover:bg-orange-400 text-white font-black text-xl py-3.5 rounded-full shadow-[0_5px_0_#ea580c] active:shadow-none active:translate-y-[5px] transition-all border-[3px] border-white">
                                        🏆 Kumpulkan Tugas!
                                    </button>
                                @endif
                                <div class="clear-both"></div>
                            </div>
                        </form>
                    </div>

                </div>
            @endforeach

        </div>
    </main>

    <div id="mobile-overlay" onclick="toggleSidebar()" class="fixed inset-0 bg-slate-900/40 z-40 hidden md:hidden backdrop-blur-sm transition-all"></div>

    <script>
        const moduleId = {{ $module->id }};
        const totalActivities = {{ count($module->activities) }};
        const storageKey = `resume_module_${moduleId}_student_{{ session('student_id') }}`;
        
        let highestUnlockedIndex = 0;
        let currentIndex = 0;
        let isSubmitting = false;

        document.addEventListener("DOMContentLoaded", function() {
            let savedUnlocked = localStorage.getItem(storageKey);
            if (savedUnlocked !== null) {
                highestUnlockedIndex = parseInt(savedUnlocked);
                if(highestUnlockedIndex >= totalActivities) highestUnlockedIndex = totalActivities - 1;
                currentIndex = highestUnlockedIndex;
            }
            updateNavigationUI();
            showActivity(currentIndex);
            if(window.innerWidth < 768) toggleSidebar();
        });

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
                if (i <= highestUnlockedIndex) {
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    btn.classList.add('cursor-pointer', 'hover:bg-slate-100');
                    icon.innerHTML = i < highestUnlockedIndex ? '✅' : '⭐'; 
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
        // MESIN PENARIK GARIS SVG (UNTUK SOAL MATCHING)
        // =========================================================
        let aktifKiri = {}; 
        function pilihKiri(btn, soalId) {
            document.querySelectorAll(`.btn-kiri-${soalId}`).forEach(el => {
                el.classList.remove('border-blue-500', 'bg-blue-50', 'ring-4', 'ring-blue-100');
                el.querySelector('.konektor-kiri').classList.replace('bg-blue-500', 'bg-slate-200');
                el.querySelector('.konektor-kiri').classList.replace('bg-green-500', 'bg-slate-200');
            });
            btn.classList.add('border-blue-500', 'bg-blue-50', 'ring-4', 'ring-blue-100');
            btn.querySelector('.konektor-kiri').classList.replace('bg-slate-200', 'bg-blue-500');
            aktifKiri[soalId] = btn;
        }

        function pilihKanan(btn, soalId) {
            if(!aktifKiri[soalId]) { alert("Pilih kotak di sebelah kiri dulu ya!"); return; }
            let btnKiri = aktifKiri[soalId];

            btn.classList.add('border-green-500', 'bg-green-50');
            btn.querySelector('.konektor-kanan').classList.replace('bg-slate-200', 'bg-green-500');
            btnKiri.classList.replace('border-blue-500', 'border-green-500');
            btnKiri.classList.replace('bg-blue-50', 'bg-green-50');
            btnKiri.classList.remove('ring-4', 'ring-blue-100');
            btnKiri.querySelector('.konektor-kiri').classList.replace('bg-blue-500', 'bg-green-500');

            // Menyimpan jawaban dalam format JSON String agar Database Server Tidak Crash
            let hiddenContainer = document.getElementById(`hidden-inputs-${soalId}`);
            let hiddenInput = document.getElementById(`ans-${soalId}`);
            if(!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.id = `ans-${soalId}`;
                hiddenInput.name = `jawaban[${soalId}]`;
                hiddenInput.value = "{}";
                hiddenContainer.appendChild(hiddenInput);
            }
            let currentAns = JSON.parse(hiddenInput.value);
            currentAns[btnKiri.dataset.nilai] = btn.dataset.nilai;
            hiddenInput.value = JSON.stringify(currentAns);

            gambarGarisSVG(btnKiri, btn, soalId);
            aktifKiri[soalId] = null;
        }

        function gambarGarisSVG(elKiri, elKanan, soalId) {
            let svg = document.getElementById(`svg-canvas-${soalId}`);
            let container = document.getElementById(`match-wrap-${soalId}`);
            let lineId = `line-${soalId}-${elKiri.dataset.nilai.replace(/[^a-zA-Z0-9]/g, '')}`;
            let line = document.getElementById(lineId);
            
            if(!line) {
                line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                line.id = lineId;
                line.setAttribute('stroke', '#22c55e');
                line.setAttribute('stroke-width', '6');
                line.setAttribute('stroke-linecap', 'round');
                line.style.strokeDasharray = "1000";
                line.style.strokeDashoffset = "1000";
                line.style.transition = "stroke-dashoffset 0.5s ease-out";
                svg.appendChild(line);
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
        function validasiForm(formElement) { return formElement.reportValidity(); }

        function simpanDanLanjut(index, moduleId) {
            const form = document.getElementById(`form-activity-${index}`);
            if (!validasiForm(form)) return;

            const btn = form.querySelector('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = "Menyimpan... ⏳"; btn.disabled = true;

            fetch(`/ruang-belajar/modul/${moduleId}/simpan-aktivitas`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': ambilToken(), 'Accept': 'application/json' },
                body: prepareSafeFormData(form) // Kirim data yang sudah di-sterilkan
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
            if (!validasiForm(form)) return;

            isSubmitting = true;
            const btn = form.querySelector('button');
            btn.innerHTML = "Mengirim... 🚀"; btn.disabled = true;

            fetch(`/ruang-belajar/modul/${moduleId}/simpan-aktivitas`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': ambilToken(), 'Accept': 'application/json' },
                body: prepareSafeFormData(form)
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    localStorage.removeItem(storageKey);
                    window.location.href = "{{ route('student.dashboard') }}";
                }
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