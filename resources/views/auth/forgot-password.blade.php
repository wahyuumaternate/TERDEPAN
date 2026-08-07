<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - SISTEM TERDEPAN BAPPEDA MALUT</title>
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
    </style>
</head>

<body class="bg-gray-100">
    <!-- MOBILE VIEW -->
    <div class="md:hidden">
        <div class="mobile-container">
            <div class="mobile-card bg-white p-6 sm:p-8">
                <!-- HEADER -->
                <div class="text-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Lupa Kata Sandi</h1>
                    <p class="text-gray-500 text-sm">Masukkan email Anda untuk menerima link reset password</p>
                </div>

                <!-- SESSION STATUS -->
                @if (session('status'))
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-600 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- FORM -->
                <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email
                        </label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            autofocus placeholder="Masukkan alamat email Anda"
                            class="custom-input w-full px-4 py-3 focus:outline-none" />

                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- SUBMIT -->
                    <button type="submit"
                        class="reset-btn w-full text-white font-medium py-3 rounded-xl transition flex items-center justify-center mt-4">
                        <span>Kirim Link Reset Password</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                        </svg>
                    </button>
                </form>

                <!-- BACK TO LOGIN -->
                <div class="mt-6 text-center">
                    <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                        Kembali ke halaman login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- DESKTOP VIEW -->
    <div class="hidden md:flex desktop-container">
        <!-- LEFT SIDE -->
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

        <!-- RIGHT SIDE -->
        <div class="w-3/5 bg-white flex items-center justify-center">
            <div class="w-full max-w-md px-10 py-10">
                <!-- HEADER -->
                <div class="text-center mb-8 mt-6">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Lupa Kata Sandi</h1>
                    <p class="text-gray-600">Masukkan email Anda untuk menerima link reset password</p>
                </div>

                <!-- SESSION STATUS -->
                @if (session('status'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-600">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- FORM -->
                <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email
                        </label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            autofocus placeholder="Masukkan alamat email Anda"
                            class="custom-input w-full px-4 py-3 focus:outline-none" />

                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- SUBMIT -->
                    <button type="submit"
                        class="reset-btn w-full text-white font-medium py-3 rounded-lg transition flex items-center justify-center mt-6">
                        <span>Kirim Link Reset Password</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                        </svg>
                    </button>
                </form>

                <!-- BACK TO LOGIN -->
                <div class="mt-6 text-center">
                    <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-700 font-medium">
                        Kembali ke halaman login
                    </a>
                </div>

                <!-- FOOTER -->
                <div class="mt-12 pt-4 border-t border-gray-200 text-center text-xs text-gray-500">
                    © 2025 Terminal Data dan Evidensi Perencanaan Pembangunan. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>

</html>
