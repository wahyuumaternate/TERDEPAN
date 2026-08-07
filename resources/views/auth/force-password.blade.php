<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Kata Sandi - SISTEM TERDEPAN BAPPEDA MALUT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eef5ff',
                            100: '#d9e9ff',
                            200: '#bcd9ff',
                            300: '#8ac1ff',
                            400: '#549cff',
                            500: '#2b75ff',
                            600: '#1a58f5',
                            700: '#1548e3',
                            800: '#183ab8',
                            900: '#1a3691',
                            950: '#132158',
                        }
                    },
                    boxShadow: {
                        'card': '0 4px 20px rgba(0, 0, 0, 0.08)',
                    }
                }
            }
        }
    </script>
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        .custom-input {
            border-radius: 12px;
            transition: all 0.2s ease;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }

        .custom-input:focus {
            border-color: #1a58f5;
            box-shadow: 0 0 0 3px rgba(26, 88, 245, 0.1);
            background-color: #fff;
        }

        .eye-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            cursor: pointer;
        }

        .eye-icon:hover {
            color: #1548e3;
        }

        .reset-btn {
            background-image: linear-gradient(to right, #1a58f5, #0f3bbd);
            box-shadow: 0 4px 12px rgba(21, 72, 227, 0.3);
            transition: all 0.3s ease;
        }

        .reset-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(21, 72, 227, 0.4);
        }

        .reset-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(21, 72, 227, 0.3);
        }

        .mobile-container {
            min-height: 100vh;
            background-image: url('{{ asset('img/texture-login.png') }}');
            background-size: 200px;
            background-repeat: repeat;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1.5rem;
        }

        .mobile-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(21, 72, 227, 0.92);
            z-index: 1;
        }

        .mobile-card {
            position: relative;
            z-index: 10;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
        }

        .desktop-container {
            height: 100vh;
            display: flex;
            overflow: hidden;
        }

        .desktop-left {
            background-color: #1548e3;
            position: relative;
        }

        .desktop-left::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('{{ asset('img/texture-login.png') }}');
            background-size: 200px;
            background-repeat: repeat;
            opacity: 0.15;
            z-index: 0;
        }

        .content-wrapper {
            position: relative;
            z-index: 10;
        }

        .desktop-illustration {
            max-width: 400px;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(0px);
            }
        }
    </style>
</head>

