<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Profilku - Ruang Belajar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800;900&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Nunito', sans-serif; 
            background-color: #E0F2FE; /* Langit biru muda */
            background-image: radial-gradient(#bae6fd 2px, transparent 2px);
            background-size: 30px 30px; /* Motif polkadot */
        }
        
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
        
        /* Tombol 3D ala Game */
        .btn-3d { transition: all 0.1s; }
        .btn-3d:active { transform: translateY(6px); box-shadow: 0 0px 0px transparent !important; }

        /* Animasi melayang ringan untuk Avatar */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .floating-avatar { animation: float 4s ease-in-out infinite; }

        /* =========================================
           SOLUSI FINAL EMOJI CENTER 
           ========================================= */
        .avatar-circle {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .avatar-emoji {
            font-family: "Apple Color Emoji", "Segoe UI Emoji", "Noto Color Emoji", sans-serif;
            font-size: 4.5rem;
            line-height: 1;
            display: block;
            user-select: none;
        }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col pb-28 overflow-x-hidden">

    <!-- Header Melengkung Khas Game -->
    <div class="bg-sky-400 text-white p-8 md:p-10 rounded-b-[3rem] shadow-[0_8px_0_#0284c7] relative z-20 text-center border-b-4 border-white">
        <!-- Ornamen hiasan di header -->
        <div class="absolute top-4 left-4 text-4xl opacity-20 transform -rotate-12">☁️</div>
        <div class="absolute top-8 right-6 text-3xl opacity-20 transform rotate-12">☁️</div>
        
        <h1 class="text-3xl md:text-4xl font-black tracking-tight mt-2 drop-shadow-md">Kartu Pelajar</h1>
        <p class="text-sky-100 font-bold mt-1 text-sm md:text-base">Identitas Jagoan Ruang Belajar!</p>
    </div>

    <!-- Konten Profil -->
    <div class="flex-1 max-w-sm md:max-w-md mx-auto w-full px-4 pt-12 flex flex-col gap-6 relative z-10">
        
        <!-- Kartu Identitas Bubbly -->
        <div class="bg-white rounded-[2.5rem] p-8 border-[6px] border-white shadow-[0_12px_0_#cbd5e1,0_15px_20px_rgba(0,0,0,0.05)] text-center relative mt-6">
            
            <!-- AVATAR ANAK -->
            <!-- Wrapper luar: hanya untuk posisi -->
            <div class="absolute -top-20 inset-x-0 flex justify-center z-30">
                <!-- Elemen dalam: hanya untuk animasi -->
                <div class="floating-avatar">
                    <div class="w-32 h-32 bg-yellow-300 rounded-full border-[6px] border-white shadow-[0_8px_0_#ca8a04] avatar-circle">
                        <img src="https://api.dicebear.com/9.x/fun-emoji/svg?seed={{ session('student_name') ?? 'Mukidi' }}&backgroundColor=transparent" 
                         alt="Avatar" class="w-24 h-24 object-contain">
                    </div>
                </div>
            </div>
            
            <div class="mt-14">
                <span class="inline-block bg-purple-100 text-purple-600 font-black px-5 py-2 rounded-full text-xs uppercase tracking-widest mb-3 border-2 border-purple-200 shadow-sm">
                    Pelajar Hebat 🌟
                </span>
                <h2 class="text-3xl font-black text-slate-700 leading-tight">{{ session('student_name') ?? 'Mukidi' }}</h2>
                
                <!-- Tampilan PIN yang lebih interaktif -->
                <div class="mt-4 bg-slate-50 inline-block px-4 py-2 rounded-2xl border-4 border-slate-100">
                    <p class="text-slate-400 font-bold text-sm uppercase tracking-wide mb-1">PIN Rahasia</p>
                    <p class="font-black text-2xl tracking-[0.5em] text-slate-700 leading-none">* * * *</p>
                </div>
            </div>
        </div>

        <!-- Statistik Bintang (Warna-warni agar menarik) -->
        <div class="flex gap-4 mt-2">
            <!-- Kartu Poin -->
            <div class="flex-1 bg-yellow-400 p-5 rounded-3xl border-4 border-white shadow-[0_8px_0_#ca8a04] text-center transform transition hover:scale-105">
                <div class="text-4xl mb-1 drop-shadow-md">🏆</div>
                <div class="text-3xl font-black text-white drop-shadow-md">0</div>
                <div class="text-[10px] md:text-xs font-black text-yellow-800 uppercase tracking-wide mt-1">Total Poin</div>
            </div>
            <!-- Kartu Tugas -->
            <div class="flex-1 bg-green-400 p-5 rounded-3xl border-4 border-white shadow-[0_8px_0_#16a34a] text-center transform transition hover:scale-105">
                <div class="text-4xl mb-1 drop-shadow-md">⭐</div>
                <div class="text-3xl font-black text-white drop-shadow-md">0</div>
                <div class="text-[10px] md:text-xs font-black text-green-900 uppercase tracking-wide mt-1">Selesai</div>
            </div>
        </div>

        <!-- Tombol Keluar / Logout -->
        <div class="mt-6 mb-10">
            <a href="{{ route('student.logout') }}" onclick="return confirm('Kamu yakin mau istirahat dan keluar?')" 
               class="btn-3d flex items-center justify-center gap-3 w-full bg-red-500 text-white font-black text-xl py-5 rounded-2xl shadow-[0_8px_0_#b91c1c] border-4 border-white uppercase tracking-wide">
                <span>Keluar</span>
                <span class="text-2xl">🚪</span>
            </a>
        </div>
    </div>

    @include('student.navbar-bawah')

</body>
</html>