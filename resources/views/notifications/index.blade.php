@extends('layouts.main')

@php
    $warnaMap = [
        'info' => ['bg-info bg-opacity-10 text-info', 'text-info'],
        'warning' => ['bg-warning bg-opacity-10 text-warning', 'text-warning'],
        'danger' => ['bg-danger bg-opacity-10 text-danger', 'text-danger'],
        'success' => ['bg-success bg-opacity-10 text-success', 'text-success'],
    ];
@endphp

@section('main')
    <div class="pagetitle">
        <h1>Notifikasi</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Notifikasi</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="alert alert-light border small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Notifikasi di sini dihitung otomatis dari kondisi tugas Anda saat ini — akan hilang dengan
                    sendirinya setelah Anda menindaklanjutinya (mis. menerima tugas, menilai, atau memutuskan
                    pengajuan perpanjangan).
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        @forelse ($notifikasi as $item)
                            @php [$badgeClass, $iconClass] = $warnaMap[$item['tipe']] ?? $warnaMap['info']; @endphp
                            <a href="{{ $item['link'] }}"
                                class="d-flex align-items-center gap-3 px-3 py-3 text-reset text-decoration-none notif-row {{ !$loop->last ? 'border-bottom' : '' }}">
                                <div class="notif-icon rounded-circle d-flex align-items-center justify-content-center {{ $badgeClass }}">
                                    <i class="bi {{ $item['ikon'] }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ $item['pesan'] }}</div>
                                </div>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </a>
                        @empty
                            <div class="text-center py-5">
                                <i class="bi bi-check-circle text-success" style="font-size: 2.5rem;"></i>
                                <p class="text-muted mt-3 mb-0">Semua beres — tidak ada yang perlu ditindaklanjuti saat ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('styles')
        <style>
            .notif-row:hover {
                background-color: #f6f9ff;
            }

            .notif-icon {
                width: 42px;
                height: 42px;
                flex-shrink: 0;
                font-size: 1.1rem;
            }
        </style>
    @endpush
@endsection
