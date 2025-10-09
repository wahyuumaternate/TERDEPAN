<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SISTEM TERDEPAN BAPPEDA KUNINGAN</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-white">
    <div class="min-h-screen flex">
        <!-- LEFT SIDE -->
        <div
            class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-blue-600 via-blue-500 to-blue-900 relative overflow-hidden justify-center items-center rounded-r-[2.5rem]">
            <div class="absolute inset-0 opacity-30">
                <svg class="w-full h-full" viewBox="0 0 1200 1200" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <radialGradient id="glow" cx="50%" cy="50%" r="40%">
                            <stop offset="0%" style="stop-color:#ffffff;stop-opacity:0.8" />
                            <stop offset="100%" style="stop-color:#ffffff;stop-opacity:0" />
                        </radialGradient>
                    </defs>
                    <g stroke="#ffffff" stroke-width="1" opacity="0.5">
                        <line x1="200" y1="200" x2="400" y2="200" />
                        <line x1="400" y1="200" x2="600" y2="300" />
                        <line x1="200" y1="200" x2="300" y2="500" />
                        <line x1="600" y1="300" x2="800" y2="250" />
                        <line x1="300" y1="500" x2="500" y2="600" />
                        <line x1="800" y1="250" x2="900" y2="500" />
                    </g>
                    <g fill="url(#glow)" opacity="0.9">
                        <circle cx="200" cy="200" r="12" />
                        <circle cx="400" cy="200" r="12" />
                        <circle cx="600" cy="300" r="12" />
                        <circle cx="300" cy="500" r="12" />
                        <circle cx="800" cy="250" r="12" />
                        <circle cx="900" cy="500" r="12" />
                    </g>
                </svg>
            </div>
        </div>

        <!-- RIGHT SIDE -->
        <div class="w-full lg:w-1/2 flex flex-col justify-center items-center px-6 py-8 lg:px-12 bg-white">
            <div class="w-full max-w-md">
                <!-- HEADER -->
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Selamat Datang Kembali!</h1>
                    <p class="text-gray-600 text-sm">Masukkan kredensial Anda untuk masuk ke sistem</p>
                </div>

                <!-- ALERTS -->
                @if (session('status'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-600 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- FORM -->
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- NIP/NIK -->
                    <div>
                        <label for="nomor_identitas" class="block text-sm font-medium text-gray-900 mb-2">NIP / NIK</label>
                        <input id="nomor_identitas" type="text" name="nomor_identitas" value="{{ old('nomor_identitas') }}" required autofocus
                            placeholder="Masukkan NIP atau NIK Anda"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                        <x-input-error :messages="$errors->get('nomor_identitas')" class="mt-2" />
                    </div>

                    <!-- PASSWORD -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-sm font-medium text-gray-900">Kata Sandi</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                    class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                                    Lupa kata sandi?
                                </a>
                            @endif
                        </div>

                        <div class="relative">
                            <input id="password" type="password" name="password" required
                                placeholder="Masukkan kata sandi Anda"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-12" />

                            <!-- Toggle Eye -->
                            <button type="button" id="togglePassword"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-blue-600 focus:outline-none">
                                <svg xmlns="http://www.w3.org/2000/svg" id="eyeIcon" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5
                                           c4.478 0 8.268 2.943 9.542 7
                                           -1.274 4.057-5.064 7-9.542 7
                                           -4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- REMEMBER -->
                    <div class="flex items-center">
                        <input id="remember_me" type="checkbox" name="remember"
                            class="w-4 h-4 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 cursor-pointer" />
                        <label for="remember_me" class="ml-2 text-sm text-gray-600 cursor-pointer">
                            Ingat saya
                        </label>
                    </div>

                    <!-- SUBMIT -->
                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition duration-200 ease-in-out transform hover:scale-[1.02]">
                        Masuk
                    </button>
                </form>

                <!-- FOOTER -->
                <div class="mt-12 pt-6 border-t border-gray-200 text-center text-xs text-gray-500">
                    © 2025 Terminal Data dan Evidensi Perencanaan Pembangunan. All rights reserved.
                </div>
            </div>
        </div>
    </div>

    <!-- JS TOGGLE PASSWORD -->
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const pwd = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                eyeIcon.innerHTML =
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.956 9.956 0 012.042-3.368m3.087-2.742A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.293 5.411M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3l18 18" />';
            } else {
                pwd.type = 'password';
                eyeIcon.innerHTML =
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
            }
        });
    </script>
</body>

</html>
