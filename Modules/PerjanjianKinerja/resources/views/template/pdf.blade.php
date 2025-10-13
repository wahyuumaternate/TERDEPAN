<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $template->kode_template }} - {{ $template->nama_template }}</title>
    <style>
        @page {
            margin: 2cm 2cm 2cm 2cm;
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-weight: bold;
            margin: 0;
            padding: 0;
        }

        h1 {
            font-size: 16pt;
            text-align: center;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 14pt;
            margin-top: 20px;
            margin-bottom: 10px;
        }

        h3 {
            font-size: 13pt;
            margin-top: 15px;
            margin-bottom: 8px;
        }

        p {
            margin: 5px 0;
            text-align: justify;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        table.info-table td {
            padding: 5px;
            vertical-align: top;
        }

        table.info-table td:first-child {
            width: 150px;
        }

        table.bordered {
            border: 1px solid #000;
        }

        table.bordered th,
        table.bordered td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        table.bordered th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .section-divider {
            border-bottom: 2px solid #000;
            margin: 20px 0;
        }

        .page-break {
            page-break-after: always;
        }

        .signature-section {
            margin-top: 40px;
        }

        .signature-box {
            text-align: center;
            display: inline-block;
            width: 45%;
            vertical-align: top;
        }

        .signature-box.left {
            float: left;
        }

        .signature-box.right {
            float: right;
        }

        .signature-line {
            margin-top: 60px;
            margin-bottom: 5px;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100pt;
            color: rgba(0, 0, 0, 0.05);
            z-index: -1;
        }

        .header-info {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 10pt;
            font-weight: bold;
            border-radius: 3px;
            margin-right: 5px;
        }

        .badge-primary {
            background-color: #0d6efd;
            color: white;
        }

        .badge-success {
            background-color: #198754;
            color: white;
        }

        .badge-info {
            background-color: #0dcaf0;
            color: black;
        }

        .badge-warning {
            background-color: #ffc107;
            color: black;
        }

        code {
            background-color: #f1f3f5;
            padding: 2px 5px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 10pt;
        }

        .section-item {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            background-color: #f8f9fa;
        }

        .section-header {
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #0d6efd;
        }

        .content-preview {
            background-color: white;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 3px;
            margin-top: 10px;
            font-size: 10pt;
            color: #666;
        }
    </style>
</head>

<body>
    <!-- Watermark -->
    <div class="watermark">TEMPLATE</div>

    <!-- Header / Cover Page -->
    <div class="text-center" style="margin-top: 100px; margin-bottom: 50px;">
        <h1 style="font-size: 20pt; margin-bottom: 10px;">TEMPLATE PERJANJIAN KINERJA</h1>
        <h2 style="font-size: 16pt; margin-bottom: 30px;">{{ strtoupper($template->nama_template) }}</h2>

        <div class="header-info" style="text-align: left; margin-top: 50px;">
            <table class="info-table">
                <tr>
                    <td><strong>Kode Template</strong></td>
                    <td>: <code>{{ $template->kode_template }}</code></td>
                </tr>
                <tr>
                    <td><strong>Jabatan</strong></td>
                    <td>: {{ $template->jabatan->nama }}</td>
                </tr>
                <tr>
                    <td><strong>Tahun</strong></td>
                    <td>: {{ $template->tahun }}</td>
                </tr>
                <tr>
                    <td><strong>Ukuran Halaman</strong></td>
                    <td>: {{ $template->page_size }}</td>
                </tr>
                <tr>
                    <td><strong>Orientasi</strong></td>
                    <td>: {{ $template->orientation }}</td>
                </tr>
                <tr>
                    <td><strong>Versi</strong></td>
                    <td>: {{ $template->versi }}</td>
                </tr>
                <tr>
                    <td><strong>Status</strong></td>
                    <td>:
                        @if ($template->is_active)
                            <span class="badge badge-success">AKTIF</span>
                        @else
                            <span class="badge" style="background-color: #6c757d; color: white;">TIDAK AKTIF</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td><strong>Total Sections</strong></td>
                    <td>: {{ $template->sections->count() }} sections</td>
                </tr>
            </table>
        </div>

        <p style="margin-top: 50px; font-size: 10pt; color: #666;">
            Dibuat: {{ $template->created_at->format('d F Y H:i') }}<br>
            Diupdate: {{ $template->updated_at->format('d F Y H:i') }}
        </p>
    </div>

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- Kop Surat Preview -->
    @if ($template->kop_surat_html)
        <h2>1. KOP SURAT</h2>
        <div style="border: 2px solid #000; padding: 15px; margin-bottom: 20px;">
            {!! $template->kop_surat_html !!}
        </div>
    @endif

    <!-- Header Preview -->
    @if ($template->header_template)
        <h2>2. HEADER DOKUMEN</h2>
        <div style="border: 1px solid #dee2e6; padding: 15px; margin-bottom: 20px; background-color: #f8f9fa;">
            {!! $template->header_template !!}
        </div>
    @endif

    <!-- Pernyataan Pembuka -->
    @if ($template->pernyataan_pembuka)
        <h2>3. PERNYATAAN PEMBUKA</h2>
        <div style="border: 1px solid #dee2e6; padding: 15px; margin-bottom: 20px; background-color: #f8f9fa;">
            {!! $template->pernyataan_pembuka !!}
        </div>
    @endif

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- Sections Detail -->
    <h2>DAFTAR SECTIONS TEMPLATE</h2>
    <p style="margin-bottom: 20px;">Template ini memiliki <strong>{{ $template->sections->count() }}</strong> sections
        yang akan digunakan dalam pembuatan dokumen Perjanjian Kinerja.</p>

    @foreach ($template->sections as $index => $section)
        <div class="section-item">
            <div class="section-header">
                {{ $section->urutan }}. {{ strtoupper($section->section_name) }}
            </div>

            <table class="info-table" style="margin: 0;">
                <tr>
                    <td width="150"><strong>Section Code</strong></td>
                    <td>: <code>{{ $section->section_code }}</code></td>
                </tr>
                <tr>
                    <td><strong>Section Type</strong></td>
                    <td>:
                        <span
                            class="badge 
                            @if ($section->section_type == 'static') badge-info
                            @elseif($section->section_type == 'dynamic') badge-warning
                            @elseif($section->section_type == 'table') badge-success
                            @else badge-primary @endif">
                            {{ strtoupper($section->section_type) }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Required</strong></td>
                    <td>: {{ $section->is_required ? 'Ya' : 'Tidak' }}</td>
                </tr>
            </table>

            @if ($section->content_template)
                <div class="content-preview">
                    <strong>Content Template:</strong><br>
                    <pre style="font-size: 9pt; white-space: pre-wrap; word-wrap: break-word;">{{ $section->content_template }}</pre>
                </div>
            @endif
        </div>

        @if (($index + 1) % 2 == 0 && !$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- Pernyataan Penutup -->
    @if ($template->pernyataan_penutup)
        <h2>PERNYATAAN PENUTUP</h2>
        <div style="border: 1px solid #dee2e6; padding: 15px; margin-bottom: 20px; background-color: #f8f9fa;">
            {!! $template->pernyataan_penutup !!}
        </div>
    @endif

    <!-- Footer / TTD -->
    @if ($template->footer_template)
        <h2>AREA TANDA TANGAN</h2>
        <div style="border: 1px solid #dee2e6; padding: 15px; margin-bottom: 20px; background-color: #f8f9fa;">
            {!! $template->footer_template !!}
        </div>
    @endif

    <!-- Footer Document -->
    <div
        style="position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 9pt; color: #666; border-top: 1px solid #dee2e6; padding-top: 10px;">
        <p>
            {{ $template->kode_template }} - {{ $template->nama_template }}<br>
            Dicetak pada: {{ now()->format('d F Y H:i') }} WIT
        </p>
    </div>
</body>

</html>
