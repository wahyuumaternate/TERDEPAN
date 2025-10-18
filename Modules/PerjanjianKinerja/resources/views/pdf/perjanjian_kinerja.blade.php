{{-- modules/perjanjian_kinerja/pdf/dokumen.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Perjanjian Kinerja' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;

        }

        /* @page {
            margin: 20mm 25mm;
        } */

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.2 !important;
            color: #000;
            margin: 20mm 25mm;
            /* size: Legal portrait !important; */
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-justify {
            text-align: justify;
        }

        .header-title {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        h2 {
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        h3 {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            margin: 5px 0;
            text-transform: uppercase;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table.info-table {
            margin: 20px 0;
        }

        table.info-table td {
            padding: 3px 0;
            vertical-align: top;
            font-size: 12pt;
            line-height: 1.6;
        }

        table.info-table td:first-child {
            width: 130px;
        }

        table.info-table td:nth-child(2) {
            width: 15px;
            text-align: center;
        }

        table.sasaran-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table.sasaran-table th,
        table.sasaran-table td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: middle;
            font-size: 11pt;
            line-height: 1.4;
        }

        table.sasaran-table th {
            font-weight: bold;
            text-align: center;
            background-color: #fff;
        }

        table.program-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table.program-table th,
        table.program-table td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 11pt;
            vertical-align: middle;
            line-height: 1.4;
        }

        table.program-table th {
            font-weight: bold;
            text-align: center;
            background-color: #fff;
        }

        .ttd-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        table.ttd-table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }

        table.ttd-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 20px;
            font-size: 11pt;
            line-height: 1.2 !important;
        }

        .ttd-space {
            height: 80px;
        }

        .page-break {
            page-break-after: always;
        }

        p {
            margin: 0;
            padding: 0;
            text-align: justify;
            line-height: 1.2 !important;
        }

        p+p {
            margin-top: 10px;

        }

        .intro-text {
            margin-top: 30px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    {{-- HALAMAN 1: PERNYATAAN PERJANJIAN --}}
    {{-- <div class="header-title">PERNYATAAN PERJANJIAN KINERJA</div> --}}

    <h2>PERJANJIAN KINERJA TAHUN {{ $pk->tahun ?? date('Y') }}</h2>

    @if ($pk->pegawai && $pk->pegawai->jabatan)
        <h3>{{ strtoupper($pk->pegawai->jabatan->nama) }}</h3>
    @endif

    <h3>BADAN PERENCANAAN PEMBANGUNAN DAERAH</h3>
    <h3>PROVINSI MALUKU UTARA</h3>

    {{-- PERNYATAAN PEMBUKA --}}
    <div class="intro-text">
        <p>Dalam rangka mewujudkan manajemen pemerintahan yang efektif, transparan dan akuntabel serta berorientasi pada
            hasil, kami yang bertanda tangan di bawah ini:</p>
    </div>

    {{-- INFO PIHAK PERTAMA (PEGAWAI) --}}
    <table class="info-table">
        <tr>
            <td>Nama</td>
            <td>:</td>
            <td>{{ $pk->pegawai->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td>{{ $pk->pegawai->jabatan->nama ?? '-' }} BAPPEDA Provinsi Maluku Utara</td>
        </tr>
        <tr>
            <td colspan="3" style="padding-top: 10px;">selanjutnya disebut <strong>pihak pertama.</strong></td>
        </tr>
    </table>

    {{-- INFO PIHAK KEDUA (ATASAN) --}}
    <table class="info-table">
        <tr>
            <td>Nama</td>
            <td>:</td>
            <td>{{ $pk->atasan->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td>{{ $pk->atasan->jabatan->nama ?? '-' }} BAPPEDA Provinsi Maluku Utara</td>
        </tr>
        <tr>
            <td colspan="3" style="padding-top: 10px;">selaku atasan langsung pihak pertama. Selanjutnya disebut
                <strong>pihak kedua.</strong>
            </td>
        </tr>
    </table>

    {{-- PERNYATAAN ISI --}}
    <div style="margin-top: 20px; line-height: 0.5;">
        <p>Pihak pertama berjanji akan mewujudkan target kinerja yang seharusnya sesuai lampiran perjanjian ini, dalam
            rangka mencapai target kinerja jangka menengah seperti yang telah ditetapkan dalam dokumen perencanaan.
            Keberhasilan dan kegagalan pencapaian target kinerja tersebut menjadi tanggung jawab kami.</p>

        <p>Pihak kedua akan melakukan supervisi yang diperlukan serta akan melakukan evaluasi terhadap capaian kinerja
            dari perjanjian ini dan mengambil tindakan yang diperlukan dalam rangka pemberian penghargaan dan sanksi.
        </p>
    </div>

    {{-- TANDA TANGAN HALAMAN 1 --}}
    <div class="ttd-section">
        @php
            $tanggalTtd = $pk->tanggal_ttd
                ? \Carbon\Carbon::parse($pk->tanggal_ttd)->locale('id')->isoFormat('D MMMM Y')
                : '........................';
        @endphp

        <p class="text-right" style="margin-bottom: 20px;">{{ $pk->tempat_ttd ?? 'Sofifi' }}, {{ $tanggalTtd }}</p>

        <table class="ttd-table">
            <tr>
                <td>
                    <p style="font-size:12px;">PIHAK KEDUA</p>
                    <p style="margin-top: 5px;">{{ strtoupper($pk->atasan->jabatan->nama ?? 'ATASAN LANGSUNG') }}</p>
                    <p>BAPPEDA PROVINSI MALUKU UTARA,</p>
                    <div class="ttd-space"></div>
                    <p style="margin-top: 10px;"><strong>{{ strtoupper($pk->atasan->nama ?? '-') }}</strong></p>
                    <p>{{ $pk->atasan->pangkat ?? 'Pembina Tk. I' }}</p>
                    <p>NIP. {{ $pk->atasan->nomor_identitas ?? '-' }}</p>
                </td>
                <td>
                    <p style="font-size:12px;">PIHAK PERTAMA</p>
                    <p style="margin-top: 5px;">{{ strtoupper($pk->pegawai->jabatan->nama ?? 'PEJABAT') }}</p>
                    <p>BAPPEDA PROVINSI MALUKU UTARA,</p>
                    <div class="ttd-space"></div>
                    <p style="margin-top: 10px;"><strong>{{ strtoupper($pk->pegawai->nama ?? '-') }}</strong></p>
                    <p>{{ $pk->pegawai->pangkat ?? 'Pembina' }}</p>
                    <p>NIP. {{ $pk->pegawai->nomor_identitas ?? '-' }}</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- PAGE BREAK --}}
    <div class="page-break"></div>

    {{-- HALAMAN 2: FORMULIR PERJANJIAN KINERJA --}}
    {{-- <div class="header-title">FORMULIR PERJANJIAN KINERJA</div> --}}

    <h2>PERJANJIAN KINERJA TAHUN {{ $pk->tahun ?? date('Y') }}</h2>
    <h3>BADAN PERENCANAAN PEMBANGUNAN DAERAH</h3>
    <h3>PROVINSI MALUKU UTARA</h3>

    {{-- INFO PEGAWAI --}}
    <table class="info-table">
        <tr>
            <td>Nama</td>
            <td>:</td>
            <td>{{ $pk->pegawai->nama ?? '-' }}</td>
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

    {{-- TABEL SASARAN KINERJA --}}
    <table class="sasaran-table">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 40%;">Sasaran Kinerja</th>
                <th style="width: 35%;">Indikator Kinerja Individu</th>
                <th style="width: 20%;">Target</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Kumpulkan semua indikator yang akan ditampilkan
                $allSasaranData = collect();
                $no = 1;

                if ($pk->sasaran && $pk->sasaran->count() > 0) {
                    foreach ($pk->sasaran as $sasaran) {
                        $indikatorItems = collect();

                        if ($sasaran->indikator && $sasaran->indikator->count() > 0) {
                            foreach ($sasaran->indikator as $indikator) {
                                $indikatorItems->push([
                                    'nama' =>
                                        $indikator->indikator_sasaran ?? 'Indikator ' . ($indikatorItems->count() + 1),
                                    'target_value' => $indikator->target_value ?? 1,
                                    'satuan' => $indikator->satuan ?? 'Persen',
                                ]);
                            }
                        }

                        if ($indikatorItems->count() > 0) {
                            $allSasaranData->push([
                                'sasaran' => $sasaran->sasaran_strategis ?? 'Sasaran ' . $no,
                                'indikator' => $indikatorItems,
                                'rowspan' => $indikatorItems->count(),
                            ]);
                        }
                    }
                }
            @endphp

            @if ($allSasaranData->count() > 0)
                @foreach ($allSasaranData as $sasaranData)
                    @php $firstRow = true; @endphp

                    @foreach ($sasaranData['indikator'] as $indikator)
                        <tr>
                            @if ($firstRow)
                                <td rowspan="{{ $sasaranData['rowspan'] }}"
                                    style="text-align: center; vertical-align: middle;">{{ $no }}</td>
                                <td rowspan="{{ $sasaranData['rowspan'] }}" style="vertical-align: middle;">
                                    {{ $sasaranData['sasaran'] }}
                                </td>
                                @php
                                    $firstRow = false;
                                    $no++;
                                @endphp
                            @endif

                            <td>{{ $indikator['nama'] }}</td>
                            <td style="text-align: center;">{{ $indikator['target_value'] }}
                                {{ $indikator['satuan'] }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @else
                <tr>
                    <td colspan="4" style="text-align: center;">Belum ada data sasaran kinerja</td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- TABEL PROGRAM, KEGIATAN, SUB KEGIATAN --}}
    <table class="program-table">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 28%;">PROGRAM</th>
                <th style="width: 28%;">KEGIATAN</th>
                <th style="width: 24%;">SUB KEGIATAN</th>
                <th style="width: 15%;">ANGGARAN (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
                // Kumpulkan semua program, kegiatan, dan sub kegiatan
                $allProgramData = collect();
                $totalAnggaran = 0;
                $no = 1;

                // Daftar program yang unik berdasarkan ID
                $uniquePrograms = collect();

                if ($pk->sasaran && $pk->sasaran->count() > 0) {
                    foreach ($pk->sasaran as $sasaran) {
                        if ($sasaran->indikator && $sasaran->indikator->count() > 0) {
                            foreach ($sasaran->indikator as $indikator) {
                                if ($indikator->program && $indikator->program->count() > 0) {
                                    foreach ($indikator->program as $program) {
                                        // Skip jika program sudah ada
                                        if ($uniquePrograms->contains('id', $program->id)) {
                                            continue;
                                        }

                                        $uniquePrograms->push(['id' => $program->id]);

                                        $programData = [
                                            'nama' => $program->nama_program,
                                            'kegiatan' => collect(),
                                        ];

                                        if ($program->kegiatan && $program->kegiatan->count() > 0) {
                                            foreach ($program->kegiatan as $kegiatan) {
                                                $kegiatanData = [
                                                    'nama' => $kegiatan->nama_kegiatan,
                                                    'sub_kegiatan' => collect(),
                                                ];

                                                if ($kegiatan->subKegiatan && $kegiatan->subKegiatan->count() > 0) {
                                                    foreach ($kegiatan->subKegiatan as $subKegiatan) {
                                                        $anggaran = $subKegiatan->anggaran ?? 0;
                                                        $totalAnggaran += $anggaran;

                                                        $kegiatanData['sub_kegiatan']->push([
                                                            'nama' => $subKegiatan->nama_sub_kegiatan,
                                                            'anggaran' => $anggaran,
                                                        ]);
                                                    }
                                                }

                                                $programData['kegiatan']->push($kegiatanData);
                                            }
                                        }

                                        $allProgramData->push($programData);
                                    }
                                }
                            }
                        }
                    }
                }
            @endphp

            @if ($allProgramData->count() > 0)
                @foreach ($allProgramData as $programData)
                    @php
                        $totalSubKegiatan = 0;

                        // Hitung total sub kegiatan untuk program ini
                        foreach ($programData['kegiatan'] as $kegiatan) {
                            $totalSubKegiatan += $kegiatan['sub_kegiatan']->count();
                        }

                        $firstRowProgram = true;
                    @endphp

                    @foreach ($programData['kegiatan'] as $kegiatan)
                        @php
                            $totalSubKegiatanKegiatan = $kegiatan['sub_kegiatan']->count();
                            $firstRowKegiatan = true;
                        @endphp

                        @if ($totalSubKegiatanKegiatan > 0)
                            @foreach ($kegiatan['sub_kegiatan'] as $subKegiatan)
                                <tr>
                                    @if ($firstRowProgram)
                                        <td rowspan="{{ $totalSubKegiatan > 0 ? $totalSubKegiatan : 1 }}"
                                            style="text-align: center; vertical-align: middle;">{{ $no }}
                                        </td>
                                        <td rowspan="{{ $totalSubKegiatan > 0 ? $totalSubKegiatan : 1 }}"
                                            style="vertical-align: middle;">
                                            {{ $programData['nama'] }}
                                        </td>
                                        @php $firstRowProgram = false; @endphp
                                    @endif

                                    @if ($firstRowKegiatan)
                                        <td rowspan="{{ $totalSubKegiatanKegiatan }}" style="vertical-align: middle;">
                                            {{ $kegiatan['nama'] }}
                                        </td>
                                        @php $firstRowKegiatan = false; @endphp
                                    @endif

                                    <td>{{ $subKegiatan['nama'] }}</td>
                                    <td style="text-align: right;">
                                        {{ number_format($subKegiatan['anggaran'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                @if ($firstRowProgram)
                                    <td style="text-align: center;">{{ $no }}</td>
                                    <td>{{ $programData['nama'] }}</td>
                                    @php $firstRowProgram = false; @endphp
                                @else
                                    <td colspan="2"></td>
                                @endif

                                <td>{{ $kegiatan['nama'] }}</td>
                                <td>-</td>
                                <td style="text-align: right;">0</td>
                            </tr>
                        @endif
                    @endforeach

                    @php $no++; @endphp
                @endforeach

                <tr>
                    <td colspan="4" style="text-align: center; font-weight: bold;">JUMLAH</td>
                    <td style="text-align: right; font-weight: bold;">{{ number_format($totalAnggaran, 0, ',', '.') }}
                    </td>
                </tr>
            @else
                <tr>
                    <td colspan="5" style="text-align: center;">Belum ada data program/kegiatan</td>
                </tr>

                <tr>
                    <td colspan="4" style="text-align: center; font-weight: bold;">JUMLAH</td>
                    <td style="text-align: right; font-weight: bold;">0</td>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- TANDA TANGAN HALAMAN 2 --}}
    <div class="ttd-section">
        <p class="text-right" style="margin-bottom: 20px;">{{ $pk->tempat_ttd ?? 'Sofifi' }}, {{ $tanggalTtd }}</p>

        <table class="ttd-table">
            <tr>
                <td>
                    <p><strong>PIHAK KEDUA</strong></p>
                    <p style="margin-top: 5px;">{{ strtoupper($pk->atasan->jabatan->nama ?? 'ATASAN LANGSUNG') }}</p>
                    <p>BAPPEDA PROVINSI MALUKU UTARA,</p>
                    <div class="ttd-space"></div>
                    <p style="margin-top: 10px;"><strong>{{ strtoupper($pk->atasan->nama ?? '-') }}</strong></p>
                    <p>{{ $pk->atasan->pangkat ?? 'Pembina Tk. I' }}</p>
                    <p>NIP. {{ $pk->atasan->nomor_identitas ?? '-' }}</p>
                </td>
                <td>
                    <p><strong>PIHAK PERTAMA</strong></p>
                    <p style="margin-top: 5px;">{{ strtoupper($pk->pegawai->jabatan->nama ?? 'PEJABAT') }}</p>
                    <p>BAPPEDA PROVINSI MALUKU UTARA,</p>
                    <div class="ttd-space"></div>
                    <p style="margin-top: 10px;"><strong>{{ strtoupper($pk->pegawai->nama ?? '-') }}</strong></p>
                    <p>{{ $pk->pegawai->pangkat ?? 'Pembina' }}</p>
                    <p>NIP. {{ $pk->pegawai->nomor_identitas ?? '-' }}</p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
