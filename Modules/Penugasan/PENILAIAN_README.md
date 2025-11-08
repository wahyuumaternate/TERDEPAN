# 📊 Sistem Penilaian Tugas Harian

## Skema Penilaian

### Bobot Penilaian

-   **Waktu**: 80% (dihitung otomatis oleh sistem)
-   **Kualitas**: 20% (diinput oleh atasan saat validasi)

### Kategori Penilaian Waktu (Otomatis)

| Keterlambatan                | Nilai | Penalti |
| ---------------------------- | ----- | ------- |
| Tepat waktu atau lebih cepat | 100   | -       |
| Terlambat 1 hari             | 90    | -10     |
| Terlambat 2 hari             | 80    | -20     |
| Terlambat 3 hari             | 70    | -30     |
| Terlambat 4+ hari            | 50    | -50     |

### Kategori Penilaian Kualitas (Input Atasan)

-   Rentang: 0 - 100
-   Bebas ditentukan oleh atasan berdasarkan kualitas pekerjaan

### Rumus Nilai Akhir

```
Nilai Akhir = (Nilai Waktu × 80%) + (Nilai Kualitas × 20%)
```

**Contoh:**

-   Nilai Waktu: 90 (terlambat 1 hari)
-   Nilai Kualitas: 85 (dari atasan)
-   Nilai Akhir = (90 × 0.8) + (85 × 0.2) = 72 + 17 = **89**

---

## 🛠️ Implementasi

### 1. Helper Class: `PenilaianHelper`

File: `Modules/Penugasan/app/Helpers/PenilaianHelper.php`

#### Method yang Tersedia:

**a. Hitung Nilai Waktu**

```php
use Modules\Penugasan\Helpers\PenilaianHelper;

$hasil = PenilaianHelper::hitungNilaiWaktu($tanggalDeadline, $tanggalSelesai);
// Return: ['nilai' => 90, 'keterlambatan_hari' => 1, 'status' => 'Terlambat 1 Hari', 'badge_class' => 'bg-warning']
```

**b. Hitung Nilai Akhir**

```php
$nilaiAkhir = PenilaianHelper::hitungNilaiAkhir($nilaiWaktu, $nilaiKualitas);
// Return: 89.00
```

**c. Get Breakdown Penilaian**

```php
$breakdown = PenilaianHelper::getBreakdownPenilaian($nilaiWaktu, $nilaiKualitas);
// Return: [
//     'nilai_waktu' => 90,
//     'bobot_waktu_persen' => 80,
//     'kontribusi_waktu' => 72,
//     'nilai_kualitas' => 85,
//     'bobot_kualitas_persen' => 20,
//     'kontribusi_kualitas' => 17,
//     'nilai_akhir' => 89
// ]
```

**d. Hitung dan Simpan Penilaian**

```php
// Otomatis hitung dan update database
$hasil = PenilaianHelper::hitungDanSimpanPenilaian($tugas, $nilaiKualitas, now());
```

**e. Get Info Penilaian (Untuk Display)**

```php
$info = PenilaianHelper::getInfoPenilaian($tugas);
// Return: Array lengkap dengan tanggal, keterlambatan, breakdown, dan grade
```

**f. Preview Penilaian (Sebelum Simpan)**

```php
$preview = PenilaianHelper::previewPenilaian($tanggalDeadline, $nilaiKualitas, now());
// Return: Preview lengkap sebelum validasi final
```

**g. Get Grade**

```php
$grade = PenilaianHelper::getGrade($nilaiAkhir);
// Return: ['grade' => 'A', 'kategori' => 'Sangat Baik', 'badge_class' => 'bg-success']
```

---

### 2. Controller Usage

File: `Modules/Penugasan/app/Http/Controllers/PenugasanController.php`

#### Validasi Tugas (Hitung Penilaian Otomatis)

```php
public function validasiTugas(Request $request, $id)
{
    // ... validation ...

    if ($validated['status_validasi'] === 'diterima') {
        $nilaiKualitas = $validated['penilaian_kualitas'];

        // Gunakan Helper untuk hitung penilaian
        $hasilPenilaian = PenilaianHelper::hitungDanSimpanPenilaian(
            $tugas,
            $nilaiKualitas,
            now()
        );

        $tugas->update([
            'status' => 'selesai',
            'validator_id' => Auth::id(),
            'validated_at' => now(),
            'hasil_validasi' => 'diterima',
            // penilaian_kualitas dan nilai_akhir sudah di-update oleh helper
        ]);

        return response()->json([
            'nilai_waktu' => $hasilPenilaian['waktu']['nilai'],
            'nilai_kualitas' => $nilaiKualitas,
            'nilai_akhir' => $hasilPenilaian['nilai_akhir'],
            'grade' => $hasilPenilaian['grade'],
        ]);
    }
}
```

#### Preview Penilaian (Real-time di Modal)

