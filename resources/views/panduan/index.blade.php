@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Panduan</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('e-kinerja.index') }}">E-Kinerja</a></li>
                <li class="breadcrumb-item active">Panduan</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0 fw-bold">Dokumen Panduan Penggunaan</h5>
                        <small class="text-muted">Kumpulan panduan/manual penggunaan aplikasi TERDEPAN</small>
                    </div>
                    @if ($isAdmin)
                        <a href="{{ route('panduan.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Panduan
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        @if ($panduans->isEmpty())
                            <div class="text-center text-muted py-5">
                                <i class="bi bi-journal-x" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0">Belum ada dokumen panduan.</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Judul</th>
                                            <th>Deskripsi</th>
                                            <th>Diunggah</th>
                                            <th class="text-end">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($panduans as $panduan)
                                            <tr>
                                                <td>
                                                    <i class="bi bi-file-earmark-pdf text-danger me-1"></i>
                                                    {{ $panduan->judul }}
                                                </td>
                                                <td class="text-muted">{{ Str::limit($panduan->deskripsi, 80) ?: '-' }}</td>
                                                <td>{{ $formatDate($panduan->created_at, 'd M Y') }}</td>
                                                <td class="text-end">
                                                    <a href="{{ route('panduan.preview', $panduan) }}" target="_blank"
                                                        class="btn btn-sm btn-outline-primary" title="Preview">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('panduan.download', $panduan) }}"
                                                        class="btn btn-sm btn-outline-success" title="Download">
                                                        <i class="bi bi-download"></i>
                                                    </a>
                                                    @if ($isAdmin)
                                                        <a href="{{ route('panduan.edit', $panduan) }}"
                                                            class="btn btn-sm btn-outline-secondary" title="Edit">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                            title="Hapus" onclick="konfirmasiHapus({{ $panduan->id }}, '{{ addslashes($panduan->judul) }}')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($isAdmin)
        <div class="modal fade" id="modalDeleteConfirm" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title">Hapus Panduan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Yakin ingin menghapus panduan "<span id="deletePanduanJudul"></span>"? Tindakan ini tidak
                        bisa dibatalkan.
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <form id="formDeletePanduan" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                function konfirmasiHapus(id, judul) {
                    document.getElementById('deletePanduanJudul').textContent = judul;
                    document.getElementById('formDeletePanduan').action = `{{ url('panduan') }}/${id}`;
                    new bootstrap.Modal(document.getElementById('modalDeleteConfirm')).show();
                }
            </script>
        @endpush
    @endif
@endsection
