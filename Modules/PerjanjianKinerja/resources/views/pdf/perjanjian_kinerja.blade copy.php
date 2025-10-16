<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        /* ===== PAGE MARGIN SETTINGS ===== */

        @page {
            margin: 20mm 25mm 20mm 25mm;
            size: A4 portrait;
        }

        /* Method 2: Print media query */
        @media print {
            body {
                margin: 20mm 25mm 20mm 25mm;
            }
        }

        /* ===== RESET & BASE STYLES ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            /* Method 3: Body margin sebagai fallback */
            margin: 0;
            padding: 0;
        }

        /* Container wrapper untuk fallback margin jika @page tidak bekerja */
        .page-container {
            /* Fallback padding - akan diabaikan jika @page margin sudah bekerja */
            padding: 0;
        }

        .page-break {
            page-break-after: always;
            clear: both;
        }

        /* ===== HALAMAN PERNYATAAN ===== */

        .header-pernyataan {
            text-align: center;
            margin-bottom: 25px;
        }

        .header-pernyataan h1 {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 8px;
            text-decoration: underline;
            line-height: 1.3;
            letter-spacing: 0.3px;
        }

        .header-pernyataan h2 {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .header-pernyataan h3 {
            font-size: 11pt;
            font-weight: bold;
            line-height: 1.3;
            margin-bottom: 3px;
        }

        /* Content Pernyataan */
        .content-pernyataan {
            text-align: justify;
            margin-bottom: 15px;
        }

        .content-pernyataan p {
            margin-bottom: 12px;
            line-height: 1.5;
        }

        /* Info Pihak */
        .pihak-label {
            margin-top: 8px;
            margin-bottom: 6px;
            font-weight: normal;
            line-height: 1.4;
        }

        .info-pihak {
            margin-left: 35px;
            margin-bottom: 12px;
        }

        .info-pihak table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-pihak td {
            padding: 2px 0;
            vertical-align: top;
            line-height: 1.4;
        }

        .info-pihak td:first-child {
            width: 110px;
            font-weight: normal;
        }

        .info-pihak td:nth-child(2) {
            width: 20px;
            text-align: center;
        }

        .info-pihak td:nth-child(3) {
            width: auto;
        }

        /* Tanda Tangan */
        .signature-section {
            margin-top: 30px;
        }

        .signature-date {
            text-align: right;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        .signature-container {
            width: 100%;
            display: table;
        }

        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 10px;
        }

        .signature-box p {
            margin-bottom: 3px;
            line-height: 1.3;
        }

        .signature-space {
            height: 50px;
            margin: 5px 0;
        }

        .signature-name {
            text-decoration: underline;
            font-weight: bold;
            margin-top: 3px;
            margin-bottom: 3px;
            line-height: 1.3;
        }

        /* ===== HALAMAN TABEL ===== */

        .header-tabel {
            text-align: center;
            margin-bottom: 20px;
        }

        .header-tabel h1 {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 8px;
            line-height: 1.3;
            letter-spacing: 0.3px;
        }

        .header-tabel h2 {
            font-size: 11pt;
            font-weight: bold;
            line-height: 1.3;
            margin-bottom: 3px;
        }

        /* Info Pegawai */
        .info-pegawai {
            margin-bottom: 15px;
        }

        .info-pegawai table {
            border-collapse: collapse;
        }

        .info-pegawai td {
            padding: 2px 0;
            vertical-align: top;
            line-height: 1.4;
        }

        .info-pegawai td:first-child {
            width: 110px;
            font-weight: normal;
        }

        .info-pegawai td:nth-child(2) {
            width: 20px;
            text-align: center;
        }

        .info-pegawai td:nth-child(3) {
            width: auto;
        }

        /* Tabel Sasaran */
        .tabel-sasaran {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .tabel-sasaran th,
        .tabel-sasaran td {
            border: 1px solid #000;
            padding: 5px 4px;
            text-align: left;
            vertical-align: top;
            line-height: 1.3;
            font-size: 10pt;
        }

        .tabel-sasaran th {
            background-color: #e8e8e8;
            font-weight: bold;
            text-align: center;
            line-height: 1.2;
            padding: 8px 4px;
        }

        .tabel-sasaran td:first-child {
            text-align: center;
            width: 25px;
            font-weight: normal;
        }

        .tabel-sasaran .col-sasaran {
            width: 28%;
        }

        .tabel-sasaran .col-aspek {
            width: 11%;
            text-align: center;
        }

        .tabel-sasaran .col-indikator {
            width: 36%;
        }

        .tabel-sasaran .col-target {
            width: 15%;
            text-align: center;
        }

        /* Prevent orphans */
        .tabel-sasaran tr {
            page-break-inside: avoid;
        }

        /* Styling untuk empty state */
        .tabel-sasaran .empty-cell {
            text-align: center;
            padding: 20px 10px;
            font-style: italic;
            color: #555;
        }
    </style>
</head>

<body>
    <div class="page-container">
        {{-- ========== HALAMAN 1: PERNYATAAN ========== --}}
        <div class="header-pernyataan">
            <h1>PERJANJIAN KINERJA TAHUN {{ $pk->tahun }}</h1>
            <h2>{{ strtoupper($pk->pegawai->jabatan->nama ?? 'JABATAN') }}</h2>
            <h3>BADAN PERENCANAAN PEMBANGUNAN DAERAH</h3>
            <h3>PROVINSI MALUKU UTARA</h3>
        </div>

        <div class="content-pernyataan">
            <p>
                Dalam rangka mewujudkan manajemen pemerintahan yang efektif, transparan dan akuntabel serta
                berorientasi pada hasil, kami yang bertanda tangan di bawah ini :
            </p>

            {{-- Info Pihak Pertama --}}
            <p class="pihak-label">Pihak Pertama</p>
            <div class="info-pihak">
                <table>
                    <tr>
                        <td>Nama</td>
                        <td>:</td>
                        <td><strong>{{ $pk->pegawai->nama }}</strong></td>
                    </tr>
                    <tr>
                        <td>NIP</td>
                        <td>:</td>
                        <td>{{ $pk->pegawai->nomor_identitas ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Jabatan</td>
                        <td>:</td>
                        <td>{{ $pk->pegawai->jabatan->nama ?? '-' }}<br>BAPPEDA Provinsi Maluku Utara</td>
                    </tr>
                </table>
            </div>

            {{-- Info Pihak Kedua --}}
            <p class="pihak-label">Pihak Kedua</p>
            <div class="info-pihak">
                <table>
                    <tr>
                        <td>Nama</td>
                        <td>:</td>
                        <td><strong>{{ $pk->atasan->nama }}</strong></td>
                    </tr>
                    <tr>
                        <td>NIP</td>
                        <td>:</td>
                        <td>{{ $pk->atasan->nomor_identitas ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Jabatan</td>
                        <td>:</td>
                        <td>{{ $pk->atasan->jabatan->nama ?? '-' }}<br>BAPPEDA Provinsi Maluku Utara</td>
                    </tr>
                </table>
            </div>

            <p style="margin-top: 5px;">
                Selaku atasan Pihak Pertama.
            </p>

            <p style="margin-top: 15px;">
                Pihak Pertama berjanji akan mewujudkan target kinerja yang seharusnya sesuai lampiran
                perjanjian ini, dalam rangka mencapai target kinerja jangka menengah seperti yang telah
                ditetapkan dalam dokumen perencanaan. Keberhasilan dan kegagalan pencapaian target kinerja
                tersebut menjadi tanggung jawab kami.
            </p>

            <p>
                Pihak Kedua akan melakukan supervisi yang diperlukan serta akan melakukan evaluasi terhadap
                capaian kinerja dari perjanjian ini dan mengambil tindakan yang diperlukan dalam rangka
                pemberian penghargaan dan sanksi.
            </p>
        </div>

        {{-- Tanda Tangan --}}
        <div class="signature-section">
            <p class="signature-date">
                {{ $pk->tempat_ttd }},
                {{ $pk->tanggal_ttd ? \Carbon\Carbon::parse($pk->tanggal_ttd)->format('d F Y') : '________________' }}
            </p>

            <div class="signature-container">
                <div class="signature-box">
                    <p><strong>Pihak Kedua,</strong></p>
                    <p>{{ $pk->atasan->jabatan->nama ?? 'Atasan' }}</p>
                    <p>BAPPEDA Provinsi Maluku Utara</p>
                    <div class="signature-space"></div>
                    <p class="signature-name">{{ $pk->atasan->nama }}</p>
                    <p>{{ $pk->atasan->pangkat ?? '' }}</p>
                    <p>NIP. {{ $pk->atasan->nomor_identitas ?? '-' }}</p>
                </div>

                <div class="signature-box">
                    <p><strong>Pihak Pertama,</strong></p>
                    <p>{{ $pk->pegawai->jabatan->nama ?? 'Pegawai' }}</p>
                    <p>BAPPEDA Provinsi Maluku Utara</p>
                    <div class="signature-space"></div>
                    <p class="signature-name">{{ $pk->pegawai->nama }}</p>
                    <p>{{ $pk->pegawai->pangkat ?? '' }}</p>
                    <p>NIP. {{ $pk->pegawai->nomor_identitas ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Page Break --}}
        <div class="page-break"></div>

        {{-- ========== HALAMAN 2: TABEL SASARAN KINERJA ========== --}}
        <div class="header-tabel">
            <h1>PERJANJIAN KINERJA TAHUN {{ $pk->tahun }}</h1>
            <h2>BADAN PERENCANAAN PEMBANGUNAN DAERAH</h2>
            <h2>PROVINSI MALUKU UTARA</h2>
        </div>

        <div class="info-pegawai">
            <table>
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td><strong>{{ $pk->pegawai->nama }}</strong></td>
                </tr>
                <tr>
                    <td>NIP</td>
                    <td>:</td>
                    <td>{{ $pk->pegawai->nomor_identitas ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Jabatan</td>
                    <td>:</td>
                    <td>{{ $pk->pegawai->jabatan->nama ?? '-' }}</td>
                </tr>
            </table>
        </div>

        {{-- Tabel Sasaran Kinerja --}}
        <table class="tabel-sasaran">
            <thead>
                <tr>
                    <th>No.</th>
                    <th class="col-sasaran">Sasaran Kinerja</th>
                    <th class="col-aspek">Aspek</th>
                    <th class="col-indikator">Indikator Kinerja Individu</th>
                    <th class="col-target">Target</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pk->sasaran as $sasaranIndex => $sasaran)
                    @php
                        $totalIndikator = $sasaran->indikator->count();
                        if ($totalIndikator == 0) {
                            $rowspan = 1;
                        } else {
                            $rowspan = $totalIndikator * 3; // 3 aspek per indikator
                        }
                    @endphp

                    @if ($totalIndikator > 0)
                        @foreach ($sasaran->indikator as $indIndex => $indikator)
                            {{-- Kuantitas --}}
                            <tr>
                                @if ($indIndex === 0)
                                    <td rowspan="{{ $rowspan }}">{{ $sasaranIndex + 1 }}</td>
                                    <td rowspan="{{ $rowspan }}">{{ $sasaran->sasaran_strategis }}</td>
                                @endif
                                <td><strong>Kuantitas</strong></td>
                                <td>{{ $indikator->indikator_sasaran }}</td>
                                <td>{{ number_format($indikator->target_value) }}<br>{{ $indikator->satuan }}
                                </td>
                            </tr>

                            {{-- Kualitas --}}
                            <tr>
                                <td><strong>Kualitas</strong></td>
                                <td>{{ $indikator->indikator_sasaran }} yang disusun sesuai regulasi yang berlaku</td>
                                <td>{{ number_format($indikator->target_value) }}<br>{{ $indikator->satuan }}
                                </td>
                            </tr>

                            {{-- Waktu --}}
                            <tr>
                                <td><strong>Waktu</strong></td>
                                <td>Tingkat ketepatan waktu {{ $indikator->indikator_sasaran }}</td>
                                <td>100%</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>{{ $sasaranIndex + 1 }}</td>
                            <td>{{ $sasaran->sasaran_strategis }}</td>
                            <td colspan="3" class="empty-cell">Belum ada indikator</td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="5" class="empty-cell">
                            Belum ada sasaran kinerja yang ditambahkan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Tanda Tangan Halaman Tabel --}}
        <div class="signature-section" style="margin-top: 25px;">
            <p class="signature-date">
                {{ $pk->tempat_ttd }},
                {{ $pk->tanggal_ttd ? \Carbon\Carbon::parse($pk->tanggal_ttd)->format('d F Y') : '________________' }}
            </p>

            <div class="signature-container">
                <div class="signature-box">
                    <p><strong>Pihak Kedua,</strong></p>
                    <p>{{ $pk->atasan->jabatan->nama ?? 'Atasan' }}</p>
                    <p>BAPPEDA Provinsi Maluku Utara</p>
                    <div class="signature-space"></div>
                    <p class="signature-name">{{ $pk->atasan->nama }}</p>
                    <p>{{ $pk->atasan->pangkat ?? '' }}</p>
                    <p>NIP. {{ $pk->atasan->nomor_identitas ?? '-' }}</p>
                </div>

                <div class="signature-box">
                    <p><strong>Pihak Pertama,</strong></p>
                    <p>{{ $pk->pegawai->jabatan->nama ?? 'Pegawai' }}</p>
                    <p>BAPPEDA Provinsi Maluku Utara</p>
                    <div class="signature-space"></div>
                    <p class="signature-name">{{ $pk->pegawai->nama }}</p>
                    <p>{{ $pk->pegawai->pangkat ?? '' }}</p>
                    <p>NIP. {{ $pk->pegawai->nomor_identitas ?? '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
