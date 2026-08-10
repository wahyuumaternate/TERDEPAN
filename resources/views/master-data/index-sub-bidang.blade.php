@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Data Sub Bidang</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item">Master Data</li>
                <li class="breadcrumb-item active">Sub Bidang</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="card-title mb-1 d-flex align-items-center">
                                    <div class="icon-box bg-primary bg-opacity-10 rounded-3 p-2 me-3">
                                        <i class="bi bi-diagram-3-fill text-primary" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold">Daftar Sub Bidang</span>
                                        <small class="d-block text-muted fw-normal mt-1">Kelola data master sub
                                            bidang</small>
                                    </div>
                                </h5>
                            </div>
                            <div>
                                <a href="{{ route('master.sub-bidang.create') }}"
                                    class="btn btn-primary btn-lg shadow-sm px-4 py-2">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Sub Bidang
                                </a>
                            </div>
                        </div>

                        <!-- Tabel Sub Bidang -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="30%">Nama Sub Bidang</th>
                                        <th width="30%">Bidang Induk</th>
                                        <th width="20%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $index => $subBidang)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="fw-bold">{{ $subBidang->nama }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info bg-opacity-10 text-info">
                                                    {{ $subBidang->bidang->nama ?? '-' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('master.sub-bidang.show', $subBidang->id) }}"
                                                        class="btn btn-sm btn-outline-info" title="Lihat / Edit">
                                                        <i class="bi bi-pencil-square"></i>
                                                    </a>
                                                    <form action="{{ route('master.sub-bidang.destroy', $subBidang->id) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Apakah Anda yakin ingin menghapus sub bidang ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            title="Hapus">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="bi bi-diagram-3 display-1 d-block mb-3"></i>
                                                    <h5>Belum ada data sub bidang</h5>
                                                    <p>Silakan tambahkan data sub bidang baru</p>
                                                    <a href="{{ route('master.sub-bidang.create') }}"
                                                        class="btn btn-primary">
                                                        <i class="bi bi-plus-circle me-1"></i> Tambah Sub Bidang
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .icon-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
        }

        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
        }
    </style>
@endsection
