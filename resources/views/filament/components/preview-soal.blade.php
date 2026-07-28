@php
    // Menentukan class Flexbox berdasarkan pilihan layout guru
    $flexClass = match($layout) {
        'image_right' => 'flex-row-reverse',
        'image_top' => 'flex-col',
        'image_bottom' => 'flex-col-reverse',
        default => 'flex-row', // image_left
    };
@endphp

<div class="border-2 border-dashed border-gray-300 p-4 rounded-lg bg-gray-50 dark:bg-gray-900">
    <div class="mb-2 text-sm font-bold text-gray-500 uppercase">Pratinjau Layar Murid</div>
    
    <div class="flex {{ $flexClass }} gap-6 items-center justify-center">
        
        {{-- BLOK GAMBAR --}}
        <div class="w-1/2 flex justify-center">
            @if($gambar)
                <div class="w-full h-32 bg-blue-100 border border-blue-300 rounded flex items-center justify-center text-blue-500 text-sm font-medium">
                    (Gambar Benda Akan Tampil Di Sini)
                </div>
            @else
                <div class="w-full h-32 bg-gray-200 border border-gray-300 rounded flex items-center justify-center text-gray-400 text-sm border-dashed">
                    Tidak ada gambar
                </div>
            @endif
        </div>

        {{-- BLOK TEKS & JAWABAN --}}
        <div class="w-1/2 flex flex-col gap-4">
            {{-- Render teks pertanyaan (mendukung format Rich Text) --}}
            <div class="prose dark:prose-invert max-w-none">
                {!! $teks ?: '<span class="text-gray-400 italic">Teks pertanyaan kosong...</span>' !!}
            </div>

            {{-- Simulasi Tombol / Input Jawaban --}}
            <div class="mt-4">
                @if($tipe === 'multiple_choice')
                    <div class="flex flex-col gap-2">
                        @forelse($opsi ?? [] as $opsiItem)
                            <div class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm text-center">
                                {{ $opsiItem['teks_pilihan'] ?? 'Opsi...' }}
                            </div>
                        @empty
                            <div class="text-xs text-gray-400">Belum ada opsi jawaban dibuat.</div>
                        @endforelse
                    </div>
                @elseif($tipe === 'number_input')
                    <input type="number" placeholder="Ketik angka di sini..." class="w-full px-4 py-2 border border-gray-300 rounded-md" disabled>
                @elseif($tipe === 'text_input')
                    <input type="text" placeholder="Ketik jawaban di sini..." class="w-full px-4 py-2 border border-gray-300 rounded-md" disabled>
                @endif
            </div>
        </div>

    </div>
</div>