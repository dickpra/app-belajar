<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <title>Ruang Belajar Anak</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Nunito -->
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- TomSelect -->
    <link
        href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css"
        rel="stylesheet">

    <style>

        /* =========================================================
           BASE
        ========================================================= */

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            font-family: 'Nunito', sans-serif;
            background:
                linear-gradient(
                    135deg,
                    #dff6ff 0%,
                    #eefcff 45%,
                    #fff7d6 100%
                );

            overflow-x: hidden;
        }


        /* =========================================================
           BACKGROUND
        ========================================================= */

        .kids-bg {
            position: fixed;
            inset: 0;
            z-index: -10;
            overflow: hidden;
            pointer-events: none;
        }

        .blob {
            position: absolute;
            border-radius: 9999px;
            filter: blur(2px);
            opacity: 0.75;
        }

        .blob-blue {
            width: 380px;
            height: 380px;
            top: -150px;
            left: -120px;
            background: #93c5fd;
        }

        .blob-yellow {
            width: 420px;
            height: 420px;
            right: -180px;
            bottom: -160px;
            background: #fde68a;
        }

        .blob-pink {
            width: 250px;
            height: 250px;
            right: 8%;
            top: 8%;
            background: #f9a8d4;
            opacity: 0.35;
        }

        .blob-green {
            width: 220px;
            height: 220px;
            left: 5%;
            bottom: 8%;
            background: #86efac;
            opacity: 0.30;
        }


        /* =========================================================
           POLKADOT
        ========================================================= */

        .dots {
            position: absolute;
            inset: 0;

            background-image:
                radial-gradient(
                    rgba(59, 130, 246, 0.20) 2px,
                    transparent 2px
                );

            background-size: 34px 34px;
            opacity: 0.7;
        }


        /* =========================================================
           FLOATING DECORATIONS
        ========================================================= */

        .decor {
            position: absolute;
            pointer-events: none;
            user-select: none;
        }

        .float {
            animation: float 4s ease-in-out infinite;
        }

        .float-slow {
            animation: floatSlow 6s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-12px) rotate(4deg);
            }

            100% {
                transform: translateY(0) rotate(0deg);
            }
        }

        @keyframes floatSlow {
            0% {
                transform: translateY(0) rotate(-4deg);
            }

            50% {
                transform: translateY(-18px) rotate(5deg);
            }

            100% {
                transform: translateY(0) rotate(-4deg);
            }
        }


        /* =========================================================
           MAIN CARD
        ========================================================= */

        .login-card {
            background: rgba(255, 255, 255, 0.96);

            border: 4px solid #ffffff;

            border-radius: 38px;

            box-shadow:
                0 8px 0 #bfdbfe,
                0 18px 45px rgba(37, 99, 235, 0.15);

            backdrop-filter: blur(12px);

            overflow: visible;
        }


        /* =========================================================
           MASCOT
        ========================================================= */

        .mascot {
            width: 108px;
            height: 108px;

            border-radius: 32px;

            background:
                linear-gradient(
                    145deg,
                    #60a5fa,
                    #2563eb
                );

            border: 6px solid white;

            box-shadow:
                0 7px 0 #1d4ed8,
                0 12px 25px rgba(37, 99, 235, 0.25);

            display: flex;
            align-items: center;
            justify-content: center;

            transform: rotate(-4deg);

            transition:
                transform 0.25s ease,
                box-shadow 0.25s ease;
        }

        .mascot:hover {
            transform: rotate(4deg) scale(1.04);

            box-shadow:
                0 9px 0 #1d4ed8,
                0 15px 30px rgba(37, 99, 235, 0.25);
        }

        .mascot span {
            font-family:
                "Apple Color Emoji",
                "Segoe UI Emoji",
                "Noto Color Emoji",
                sans-serif;

            font-size: 58px;

            line-height: 1;

            letter-spacing: normal;
        }


        /* =========================================================
           TITLE
        ========================================================= */

        .title {
            color: #172554;
            line-height: 1.15;
            letter-spacing: -0.04em;
        }

        .title-wave {
            display: inline-block;

            font-family:
                "Apple Color Emoji",
                "Segoe UI Emoji",
                "Noto Color Emoji",
                sans-serif;

            line-height: 1;

            letter-spacing: normal;

            animation:
                wave 1.8s ease-in-out infinite;

            transform-origin: 70% 70%;
        }

        @keyframes wave {

            0%,
            60%,
            100% {
                transform: rotate(0deg);
            }

            10%,
            30% {
                transform: rotate(15deg);
            }

            20% {
                transform: rotate(-8deg);
            }

            40% {
                transform: rotate(10deg);
            }

        }


        /* =========================================================
           LABEL
        ========================================================= */

        .form-label {
            color: #334155;
            font-size: 1.05rem;
            font-weight: 900;
        }


        /* =========================================================
           INPUT AREA
        ========================================================= */

        .input-shell {
            background: #f8fafc;

            border: 3px solid #dbeafe;

            border-radius: 20px;

            box-shadow:
                inset 0 3px 7px rgba(15, 23, 42, 0.06);

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                transform 0.2s ease;
        }

        .input-shell:focus-within {
            border-color: #60a5fa;

            box-shadow:
                0 0 0 5px #dbeafe,
                inset 0 3px 7px rgba(15, 23, 42, 0.04);

            transform: translateY(-2px);
        }


        /* =========================================================
           PIN INPUT
        ========================================================= */

        .pin-input {
            letter-spacing: 0.65em;

            text-align: center;

            font-weight: 900;

            font-size: 2rem;
        }

        .pin-input::placeholder {
            color: #94a3b8;
            opacity: 0.65;
        }


        /* =========================================================
           LOGIN BUTTON
        ========================================================= */

        .login-button {
            background:
                linear-gradient(
                    180deg,
                    #4ade80 0%,
                    #22c55e 100%
                );

            border: 3px solid #86efac;

            border-bottom: 8px solid #15803d;

            border-radius: 20px;

            color: white;

            min-height: 70px;

            box-shadow:
                0 4px 15px rgba(34, 197, 94, 0.25);

            transition:
                transform 0.1s ease,
                filter 0.2s ease;
        }

        .login-button:hover {
            filter: brightness(1.05);

            transform: translateY(-2px);
        }

        .login-button:active {
            transform: translateY(5px);

            border-bottom-width: 3px;
        }

        .rocket {
            display: inline-block;

            font-family:
                "Apple Color Emoji",
                "Segoe UI Emoji",
                "Noto Color Emoji",
                sans-serif;

            font-size: 1.8rem;

            line-height: 1;

            letter-spacing: normal;

            transform: rotate(-5deg);
        }


        /* =========================================================
           ERROR
        ========================================================= */

        .error-box {
            background: #fff1f2;

            border: 3px solid #fecdd3;

            border-radius: 20px;

            color: #be123c;

            box-shadow:
                0 4px 0 #fecdd3;
        }

        .error-icon {
            width: 48px;
            min-width: 48px;

            height: 48px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: white;

            border-radius: 15px;

            font-family:
                "Apple Color Emoji",
                "Segoe UI Emoji",
                "Noto Color Emoji",
                sans-serif;

            font-size: 28px;

            line-height: 1;
        }


        /* =========================================================
           TOM SELECT
        ========================================================= */

        .ts-wrapper {
            width: 100% !important;

            border: none !important;

            padding: 0 !important;
        }

        .ts-control {
            min-height: 62px !important;

            border: 3px solid #dbeafe !important;

            border-radius: 20px !important;

            padding: 0.9rem 1.1rem !important;

            background: #f8fafc !important;

            color: #334155 !important;

            font-family: 'Nunito', sans-serif !important;

            font-size: 1.05rem !important;

            font-weight: 800 !important;

            box-shadow:
                inset 0 3px 7px rgba(15, 23, 42, 0.06) !important;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease !important;
        }

        .ts-control.focus {
            border-color: #60a5fa !important;

            box-shadow:
                0 0 0 5px #dbeafe,
                inset 0 3px 7px rgba(15, 23, 42, 0.04) !important;
        }

        .ts-control > input {
            color: #334155 !important;

            font-family: 'Nunito', sans-serif !important;

            font-size: 1.05rem !important;

            font-weight: 800 !important;
        }

        .ts-control::after {
            display: none !important;
        }

        .ts-dropdown {
            margin-top: 8px !important;

            border: 3px solid #bfdbfe !important;

            border-radius: 20px !important;

            overflow: hidden;

            font-family: 'Nunito', sans-serif !important;

            font-weight: 800 !important;

            box-shadow:
                0 15px 35px rgba(37, 99, 235, 0.18) !important;
        }

        .ts-dropdown .option {
            padding: 14px 18px !important;

            font-size: 1rem !important;

            color: #475569 !important;
        }

        .ts-dropdown .option:hover,
        .ts-dropdown .option.active {
            background: #eff6ff !important;

            color: #2563eb !important;
        }


        /* =========================================================
           SMALL SCREEN
        ========================================================= */

        @media (max-width: 640px) {

            body {
                align-items: flex-start;
            }

            .login-wrapper {
                padding-top: 48px;
                padding-bottom: 40px;
            }

            .login-card {
                border-radius: 30px;

                border-width: 3px;

                box-shadow:
                    0 7px 0 #bfdbfe,
                    0 12px 30px rgba(37, 99, 235, 0.12);
            }

            .mascot {
                width: 92px;
                height: 92px;

                border-radius: 27px;
            }

            .mascot span {
                font-size: 48px;
            }

            .title {
                font-size: 2rem;
            }

            .decor {
                transform: scale(0.75);
            }

            .blob-blue {
                width: 260px;
                height: 260px;
            }

            .blob-yellow {
                width: 280px;
                height: 280px;
            }

            .login-button {
                min-height: 64px;

                font-size: 1.25rem;
            }

        }


        /* =========================================================
           VERY SMALL PHONE
        ========================================================= */

        @media (max-width: 380px) {

            .login-card {
                padding: 22px 18px !important;
            }

            .title {
                font-size: 1.75rem;
            }

            .form-label {
                font-size: 1rem;
            }

            .pin-input {
                font-size: 1.75rem;
            }

        }

    </style>
    @laravelPWA

