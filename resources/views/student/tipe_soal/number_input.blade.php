@php 
    $rawJawaban = (string) ($existingAnswers[$question->id] ?? '');
    $jawabanLama = preg_replace('/[^0-9\.\-]/', '', $rawJawaban);
@endphp
<input type="number" name="jawaban[{{ $question->id }}]" value="{{ $jawabanLama }}" placeholder="Ketik angka..." required class="w-full max-w-sm text-center text-4xl font-black text-blue-600 px-6 py-6 bg-slate-50 border-[3px] border-slate-200 rounded-2xl focus:border-blue-500 focus:bg-blue-100 shadow-inner outline-none transition-colors placeholder:text-slate-300" {{ $isCompleted ? 'disabled' : '' }}>