<x-filament-panels::page>
    
    <!-- ALAT DIKTE SUARA (DITENAGAI OLEH ALPINE.JS) -->
    <div x-data="{
            isRecording: false,
            statusText: 'Siap digunakan',
            textOutput: '',
            recognition: null,
            finalText: '',
            
            initApp() {
                const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                if (!SpeechRecognition) {
                    this.statusText = 'Browser ini tidak mendukung mikrofon (Gunakan Chrome/Edge).';
                    return;
                }
                
                this.recognition = new SpeechRecognition();
                this.recognition.lang = 'id-ID'; 
                this.recognition.continuous = true;
                this.recognition.interimResults = true;

                this.recognition.onstart = () => {
                    this.isRecording = true;
                    this.statusText = 'Sedang mendengarkan... (Silakan bicara)';
                };

                this.recognition.onresult = (event) => {
                    let interim = '';
                    for (let i = event.resultIndex; i < event.results.length; i++) {
                        if (event.results[i].isFinal) {
                            this.finalText += event.results[i][0].transcript.trim() + ' ';
                        } else {
                            interim += event.results[i][0].transcript;
                        }
                    }
                    this.textOutput = this.finalText + interim;
                };

                this.recognition.onerror = (event) => {
                    this.isRecording = false;
                    this.statusText = 'Terjadi masalah: ' + event.error;
                };

                this.recognition.onend = () => {
                    this.isRecording = false;
                    if(this.statusText.includes('Sedang mendengarkan')) {
                        this.statusText = 'Selesai merekam';
                    }
                };
            },
            
            startDictation() {
                if (!this.recognition) return;
                this.finalText = this.textOutput.trim() ? this.textOutput.trim() + ' ' : '';
                try { this.recognition.start(); } catch(e) {}
            },
            
            stopDictation() {
                if (this.recognition) this.recognition.stop();
            },
            
            copyText() {
                navigator.clipboard.writeText(this.textOutput).then(() => {
                    this.statusText = '✅ Teks berhasil disalin!';
                    setTimeout(() => { if(!this.isRecording) this.statusText = 'Siap digunakan'; }, 3000);
                });
            }
        }" 
        x-init="initApp()"
        class="bg-white p-6 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">

        <h2 class="text-xl font-bold mb-2">🎙️ Asisten Dikte Suara (GuruVoice)</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Malas mengetik? Klik tombol di bawah dan mulailah berbicara. Teks akan muncul otomatis.</p>

        <!-- TOMBOL DIKTE -->
        <div class="flex gap-3 mb-4">
            <x-filament::button type="button" x-on:click="startDictation()" color="info" icon="heroicon-m-microphone" x-bind:disabled="isRecording">
                Mulai Bicara
            </x-filament::button>
            
            <x-filament::button type="button" x-on:click="stopDictation()" color="danger" icon="heroicon-m-stop-circle" x-bind:disabled="!isRecording">
                Berhenti
            </x-filament::button>
        </div>

        <!-- STATUS -->
        <div class="flex items-center gap-2 mb-2 text-sm text-gray-500 dark:text-gray-400 font-medium">
            <span x-bind:class="isRecording ? 'bg-red-500 animate-pulse' : 'bg-gray-400'" style="width: 12px; height: 12px; border-radius: 50%; transition: all 0.3s;"></span>
            <span x-text="statusText"></span>
        </div>

        <!-- AREA TEKS -->
        <textarea x-model="textOutput" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-4 text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 min-h-[120px] mb-3 focus:ring-2 focus:ring-primary-500 focus:outline-none" placeholder="Hasil ucapan akan muncul di sini..."></textarea>

        <!-- TOMBOL SALIN -->
        <x-filament::button type="button" x-on:click="copyText()" color="gray" icon="heroicon-m-clipboard-document">
            Salin Teks
        </x-filament::button>
    </div>

    <!-- FORM PENYIMPANAN FILAMENT -->
    <form wire:submit="simpan" class="mt-4">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit" color="primary" size="lg" icon="heroicon-m-check-circle">
                💾 Simpan Ucapan Guru
            </x-filament::button>
        </div>
    </form>

</x-filament-panels::page>