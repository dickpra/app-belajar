<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Raporku - Ruang Belajar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; background-color: #F0FDF4; } /* Warna hijau muda */
        .page-transition { animation: slideUpFade 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes slideUpFade { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col pb-24">
    
    <div class="bg-emerald-500 text-white p-6 md:p-10 rounded-b-[3rem] shadow-lg border-b-8 border-emerald-600 text-center">
        <h1 class="text-3xl font-black tracking-tight">Raporku 📚</h1>
        <p class="text-emerald-100 font-bold mt-1">Koleksi nilai dari petualangan belajarmu!</p>
    </div>

    <div class="flex-1 max-w-md mx-auto w-full px-4 pt-8 flex flex-col gap-4 page-transition">
        @forelse($submissions as $sub)
            <div class="bg-white p-4 rounded-2xl border-4 border-slate-200 shadow-sm flex items-center gap-4">
                <div class="w-16 h-16 rounded-xl border-[3px] flex-shrink-0 flex items-center justify-center text-xl font-black
                    {{ $sub->total_score >= 80 ? 'bg-emerald-100 border-emerald-400 text-emerald-600' : ($sub->total_score >= 50 ? 'bg-amber-100 border-amber-400 text-amber-600' : 'bg-red-100 border-red-400 text-red-600') }}">
                    {{ $sub->total_score ?? 0 }}
                </div>
                <div class="flex-1">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $sub->activity->module->title ?? 'Modul' }}</span>
                    <h3 class="font-black text-slate-700 leading-tight">{{ $sub->activity->title ?? 'Tugas' }}</h3>
                </div>
                <div class="text-2xl drop-shadow-sm">
                    {{ $sub->total_score >= 80 ? '🏆' : ($sub->total_score >= 50 ? '👍' : '💪') }}
                </div>
            </div>
        @empty
            <div class="text-center bg-white p-8 rounded-3xl border-4 border-slate-200 mt-10">
                <div class="text-6xl mb-4 grayscale">📭</div>
                <h3 class="font-black text-xl text-slate-500">Belum Ada Nilai</h3>
                <p class="font-bold text-slate-400 text-sm mt-2">Selesaikan tugas di Peta Petualangan agar Pak/Bu Guru bisa memberimu nilai!</p>
            </div>
        @endforelse
    </div>

    @include('student.navbar-bawah')
</body>
</html>