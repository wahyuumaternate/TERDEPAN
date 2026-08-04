@extends('layouts.main')

@php
    $adaJalurA = $bisaMemberi && $calonPegawai->isNotEmpty();
    $adaJalurB = $atasanKandidat->isNotEmpty();
    $jalurAwal = $adaJalurA ? 'a' : 'b';
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
                                <i class="bi bi-send me-1"></i>Berikan ke Pegawai Lain
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" data-jalur="b" onclick="pilihJalur('b')">
                                <i class="bi bi-person-check me-1"></i>Buat Tugas Mandiri
                            </button>
                        </li>
                    </ul>
                @endif

                <!-- ===================== JALUR A: Berikan ke Pegawai Lain ===================== -->
                @if ($adaJalurA)
                    <div class="card shadow-sm border-0 jalur-panel" id="panel-a"
                        style="{{ $jalurAwal === 'a' ? '' : 'display:none' }}">
                        <div class="card-header bg-light">
                            <h6 class="card-title mb-0"><i class="bi bi-send me-2"></i>Berikan Tugas ke Pegawai Lain</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('penugasan.store') }}" method="POST" id="formJalurA">
                                @csrf
                                <input type="hidden" name="pegawai_id" id="pegawaiIdSingle" disabled>

                                <!-- Step 1: Info Dasar -->
                                <div class="step-a" data-step="1">
                                    <h6 class="text-muted mb-3">Langkah 1 dari 5 — Info Dasar</h6>
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
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-primary" onclick="langkahBerikut('a', 1)">Lanjut</button>
                                    </div>
                                </div>

                                <!-- Step 2: Personil -->
                                <div class="step-a d-none" data-step="2">
                                    <h6 class="text-muted mb-3">Langkah 2 dari 5 — Personil</h6>
                                    <label class="form-label">Pilih Pegawai <span class="text-danger">*</span></label>
                                    <div class="border rounded p-2 mb-3" style="max-height: 260px; overflow-y: auto;">
                                        @foreach ($calonPegawai as $pegawai)
                                            <div class="form-check">
                                                <input class="form-check-input pegawai-checkbox" type="checkbox"
                                                    name="pegawai_ids[]" value="{{ $pegawai->id }}"
                                                    id="pegawai{{ $pegawai->id }}"
                                                    {{ (string) $pegawaiIdTerpilih === (string) $pegawai->id ? 'checked' : '' }}>
                                                <label class="form-check-label" for="pegawai{{ $pegawai->id }}">
                                                    {{ $pegawai->nama }} — {{ $pegawai->profile->jabatan->nama ?? '-' }}
                                                    @if ($pegawai->profile->bidang)
                                                        <span class="text-muted">({{ $pegawai->profile->bidang->nama }})</span>
                                                    @endif
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div id="grupOptions" class="d-none border rounded p-3 bg-light mb-3">
                                        <label class="form-label">Mode Grup <span class="text-danger">*</span></label>
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

                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-secondary" onclick="langkahMundur('a', 2)">Kembali</button>
                                        <button type="button" class="btn btn-primary" onclick="langkahBerikut('a', 2)">Lanjut</button>
                                    </div>
                                </div>

                                <!-- Step 3: Jadwal & Prioritas -->
                                <div class="step-a d-none" data-step="3">
                                    <h6 class="text-muted mb-3">Langkah 3 dari 5 — Jadwal &amp; Prioritas</h6>
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
                                    <div class="mb-3">
                                        <label class="form-label">Prioritas <span class="text-danger">*</span></label>
                                        <select class="form-select" name="prioritas" required>
                                            <option value="rendah">Rendah</option>
                                            <option value="sedang" selected>Sedang</option>
                                            <option value="tinggi">Tinggi</option>
                                        </select>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Target Value</label>
                                            <input type="number" class="form-control" name="target_value" step="0.01" placeholder="Opsional">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Satuan</label>
                                            <input type="text" class="form-control" name="satuan" placeholder="dokumen, laporan, dll">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-secondary" onclick="langkahMundur('a', 3)">Kembali</button>
                                        <button type="button" class="btn btn-primary" onclick="langkahBerikut('a', 3)">Lanjut</button>
                                    </div>
                                </div>

                                <!-- Step 4: Bobot -->
                                <div class="step-a d-none" data-step="4">
                                    <h6 class="text-muted mb-3">Langkah 4 dari 5 — Bobot</h6>
                                    <div class="mb-3">
                                        <label class="form-label">Bobot (%)</label>
                                        <input type="number" class="form-control" name="bobot_persen" min="0" max="100" step="0.01">
                                        <small class="text-muted">Boleh dikosongkan — bisa diisi nanti sebelum tugas dinilai.</small>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-secondary" onclick="langkahMundur('a', 4)">Kembali</button>
                                        <button type="button" class="btn btn-primary" onclick="langkahBerikut('a', 4)">Lanjut</button>
                                    </div>
                                </div>

                                <!-- Step 5: Review -->
                                <div class="step-a d-none" data-step="5">
                                    <h6 class="text-muted mb-3">Langkah 5 dari 5 — Review &amp; Kirim</h6>
                                    <div id="reviewA" class="border rounded p-3 bg-light mb-3 small"></div>
                                    <div class="d-flex justify-content-between">
                                        <button type="button" class="btn btn-outline-secondary" onclick="langkahMundur('a', 5)">Kembali</button>
                                        <button type="submit" class="btn btn-success"><i class="bi bi-send me-1"></i>Kirim Tugas</button>
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

                                <div class="mb-3">
                                    <label class="form-label">Prioritas <span class="text-danger">*</span></label>
                                    <select class="form-select" name="prioritas" required>
                                        <option value="rendah">Rendah</option>
                                        <option value="sedang" selected>Sedang</option>
                                        <option value="tinggi">Tinggi</option>
                                    </select>
                                    <small class="text-muted">Atasan bisa mengubah prioritas ini saat menyetujui tugas.</small>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Target Value</label>
                                        <input type="number" class="form-control" name="target_value" step="0.01" placeholder="Opsional">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Satuan</label>
                                        <input type="text" class="form-control" name="satuan" placeholder="dokumen, laporan, dll">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Bobot (%)</label>
                                    <input type="number" class="form-control" name="bobot_persen" min="0" max="100" step="0.01">
                                    <small class="text-muted">Boleh dikosongkan — bisa diisi nanti sebelum tugas dinilai.</small>
                                </div>

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

    @push('scripts')
        <script>
            function pilihJalur(jalur) {
                document.querySelectorAll('.jalur-panel').forEach(el => el.style.display = 'none');
                document.getElementById('panel-' + jalur).style.display = '';
                document.querySelectorAll('#jalurTab .nav-link').forEach(el => el.classList.remove('active'));
                document.querySelector('#jalurTab [data-jalur="' + jalur + '"]').classList.add('active');
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

                container.classList.add('d-none');
                document.querySelector('.step-' + jalur + '[data-step="' + (dariStep + 1) + '"]').classList.remove('d-none');

                if (jalur === 'a' && dariStep + 1 === 5) {
                    isiReviewA();
                }
            }

            function langkahMundur(jalur, dariStep) {
                document.querySelector('.step-' + jalur + '[data-step="' + dariStep + '"]').classList.add('d-none');
                document.querySelector('.step-' + jalur + '[data-step="' + (dariStep - 1) + '"]').classList.remove('d-none');
            }

            function isiReviewA() {
                const form = document.getElementById('formJalurA');
                const namaTugas = form.nama_tugas.value;
                const jenis = form.jenis.value === 'pokok' ? 'Tugas Pokok' : 'Tugas Tambahan';
                const jumlahPegawai = document.querySelectorAll('.pegawai-checkbox:checked').length;
                const mulai = form.tanggal_mulai.value;
                const selesai = form.tanggal_selesai.value;
                const prioritas = form.prioritas.value;
                const bobot = form.bobot_persen.value || '(belum diisi)';

                document.getElementById('reviewA').innerHTML = `
                    <div><strong>Nama Tugas:</strong> ${namaTugas}</div>
                    <div><strong>Jenis:</strong> ${jenis}</div>
                    <div><strong>Jumlah Pegawai:</strong> ${jumlahPegawai} orang</div>
                    <div><strong>Jadwal:</strong> ${mulai} s.d. ${selesai}</div>
                    <div><strong>Prioritas:</strong> ${prioritas}</div>
                    <div><strong>Bobot:</strong> ${bobot}%</div>
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
