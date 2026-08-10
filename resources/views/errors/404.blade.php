<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">

    <title>404 - Halaman Tidak Ditemukan | TERDEPAN</title>

    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:400,600,700,800"
        rel="stylesheet">

    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">

    <style>
        body {
            font-family: "Nunito", sans-serif;
            background: #f6f9ff;
            color: #444444;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .error-code {
            font-size: 6rem;
            font-weight: 800;
            color: #4154f1;
            line-height: 1;
        }

        @media (min-width: 576px) {
            .error-code {
                font-size: 8rem;
            }
        }

        .error-icon {
            font-size: 2.5rem;
            color: #4154f1;
        }
    </style>
</head>

<body>
    <div class="text-center" style="max-width: 480px;">
        <div class="error-icon mb-2">
            <i class="bi bi-signpost-2"></i>
        </div>
        <div class="error-code mb-2">404</div>
        <h1 class="h4 fw-bold mb-2" style="color: #012970;">Halaman Tidak Ditemukan</h1>
        <p class="text-muted mb-4">
            Halaman yang Anda cari mungkin sudah dipindahkan, dihapus, atau alamatnya salah ketik.
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <button type="button" class="btn btn-outline-secondary px-4" onclick="kembaliKeSebelumnya()">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </button>
            <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}" class="btn btn-primary px-4">
                <i class="bi bi-house-door me-1"></i>Ke Dashboard
            </a>
        </div>
    </div>

    <script>
        function kembaliKeSebelumnya() {
            if (document.referrer && document.referrer !== window.location.href) {
                window.history.back();
            } else {
                window.location.href = "{{ Route::has('dashboard') ? route('dashboard') : url('/') }}";
            }
        }
    </script>
</body>

</html>
