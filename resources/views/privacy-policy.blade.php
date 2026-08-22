<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Kebijakan Privasi Sistem TERDEPAN BAPPEDA Maluku Utara">
    <title>Kebijakan Privasi - TERDEPAN BAPPEDA MALUT</title>

    <link href="{{ asset('favicon/favicon.ico') }}" rel="icon">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50 text-slate-800">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-4xl items-center justify-between gap-4 px-6 py-5">
            <a href="{{ route('login') }}" class="flex items-center gap-3">
                <img src="{{ asset('assets/img/logo.webp') }}" alt="Logo TERDEPAN" class="h-12 w-auto">
                <div>
                    <p class="font-bold tracking-wide text-slate-900">TERDEPAN</p>
                    <p class="text-xs text-slate-500">BAPPEDA Maluku Utara</p>
                </div>
            </a>
            <a href="{{ route('login') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">
                Kembali ke Login
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-4xl px-6 py-10 sm:py-14">
        <article class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-10">
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-700">Dokumen Privasi</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Kebijakan Privasi</h1>
            <p class="mt-3 text-sm text-slate-500">Berlaku efektif: 22 Agustus 2026</p>

            <div class="mt-8 space-y-8 leading-7 text-slate-700">
                <section>
                    <h2 class="text-xl font-semibold text-slate-900">1. Tentang kebijakan ini</h2>
                    <p class="mt-3">Kebijakan ini menjelaskan bagaimana Sistem TERDEPAN (Terminal Data dan e-Kinerja Perencana) yang dikelola oleh BAPPEDA Provinsi Maluku Utara mengumpulkan, menggunakan, menyimpan, dan melindungi informasi pengguna.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">2. Informasi yang kami kelola</h2>
                    <p class="mt-3">Untuk menyediakan layanan, kami dapat mengelola:</p>
                    <ul class="mt-3 list-disc space-y-2 pl-6">
                        <li>data akun seperti nama, alamat email, jabatan, unit kerja, dan identitas kepegawaian;</li>
                        <li>nomor telepon atau WhatsApp yang diberikan pengguna atau administrator untuk keperluan notifikasi;</li>
                        <li>data aktivitas yang diperlukan untuk keamanan, audit, dan pengoperasian sistem; dan</li>
                        <li>isi dokumen atau data kinerja yang pengguna masukkan ke dalam sistem.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">3. Notifikasi WhatsApp</h2>
                    <p class="mt-3">Jika fitur diaktifkan, TERDEPAN dapat mengirim notifikasi layanan melalui WhatsApp berdasarkan persetujuan atau penugasan resmi yang berlaku. Notifikasi dapat berisi pengingat, status tugas, informasi akun, atau pemberitahuan operasional terkait layanan TERDEPAN.</p>
                    <p class="mt-3">Pengiriman dilakukan menggunakan WhatsApp Business Platform atau WhatsApp Cloud API milik Meta Platforms, Inc. Nomor telepon dan isi pesan yang diperlukan untuk pengiriman dapat diproses oleh Meta sesuai dengan kebijakan privasi dan ketentuan WhatsApp yang berlaku. TERDEPAN tidak menjual data pribadi pengguna untuk iklan.</p>
                    <p class="mt-3">Pengguna dapat menghentikan notifikasi WhatsApp dengan membalas <strong>STOP</strong> apabila tersedia pada pesan, menggunakan pengaturan notifikasi di TERDEPAN, atau menghubungi pengelola sistem. Penghentian notifikasi tidak menghapus akun atau mengubah kewajiban kedinasan pengguna.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">4. Tujuan penggunaan</h2>
                    <p class="mt-3">Informasi digunakan untuk autentikasi, pengelolaan data dan kinerja perencana, pengiriman notifikasi yang diminta, peningkatan keamanan, pencatatan audit, serta pemenuhan kewajiban administratif dan hukum yang berlaku.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">5. Penyimpanan dan perlindungan</h2>
                    <p class="mt-3">Kami menerapkan pengamanan teknis dan administratif yang wajar untuk melindungi informasi dari akses, perubahan, pengungkapan, atau pemusnahan yang tidak sah. Data disimpan selama diperlukan untuk tujuan layanan, audit, dan kewajiban hukum, kemudian dihapus atau dianonimkan sesuai kebijakan retensi yang berlaku.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">6. Hak dan permintaan pengguna</h2>
                    <p class="mt-3">Sepanjang diizinkan oleh peraturan yang berlaku, pengguna dapat meminta akses, koreksi, atau informasi mengenai pemrosesan datanya, serta menyampaikan keberatan atau permintaan penghentian notifikasi. Permintaan dapat disampaikan kepada pengelola TERDEPAN melalui kanal resmi BAPPEDA Provinsi Maluku Utara dengan menyertakan identitas dan rincian permintaan yang cukup.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">7. Perubahan kebijakan</h2>
                    <p class="mt-3">Kebijakan ini dapat diperbarui jika layanan, peraturan, atau integrasi pihak ketiga berubah. Versi terbaru akan dipublikasikan pada halaman ini beserta tanggal berlakunya.</p>
                </section>
            </div>
        </article>
    </main>

    <footer class="pb-10 text-center text-sm text-slate-500">
        &copy; {{ date('Y') }} TERDEPAN - BAPPEDA Provinsi Maluku Utara
    </footer>
</body>

</html>