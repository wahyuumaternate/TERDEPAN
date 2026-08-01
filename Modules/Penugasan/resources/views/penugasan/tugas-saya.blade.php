@extends('layouts.main')

@php
    $jenisLabel = match ($jenis) {
        'pokok' => 'Tugas Pokok Saya',
        'tambahan' => 'Tugas Tambahan Saya',
        default => 'Tugas Saya',
    };
    $statusBadgeMap = [
        'pending' => 'bg-secondary',
        'dikerjakan' => 'bg-primary',
        'revisi' => 'bg-danger',
        'validasi' => 'bg-warning text-dark',
        'selesai' => 'bg-success',
    ];
@endphp

@section('main')
    <div class="pagetitle d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
            <h1>{{ $jenisLabel }}</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">{{ $jenisLabel }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('penugasan.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Buat Tugas Mandiri
        </a>
    </div>

    <section class="section">
        <!-- Ringkasan Status -->
        <div class="row mb-4">
            @foreach (['pending' => 'Pending', 'dikerjakan' => 'Dikerjakan', 'revisi' => 'Revisi', 'validasi' => 'Validasi', 'selesai' => 'Selesai'] as $key => $label)
                <div class="col">
                    <div class="card shadow-sm border-0 text-center">
                        <div class="card-body py-3">
                            <h5 class="fw-bold mb-0">{{ $grouped->get($key, collect())->count() }}</h5>
                            <small class="text-muted">{{ $label }}</small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <!-- Filter Status -->
                <div class="d-flex flex-wrap gap-2 mb-3">
                    <a href="{{ route('penugasan.tugas-saya', ['jenis' => $jenis, 'status' => 'all']) }}"
                        class="btn btn-sm {{ $status === 'all' ? 'btn-primary' : 'btn-outline-primary' }}">Semua</a>
                    @foreach (['pending' => 'Pending', 'dikerjakan' => 'Dikerjakan', 'revisi' => 'Revisi', 'validasi' => 'Validasi', 'selesai' => 'Selesai'] as $key => $label)
                        <a href="{{ route('penugasan.tugas-saya', ['jenis' => $jenis, 'status' => $key]) }}"
                            class="btn btn-sm {{ $status === $key ? 'btn-primary' : 'btn-outline-primary' }}">{{ $label }}</a>
                    @endforeach
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Tugas</th>
                                @if (!$jenis)
                                    <th>Jenis</th>
                                @endif
                                <th>Pemberi Tugas</th>
                                <th class="text-center">Bobot</th>
                                <th class="text-center">Progress</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Deadline</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($penugasan as $tugas)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $tugas->nama_tugas }}</div>
                                        @if ($tugas->is_mandiri)
                                            <small class="text-muted"><i class="bi bi-person-check me-1"></i>Mandiri</small>
                                        @endif
                                    </td>
                                    @if (!$jenis)
                                        <td>
                                            <span
                                                class="badge bg-info bg-opacity-10 text-info">{{ ucfirst($tugas->jenis) }}</span>
                                        </td>
                                    @endif
                                    <td>{{ $tugas->pemberiTugas->nama ?? 'Mandiri' }}</td>
                                    <td class="text-center">{{ $tugas->bobot_persen }}%</td>
                                    <td class="text-center">{{ $tugas->progress_persen }}%</td>
                                    <td class="text-center">
                                        <span
                                            class="badge {{ $statusBadgeMap[$tugas->status] ?? 'bg-secondary' }}">{{ ucfirst($tugas->status) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <small>{{ $tugas->tanggal_selesai->format('d M Y') }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('penugasan.show', $tugas->id) }}"
                                                class="btn btn-outline-primary" title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            @if ($tugas->status === 'pending')
                                                <button type="button" class="btn btn-outline-success"
                                                    onclick="terimaTugas('{{ $tugas->id }}')" title="Terima">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <i class="bi bi-inbox fs-1 text-muted"></i>
                                        <p class="text-muted mt-2 mb-0">Tidak ada penugasan</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $penugasan->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        function terimaTugas(id) {
            fetch(`{{ url('/penugasan') }}/${id}/terima`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => window.location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: data.message
                        });
                    }
                });
        }
    </script>
@endpush
