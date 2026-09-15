<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Perjalanan Belajar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #F0F9FF; font-family: 'Nunito', 'Segoe UI', Tahoma, sans-serif; }
        .bubbly-button { transition: all 0.2s ease; }
        .bubbly-button:active { transform: translateY(4px); box-shadow: 0 0px 0 transparent !important; }
    </style>
</head>
<body class="text-gray-800 antialiased min-h-screen relative overflow-x-hidden">

    <div class="bg-blue-500 text-white p-6 rounded-b-[3rem] shadow-lg flex justify-between items-center border-b-8 border-blue-600 px-6 md:px-10 relative z-20">
        <div class="flex items-center gap-4">
            <div class="bg-white p-3 rounded-full text-blue-500 shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <div>
                <h1 class="text-xl md:text-3xl font-black tracking-tight">Halo, {{ session('student_name') }}!</h1>
                <p class="text-blue-200 font-bold text-sm hidden md:block">Lanjutkan perjalanan belajarmu hari ini 🚀</p>
            </div>
        </div>
        {{-- <a href="{{ route('student.logout') }}" class="bg-red-400 hover:bg-red-500 px-4 md:px-6 py-2 md:py-3 rounded-full font-black text-white shadow-[0_4px_0_#b91c1c] bubbly-button border-2 border-white">
            Keluar
        </a> --}}
    </div>

    <div class="max-w-2xl mx-auto px-4 py-12 pb-32 relative">
        
        <div class="absolute top-10 bottom-20 left-1/2 transform -translate-x-1/2 w-6 md:w-8 bg-blue-100 rounded-full z-0"></div>

        <div class="flex flex-col gap-12 md:gap-20 relative z-10 pt-10">
            @php 
                $previousCompleted = true; // Modul 1 selalu terbuka
                $emojis = ['🚀', '🧠', '🎨', '🔬', '🌟', '📚', '🧩', '🏆'];
            @endphp

            @forelse($modules as $index => $module)
                @php
                    $isCompleted = in_array($module->id, $completedModuleIds);
                    $isInProgress = in_array($module->id, $inProgressModuleIds ?? []); // 👈 Deteksi status Lanjut
                    $isUnlocked = $previousCompleted;
                    
                    $previousCompleted = $isCompleted; 
                    $translateClass = $index % 2 == 0 ? '-translate-x-6 md:-translate-x-12' : 'translate-x-6 md:translate-x-12';
                    $randomEmoji = $emojis[$module->id % count($emojis)];
                @endphp

                <div class="w-full flex justify-center transform {{ $translateClass }}">
                    
                    @if($isUnlocked)
                        <!-- 👇 Perhatikan tambahan tanda kutip tunggal (' ') dan fungsi acak_id() di parameter pertama 👇 -->
                        <button onclick="bukaModalPin('{{ acak_id($module->id) }}', '{{ $module->access_pin ? 'yes' : 'no' }}', '{{ addslashes($module->title) }}')" 
                            class="group relative bg-white w-[260px] md:w-[320px] rounded-[2rem] border-4 
                            {{ $isCompleted ? 'border-green-400 shadow-[0_8px_0_#4ade80]' : ($isInProgress ? 'border-orange-400 shadow-[0_8px_0_#fb923c]' : 'border-blue-400 shadow-[0_8px_0_#60a5fa]') }} 
                            p-6 text-center bubbly-button hover:-translate-y-2">
                            
                            <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 w-20 h-20 rounded-full border-4 border-white flex items-center justify-center text-4xl shadow-md 
                                {{ $isCompleted ? 'bg-green-400' : ($isInProgress ? 'bg-orange-400' : 'bg-blue-400') }}">
                                {{ $isCompleted ? '⭐' : $randomEmoji }}
                            </div>

                            <div class="mt-8">
                                <h3 class="text-xl font-black {{ $isCompleted ? 'text-green-600' : ($isInProgress ? 'text-orange-600' : 'text-blue-600') }} leading-tight mb-2">{{ $module->title }}</h3>
                                
                                @if($isCompleted)
                                @php
                                    // Cek apakah tugas di modul ini sudah dinilai oleh guru dan ambil rata-ratanya
                                    $avgScore = \App\Models\ActivitySubmission::where('student_id', session('student_id') ?? auth()->id())
                                        ->whereHas('activity', fn($q) => $q->where('module_id', $module->id))
                                        ->where('status', 'dinilai')
                                        ->avg('total_score');
                                @endphp
                                
                                @if(!is_null($avgScore))
                                    <!-- JIKA SUDAH DINILAI GURU -->
                                    <div class="flex flex-col items-center gap-1.5 mt-1">
                                        <span class="inline-block bg-gradient-to-r from-yellow-400 to-orange-500 text-white font-black px-4 py-1 rounded-full border-2 border-white shadow-sm text-sm uppercase tracking-wider">
                                            Skor: {{ round($avgScore) }} 🏆
                                        </span>
                                        <div class="flex gap-1 text-yellow-400 text-base drop-shadow-sm animate-pulse">
                                            @if($avgScore >= 80) ⭐⭐⭐ 
                                            @elseif($avgScore >= 50) ⭐⭐ 
                                            @else ⭐ 
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <!-- JIKA SELESAI TAPI BELUM DINILAI -->
                                    <span class="inline-block bg-emerald-100 text-emerald-700 font-black px-4 py-1.5 rounded-full text-xs border-2 border-emerald-200 uppercase tracking-widest">
                                        ⏳ Menunggu Nilai
                                    </span>
                                @endif
                            @elseif($isInProgress)
                                    <span class="inline-block bg-orange-500 text-white font-black px-5 py-2 rounded-full border-2 border-white shadow-sm">LANJUT ➔</span>
                                @else
                                    <span class="inline-block bg-yellow-400 text-yellow-900 font-black px-5 py-2 rounded-full border-2 border-white shadow-sm">MULAI ➔</span>
                                @endif
                            </div>
                        </button>
                    @else
                        <!-- (Blok Kode Card Terkunci / Abu-abu Biarkan Sama Seperti Aslinya) -->   
                        <div class="relative bg-gray-100 w-[260px] md:w-[320px] rounded-[2rem] border-4 border-gray-300 p-6 text-center shadow-[0_8px_0_#d1d5db] opacity-80 cursor-not-allowed">
                            
                            <div class="absolute -top-10 left-1/2 transform -translate-x-1/2 w-20 h-20 rounded-full border-4 border-white flex items-center justify-center text-4xl shadow-md bg-gray-300">
                                🔒
                            </div>

                            <div class="mt-8">
                                <h3 class="text-xl font-black text-gray-400 leading-tight mb-2">{{ $module->title }}</h3>
                                <p class="text-xs font-bold text-gray-400">Selesaikan level sebelumnya untuk membuka.</p>
                            </div>
                        </div>
                    @endif

                </div>
            @empty
                <div class="text-center py-12">
                    <h3 class="text-2xl font-bold text-gray-500">Belum ada rute perjalanan.</h3>
                </div>
            @endforelse
            
            @if(count($modules) > 0)
                <div class="w-full flex justify-center mt-4">
                    <div class="w-24 h-24 bg-yellow-400 rounded-full border-8 border-white shadow-[0_8px_0_#eab308] flex items-center justify-center text-5xl">
                        🎁
                    </div>
                </div>
            @endif

        </div>
    </div>

    <div id="pinModal" class="fixed inset-0 bg-blue-900 bg-opacity-70 flex items-center justify-center z-50 hidden backdrop-blur-sm transition-opacity">
        <div class="bg-white max-w-sm w-full p-8 rounded-[2.5rem] shadow-2xl border-4 border-blue-400 text-center transform scale-95 transition-transform" id="pinModalContent">
            
            <div class="w-16 h-16 bg-yellow-400 text-yellow-900 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-white shadow-md">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>

            <h3 class="text-2xl font-black text-gray-800 mb-2">Level Terkunci!</h3>
            <p class="text-sm text-gray-500 font-bold mb-6" id="modalModuleName">Minta PIN ke gurumu untuk membuka level ini.</p>
            
            <input type="password" id="inputPin" maxlength="6" placeholder="* * * *" class="w-full text-center text-4xl px-4 py-4 border-4 border-gray-200 rounded-2xl focus:border-blue-500 outline-none font-black text-gray-800 tracking-[0.5em] mb-2 shadow-inner">
            <p id="pinError" class="text-red-500 text-sm font-bold h-6 mb-4 opacity-0 transition-opacity">PIN salah, coba lagi!</p>

            <div class="flex gap-4">
                <button onclick="tutupModal()" class="w-1/2 bg-gray-200 text-gray-700 font-black py-3 rounded-full bubbly-button">Batal</button>
                <button id="btnBuka" onclick="cekPin()" class="w-1/2 bg-blue-500 text-white font-black py-3 rounded-full shadow-[0_4px_0_#2563eb] border-2 border-white bubbly-button">Buka! 🚀</button>
            </div>
        </div>
    </div>

   @include('student.navbar-bawah')

    <script>
        let currentModuleId = null;

        if (window.location.search.includes('status=hore')) {
            window.history.replaceState(null, '', window.location.pathname);
        }

        function bukaModalPin(moduleId, requiresPin, moduleName) {
            currentModuleId = moduleId;
            if (requiresPin === 'no') {
                prosesVerifikasiPin('');
                return;
            }
            document.getElementById('inputPin').value = '';
            document.getElementById('pinError').classList.add('opacity-0');
            const modal = document.getElementById('pinModal');
            modal.classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('pinModalContent').classList.remove('scale-95');
                document.getElementById('pinModalContent').classList.add('scale-100');
                document.getElementById('inputPin').focus();
            }, 50);
        }

        function tutupModal() {
            document.getElementById('pinModalContent').classList.remove('scale-100');
            document.getElementById('pinModalContent').classList.add('scale-95');
            setTimeout(() => { document.getElementById('pinModal').classList.add('hidden'); }, 200);
        }

        function cekPin() {
            prosesVerifikasiPin(document.getElementById('inputPin').value);
        }

        function prosesVerifikasiPin(pinValue) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const btn = document.getElementById('btnBuka');
            if(btn) btn.innerHTML = "Mengecek... ⏳";

            fetch(`/ruang-belajar/modul/${currentModuleId}/verifikasi-pin`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ pin: pinValue })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = `/ruang-belajar/modul/${currentModuleId}`;
                } else {
                    document.getElementById('pinError').classList.remove('opacity-0');
                    if(btn) btn.innerHTML = "Buka! 🚀";
                }
            });
        }

        document.getElementById('inputPin').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') cekPin();
        });
    </script>
</body>
</html>