</head>


<body>

    <!-- =========================================================
         BACKGROUND
    ========================================================= -->

    <div class="kids-bg">

        <div class="blob blob-blue"></div>

        <div class="blob blob-yellow"></div>

        <div class="blob blob-pink"></div>

        <div class="blob blob-green"></div>

        <div class="dots"></div>


        <!-- Decorations -->

        <div
            class="decor float text-4xl sm:text-5xl"
            style="top: 8%; left: 8%;">
            ☁️
        </div>

        <div
            class="decor float-slow text-3xl sm:text-4xl"
            style="top: 16%; right: 9%;">
            ⭐
        </div>

        <div
            class="decor float text-3xl sm:text-4xl"
            style="bottom: 14%; left: 8%;">
            🌈
        </div>

        <div
            class="decor float-slow text-3xl sm:text-4xl"
            style="bottom: 9%; right: 10%;">
            🎈
        </div>

        <div
            class="decor float text-2xl"
            style="top: 35%; left: 4%;">
            ✨
        </div>

        <div
            class="decor float-slow text-2xl"
            style="top: 42%; right: 5%;">
            ⭐
        </div>

    </div>


    <!-- =========================================================
         MAIN
    ========================================================= -->

    <main
        class="login-wrapper min-h-screen w-full flex items-center justify-center px-4 py-12 sm:py-16">


        <!-- LOGIN CARD -->

        <div
            class="login-card relative w-full max-w-md px-5 py-7 sm:px-8 sm:py-9">


            <!-- =================================================
                 MASCOT HEADER
            ================================================= -->

            <div class="text-center">


                <!-- Mascot -->

                <div class="flex justify-center mb-5">

                    <div class="mascot">

                        <span aria-hidden="true">
                            👦🏻
                        </span>

                    </div>

                </div>


                <!-- Title -->

                <h1
                    class="title text-3xl sm:text-4xl font-black">

                    <span>
                        Halo, Teman!
                    </span>

                    <span
                        class="title-wave ml-1"
                        aria-hidden="true">
                        👋
                    </span>

                </h1>


                <!-- Subtitle -->

                <p
                    class="mt-3 px-2 text-slate-500 font-bold text-sm sm:text-base leading-relaxed">

                    Yuk masuk ke ruang belajar dan mulai petualanganmu! 🚀

                </p>


                <!-- Little separator -->

                <div
                    class="flex items-center justify-center gap-2 mt-5">

                    <span class="w-8 h-1.5 bg-blue-300 rounded-full"></span>

                    <span class="w-3 h-3 bg-yellow-300 rounded-full"></span>

                    <span class="w-8 h-1.5 bg-pink-300 rounded-full"></span>

                </div>

            </div>


            <!-- =================================================
                 ERROR
            ================================================= -->

            @if($errors->any())

                <div
                    class="error-box mt-7 p-4 flex items-center gap-3">

                    <div
                        class="error-icon shrink-0"
                        aria-hidden="true">

                        🙀

                    </div>

                    <div class="min-w-0">

                        <p class="font-black text-base sm:text-lg">
                            Ups, ada yang salah!
                        </p>

                        <p class="font-bold text-sm break-words mt-0.5">
                            {{ $errors->first() }}
                        </p>

                    </div>

                </div>

            @endif


            <!-- =================================================
                 FORM
            ================================================= -->

            <form
                action="{{ route('student.login.process') }}"
                method="POST"
                class="mt-7 space-y-5">

                @csrf


                <!-- =============================================
                     NAME
                ============================================== -->

                <div>

                    <label
                        for="student-select"
                        class="form-label block mb-2">

                        👤 Namamu siapa?

                    </label>


                    <select
                        id="student-select"
                        name="student_id"
                        required>

                        <option value="">
                            🔍 Ketik atau cari namamu...
                        </option>

                        @foreach($students ?? [] as $student)

                            <option value="{{ $student->id }}">
                                {{ $student->name }}
                            </option>

                        @endforeach

                    </select>

                </div>


                <!-- =============================================
                     PIN
                ============================================== -->

                <div>

                    <label
                        for="student-pin"
                        class="form-label block mb-2">

                        🔐 PIN rahasia

                    </label>


                    <div class="input-shell">

                        <input
                            id="student-pin"
                            type="password"
                            name="pin"
                            required
                            maxlength="4"
                            pattern="\d{4}"
                            inputmode="numeric"
                            autocomplete="current-password"
                            placeholder="• • • •"
                            class="pin-input w-full px-5 py-4 bg-transparent border-0 outline-none text-slate-800">

                    </div>


                    <p
                        class="mt-2 text-xs sm:text-sm text-slate-400 font-bold text-center">

                        Masukkan 4 angka PIN-mu ya 😊

                    </p>

                </div>


                <!-- =============================================
                     LOGIN BUTTON
                ============================================== -->

                <button
                    type="submit"
                    class="login-button w-full mt-2 px-5 flex items-center justify-center gap-3">

                    <span>
                        MULAI BELAJAR!
                    </span>

                    <span
                        class="rocket"
                        aria-hidden="true">
                        🚀
                    </span>

                </button>


                <!-- Bottom encouragement -->

                <div
                    class="flex items-center justify-center gap-2 pt-2 text-xs sm:text-sm font-black text-slate-400">

                    <span>⭐</span>

                    <span>
                        Siap belajar hari ini?
                    </span>

                    <span>🌟</span>

                </div>

            </form>

        </div>

    </main>


    <!-- =========================================================
         TOM SELECT
    ========================================================= -->

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>


    <script>

        document.addEventListener('DOMContentLoaded', function () {

            new TomSelect('#student-select', {

                create: false,

                maxOptions: null,

                sortField: {
                    field: 'text',
                    direction: 'asc'
                },

                placeholder: '🔍 Ketik atau cari namamu...',

                render: {

                    no_results: function (data, escape) {

                        return `
                            <div class="p-4 text-center text-slate-400 font-bold">
                                😅 Nama tidak ditemukan
                            </div>
                        `;

                    }

                }

            });

        });

        // CEGATAN 2 (Lapis Browser): Jika tombol back ditekan dari dashboard, 
        // langsung tendang balik ke depan!
        let isLoggedIn = "{{ session()->has('student_id') ? 'yes' : 'no' }}";
        if (isLoggedIn === 'yes') {
            window.location.replace("{{ route('student.dashboard') }}");
        }

    </script>

</body>
</html>