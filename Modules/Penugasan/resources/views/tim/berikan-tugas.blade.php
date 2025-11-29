@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Berikan Tugas</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('penugasan.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('penugasan.tim.index') }}">Tim</a></li>
                <li class="breadcrumb-item active">Berikan Tugas</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <!-- Form Section -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-person-plus me-2"></i>
                            Form Berikan Tugas
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Tabs for Tugas Type -->
                        <ul class="nav nav-tabs mb-4" id="tugasTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tugas-harian-tab" data-bs-toggle="tab"
                                    data-bs-target="#tugas-harian" type="button" role="tab">
                                    <i class="bi bi-list-task me-1"></i>Tugas Harian
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tugas-tambahan-tab" data-bs-toggle="tab"
                                    data-bs-target="#tugas-tambahan" type="button" role="tab">
                                    <i class="bi bi-file-earmark-plus me-1"></i>Tugas Tambahan
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="tugasTabContent">
                            <!-- Tugas Harian Form -->
                            <div class="tab-pane fade show active" id="tugas-harian" role="tabpanel">
                                <form action="{{ route('penugasan.tugas-harian.store') }}" method="POST">
                                    @csrf
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Tugas harian harus terkait dengan tugas pokok pegawai.
                                    </div>

                                    @if ($errors->any())
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            <strong>Error!</strong>
                                            <ul class="mb-0 mt-2">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    @endif

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Pegawai <span class="text-danger">*</span></label>
                                            <select class="form-select" name="pegawai_id" id="pegawaiHarian" required>
                                                <option value="">Pilih Pegawai</option>
                                                @foreach ($bawahanLangsung as $pegawai)
                                                    <option value="{{ $pegawai->id }}">
                                                        {{ $pegawai->nama }} - {{ $pegawai->jabatan->nama ?? '' }}
                                                        @if ($pegawai->bidang)
                                                            ({{ $pegawai->bidang->nama }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <small class="text-muted">
                                                @php
                                                    $kodeJabatan = auth()->user()->jabatan?->kode;
                                                @endphp
                                                @if (in_array($kodeJabatan, ['KABAN', 'SEKBAN']))
                                                    Anda dapat memberikan tugas harian ke semua pegawai
                                                @elseif($kodeJabatan === 'KABID')
                                                    Anda dapat memberikan tugas harian ke pegawai di bidang Anda
                                                @else
                                                    Hanya bawahan langsung Anda
                                                @endif
                                            </small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Tugas Pokok <span class="text-danger">*</span></label>
                                            <select class="form-select" name="tugas_pokok_id" id="tugasPokokSelect"
                                                required>
                                                <option value="">Pilih pegawai terlebih dahulu</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Nama Tugas <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="nama_tugas" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Deskripsi</label>
                                        <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Tanggal Mulai <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="tanggal_mulai" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Tanggal Selesai <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="tanggal_selesai" required>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Target Value <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="target_value" step="0.01"
                                                required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="satuan" id="satuanInput"
                                                placeholder="dokumen, kegiatan, dll" required readonly>
                                            <small class="text-muted">Satuan otomatis mengikuti tugas pokok</small>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Nilai (Bobot)</label>
                                        <input type="number" class="form-control" name="nilai" step="0.01"
                                            placeholder="Opsional">
                                        <small class="text-muted">Nilai/bobot untuk penilaian kinerja</small>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('penugasan.tim.index') }}" class="btn btn-secondary">
                                            <i class="bi bi-x-circle me-1"></i>Batal
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-send me-1"></i>Berikan Tugas
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Tugas Tambahan Form -->
                            <div class="tab-pane fade" id="tugas-tambahan" role="tabpanel">
                                <form action="{{ route('penugasan.tugas-tambahan.store') }}" method="POST">
                                    @csrf
                                    <div class="alert alert-info">
                                        <i class="bi bi-info-circle me-2"></i>
                                        Tugas tambahan tidak terikat dengan tugas pokok dan bisa diberikan lintas bidang.
                                    </div>

                                    @if ($errors->any())
                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            <strong>Error!</strong>
                                            <ul class="mb-0 mt-2">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    @endif

                                    <div class="mb-3">
                                        <label class="form-label">Pegawai <span class="text-danger">*</span></label>
                                        <select class="form-select" name="pegawai_id" required>
                                            <option value="">Pilih Pegawai</option>
                                            @foreach ($pegawaiTugasTambahan as $pegawai)
                                                <option value="{{ $pegawai->id }}">
                                                    {{ $pegawai->nama }} - {{ $pegawai->jabatan->nama ?? '' }}
                                                    @if ($pegawai->bidang)
                                                        ({{ $pegawai->bidang->nama }})
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">
                                            @php
                                                $kodeJabatan = auth()->user()->jabatan?->kode;
                                            @endphp
                                            @if (in_array($kodeJabatan, ['KABAN', 'SEKBAN']))
                                                Anda dapat memberikan tugas tambahan ke semua pegawai
                                            @elseif($kodeJabatan === 'KABID')
                                                Anda dapat memberikan tugas tambahan ke pegawai di bidang Anda dan semua
                                                GATEK
                                            @elseif($kodeJabatan === 'KASUBAG')
                                                Anda dapat memberikan tugas tambahan ke bawahan langsung dan semua GATEK
                                            @else
                                                Anda dapat memberikan tugas tambahan ke bawahan langsung
                                            @endif
                                        </small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Nama Tugas <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="nama_tugas" required>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Deskripsi</label>
                                        <textarea class="form-control" name="deskripsi" rows="3"></textarea>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Tanggal Mulai <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="tanggal_mulai" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Tanggal Selesai <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="tanggal_selesai" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Alasan Penugasan</label>
                                        <textarea class="form-control" name="alasan_penugasan" rows="2"
                                            placeholder="Opsional - Jelaskan alasan mengapa pegawai ini yang ditugaskan"></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Target Penilaian</label>
                                        <input type="number" class="form-control" name="target_penilaian"
                                            step="0.01" placeholder="Opsional - Score target 0-100">
                                        <small class="text-muted">Target skor penilaian untuk tugas tambahan ini</small>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('penugasan.tim.index') }}" class="btn btn-secondary">
                                            <i class="bi bi-x-circle me-1"></i>Batal
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-send me-1"></i>Berikan Tugas
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar - Recent Assignments -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-clock-history me-2"></i>
                            Tugas Baru Diberikan
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($recentAssignments->count() > 0)
                            <div class="timeline">
                                @foreach ($recentAssignments as $assignment)
                                    <div class="timeline-item mb-3">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <i class="bi bi-circle-fill text-primary" style="font-size: 8px;"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-1">{{ Str::limit($assignment->nama_tugas, 40) }}</h6>
                                                <p class="text-muted small mb-1">
                                                    Untuk: <strong>{{ $assignment->pegawai->nama ?? 'N/A' }}</strong>
                                                </p>
                                                <small class="text-muted">
                                                    <i class="bi bi-clock me-1"></i>
                                                    {{ $assignment->created_at->diffForHumans() }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted text-center">Belum ada tugas yang diberikan</p>
                        @endif
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bi bi-graph-up me-2"></i>
                            Statistik Tim
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Bawahan</span>
                                <strong>{{ $bawahanLangsung->count() }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tugas Aktif</span>
                                <strong class="text-primary">{{ $stats['tugas_aktif'] ?? 0 }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Menunggu Validasi</span>
                                <strong class="text-warning">{{ $stats['menunggu_validasi'] ?? 0 }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Rata-rata Progress</span>
                                <strong class="text-success">{{ $stats['avg_progress'] ?? 0 }}%</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        // Show success message with SweetAlert
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        @endif

        // Show error message with SweetAlert
        @if (session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}',
                confirmButtonText: 'OK',
                confirmButtonColor: '#d33'
            });
        @endif

        // Auto-fill satuan when tugas pokok is selected
        document.getElementById('tugasPokokSelect').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const satuan = selectedOption.dataset.satuan || '';
            document.getElementById('satuanInput').value = satuan;
        });

        // Load Tugas Pokok based on selected Pegawai
        document.getElementById('pegawaiHarian').addEventListener('change', function() {
            const pegawaiId = this.value;
            const tugasPokokSelect = document.getElementById('tugasPokokSelect');

            tugasPokokSelect.innerHTML = '<option value="">Loading...</option>';

            if (pegawaiId) {
                fetch(`{{ url('/penugasan/api/pegawai') }}/${pegawaiId}/tugas-pokok`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.length > 0) {
                            tugasPokokSelect.innerHTML = '<option value="">Pilih Tugas Pokok</option>';
                            data.forEach(tp => {
                                const option = document.createElement('option');
                                option.value = tp.id;
                                option.textContent = `${tp.nama_tugas} (${tp.progress_persen}%)`;
                                option.dataset.satuan = tp.satuan || '';
                                tugasPokokSelect.appendChild(option);
                            });
                        } else {
                            tugasPokokSelect.innerHTML =
                                '<option value="">Tidak ada tugas pokok aktif</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        tugasPokokSelect.innerHTML = '<option value="">Error loading data</option>';
                    });
            } else {
                tugasPokokSelect.innerHTML = '<option value="">Pilih pegawai terlebih dahulu</option>';
            }
        });

        // Auto set today as minimum date
        const today = new Date().toISOString().split('T')[0];
        document.querySelectorAll('input[type="date"]').forEach(input => {
            input.setAttribute('min', today);
        });

        // Validate date range
        document.querySelectorAll('input[name="tanggal_selesai"]').forEach(input => {
            input.addEventListener('change', function() {
                const mulai = this.closest('form').querySelector('input[name="tanggal_mulai"]').value;
                if (mulai && this.value < mulai) {
                    alert('Tanggal selesai tidak boleh lebih awal dari tanggal mulai');
                    this.value = '';
                }
            });
        });
    </script>
@endpush
