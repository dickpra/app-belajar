<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Panduan - Ruang Belajar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; background-color: #FFFBEB; } /* Warna kuning muda */
        .page-transition { animation: slideUpFade 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes slideUpFade { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col pb-24">
    
    <div class="bg-amber-400 text-amber-900 p-6 md:p-10 rounded-b-[3rem] shadow-lg border-b-8 border-amber-500 text-center">
        <h1 class="text-3xl font-black tracking-tight">Panduan 💡</h1>
        <p class="text-amber-800 font-bold mt-1">Arti ikon dan cara bermain di Ruang Belajar.</p>
    </div>

    <div class="flex-1 max-w-md mx-auto w-full px-4 pt-8 flex flex-col gap-4 page-transition">
        
        <div class="bg-white p-4 rounded-2xl border-4 border-slate-200 shadow-sm flex gap-4">
            <div class="w-12 h-12 bg-slate-100 rounded-full border-[3px] border-slate-300 flex items-center justify-center text-xl shrink-0">🔒</div>
            <div>
                <h3 class="font-black text-slate-700">Level Terkunci</h3>
                <p class="font-bold text-slate-500 text-sm">Kamu harus menyelesaikan level sebelumnya untuk membuka misi ini.</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border-4 border-slate-200 shadow-sm flex gap-4">
            <div class="w-12 h-12 bg-blue-100 rounded-full border-[3px] border-blue-400 flex items-center justify-center text-xl shrink-0">⭐</div>
            <div>
                <h3 class="font-black text-blue-700">Level Sedang Terbuka</h3>
                <p class="font-bold text-slate-500 text-sm">Level ini bisa kamu mainkan sekarang. Ayo kumpulkan poinnya!</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border-4 border-slate-200 shadow-sm flex gap-4">
            <div class="w-12 h-12 bg-green-100 rounded-full border-[3px] border-green-400 flex items-center justify-center text-xl shrink-0">✅</div>
            <div>
                <h3 class="font-black text-green-700">Tugas Selesai</h3>
                <p class="font-bold text-slate-500 text-sm">Kamu sudah mengirimkan jawaban dan sedang diperiksa oleh gurumu.</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border-4 border-slate-200 shadow-sm flex gap-4">
            <div class="w-12 h-12 bg-purple-100 rounded-full border-[3px] border-purple-400 flex items-center justify-center text-xl shrink-0">🤟</div>
            <div>
                <h3 class="font-black text-purple-700">Bantuan Isyarat</h3>
                <p class="font-bold text-slate-500 text-sm">Klik tombol ini untuk melihat video penerjemah bahasa isyarat.</p>
            </div>
        </div>

    </div>

    @include('student.navbar-bawah')
</body>
</html>