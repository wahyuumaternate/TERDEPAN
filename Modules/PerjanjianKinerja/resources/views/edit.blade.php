@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Edit Perjanjian Kinerja</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('perjanjian-kinerja.index') }}">Perjanjian Kinerja</a></li>
                <li class="breadcrumb-item"><a href="{{ route('perjanjian-kinerja.show', $pk->id) }}">Detail</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        @if ($pk->is_locked)
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="bi bi-exclamation-triangle-fill"></i> Dokumen Terkunci
                </h5>
                <p class="mb-0">Dokumen ini sudah ditandatangani dan terkunci. Perubahan tidak dapat dilakukan.</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('perjanjian-kinerja.update', $pk->id) }}" method="POST" id="formPerjanjianKinerja">
            @csrf
            @method('PUT')
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Status Info -->
                    <div class="card border-start border-primary border-4">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-1 pt-2">Nomor Perjanjian</h6>
                                    <h5 class="mb-0 text-primary">{{ $pk->nomor_perjanjian }}</h5>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    @php
                                        $statusClass = [
                                            'Draft' => 'secondary',
                                            'Generated' => 'info',
                                            'Menunggu_TTD' => 'warning',
                                            'Aktif' => 'success',
                                            'Selesai' => 'primary',
                                            'Dibatalkan' => 'danger',
                                        ];
                                        $statusText = str_replace('_', ' ', $pk->status_dokumen);
                                    @endphp
                                    <span class="badge bg-{{ $statusClass[$pk->status_dokumen] }} px-3 py-2">
                                        <i class="bi bi-circle-fill"></i> {{ $statusText }}
                                    </span>
                                    @if ($pk->is_locked)
                                        <span class="badge bg-dark px-3 py-2 ms-2">
                                            <i class="bi bi-lock-fill"></i> Terkunci
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Dasar -->
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-file-earmark-text text-primary"></i>
                                Informasi Dasar
                            </h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="pegawai_id" class="form-label">
                                        Pegawai <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select select2" id="pegawai_id" name="pegawai_id" required
                                        {{ $pk->is_locked ? 'disabled' : '' }}>
                                        <option value="">-- Pilih Pegawai --</option>
                                        @foreach ($pegawai as $p)
                                            <option value="{{ $p->id }}"
                                                data-jabatan="{{ $p->jabatan->nama ?? '-' }}"
                                                data-bidang="{{ $p->bidang->nama ?? '-' }}"
                                                data-nip="{{ $p->nomor_identitas }}"
                                                {{ $pk->pegawai_id == $p->id ? 'selected' : '' }}>
                                                {{ $p->nama }} - {{ $p->nomor_identitas }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="atasan_id" class="form-label">
                                        Atasan Langsung <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select select2" id="atasan_id" name="atasan_id" required
                                        {{ $pk->is_locked ? 'disabled' : '' }}>
                                        <option value="">-- Pilih Atasan --</option>
                                        @foreach ($atasan as $a)
                                            <option value="{{ $a->id }}"
                                                data-jabatan="{{ $a->jabatan->nama ?? '-' }}"
                                                data-nip="{{ $a->nomor_identitas }}"
                                                {{ $pk->atasan_id == $a->id ? 'selected' : '' }}>
                                                {{ $a->nama }} - {{ $a->nomor_identitas }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="template_id" class="form-label">
                                        Template Dokumen <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="template_id" name="template_id" required
                                        {{ $pk->is_locked ? 'disabled' : '' }}>
                                        <option value="">-- Pilih Template --</option>
                                        @foreach ($templates as $t)
                                            <option value="{{ $t->id }}"
                                                {{ $pk->template_id == $t->id ? 'selected' : '' }}>
                                                {{ $t->nama_template }} (v{{ $t->versi }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="tahun" class="form-label">
                                        Tahun Perjanjian <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="tahun" name="tahun"
                                        value="{{ $pk->tahun }}" min="2020" max="2100" required
                                        {{ $pk->is_locked ? 'disabled' : '' }}>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="periode_mulai" class="form-label">
                                        Periode Mulai <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control" id="periode_mulai" name="periode_mulai"
                                        value="{{ date('Y-m-d', strtotime($pk->periode_mulai)) }}" required
                                        {{ $pk->is_locked ? 'disabled' : '' }}>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="periode_selesai" class="form-label">
                                        Periode Selesai <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control" id="periode_selesai" name="periode_selesai"
                                        value="{{ date('Y-m-d', strtotime($pk->periode_selesai)) }}" required
                                        {{ $pk->is_locked ? 'disabled' : '' }}>
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="catatan" class="form-label">
                                        <i class="bi bi-sticky"></i> Catatan Tambahan
                                    </label>
                                    <textarea class="form-control" id="catatan" name="catatan" rows="3"
                                        placeholder="Tambahkan catatan jika diperlukan..." style="resize: vertical;"
                                        {{ $pk->is_locked ? 'disabled' : '' }}>{{ $pk->catatan }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sasaran Strategis -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-bullseye text-primary"></i>
                                    Sasaran Strategis & Indikator Kinerja
                                </h5>
                                @if (!$pk->is_locked)
                                    <button type="button" class="btn btn-primary btn-sm" onclick="addSasaran()">
                                        <i class="bi bi-plus-circle"></i> Tambah Sasaran
                                    </button>
                                @endif
                            </div>

                            <div id="sasaran-container">
                                @forelse($pk->sasaran->sortBy('urutan') as $sIndex => $sasaran)
                                    <div class="sasaran-item card border-start border-primary border-4 mb-3"
                                        data-index="{{ $sIndex }}">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <span
                                                        class="badge bg-primary rounded-pill sasaran-number">{{ $sasaran->urutan }}</span>
                                                    <strong class="ms-2">Sasaran Strategis</strong>
                                                </div>
                                                @if (!$pk->is_locked)
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        onclick="removeSasaran(this)" title="Hapus Sasaran">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                @endif
                                            </div>

                                            <input type="hidden" name="sasaran[{{ $sIndex }}][id]"
                                                value="{{ $sasaran->id }}">

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">
                                                    <i class="bi bi-card-text"></i> Deskripsi Sasaran
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <textarea class="form-control sasaran-desc" name="sasaran[{{ $sIndex }}][sasaran_strategis]" rows="3"
                                                    required placeholder="Contoh: Meningkatkan kualitas pelayanan publik..." {{ $pk->is_locked ? 'disabled' : '' }}>{{ $sasaran->sasaran_strategis }}</textarea>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label fw-semibold">Urutan</label>
                                                    <input type="number" class="form-control"
                                                        name="sasaran[{{ $sIndex }}][urutan]"
                                                        value="{{ $sasaran->urutan }}" min="1" required
                                                        {{ $pk->is_locked ? 'disabled' : '' }}>
                                                </div>
                                            </div>

                                            <hr>

                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <label class="form-label fw-semibold mb-0">
                                                    <i class="bi bi-bar-chart"></i> Indikator Kinerja
                                                </label>
                                                @if (!$pk->is_locked)
                                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                                        onclick="addIndikator(this)">
                                                        <i class="bi bi-plus"></i> Tambah Indikator
                                                    </button>
                                                @endif
                                            </div>

                                            <div class="indikator-container">
                                                @foreach ($sasaran->indikator as $iIndex => $indikator)
                                                    <div class="indikator-item card bg-light border mb-2">
                                                        <div class="card-body p-3">
                                                            <div
                                                                class="d-flex justify-content-between align-items-start mb-2">
                                                                <small class="text-muted fw-semibold">
                                                                    <i class="bi bi-check2-circle"></i> Indikator <span
                                                                        class="indikator-number">{{ $iIndex + 1 }}</span>
                                                                </small>
                                                                @if (!$pk->is_locked)
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-danger"
                                                                        onclick="removeIndikator(this)"
                                                                        title="Hapus Indikator">
                                                                        <i class="bi bi-x"></i>
                                                                    </button>
                                                                @endif
                                                            </div>

                                                            <input type="hidden"
                                                                name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][id]"
                                                                value="{{ $indikator->id }}">

                                                            <div class="row g-2 mb-3">
                                                                <div class="col-12">
                                                                    <input type="text"
                                                                        class="form-control form-control-sm"
                                                                        name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][indikator_sasaran]"
                                                                        placeholder="Nama indikator kinerja..."
                                                                        value="{{ $indikator->indikator_sasaran }}"
                                                                        required {{ $pk->is_locked ? 'disabled' : '' }}>
                                                                </div>
                                                                <div class="col-6">
                                                                    <div class="input-group input-group-sm">
                                                                        <span class="input-group-text">Target</span>
                                                                        <input type="number" class="form-control"
                                                                            name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][target_value]"
                                                                            placeholder="100"
                                                                            value="{{ $indikator->target_value }}"
                                                                            step="0.01" required
                                                                            {{ $pk->is_locked ? 'disabled' : '' }}>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6">
                                                                    <input type="text"
                                                                        class="form-control form-control-sm"
                                                                        name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][satuan]"
                                                                        placeholder="Satuan (%, Orang, Kegiatan...)"
                                                                        value="{{ $indikator->satuan }}" required
                                                                        {{ $pk->is_locked ? 'disabled' : '' }}>
                                                                </div>
                                                            </div>

                                                            <hr class="my-2">

                                                            <!-- Program Section -->
                                                            <div
                                                                class="d-flex justify-content-between align-items-center mb-2">
                                                                <small class="text-muted fw-semibold">
                                                                    <i class="bi bi-folder"></i> Program & Kegiatan
                                                                </small>
                                                                @if (!$pk->is_locked)
                                                                    <button type="button"
                                                                        class="btn btn-sm btn-outline-success btn-xs"
                                                                        onclick="addProgram(this)">
                                                                        <i class="bi bi-plus"></i> Program
                                                                    </button>
                                                                @endif
                                                            </div>

                                                            <div class="program-container">
                                                                @foreach ($indikator->program->sortBy('urutan') as $pIndex => $program)
                                                                    <div
                                                                        class="program-item card border-start border-success border-3 mb-2">
                                                                        <div class="card-body p-2 bg-white">
                                                                            <div
                                                                                class="d-flex justify-content-between align-items-start mb-2">
                                                                                <small class="text-success fw-semibold">
                                                                                    <i class="bi bi-folder-fill"></i>
                                                                                    Program <span
                                                                                        class="program-number">{{ $pIndex + 1 }}</span>
                                                                                </small>
                                                                                @if (!$pk->is_locked)
                                                                                    <button type="button"
                                                                                        class="btn btn-sm btn-outline-danger btn-xs"
                                                                                        onclick="removeProgram(this)">
                                                                                        <i class="bi bi-x"></i>
                                                                                    </button>
                                                                                @endif
                                                                            </div>

                                                                            <input type="hidden"
                                                                                name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][program][{{ $pIndex }}][id]"
                                                                                value="{{ $program->id }}">

                                                                            <div class="row g-2 mb-2">
                                                                                <div class="col-4">
                                                                                    <input type="text"
                                                                                        class="form-control form-control-sm"
                                                                                        name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][program][{{ $pIndex }}][kode_program]"
                                                                                        placeholder="Kode Program"
                                                                                        value="{{ $program->kode_program }}"
                                                                                        required
                                                                                        {{ $pk->is_locked ? 'disabled' : '' }}>
                                                                                </div>
                                                                                <div class="col-8">
                                                                                    <input type="text"
                                                                                        class="form-control form-control-sm"
                                                                                        name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][program][{{ $pIndex }}][nama_program]"
                                                                                        placeholder="Nama Program"
                                                                                        value="{{ $program->nama_program }}"
                                                                                        required
                                                                                        {{ $pk->is_locked ? 'disabled' : '' }}>
                                                                                </div>
                                                                                <div class="col-6">
                                                                                    <input type="number"
                                                                                        class="form-control form-control-sm"
                                                                                        name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][program][{{ $pIndex }}][anggaran]"
                                                                                        placeholder="Anggaran"
                                                                                        value="{{ $program->anggaran }}"
                                                                                        step="0.01" required
                                                                                        {{ $pk->is_locked ? 'disabled' : '' }}>
                                                                                </div>
                                                                                <div class="col-6">
                                                                                    <input type="number"
                                                                                        class="form-control form-control-sm"
                                                                                        name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][program][{{ $pIndex }}][urutan]"
                                                                                        placeholder="Urutan"
                                                                                        value="{{ $program->urutan }}"
                                                                                        min="1" required
                                                                                        {{ $pk->is_locked ? 'disabled' : '' }}>
                                                                                </div>
                                                                            </div>

                                                                            <!-- Kegiatan Section -->
                                                                            <div
                                                                                class="d-flex justify-content-between align-items-center mb-1">
                                                                                <small class="text-muted"
                                                                                    style="font-size: 0.75rem;">
                                                                                    <i class="bi bi-list-task"></i>
                                                                                    Kegiatan
                                                                                </small>
                                                                                @if (!$pk->is_locked)
                                                                                    <button type="button"
                                                                                        class="btn btn-sm btn-outline-info btn-xs"
                                                                                        style="padding: 0.1rem 0.3rem; font-size: 0.7rem;"
                                                                                        onclick="addKegiatan(this)">
                                                                                        <i class="bi bi-plus"></i> Kegiatan
                                                                                    </button>
                                                                                @endif
                                                                            </div>

                                                                            <div class="kegiatan-container ms-2">
                                                                                @foreach ($program->kegiatan->sortBy('urutan') as $kIndex => $kegiatan)
                                                                                    <div class="kegiatan-item card bg-light mb-1"
                                                                                        style="border-left: 3px solid #0dcaf0;">
                                                                                        <div class="card-body p-2">
                                                                                            <div
                                                                                                class="d-flex justify-content-between align-items-start mb-1">
                                                                                                <small class="text-info"
                                                                                                    style="font-size: 0.75rem;">
                                                                                                    <i
                                                                                                        class="bi bi-list-check"></i>
                                                                                                    Kegiatan <span
                                                                                                        class="kegiatan-number">{{ $kIndex + 1 }}</span>
                                                                                                </small>
                                                                                                @if (!$pk->is_locked)
                                                                                                    <button type="button"
                                                                                                        class="btn btn-sm btn-outline-danger"
                                                                                                        style="padding: 0.1rem 0.3rem; font-size: 0.7rem;"
                                                                                                        onclick="removeKegiatan(this)">
                                                                                                        <i
                                                                                                            class="bi bi-x"></i>
                                                                                                    </button>
                                                                                                @endif
                                                                                            </div>

                                                                                            <input type="hidden"
                                                                                                name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][program][{{ $pIndex }}][kegiatan][{{ $kIndex }}][id]"
                                                                                                value="{{ $kegiatan->id }}">

                                                                                            <div class="row g-1 mb-1">
                                                                                                <div class="col-4">
                                                                                                    <input type="text"
                                                                                                        class="form-control form-control-sm"
                                                                                                        style="font-size: 0.8rem;"
                                                                                                        name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][program][{{ $pIndex }}][kegiatan][{{ $kIndex }}][kode_kegiatan]"
                                                                                                        placeholder="Kode"
                                                                                                        value="{{ $kegiatan->kode_kegiatan }}"
                                                                                                        required
                                                                                                        {{ $pk->is_locked ? 'disabled' : '' }}>
                                                                                                </div>
                                                                                                <div class="col-8">
                                                                                                    <input type="text"
                                                                                                        class="form-control form-control-sm"
                                                                                                        style="font-size: 0.8rem;"
                                                                                                        name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][program][{{ $pIndex }}][kegiatan][{{ $kIndex }}][nama_kegiatan]"
                                                                                                        placeholder="Nama Kegiatan"
                                                                                                        value="{{ $kegiatan->nama_kegiatan }}"
                                                                                                        required
                                                                                                        {{ $pk->is_locked ? 'disabled' : '' }}>
                                                                                                </div>
                                                                                                <div class="col-6">
                                                                                                    <input type="number"
                                                                                                        class="form-control form-control-sm"
                                                                                                        style="font-size: 0.8rem;"
                                                                                                        name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][program][{{ $pIndex }}][kegiatan][{{ $kIndex }}][anggaran]"
                                                                                                        placeholder="Anggaran"
                                                                                                        value="{{ $kegiatan->anggaran }}"
                                                                                                        step="0.01"
                                                                                                        required
                                                                                                        {{ $pk->is_locked ? 'disabled' : '' }}>
                                                                                                </div>
                                                                                                <div class="col-6">
                                                                                                    <input type="number"
                                                                                                        class="form-control form-control-sm"
                                                                                                        style="font-size: 0.8rem;"
                                                                                                        name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][program][{{ $pIndex }}][kegiatan][{{ $kIndex }}][urutan]"
                                                                                                        placeholder="Urutan"
                                                                                                        value="{{ $kegiatan->urutan }}"
                                                                                                        min="1"
                                                                                                        required
                                                                                                        {{ $pk->is_locked ? 'disabled' : '' }}>
                                                                                                </div>
                                                                                            </div>

                                                                                            <!-- Sub Kegiatan Section -->
                                                                                            <div
                                                                                                class="d-flex justify-content-between align-items-center mb-1">
                                                                                                <small class="text-muted"
                                                                                                    style="font-size: 0.7rem;">
                                                                                                    <i
                                                                                                        class="bi bi-arrow-return-right"></i>
                                                                                                    Sub Kegiatan
                                                                                                </small>
                                                                                                @if (!$pk->is_locked)
                                                                                                    <button type="button"
                                                                                                        class="btn btn-sm btn-outline-secondary btn-xs"
                                                                                                        style="padding: 0.05rem 0.2rem; font-size: 0.65rem;"
                                                                                                        onclick="addSubKegiatan(this)">
                                                                                                        <i
                                                                                                            class="bi bi-plus"></i>
                                                                                                        Sub
                                                                                                    </button>
                                                                                                @endif
                                                                                            </div>

                                                                                            <div
                                                                                                class="subkegiatan-container ms-2">
                                                                                                @foreach ($kegiatan->subKegiatan->sortBy('urutan') as $skIndex => $subKegiatan)
                                                                                                    <div class="subkegiatan-item p-2 mb-1 bg-white border rounded"
                                                                                                        style="border-left: 2px solid #6c757d !important;">
                                                                                                        <div
                                                                                                            class="d-flex justify-content-between align-items-start mb-1">
                                                                                                            <small
                                                                                                                class="text-muted"
                                                                                                                style="font-size: 0.7rem;">
                                                                                                                <i
                                                                                                                    class="bi bi-arrow-return-right"></i>
                                                                                                                Sub <span
                                                                                                                    class="subkegiatan-number">{{ $skIndex + 1 }}</span>
                                                                                                            </small>
                                                                                                            @if (!$pk->is_locked)
                                                                                                                <button
                                                                                                                    type="button"
                                                                                                                    class="btn btn-sm btn-outline-danger"
                                                                                                                    style="padding: 0.05rem 0.2rem; font-size: 0.65rem;"
                                                                                                                    onclick="removeSubKegiatan(this)">
                                                                                                                    <i
                                                                                                                        class="bi bi-x"></i>
                                                                                                                </button>
                                                                                                            @endif
                                                                                                        </div>

                                                                                                        <input
                                                                                                            type="hidden"
                                                                                                            name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][program][{{ $pIndex }}][kegiatan][{{ $kIndex }}][subkegiatan][{{ $skIndex }}][id]"
                                                                                                            value="{{ $subKegiatan->id }}">

                                                                                                        <div
                                                                                                            class="row g-1">
                                                                                                            <div
                                                                                                                class="col-4">
                                                                                                                <input
                                                                                                                    type="text"
                                                                                                                    class="form-control form-control-sm"
                                                                                                                    style="font-size: 0.75rem;"
                                                                                                                    name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][program][{{ $pIndex }}][kegiatan][{{ $kIndex }}][subkegiatan][{{ $skIndex }}][kode_sub_kegiatan]"
                                                                                                                    placeholder="Kode"
                                                                                                                    value="{{ $subKegiatan->kode_sub_kegiatan }}"
                                                                                                                    required
                                                                                                                    {{ $pk->is_locked ? 'disabled' : '' }}>
                                                                                                            </div>
                                                                                                            <div
                                                                                                                class="col-8">
                                                                                                                <input
                                                                                                                    type="text"
                                                                                                                    class="form-control form-control-sm"
                                                                                                                    style="font-size: 0.75rem;"
                                                                                                                    name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][program][{{ $pIndex }}][kegiatan][{{ $kIndex }}][subkegiatan][{{ $skIndex }}][nama_sub_kegiatan]"
                                                                                                                    placeholder="Nama Sub Kegiatan"
                                                                                                                    value="{{ $subKegiatan->nama_sub_kegiatan }}"
                                                                                                                    required
                                                                                                                    {{ $pk->is_locked ? 'disabled' : '' }}>
                                                                                                            </div>
                                                                                                            <div
                                                                                                                class="col-4">
                                                                                                                <input
                                                                                                                    type="number"
                                                                                                                    class="form-control form-control-sm"
                                                                                                                    style="font-size: 0.75rem;"
                                                                                                                    name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][program][{{ $pIndex }}][kegiatan][{{ $kIndex }}][subkegiatan][{{ $skIndex }}][anggaran]"
                                                                                                                    placeholder="Anggaran"
                                                                                                                    value="{{ $subKegiatan->anggaran }}"
                                                                                                                    step="0.01"
                                                                                                                    required
                                                                                                                    {{ $pk->is_locked ? 'disabled' : '' }}>
                                                                                                            </div>
                                                                                                            <div
                                                                                                                class="col-4">
                                                                                                                <input
                                                                                                                    type="number"
                                                                                                                    class="form-control form-control-sm"
                                                                                                                    style="font-size: 0.75rem;"
                                                                                                                    name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][program][{{ $pIndex }}][kegiatan][{{ $kIndex }}][subkegiatan][{{ $skIndex }}][target_value]"
                                                                                                                    placeholder="Target"
                                                                                                                    value="{{ $subKegiatan->target_value }}"
                                                                                                                    step="0.01"
                                                                                                                    required
                                                                                                                    {{ $pk->is_locked ? 'disabled' : '' }}>
                                                                                                            </div>
                                                                                                            <div
                                                                                                                class="col-4">
                                                                                                                <input
                                                                                                                    type="text"
                                                                                                                    class="form-control form-control-sm"
                                                                                                                    style="font-size: 0.75rem;"
                                                                                                                    name="sasaran[{{ $sIndex }}][indikator][{{ $iIndex }}][program][{{ $pIndex }}][kegiatan][{{ $kIndex }}][subkegiatan][{{ $skIndex }}][satuan]"
                                                                                                                    placeholder="Satuan"
                                                                                                                    value="{{ $subKegiatan->satuan }}"
                                                                                                                    required
                                                                                                                    {{ $pk->is_locked ? 'disabled' : '' }}>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                @endforeach
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5" id="empty-sasaran">
                                        <i class="bi bi-inbox display-1 text-muted opacity-50"></i>
                                        <p class="text-muted mt-3 mb-0">Belum ada sasaran strategis</p>
                                        <small class="text-muted">Klik tombol "Tambah Sasaran" untuk memulai</small>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Info Preview -->
                    <div class="card info-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="bi bi-eye text-white"></i>
                                Preview Informasi
                            </h5>

                            <div class="preview-section">
                                <div class="preview-item">
                                    <label class="preview-label">
                                        <i class="bi bi-person-badge"></i> Pegawai
                                    </label>
                                    <div id="preview-pegawai" class="preview-value">{{ $pk->pegawai->nama }}</div>
                                    <small id="preview-pegawai-detail" class="preview-detail">
                                        NIP: {{ $pk->pegawai->nomor_identitas }}<br>
                                        Jabatan: {{ $pk->pegawai->jabatan->nama ?? '-' }}<br>
                                        Unit: {{ $pk->pegawai->bidang->nama ?? '-' }}
                                    </small>
                                </div>

                                <hr class="my-3">

                                <div class="preview-item">
                                    <label class="preview-label">
                                        <i class="bi bi-person-check"></i> Atasan Langsung
                                    </label>
                                    <div id="preview-atasan" class="preview-value">{{ $pk->atasan->nama }}</div>
                                    <small id="preview-atasan-detail" class="preview-detail">
                                        NIP: {{ $pk->atasan->nomor_identitas }}<br>
                                        Jabatan: {{ $pk->atasan->jabatan->nama ?? '-' }}
                                    </small>
                                </div>

                                <hr class="my-3">

                                <div class="preview-item">
                                    <label class="preview-label">
                                        <i class="bi bi-calendar-range"></i> Periode
                                    </label>
                                    <div id="preview-periode" class="preview-value">
                                        {{ date('d M Y', strtotime($pk->periode_mulai)) }} -
                                        {{ date('d M Y', strtotime($pk->periode_selesai)) }}
                                    </div>
                                </div>

                                <hr class="my-3">

                                <div class="preview-item">
                                    <label class="preview-label">
                                        <i class="bi bi-diagram-3"></i> Total Sasaran
                                    </label>
                                    <h3 id="preview-total-sasaran" class="preview-count mb-0">
                                        {{ $pk->sasaran->count() }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="card">
                        <div class="card-body">
                            @if (!$pk->is_locked)
                                <button type="submit" class="btn btn-primary w-100 mb-2">
                                    <i class="bi bi-save"></i> Simpan Perubahan
                                </button>
                            @endif
                            <a href="{{ route('perjanjian-kinerja.show', $pk->id) }}"
                                class="btn btn-outline-secondary w-100">
                                <i class="bi bi-arrow-left"></i> Kembali ke Detail
                            </a>
                        </div>
                    </div>

                    <!-- Warning Card -->
                    @if (!$pk->is_locked)
                        <div class="card border-warning">
                            <div class="card-body">
                                <h6 class="card-title text-warning">
                                    <i class="bi bi-exclamation-triangle"></i> Perhatian
                                </h6>
                                <ul class="small mb-0 ps-3">
                                    <li class="mb-2">Pastikan perubahan sudah benar sebelum menyimpan</li>
                                    <li class="mb-2">Setelah ditandatangani, dokumen akan terkunci</li>
                                    <li>Dokumen terkunci tidak dapat diedit lagi</li>
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </section>

    <!-- Template Sasaran Item -->
    <template id="template-sasaran">
        <div class="sasaran-item card border-start border-primary border-4 mb-3" data-index="0">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-primary rounded-pill sasaran-number">1</span>
                        <strong class="ms-2">Sasaran Strategis</strong>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeSasaran(this)"
                        title="Hapus Sasaran">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-card-text"></i> Deskripsi Sasaran
                        <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control sasaran-desc" name="sasaran[0][sasaran_strategis]" rows="3" required
                        placeholder="Contoh: Meningkatkan kualitas pelayanan publik..."></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-semibold">Urutan</label>
                        <input type="number" class="form-control" name="sasaran[0][urutan]" value="1"
                            min="1" required>
                    </div>
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <label class="form-label fw-semibold mb-0">
                        <i class="bi bi-bar-chart"></i> Indikator Kinerja
                    </label>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="addIndikator(this)">
                        <i class="bi bi-plus"></i> Tambah Indikator
                    </button>
                </div>

                <div class="indikator-container"></div>
            </div>
        </div>
    </template>

    <!-- Template Indikator Item -->
    <template id="template-indikator">
        <div class="indikator-item card bg-light border mb-2">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <small class="text-muted fw-semibold">
                        <i class="bi bi-check2-circle"></i> Indikator <span class="indikator-number">1</span>
                    </small>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeIndikator(this)"
                        title="Hapus Indikator">
                        <i class="bi bi-x"></i>
                    </button>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-12">
                        <input type="text" class="form-control form-control-sm"
                            name="sasaran[0][indikator][0][indikator_sasaran]" placeholder="Nama indikator kinerja..."
                            required>
                    </div>
                    <div class="col-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Target</span>
                            <input type="number" class="form-control" name="sasaran[0][indikator][0][target_value]"
                                placeholder="100" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-6">
                        <input type="text" class="form-control form-control-sm"
                            name="sasaran[0][indikator][0][satuan]" placeholder="Satuan (%, Orang, Kegiatan...)" required>
                    </div>
                </div>

                <hr class="my-2">

                <!-- Program Section -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted fw-semibold">
                        <i class="bi bi-folder"></i> Program & Kegiatan
                    </small>
                    <button type="button" class="btn btn-sm btn-outline-success btn-xs" onclick="addProgram(this)">
                        <i class="bi bi-plus"></i> Program
                    </button>
                </div>

                <div class="program-container"></div>
            </div>
        </div>
    </template>

    <!-- Template Program Item -->
    <template id="template-program">
        <div class="program-item card border-start border-success border-3 mb-2">
            <div class="card-body p-2 bg-white">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <small class="text-success fw-semibold">
                        <i class="bi bi-folder-fill"></i> Program <span class="program-number">1</span>
                    </small>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-xs" onclick="removeProgram(this)">
                        <i class="bi bi-x"></i>
                    </button>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-4">
                        <input type="text" class="form-control form-control-sm"
                            name="sasaran[0][indikator][0][program][0][kode_program]" placeholder="Kode Program" required>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control form-control-sm"
                            name="sasaran[0][indikator][0][program][0][nama_program]" placeholder="Nama Program" required>
                    </div>
                    <div class="col-6">
                        <input type="number" class="form-control form-control-sm"
                            name="sasaran[0][indikator][0][program][0][anggaran]" placeholder="Anggaran" step="0.01"
                            required>
                    </div>
                    <div class="col-6">
                        <input type="number" class="form-control form-control-sm"
                            name="sasaran[0][indikator][0][program][0][urutan]" placeholder="Urutan" value="1"
                            min="1" required>
                    </div>
                </div>

                <!-- Kegiatan Section -->
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted" style="font-size: 0.75rem;">
                        <i class="bi bi-list-task"></i> Kegiatan
                    </small>
                    <button type="button" class="btn btn-sm btn-outline-info btn-xs"
                        style="padding: 0.1rem 0.3rem; font-size: 0.7rem;" onclick="addKegiatan(this)">
                        <i class="bi bi-plus"></i> Kegiatan
                    </button>
                </div>

                <div class="kegiatan-container ms-2"></div>
            </div>
        </div>
    </template>

    <!-- Template Kegiatan Item -->
    <template id="template-kegiatan">
        <div class="kegiatan-item card bg-light mb-1" style="border-left: 3px solid #0dcaf0;">
            <div class="card-body p-2">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <small class="text-info" style="font-size: 0.75rem;">
                        <i class="bi bi-list-check"></i> Kegiatan <span class="kegiatan-number">1</span>
                    </small>
                    <button type="button" class="btn btn-sm btn-outline-danger"
                        style="padding: 0.1rem 0.3rem; font-size: 0.7rem;" onclick="removeKegiatan(this)">
                        <i class="bi bi-x"></i>
                    </button>
                </div>

                <div class="row g-1 mb-1">
                    <div class="col-4">
                        <input type="text" class="form-control form-control-sm" style="font-size: 0.8rem;"
                            name="sasaran[0][indikator][0][program][0][kegiatan][0][kode_kegiatan]" placeholder="Kode"
                            required>
                    </div>
                    <div class="col-8">
                        <input type="text" class="form-control form-control-sm" style="font-size: 0.8rem;"
                            name="sasaran[0][indikator][0][program][0][kegiatan][0][nama_kegiatan]"
                            placeholder="Nama Kegiatan" required>
                    </div>
                    <div class="col-6">
                        <input type="number" class="form-control form-control-sm" style="font-size: 0.8rem;"
                            name="sasaran[0][indikator][0][program][0][kegiatan][0][anggaran]" placeholder="Anggaran"
                            step="0.01" required>
                    </div>
                    <div class="col-6">
                        <input type="number" class="form-control form-control-sm" style="font-size: 0.8rem;"
                            name="sasaran[0][indikator][0][program][0][kegiatan][0][urutan]" placeholder="Urutan"
                            value="1" min="1" required>
                    </div>
                </div>

                <!-- Sub Kegiatan Section -->
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted" style="font-size: 0.7rem;">
                        <i class="bi bi-arrow-return-right"></i> Sub Kegiatan
                    </small>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-xs"
                        style="padding: 0.05rem 0.2rem; font-size: 0.65rem;" onclick="addSubKegiatan(this)">
                        <i class="bi bi-plus"></i> Sub
                    </button>
                </div>

                <div class="subkegiatan-container ms-2"></div>
            </div>
        </div>
    </template>

    <!-- Template Sub Kegiatan Item -->
    <template id="template-subkegiatan">
        <div class="subkegiatan-item p-2 mb-1 bg-white border rounded" style="border-left: 2px solid #6c757d !important;">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <small class="text-muted" style="font-size: 0.7rem;">
                    <i class="bi bi-arrow-return-right"></i> Sub <span class="subkegiatan-number">1</span>
                </small>
                <button type="button" class="btn btn-sm btn-outline-danger"
                    style="padding: 0.05rem 0.2rem; font-size: 0.65rem;" onclick="removeSubKegiatan(this)">
                    <i class="bi bi-x"></i>
                </button>
            </div>

            <div class="row g-1">
                <div class="col-4">
                    <input type="text" class="form-control form-control-sm" style="font-size: 0.75rem;"
                        name="sasaran[0][indikator][0][program][0][kegiatan][0][subkegiatan][0][kode_sub_kegiatan]"
                        placeholder="Kode" required>
                </div>
                <div class="col-8">
                    <input type="text" class="form-control form-control-sm" style="font-size: 0.75rem;"
                        name="sasaran[0][indikator][0][program][0][kegiatan][0][subkegiatan][0][nama_sub_kegiatan]"
                        placeholder="Nama Sub Kegiatan" required>
                </div>
                <div class="col-4">
                    <input type="number" class="form-control form-control-sm" style="font-size: 0.75rem;"
                        name="sasaran[0][indikator][0][program][0][kegiatan][0][subkegiatan][0][anggaran]"
                        placeholder="Anggaran" step="0.01" required>
                </div>
                <div class="col-4">
                    <input type="number" class="form-control form-control-sm" style="font-size: 0.75rem;"
                        name="sasaran[0][indikator][0][program][0][kegiatan][0][subkegiatan][0][target_value]"
                        placeholder="Target" step="0.01" required>
                </div>
                <div class="col-4">
                    <input type="text" class="form-control form-control-sm" style="font-size: 0.75rem;"
                        name="sasaran[0][indikator][0][program][0][kegiatan][0][subkegiatan][0][satuan]"
                        placeholder="Satuan" required>
                </div>
            </div>
        </div>
    </template>

    <style>
        /* Card Styling */
        .card {
            margin-bottom: 1.5rem;
            border: none;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
            border-radius: 10px;
        }

        .card-title {
            color: #012970;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        /* Preview Styling */
        .info-card {
            background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
            color: white;
        }

        .info-card .card-title {
            color: white;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 0.75rem;
        }

        .preview-section {
            padding-top: 0.5rem;
        }

        .preview-item {
            margin-bottom: 0.5rem;
        }

        .preview-label {
            display: block;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.25rem;
            font-weight: 600;
        }

        .preview-value {
            font-size: 1rem;
            font-weight: 600;
            color: white;
        }

        .preview-detail {
            display: block;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.7);
            line-height: 1.4;
        }

        .preview-count {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            text-align: center;
            margin-top: 0.5rem;
        }

        .info-card hr {
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* Sasaran & Indikator Styling */
        .sasaran-item {
            transition: all 0.3s ease;
            animation: slideIn 0.3s ease-out;
        }

        .sasaran-item:hover {
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.12);
            transform: translateY(-2px);
        }

        .indikator-item {
            transition: all 0.3s ease;
            animation: fadeIn 0.3s ease-out;
        }

        .indikator-item:hover {
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
        }

        /* Program, Kegiatan, Sub Kegiatan */
        .program-item {
            animation: slideIn 0.2s ease-out;
        }

        .kegiatan-item {
            animation: slideIn 0.2s ease-out;
            font-size: 0.9rem;
        }

        .subkegiatan-item {
            animation: slideIn 0.2s ease-out;
            font-size: 0.85rem;
        }

        .btn-xs {
            padding: 0.2rem 0.4rem;
            font-size: 0.75rem;
        }

        .program-container,
        .kegiatan-container,
        .subkegiatan-container {
            margin-top: 0.5rem;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        /* Form Controls */
        .form-control:focus,
        .form-select:focus {
            border-color: #4154f1;
            box-shadow: 0 0 0 0.2rem rgba(65, 84, 241, 0.15);
        }

        .select2-container {
            width: 100% !important;
        }

        .select2-container--bootstrap-5 .select2-selection {
            min-height: 38px;
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, #1e88e5 0%, #1565c0 100%);
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #1e88e5 0%, #26b4f1 100%);
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        /* Empty State */
        #empty-sasaran {
            padding: 3rem 1rem;
        }

        /* Badge */
        .badge {
            padding: 0.4em 0.7em;
            font-weight: 600;
        }

        /* Border Cards */
        .border-warning {
            border-left: 4px solid #ffc107 !important;
        }

        /* Alert */
        .alert-warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
        }

        /* Responsive */
        @media (max-width: 991px) {
            .info-card {
                margin-bottom: 1.5rem;
            }
        }
    </style>
@endsection

@push('scripts')
    <script>
        let sasaranIndex = {{ $pk->sasaran->count() }};

        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Update preview on change
            $('#pegawai_id').change(updatePegawaiPreview);
            $('#atasan_id').change(updateAtasanPreview);
            $('#periode_mulai, #periode_selesai').change(updatePeriodePreview);

            // Real-time update preview
            $('#tahun, #periode_mulai, #periode_selesai').on('input change', updatePeriodePreview);
            $('#formPerjanjianKinerja').on('change input', function() {
                updateAllPreviews();
            });

            // Form validation
            $('#formPerjanjianKinerja').submit(function(e) {
                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();

                    Swal.fire({
                        icon: 'warning',
                        title: 'Form Tidak Lengkap',
                        text: 'Mohon lengkapi semua field yang wajib diisi',
                        confirmButtonColor: '#667eea'
                    });
                }
                $(this).addClass('was-validated');
            });

            // Initialize preview
            updateAllPreviews();
        });

        function updatePegawaiPreview() {
            const selected = $('#pegawai_id option:selected');
            if (selected.val()) {
                $('#preview-pegawai').text(selected.text().split(' - ')[0]);
                $('#preview-pegawai-detail').html(
                    `NIP: ${selected.data('nip')}<br>` +
                    `Jabatan: ${selected.data('jabatan')}<br>` +
                    `Unit: ${selected.data('bidang')}`
                );
            }
        }

        function updateAtasanPreview() {
            const selected = $('#atasan_id option:selected');
            if (selected.val()) {
                $('#preview-atasan').text(selected.text().split(' - ')[0]);
                $('#preview-atasan-detail').html(
                    `NIP: ${selected.data('nip')}<br>` +
                    `Jabatan: ${selected.data('jabatan')}`
                );
            }
        }

        function updatePeriodePreview() {
            const mulai = $('#periode_mulai').val();
            const selesai = $('#periode_selesai').val();
            if (mulai && selesai) {
                const startDate = new Date(mulai).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
                const endDate = new Date(selesai).toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
                $('#preview-periode').text(`${startDate} - ${endDate}`);
            }
        }

        function addSasaran() {
            $('#empty-sasaran').hide();
            const template = document.getElementById('template-sasaran');
            const clone = template.content.cloneNode(true);
            const sasaranItem = clone.querySelector('.sasaran-item');

            sasaranItem.dataset.index = sasaranIndex;
            sasaranItem.querySelector('.sasaran-number').textContent = sasaranIndex + 1;
            sasaranItem.querySelector('textarea[name*="sasaran_strategis"]').name =
                `sasaran[${sasaranIndex}][sasaran_strategis]`;
            sasaranItem.querySelector('input[name*="urutan"]').name = `sasaran[${sasaranIndex}][urutan]`;
            sasaranItem.querySelector('input[name*="urutan"]').value = sasaranIndex + 1;

            $('#sasaran-container').append(sasaranItem);
            sasaranIndex++;
            updateSasaranCount();
        }

        function removeSasaran(btn) {
            Swal.fire({
                title: 'Hapus Sasaran?',
                text: 'Semua indikator di sasaran ini akan ikut terhapus',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash"></i> Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(btn).closest('.sasaran-item').fadeOut(300, function() {
                        $(this).remove();
                        updateSasaranNumbers();
                        updateSasaranCount();
                        if ($('.sasaran-item').length === 0) {
                            $('#empty-sasaran').fadeIn();
                        }
                    });
                }
            });
        }

        function addIndikator(btn) {
            const sasaranItem = $(btn).closest('.sasaran-item');
            const sasaranIdx = sasaranItem.data('index');
            const container = sasaranItem.find('.indikator-container');
            const indikatorCount = container.find('.indikator-item').length;

            const template = document.getElementById('template-indikator');
            const clone = template.content.cloneNode(true);

            clone.querySelectorAll('input').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name.replace(/sasaran\[\d+\]/, `sasaran[${sasaranIdx}]`)
                        .replace(/indikator\[\d+\]/, `indikator[${indikatorCount}]`));
                }
            });

            clone.querySelector('.indikator-number').textContent = indikatorCount + 1;
            container.append(clone);
        }

        function removeIndikator(btn) {
            $(btn).closest('.indikator-item').fadeOut(200, function() {
                $(this).remove();
                updateIndikatorNumbers();
            });
        }

        function addProgram(btn) {
            const indikatorItem = $(btn).closest('.indikator-item');
            const sasaranIdx = $(btn).closest('.sasaran-item').data('index');
            const indikatorIdx = indikatorItem.parent().children('.indikator-item').index(indikatorItem);
            const container = indikatorItem.find('.program-container');
            const programCount = container.find('.program-item').length;

            const template = document.getElementById('template-program');
            const clone = template.content.cloneNode(true);

            clone.querySelectorAll('input').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name
                        .replace(/sasaran\[\d+\]/, `sasaran[${sasaranIdx}]`)
                        .replace(/indikator\[\d+\]/, `indikator[${indikatorIdx}]`)
                        .replace(/program\[\d+\]/, `program[${programCount}]`));
                }
            });

            clone.querySelector('.program-number').textContent = programCount + 1;
            clone.querySelector('input[name*="urutan"]').value = programCount + 1;
            container.append(clone);
        }

        function removeProgram(btn) {
            $(btn).closest('.program-item').fadeOut(200, function() {
                $(this).remove();
            });
        }

        function addKegiatan(btn) {
            const programItem = $(btn).closest('.program-item');
            const indikatorItem = $(btn).closest('.indikator-item');
            const sasaranIdx = $(btn).closest('.sasaran-item').data('index');
            const indikatorIdx = indikatorItem.parent().children('.indikator-item').index(indikatorItem);
            const programIdx = programItem.parent().children('.program-item').index(programItem);
            const container = programItem.find('.kegiatan-container');
            const kegiatanCount = container.find('.kegiatan-item').length;

            const template = document.getElementById('template-kegiatan');
            const clone = template.content.cloneNode(true);

            clone.querySelectorAll('input').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name
                        .replace(/sasaran\[\d+\]/, `sasaran[${sasaranIdx}]`)
                        .replace(/indikator\[\d+\]/, `indikator[${indikatorIdx}]`)
                        .replace(/program\[\d+\]/, `program[${programIdx}]`)
                        .replace(/kegiatan\[\d+\]/, `kegiatan[${kegiatanCount}]`));
                }
            });

            clone.querySelector('.kegiatan-number').textContent = kegiatanCount + 1;
            clone.querySelector('input[name*="urutan"]').value = kegiatanCount + 1;
            container.append(clone);
        }

        function removeKegiatan(btn) {
            $(btn).closest('.kegiatan-item').fadeOut(200, function() {
                $(this).remove();
            });
        }

        function addSubKegiatan(btn) {
            const kegiatanItem = $(btn).closest('.kegiatan-item');
            const programItem = $(btn).closest('.program-item');
            const indikatorItem = $(btn).closest('.indikator-item');
            const sasaranIdx = $(btn).closest('.sasaran-item').data('index');
            const indikatorIdx = indikatorItem.parent().children('.indikator-item').index(indikatorItem);
            const programIdx = programItem.parent().children('.program-item').index(programItem);
            const kegiatanIdx = kegiatanItem.parent().children('.kegiatan-item').index(kegiatanItem);
            const container = kegiatanItem.find('.subkegiatan-container');
            const subKegiatanCount = container.find('.subkegiatan-item').length;

            const template = document.getElementById('template-subkegiatan');
            const clone = template.content.cloneNode(true);

            clone.querySelectorAll('input').forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    input.setAttribute('name', name
                        .replace(/sasaran\[\d+\]/, `sasaran[${sasaranIdx}]`)
                        .replace(/indikator\[\d+\]/, `indikator[${indikatorIdx}]`)
                        .replace(/program\[\d+\]/, `program[${programIdx}]`)
                        .replace(/kegiatan\[\d+\]/, `kegiatan[${kegiatanIdx}]`)
                        .replace(/subkegiatan\[\d+\]/, `subkegiatan[${subKegiatanCount}]`));
                }
            });

            clone.querySelector('.subkegiatan-number').textContent = subKegiatanCount + 1;
            container.append(clone);
        }

        function removeSubKegiatan(btn) {
            $(btn).closest('.subkegiatan-item').fadeOut(200, function() {
                $(this).remove();
            });
        }

        function updateSasaranNumbers() {
            $('.sasaran-item').each(function(index) {
                $(this).find('.sasaran-number').text(index + 1);
                $(this).find('input[name*="urutan"]').val(index + 1);
            });
        }

        function updateIndikatorNumbers() {
            $('.sasaran-item').each(function() {
                $(this).find('.indikator-item').each(function(index) {
                    $(this).find('.indikator-number').text(index + 1);
                });
            });
        }

        function updateSasaranCount() {
            $('#preview-total-sasaran').text($('.sasaran-item').length);
        }

        function updateAllPreviews() {
            updatePegawaiPreview();
            updateAtasanPreview();
            updatePeriodePreview();
            updateSasaranCount();
        }
    </script>
@endpush
