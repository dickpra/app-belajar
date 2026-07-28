<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ruang Belajar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #F0F9FF; font-family: 'Nunito', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .bubbly-card { border-radius: 30px; transition: all 0.3s ease; }
        .bubbly-card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.2); }
    </style>
</head>
<body class="text-gray-800 antialiased min-h-screen">

    <!-- HEADER / NAVBAR -->
    <div class="bg-blue-500 text-white p-6 rounded-b-[3rem] shadow-lg flex justify-between items-center border-b-8 border-blue-600 px-10">
        <div class="flex items-center gap-4">
            <div class="bg-white p-3 rounded-full text-blue-500 shadow-inner">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-black tracking-tight">Halo, {{ session('student_name') }}!</h1>
        </div>
        <a href="{{ route('student.logout') }}" class="bg-red-400 hover:bg-red-500 px-6 py-3 rounded-full font-bold text-white shadow-md border-2 border-white transition transform hover:scale-105">
            Keluar 🚪
        </a>
    </div>

    <div class="max-w-6xl mx-auto px-4 py-12 relative">
        
        <!-- ALERT SUKSES (Muncul jika baru saja menyelesaikan modul) -->
        @if(request()->query('status') == 'hore')
            <div class="bg-green-400 text-white p-6 rounded-3xl shadow-lg mb-10 text-center border-4 border-white animate-bounce">
                <h2 class="text-2xl font-black">🎉 Horeee! Hebat Banget! 🎉</h2>
                <p class="text-lg font-bold mt-1">Kamu berhasil mengumpulkan tugas. Guru akan segera memeriksanya.</p>
            </div>
        @endif

        <div class="text-center mb-10">
            <h2 class="text-4xl font-black text-blue-600 mb-4">Pilih Modul Belajarmu Hari Ini!</h2>
            <p class="text-xl text-gray-500 font-medium">Klik pada kartu di bawah ini dan masukkan PIN dari gurumu ya.</p>
        </div>

        <!-- GRID DAFTAR MODUL -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($modules as $module)
                @php
                    $isCompleted = in_array($module->id, $completedModuleIds);
                    
                    // Array warna-warni ceria bergaya gradien
                    $gradients = [
                        'from-pink-400 to-rose-500',
                        'from-purple-400 to-indigo-500',
                        'from-cyan-400 to-blue-500',
                        'from-emerald-400 to-teal-500',
                        'from-amber-300 to-orange-400',
                    ];
                    // Array emoji menarik
                    $emojis = ['🚀', '🧠', '🎨', '🔬', '🌟', '📚', '🧩', '🏆'];
                    
                    // Menentukan warna dan emoji secara statis berdasarkan ID Modul
                    $randomGradient = $gradients[$module->id % count($gradients)];
                    $randomEmoji = $emojis[$module->id % count($emojis)];
                @endphp

                <!-- KARTU MODUL -->
                <div class="bg-white bubbly-card cursor-pointer text-center relative overflow-hidden group border-4 {{ $isCompleted ? 'border-green-400 opacity-80' : 'border-blue-100' }}" 
                    onclick="{{ $isCompleted ? 'alert(\'Kamu sudah menyelesaikan modul ini! Hebat! ✅\')' : 'bukaModalPin('.$module->id.', \''.($module->access_pin ? 'yes' : 'no').'\', \''.addslashes($module->title).'\')' }}">
                    
                    <!-- BLOK GAMBAR SAMPUL -->
                    <div class="h-48 w-full relative {{ $module->cover_image ? 'bg-blue-50' : 'bg-gradient-to-br ' . $randomGradient }} flex items-center justify-center overflow-hidden transition-all duration-300">
                        @if($module->cover_image)
                            <img src="{{ asset('storage/' . $module->cover_image) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <!-- Ornamen Pola Acak Menarik -->
                            <div class="absolute -top-6 -left-6 w-24 h-24 bg-white opacity-20 rounded-full mix-blend-overlay animate-pulse"></div>
                            <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-white opacity-20 rounded-full mix-blend-overlay animate-pulse delay-75"></div>
                            
                            <!-- Emoji Utama -->
                            <div class="text-7xl transform group-hover:scale-125 group-hover:rotate-12 transition duration-300 drop-shadow-xl">
                                {{ $randomEmoji }}
                            </div>
                        @endif
                    </div>
                    
                    <!-- KONTEN TEKS KARTU -->
                    <div class="p-8">
                        <h3 class="text-2xl font-black text-gray-800 mb-3">{{ $module->title }}</h3>
                        <p class="text-gray-500 font-medium mb-6 line-clamp-2">{{ $module->description ?? 'Ayo kerjakan aktivitas seru di dalam modul ini!' }}</p>
                        
                        @if($isCompleted)
                            <span class="inline-block bg-green-500 text-white font-bold px-6 py-2 rounded-full shadow-sm">
                                Selesai Dikerjakan ✅
                            </span>
                        @else
                            <span class="inline-block bg-yellow-400 text-yellow-900 font-bold px-6 py-2 rounded-full border-2 border-white shadow-sm group-hover:bg-yellow-500 group-hover:translate-y-1 transition-transform">
                                Mulai Kerjakan ➔
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <!-- Tampilan saat modul kosong -->
                <div class="col-span-full text-center py-12">
                    <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" class="w-32 mx-auto mb-6 opacity-50" alt="Kosong">
                    <h3 class="text-2xl font-bold text-gray-500">Belum ada modul yang aktif.</h3>
                    <p class="text-gray-400 mt-2">Tunggu gurumu menambahkan tugas ya!</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- MODAL INPUT PIN -->
    <div id="pinModal" class="fixed inset-0 bg-blue-900 bg-opacity-70 flex items-center justify-center z-50 hidden backdrop-blur-sm transition-opacity">
        <div class="bg-white max-w-sm w-full p-8 rounded-[2.5rem] shadow-2xl border-4 border-blue-400 text-center transform scale-95 transition-transform" id="pinModalContent">
            
            <div class="w-16 h-16 bg-yellow-400 text-yellow-900 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-white shadow-md">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>

            <h3 class="text-2xl font-black text-gray-800 mb-2">Kunci Terpasang!</h3>
            <p class="text-sm text-gray-500 font-medium mb-6" id="modalModuleName">Masukkan PIN dari gurumu untuk membuka modul ini.</p>
            
            <!-- Input PIN (Batas maksimal 6 karakter jika mengacu pada database, disesuaikan) -->
            <input type="password" id="inputPin" maxlength="6" placeholder="* * * *" class="w-full text-center text-4xl px-4 py-4 border-4 border-gray-200 rounded-2xl focus:border-blue-500 focus:ring-0 transition-all font-black text-gray-800 tracking-[0.5em] mb-2 shadow-inner">
            <p id="pinError" class="text-red-500 text-sm font-bold h-6 mb-4 opacity-0 transition-opacity">PIN salah, coba lagi!</p>

            <div class="flex gap-4">
                <button onclick="tutupModal()" class="w-1/2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 rounded-full border-2 border-transparent transition">
                    Batal
                </button>
                <button id="btnBuka" onclick="cekPin()" class="w-1/2 bg-blue-500 hover:bg-blue-600 text-white font-black py-3 rounded-full shadow-[0_4px_0_#2563eb] hover:translate-y-1 transition border-2 border-white">
                    Buka! 🚀
                </button>
            </div>
        </div>
    </div>

    <!-- SCRIPT LOGIKA KEAMANAN AJAX -->
    <script>
        let currentModuleId = null;

        // Hapus parameter ?status=hore dari URL tanpa me-refresh halaman
        if (window.location.search.includes('status=hore')) {
            window.history.replaceState(null, '', window.location.pathname);
        }

        function bukaModalPin(moduleId, requiresPin, moduleName) {
            currentModuleId = moduleId;
            
            // Jika modul tidak butuh PIN, langsung minta akses ke backend
            if (requiresPin === 'no') {
                prosesVerifikasiPin('');
                return;
            }

            // Jika butuh PIN, tampilkan Modal
            document.getElementById('modalModuleName').innerText = `Membuka Modul: ${moduleName}`;
            document.getElementById('inputPin').value = '';
            document.getElementById('pinError').classList.add('opacity-0');
            
            const modal = document.getElementById('pinModal');
            modal.classList.remove('hidden');
            
            // Animasi pop-up
            setTimeout(() => {
                document.getElementById('pinModalContent').classList.remove('scale-95');
                document.getElementById('pinModalContent').classList.add('scale-100');
                document.getElementById('inputPin').focus();
            }, 50);
        }

        function tutupModal() {
            document.getElementById('pinModalContent').classList.remove('scale-100');
            document.getElementById('pinModalContent').classList.add('scale-95');
            setTimeout(() => {
                document.getElementById('pinModal').classList.add('hidden');
            }, 200);
        }

        function cekPin() {
            const inputPin = document.getElementById('inputPin').value;
            prosesVerifikasiPin(inputPin);
        }

        function prosesVerifikasiPin(pinValue) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const btn = document.getElementById('btnBuka');
            
            if(btn) btn.innerHTML = "Mengecek... ⏳";

            fetch(`/ruang-belajar/modul/${currentModuleId}/verifikasi-pin`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ pin: pinValue })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // PIN Benar: Arahkan ke halaman modul
                    window.location.href = `/ruang-belajar/modul/${currentModuleId}`;
                } else {
                    // PIN Salah: Tampilkan animasi error
                    const errorMsg = document.getElementById('pinError');
                    const inputField = document.getElementById('inputPin');
                    
                    errorMsg.classList.remove('opacity-0');
                    inputField.classList.add('border-red-400', 'text-red-500');
                    inputField.value = ''; // Bersihkan input
                    
                    setTimeout(() => {
                        inputField.classList.remove('border-red-400', 'text-red-500');
                    }, 1500);

                    if(btn) btn.innerHTML = "Buka! 🚀";
                }
            })
            .catch(error => {
                alert('Waduh, koneksi terputus. Coba lagi ya!');
                if(btn) btn.innerHTML = "Buka! 🚀";
            });
        }

        // Dukungan enter pada keyboard
        document.getElementById('inputPin').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') cekPin();
        });
    </script>
</body>
</html>