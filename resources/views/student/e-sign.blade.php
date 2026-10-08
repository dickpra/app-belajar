<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>E-Sign Kamus - Ruang Belajar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; background-color: #F8FAFC; }
        .page-transition { animation: slideUpFade 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes slideUpFade { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        /* Hilangkan scrollbar tapi bisa discroll */
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>

<body class="text-slate-800 antialiased min-h-screen flex flex-col pb-28 relative">

    <!-- HEADER -->
    <div class="bg-purple-500 text-purple-50 p-6 md:p-10 rounded-b-[3rem] shadow-lg border-b-8 border-purple-700 text-center relative z-10 sticky top-0">
        <h1 class="text-3xl font-black tracking-tight">Kamus E-Sign 🤟</h1>
        <p class="text-purple-200 font-bold mt-1">Pilih kata untuk melihat isyaratnya</p>
        
        <!-- PITA NAVIGASI ABJAD CEPAT -->
        <div class="flex gap-2 overflow-x-auto mt-6 pb-2 no-scrollbar px-2 snap-x">
            @foreach($groupedWords as $letter => $items)
                <a href="#huruf-{{ $letter }}" class="shrink-0 snap-center bg-white text-purple-700 font-black w-10 h-10 rounded-full flex items-center justify-center border-b-4 border-purple-300 active:border-b-0 active:translate-y-1 transition-all shadow-sm">
                    {{ $letter }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- DAFTAR KATA -->
    <div class="flex-1 max-w-3xl mx-auto w-full px-4 pt-8 flex flex-col gap-8 page-transition relative z-0">
        @forelse($groupedWords as $letter => $items)
            <div id="huruf-{{ $letter }}" class="scroll-mt-48">
                <!-- PEMBATAS HURUF -->
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-purple-200 text-purple-800 rounded-2xl font-black text-2xl flex items-center justify-center border-4 border-white shadow-sm">{{ $letter }}</div>
                    <div class="h-1 flex-1 bg-slate-200 rounded-full"></div>
                </div>

                <!-- KOTAK KATA -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @foreach($items as $item)
                        <button onclick="bukaVideo('{{ $item->word }}', '{{ route('private.video', ['path' => $item->video_path]) }}')" 
                            class="bg-white p-4 rounded-2xl border-4 border-slate-200 shadow-sm text-center font-bold text-slate-600 hover:border-purple-400 hover:bg-purple-50 active:scale-95 transition-all flex flex-col items-center justify-center gap-2">
                            <span class="text-2xl">📹</span>
                            <span class="text-sm md:text-base">{{ $item->word }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="text-center py-20 text-slate-400 font-bold">
                <div class="text-6xl mb-4 opacity-50">📂</div>
                Belum ada kata di kamus ini.
            </div>
        @endforelse
    </div>

    @include('student.navbar-bawah')

    <!-- ========================================== -->
    <!-- POPUP VIDEO PLAYER (MODAL) -->
    <!-- ========================================== -->
    <div id="videoModal" class="fixed inset-0 bg-slate-900/80 z-[100] hidden flex-col items-center justify-center backdrop-blur-sm p-4 transition-opacity">
        <div class="w-full max-w-lg bg-white rounded-[2rem] border-8 border-purple-400 shadow-2xl overflow-hidden flex flex-col">
            
            <div class="bg-purple-100 px-6 py-4 flex justify-between items-center border-b-4 border-purple-200">
                <h3 id="modalWord" class="font-black text-xl md:text-2xl text-purple-800 uppercase tracking-wider"></h3>
                <button onclick="tutupVideo()" class="bg-red-500 text-white w-10 h-10 rounded-full font-black text-xl border-b-4 border-red-700 active:border-b-0 active:translate-y-1 transition-all">✖</button>
            </div>
            
            <!-- 👇 Tambahkan class select-none agar area hitam juga tidak bisa diblok/diklik 👇 -->
            <div class="bg-black relative w-full aspect-video flex items-center justify-center select-none">
                <!-- Efek loading saat video dimuat -->
                <div id="loadingVideo" class="absolute text-white font-bold animate-pulse z-0">Memuat video...</div>
                
                <!-- 👇 Hapus 'controls', Tambahkan loop, muted, playsinline, dan pointer-events-none 👇 -->
                <video id="videoPlayer" 
                       class="w-full h-full relative z-10 pointer-events-none object-contain" 
                       autoplay 
                       loop 
                       playsinline 
                       preload="auto">
                    <source src="" type="video/mp4">
                    Browser Anda tidak mendukung video HTML5.
                </video>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('videoModal');
        const player = document.getElementById('videoPlayer');
        const judul = document.getElementById('modalWord');
        const loading = document.getElementById('loadingVideo');

        function bukaVideo(kata, urlVideo) {
            judul.innerText = kata;
            player.src = urlVideo;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            player.oncanplay = () => loading.classList.add('hidden');
            player.play();
        }

        function tutupVideo() {
            player.pause();
            player.src = "";
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            loading.classList.remove('hidden');
        }

        // Tutup jika klik area hitam di luar video
        modal.addEventListener('click', function(e) {
            if (e.target === modal) tutupVideo();
        });
    </script>
</body>
</html>