<body class="bg-gray-100">
    @php
        $eyeOpenSvg =
            '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
    @endphp

    <!-- MOBILE VIEW -->
    <div class="md:hidden">
        <div class="mobile-container">
            <div class="mobile-card bg-white p-6 sm:p-8">
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Ganti Kata Sandi</h1>
                    <p class="text-gray-500 text-sm">
                        Akun Anda masih menggunakan kata sandi default. Buat kata sandi baru sebelum melanjutkan.
                    </p>
                </div>

                @if ($errors->updatePassword->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->updatePassword->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                    @csrf
                    @method('put')

                    <div class="relative">
                        <label for="current_password-mobile" class="block text-sm font-medium text-gray-700 mb-1">Kata
                            Sandi Saat Ini</label>
                        <input id="current_password-mobile" type="password" name="current_password" required
                            autocomplete="current-password" autofocus
                            class="custom-input w-full px-4 py-3 pr-12 focus:outline-none" />
                        <button type="button" class="eye-icon" style="top: 38px;"
                            onclick="togglePw('current_password-mobile', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">{!! $eyeOpenSvg !!}</svg>
                        </button>
                    </div>

                    <div class="relative">
                        <label for="password-mobile" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi
                            Baru</label>
                        <input id="password-mobile" type="password" name="password" required
                            autocomplete="new-password" placeholder="Minimal 8 karakter"
                            class="custom-input w-full px-4 py-3 pr-12 focus:outline-none" />
                        <button type="button" class="eye-icon" style="top: 38px;" onclick="togglePw('password-mobile', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">{!! $eyeOpenSvg !!}</svg>
                        </button>
                    </div>

                    <div class="relative">
                        <label for="password_confirmation-mobile" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi
                            Kata Sandi Baru</label>
                        <input id="password_confirmation-mobile" type="password" name="password_confirmation" required
                            autocomplete="new-password" placeholder="Ulangi kata sandi baru"
                            class="custom-input w-full px-4 py-3 pr-12 focus:outline-none" />
                        <button type="button" class="eye-icon" style="top: 38px;"
                            onclick="togglePw('password_confirmation-mobile', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">{!! $eyeOpenSvg !!}</svg>
                        </button>
                    </div>

                    <button type="submit"
                        class="reset-btn w-full text-white font-medium py-3 rounded-xl transition flex items-center justify-center mt-4">
                        <span>Simpan Kata Sandi Baru</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="mt-6 text-center">
                    @csrf
                    <button type="submit" class="text-gray-500 hover:text-gray-700 text-sm underline">Keluar</button>
                </form>
            </div>
        </div>
    </div>

    <!-- DESKTOP VIEW -->
    <div class="hidden md:flex desktop-container">
        <div class="w-2/5 desktop-left flex items-center justify-center overflow-hidden">
            <div class="content-wrapper text-center text-white px-8 py-12 max-w-lg">
                <div class="mb-10">
                    <img src="{{ asset('assets/img/putih.webp') }}" alt="Logo TERDEPAN" class="w-32 h-32 mx-auto mb-4">
                    <h1 class="text-4xl font-bold mb-2">TERDEPAN</h1>
                    <p class="text-xl opacity-90">Terminal Data dan Evidensi Perencanaan Pembangunan</p>
                </div>

                <div class="flex justify-center items-center mt-8">
                    <img src="{{ asset('img/login-illustration.png') }}" alt="Illustration"
                        class="desktop-illustration">
                </div>
            </div>
        </div>

        <div class="w-3/5 bg-white flex items-center justify-center">
            <div class="w-full max-w-md px-10 py-10">
                <div class="text-center mb-8 mt-6">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Ganti Kata Sandi</h1>
                    <p class="text-gray-600">
                        Akun Anda masih menggunakan kata sandi default. Buat kata sandi baru sebelum melanjutkan.
                    </p>
                </div>

                @if ($errors->updatePassword->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->updatePassword->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                    @csrf
                    @method('put')

                    <div class="relative">
                        <label for="current_password-desktop" class="block text-sm font-medium text-gray-700 mb-1">Kata
                            Sandi Saat Ini</label>
                        <input id="current_password-desktop" type="password" name="current_password" required
                            autocomplete="current-password" autofocus
                            class="custom-input w-full px-4 py-2.5 pr-12 focus:outline-none" />
                        <button type="button" class="eye-icon" style="top: 38px;"
                            onclick="togglePw('current_password-desktop', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">{!! $eyeOpenSvg !!}</svg>
                        </button>
                    </div>

                    <div class="relative">
                        <label for="password-desktop" class="block text-sm font-medium text-gray-700 mb-1">Kata Sandi
                            Baru</label>
                        <input id="password-desktop" type="password" name="password" required
                            autocomplete="new-password" placeholder="Minimal 8 karakter"
                            class="custom-input w-full px-4 py-2.5 pr-12 focus:outline-none" />
                        <button type="button" class="eye-icon" style="top: 38px;"
                            onclick="togglePw('password-desktop', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">{!! $eyeOpenSvg !!}</svg>
                        </button>
                    </div>

                    <div class="relative">
                        <label for="password_confirmation-desktop"
                            class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Kata Sandi Baru</label>
                        <input id="password_confirmation-desktop" type="password" name="password_confirmation" required
                            autocomplete="new-password" placeholder="Ulangi kata sandi baru"
                            class="custom-input w-full px-4 py-2.5 pr-12 focus:outline-none" />
                        <button type="button" class="eye-icon" style="top: 38px;"
                            onclick="togglePw('password_confirmation-desktop', this)">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">{!! $eyeOpenSvg !!}</svg>
                        </button>
                    </div>

                    <button type="submit"
                        class="reset-btn w-full text-white font-medium py-3 rounded-lg transition flex items-center justify-center mt-6">
                        <span>Simpan Kata Sandi Baru</span>
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="mt-6 text-center">
                    @csrf
                    <button type="submit" class="text-gray-500 hover:text-gray-700 text-sm underline">Keluar</button>
                </form>

                <div class="mt-12 pt-4 border-t border-gray-200 text-center text-xs text-gray-500">
                    © {{ date('Y') }} Terminal Data dan Evidensi Perencanaan Pembangunan. All rights reserved.
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePw(inputId, btn) {
            const input = document.getElementById(inputId);
            const eyeClosed =
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 9.956 0 012.042-3.368m3.087-2.742A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.293 5.411M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />';
            const eyeOpen =
                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';

            if (input.type === 'password') {
                input.type = 'text';
                btn.querySelector('svg').innerHTML = eyeClosed;
            } else {
                input.type = 'password';
                btn.querySelector('svg').innerHTML = eyeOpen;
            }
        }
    </script>
</body>

</html>
