@php 
    $rawJawaban = (string) ($existingAnswers[$question->id] ?? '');
    $jawabanLama = trim(str_replace(['"', '\\'], '', $rawJawaban)); 
@endphp
<textarea name="jawaban[{{ $question->id }}]" rows="2" placeholder="Ketik jawabanmu..." required class="w-full px-6 py-4 font-bold text-xl text-slate-700 bg-slate-50 border-[3px] border-slate-200 rounded-2xl focus:border-blue-500 focus:bg-blue-50 outline-none shadow-inner transition-colors placeholder:text-slate-300" {{ $isCompleted ? 'disabled' : '' }}>{{ htmlspecialchars($jawabanLama) }}</textarea>