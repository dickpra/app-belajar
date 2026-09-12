<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Profilku - Ruang Belajar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; background-color: #F0F9FF; }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom, 20px); }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col pb-24">

    <!-- Header Biru -->
    <div class="bg-blue-500 text-white p-6 md:p-10 rounded-b-[3rem] shadow-lg border-b-8 border-blue-600 relative z-20 text-center">
        <h1 class="text-3xl font-black tracking-tight">Kartu Pelajar</h1>
        <p class="text-blue-200 font-bold mt-1">Identitas Jagoan Ruang Belajar!</p>
    </div>

    <!-- Konten Profil -->
    <div class="flex-1 max-w-md mx-auto w-full px-6 pt-10 flex flex-col gap-6 relative z-10">
        
        <!-- Kartu Identitas -->
        <div class="bg-white rounded-3xl p-8 border-4 border-slate-200 shadow-sm text-center relative mt-8">
            <div class="absolute -top-16 left-1/2 transform -translate-x-1/2">
                <!-- Avatar Sederhana (Emoji) -->
                <div class="w-32 h-32 bg-blue-100 rounded-full border-8 border-white shadow-md flex items-center justify-center text-7xl">
                    👦 <!-- Bisa diganti 👧 -->
                </div>
            </div>
            
            <div class="mt-16">
                <span class="inline-block bg-purple-100 text-purple-600 font-black px-4 py-1.5 rounded-full text-xs uppercase tracking-widest mb-3 border-2 border-purple-200">
                    Pelajar Hebat 🌟
                </span>
                <h2 class="text-3xl font-black text-slate-800">{{ session('student_name') ?? 'Nama Murid' }}</h2>
                <p class="text-slate-500 font-bold mt-2 text-lg">PIN: <span class="tracking-widest bg-slate-100 px-3 py-1 rounded-lg">****</span></p>
            </div>
        </div>

        <!-- Statistik Bintang (Opsional/Statis dulu) -->
        <div class="flex gap-4">
            <div class="flex-1 bg-white p-5 rounded-3xl border-4 border-slate-200 shadow-sm text-center">
                <div class="text-4xl mb-2">🏆</div>
                <div class="text-2xl font-black text-slate-700">0</div>
                <div class="text-xs font-bold text-slate-400 uppercase">Total Poin</div>
            </div>
            <div class="flex-1 bg-white p-5 rounded-3xl border-4 border-slate-200 shadow-sm text-center">
                <div class="text-4xl mb-2">⭐</div>
                <div class="text-2xl font-black text-slate-700">0</div>
                <div class="text-xs font-bold text-slate-400 uppercase">Tugas Selesai</div>
            </div>
        </div>

        <!-- Tombol Keluar / Logout -->
        <div class="mt-8">
            <a href="{{ route('student.logout') }}" onclick="return confirm('Kamu yakin mau keluar?')" 
               class="block w-full bg-red-500 hover:bg-red-400 text-white text-center font-black text-xl py-4 rounded-2xl shadow-[0_6px_0_#b91c1c] active:shadow-none active:translate-y-[6px] transition-all border-4 border-white">
                Keluar dari Ruang Belajar ✖
            </a>
        </div>
    </div>

@include('student.navbar-bawah')

</body>
</html>