<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SISTEM TERDEPAN BAPPEDA MALUT</title>
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

        /* Shared styles */
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

        .login-btn {
            background-image: linear-gradient(to right, #1a58f5, #0f3bbd);
            box-shadow: 0 4px 12px rgba(21, 72, 227, 0.3);
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(21, 72, 227, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(21, 72, 227, 0.3);
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

        /* Mobile styles */
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

        .qr-container {
            position: relative;
        }

        .qr-container::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 40px;
            height: 40px;
            background-image: url('{{ asset('img/logo-small.png') }}');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        /* Desktop styles */
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

        .qrcode-fixed {
            width: 150px;
            height: 150px;
            flex-shrink: 0;
        }
    </style>
</head>

<body class="bg-gray-100">
    <!-- MOBILE VIEW -->
    <div class="md:hidden">
        <div class="mobile-container">
            <div class="mobile-card bg-white p-6 sm:p-8">
                <!-- HEADER -->
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Masuk TERDEPAN</h1>
                    {{-- <p class="text-gray-500 text-sm">Belum punya akun? <a href="{{ route('register') }}"
                            class="text-primary-600 font-medium">Registrasi</a></p> --}}
                </div>

                <!-- QR CODE -->
                <div class="flex justify-center mb-6">
                    <div
                        class="qr-container bg-white p-3 rounded-xl shadow-sm border w-52 h-52 flex items-center justify-center">
                        <img src="{{ asset('img/qrcode.png') }}" alt="QR Code" class="w-full h-auto">
                    </div>
                </div>

                <p class="text-center text-gray-500 text-sm mb-6">
                    Scan kode QR dengan<br>Aplikasi TERDEPAN di HP Anda
                </p>

                <!-- ALERTS -->
                @if (session('status'))
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-600 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- FORM -->
                <form method="POST" action="{{ route('login') }}" id="loginForm-mobile" class="space-y-4">
                    @csrf

                    <!-- NIP/NIK -->
                    <div>
                        <input id="nomor_identitas-mobile" type="text" name="nomor_identitas" required
                            placeholder="Isikan username atau NIK"
                            class="custom-input w-full px-4 py-3 focus:outline-none" />
                    </div>

                    <!-- PASSWORD -->
                    <div class="relative">
                        <input id="password-mobile" type="password" name="password" required placeholder="Kata sandi"
                            class="custom-input w-full px-4 py-3 pr-12 focus:outline-none" />

                        <!-- Toggle Eye -->
                        <button type="button" id="togglePassword-mobile" class="eye-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" id="eyeIcon-mobile" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5
                                    c4.478 0 8.268 2.943 9.542 7
                                    -1.274 4.057-5.064 7-9.542 7
                                    -4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>

                    <!-- REMEMBER & FORGOT -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember_me-mobile" type="checkbox" name="remember"
                                class="w-4 h-4 border border-gray-300 rounded focus:ring-2 focus:ring-primary-500 cursor-pointer" />
                            <label for="remember_me-mobile" class="ml-2 text-gray-600 text-sm cursor-pointer">
                                Ingat saya
                            </label>
                        </div>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                                class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                                Lupa kata sandi?
                            </a>
                        @endif
                    </div>

                    <!-- SUBMIT -->
                    <button type="submit" id="loginButton-mobile"
                        class="login-btn w-full text-white font-medium py-3 rounded-xl transition flex items-center justify-center mt-4">
                        <span>Masuk Akun</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </form>

                <!-- FOOTER -->
                <div class="mt-6 flex justify-between items-center text-sm">
                    <div class="relative">
                        <button
                            class="flex items-center px-2 py-1.5 border border-gray-300 rounded-lg text-xs text-gray-700">
                            <span>Indonesia</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 ml-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>

                    <a href="#" class="text-xs text-primary-600 hover:text-primary-700">Perlu Bantuan?</a>
                </div>
            </div>

            <!-- AUTO LOGIN FOR LOCAL (Mobile) -->
            @if (app()->environment('local'))
                <div class="mt-4 text-center z-10">
                    <button type="button" id="autoLoginButton-mobile"
                        class="text-xs text-white hover:text-white/80 focus:outline-none">
                        Login Otomatis (hanya development)
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- DESKTOP VIEW -->
    <div class="hidden md:flex desktop-container">
        <!-- LEFT SIDE -->
        <div class="w-2/5 desktop-left flex items-center justify-center overflow-hidden">
            <div class="content-wrapper text-center text-white px-8 py-12 max-w-lg">
                <div class="mb-10">
                    <img src="{{ asset('img/logo-white.png') }}" alt="Logo TERDEPAN" class="w-24 h-24 mx-auto mb-4">
                    <h1 class="text-4xl font-bold mb-2">TERDEPAN</h1>
                    <p class="text-xl opacity-90">Terminal Data dan e-Kinerja Perencana</p>
                </div>

                <div class="flex justify-center items-center mt-8">
                    <img src="{{ asset('img/login-illustration.png') }}" alt="Illustration"
                        class="desktop-illustration">
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE -->
        <div class="w-3/5 bg-white flex items-center justify-center">
            <div class="w-full max-w-xl px-10 py-10">
                <!-- HEADER -->
                <div class="text-center mb-10 mt-6">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Masuk TERDEPAN</h1>
                    <p class="text-gray-600">Masukkan kredensial Anda untuk masuk ke sistem</p>
                </div>

                <!-- ENVIRONMENT INDICATOR -->
                @if (app()->environment('local'))
                    <div
                        class="mb-5 p-2 bg-yellow-50 border border-yellow-200 rounded-lg text-yellow-700 text-xs text-center">
                        <p>Mode Pengembangan Lokal</p>
                    </div>
                @endif

                <!-- ALERTS -->
                @if (session('status'))
                    <div class="mb-5 p-3 bg-green-50 border border-green-200 rounded-lg text-green-600 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- QR CODE + FORM CONTAINER -->
                <div class="flex flex-row gap-8 mb-6">
                    <!-- QR CODE -->
                    <div class="qrcode-fixed">
                        <div
                            class="bg-white p-3 rounded-xl shadow-sm border w-full h-full flex items-center justify-center">
                            <img src="{{ asset('img/qrcode.png') }}" alt="QR Code" class="w-full h-auto">
                        </div>
                        <p class="text-center text-gray-500 text-sm mb-6">
                            Scan kode QR dengan<br>Aplikasi TERDEPAN di HP Anda
                        </p>
                    </div>

                    <!-- FORM -->
                    <div class="flex-grow">
                        <form method="POST" action="{{ route('login') }}" id="loginForm-desktop"
                            class="space-y-5">
                            @csrf

                            <!-- NIP/NIK -->
                            <div>
                                <label for="nomor_identitas-desktop"
                                    class="block text-sm font-medium text-gray-700 mb-1">
                                    NIP / NIK
                                </label>
                                <input id="nomor_identitas-desktop" type="text" name="nomor_identitas" required
                                    placeholder="Isikan NIP atau NIK Anda"
                                    class="custom-input w-full px-4 py-2.5 focus:outline-none" />
                            </div>

                            <!-- PASSWORD -->
                            <div>
                                <label for="password-desktop" class="block text-sm font-medium text-gray-700 mb-1">
                                    Kata Sandi
                                </label>
                                <div class="relative">
                                    <input id="password-desktop" type="password" name="password" required
                                        placeholder="Masukkan kata sandi"
                                        class="custom-input w-full px-4 py-2.5 pr-12 focus:outline-none" />

                                    <!-- Toggle Eye -->
                                    <button type="button" id="togglePassword-desktop" class="eye-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" id="eyeIcon-desktop" class="h-5 w-5"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5
                                                c4.478 0 8.268 2.943 9.542 7
                                                -1.274 4.057-5.064 7-9.542 7
                                                -4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- REMEMBER & FORGOT -->
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center">
                                    <input id="remember_me-desktop" type="checkbox" name="remember"
                                        class="w-4 h-4 border border-gray-300 rounded focus:ring-2 focus:ring-primary-500 cursor-pointer" />
                                    <label for="remember_me-desktop" class="ml-2 text-gray-600 cursor-pointer">
                                        Ingat saya
                                    </label>
                                </div>

                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}"
                                        class="text-primary-600 hover:text-primary-700 font-medium">
                                        Lupa kata sandi?
                                    </a>
                                @endif
                            </div>

                            <!-- SUBMIT -->
                            <button type="submit" id="loginButton-desktop"
                                class="login-btn w-full text-white font-medium py-3 rounded-lg transition flex items-center justify-center mt-6">
                                <span>Masuk Akun</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="text-center text-sm">
                    <p class="text-gray-500 mb-4">Scan kode QR dengan Aplikasi TERDEPAN di HP Anda</p>
                </div>

                {{-- <!-- REGISTER LINK -->
                <div class="mt-6 text-center text-sm">
                    <span class="text-gray-600">Belum punya akun? </span>
                    <a href="{{ route('register') }}"
                        class="text-primary-600 hover:text-primary-700 font-medium">Registrasi</a>
                </div> --}}

                <!-- AUTO LOGIN FOR LOCAL (Desktop) -->
                @if (app()->environment('local'))
                    <div class="mt-4 text-center">
                        <button type="button" id="autoLoginButton-desktop"
                            class="text-xs text-primary-600 hover:text-primary-800 focus:outline-none">
                            Login Otomatis (hanya development)
                        </button>
                    </div>
                @endif

                <!-- LANGUAGE SELECTOR -->
                <div class="mt-8 flex justify-between items-center">
                    <div class="relative">
                        <button
                            class="flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700">
                            <span>Indonesia</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                    </div>

                    <a href="#" class="text-sm text-primary-600 hover:text-primary-700">Perlu Bantuan?</a>
                </div>

                <!-- FOOTER -->
                <div class="mt-8 pt-4 border-t border-gray-200 text-center text-xs text-gray-500">
                    © 2025 Terminal Data dan Evidensi Perencanaan Pembangunan. All rights reserved.
                </div>
            </div>
        </div>
    </div>

    <!-- JS FOR PASSWORD TOGGLE AND AUTO LOGIN -->
    <script>
        // Toggle password for mobile view
        document.getElementById('togglePassword-mobile').addEventListener('click', function() {
            const pwd = document.getElementById('password-mobile');
            const eyeIcon = document.getElementById('eyeIcon-mobile');
            togglePasswordVisibility(pwd, eyeIcon);
        });

        // Toggle password for desktop view
        document.getElementById('togglePassword-desktop').addEventListener('click', function() {
            const pwd = document.getElementById('password-desktop');
            const eyeIcon = document.getElementById('eyeIcon-desktop');
            togglePasswordVisibility(pwd, eyeIcon);
        });

        function togglePasswordVisibility(passwordField, eyeIcon) {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.innerHTML =
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 9.956 0 012.042-3.368m3.087-2.742A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.293 5.411M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />';
            } else {
                passwordField.type = 'password';
                eyeIcon.innerHTML =
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
            }
        }

        // Auto login functionality for local environment
        document.addEventListener('DOMContentLoaded', function() {
            @if (app()->environment('local'))
                const defaultCredentials = {
                    nomor_identitas: '197001011990011001',
                    password: 'password123'
                };

                // Auto-login for mobile
                const autoLoginButtonMobile = document.getElementById('autoLoginButton-mobile');
                if (autoLoginButtonMobile) {
                    autoLoginButtonMobile.addEventListener('click', function() {
                        document.getElementById('nomor_identitas-mobile').value = defaultCredentials
                            .nomor_identitas;
                        document.getElementById('password-mobile').value = defaultCredentials.password;
                        document.getElementById('remember_me-mobile').checked = true;

                        setTimeout(() => {
                            document.getElementById('loginForm-mobile').submit();
                        }, 500);
                    });
                }

                // Auto-login for desktop
                const autoLoginButtonDesktop = document.getElementById('autoLoginButton-desktop');
                if (autoLoginButtonDesktop) {
                    autoLoginButtonDesktop.addEventListener('click', function() {
                        document.getElementById('nomor_identitas-desktop').value = defaultCredentials
                            .nomor_identitas;
                        document.getElementById('password-desktop').value = defaultCredentials.password;
                        document.getElementById('remember_me-desktop').checked = true;

                        setTimeout(() => {
                            document.getElementById('loginForm-desktop').submit();
                        }, 500);
                    });
                }
            @endif
        });
    </script>
</body>

</html>
