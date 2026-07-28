<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $module->title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background-color: #F0F9FF; font-family: 'Nunito', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .bubbly-card { border-radius: 30px; box-shadow: 0 10px 30px -5px rgba(59, 130, 246, 0.15); }
    </style>
</head>
<body class="text-gray-800 antialiased min-h-screen pb-20">

    <!-- HEADER MODUL -->
    <div class="bg-blue-500 text-white p-8 rounded-b-[3rem] shadow-lg text-center mb-10 border-b-8 border-blue-600">
        <h1 class="text-4xl font-black tracking-tight mb-2">{{ $module->title }}</h1>
        <p class="text-blue-100 text-xl font-medium">{{ $module->description }}</p>
    </div>

    <div class="max-w-5xl mx-auto px-4 relative">
        
        <!-- WRAPPER AKTIVITAS (WIZARD) -->
        @foreach($module->activities as $index => $activity)
            <div id="activity-{{ $index }}" class="activity-section mb-12 bg-white p-8 bubbly-card border-4 border-blue-100" style="{{ $index == 0 ? '' : 'display: none;' }}">
                
                <!-- BADGE JUDUL AKTIVITAS -->
                <div class="text-center mb-8">
                    <span class="bg-yellow-400 px-8 py-3 rounded-full font-black text-yellow-900 text-2xl shadow-sm border-4 border-white inline-block transform -translate-y-14">
                        {{ $activity->title }}
                    </span>
                </div>

                <!-- KONTEKS GAMBAR/CERITA UTAMA AKTIVITAS -->
                @if($activity->image || $activity->description)
                    <div class="bg-yellow-50 rounded-3xl p-6 mb-10 border-2 border-yellow-200">
                        @if($activity->image)
                            <img src="{{ asset('storage/' . $activity->image) }}" alt="Gambar Aktivitas" class="max-h-72 mx-auto rounded-2xl shadow-md mb-6 object-contain bg-white p-2">
                        @endif
                        @if($activity->description)
                            <div class="prose prose-xl prose-blue text-gray-700 text-center mx-auto font-medium">
                                {!! $activity->description !!}
                            </div>
                        @endif
                    </div>
                @endif

                <!-- FORM JAWABAN AJAX -->
                <form id="form-activity-{{ $index }}">
                    <div class="space-y-10">
                        @foreach($activity->questions as $qIndex => $question)
                            
                            @php
                                // Penentuan Flexbox Tailwind berdasarkan layout dari Panel Admin
                                $flexClass = match($question->layout_position) {
                                    'image_right' => 'flex-col md:flex-row-reverse',
                                    'image_top' => 'flex-col',
                                    'image_bottom' => 'flex-col-reverse',
                                    default => 'flex-col md:flex-row', // image_left
                                };
                            @endphp

                            <div class="bg-blue-50/50 rounded-3xl p-6 md:p-8 border-2 border-blue-100 relative">
                                
                                <!-- NOMOR SOAL -->
                                <div class="absolute -top-5 -left-5 w-12 h-12 bg-blue-600 text-white rounded-full flex items-center justify-center font-black text-2xl shadow-lg border-4 border-white">
                                    {{ $qIndex + 1 }}
                                </div>

                                <div class="flex {{ $flexClass }} gap-8 items-center mt-2">
                                    
                                    <!-- GAMBAR KHUSUS SOAL -->
                                    @if($question->image)
                                        <div class="w-full md:w-1/2 flex justify-center bg-white p-4 rounded-2xl shadow-sm">
                                            <img src="{{ asset('storage/' . $question->image) }}" class="max-h-64 object-contain">
                                        </div>
                                    @endif

                                    <!-- TEKS SOAL & INPUT -->
                                    <div class="w-full {{ $question->image ? 'md:w-1/2' : 'w-full' }} flex flex-col gap-5">
                                        
                                        <div class="prose prose-xl font-bold text-gray-800">
                                            {!! $question->question_text !!}
                                        </div>

                                        <!-- INPUT BERDASARKAN TIPE -->
                                        <div class="mt-2">

                                            <!-- PILIHAN GANDA -->
                                            @if($question->answer_format === 'multiple_choice')
                                                <div class="flex flex-col gap-3">
                                                    @foreach($question->options as $opsi)
                                                        @php 
                                                            $isChecked = isset($existingAnswers[$question->id]) && $existingAnswers[$question->id] === $opsi['teks_pilihan'];
                                                        @endphp
                                                        <label class="flex items-center gap-4 p-4 bg-white border-4 border-gray-100 rounded-2xl cursor-pointer hover:border-blue-400 transition-all">
                                                            <input type="radio" name="jawaban[{{ $question->id }}]" value="{{ $opsi['teks_pilihan'] }}" class="w-6 h-6 text-blue-600 focus:ring-blue-500" required {{ $isChecked ? 'checked' : '' }}>
                                                            <span class="text-xl font-bold text-gray-700">{{ $opsi['teks_pilihan'] }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            
                                            <!-- INPUT ANGKA -->
                                            @elseif($question->answer_format === 'number_input')
                                                <input type="number" name="jawaban[{{ $question->id }}]" value="{{ $existingAnswers[$question->id] ?? '' }}" placeholder="Ketik angka..." required class="w-full text-center text-4xl px-6 py-6 border-4 border-gray-200 rounded-2xl focus:border-blue-500">
                                            
                                            <!-- INPUT TEKS -->
                                            @elseif($question->answer_format === 'text_input')
                                                <input type="text" name="jawaban[{{ $question->id }}]" value="{{ $existingAnswers[$question->id] ?? '' }}" placeholder="Ketik jawabanmu..." required class="w-full px-6 py-5 border-4 border-gray-200 rounded-2xl focus:border-blue-500">
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- TOMBOL NAVIGASI / SUBMIT WIZARD -->
                    <div class="flex justify-center gap-4 mt-12 pt-8 border-t-4 border-dashed border-gray-200">
                        <!-- Tombol Kembali (Hilang di halaman pertama) -->
                        @if($index > 0)
                            <button type="button" onclick="kembaliKeAktivitas({{ $index - 1 }})" class="bg-gray-400 hover:bg-gray-500 text-white font-black text-xl py-4 px-8 rounded-full shadow-[0_6px_0_#9ca3af] hover:shadow-[0_3px_0_#9ca3af] hover:translate-y-1 transition-all border-2 border-white">
                                ⬅️ Kembali
                            </button>
                        @endif

                        <!-- Tombol Lanjut / Selesai -->
                        @if($index < count($module->activities) - 1)
                            <button type="button" onclick="simpanDanLanjut({{ $index }}, {{ $module->id }})" class="bg-blue-500 hover:bg-blue-600 text-white font-black text-xl py-4 px-10 rounded-full shadow-[0_6px_0_#2563eb] hover:shadow-[0_3px_0_#2563eb] hover:translate-y-1 transition-all border-2 border-white">
                                Simpan & Lanjut ➔
                            </button>
                        @else
                            <button type="button" onclick="simpanDanSelesai({{ $index }}, {{ $module->id }})" class="bg-green-500 hover:bg-green-600 text-white font-black text-2xl py-4 px-12 rounded-full shadow-[0_6px_0_#16a34a] hover:shadow-[0_3px_0_#16a34a] hover:translate-y-1 transition-all border-2 border-white">
                                Selesai & Kumpulkan! 🎉
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        @endforeach
    </div>

    <!-- SCRIPT AJAX WIZARD -->
    <script>
    const moduleId = {{ $module->id }};
    const storageKey = `resume_module_${moduleId}_student_{{ session('student_id') }}`;
    
    // ==========================================
    // 1. LOGIKA RESUME HALAMAN TERAKHIR
    // ==========================================
    document.addEventListener("DOMContentLoaded", function() {
        let savedIndex = localStorage.getItem(storageKey);
        
        if (savedIndex !== null) {
            savedIndex = parseInt(savedIndex);
            // Sembunyikan aktivitas pertama (default), tampilkan aktivitas terakhir
            document.querySelectorAll('.activity-section').forEach(el => el.style.display = 'none');
            const targetActivity = document.getElementById(`activity-${savedIndex}`);
            if(targetActivity) targetActivity.style.display = 'block';
        }
    });

    // ==========================================
    // 2. PERINGATAN KELUAR HALAMAN
    // ==========================================
    let isSubmitting = false; // Flag agar tidak muncul peringatan jika selesai normal

    window.addEventListener('beforeunload', function (e) {
        if (!isSubmitting) {
            // Browser modern akan menampilkan pesan bawaannya sendiri ("Changes you made may not be saved")
            e.preventDefault();
            e.returnValue = ''; 
        }
    });

    // ==========================================
    // 3. FUNGSI SIMPAN & LANJUT
    // ==========================================
    function ambilToken() { return document.querySelector('meta[name="csrf-token"]').content; }
    function validasiForm(formElement) { return formElement.reportValidity(); }

    function simpanDanLanjut(currentIndex, moduleId) {
        const form = document.getElementById(`form-activity-${currentIndex}`);
        if (!validasiForm(form)) return;

        const formData = new FormData(form);
        const btn = form.querySelector('button');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = "Menyimpan... ⏳";
        btn.disabled = true;

        fetch(`/ruang-belajar/modul/${moduleId}/simpan-aktivitas`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': ambilToken(), 'Accept': 'application/json' },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                const nextIndex = currentIndex + 1;
                
                // Simpan posisi baru ke LocalStorage
                localStorage.setItem(storageKey, nextIndex);

                // Animasi perpindahan tab
                document.getElementById(`activity-${currentIndex}`).style.display = 'none';
                document.getElementById(`activity-${nextIndex}`).style.display = 'block';
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    // ==========================================
    // 4. FUNGSI SELESAI
    // ==========================================
    function simpanDanSelesai(currentIndex, moduleId) {
        const form = document.getElementById(`form-activity-${currentIndex}`);
        if (!validasiForm(form)) return;

        isSubmitting = true; // Matikan peringatan keluar halaman
        const formData = new FormData(form);
        const btn = form.querySelector('button');
        btn.innerHTML = "Mengirim... 🚀";
        btn.disabled = true;

        fetch(`/ruang-belajar/modul/${moduleId}/simpan-aktivitas`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': ambilToken(), 'Accept': 'application/json' },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === 'success') {
                // Hapus memori posisi terakhir agar jika mengulang, mulai dari awal lagi
                localStorage.removeItem(storageKey);
                window.location.href = "{{ route('student.dashboard') }}?status=hore";
            }
        });
    }

    function kembaliKeAktivitas(targetIndex) {
        // Sembunyikan semua tab
        document.querySelectorAll('.activity-section').forEach(el => el.style.display = 'none');
        
        // Tampilkan tab target
        const targetActivity = document.getElementById(`activity-${targetIndex}`);
        if(targetActivity) targetActivity.style.display = 'block';
        
        // Scroll ke atas dengan halus
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
</script>
</body>
</html>