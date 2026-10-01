<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <title>Ucapan Guru - Ruang Belajar</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;800;900&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #FDF2F8;
        }

        .page-transition {
            animation: slideUpFade 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes slideUpFade {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Animasi avatar */
        .float-anim {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {
            0% {
                transform: translate(-50%, 0);
            }

            50% {
                transform: translate(-50%, -10px);
            }

            100% {
                transform: translate(-50%, 0);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FIX EMOJI
        |--------------------------------------------------------------------------
        */

        /* Supaya emoji tidak punya line-height aneh */
        .emoji {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            flex-shrink: 0;
        }

        /* Area emoji tombol dibuat fixed */
        .button-emoji {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            flex-shrink: 0;
        }

        /* Avatar emoji */
        .avatar-emoji {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
    </style>
    @laravelPWA
</head>

<body class="text-slate-800 antialiased min-h-screen flex flex-col pb-28 relative overflow-x-hidden">

    <!-- HEADER -->
    <div class="bg-pink-400 text-pink-900 p-6 md:p-10 rounded-b-[3rem] shadow-lg border-b-8 border-pink-500 text-center relative z-10">

        <h1 class="text-3xl font-black tracking-tight">
            Ucapan Guru
            <span class="emoji text-3xl align-middle">💬</span>
        </h1>

        <p class="text-pink-800 font-bold mt-1">
            Pesan semangat langsung dari gurumu!
        </p>

    </div>


    <!-- KONTEN UTAMA -->
    <div class="flex-1 max-w-md mx-auto w-full px-4 pt-10 flex flex-col gap-6 page-transition relative z-10">

        <div class="bg-white p-6 rounded-3xl border-4 border-slate-200 shadow-sm relative text-center">

            <!--
            ============================================================
            AVATAR GURU
            ============================================================
            -->

            <div
                class="absolute -top-10 left-1/2 -translate-x-1/2
                       w-20 h-20
                       bg-blue-100
                       rounded-full
                       border-4 border-white
                       shadow-md
                       flex items-center justify-center
                       float-anim"
            >

                <span class="avatar-emoji text-[42px]">
                    👩🏻‍🏫
                </span>

            </div>


            <!-- PESAN -->
            <div
                class="bg-slate-50
                       border-2 border-dashed border-slate-200
                       rounded-2xl
                       p-4
                       text-slate-600
                       font-bold
                       leading-relaxed
                       mb-6
                       mt-4"
            >
                "{{ $ucapan }}"
            </div>


            <!--
            ============================================================
            TOMBOL TEXT TO SPEECH
            ============================================================
            -->

            <button
                type="button"
                onclick="bacakanUcapan(`{{ $ucapan }}`, this)"

                class="w-full
                       min-h-[64px]
                       bg-pink-100
                       hover:bg-pink-200
                       text-pink-700
                       font-black
                       text-lg
                       py-4
                       px-5
                       rounded-2xl
                       border-2
                       border-pink-300
                       border-b-[6px]
                       transition-all
                       active:translate-y-1
                       active:border-b-2

                       flex
                       items-center
                       justify-center
                       gap-3
                       leading-none"
            >

                <!-- Emoji fixed -->
                <span class="button-emoji text-2xl">
                    ▶️
                </span>

                <!-- Text -->
                <span class="leading-tight">
                    Dengarkan Suara Guru
                </span>

            </button>

        </div>

    </div>


    <!-- NAVBAR -->
    @include('student.navbar-bawah')


    <!--
    ================================================================
    TEXT TO SPEECH
    ================================================================
    -->

    <script>

        let synth = window.speechSynthesis;

        let voices = [];

        function loadVoices() {
            voices = synth.getVoices();
        }

        loadVoices();

        if (speechSynthesis.onvoiceschanged !== undefined) {
            speechSynthesis.onvoiceschanged = loadVoices;
        }


        function setButtonNormal(btnElement) {

            btnElement.innerHTML = `
                <span class="button-emoji text-2xl">
                    ▶️
                </span>

                <span class="leading-tight">
                    Dengarkan Suara Guru
                </span>
            `;

            btnElement.classList.remove('bg-pink-200');
            btnElement.classList.add('bg-pink-100');
        }


        function setButtonSpeaking(btnElement) {

            btnElement.innerHTML = `
                <span class="button-emoji text-2xl animate-pulse">
                    ⏹️
                </span>

                <span class="leading-tight">
                    Hentikan Suara
                </span>
            `;

            btnElement.classList.remove('bg-pink-100');
            btnElement.classList.add('bg-pink-200');
        }


        function bacakanUcapan(teks, btnElement) {

            /*
            |--------------------------------------------------------------------------
            | Jika sedang berbicara → hentikan
            |--------------------------------------------------------------------------
            */

            if (synth.speaking) {

                synth.cancel();

                setButtonNormal(btnElement);

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Buat suara
            |--------------------------------------------------------------------------
            */

            let utterThis = new SpeechSynthesisUtterance(teks);

            utterThis.lang = 'id-ID';

            // Sedikit lebih tinggi agar terdengar lebih ceria
            utterThis.pitch = 1.1;

            // Kecepatan normal
            utterThis.rate = 1.0;


            /*
            |--------------------------------------------------------------------------
            | Cari suara Indonesia
            |--------------------------------------------------------------------------
            */

            let femaleVoice = voices.find(v =>
                v.lang.toLowerCase().includes('id') &&
                (
                    v.name.toLowerCase().includes('female') ||
                    v.name.toLowerCase().includes('perempuan')
                )
            );

            if (femaleVoice) {
                utterThis.voice = femaleVoice;
            }


            /*
            |--------------------------------------------------------------------------
            | Ubah tombol
            |--------------------------------------------------------------------------
            */

            setButtonSpeaking(btnElement);


            /*
            |--------------------------------------------------------------------------
            | Selesai bicara
            |--------------------------------------------------------------------------
            */

            utterThis.onend = function () {

                setButtonNormal(btnElement);

            };


            /*
            |--------------------------------------------------------------------------
            | Error
            |--------------------------------------------------------------------------
            */

            utterThis.onerror = function () {

                setButtonNormal(btnElement);

            };


            /*
            |--------------------------------------------------------------------------
            | Mulai bicara
            |--------------------------------------------------------------------------
            */

            synth.speak(utterThis);
        }

    </script>

</body>
</html>