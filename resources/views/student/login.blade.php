<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Ruang Belajar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #E0F2FE; /* Sky blue muda */ font-family: 'Comic Sans MS', 'Chalkboard SE', sans-serif; }
        .bubbly-shape { border-radius: 40px 10px 40px 10px; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Ornamen Latar Belakang (Lingkaran hiasan) -->
    <div class="absolute top-10 left-10 w-32 h-32 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse"></div>
    <div class="absolute bottom-10 right-10 w-40 h-40 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse"></div>

    <div class="max-w-md w-full bg-white p-8 bubbly-shape shadow-2xl relative z-10 border-4 border-blue-400">
        
        <div class="text-center mb-8">
            <div class="inline-block bg-blue-500 p-4 rounded-full mb-4 shadow-lg transform -translate-y-12 border-4 border-white">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-extrabold text-blue-600 tracking-tight">Halo, Teman! 👋</h1>
            <p class="text-gray-500 mt-2 font-medium">Pilih namamu dan masukkan PIN rahasiamu untuk mulai belajar.</p>
        </div>

        @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-xl mb-6 shadow-sm">
                <p class="font-bold">Ups, ada yang salah!</p>
                <p>{{ $errors->first() }}</p>
            </div>
        @endif

        <form action="{{ route('student.login.process') }}" method="POST" class="space-y-6">
            @csrf
            
            <div>
                <label class="block text-lg font-bold text-gray-700 mb-2">Namamu Siapa?</label>
                <select name="student_id" required class="w-full px-5 py-4 bg-gray-50 border-2 border-blue-200 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 text-lg font-bold text-gray-700 transition appearance-none cursor-pointer">
                    <option value="" disabled selected>-- Klik untuk mencari namamu --</option>
                    @foreach($students ?? [] as $student)
                        <option value="{{ $student->id }}">{{ $student->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-lg font-bold text-gray-700 mb-2">PIN Rahasia (4 Angka)</label>
                <!-- Menggunakan input password dengan pattern angka -->
                <input type="password" name="pin" required maxlength="4" pattern="\d{4}" placeholder="* * * *" class="w-full px-5 py-4 bg-gray-50 border-2 border-blue-200 rounded-2xl focus:ring-4 focus:ring-blue-100 focus:border-blue-500 text-center text-3xl tracking-[1em] font-black text-gray-800 transition">
            </div>

            <button type="submit" class="w-full bg-yellow-400 hover:bg-yellow-500 text-yellow-900 font-extrabold text-xl py-4 px-6 rounded-2xl shadow-[0_8px_0_#ca8a04] hover:shadow-[0_4px_0_#ca8a04] hover:translate-y-1 transition-all">
                Mulai Belajar! 🚀
            </button>
        </form>

    </div>
</body>
</html>