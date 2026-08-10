@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Dashboard</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body py-5 text-center">
                        <i class="bi bi-person-workspace text-primary" style="font-size: 2.5rem;"></i>
                        <h4 class="fw-bold mt-3 mb-1">Selamat datang, {{ $user->nama }}</h4>
                        <p class="text-muted mb-4">
                            {{ $user->profile?->jabatan?->nama ?? '-' }}
                            @if ($user->profile?->bidang)
                                &middot; {{ $user->profile->bidang->nama }}
                            @endif
                        </p>

                        <a href="{{ route('penugasan.tugas-saya') }}" class="btn btn-primary">
                            <i class="bi bi-list-task me-1"></i>Buka Penugasan Pegawai
                        </a>

                        <p class="text-muted small mt-4 mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Dashboard ini masih tampilan sementara — ringkasan & statistik akan ditambahkan kembali sesuai kebutuhan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
