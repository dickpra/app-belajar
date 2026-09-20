@php
    $ansData = json_decode($existingAnswers[$question->id] ?? '{}', true);
    $pilihan = is_array($ansData) ? ($ansData['pilihan'] ?? '') : (is_string($ansData) ? $ansData : '');
    $perbaikan = is_array($ansData) ? ($ansData['perbaikan'] ?? '') : '';
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 relative">
    <!-- Input tersembunyi yang menyimpan nilai pilihan -->
    <input type="hidden" id="tf-val-{{ $question->id }}" name="jawaban[{{ $question->id }}][pilihan]" value="{{ $pilihan }}">

    <!-- Tombol Benar -->
    <button type="button" 
        onclick="pilihOpsiTF({{ $question->id }}, 'Benar')"
        id="btn-tf-benar-{{ $question->id }}"
        class="w-full flex items-center justify-center p-5 rounded-2xl border-4 transition-all duration-150 font-black text-xl cursor-pointer {{ $pilihan === 'Benar' ? 'bg-blue-100 border-blue-500 text-blue-800 shadow-[0_5px_0_#3b82f6] -translate-y-1' : 'bg-white border-slate-200 text-slate-600 hover:bg-blue-50 hover:border-blue-300' }}">
        <span>✅ BENAR</span>
    </button>

    <!-- Tombol Salah -->
    <button type="button" 
        onclick="pilihOpsiTF({{ $question->id }}, 'Salah')"
        id="btn-tf-salah-{{ $question->id }}"
        class="w-full flex items-center justify-center p-5 rounded-2xl border-4 transition-all duration-150 font-black text-xl cursor-pointer {{ $pilihan === 'Salah' ? 'bg-red-100 border-red-500 text-red-800 shadow-[0_5px_0_#ef4444] -translate-y-1' : 'bg-white border-slate-200 text-slate-600 hover:bg-red-50 hover:border-red-300' }}">
        <span>❌ SALAH</span>
    </button>

    <!-- Kotak Input Perbaikan (Muncul saat memilih Salah) -->
    <div id="perbaikan-box-{{ $question->id }}" class="col-span-1 md:col-span-2 {{ $pilihan === 'Salah' ? 'block' : 'hidden' }} mt-2 bg-red-50 p-5 rounded-2xl border-4 border-red-200">
        <label class="block text-sm font-black text-red-700 mb-2">Tuliskan Perbaikannya:</label>
        <input type="text" 
            name="jawaban[{{ $question->id }}][perbaikan]" 
            value="{{ htmlspecialchars($perbaikan) }}" 
            placeholder="Ketik jawaban yang benar di sini..." 
            class="w-full px-4 py-3 font-bold text-lg text-slate-700 bg-white border-2 border-slate-300 rounded-xl focus:border-red-500 outline-none">
    </div>
</div>

<script>
    if (typeof pilihOpsiTF !== 'function') {
        window.pilihOpsiTF = function(id, val) {
            const hiddenInput = document.getElementById('tf-val-' + id);
            if (hiddenInput) hiddenInput.value = val;

            const btnBenar = document.getElementById('btn-tf-benar-' + id);
            const btnSalah = document.getElementById('btn-tf-salah-' + id);
            const box = document.getElementById('perbaikan-box-' + id);

            // Reset tampilan tombol
            btnBenar.className = "w-full flex items-center justify-center p-5 rounded-2xl border-4 transition-all duration-150 font-black text-xl cursor-pointer bg-white border-slate-200 text-slate-600 hover:bg-blue-50 hover:border-blue-300";
            btnSalah.className = "w-full flex items-center justify-center p-5 rounded-2xl border-4 transition-all duration-150 font-black text-xl cursor-pointer bg-white border-slate-200 text-slate-600 hover:bg-red-50 hover:border-red-300";

            if (val === 'Benar') {
                btnBenar.className = "w-full flex items-center justify-center p-5 rounded-2xl border-4 transition-all duration-150 font-black text-xl cursor-pointer bg-blue-100 border-blue-500 text-blue-800 shadow-[0_5px_0_#3b82f6] -translate-y-1";
                if (box) box.classList.add('hidden');
            } else if (val === 'Salah') {
                btnSalah.className = "w-full flex items-center justify-center p-5 rounded-2xl border-4 transition-all duration-150 font-black text-xl cursor-pointer bg-red-100 border-red-500 text-red-800 shadow-[0_5px_0_#ef4444] -translate-y-1";
                if (box) box.classList.remove('hidden');
            }
        };
    }
</script>