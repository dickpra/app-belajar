@php
    $isRow = in_array($question->layout_position, ['image_left', 'image_right']);
    $flexClass = match($question->layout_position) {
        'image_right' => 'flex-col md:flex-row-reverse',
        'image_top' => 'flex-col',
        'image_bottom' => 'flex-col-reverse',
        default => 'flex-col md:flex-row', // image_left
    };

    $imgWidth = $isRow ? 'w-full md:w-5/12' : 'w-full md:w-3/4 mx-auto'; 
    $textWidth = ($isRow && !empty($question->image)) ? 'w-full md:w-7/12' : 'w-full';
@endphp

<div class="bg-white rounded-3xl p-6 md:p-8 border-[3px] border-slate-200 relative pt-12 shadow-sm">
    <div class="absolute -top-6 left-6 w-14 h-14 bg-blue-500 text-white rounded-2xl flex items-center justify-center font-black text-2xl shadow-[0_4px_0_#1d4ed8] border-4 border-white transform -rotate-6">
        {{ $qIndex + 1 }}
    </div>

    <div class="flex {{ $flexClass }} gap-6 md:gap-8 items-start">
        
        @if(!empty($question->image))
            <div class="{{ $imgWidth }} flex justify-center bg-slate-50 p-3 rounded-2xl border-[3px] border-slate-100 shadow-inner">
                <img src="{{ route('private.image', ['path' => $question->image]) }}" class="max-h-72 object-contain rounded-xl w-full">
            </div>
        @endif

        <div class="{{ $textWidth }} flex flex-col gap-6 w-full">
            
            <div class="prose prose-blue prose-lg font-bold text-slate-800 w-full max-w-full leading-loose">
                @php
                    $teksSoal = renderPrivateImages($question->question_text);
                    if($question->answer_format === 'complex_fill') {
                        $teksSoal = preg_replace('/_{3,}|\.{3,}/', '<input type="text" name="jawaban['.$question->id.'][]" class="inline-block w-24 md:w-32 mx-1 px-2 py-1 bg-blue-50 border-b-4 border-blue-400 text-blue-800 font-black text-center outline-none focus:border-blue-600 focus:bg-blue-200 transition-colors rounded-t-md shadow-inner" required placeholder="...">', $teksSoal);
                    }
                @endphp
                {!! $teksSoal !!}
            </div>

            <div class="mt-2">
                {{-- TIPE A: PILIHAN GANDA --}}
                @if($question->answer_format === 'multiple_choice')
                    <div class="grid grid-cols-1 {{ count($question->options ?? []) > 2 ? 'md:grid-cols-2' : 'md:grid-cols-1' }} gap-4">
                        @foreach($question->options as $opsi)
                            @php 
                                $nilaiJawaban = !empty($opsi['teks_pilihan']) ? $opsi['teks_pilihan'] : ($opsi['image_pilihan'] ?? 'gambar');
                            @endphp
                            <label class="relative cursor-pointer group h-full">
                                <input type="radio" name="jawaban[{{ $question->id }}]" value="{{ $nilaiJawaban }}" class="peer sr-only" required {{ $isCompleted ? 'disabled' : '' }}>
                                <div class="h-full flex flex-col items-center justify-center gap-3 p-4 bg-white border-[3px] border-slate-200 rounded-2xl transition-all duration-200 ease-in-out hover:border-blue-300 hover:bg-blue-50 hover:-translate-y-1 peer-checked:border-blue-500 peer-checked:bg-blue-100 peer-checked:shadow-[0_4px_0_#3b82f6] peer-checked:-translate-y-1">
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

                {{-- TIPE BARU: BENAR/SALAH + KOTAK PERBAIKAN --}}
                @elseif($question->answer_format === 'true_false_correction')
                    @php
                        // Menarik data jawaban lama untuk fitur Resume (jika murid refresh browser)
                        $ansData = json_decode($existingAnswers[$question->id] ?? '{}', true);
                        $pilihan = is_array($ansData) ? ($ansData['pilihan'] ?? '') : (is_string($ansData) ? $ansData : '');
                        $perbaikan = is_array($ansData) ? ($ansData['perbaikan'] ?? '') : '';
                    @endphp
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="radio" name="jawaban[{{ $question->id }}][pilihan]" id="benar-{{ $question->id }}" value="Benar" class="peer/benar sr-only" required {{ $pilihan === 'Benar' ? 'checked' : '' }}>
                        <label for="benar-{{ $question->id }}" class="h-full flex flex-col items-center justify-center gap-3 p-4 bg-white border-[3px] border-slate-200 rounded-2xl cursor-pointer transition-all duration-200 hover:border-blue-300 hover:bg-blue-50 hover:-translate-y-1 peer-checked/benar:border-blue-500 peer-checked/benar:bg-blue-100 peer-checked/benar:shadow-[0_4px_0_#3b82f6] peer-checked/benar:-translate-y-1">
                            <span class="text-xl font-black text-slate-600 peer-checked/benar:text-blue-800">✅ BENAR</span>
                        </label>

                        <input type="radio" name="jawaban[{{ $question->id }}][pilihan]" id="salah-{{ $question->id }}" value="Salah" class="peer/salah sr-only" required {{ $pilihan === 'Salah' ? 'checked' : '' }}>
                        <label for="salah-{{ $question->id }}" class="h-full flex flex-col items-center justify-center gap-3 p-4 bg-white border-[3px] border-slate-200 rounded-2xl cursor-pointer transition-all duration-200 hover:border-red-300 hover:bg-red-50 hover:-translate-y-1 peer-checked/salah:border-red-500 peer-checked/salah:bg-red-100 peer-checked/salah:shadow-[0_4px_0_#ef4444] peer-checked/salah:-translate-y-1">
                            <span class="text-xl font-black text-slate-600 peer-checked/salah:text-red-800">❌ SALAH</span>
                        </label>

                        <div class="col-span-1 md:col-span-2 hidden peer-checked/salah:block mt-2 bg-red-50 p-5 rounded-2xl border-[3px] border-red-200 shadow-inner">
                            <label class="block text-sm font-black text-red-700 mb-2">Tuliskan Perbaikannya:</label>
                            <input type="text" name="jawaban[{{ $question->id }}][perbaikan]" value="{{ $perbaikan }}" placeholder="Ketik jawaban yang benar di sini..." class="w-full px-4 py-3 font-bold text-lg text-slate-700 bg-white border-[3px] border-slate-200 rounded-xl focus:border-red-500 focus:bg-red-50 outline-none transition-colors">
                        </div>
                    </div>

                {{-- TIPE B: MENJODOHKAN (TARIK GARIS SVG INTERAKTIF) --}}
                @elseif($question->answer_format === 'matching')
                    <div class="text-sm font-black text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                        <span>👆</span> Klik kotak kiri, lalu pasangannya!
                    </div>
                    <div class="matching-wrapper relative bg-slate-50 p-4 md:p-8 rounded-3xl border-[3px] border-slate-200" id="match-wrap-{{ $question->id }}">
                        <svg class="absolute inset-0 w-full h-full pointer-events-none z-10" id="svg-canvas-{{ $question->id }}"></svg>
                        <div class="flex justify-between relative z-20 gap-8 md:gap-16">
                            <div class="w-1/2 flex flex-col gap-4 relative">
                                @foreach($question->options as $opsi)
                                    @php $nilaiKiri = !empty($opsi['teks_pilihan']) ? $opsi['teks_pilihan'] : ($opsi['image_pilihan'] ?? 'kiri-'.$loop->index); @endphp
                                    <button type="button" onclick="pilihKiri(this, {{ $question->id }})" data-nilai="{{ $nilaiKiri }}" class="btn-kiri-{{ $question->id }} p-4 bg-white border-[3px] border-slate-200 rounded-xl font-bold text-slate-700 hover:border-blue-400 hover:bg-blue-50 transition-all text-center relative shadow-sm z-20 flex flex-col items-center justify-center gap-3">
                                        @if(!empty($opsi['image_pilihan']))
                                            <img src="{{ route('private.image', ['path' => $opsi['image_pilihan']]) }}" class="max-h-24 object-contain rounded-lg pointer-events-none">
                                        @endif
                                        @if(!empty($opsi['teks_pilihan']))
                                            <span class="text-sm md:text-base pointer-events-none">{{ $opsi['teks_pilihan'] }}</span>
                                        @endif
                                        <div class="absolute top-1/2 -right-3 md:-right-4 transform -translate-y-1/2 w-5 h-5 md:w-6 md:h-6 bg-slate-200 rounded-full border-4 border-white shadow-sm konektor-kiri"></div>
                                    </button>
                                @endforeach
                            </div>
                            <div class="w-1/2 flex flex-col gap-4 relative">
                                @php $opsiKanan = collect($question->options)->shuffle(); @endphp
                                @foreach($opsiKanan as $kanan)
                                    @php $nilaiKanan = !empty($kanan['matching_right']) ? $kanan['matching_right'] : ($kanan['image_matching_right'] ?? 'kanan-'.$loop->index); @endphp
                                    <button type="button" onclick="pilihKanan(this, {{ $question->id }})" data-nilai="{{ $nilaiKanan }}" class="btn-kanan-{{ $question->id }} p-4 bg-white border-[3px] border-slate-200 rounded-xl font-bold text-slate-700 hover:border-blue-400 hover:bg-blue-50 transition-all text-center relative shadow-sm z-20 flex flex-col items-center justify-center gap-3">
                                        <div class="absolute top-1/2 -left-3 md:-left-4 transform -translate-y-1/2 w-5 h-5 md:w-6 md:h-6 bg-slate-200 rounded-full border-4 border-white shadow-sm konektor-kanan"></div>
                                        @if(!empty($kanan['image_matching_right']))
                                            <img src="{{ route('private.image', ['path' => $kanan['image_matching_right']]) }}" class="max-h-24 object-contain rounded-lg pointer-events-none">
                                        @endif
                                        @if(!empty($kanan['matching_right']))
                                            <span class="text-sm md:text-base pointer-events-none">{{ $kanan['matching_right'] }}</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div id="hidden-inputs-{{ $question->id }}"></div>
                    </div>

                {{-- TIPE C: INPUT ANGKA BESAR --}}
                @elseif($question->answer_format === 'number_input')
                    <input type="number" name="jawaban[{{ $question->id }}]" {{ $isCompleted ? 'disabled' : '' }} placeholder="Ketik angka..." required class="w-full max-w-sm text-center text-4xl font-black text-blue-600 px-6 py-6 bg-slate-50 border-[3px] border-slate-200 rounded-2xl focus:border-blue-500 focus:bg-blue-100 shadow-inner outline-none transition-colors placeholder:text-slate-300">
                
                {{-- TIPE D: INPUT TEKS --}}
                @elseif($question->answer_format === 'text_input')
                    <textarea name="jawaban[{{ $question->id }}]" {{ $isCompleted ? 'disabled' : '' }} rows="2" placeholder="Ketik jawabanmu..." required class="w-full px-6 py-4 font-bold text-xl text-slate-700 bg-slate-50 border-[3px] border-slate-200 rounded-2xl focus:border-blue-500 focus:bg-blue-50 outline-none shadow-inner transition-colors placeholder:text-slate-300"></textarea>
                @endif
            </div>
        </div>
    </div>
</div>