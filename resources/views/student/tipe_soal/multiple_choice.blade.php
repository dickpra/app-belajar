@php 
    $rawJawaban = $existingAnswers[$question->id] ?? '';
    $jawabanLama = is_string($rawJawaban) ? trim(str_replace(['"', "'", '\\'], '', $rawJawaban)) : $rawJawaban; 
@endphp
<div class="grid grid-cols-1 {{ count($question->options ?? []) > 2 ? 'md:grid-cols-2' : 'md:grid-cols-1' }} gap-4">
    @foreach($question->options as $opsi)
        @php 
            $nilaiJawaban = !empty($opsi['teks_pilihan']) ? $opsi['teks_pilihan'] : ($opsi['image_pilihan'] ?? 'gambar');
        @endphp
        <label class="relative cursor-pointer group h-full">
            <input type="radio" name="jawaban[{{ $question->id }}]" value="{{ $nilaiJawaban }}" class="peer sr-only" required {{ ($jawabanLama === $nilaiJawaban) ? 'checked' : '' }} {{ $isCompleted ? 'disabled' : '' }}>
            <div class="h-full flex flex-col items-center justify-center gap-3 p-4 bg-white border-[3px] border-slate-200 rounded-2xl transition-all duration-200 ease-in-out hover:border-blue-300 hover:bg-blue-50 hover:-translate-y-1 peer-checked:border-blue-500 peer-checked:bg-blue-100 peer-checked:shadow-[0_4px_0_#3b82f6] peer-checked:-translate-y-1 opacity-100 peer-disabled:opacity-75 peer-disabled:cursor-not-allowed">
                @if(!empty($opsi['image_pilihan']))
                    <img src="{{ route('private.image', ['path' => $opsi['image_pilihan']]) }}" class="max-h-32 object-contain rounded-lg pointer-events-none">
                @endif
                @if(!empty($opsi['teks_pilihan']))
                    <span class="text-lg font-black text-slate-600 peer-checked:text-blue-800 text-center">{{ $opsi['teks_pilihan'] }}</span>
                @endif
                <div class="absolute top-3 right-3 w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center opacity-0 scale-50 transition-all peer-checked:opacity-100 peer-checked:scale-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
            </div>
        </label>
    @endforeach
</div>