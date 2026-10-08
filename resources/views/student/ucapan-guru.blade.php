<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <title>Papan Suara - Ruang Belajar</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800;900&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Nunito', sans-serif; background-color: #F8FAFC; }
        .page-transition { animation: slideUpFade 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        
        @keyframes slideUpFade {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Animasi lampu rekam */
        .live-glow {
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            animation: pulse-red 1.5s infinite cubic-bezier(0.66, 0, 0, 1);
        }
        @keyframes pulse-red {
            to { box-shadow: 0 0 0 15px rgba(239, 68, 68, 0); }
        }
        
        textarea::-webkit-scrollbar { width: 8px; }
        textarea::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 10px; }
    </style>
    @laravelPWA
</head>

<body class="text-slate-800 antialiased min-h-screen flex flex-col pb-28 relative overflow-x-hidden">

    <!-- HEADER -->
    <div class="bg-indigo-500 text-indigo-50 p-6 md:p-10 rounded-b-[3rem] shadow-lg border-b-8 border-indigo-700 text-center relative z-10">
        <h1 class="text-3xl font-black tracking-tight">
            Papan Suara
            <span class="text-3xl align-middle">🎙️</span>
        </h1>
        <p class="text-indigo-200 font-bold mt-1">
            Bicara ke mikrofon, biar robot yang mengetik!
        </p>
    </div>

    <!-- KONTEN UTAMA -->
    <div class="flex-1 max-w-2xl mx-auto w-full px-4 pt-8 flex flex-col gap-4 page-transition relative z-10">

        <!-- STATUS INDICATOR -->
        <div class="flex items-center justify-center gap-3 font-bold text-slate-500 bg-white py-2 px-5 rounded-full shadow-sm mx-auto border-4 border-slate-200">
            <span id="dot" class="w-4 h-4 rounded-full bg-slate-300 block transition-colors"></span>
            <span id="status" class="tracking-wide">Siap digunakan</span>
        </div>

        <!-- AREA KERTAS TULIS -->
        <div class="bg-white p-5 md:p-7 rounded-3xl border-4 border-slate-200 shadow-sm relative flex flex-col gap-4">

            <textarea id="output" placeholder="Hasil ucapanmu akan muncul di sini ya..."
                class="w-full min-h-[250px] p-5 bg-slate-50 border-4 border-slate-100 rounded-2xl text-lg md:text-xl font-bold text-slate-700 outline-none focus:border-indigo-300 focus:bg-indigo-50 resize-none transition-colors placeholder:text-slate-300 leading-relaxed"></textarea>

            <div class="flex justify-between items-center text-sm font-black text-slate-400 px-2">
                <span id="wordCount" class="bg-slate-100 px-3 py-1 rounded-md">0 kata</span>
                <span class="text-xs md:text-sm">🗣️ Bicaralah dengan jelas</span>
            </div>

            <!-- TOMBOL KONTROL -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">
                
                <button id="start" class="col-span-2 md:col-span-1 bg-green-500 hover:bg-green-400 text-white font-black text-lg py-3 px-4 rounded-2xl border-b-[6px] border-green-700 active:border-b-0 active:translate-y-[6px] transition-all flex items-center justify-center gap-2">
                    🎙️ Mulai
                </button>
                
                <button id="stop" disabled class="col-span-2 md:col-span-1 bg-red-500 hover:bg-red-400 text-white font-black text-lg py-3 px-4 rounded-2xl border-b-[6px] border-red-700 active:border-b-0 active:translate-y-[6px] transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none disabled:border-b-[6px]">
                    ⏹️ Stop
                </button>

                <button id="copy" class="bg-blue-500 hover:bg-blue-400 text-white font-black text-lg py-3 px-4 rounded-2xl border-b-[6px] border-blue-700 active:border-b-0 active:translate-y-[6px] transition-all flex items-center justify-center gap-2">
                    📋 Salin
                </button>

                <button id="clear" class="bg-slate-200 hover:bg-slate-100 text-slate-600 font-black text-lg py-3 px-4 rounded-2xl border-b-[6px] border-slate-400 active:border-b-0 active:translate-y-[6px] transition-all flex items-center justify-center gap-2">
                    🗑️ Hapus
                </button>

            </div>

        </div>
    </div>

    <!-- NAVBAR BAWAH (Tetap Dipertahankan) -->
    @include('student.navbar-bawah')


    <!-- ================================================================
         MESIN SPEECH TO TEXT (TANPA DATABASE)
         ================================================================ -->
    <script>
        const output = document.getElementById("output");
        const start = document.getElementById("start");
        const stop = document.getElementById("stop");
        const clearBtn = document.getElementById("clear");
        const copy = document.getElementById("copy");
        const statusEl = document.getElementById("status");
        const dot = document.getElementById("dot");
        const wordCount = document.getElementById("wordCount");

        // Deteksi Dukungan Browser
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        let recognition = null;
        let finalText = "";

        // Fungsi Hitung Kata
        function updateCount() {
            const text = output.value.trim();
            const words = text ? text.split(/\s+/).length : 0;
            wordCount.textContent = words + " kata";
        }
        output.addEventListener("input", updateCount);

        if (!SpeechRecognition) {
            statusEl.textContent = "Browser ini belum mendukung mikrofon.";
            statusEl.classList.add("text-red-500");
            start.disabled = true;
        } else {
            recognition = new SpeechRecognition();
            recognition.lang = "id-ID"; // Wajib Bahasa Indonesia
            recognition.continuous = true; // Terus mendengar meski ada jeda
            recognition.interimResults = true; // Tampilkan teks sementara saat bicara
            recognition.maxAlternatives = 1;

            // Saat Mulai Mendengar
            recognition.onstart = () => {
                statusEl.textContent = "Sedang mendengarkan suaramu...";
                statusEl.classList.replace("text-slate-500", "text-red-600");
                dot.classList.replace("bg-slate-300", "bg-red-500");
                dot.classList.add("live-glow");
                
                start.disabled = true; 
                stop.disabled = false;
            };

            // Saat Menangkap Suara dan Mengubahnya ke Teks
            recognition.onresult = (event) => {
                let interim = "";
                for (let i = event.resultIndex; i < event.results.length; i++) {
                    const transcript = event.results[i][0].transcript;
                    if (event.results[i].isFinal) {
                        finalText += transcript.trim() + " ";
                    } else {
                        interim += transcript;
                    }
                }
                output.value = finalText + interim;
                updateCount();
                
                // Otomatis scroll textarea ke bawah jika teks sudah penuh
                output.scrollTop = output.scrollHeight;
            };

            // Jika Ada Error (Misal mic belum diizinkan)
            recognition.onerror = (event) => {
                statusEl.textContent = "Gagal: " + event.error;
                statusEl.classList.replace("text-red-600", "text-slate-500");
                dot.classList.replace("bg-red-500", "bg-slate-300");
                dot.classList.remove("live-glow");
                
                start.disabled = false; 
                stop.disabled = true;
            };

            // Saat Berhenti Mendengar
            recognition.onend = () => {
                dot.classList.replace("bg-red-500", "bg-slate-300");
                dot.classList.remove("live-glow");
                statusEl.classList.replace("text-red-600", "text-slate-500");
                
                start.disabled = false; 
                stop.disabled = true;
                
                if (statusEl.textContent.includes("mendengarkan")) {
                    statusEl.textContent = "Selesai mendengarkan.";
                }
            };
        }

        // AKSI TOMBOL-TOMBOL
        start.onclick = () => {
            if (!recognition) return;
            // Agar teks lama tidak hilang saat ditekan "Mulai" lagi
            finalText = output.value.trim() ? output.value.trim() + " " : "";
            try { recognition.start(); } catch (e) {}
        };

        stop.onclick = () => { 
            if (recognition) recognition.stop(); 
        };

        clearBtn.onclick = () => {
            if (confirm("Yakin ingin menghapus semua teks?")) {
                output.value = "";
                finalText = "";
                updateCount();
                statusEl.textContent = "Kertas sudah bersih!";
            }
        };

        copy.onclick = async () => {
            if (!output.value.trim()) {
                alert("Tidak ada teks untuk disalin!");
                return;
            }
            try {
                await navigator.clipboard.writeText(output.value);
                const originalText = copy.innerHTML;
                copy.innerHTML = "✅ Berhasil!";
                setTimeout(() => copy.innerHTML = originalText, 2000);
            } catch (e) {
                output.select();
                document.execCommand("copy");
            }
        };
    </script>
</body>
</html>