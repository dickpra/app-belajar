<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <!-- Mencegah layar nge-zoom saat input diklik di HP -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Masuk ke Ruang Belajar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #E0F2FE; /* Langit biru muda */
            background-image: radial-gradient(#bae6fd 2px, transparent 2px);
            background-size: 30px 30px; /* Motif polkadot */
        }
        
        /* Tombol 3D ala Game */
        .btn-3d { transition: all 0.1s; }
        .btn-3d:active { transform: translateY(8px); box-shadow: 0 0px 0px transparent !important; }

        /* Animasi Latar Blob yang halus */
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
        }
        .floating-blob { animation: float 6s ease-in-out infinite; }
        .floating-blob-delay { animation: float 6s ease-in-out infinite; animation-delay: 3s; }

        /* Menyembunyikan panah default bawaan select browser */
        select { -webkit-appearance: none; -moz-appearance: none; appearance: none; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Ornamen Latar Belakang (Lebih interaktif) -->
    <div class="absolute top-10 left-4 w-32 h-32 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 floating-blob z-0"></div>
    <div class="absolute bottom-10 right-4 w-40 h-40 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 floating-blob-delay z-0"></div>
    <div class="absolute top-1/2 -right-10 w-28 h-28 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-60 floating-blob z-0"></div>

    <!-- Kartu Login (Margin mx-2 agar tidak mentok di layar HP kecil) -->
    <div class="w-full max-w-sm bg-white p-6 md:p-8 rounded-[2.5rem] shadow-[0_15px_30px_rgba(0,0,0,0.1),_0_12px_0_#cbd5e1] relative z-10 border-[6px] border-white mx-2 mt-12">
        
        <!-- Avatar Lucu Menembus Batas Atas Kartu -->
        <div class="absolute -top-16 left-1/2 transform -translate-x-1/2">
            <div class="bg-sky-400 w-28 h-28 rounded-[2rem] flex items-center justify-center shadow-[0_8px_0_#0284c7] border-4 border-white text-6xl rotate-3 hover:rotate-6 transition-transform">
                🎒
            </div>
        </div>

        <div class="text-center mt-10 mb-8">
            <h1 class="text-3xl md:text-4xl font-black text-slate-700 tracking-tight">Halo, Teman! 👋</h1>
            <p class="text-slate-500 mt-2 font-bold text-sm md:text-base">Pilih namamu dan masukkan PIN rahasiamu ya.</p>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border-4 border-red-300 text-red-700 p-4 rounded-2xl mb-6 shadow-sm flex items-center gap-3">
                <span class="text-3xl drop-shadow-sm">🥺</span>
                <div>
                    <p class="font-black text-sm uppercase tracking-wide">Ups, ada yang salah!</p>
                    <p class="text-xs md:text-sm font-bold">{{ $errors->first() }}</p>
                </div>
            </div>
        @endif

        <form action="{{ route('student.login.process') }}" method="POST" class="space-y-6">
            @csrf
            
            <!-- Input Nama -->
            <div>
                <label class="block text-base font-black text-slate-700 mb-2 ml-2">Namamu Siapa?</label>
                <div class="relative">
                    <select name="student_id" required class="w-full px-5 py-4 bg-slate-50 border-4 border-slate-200 rounded-2xl focus:ring-0 focus:outline-none focus:border-sky-400 text-base md:text-lg font-bold text-slate-700 transition cursor-pointer shadow-inner">
                        <option value="" disabled selected>👉 Cari namamu di sini...</option>
                        @foreach($students ?? [] as $student)
                            <option value="{{ $student->id }}">{{ $student->name }}</option>
                        @endforeach
                    </select>
                    <!-- Custom Panah Bawah -->
                    <div class="absolute inset-y-0 right-0 flex items-center pr-5 pointer-events-none">
                        <span class="text-2xl drop-shadow-sm">🔽</span>
                    </div>
                </div>
            </div>

            <!-- Input PIN -->
            <div>
                <label class="block text-base font-black text-slate-700 mb-2 ml-2">PIN Rahasia (4 Angka)</label>
                <!-- inputmode="numeric" SANGAT PENTING untuk Mobile -->
                <input type="password" name="pin" required maxlength="4" 
                    inputmode="numeric" pattern="[0-9]*" 
                    placeholder="••••" 
                    class="w-full px-5 py-4 bg-slate-50 border-4 border-slate-200 rounded-2xl focus:ring-0 focus:outline-none focus:border-sky-400 text-center text-4xl tracking-[0.5em] font-black text-slate-800 transition shadow-inner">
            </div>

            <div class="pt-2">
                <button type="submit" class="btn-3d w-full bg-green-400 hover:bg-green-500 text-white font-black text-2xl py-4 px-6 rounded-2xl shadow-[0_8px_0_#16a34a] border-4 border-white uppercase tracking-wide flex justify-center items-center gap-2">
                    Mulai Belajar! 🚀
                </button>
            </div>
        </form>

    </div>
</body>
</html>