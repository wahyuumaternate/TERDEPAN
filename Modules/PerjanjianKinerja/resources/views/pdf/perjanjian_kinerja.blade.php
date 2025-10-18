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

        @page {
            margin: 20mm 25mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #000;
        }

        /* Text alignment */
        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-justify {
            text-align: justify;
        }

        /* Spacing */
        .mt-20 {
            margin-top: 20px;
        }

        .mt-30 {
            margin-top: 30px;
        }

        .mb-20 {
            margin-bottom: 20px;
        }

        /* Headers */
        h1,
        h2,
        h3 {
            font-weight: bold;
            text-align: center;
        }

        h2 {
            font-size: 14pt;
            margin: 20px 0;
            text-transform: uppercase;
        }

        /* Info Table */
        table.info-table {
            width: 100%;
            margin: 20px 0;
        }

        table.info-table td {
            padding: 3px 0;
            vertical-align: top;
        }

        table.info-table td:first-child {
            width: 150px;
        }

        table.info-table td:nth-child(2) {
            width: 20px;
        }

        /* Sasaran Kinerja Table */
        table.sasaran-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        table.sasaran-table th,
        table.sasaran-table td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
        }

        table.sasaran-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }

        table.sasaran-table td:nth-child(1) {
            width: 5%;
            text-align: center;
        }

        table.sasaran-table td:nth-child(2) {
            width: 35%;
        }

        table.sasaran-table td:nth-child(3) {
            width: 15%;
            text-align: center;
        }

        table.sasaran-table td:nth-child(4) {
            width: 30%;
        }

        table.sasaran-table td:nth-child(5) {
            width: 15%;
            text-align: center;
        }

        /* TTD */
        .ttd-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }

        table.ttd-table {
            width: 100%;
            margin-top: 30px;
        }

        table.ttd-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 20px;
        }

        .ttd-space {
            height: 80px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    {{-- HALAMAN 1: PERNYATAAN PERJANJIAN --}}
    {{-- @if ($pk->template->kop_surat_html)
        {!! $pk->template->kop_surat_html !!}
    @endif --}}

    <h2>PERJANJIAN KINERJA TAHUN {{ $pk->tahun }}</h2>

    @if ($pk->pegawai->jabatan)
        <h3 style="font-size: 12pt;">{{ strtoupper($pk->pegawai->jabatan->nama) }}</h3>
    @endif

    <h3 style="font-size: 12pt;">BADAN PERENCANAAN PEMBANGUNAN DAERAH</h3>
    <h3 style="font-size: 12pt;">PROVINSI MALUKU UTARA</h3>

    {{-- PERNYATAAN PEMBUKA --}}
    <div class="mt-30 text-justify">
        @if ($pk->template->pernyataan_pembuka)
            {!! $pk->template->pernyataan_pembuka !!}
        @else
            <p>Dalam rangka mewujudkan manajemen pemerintahan yang efektif, transparan dan akuntabel serta
                berorientasi pada hasil, kami yang bertanda tangan di bawah ini :</p>
        @endif
    </div>

    {{-- INFO PIHAK PERTAMA (PEGAWAI) --}}
    <table class="info-table mt-20">
        <tr>
            <td>Nama</td>
            <td>:</td>
            <td>{{ $pk->pegawai->nama }}</td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>:</td>
            <td>{{ $pk->pegawai->nomor_identitas }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td>{{ $pk->pegawai->jabatan->nama ?? '-' }}<br>BAPPEDA Provinsi Maluku Utara</td>
        </tr>
        <tr>
            <td colspan="3">selanjutnya disebut <strong>Pihak Pertama</strong></td>
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
            <td>NIP</td>
            <td>:</td>
            <td>{{ $pk->atasan->nomor_identitas ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td>{{ $pk->atasan->jabatan->nama ?? '-' }}<br>BAPPEDA Provinsi Maluku Utara</td>
        </tr>
        <tr>
            <td colspan="3">selaku atasan pihak pertama, selanjutnya disebut <strong>Pihak Kedua</strong></td>
        </tr>
    </table>

    {{-- PERNYATAAN ISI --}}
    <div class="mt-20 text-justify">
        <p style="margin-bottom: 10px;">Pihak pertama berjanji akan mewujudkan target kinerja yang seharusnya sesuai
            lampiran perjanjian ini, dalam rangka mencapai target kinerja jangka menengah seperti yang telah
            ditetapkan dalam dokumen perencanaan. Keberhasilan dan kegagalan pencapaian target kinerja
            tersebut menjadi tanggung jawab kami.</p>

        <p>Pihak kedua akan melakukan supervisi yang diperlukan serta akan melakukan evaluasi terhadap
            capaian kinerja dari perjanjian ini dan mengambil tindakan yang diperlukan dalam rangka
            pemberian penghargaan dan sanksi.</p>
    </div>

    {{-- TANDA TANGAN HALAMAN 1 --}}
    <div class="ttd-section">
        @php
            $tanggalTtd = $pk->tanggal_ttd
                ? \Carbon\Carbon::parse($pk->tanggal_ttd)->locale('id')->isoFormat('D MMMM Y')
                : '........................';
        @endphp

        <p class="text-right">{{ $pk->tempat_ttd ?? 'Sofifi' }}, {{ $tanggalTtd }}</p>

        <table class="ttd-table">
            <tr>
                <td>
                    <p><strong>Pihak Kedua,</strong></p>
                    <p>{{ $pk->atasan->jabatan->nama ?? 'Atasan Langsung' }}</p>
                    <p>BAPPEDA Provinsi Maluku Utara</p>
                    <div class="ttd-space"></div>
                    <p><strong><u>{{ $pk->atasan->nama ?? '-' }}</u></strong></p>
                    <p>{{ $pk->atasan->pangkat ?? 'Pembina Tk. I' }}</p>
                    <p>NIP. {{ $pk->atasan->nomor_identitas ?? '-' }}</p>
                </td>
                <td>
                    <p><strong>Pihak Pertama,</strong></p>
                    <p>{{ $pk->pegawai->jabatan->nama ?? 'Pejabat' }}</p>
                    <p>BAPPEDA Provinsi Maluku Utara</p>
                    <div class="ttd-space"></div>
                    <p><strong><u>{{ $pk->pegawai->nama }}</u></strong></p>
                    <p>{{ $pk->pegawai->pangkat ?? 'Pembina' }}</p>
                    <p>NIP. {{ $pk->pegawai->nomor_identitas }}</p>
                </td>
            </tr>
        </table>
    </div>

    {{-- PAGE BREAK --}}
    <div class="page-break"></div>

    {{-- HALAMAN 2: TABEL SASARAN KINERJA --}}
    {{-- @if ($pk->template->kop_surat_html)
        {!! $pk->template->kop_surat_html !!}
    @endif --}}

    <h2>PERJANJIAN KINERJA TAHUN {{ $pk->tahun }}</h2>
    <h3 style="font-size: 12pt;">BADAN PERENCANAAN PEMBANGUNAN DAERAH</h3>
    <h3 style="font-size: 12pt;">PROVINSI MALUKU UTARA</h3>

    {{-- INFO PEGAWAI --}}
    <table class="info-table mt-20">
        <tr>
            <td>Nama</td>
            <td>:</td>
            <td>{{ $pk->pegawai->nama }}</td>
        </tr>
        <tr>
            <td>NIP</td>
            <td>:</td>
            <td>{{ $pk->pegawai->nomor_identitas }}</td>
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
                <th>No.</th>
                <th>Sasaran Kinerja</th>
                <th>Aspek</th>
                <th>Indikator Kinerja Individu</th>
                <th>Target</th>
            </tr>
        </thead>
        <tbody>
            @php
                $nomorSasaran = 1;
            @endphp
            @foreach ($pk->sasaran as $sasaran)
                @foreach ($sasaran->indikator as $indikator)
                    @foreach ($indikator->program as $program)
                        @foreach ($program->kegiatan as $kegiatan)
                            @foreach ($kegiatan->subKegiatan as $indexSub => $subKegiatan)
                                {{-- Kuantitas --}}
                                <tr>
                                    @if ($indexSub === 0)
                                        <td rowspan="3">{{ $nomorSasaran }}</td>
                                        <td rowspan="3">{{ $subKegiatan->nama_sub_kegiatan }}</td>
                                    @endif
                                    <td>Kuantitas</td>
                                    <td>{{ $subKegiatan->nama_sub_kegiatan }}</td>
                                    <td>{{ number_format($subKegiatan->target_value, 0, ',', '.') }}
                                        {{ $subKegiatan->satuan }}</td>
                                </tr>
                                {{-- Kualitas --}}
                                <tr>
                                    <td>Kualitas</td>
                                    <td>{{ $subKegiatan->nama_sub_kegiatan }} yang disusun sesuai regulasi yang
                                        berlaku</td>
                                    <td>{{ number_format($subKegiatan->target_value, 0, ',', '.') }}
                                        {{ $subKegiatan->satuan }}</td>
                                </tr>
                                {{-- Waktu --}}
                                <tr>
                                    <td>Waktu</td>
                                    <td>Tingkat Ketepatan Waktu {{ $subKegiatan->nama_sub_kegiatan }}</td>
                                    <td>100%</td>
                                </tr>
                                @php
                                    $nomorSasaran++;
                                @endphp
                            @endforeach
                        @endforeach
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
    </table>

    {{-- TANDA TANGAN HALAMAN 2 --}}
    <div class="ttd-section">
        <p class="text-right">{{ $pk->tempat_ttd ?? 'Sofifi' }}, {{ $tanggalTtd }}</p>

        <table class="ttd-table">
            <tr>
                <td>
                    <p><strong>Pihak Kedua,</strong></p>
                    <p>{{ $pk->atasan->jabatan->nama ?? 'Atasan Langsung' }}</p>
                    <p>BAPPEDA Provinsi Maluku Utara</p>
                    <div class="ttd-space"></div>
                    <p><strong><u>{{ $pk->atasan->nama ?? '-' }}</u></strong></p>
                    <p>{{ $pk->atasan->pangkat ?? 'Pembina Tk. I' }}</p>
                    <p>NIP. {{ $pk->atasan->nomor_identitas ?? '-' }}</p>
                </td>
                <td>
                    <p><strong>Pihak Pertama,</strong></p>
                    <p>{{ $pk->pegawai->jabatan->nama ?? 'Pejabat' }}</p>
                    <p>BAPPEDA Provinsi Maluku Utara</p>
                    <div class="ttd-space"></div>
                    <p><strong><u>{{ $pk->pegawai->nama }}</u></strong></p>
                    <p>{{ $pk->pegawai->pangkat ?? 'Pembina' }}</p>
                    <p>NIP. {{ $pk->pegawai->nomor_identitas }}</p>
                </td>
            </tr>
        </table>
    </div>
</body>

</html>
