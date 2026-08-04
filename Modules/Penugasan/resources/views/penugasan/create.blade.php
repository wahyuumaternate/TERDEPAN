@extends('layouts.main')

@php
    $adaJalurA = $bisaMemberi && $calonPegawai->isNotEmpty();
    $adaJalurB = $atasanKandidat->isNotEmpty();
    $jalurAwal = $adaJalurA ? 'a' : 'b';
    $langkahJalurA = [
        1 => ['label' => 'Info Dasar', 'icon' => 'bi-card-text'],
        2 => ['label' => 'Personil', 'icon' => 'bi-people'],
        3 => ['label' => 'Jadwal & Bobot', 'icon' => 'bi-calendar-week'],
        4 => ['label' => 'Review', 'icon' => 'bi-check2-circle'],
    ];
@endphp

@section('main')
    <div class="pagetitle">
        <h1>Buat Tugas</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('penugasan.tugas-saya') }}">Tugas Saya</a></li>
                <li class="breadcrumb-item active">Buat Tugas</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (!$adaJalurA && !$adaJalurB)
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle me-2"></i>Anda tidak memiliki hak untuk memberikan tugas ke pegawai
                        lain maupun kandidat atasan untuk tugas mandiri.
                    </div>
                @endif

                @if ($adaJalurA && $adaJalurB)
                    <ul class="nav nav-pills mb-4" id="jalurTab">
                        <li class="nav-item">
                            <button type="button" class="nav-link active" data-jalur="a" onclick="pilihJalur('a')">
                                <i class="bi bi-send me-1"></i>Berikan Penugasan
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-jalur="b" onclick="pilihJalur('b')">
                                <i class="bi bi-person-check me-1"></i>Buat Tugas Mandiri
                            </button>
                        </li>
                    </ul>
                @endif

                <!-- ===================== JALUR A: Berikan Tugas ke Pegawai ===================== -->
                @if ($adaJalurA)
                    <div class="card shadow-sm border-0 jalur-panel" id="panel-a"
                        style="{{ $jalurAwal === 'a' ? '' : 'display:none' }}">
                        <div class="card-header bg-white py-3">
                            <h6 class="card-title mb-0"><i class="bi bi-send me-2 text-primary"></i>Berikan Tugas ke Pegawai</h6>
                        </div>
                        <div class="card-body p-4">
                            <!-- Stepper numerik 1-2-3-4 -->
                            <div class="wizard-stepper mb-4" id="stepperA">
                                @foreach ($langkahJalurA as $nomor => $info)
                                    <div class="wizard-step-item {{ $nomor === 1 ? 'active' : '' }}" data-step-indicator="{{ $nomor }}"
                                        onclick="cobaLompatKeLangkah('a', {{ $nomor }})">
                                        <div class="step-circle">
                                            <span class="step-number">{{ $nomor }}</span>
                                            <i class="bi bi-check-lg step-check"></i>
                                        </div>
                                        <div class="step-label">{{ $info['label'] }}</div>
                                    </div>
                                @endforeach
                            </div>

                            <form action="{{ route('penugasan.store') }}" method="POST" id="formJalurA">
                                @csrf
                                <input type="hidden" name="pegawai_id" id="pegawaiIdSingle" disabled>

                                <!-- Step 1: Info Dasar -->
                                <div class="step-a" data-step="1">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-card-text me-2 text-primary"></i>Info Dasar</h6>
                                    <div class="mb-3">
                                        <label class="form-label">Jenis Tugas <span class="text-danger">*</span></label>
                                        <select class="form-select" name="jenis" required>
                                            <option value="tambahan" selected>Tugas Tambahan</option>
                                            <option value="pokok">Tugas Pokok</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Nama Tugas <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="nama_tugas" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="deskripsi" rows="3" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Alasan Penugasan</label>
                                        <textarea class="form-control" name="alasan_penugasan" rows="2" placeholder="Opsional"></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end pt-2 border-top">
                                        <button type="button" class="btn btn-primary px-4" onclick="langkahBerikut('a', 1)">
                                            Lanjut<i class="bi bi-arrow-right ms-1"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Step 2: Personil -->
                                <div class="step-a d-none" data-step="2">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-people me-2 text-primary"></i>Personil</h6>
                                    <label class="form-label">Pilih Pegawai <span class="text-danger">*</span></label>
                                    <div class="border rounded-3 p-2 mb-3 personil-list" style="max-height: 260px; overflow-y: auto;">
                                        @foreach ($calonPegawai as $pegawai)
                                            <div class="form-check personil-item px-5 py-2 rounded-2">
                                                <input class="form-check-input pegawai-checkbox" type="checkbox"
                                                    name="pegawai_ids[]" value="{{ $pegawai->id }}"
                                                    id="pegawai{{ $pegawai->id }}"
                                                    {{ (string) $pegawaiIdTerpilih === (string) $pegawai->id ? 'checked' : '' }}>
                                                <label class="form-check-label w-100" for="pegawai{{ $pegawai->id }}">
                                                    {{ $pegawai->nama }} — {{ $pegawai->profile->jabatan->nama ?? '-' }}
                                                    @if ($pegawai->profile->bidang)
                                                        <span class="text-muted">({{ $pegawai->profile->bidang->kode }})</span>
                                                    @endif
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div id="grupOptions" class="d-none border rounded-3 p-3 bg-light mb-3">
                                        <label class="form-label fw-semibold"><i class="bi bi-diagram-3 me-1"></i>Mode Grup <span class="text-danger">*</span></label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="mode_grup" value="per_orang" id="modePerOrang" checked>
                                            <label class="form-check-label" for="modePerOrang">
                                                Per Orang — setiap pegawai bekerja &amp; dinilai independen
                                            </label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" name="mode_grup" value="kolektif" id="modeKolektif">
                                            <label class="form-check-label" for="modeKolektif">
                                                Kolektif — satu koordinator bertindak atas nama grup
                                            </label>
                                        </div>
                                        <div id="koordinatorGroup" class="d-none">
                                            <label class="form-label">Koordinator <span class="text-danger">*</span></label>
                                            <select class="form-select" name="koordinator_id" id="koordinatorSelect">
                                                <option value="">Pilih koordinator dari pegawai terpilih</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between pt-2 border-top">
                                        <button type="button" class="btn btn-outline-secondary px-4" onclick="langkahMundur('a', 2)">
                                            <i class="bi bi-arrow-left me-1"></i>Kembali
                                        </button>
                                        <button type="button" class="btn btn-primary px-4" onclick="langkahBerikut('a', 2)">
                                            Lanjut<i class="bi bi-arrow-right ms-1"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Step 3: Jadwal, Prioritas & Bobot -->
                                <div class="step-a d-none" data-step="3">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-calendar-week me-2 text-primary"></i>Jadwal, Prioritas &amp; Bobot</h6>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="tanggal_mulai" value="{{ now()->toDateString() }}" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="tanggal_selesai" required>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Prioritas <span class="text-danger">*</span></label>
                                            <select class="form-select" name="prioritas" required>
                                                <option value="rendah">Rendah</option>
                                                <option value="sedang" selected>Sedang</option>
                                                <option value="tinggi">Tinggi</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Satuan</label>
                                            <input type="text" class="form-control" name="satuan" placeholder="dokumen, laporan, dll">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Bobot</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" name="bobot_persen" min="0" max="100" step="0.01">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted d-block mb-3">Bobot boleh dikosongkan — bisa diisi nanti sebelum tugas dinilai.</small>
                                    <div class="d-flex justify-content-between pt-2 border-top">
                                        <button type="button" class="btn btn-outline-secondary px-4" onclick="langkahMundur('a', 3)">
                                            <i class="bi bi-arrow-left me-1"></i>Kembali
                                        </button>
                                        <button type="button" class="btn btn-primary px-4" onclick="langkahBerikut('a', 3)">
                                            Lanjut<i class="bi bi-arrow-right ms-1"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Step 4: Review -->
                                <div class="step-a d-none" data-step="4">
                                    <h6 class="fw-bold mb-3"><i class="bi bi-check2-circle me-2 text-primary"></i>Review &amp; Kirim</h6>
                                    <div id="reviewA" class="border rounded-3 p-3 bg-light mb-3"></div>
                                    <div class="d-flex justify-content-between pt-2 border-top">
                                        <button type="button" class="btn btn-outline-secondary px-4" onclick="langkahMundur('a', 4)">
                                            <i class="bi bi-arrow-left me-1"></i>Kembali
                                        </button>
                                        <button type="submit" class="btn btn-success px-4"><i class="bi bi-send me-1"></i>Kirim Tugas</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif

                <!-- ===================== JALUR B: Mandiri ===================== -->
                @if ($adaJalurB)
                    <div class="card shadow-sm border-0 jalur-panel" id="panel-b"
                        style="{{ $jalurAwal === 'b' ? '' : 'display:none' }}">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0"><i class="bi bi-person-check me-2"></i>Buat Tugas Mandiri</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>Tugas mandiri (self-initiated) akan menunggu
                                persetujuan atasan yang Anda pilih sebelum bisa dikerjakan.
                            </div>

                            <form action="{{ route('penugasan.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="pegawai_id" value="{{ auth()->id() }}">

                                <div class="mb-3">
                                    <label class="form-label">Pilih Atasan <span class="text-danger">*</span></label>
                                    <select class="form-select" name="atasan_id" required>
                                        <option value="">Pilih atasan yang berhak menyetujui</option>
                                        @foreach ($atasanKandidat as $atasan)
                                            <option value="{{ $atasan->id }}">{{ $atasan->nama }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Daftar ini otomatis mengikuti aturan hierarki persetujuan tugas mandiri.</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Jenis Tugas <span class="text-danger">*</span></label>
                                    <select class="form-select" name="jenis" required>
                                        <option value="tambahan" selected>Tugas Tambahan</option>
                                        <option value="pokok">Tugas Pokok</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Nama Tugas <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="nama_tugas" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="deskripsi" rows="3" required></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Alasan Penugasan</label>
                                    <textarea class="form-control" name="alasan_penugasan" rows="2" placeholder="Opsional"></textarea>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="tanggal_mulai" value="{{ now()->toDateString() }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="tanggal_selesai" required>
                                    </div>
                                </div>

                                <div class="row mb-1">
                                    <div class="col-md-4">
                                        <label class="form-label">Prioritas <span class="text-danger">*</span></label>
                                        <select class="form-select" name="prioritas" required>
                                            <option value="rendah">Rendah</option>
                                            <option value="sedang" selected>Sedang</option>
                                            <option value="tinggi">Tinggi</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Satuan</label>
                                        <input type="text" class="form-control" name="satuan" placeholder="dokumen, laporan, dll">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Bobot</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" name="bobot_persen" min="0" max="100" step="0.01">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </div>
                                <small class="text-muted d-block mb-4">Atasan bisa mengubah prioritas saat menyetujui tugas. Bobot boleh dikosongkan — bisa diisi nanti sebelum tugas dinilai.</small>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success"><i class="bi bi-send me-1"></i>Ajukan Tugas</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    @push('styles')
        <style>
            .wizard-stepper {
                display: flex;
                align-items: flex-start;
            }

            .wizard-step-item {
                flex: 1;
                position: relative;
                text-align: center;
                cursor: pointer;
            }

            .wizard-step-item:not(:last-child)::after {
                content: '';
                position: absolute;
                top: 21px;
                left: 50%;
                width: 100%;
                height: 3px;
                background-color: #e9ecef;
                z-index: 0;
            }

            .wizard-step-item.completed:not(:last-child)::after {
                background-color: var(--bs-primary);
            }

            .wizard-step-item .step-circle {
                width: 44px;
                height: 44px;
                margin: 0 auto 0.5rem;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: #fff;
                border: 3px solid #e9ecef;
                color: #adb5bd;
                font-weight: 700;
                position: relative;
                z-index: 1;
                transition: all 0.2s ease;
            }

            .wizard-step-item .step-check {
                display: none;
            }

            .wizard-step-item.active .step-circle {
                border-color: var(--bs-primary);
                background-color: var(--bs-primary);
                color: #fff;
                box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15);
            }

            .wizard-step-item.completed .step-circle {
                border-color: var(--bs-primary);
                background-color: var(--bs-primary);
                color: #fff;
            }

            .wizard-step-item.completed .step-number {
                display: none;
            }

            .wizard-step-item.completed .step-check {
                display: inline-block;
            }

            .wizard-step-item .step-label {
                font-size: 0.8rem;
                font-weight: 600;
                color: #adb5bd;
            }

            .wizard-step-item.active .step-label,
            .wizard-step-item.completed .step-label {
                color: var(--bs-primary);
            }

            .personil-item:hover {
                background-color: #f8f9fa;
            }

            .step-a {
                animation: fadeInStep 0.2s ease;
            }

            @keyframes fadeInStep {
                from {
                    opacity: 0;
                    transform: translateY(4px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            const TOTAL_LANGKAH_A = 4;
            let langkahTerjauhA = 1;

            function pilihJalur(jalur) {
                document.querySelectorAll('.jalur-panel').forEach(el => el.style.display = 'none');
                document.getElementById('panel-' + jalur).style.display = '';
                document.querySelectorAll('#jalurTab .nav-link').forEach(el => el.classList.remove('active'));
                document.querySelector('#jalurTab [data-jalur="' + jalur + '"]').classList.add('active');
            }

            function perbaruiStepper(langkahAktif) {
                document.querySelectorAll('#stepperA .wizard-step-item').forEach(el => {
                    const nomor = parseInt(el.dataset.stepIndicator, 10);
                    el.classList.remove('active', 'completed');
                    if (nomor < langkahAktif) {
                        el.classList.add('completed');
                    } else if (nomor === langkahAktif) {
                        el.classList.add('active');
                    }
                });
            }

            function tampilkanLangkah(langkah) {
                document.querySelectorAll('.step-a').forEach(el => el.classList.add('d-none'));
                document.querySelector('.step-a[data-step="' + langkah + '"]').classList.remove('d-none');
                perbaruiStepper(langkah);
                langkahTerjauhA = Math.max(langkahTerjauhA, langkah);

                if (langkah === TOTAL_LANGKAH_A) {
                    isiReviewA();
                }
            }

            function cobaLompatKeLangkah(jalur, tujuan) {
                if (jalur !== 'a' || tujuan > langkahTerjauhA) {
                    return;
                }
                tampilkanLangkah(tujuan);
            }

            function langkahBerikut(jalur, dariStep) {
                const container = document.querySelector('.step-' + jalur + '[data-step="' + dariStep + '"]');
                const inputs = container.querySelectorAll('input, select, textarea');
                for (const input of inputs) {
                    if (!input.checkValidity()) {
                        input.reportValidity();
                        return;
                    }
                }

                if (jalur === 'a' && dariStep === 2) {
                    const checked = document.querySelectorAll('.pegawai-checkbox:checked');
                    if (checked.length === 0) {
                        Swal.fire('Perhatian', 'Pilih minimal satu pegawai', 'warning');
                        return;
                    }
                    if (document.getElementById('modeKolektif').checked && !document.getElementById('koordinatorSelect').value) {
                        Swal.fire('Perhatian', 'Pilih koordinator untuk mode Kolektif', 'warning');
                        return;
                    }
                }

                tampilkanLangkah(dariStep + 1);
            }

            function langkahMundur(jalur, dariStep) {
                tampilkanLangkah(dariStep - 1);
            }

            // Input date HTML selalu bernilai ISO "YYYY-MM-DD" — ubah ke format tampilan "DD-MM-YYYY".
            function formatTanggalReview(tanggalIso) {
                if (!tanggalIso) {
                    return '-';
                }
                const [tahun, bulan, tanggal] = tanggalIso.split('-');
                return `${tanggal}-${bulan}-${tahun}`;
            }

            function isiReviewA() {
                const form = document.getElementById('formJalurA');
                const namaTugas = form.nama_tugas.value;
                const jenis = form.jenis.value === 'pokok' ? 'Tugas Pokok' : 'Tugas Tambahan';
                const jumlahPegawai = document.querySelectorAll('.pegawai-checkbox:checked').length;
                const mulai = formatTanggalReview(form.tanggal_mulai.value);
                const selesai = formatTanggalReview(form.tanggal_selesai.value);
                const prioritas = form.prioritas.value;
                const bobot = form.bobot_persen.value || '(belum diisi)';

                document.getElementById('reviewA').innerHTML = `
                    <div class="row g-3">
                        <div class="col-sm-6"><small class="text-muted d-block">Nama Tugas</small><strong>${namaTugas}</strong></div>
                        <div class="col-sm-6"><small class="text-muted d-block">Jenis</small><strong>${jenis}</strong></div>
                        <div class="col-sm-6"><small class="text-muted d-block">Jumlah Pegawai</small><strong>${jumlahPegawai} orang</strong></div>
                        <div class="col-sm-6"><small class="text-muted d-block">Prioritas</small><strong>${prioritas}</strong></div>
                        <div class="col-sm-6"><small class="text-muted d-block">Jadwal</small><strong>${mulai} s.d. ${selesai}</strong></div>
                        <div class="col-sm-6"><small class="text-muted d-block">Bobot</small><strong>${bobot}${bobot !== '(belum diisi)' ? '%' : ''}</strong></div>
                    </div>
                `;
            }

            // Toggle opsi grup (mode_grup/koordinator) hanya saat personil terpilih > 1
            document.querySelectorAll('.pegawai-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    const checked = [...document.querySelectorAll('.pegawai-checkbox:checked')];
                    const grupOptions = document.getElementById('grupOptions');
                    const koordinatorSelect = document.getElementById('koordinatorSelect');

                    if (checked.length > 1) {
                        grupOptions.classList.remove('d-none');
                        koordinatorSelect.innerHTML = '<option value="">Pilih koordinator dari pegawai terpilih</option>' +
                            checked.map(c => `<option value="${c.value}">${c.nextElementSibling.textContent.trim()}</option>`).join('');
                    } else {
                        grupOptions.classList.add('d-none');
                    }
                });
            });

            document.getElementById('modeKolektif')?.addEventListener('change', function() {
                document.getElementById('koordinatorGroup').classList.toggle('d-none', !this.checked);
            });
            document.getElementById('modePerOrang')?.addEventListener('change', function() {
                document.getElementById('koordinatorGroup').classList.add('d-none');
            });

            // Set form action + payload akhir sebelum submit: satu pegawai -> penugasan.store,
            // lebih dari satu -> penugasan.store-grup (dok. 08 §4.1 catatan personil[])
            document.getElementById('formJalurA')?.addEventListener('submit', function(e) {
                const checked = [...document.querySelectorAll('.pegawai-checkbox:checked')];
                const pegawaiIdSingle = document.getElementById('pegawaiIdSingle');

                if (checked.length === 1) {
                    this.action = "{{ route('penugasan.store') }}";
                    pegawaiIdSingle.disabled = false;
                    pegawaiIdSingle.value = checked[0].value;
                    checked[0].disabled = true;
                } else {
                    this.action = "{{ route('penugasan.store-grup') }}";
                    pegawaiIdSingle.disabled = true;
                }
            });

            document.querySelectorAll('input[name="tanggal_selesai"]').forEach(input => {
                input.addEventListener('change', function() {
                    const mulai = this.closest('form').querySelector('input[name="tanggal_mulai"]').value;
                    if (mulai && this.value < mulai) {
                        Swal.fire('Perhatian', 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai', 'warning');
                        this.value = '';
                    }
                });
            });
        </script>
    @endpush
@endsection
