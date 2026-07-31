<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Aplikasi - SISTEM TERDEPAN BAPPEDA MALUT</title>
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

        /* Menu Card Styles */
        .menu-card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid transparent;
        }

        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            border-color: #1a58f5;
        }

        .menu-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            transition: all 0.3s ease;
        }

        .menu-card:hover .menu-icon {
            transform: scale(1.1);
        }

        /* Mobile styles */
        .mobile-container {
            min-height: 100vh;
            background-image: url('{{ asset('img/texture-login2.png') }}');
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
            background-image: url('{{ asset('img/texture-login2.png') }}');
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
            <div class="relative z-10 w-full max-w-md">
                <!-- HEADER -->
                <div class="text-center mb-8">
                    <div class="mb-4">
                        <img src="{{ asset('assets/img/putih.webp') }}" alt="Logo TERDEPAN"
                            class="w-20 h-20 mx-auto mb-3">
                    </div>
                    <h1 class="text-3xl font-bold text-white mb-2">TERDEPAN</h1>
                    <p class="text-white/90 text-sm">Pilih Dashboard yang akan digunakan</p>
                </div>

                <!-- MENU CARDS -->
                <div class="space-y-4">
                    <!-- Terminal Data -->
                    <a href="{{ route('terminaldata.index') }}" class="menu-card block">
                        <div class="menu-icon bg-blue-100">
                            <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3 text-center">Terminal Data</h3>
                        <div class="mt-4 flex justify-center">
                            <span class="inline-flex items-center text-primary-600 font-medium">
                                Buka Terminal Data
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>
                    </a>

                    <!-- E-Kinerja -->
                    <a href="{{ route('e-kinerja.index') }}" class="menu-card block">
                        <div class="menu-icon bg-green-100">
                            <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3 text-center">E-Kinerja Perencana</h3>
                        <div class="mt-4 flex justify-center">
                            <span class="inline-flex items-center text-primary-600 font-medium">
                                Buka E-Kinerja
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>
                    </a>
                </div>

                <!-- FOOTER -->
                <div class="mt-8 text-center">
                    <p class="text-white/80 text-xs">© 2025 BAPPEDA Maluku Utara</p>
                </div>
            </div>
        </div>
    </div>

    <!-- DESKTOP VIEW -->
    <div class="hidden md:flex desktop-container">
        <!-- LEFT SIDE -->
        <div class="w-2/5 desktop-left flex items-center justify-center overflow-hidden">
            <div class="content-wrapper text-center text-white px-8 py-12 max-w-lg">
                <div class="flex justify-center items-center mb-10">
                    <img src="{{ asset('assets/img/putih.webp') }}" alt="Illustration" class="desktop-illustration">
                </div>
                <div class="mt-5">
                    <h1 class="text-4xl font-bold mt-5">TERDEPAN</h1>
                    <p class="text-xl opacity-90">Terminal Data dan e-Kinerja Perencana</p>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE -->
        <div class="w-3/5 bg-white flex items-center justify-center">
            <div class="w-full max-w-3xl px-10 py-10">
                <!-- HEADER -->
                <div class="text-center mb-12">
                    <h1 class="text-3xl font-bold text-gray-900 mb-3">Selamat Datang</h1>
                    <p class="text-gray-600 text-lg">Pilih Dashboard akan digunakan</p>
                </div>

                <!-- MENU CARDS GRID -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                    <!-- Terminal Data -->
                    <a href="{{ route('terminaldata.index') }}" class="menu-card">
                        <div class="menu-icon bg-blue-100">
                            <svg class="w-12 h-12 text-blue-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3 text-center">Terminal Data</h3>
                        <div class="mt-4 flex justify-center">
                            <span class="inline-flex items-center text-primary-600 font-medium">
                                Buka Terminal Data
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>
                    </a>

                    <!-- E-Kinerja -->
                    <a href="{{ route('e-kinerja.index') }}" class="menu-card">
                        <div class="menu-icon bg-green-100">
                            <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3 text-center">E-Kinerja Perencana</h3>
                        <div class="mt-4 flex justify-center">
                            <span class="inline-flex items-center text-primary-600 font-medium">
                                Buka E-Kinerja
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>
                    </a>
                </div>

                <!-- LOGOUT BUTTON -->
                <div class="text-center">
                    <form method="POST" action="{{ route('logout') }}" class="inline-block">
                        @csrf
                        <button type="submit"
                            class="text-gray-600 hover:text-gray-900 text-sm font-medium flex items-center mx-auto">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Keluar dari Sistem
                        </button>
                    </form>
                </div>

                <!-- FOOTER -->
                <div class="mt-8 pt-6 border-t border-gray-200 text-center text-xs text-gray-500">
                    © 2025 Terminal Data dan E-Kinerja Perencana. BAPPEDA Provinsi Maluku Utara. All rights reserved.
                </div>
            </div>
        </div>
    </div>
</body>

</html>