```php
public function previewPenilaian(Request $request)
{
    $tugas = TugasHarian::findOrFail($request->tugas_id);

    $preview = PenilaianHelper::previewPenilaian(
        Carbon::parse($tugas->tanggal_selesai),
        $request->nilai_kualitas,
        now()
    );

    return response()->json(['preview' => $preview]);
}
```

---

### 3. View/Frontend Usage

#### Contoh AJAX untuk Preview Real-time

```javascript
// Saat input nilai kualitas berubah
$("#nilai_kualitas_input").on("input", function () {
    let nilaiKualitas = $(this).val();

    $.ajax({
        url: '{{ route("penugasan.preview-penilaian") }}',
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            tugas_id: tugasId,
            jenis_tugas: "tugas_harian",
            nilai_kualitas: nilaiKualitas,
        },
        success: function (response) {
            let preview = response.preview;

            // Update tampilan
            $("#preview_nilai_waktu").text(preview.keterlambatan.nilai);
            $("#preview_status_keterlambatan").html(
                `<span class="badge ${preview.keterlambatan.badge_class}">
                    ${preview.keterlambatan.status}
                </span>`
            );
            $("#preview_nilai_kualitas").text(preview.breakdown.nilai_kualitas);
            $("#preview_nilai_akhir").text(preview.nilai_akhir);
            $("#preview_grade").html(
                `<span class="badge ${preview.grade.badge_class}">
                    ${preview.grade.grade} - ${preview.grade.kategori}
                </span>`
            );
        },
    });
});
```

#### Contoh Tampilan Info Penilaian

```blade
@php
    $info = \Modules\Penugasan\Helpers\PenilaianHelper::getInfoPenilaian($tugas);
@endphp

@if($info)
<div class="card">
    <div class="card-header">
        <h5>Detail Penilaian</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Penilaian Waktu (80%)</h6>
                <p>Deadline: {{ $info['tanggal_deadline'] }}</p>
                <p>Selesai: {{ $info['tanggal_selesai'] }}</p>
                <p>
                    Status:
                    <span class="badge {{ $info['keterlambatan']['badge_class'] }}">
                        {{ $info['keterlambatan']['status'] }}
                    </span>
                </p>
                <p>Nilai: <strong>{{ $info['breakdown']['nilai_waktu'] }}</strong></p>
                <p>Kontribusi: <strong>{{ $info['breakdown']['kontribusi_waktu'] }}</strong></p>
            </div>

            <div class="col-md-6">
                <h6>Penilaian Kualitas (20%)</h6>
                <p>Nilai: <strong>{{ $info['breakdown']['nilai_kualitas'] }}</strong></p>
                <p>Kontribusi: <strong>{{ $info['breakdown']['kontribusi_kualitas'] }}</strong></p>
            </div>
        </div>

        <hr>

        <div class="text-center">
            <h4>Nilai Akhir</h4>
            <h2 class="text-primary">{{ $info['breakdown']['nilai_akhir'] }}</h2>
            <span class="badge {{ $info['grade']['badge_class'] }} fs-5">
                {{ $info['grade']['grade'] }} - {{ $info['grade']['kategori'] }}
            </span>
        </div>
    </div>
</div>
@endif
```

---

## 📋 Grade System

| Nilai Akhir | Grade | Kategori      | Badge        |
| ----------- | ----- | ------------- | ------------ |
| 90 - 100    | A     | Sangat Baik   | `bg-success` |
| 80 - 89     | B     | Baik          | `bg-primary` |
| 70 - 79     | C     | Cukup         | `bg-info`    |
| 60 - 69     | D     | Kurang        | `bg-warning` |
| < 60        | E     | Sangat Kurang | `bg-danger`  |

---

## 🔄 Alur Penilaian

1. **Pegawai Upload Bukti** → Status: `dikerjakan` → `validasi`
2. **Sistem Catat Waktu Submit** → Tanggal sekarang
3. **Atasan Validasi Tugas** → Input nilai kualitas (0-100)
4. **Sistem Hitung Otomatis:**
    - Hitung nilai waktu berdasarkan keterlambatan
    - Hitung nilai akhir: `(nilai_waktu × 0.8) + (nilai_kualitas × 0.2)`
    - Tentukan grade (A/B/C/D/E)
5. **Simpan ke Database:**
    - `penilaian_kualitas` (dari input atasan)
    - `nilai_akhir` (hasil kalkulasi)
6. **Status Tugas** → `selesai`

---

## ✅ Keuntungan Sistem Ini

1. **Tidak Perlu Migration Baru** - Menggunakan field yang sudah ada
2. **Kalkulasi Otomatis** - Sistem hitung nilai waktu otomatis
3. **Transparan** - Pegawai tahu penilaian berdasarkan ketepatan waktu
4. **Flexible** - Atasan tetap bisa menilai kualitas secara subjektif
5. **Fair** - Kombinasi 80% waktu (objektif) + 20% kualitas (subjektif)
6. **Reusable** - Helper bisa digunakan untuk tugas harian dan tugas tambahan
7. **Real-time Preview** - Atasan bisa lihat preview sebelum validasi final
