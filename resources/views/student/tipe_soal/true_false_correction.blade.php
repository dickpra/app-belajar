@php
    $ansData = json_decode($existingAnswers[$question->id] ?? '{}', true);
    $pilihan = is_array($ansData) ? ($ansData['pilihan'] ?? '') : (is_string($ansData) ? $ansData : '');
    $perbaikan = is_array($ansData) ? ($ansData['perbaikan'] ?? '') : '';
@endphp
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <input type="radio" name="jawaban[{{ $question->id }}][pilihan]" id="benar-{{ $question->id }}" value="Benar" class="peer/benar sr-only" required {{ $pilihan === 'Benar' ? 'checked' : '' }} {{ $isCompleted ? 'disabled' : '' }}>
    <label for="benar-{{ $question->id }}" class="h-full flex flex-col items-center justify-center gap-3 p-4 bg-white border-[3px] border-slate-200 rounded-2xl cursor-pointer transition-all duration-200 hover:border-blue-300 hover:bg-blue-50 hover:-translate-y-1 peer-checked/benar:border-blue-500 peer-checked/benar:bg-blue-100 peer-checked/benar:shadow-[0_4px_0_#3b82f6] peer-checked/benar:-translate-y-1 peer-disabled/benar:opacity-75 peer-disabled/benar:cursor-not-allowed">
        <span class="text-xl font-black text-slate-600 peer-checked/benar:text-blue-800">✅ BENAR</span>
    </label>

    <input type="radio" name="jawaban[{{ $question->id }}][pilihan]" id="salah-{{ $question->id }}" value="Salah" class="peer/salah sr-only" required {{ $pilihan === 'Salah' ? 'checked' : '' }} {{ $isCompleted ? 'disabled' : '' }}>
    <label for="salah-{{ $question->id }}" class="h-full flex flex-col items-center justify-center gap-3 p-4 bg-white border-[3px] border-slate-200 rounded-2xl cursor-pointer transition-all duration-200 hover:border-red-300 hover:bg-red-50 hover:-translate-y-1 peer-checked/salah:border-red-500 peer-checked/salah:bg-red-100 peer-checked/salah:shadow-[0_4px_0_#ef4444] peer-checked/salah:-translate-y-1 peer-disabled/salah:opacity-75 peer-disabled/salah:cursor-not-allowed">
        <span class="text-xl font-black text-slate-600 peer-checked/salah:text-red-800">❌ SALAH</span>
    </label>

    <div class="col-span-1 md:col-span-2 hidden peer-checked/salah:block mt-2 bg-red-50 p-5 rounded-2xl border-[3px] border-red-200 shadow-inner">
        <label class="block text-sm font-black text-red-700 mb-2">Tuliskan Perbaikannya:</label>
        <input type="text" name="jawaban[{{ $question->id }}][perbaikan]" value="{{ htmlspecialchars($perbaikan) }}" {{ $isCompleted ? 'disabled' : '' }} placeholder="Ketik jawaban yang benar di sini..." class="w-full px-4 py-3 font-bold text-lg text-slate-700 bg-white border-[3px] border-slate-200 rounded-xl focus:border-red-500 focus:bg-red-50 outline-none transition-colors">
    </div>
</div>