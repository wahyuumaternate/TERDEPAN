@extends('layouts.main')

@section('main')
    <section class="section terminal-data-page">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <!-- Row 1: Breadcrumb + Grid/Table toggle + Info -->
                <div
                    class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
                    <nav aria-label="breadcrumb" class="pagetitle" style="margin-bottom: 0; padding: 0; min-width: 0;">
                        <div class="d-flex align-items-center flex-nowrap" style="gap: 8px; font-size: 1.1rem; min-width: 0;">
                            @if (isset($folder))
                                @php
                                    $breadcrumbs = $folder->getBreadcrumb();
                                    $totalLevels = count($breadcrumbs);
                                    $maxVisibleLevels = 3; // Maximum breadcrumbs to show before collapsing
                                @endphp

                                @if ($totalLevels > $maxVisibleLevels)
                                    {{-- Show "..." dropdown for hidden breadcrumbs --}}
                                    <div class="dropdown">
                                        <button
                                            class="btn btn-link text-dark fw-semibold text-decoration-none p-0 dropdown-toggle"
                                            type="button" id="hiddenBreadcrumbsDropdown" data-bs-toggle="dropdown"
                                            aria-expanded="false" style="font-size: 1.1rem;">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="hiddenBreadcrumbsDropdown">
                                            @foreach ($breadcrumbs as $index => $crumb)
                                                @if ($index < $totalLevels - $maxVisibleLevels + 1)
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('terminaldata.folder.detail', $crumb->id) }}">
                                                            <i class="bi bi-folder me-2"></i>{{ $crumb->name }}
                                                        </a>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                    <span class="text-muted">&gt;</span>

                                    {{-- Show last visible breadcrumbs --}}
                                    @foreach ($breadcrumbs as $index => $crumb)
                                        @if ($index >= $totalLevels - $maxVisibleLevels + 1)
                                            @if (!$loop->last)
                                                <a href="{{ route('terminaldata.folder.detail', $crumb->id) }}"
                                                    class="text-decoration-none text-dark fw-semibold breadcrumb-segment"
                                                    style="cursor: pointer;" title="{{ $crumb->name }}">
                                                    {{ $crumb->name }}
                                                </a>
                                                <span class="text-muted">&gt;</span>
                                            @else
                                                {{-- Current folder with dropdown --}}
                                                <div class="dropdown">
                                                    <button
                                                        class="btn btn-link text-dark fw-semibold text-decoration-none p-0 dropdown-toggle breadcrumb-segment"
                                                        type="button" id="currentFolderDropdown" data-bs-toggle="dropdown"
                                                        aria-expanded="false" style="font-size: 1.1rem;"
                                                        title="{{ $crumb->name }}">
                                                        {{ $crumb->name }}
                                                    </button>
                                                    <ul class="dropdown-menu" aria-labelledby="currentFolderDropdown">
                                                        <li><span
                                                                class="dropdown-item-text"><strong>{{ $crumb->name }}</strong></span>
                                                        </li>
                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                        <li><a class="dropdown-item" href="#"
                                                                onclick="renameFolder('{{ $crumb->id }}', '{{ addslashes($crumb->name) }}'); return false;">
                                                                <i class="bi bi-pencil me-2"></i>Ganti Nama</a></li>
                                                        <li><a class="dropdown-item" href="#"><i
                                                                    class="bi bi-share me-2"></i>Bagikan</a></li>
                                                        <li><a class="dropdown-item" href="#"
                                                                onclick="openInfoPanel('folder', '{{ $crumb->id }}'); return false;">
                                                                <i class="bi bi-info-circle me-2"></i>Info Folder</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            @endif
                                        @endif
                                    @endforeach
                                @else
                                    {{-- Show all breadcrumbs if less than or equal to maxVisibleLevels --}}
                                    @foreach ($breadcrumbs as $index => $crumb)
                                        @if (!$loop->last)
                                            <a href="{{ route('terminaldata.folder.detail', $crumb->id) }}"
                                                class="text-decoration-none text-dark fw-semibold breadcrumb-segment"
                                                style="cursor: pointer;" title="{{ $crumb->name }}">
                                                {{ $crumb->name }}
                                            </a>
                                            <span class="text-muted">&gt;</span>
                                        @else
                                            <div class="dropdown">
                                                <button
                                                    class="btn btn-link text-dark fw-semibold text-decoration-none p-0 dropdown-toggle breadcrumb-segment"
                                                    type="button" id="currentFolderDropdown" data-bs-toggle="dropdown"
                                                    aria-expanded="false" style="font-size: 1.1rem;"
                                                    title="{{ $crumb->name }}">
                                                    {{ $crumb->name }}
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="currentFolderDropdown">
                                                    <li><span
                                                            class="dropdown-item-text"><strong>{{ $crumb->name }}</strong></span>
                                                    </li>
                                                    <li>
                                                        <hr class="dropdown-divider">
                                                    </li>
                                                    @can('rename', $crumb)
                                                        <li><a class="dropdown-item" href="#"><i
                                                                    class="bi bi-pencil me-2"></i>Ganti Nama</a></li>
                                                    @endcan
                                                    <li><a class="dropdown-item" href="#"><i
                                                                class="bi bi-share me-2"></i>Bagikan</a></li>
                                                    <li><a class="dropdown-item" href="#"
                                                            onclick="openInfoPanel('folder', '{{ $crumb->id }}'); return false;">
                                                            <i class="bi bi-info-circle me-2"></i>Info Folder</a></li>
                                                </ul>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            @else
                                <span class="fw-semibold">Terminal Data</span>
                            @endif
                        </div>
                    </nav>

                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        <div class="btn-group shadow-sm" role="group">
                            <input type="radio" class="btn-check" name="viewMode" id="viewGrid" checked>
                            <label class="btn btn-sm btn-outline-primary" for="viewGrid">
                                <i class="bi bi-grid"></i>
                            </label>
                            <input type="radio" class="btn-check" name="viewMode" id="viewTable">
                            <label class="btn btn-sm btn-outline-primary" for="viewTable">
                                <i class="bi bi-list"></i>
                            </label>
                        </div>
                        @if (isset($folder))
                            <button type="button" class="btn btn-sm btn-outline-secondary shadow-sm"
                                data-bs-toggle="tooltip" data-bs-placement="top" title="Informasi folder"
                                onclick="openInfoPanel('folder', '{{ $folder->id }}')">
                                <i class="bi bi-info-circle"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Row 2: Aksi (Baru, Filter, Sort) -->
                <div class="d-flex flex-wrap justify-content-start gap-2 mb-4">
                    @php
                        $user = auth()->user();
                        $kodeJabatan = $user->profile?->jabatan?->kode;
                        $canCreateInFolder = false;

                        // ADMIN, KABAN, SEKBAN bisa buat folder di mana saja
                        if (in_array($kodeJabatan, ['ADMIN', 'KABAN', 'SEKBAN'])) {
                            $canCreateInFolder = true;
                        }
                        // KABID, KASUBAG, PELAKSANA, JAFUNG, GATEK hanya bisa buat di folder bidangnya
                        elseif (in_array($kodeJabatan, ['KABID', 'KASUBAG', 'PELAKSANA', 'JAFUNG', 'GATEK'])) {
                            $canCreateInFolder = $folder->bidang_id === $user->profile?->bidang_id;
                        }
                    @endphp

                    <!-- Filter -->
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                        <div class="dropdown-menu p-3 shadow-sm" style="min-width: 260px;">
                            <div class="mb-3">
                                <div class="fw-semibold small text-muted mb-2">Jenis</div>
                                <div class="form-check">
                                    <input class="form-check-input filter-jenis" type="checkbox" value="pdf"
                                        id="filterJenisPdf">
                                    <label class="form-check-label small" for="filterJenisPdf">PDF</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input filter-jenis" type="checkbox" value="word"
                                        id="filterJenisWord">
                                    <label class="form-check-label small" for="filterJenisWord">Word</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input filter-jenis" type="checkbox" value="excel"
                                        id="filterJenisExcel">
                                    <label class="form-check-label small" for="filterJenisExcel">Excel</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input filter-jenis" type="checkbox" value="powerpoint"
                                        id="filterJenisPowerpoint">
                                    <label class="form-check-label small" for="filterJenisPowerpoint">PowerPoint</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input filter-jenis" type="checkbox" value="image"
                                        id="filterJenisImage">
                                    <label class="form-check-label small" for="filterJenisImage">Gambar</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input filter-jenis" type="checkbox" value="other"
                                        id="filterJenisOther">
                                    <label class="form-check-label small" for="filterJenisOther">Lainnya</label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="fw-semibold small text-muted mb-2">Orang</div>
                                <div id="filterOrangList">
                                    <span class="text-muted small">Tidak ada data</span>
                                </div>
                            </div>

                            <div class="mb-2">
                                <div class="fw-semibold small text-muted mb-2">Di Modifikasi</div>
                                <select class="form-select form-select-sm" id="filterModifikasi">
                                    <option value="">Kapan saja</option>
                                    <option value="today">Hari ini</option>
                                    <option value="7days">7 hari terakhir</option>
                                    <option value="30days">30 hari terakhir</option>
                                    <option value="year">Tahun ini</option>
                                </select>
                            </div>

                            <hr class="my-2">
                            <button type="button" class="btn btn-sm btn-link text-decoration-none p-0"
                                id="btnResetFilter">
                                <i class="bi bi-arrow-clockwise me-1"></i>Reset filter
                            </button>
                        </div>
                    </div>

                    <!-- Sort -->
                    <div class="dropdown">
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-sort-down me-1"></i>Urutkan
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item sort-option active" href="#" data-sort="name_asc">Nama
                                    (A-Z)</a></li>
                            <li><a class="dropdown-item sort-option" href="#" data-sort="name_desc">Nama (Z-A)</a></li>
                            <li><a class="dropdown-item sort-option" href="#" data-sort="updated_desc">Baru
                                    diubah</a></li>
                            <li><a class="dropdown-item sort-option" href="#" data-sort="updated_asc">Lama diubah</a>
                            </li>
                            <li><a class="dropdown-item sort-option" href="#" data-sort="size_desc">Ukuran
                                    terbesar</a></li>
                            <li><a class="dropdown-item sort-option" href="#" data-sort="size_asc">Ukuran
                                    terkecil</a></li>
                        </ul>
                    </div>

                    <!-- Baru -->
                    @if ($canCreateInFolder || $user->can('upload', [$folder]))
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="bi bi-plus-lg me-1"></i>Baru
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if ($canCreateInFolder)
                                    <li>
                                        <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target="#modalTambahFolder">
                                            <i class="bi bi-folder-plus me-2"></i>Folder Baru
                                        </button>
                                    </li>
                                @endif
                                @can('upload', [$folder])
                                    <li>
                                        <button type="button" class="dropdown-item" id="btnTriggerUpload"
                                            data-bs-toggle="tooltip" data-bs-placement="left"
                                            title="Maksimal 100MB per file">
                                            <i class="bi bi-cloud-upload me-2"></i>Upload Dokumen
                                        </button>
                                    </li>
                                @endcan
                            </ul>
                        </div>
                    @endif

                    @can('upload', [$folder])
                        <form id="formUploadDokumen" class="d-none">
                            @csrf
                            <input type="hidden" name="folder_id" value="{{ $folder->id }}">
                            <input type="file" id="fileUpload" name="file" style="display: none;"
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.bmp,.svg,.webp" multiple>
                        </form>
                    @endcan
                </div>

                <div class="terminal-data-scroll">
                    <!-- Grid View -->
                    <div id="gridView">
                    <!-- Subfolders Section -->
                    <div id="subfoldersSection" style="display: none;">
                        <!-- Skeleton Loading for Subfolders -->
                        <div class="td-grid mb-3" id="subfoldersSkeleton">
                            <div class="td-grid-item">
                                <div class="skeleton-folder-card">
                                    <div class="skeleton-folder-content">
                                        <div class="skeleton skeleton-folder-icon"></div>
                                        <div class="skeleton-folder-info">
                                            <div class="skeleton skeleton-title"></div>
                                            <div class="skeleton skeleton-text"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="td-grid-item">
                                <div class="skeleton-folder-card">
                                    <div class="skeleton-folder-content">
                                        <div class="skeleton skeleton-folder-icon"></div>
                                        <div class="skeleton-folder-info">
                                            <div class="skeleton skeleton-title"></div>
                                            <div class="skeleton skeleton-text"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="td-grid-item">
                                <div class="skeleton-folder-card">
                                    <div class="skeleton-folder-content">
                                        <div class="skeleton skeleton-folder-icon"></div>
                                        <div class="skeleton-folder-info">
                                            <div class="skeleton skeleton-title"></div>
                                            <div class="skeleton skeleton-text"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="td-grid-item">
                                <div class="skeleton-folder-card">
                                    <div class="skeleton-folder-content">
                                        <div class="skeleton skeleton-folder-icon"></div>
                                        <div class="skeleton-folder-info">
                                            <div class="skeleton skeleton-title"></div>
                                            <div class="skeleton skeleton-text"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="td-grid mb-3" id="subfoldersGrid"></div>
                    </div>

                    <!-- Documents Section -->
                    <!-- Skeleton Loading for Files -->
                    <div class="td-grid mb-3" id="filesSkeleton">
                        <div class="td-grid-item">
                            <div class="skeleton-card">
                                <div class="skeleton-preview">
                                    <div class="skeleton-icon"></div>
                                </div>
                                <div class="skeleton-body">
                                    <div class="skeleton skeleton-title"></div>
                                    <div class="skeleton skeleton-text"></div>
                                </div>
                            </div>
                        </div>
                        <div class="td-grid-item">
                            <div class="skeleton-card">
                                <div class="skeleton-preview">
                                    <div class="skeleton-icon"></div>
                                </div>
                                <div class="skeleton-body">
                                    <div class="skeleton skeleton-title"></div>
                                    <div class="skeleton skeleton-text"></div>
                                </div>
                            </div>
                        </div>
                        <div class="td-grid-item">
                            <div class="skeleton-card">
                                <div class="skeleton-preview">
                                    <div class="skeleton-icon"></div>
                                </div>
                                <div class="skeleton-body">
                                    <div class="skeleton skeleton-title"></div>
                                    <div class="skeleton skeleton-text"></div>
                                </div>
                            </div>
                        </div>
                        <div class="td-grid-item">
                            <div class="skeleton-card">
                                <div class="skeleton-preview">
                                    <div class="skeleton-icon"></div>
                                </div>
                                <div class="skeleton-body">
                                    <div class="skeleton skeleton-title"></div>
                                    <div class="skeleton skeleton-text"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Files Grid -->
                    <div class="td-grid" id="dokumenGrid"></div>

                    <!-- Empty State -->
                    <div class="empty-state text-center" id="filesEmptyState" style="display: none;">
                        <i class="bi bi-file-earmark-x empty-state-icon"></i>
                        <h4 class="text-muted mb-2">Belum ada dokumen</h4>
                        <p class="text-muted mb-4">Mulai dengan upload dokumen pertama Anda</p>
                        @can('upload', [$folder])
                            <button class="btn btn-primary btn-sm px-3" id="btnTriggerUploadEmpty">
                                <i class="bi bi-cloud-upload me-2"></i>Upload Dokumen
                            </button>
                        @endcan
                    </div>
                </div>

                <!-- Table View -->
                <div id="tableView" style="display: none;">
                    <h6 class="text-muted mb-3">
                        <i class="bi bi-folder-fill me-2"></i>Folder & File
                    </h6>
                    <table class="table" id="combinedTable">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Pemilik</th>
                                <th>Tanggal Diubah</th>
                                <th>Ukuran/Total</th>
                                <th width="80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="combinedTableBody">
                            <!-- Will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
                </div>
            </div>
        </div>
    </section>

    @include('terminaldata::partials.info-panel')

    <!-- Upload Progress Modal -->
    <div class="modal fade" id="modalUploadProgress" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3">
                        <i class="bi bi-cloud-upload text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="mb-3">Mengupload Dokumen</h5>
                    <div class="progress mb-3" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar"
                            style="width: 0%" id="uploadProgressBar"></div>
                    </div>
                    <p class="text-muted mb-0" id="uploadProgressText">Mempersiapkan upload...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Rename Folder -->
    <div class="modal fade" id="modalRenameFolder" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>Ganti Nama Folder
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formRenameFolder">
                        @csrf
                        <input type="hidden" id="rename_folder_id">

                        <div class="mb-3">
                            <label for="folder_new_name" class="form-label fw-semibold">
                                Nama Folder Baru <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-lg" id="folder_new_name"
                                name="name" placeholder="Masukkan nama folder baru" required>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Nama folder harus unik dalam folder yang sama
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Batal
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Rename File -->
    <div class="modal fade" id="modalRenameFile" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>Ganti Nama File
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formRenameFile">
                        @csrf
                        <input type="hidden" id="rename_file_id">

                        <div class="mb-3">
                            <label for="file_new_name" class="form-label fw-semibold">
                                Nama File Baru <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-lg" id="file_new_name" name="name"
                                placeholder="Masukkan nama file baru" required>
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Ekstensi file akan tetap sama
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Batal
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit (similar structure to Upload modal with improvements) -->
    <div class="modal fade" id="modalEditDokumen" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>Edit Dokumen
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formEditDokumen" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_dokumen_id" name="dokumen_id">
                        <input type="hidden" name="folder_id" value="{{ $folder->id }}">

                        <div class="mb-4">
                            <div class="alert alert-warning d-flex">
                                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                                <div>
                                    <strong>Perhatian:</strong> Dokumen akan tetap disimpan di folder
                                    <strong>{{ $folder->nama }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_judul" class="form-label fw-semibold">
                                Judul Dokumen <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-lg" id="edit_judul" name="judul"
                                required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_nomor_surat" class="form-label fw-semibold">Nomor Surat</label>
                                <input type="text" class="form-control" id="edit_nomor_surat" name="nomor_surat">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_tanggal_dokumen" class="form-label fw-semibold">
                                    Tanggal Dokumen <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="edit_tanggal_dokumen"
                                    name="tanggal_dokumen" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_deskripsi" class="form-label fw-semibold">Deskripsi</label>
                            <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="edit_status" class="form-label fw-semibold">Status Dokumen</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="edit_statusDraft"
                                        value="Draft">
                                    <label class="form-check-label" for="edit_statusDraft">
                                        Draft
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="edit_statusFinal"
                                        value="Final">
                                    <label class="form-check-label" for="edit_statusFinal">
                                        Final
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status"
                                        id="edit_statusArchived" value="Archived">
                                    <label class="form-check-label" for="edit_statusArchived">
                                        Archived
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">File Saat Ini</label>
                            <div id="current_file_info" class="alert alert-info d-flex align-items-center">
                                <div class="spinner-border spinner-border-sm me-2" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <div>Memuat informasi file...</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_file" class="form-label fw-semibold">Upload File Baru (Opsional)</label>
                            <input type="file" class="form-control" id="edit_file" name="file">
                            <div class="form-text" id="editFileHelp">
                                Format: PDF, DOCX, XLSX (Max: 10MB). Kosongkan jika tidak ingin mengganti file.
                            </div>
                            <div class="alert alert-light mt-2 p-2 small">
                                <i class="bi bi-info-circle-fill me-2"></i>
                                Mengganti file akan membuat versi baru dokumen
                            </div>
                        </div>

                        <div id="editProgress" class="mt-3" style="display: none;">
                            <label class="form-label fw-semibold">Proses Update</label>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"
                                    style="width: 0%"></div>
                            </div>
                            <p class="text-center mt-2 mb-0" id="editProgressText">Mempersiapkan update...</p>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-warning px-4" id="btnUpdateDokumen">
                        <i class="bi bi-save me-1"></i>Update Dokumen
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail -->
    <div class="modal fade" id="modalDetailDokumen" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-file-earmark-text me-2"></i>Detail Dokumen
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="detailDokumenContent">
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memuat detail dokumen...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>Tutup
                        </button>
                        <button type="button" class="btn btn-warning" id="btnEditFromDetail" data-id="">
                            <i class="bi bi-pencil me-1"></i>Edit
                        </button>
                        <button type="button" class="btn btn-primary" id="btnDownloadDokumen" data-id="">
                            <i class="bi bi-download me-1"></i>Download
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Subfolder -->
    <div class="modal fade" id="modalTambahFolder" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-success text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-folder-plus me-2"></i>Folder Baru
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formTambahFolder">
                        @csrf
                        <input type="hidden" name="parent_id" value="{{ $folder->id }}">

                        <div class="mb-3">
                            <label for="nama_folder" class="form-label fw-semibold">
                                <i class="bi bi-folder me-2"></i>Nama<span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-lg" id="nama_folder" name="nama"
                                placeholder="Contoh: Laporan 2025" required maxlength="100">
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-success px-4" id="btnSaveFolder">
                        <i class="bi bi-check-circle me-1"></i>Buat
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Upload Progress (Bottom Right) -->
    <div id="uploadProgressModal" style="display: none;">
        <div class="upload-modal-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong id="uploadModalTitle">Mengupload <span id="uploadCount">0</span> file</strong>
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-link text-white p-0" id="minimizeUpload">
                        <i class="bi bi-dash-lg"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-link text-white p-0 ms-2" id="closeUpload">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="upload-modal-body" id="uploadModalBody">
            <!-- Upload items will be added here dynamically -->
        </div>
    </div>

    <!-- Modal PDF Preview -->
    <div class="modal fade" id="modalPdfPreview" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="pdfPreviewTitle">
                        <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>
                        <span id="pdfFileName">Preview PDF</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="height: 80vh;">
                    <div id="pdfPreviewLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Memuat PDF...</p>
                    </div>
                    <iframe id="pdfPreviewFrame" style="width: 100%; height: 100%; border: none; display: none;"
                        frameborder="0">
                    </iframe>
                </div>
                <div class="modal-footer border-0">
                    <a id="btnDownloadPdf" href="#" class="btn btn-primary" download>
                        <i class="bi bi-download me-1"></i> Download
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Image Preview -->
    <div class="modal fade" id="modalImagePreview" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="imagePreviewTitle">
                        <i class="bi bi-file-earmark-image-fill text-warning me-2"></i>
                        <span id="imageFileName">Preview Gambar</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="height: 80vh; background-color: #f8f9fa;">
                    <div id="imagePreviewLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Memuat gambar...</p>
                    </div>
                    <div id="imagePreviewContainer"
                        style="width: 100%; height: 100%; display: none; overflow: auto; text-align: center;">
                        <img id="imagePreviewImg" style="max-width: 100%; max-height: 100%; object-fit: contain;" />
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <a id="btnDownloadImage" href="#" class="btn btn-primary" download>
                        <i class="bi bi-download me-1"></i> Download
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Office Document Preview (Word, Excel, PowerPoint) -->
    <div class="modal fade" id="modalOfficePreview" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="officePreviewTitle">
                        <i class="bi bi-file-earmark-text-fill text-primary me-2" id="officePreviewIcon"></i>
                        <span id="officeFileName">Preview Dokumen</span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="height: 80vh;">
                    <div id="officePreviewLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Memuat preview dokumen...</p>
                        <small class="text-muted d-block mt-2">Menggunakan Microsoft Office Online Viewer</small>
                    </div>
                    <iframe id="officePreviewFrame" style="width: 100%; height: 100%; border: none; display: none;"
                        frameborder="0">
                    </iframe>
                    <div id="officePreviewError" style="display: none;" class="text-center py-5">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 48px;"></i>
                        <p class="mt-3 text-muted">Preview tidak tersedia untuk file ini</p>
                        <small class="text-muted">Silakan download file untuk melihat isinya</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <a id="btnDownloadOffice" href="#" class="btn btn-primary" download>
                        <i class="bi bi-download me-1"></i> Download
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @include('terminaldata::partials.info-panel')
@endsection

@push('styles')
    <style>
        /* Halaman ini satu card penuh (bukan lagi breadcrumb terpisah + card terpisah), tinggi
           PASTI mengisi sisa viewport — bukan tebakan calc(100vh - Npx) yang gampang meleset
           kalau tinggi footer berubah, tapi height:100% yang resolve ke tinggi #main
           sesungguhnya. #main sudah flex:1 0 auto terhadap body (flex-column, min-height:100vh)
           dan sudah punya margin-top:60px (mengalah untuk #header yang fixed) — jadi tinggi
           #main hasil resolve flex-grow itu SUDAH otomatis = sisa viewport dikurangi header
           dan footer sungguhan (bukan tebakan), dan .terminal-data-page (anak langsung #main)
           tinggal height:100% untuk mengisinya persis. Dipakai height (bukan min-height) supaya
           card TIDAK ikut tumbuh mengikuti isi — breadcrumb/toolbar/filter tetap diam di atas,
           dan section folder/file di bawahnya (.terminal-data-scroll) yang scroll sendiri kalau
           isinya melebihi tinggi ini, bukan seluruh halaman yang memanjang. */
        .terminal-data-page {
            display: flex;
            flex-direction: column;
            height: 100%;
            /* Menyempit saat info panel dibuka (lihat body.info-panel-open di blok Info Panel
               di bawah) supaya panel tampil berdampingan, bukan menutupi konten utama. */
            transition: margin-right 0.25s ease;
        }

        .terminal-data-page>.card {
            flex: 1 1 auto;
            width: 100%;
            min-height: 0;
        }

        .terminal-data-page>.card>.card-body {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 0;
        }

        /* min-height:0 wajib — tanpa ini flex child dengan overflow-y:auto tidak akan pernah
           benar-benar scroll, dia akan terus tumbuh mengikuti isi (gotcha flexbox yang sama
           seperti kasus min-width:0 pada breadcrumb/info panel). overflow-x:hidden sebagai
           jaring pengaman tambahan (lihat .td-grid di bawah untuk grid folder/file itu sendiri). */
        .terminal-data-scroll {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
        }

        /* Grid folder/file (#subfoldersGrid, #dokumenGrid, + skeleton loading-nya) — sengaja
           CSS Grid dengan auto-fill/minmax, BUKAN "row"+"col-*" Bootstrap yang breakpoint-nya
           berbasis lebar VIEWPORT. Saat panel info dibuka, .terminal-data-page menyempit lewat
           margin-right tapi lebar viewport-nya sendiri tidak berubah — kalau masih pakai
           col-lg-3/col-xl-2, jumlah kolom tetap dipaksa sama seperti saat panel tertutup,
           sehingga tiap kartu jadi gepeng karena harus berbagi ruang yang sudah menyempit.
           auto-fill/minmax menghitung jumlah kolom dari lebar CONTAINER sesungguhnya, jadi
           otomatis menyesuaikan baik saat panel terbuka maupun tertutup. */
        .td-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 1rem;
        }

        /* Breadcrumb Navigation Styles */
        .pagetitle {
            margin-bottom: 1rem;
            padding: 0.75rem 0;
        }

        .pagetitle .d-flex {
            align-items: center;
            gap: 4px;
        }

        /* Mobile Responsiveness */
        @media (max-width: 576px) {
            .pagetitle {
                font-size: 0.9rem;
            }

            .card-body {
                padding: 1rem !important;
            }

            .file-card-header .file-card-title {
                font-size: 12px;
            }

            .file-extension {
                font-size: 10px;
                padding: 3px 8px;
            }

            .file-icon-preview i {
                font-size: 48px;
            }
        }

        .pagetitle a {
            color: #202124;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .pagetitle a:hover {
            color: #1a73e8;
        }

        .pagetitle .dropdown-toggle::after {
            margin-left: 0.3em;
            vertical-align: 0.15em;
        }

        .pagetitle .text-muted {
            color: #5f6368 !important;
            font-weight: 400;
        }

        /* Nama folder cuma dipotong "..." kalau memang sudah tidak muat di sisa ruang baris
           breadcrumb (baris ini flex-nowrap) — bukan dipotong ke lebar tetap yang sama untuk
           semua nama. min-width:0 melepas batas bawaan flex item (yang sebesar konten
           penuhnya) supaya elemen ini benar-benar boleh menyusut duluan sebelum tombol
           toggle/filter/sort/info di kanan ikut terdesak; overflow+ellipsis baru terlihat
           setelah flexbox benar-benar menyusutkannya. */
        .breadcrumb-segment {
            min-width: 0;
            flex-shrink: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: bottom;
        }

        /* Google Drive Style Folder Cards */
        .gdrive-folder-card {
            background: white;
            border: 1px solid #dadce0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            overflow: visible;
            position: relative;
        }

        .gdrive-folder-card:hover {
            box-shadow: 0 1px 2px 0 rgba(60, 64, 67, .3), 0 2px 6px 2px rgba(60, 64, 67, .15);
            border-color: transparent;
        }

        .gdrive-folder-content {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            gap: 12px;
        }

        .gdrive-folder-icon {
            flex-shrink: 0;
        }

        .gdrive-folder-icon i {
            font-size: 24px;
            color: #5f6368;
        }

        .gdrive-folder-card:hover .gdrive-folder-icon i {
            color: #1a73e8;
        }

        .gdrive-folder-info {
            flex: 1;
            min-width: 0;
        }

        .gdrive-folder-title {
            font-size: 14px;
            color: #202124;
            font-weight: 400;
            line-height: 20px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-bottom: 2px;
        }

        .gdrive-folder-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px;
        }

        .gdrive-folder-meta small,
        .gdrive-folder-meta span {
            font-size: 12px;
            color: #5f6368;
        }

        .gdrive-folder-meta .text-muted {
            color: #5f6368 !important;
        }

        .gdrive-folder-menu {
            opacity: 0;
            transition: opacity 0.2s ease;
            flex-shrink: 0;
        }

        .gdrive-folder-card:hover .gdrive-folder-menu {
            opacity: 1;
        }

        .gdrive-folder-menu .btn-link {
            color: #5f6368;
            text-decoration: none;
            padding: 4px;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gdrive-folder-menu .btn-link:hover {
            background-color: #f1f3f4;
            color: #202124;
        }

        /* Dropdown menu styling */
        .gdrive-folder-menu .dropdown-menu {
            border: 1px solid #dadce0;
            box-shadow: 0 1px 2px 0 rgba(60, 64, 67, .3), 0 2px 6px 2px rgba(60, 64, 67, .15);
            border-radius: 8px;
            padding: 8px 0;
            min-width: 160px;
        }

        .gdrive-folder-menu .dropdown-item {
            padding: 10px 16px;
            font-size: 14px;
            color: #202124;
        }

        .gdrive-folder-menu .dropdown-item:hover {
            background-color: #f1f3f4;
        }

        .gdrive-folder-menu .dropdown-item i {
            width: 20px;
            text-align: center;
        }

        .gdrive-folder-menu .dropdown-divider {
            margin: 8px 0;
            border-color: #e8eaed;
        }

        /* Google Drive Style Document Cards */
        .gdrive-card {
            background: white;
            border: 1px solid #e8eaed;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .gdrive-card:hover {
            box-shadow: 0 1px 3px 0 rgba(60, 64, 67, .3), 0 4px 8px 3px rgba(60, 64, 67, .15);
            border-color: transparent;
        }

        .gdrive-card-preview {
            background: #f1f3f4;
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid #e8eaed;
            min-height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gdrive-card-preview i {
            font-size: 64px;
        }

        .gdrive-card-body {
            padding: 12px 16px;
            flex: 1;
        }

        .gdrive-card-title {
            font-size: 14px;
            color: #202124;
            font-weight: 400;
            line-height: 20px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            margin-bottom: 8px;
        }

        .gdrive-card-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .gdrive-card-meta small {
            font-size: 12px;
            color: #5f6368;
        }

        .gdrive-card-actions {
            padding: 8px 12px;
            border-top: 1px solid #e8eaed;
            display: flex;
            gap: 4px;
            opacity: 1;
            transition: opacity 0.2s ease;
            position: relative;
            z-index: 10;
        }

        .gdrive-card:hover .gdrive-card-actions {
            opacity: 1;
        }

        .gdrive-card-actions .gdrive-folder-menu {
            width: 100%;
            display: flex;
            justify-content: flex-end;
        }

        .gdrive-card-actions .gdrive-folder-menu .btn-link {
            color: #5f6368;
            text-decoration: none;
            padding: 4px;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gdrive-card-actions .gdrive-folder-menu .btn-link:hover {
            background-color: #f1f3f4;
            color: #202124;
        }

        /* New File Card Styles with Thumbnail Support */
        .file-card {
            background: white;
            border: 1px solid #e8eaed;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: visible;
            position: relative;
        }

        .file-card:hover {
            box-shadow: 0 1px 3px 0 rgba(60, 64, 67, .3), 0 4px 8px 3px rgba(60, 64, 67, .15);
            border-color: transparent;
        }

        /* File card header with name and action button */
        .file-card-header {
            background: white;
            padding: 10px 12px;
            border-bottom: 1px solid #e8eaed;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            min-height: 48px;
            position: relative;
            z-index: 10;
        }

        .file-card-header .file-card-title {
            font-size: 13px;
            color: #202124;
            font-weight: 500;
            line-height: 18px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1;
            margin: 0;
        }

        .file-card-header .file-card-menu {
            position: relative;
            opacity: 1;
            flex-shrink: 0;
        }

        .file-card-header .file-card-menu .btn {
            width: 28px;
            height: 28px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            color: #5f6368;
            box-shadow: none;
        }

        .file-card-header .file-card-menu .btn:hover {
            background: #f1f3f4;
            color: #202124;
            box-shadow: none;
            border-radius: 50%;
        }

        .file-card-header .file-card-menu .dropdown-menu {
            z-index: 1050;
        }

        .file-card-preview {
            background: #f8f9fa;
            min-height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            border-radius: 0;
        }

        .file-thumbnail-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .file-icon-preview {
            width: 100%;
            height: 180px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
        }

        .file-icon-preview i {
            font-size: 64px;
            color: white;
            margin-bottom: 12px;
        }

        .file-extension {
            background: rgba(255, 255, 255, 0.9);
            color: #202124;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Different gradient colors for file types */
        .file-icon-preview[data-type="pdf"] {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .file-icon-preview[data-type="doc"],
        .file-icon-preview[data-type="docx"] {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .file-icon-preview[data-type="xls"],
        .file-icon-preview[data-type="xlsx"] {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .file-icon-preview[data-type="ppt"],
        .file-icon-preview[data-type="pptx"] {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .file-icon-preview[data-type="image"],
        .file-icon-preview[data-type="jpg"],
        .file-icon-preview[data-type="jpeg"],
        .file-icon-preview[data-type="png"],
        .file-icon-preview[data-type="gif"],
        .file-icon-preview[data-type="bmp"],
        .file-icon-preview[data-type="webp"] {
            background: linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%);
        }

        /* File card footer with file size */
        .file-card-footer {
            background: white;
            padding: 8px 12px;
            border-top: 1px solid #e8eaed;
            font-size: 12px;
            color: #5f6368;
        }

        /* Empty State */
        .empty-state {
            padding: 4rem 2rem;
        }

        .empty-state-icon {
            font-size: 6rem;
            color: #e0e0e0;
            margin-bottom: 2rem;
        }

        /* Table Styles */
        .datatable-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }

        /* Skeleton Loading Styles */
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s ease-in-out infinite;
            border-radius: 4px;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }

        .skeleton-card {
            background: white;
            border: 1px solid #e8eaed;
            border-radius: 8px;
            height: 100%;
            overflow: hidden;
        }

        .skeleton-preview {
            height: 140px;
            background: #f1f3f4;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #e8eaed;
        }

        .skeleton-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(90deg, #e0e0e0 25%, #d0d0d0 50%, #e0e0e0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s ease-in-out infinite;
            border-radius: 8px;
        }

        .skeleton-body {
            padding: 12px;
        }

        .skeleton-title {
            height: 16px;
            width: 70%;
            margin-bottom: 8px;
        }

        .skeleton-text {
            height: 12px;
            width: 40%;
        }

        .skeleton-folder-card {
            background: white;
            border: 1px solid #e8eaed;
            border-radius: 8px;
            padding: 16px;
            height: 100%;
        }

        .skeleton-folder-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .skeleton-folder-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
        }

        .skeleton-folder-info {
            flex: 1;
        }

        .skeleton-table-row {
            height: 53px;
            border-bottom: 1px solid #e8eaed;
        }

        .skeleton-table-cell {
            padding: 12px;
            vertical-align: middle;
        }

        .skeleton-table-icon {
            width: 24px;
            height: 24px;
            border-radius: 4px;
            display: inline-block;
        }

        .skeleton-table-text {
            height: 16px;
            display: inline-block;
            vertical-align: middle;
        }

        /* Upload Progress Modal */
        #uploadProgressModal {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 400px;
            max-height: 500px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            z-index: 9999;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        #uploadProgressModal.minimized .upload-modal-body {
            display: none;
        }

        #uploadProgressModal.minimized {
            max-height: 60px;
        }

        .upload-modal-header {
            background: #0d6efd;
            color: white;
            padding: 12px 16px;
            font-weight: 500;
            cursor: pointer;
        }

        .upload-modal-body {
            max-height: 400px;
            overflow-y: auto;
            padding: 12px;
        }

        .upload-item {
            padding: 12px;
            border-bottom: 1px solid #e8eaed;
            transition: background-color 0.2s;
        }

        .upload-item:last-child {
            border-bottom: none;
        }

        .upload-item:hover {
            background-color: #f8f9fa;
        }

        .upload-item-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            background: #f1f3f4;
        }

        .upload-item-progress {
            height: 4px;
            background: #e8eaed;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 8px;
        }

        .upload-item-progress-bar {
            height: 100%;
            background: #0d6efd;
            transition: width 0.3s ease;
        }

        .upload-item-progress-bar.success {
            background: #198754;
        }

        .upload-item-progress-bar.error {
            background: #dc3545;
        }

        .upload-item-status {
            font-size: 12px;
            margin-top: 4px;
        }

        .upload-item-status.success {
            color: #198754;
        }

        .upload-item-status.error {
            color: #dc3545;
        }

        .upload-item-status.processing {
            color: #0d6efd;
        }

        /* Info Panel (sidebar Detail + Aktivitas) — didesain sebagai panel yang BERDAMPINGAN
           dengan konten utama (menggeser .terminal-data-page lewat body.info-panel-open),
           bukan modal mengambang di atas backdrop gelap. Backdrop (.info-panel-overlay) hanya
           dipakai di layar sempit (<1200px, sama seperti breakpoint sidebar kiri) yang memang
           tidak cukup ruang untuk menampilkan keduanya berdampingan — di situ panel jadi
           overlay penuh seperti sebelumnya. Di layar lebar, tidak ada backdrop sama sekali. */
        .info-panel-overlay {
            display: none;
            position: fixed;
            top: 60px;
            right: 0;
            bottom: 0;
            left: 0;
            background: rgba(32, 33, 36, 0.35);
            z-index: 1050;
        }

        .info-panel-overlay.show {
            display: block;
        }

        .info-panel {
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 72px;
            right: 12px;
            /* Default (fallback sebelum JS sempat menghitung, & untuk layar <1200px yang
               memang full-overlay): 12px dari bawah viewport. Begitu panel dibuka di layar
               lebar, adjustInfoPanelBottom() menimpa nilai ini lewat inline style memakai
               posisi asli #footer, supaya panel berhenti persis di atas footer — bukan
               menembusnya — tanpa perlu menebak tinggi footer di CSS (bisa berubah-ubah). */
            bottom: 12px;
            width: 360px;
            max-width: calc(100vw - 24px);
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 28px rgba(0, 0, 0, 0.18);
            z-index: 1051;
            transform: translateX(calc(100% + 24px));
            transition: transform 0.25s ease;
            overflow: hidden;
        }

        @media (min-width: 1200px) {

            .info-panel-overlay {
                left: 300px;
            }

            /* Di layar lebar backdrop dimatikan — panel tampil berdampingan, bukan modal. */
            .info-panel-overlay.show {
                display: none;
            }

            body.info-panel-open .terminal-data-page {
                margin-right: 360px;
            }
        }

        .info-panel.show {
            transform: translateX(0);
        }

        .info-panel-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
        }

        .info-panel-title {
            /* Default min-width dari flex item adalah "auto", bukan 0 — tanpa ini,
                   .text-truncate pada nama file tidak pernah benar-benar menyusut dan
                   akan menutupi tombol close saat nama file panjang. */
            min-width: 0;
        }

        .info-panel-header .btn-close {
            flex-shrink: 0;
        }

        .info-panel-icon {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #f1f3f4;
            color: #5f6368;
            font-size: 18px;
        }

        .info-panel-tabs {
            border-bottom: 1px solid #dadce0;
        }

        .info-panel-preview {
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .info-panel-preview .file-icon-preview,
        .info-panel-preview .file-thumbnail-img {
            border-radius: 8px;
        }

        .info-panel-preview-folder {
            width: 100%;
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f3f4;
            border-radius: 8px;
        }

        .info-panel-preview-folder i {
            font-size: 64px;
            color: #5f6368;
        }

        .info-panel-body {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
        }

        .info-panel-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }

        .info-panel-section {
            margin-bottom: 20px;
        }

        .info-panel-section-title {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #5f6368;
            margin-bottom: 8px;
        }

        .info-panel-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 6px 0;
            font-size: 13px;
            border-bottom: 1px solid #f1f3f4;
        }

        .info-panel-label {
            color: #5f6368;
            flex-shrink: 0;
        }

        .info-panel-value {
            color: #202124;
            text-align: right;
            word-break: break-word;
        }

        .info-panel-empty {
            text-align: center;
            padding: 40px 0;
        }

        .info-panel-activity-item {
            display: flex;
            gap: 10px;
            padding: 10px 0;
            border-bottom: 1px solid #f1f3f4;
        }

        .info-panel-activity-icon {
            width: 28px;
            height: 28px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f1f3f4;
            color: #5f6368;
            font-size: 13px;
        }

        .info-panel-activity-text {
            font-size: 13px;
            color: #202124;
        }

        .info-panel-activity-time {
            font-size: 11px;
            color: #5f6368;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let dataTable = null;
        // *All = sumber kebenaran hasil fetch server (tidak pernah difilter). subfolders/documents
        // (dipakai renderSubfoldersGrid/renderFilesGrid/prepareTableData yang sudah ada) sekarang
        // jadi hasil filter+sort dari applyFiltersAndSort() — kedua fungsi render itu sendiri
        // tidak perlu diubah karena tetap membaca nama variabel yang sama.
        let subfoldersAll = [];
        let documentsAll = @json($files);
        let subfolders = [];
        let documents = documentsAll;
        let currentSort = 'name_asc';
        let uploadQueue = [];
        let activeUploads = 0;
        const MAX_CONCURRENT_UPLOADS = 3;

        $(document).ready(function() {
            console.log('Document ready');
            console.log('Files/Documents data:', documents);

            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Load subfolders
            loadSubfolders();

            // Load files
            loadFiles();

            // Filter: Jenis (statis) + Di Modifikasi
            $('.filter-jenis, #filterModifikasi').on('change', applyFiltersAndSort);

            // Filter: Orang (dibangun dinamis oleh populateOrangFilterOptions())
            $(document).on('change', '.filter-orang', applyFiltersAndSort);

            $('#btnResetFilter').on('click', function() {
                $('.filter-jenis, .filter-orang').prop('checked', false);
                $('#filterModifikasi').val('');
                applyFiltersAndSort();
            });

            // Sort
            $('.sort-option').on('click', function(e) {
                e.preventDefault();
                currentSort = $(this).data('sort');
                $('.sort-option').removeClass('active');
                $(this).addClass('active');
                applyFiltersAndSort();
            });

            // Trigger file upload when button clicked (using event delegation for dynamically added buttons)
            $(document).on('click', '#btnTriggerUpload, #btnTriggerUploadEmpty', function() {
                $('#fileUpload').click();
            });

            // Handle file selection - Multiple files
            $('#fileUpload').on('change', function() {
                let files = this.files;
                if (files.length > 0) {
                    // Validate all files
                    const maxSize = 100 * 1024 * 1024; // 100MB in bytes
                    const allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg',
                        'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'
                    ];
                    let hasError = false;

                    for (let i = 0; i < files.length; i++) {
                        // Check file size
                        if (files[i].size > maxSize) {
                            Swal.fire({
                                icon: 'error',
                                title: 'File Terlalu Besar',
                                text: `File "${files[i].name}" melebihi batas 100MB`,
                                confirmButtonColor: '#0d6efd'
                            });
                            hasError = true;
                            break;
                        }

                        // Check file extension
                        const fileName = files[i].name;
                        const fileExtension = fileName.split('.').pop().toLowerCase();
                        if (!allowedExtensions.includes(fileExtension)) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Format File Tidak Didukung',
                                html: `File "${fileName}" memiliki format yang tidak didukung.<br><br>
                                       <small>Format yang didukung:<br>
                                       <strong>Dokumen:</strong> PDF, Word, Excel, PowerPoint<br>
                                       <strong>Gambar:</strong> JPG, PNG, GIF, BMP, SVG, WebP</small>`,
                                confirmButtonColor: '#0d6efd'
                            });
                            hasError = true;
                            break;
                        }
                    }

                    if (!hasError) {
                        // Show upload modal
                        $('#uploadProgressModal').fadeIn();

                        // Add files to upload queue
                        for (let i = 0; i < files.length; i++) {
                            uploadQueue.push(files[i]);
                            addUploadItem(files[i]);
                        }

                        // Update count
                        updateUploadCount();

                        // Start processing queue
                        processUploadQueue();
                    }

                    // Reset file input
                    $(this).val('');
                }
            });

            // Upload modal controls
            $('#minimizeUpload').click(function() {
                $('#uploadProgressModal').toggleClass('minimized');
            });

            $('#closeUpload').click(function() {
                if (activeUploads > 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Upload sedang berjalan',
                        text: 'Masih ada file yang sedang diupload. Tutup tetap?',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Tutup',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#uploadProgressModal').fadeOut();
                            uploadQueue = [];
                        }
                    });
                } else {
                    $('#uploadProgressModal').fadeOut();
                }
            });

            // Handle Rename Folder Form Submit
            $('#formRenameFolder').submit(function(e) {
                e.preventDefault();

                const folderId = $('#rename_folder_id').val();
                const newName = $('#folder_new_name').val().trim();

                if (!newName) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nama Tidak Boleh Kosong',
                        text: 'Silakan masukkan nama folder yang baru',
                        confirmButtonColor: '#0d6efd'
                    });
                    return;
                }

                // Show loading
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mengubah nama folder',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: `{{ url('terminal-data/api/folders') }}/${folderId}`,
                    type: 'POST',
                    data: {
                        _method: 'PUT',
                        _token: '{{ csrf_token() }}',
                        name: newName
                    },
                    success: function(response) {
                        $('#modalRenameFolder').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Nama folder berhasil diubah',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let message = 'Gagal mengubah nama folder';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: message
                        });
                    }
                });
            });

            // Handle Rename File Form Submit
            $('#formRenameFile').submit(function(e) {
                e.preventDefault();

                const fileId = $('#rename_file_id').val();
                const newName = $('#file_new_name').val().trim();

                if (!newName) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nama Tidak Boleh Kosong',
                        text: 'Silakan masukkan nama file yang baru',
                        confirmButtonColor: '#0d6efd'
                    });
                    return;
                }

                // Show loading
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mengubah nama file',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: `{{ url('terminal-data/api/files') }}/${fileId}`,
                    type: 'POST',
                    data: {
                        _method: 'PUT',
                        _token: '{{ csrf_token() }}',
                        name: newName
                    },
                    success: function(response) {
                        $('#modalRenameFile').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Nama file berhasil diubah',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let message = 'Gagal mengubah nama file';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: message
                        });
                    }
                });
            });

            // View mode toggle
            $('input[name="viewMode"]').change(function() {
                let viewMode = $(this).attr('id');
                if (viewMode === 'viewGrid') {
                    $('#gridView').show();
                    $('#tableView').hide();

                    if (dataTable) {
                        dataTable.destroy();
                        dataTable = null;
                    }
                } else {
                    $('#tableView').show();
                    $('#gridView').hide();

                    populateCombinedTable();
                }
            });

            // Reset edit form when modal is closed
            $('#modalEditDokumen').on('hidden.bs.modal', function() {
                $('#formEditDokumen')[0].reset();
                $('#editProgress').hide();
                $('#editProgress .progress-bar').css('width', '0%');
                $('#editProgressText').text('Mempersiapkan update...');
            });

            // Reset folder form when modal is closed
            $('#modalTambahFolder').on('hidden.bs.modal', function() {
                $('#formTambahFolder')[0].reset();
            });

            // Save Folder
            $('#btnSaveFolder').click(function() {
                console.log('Button clicked');
                let form = $('#formTambahFolder')[0];

                // Basic form validation
                if (!form.checkValidity()) {
                    console.log('Form validation failed');
                    form.reportValidity();
                    return;
                }

                let formData = {
                    _token: '{{ csrf_token() }}',
                    parent_id: '{{ $folder->id }}',
                    name: $('#nama_folder').val()
                };

                console.log('Form data:', formData);

                $.ajax({
                    url: '{{ route('terminaldata.foldersData.store') }}',
                    type: 'POST',
                    data: formData,
                    beforeSend: function() {
                        console.log('AJAX starting...');
                        $('#btnSaveFolder').prop('disabled', true).html(
                            '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...'
                        );
                    },
                    success: function(response) {
                        console.log('AJAX success:', response);
                        $('#modalTambahFolder').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message || 'Subfolder berhasil dibuat',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Reload to show new subfolder
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        console.log('AJAX error:', xhr.status, xhr.responseJSON);
                        $('#btnSaveFolder').prop('disabled', false).html(
                            '<i class="bi bi-check-circle me-1"></i>Buat'
                        );

                        let errorMessage = 'Terjadi kesalahan';

                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            errorMessage = '<ul class="text-start">';
                            Object.keys(errors).forEach(key => {
                                errors[key].forEach(error => {
                                    errorMessage += `<li>${error}</li>`;
                                });
                            });
                            errorMessage += '</ul>';
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            html: errorMessage,
                            confirmButtonColor: '#0d6efd'
                        });
                    },
                    complete: function() {
                        $('#btnSaveFolder').prop('disabled', false).html(
                            '<i class="bi bi-check-circle me-1"></i>Buat'
                        );
                    }
                });
            });
        });

        // ============================================
        // UPLOAD QUEUE FUNCTIONS
        // ============================================

        function addUploadItem(file) {
            const uploadId = generateUploadId();
            const fileSize = formatFileSize(file.size);
            const extension = file.name.split('.').pop().toLowerCase();

            let iconClass = 'bi-file-earmark-text-fill text-secondary';
            if (extension === 'pdf') {
                iconClass = 'bi-file-earmark-pdf-fill text-danger';
            } else if (['doc', 'docx'].includes(extension)) {
                iconClass = 'bi-file-earmark-word-fill text-primary';
            } else if (['xls', 'xlsx'].includes(extension)) {
                iconClass = 'bi-file-earmark-excel-fill text-success';
            } else if (['ppt', 'pptx'].includes(extension)) {
                iconClass = 'bi-file-earmark-slides-fill text-danger';
            } else if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'].includes(extension)) {
                iconClass = 'bi-file-earmark-image-fill text-warning';
            } else if (['zip', 'rar', '7z', 'tar', 'gz'].includes(extension)) {
                iconClass = 'bi-file-earmark-zip-fill text-info';
            }

            const html = `
                <div class="upload-item" data-upload-id="${uploadId}">
                    <div class="d-flex align-items-start gap-3">
                        <div class="upload-item-icon">
                            <i class="bi ${iconClass}" style="font-size: 20px;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium text-truncate" style="max-width: 280px;">${file.name}</div>
                            <div class="text-muted small">${fileSize}</div>
                            <div class="upload-item-progress">
                                <div class="upload-item-progress-bar" style="width: 0%"></div>
                            </div>
                            <div class="upload-item-status processing">
                                <i class="bi bi-hourglass-split"></i> Menunggu...
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('#uploadModalBody').append(html);

            // Store upload ID with file
            file.uploadId = uploadId;
        }

        function updateUploadCount() {
            const total = uploadQueue.length + activeUploads;
            $('#uploadCount').text(total);

            if (total === 0) {
                setTimeout(() => {
                    $('#uploadProgressModal').fadeOut();
                    $('#uploadModalBody').empty();
                }, 2000);
            }
        }

        function processUploadQueue() {
            while (activeUploads < MAX_CONCURRENT_UPLOADS && uploadQueue.length > 0) {
                const file = uploadQueue.shift();
                uploadFile(file);
            }
        }

        function uploadFile(file) {
            activeUploads++;
            const uploadId = file.uploadId;

            updateUploadStatus(uploadId, 'processing', 'Mengupload...');

            const formData = new FormData();
            formData.append('file', file);
            formData.append('folder_id', '{{ $folder->id }}');
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: '{{ route('terminaldata.filesData.upload') }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            updateUploadProgress(uploadId, percentComplete);
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    updateUploadStatus(uploadId, 'success', 'Berhasil diupload');
                    updateUploadProgress(uploadId, 100, 'success');

                    // Reload after all uploads complete
                    activeUploads--;
                    updateUploadCount();

                    if (activeUploads === 0 && uploadQueue.length === 0) {
                        // File already processed, reload immediately
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        processUploadQueue();
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Gagal mengupload';
                    updateUploadStatus(uploadId, 'error', message);
                    updateUploadProgress(uploadId, 100, 'error');

                    activeUploads--;
                    updateUploadCount();
                    processUploadQueue();
                }
            });
        }

        function updateUploadProgress(uploadId, percent, status = '') {
            const item = $(`.upload-item[data-upload-id="${uploadId}"]`);
            item.find('.upload-item-progress-bar').css('width', percent + '%');
            if (status) {
                item.find('.upload-item-progress-bar').addClass(status);
            }
        }

        function updateUploadStatus(uploadId, type, message) {
            const item = $(`.upload-item[data-upload-id="${uploadId}"]`);
            const statusEl = item.find('.upload-item-status');

            statusEl.removeClass('processing success error').addClass(type);

            let icon = 'bi-hourglass-split';
            if (type === 'success') icon = 'bi-check-circle-fill';
            else if (type === 'error') icon = 'bi-x-circle-fill';
            else if (type === 'processing') icon = 'bi-arrow-repeat';

            statusEl.html(`<i class="bi ${icon}"></i> ${message}`);
        }

        function generateUploadId() {
            return 'upload_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        }

        /**
         * Format file size from bytes to human readable format
         * Examples:
         * - 500 bytes -> "500 Bytes"
         * - 1500 bytes -> "1.46 KB"
         * - 1500000 bytes -> "1.43 MB"
         * - 1500000000 bytes -> "1.40 GB"
         */
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return (bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i];
        }

        // ============================================
        // OLD UPLOAD FUNCTION (Keep for reference, can be removed)
        // ============================================

        function uploadDocument() {
            let form = $('#formUploadDokumen')[0];
            let formData = new FormData(form);

            // Show progress modal
            $('#modalUploadProgress').modal('show');

            $.ajax({
                url: '{{ route('terminaldata.filesData.upload') }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    let xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            let percentComplete = (evt.loaded / evt.total) * 100;
                            $('#uploadProgressBar').css('width', percentComplete + '%');
                            $('#uploadProgressText').text(
                                `Mengupload ${Math.round(percentComplete)}% selesai`
                            );

                            if (percentComplete >= 100) {
                                $('#uploadProgressText').text('Memproses dokumen...');
                            }
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    $('#modalUploadProgress').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Dokumen berhasil diupload',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Reset form and reload
                        $('#formUploadDokumen')[0].reset();
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    $('#modalUploadProgress').modal('hide');

                    let errorMessage = 'Terjadi kesalahan saat upload dokumen';

                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        if (xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            errorMessage += '<br><ul style="text-align: left;">';
                            Object.keys(errors).forEach(key => {
                                errors[key].forEach(error => {
                                    errorMessage += `<li>${error}</li>`;
                                });
                            });
                            errorMessage += '</ul>';
                        }
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        html: errorMessage
                    });

                    // Reset file input
                    $('#fileUpload').val('');
                }
            });
        }

        // ============================================
        // DATATABLE INITIALIZATION
        $('#btnUpdateDokumen').click(function() {
            let form = $('#formEditDokumen')[0];

            // Basic form validation
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            let dokumenId = $('#edit_dokumen_id').val();
            let formData = new FormData(form);

            $.ajax({
                url: `/dokumen/${dokumenId}`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $('#editProgress').show();
                    $('#editProgressText').text('Memulai update...');
                    $('#btnUpdateDokumen').prop('disabled', true).html(
                        '<span class="spinner-border spinner-border-sm me-2"></span>Updating...'
                    );
                },
                xhr: function() {
                    let xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            let percentComplete = (evt.loaded / evt.total) * 100;
                            $('#editProgress .progress-bar').css('width',
                                percentComplete + '%');
                            $('#editProgressText').text(
                                `Upload ${Math.round(percentComplete)}% selesai`
                            );

                            if (percentComplete >= 100) {
                                $('#editProgressText').text(
                                    'Memproses update dokumen...');
                            }
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    $('#modalEditDokumen').modal('hide');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Dokumen berhasil diupdate',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Reload page to show updated document
                        window.location.reload();
                    });
                },
                error: function(xhr) {
                    $('#editProgress').hide();
                    $('#btnUpdateDokumen').prop('disabled', false).html(
                        '<i class="bi bi-save me-1"></i>Update Dokumen'
                    );

                    let errorMessage = 'Terjadi kesalahan saat update dokumen';

                    if (xhr.responseJSON) {
                        if (xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        if (xhr.responseJSON.errors) {
                            let errors = xhr.responseJSON.errors;
                            errorMessage += '<br><ul style="text-align: left;">';
                            Object.keys(errors).forEach(key => {
                                errors[key].forEach(error => {
                                    errorMessage += `<li>${error}</li>`;
                                });
                            });
                            errorMessage += '</ul>';
                        }
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        html: errorMessage
                    });
                }
            });
        });

        // Set up download button in detail modal
        $('#btnDownloadDokumen').click(function() {
            let id = $(this).attr('data-id');
            if (id) {
                window.location.href = `/dokumen/${id}/download`;
            }
        });

        // Set up edit from detail button
        $('#btnEditFromDetail').click(function() {
            let id = $(this).attr('data-id');
            if (id) {
                $('#modalDetailDokumen').modal('hide');
                editDokumen(id);
            }
        });

        // ============================================
        // COMBINED TABLE POPULATION
        // ============================================

        function prepareTableData() {
            console.log('Preparing table data...', 'Subfolders:', subfolders.length, 'Documents:', documents.length);
            let tbody = '';

            try {
                // Add folders first
                subfolders.forEach(folder => {
                    const updatedDate = folder.updated_at ? formatDateOnly(folder.updated_at) : '-';
                    const creatorName = folder.creator ? folder.creator.nama : (folder.created_by ? 'User #' +
                        folder.created_by : '-');

                    tbody += `
                    <tr style="cursor:pointer;" onclick="navigateToFolder('${folder.id}')">
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-folder-fill text-secondary me-2" style="font-size: 20px;"></i>
                                ${folder.name}
                            </div>
                        </td>
                        <td>${creatorName}</td>
                        <td>${updatedDate}</td>
                        <td>
                            <small class="text-muted">${folder.total_files || 0} file${(folder.total_files || 0) !== 1 ? 's' : ''}</small>
                        </td>
                        <td onclick="event.stopPropagation();">
                            <button class="btn btn-sm btn-link text-secondary p-1" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#" onclick="navigateToFolder('${folder.id}'); return false;">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Buka
                                </a></li>
                                ${folder.permissions?.rename ? `<li><a class="dropdown-item" href="#" onclick="renameFolder('${folder.id}', '${folder.name}'); return false;">
                                                                                        <i class="bi bi-pencil-square me-2"></i>Ganti Nama
                                                                                    </a></li>` : ''}
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="copyFolderLink('${folder.id}'); return false;">
                                    <i class="bi bi-link-45deg me-2"></i>Salin Link
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="event.stopPropagation(); openInfoPanel('folder', '${folder.id}'); return false;">
                                    <i class="bi bi-info-circle me-2"></i>Informasi Folder
                                </a></li>
                                ${folder.permissions?.delete ? `
                                                                                    <li><hr class="dropdown-divider"></li>
                                                                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteFolder('${folder.id}', '${folder.name}'); return false;">
                                                                                        <i class="bi bi-trash me-2"></i>Pindahkan ke Sampah
                                                                                    </a></li>` : ''}
                            </ul>
                        </td>
                    </tr>
                    `;
                });

                // Add documents
                documents.forEach(doc => {
                    let extension, fileName, fileSize, updatedDate, creatorName;

                    // Support both TdFile and Dokumen structure
                    if (doc.storage_path || doc.extension) {
                        // TdFile structure (direct file)
                        extension = doc.extension || 'pdf';
                        fileName = doc.name || doc.original_name;
                        fileSize = doc.size ? formatFileSize(doc.size) : '-'; // Use formatFileSize function
                        updatedDate = doc.updated_at ? new Date(doc.updated_at).toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        }) : '-';
                        creatorName = doc.creator ? doc.creator.nama : '-';
                    } else {
                        // Dokumen structure (multiple versions)
                        const currentFile = doc.files && doc.files.length > 0 ? (doc.files.find(f => f
                                .is_current) ||
                            doc.files[0]) : null;
                        extension = currentFile ? currentFile.extension : 'pdf';
                        fileName = doc.judul;
                        // Convert KB to bytes first, then format
                        fileSize = currentFile ? formatFileSize(Number(currentFile.size_kb) * 1024) : '-';
                        updatedDate = doc.updated_at ? new Date(doc.updated_at).toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        }) : '-';
                        creatorName = doc.uploader ? doc.uploader.nama : '-';
                    }

                    let fileIcon = 'bi-file-earmark-text-fill';
                    if (extension === 'pdf') {
                        fileIcon = 'bi-file-earmark-pdf-fill text-danger';
                    } else if (['doc', 'docx'].includes(extension)) {
                        fileIcon = 'bi-file-earmark-word-fill text-primary';
                    } else if (['xls', 'xlsx'].includes(extension)) {
                        fileIcon = 'bi-file-earmark-excel-fill text-success';
                    } else if (['ppt', 'pptx'].includes(extension)) {
                        fileIcon = 'bi-file-earmark-slides-fill';
                    } else if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'].includes(extension)) {
                        fileIcon = 'bi-file-earmark-image-fill text-warning';
                    } else if (['zip', 'rar', '7z', 'tar', 'gz'].includes(extension)) {
                        fileIcon = 'bi-file-earmark-zip-fill';
                    }

                    tbody += `
                    <tr style="cursor:pointer;" onclick="showFileDetail('${doc.id}')">
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi ${fileIcon} me-2" style="font-size: 20px;"></i>
                                ${fileName}
                            </div>
                        </td>
                        <td>${creatorName}</td>
                        <td>${updatedDate}</td>
                        <td>${fileSize}</td>
                        <td onclick="event.stopPropagation();">
                            <button class="btn btn-sm btn-link text-secondary p-1" type="button"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                ${doc.permissions?.download !== false ? `<li><a class="dropdown-item" href="{{ url('terminal-data/api/files') }}/${doc.id}/download" onclick="event.stopPropagation()">
                                                                                        <i class="bi bi-download me-2"></i>Unduh
                                                                                    </a></li>` : ''}
                                ${doc.permissions?.update ? `<li><a class="dropdown-item" href="#" onclick="event.stopPropagation(); editFile('${doc.id}'); return false;">
                                                                                        <i class="bi bi-pencil-square me-2"></i>Ganti Nama
                                                                                    </a></li>` : ''}
                                ${(doc.permissions?.download !== false || doc.permissions?.update) ? `<li><hr class="dropdown-divider"></li>` : ''}
                                <li><a class="dropdown-item" href="#" onclick="event.stopPropagation(); showFileDetail('${doc.id}'); return false;">
                                    <i class="bi bi-info-circle me-2"></i>Info File
                                </a></li>
                                ${doc.permissions?.delete ? `
                                                                                    <li><hr class="dropdown-divider"></li>
                                                                                    <li><a class="dropdown-item text-danger" href="#" onclick="event.stopPropagation(); deleteFile('${doc.id}', '${fileName}'); return false;">
                                                                                        <i class="bi bi-trash me-2"></i>Pindahkan ke Sampah
                                                                                    </a></li>` : ''}
                            </ul>
                        </td>
                    </tr>
                    `;
                });

                $('#combinedTableBody').html(tbody);
                console.log('Table data prepared successfully. Total rows:', subfolders.length + documents.length);
            } catch (error) {
                console.error('Error preparing table data:', error);
            }
        }

        function populateCombinedTable() {
            console.log('Populating combined table...');

            // Destroy existing DataTable if any
            if (dataTable) {
                console.log('Destroying existing DataTable');
                dataTable.destroy();
                dataTable = null;
            }

            // Generate tbody HTML (call prepareTableData to ensure fresh data)
            prepareTableData();

            // Initialize DataTable after a small delay to ensure DOM is ready
            setTimeout(function() {
                const rowCount = $('#combinedTableBody tr').length;
                console.log('Table body has', rowCount, 'rows');

                if ($('#combinedTable').length && rowCount > 0) {
                    dataTable = new simpleDatatables.DataTable("#combinedTable", {
                        searchable: true,
                        fixedHeight: false,
                        perPage: 10,
                        labels: {
                            placeholder: "Cari folder atau dokumen...",
                            perPage: "Data per halaman",
                            noRows: "Tidak ada data",
                            info: "Menampilkan {start} sampai {end} dari {rows} data",
                        }
                    });
                    console.log('DataTable initialized successfully with', rowCount, 'rows');
                } else {
                    console.warn('Cannot initialize DataTable: no rows found');
                }
            }, 150);
        }

        // ============================================
        // CRUD FUNCTIONS
        // ============================================

        function showDetail(id) {
            $('#modalDetailDokumen').modal('show');
            $('#detailDokumenContent').html(`
                <div class="text-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Memuat detail dokumen...</p></div>
            `);

            $.ajax({
                url: `/dokumen/${id}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    // Prepare file icon based on extension
                    let fileIcon = 'bi-file-earmark-text';
                    let fileIconColor = '#4154f1';
                    if (response.files && response.files.length > 0) {
                        const extension = response.files[0].extension || 'pdf';
                        if (extension === 'pdf') {
                            fileIcon = 'bi-file-earmark-pdf-fill';
                            fileIconColor = '#dc3545';
                        } else if (['doc', 'docx'].includes(extension)) {
                            fileIcon = 'bi-file-earmark-word-fill';
                            fileIconColor = '#0d6efd';
                        } else if (['xls', 'xlsx'].includes(extension)) {
                            fileIcon = 'bi-file-earmark-excel-fill';
                            fileIconColor = '#198754';
                        }
                    }

                    // Determine status badge
                    let statusClass = 'bg-secondary';
                    let statusIcon = 'bi-clock';
                    if (response.status === 'Final') {
                        statusClass = 'bg-success';
                        statusIcon = 'bi-check-circle';
                    } else if (response.status === 'Draft') {
                        statusClass = 'bg-warning text-dark';
                        statusIcon = 'bi-pencil';
                    } else if (response.status === 'Archived') {
                        statusClass = 'bg-secondary';
                        statusIcon = 'bi-archive';
                    }

                    let html = `
                        <div class="p-4">
                            <!-- Document Header Section -->
                            <div class="detail-header mb-4 text-center">
                                <div class="mb-3">
                                    <i class="bi ${fileIcon}" style="font-size: 5rem; color: ${fileIconColor};"></i>
                                </div>
                                <h3 class="fw-bold mb-3">${response.judul}</h3>
                                <div class="d-flex justify-content-center gap-2 mb-2">
                                    <span class="badge ${statusClass} rounded-pill px-3 py-2">
                                        <i class="bi ${statusIcon} me-1"></i>${response.status}
                                    </span>
                                    <span class="badge bg-primary rounded-pill px-3 py-2">
                                        <i class="bi bi-layers me-1"></i>v${response.version}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Document Info Table -->
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-body p-0">
                                    <table class="table table-striped mb-0">
                                        <tr>
                                            <th class="bg-light" width="30%">Nomor Dokumen</th>
                                            <td>${response.nomor || '-'}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Jenis</th>
                                            <td>${response.jenis?.nama || '-'}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Folder</th>
                                            <td>${response.folder?.nama || '-'} <small class=\"text-muted\">${response.folder?.path || ''}</small></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Nomor Surat</th>
                                            <td>${response.nomor_surat || '-'}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Tanggal</th>
                                            <td>${formatDate(response.tanggal_dokumen)}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Deskripsi</th>
                                            <td>${response.deskripsi || '<em class=\"text-muted\">Tidak ada deskripsi</em>'}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Diupload oleh</th>
                                            <td>${response.uploader?.nama || '-'}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light">Statistik</th>
                                            <td>
                                                <div class="d-flex gap-3">
                                                    <div>
                                                        <i class="bi bi-eye me-1"></i>${response.views || 0} Views
                                                    </div>
                                                    <div>
                                                        <i class="bi bi-download me-1"></i>${response.downloads || 0} Downloads
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>`;

                    // Metadata section
                    if (response.metadata && response.metadata.length > 0) {
                        html += `
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="bi bi-tags me-2"></i>Metadata
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th width="40%">Kunci</th>
                                                <th width="60%">Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;

                        response.metadata.forEach(meta => {
                            html += `
                                <tr>
                                    <td class="fw-bold">${meta.key}</td>
                                    <td>${meta.value}</td>
                                </tr>`;
                        });

                        html += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>`;
                    }

                    // File information
                    if (response.files && response.files.length > 0) {
                        let currentFile = response.files.find(f => f.is_current) || response.files[0];
                        const extension = currentFile.extension || 'pdf';

                        let fileIconClass = 'text';
                        if (extension === 'pdf') {
                            fileIconClass = 'pdf-fill text-danger';
                        } else if (['doc', 'docx'].includes(extension)) {
                            fileIconClass = 'word-fill text-primary';
                        } else if (['xls', 'xlsx'].includes(extension)) {
                            fileIconClass = 'excel-fill text-success';
                        }

                        html += `
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">
                                        <i class="bi bi-file-earmark me-2"></i>File Dokumen
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="detail-file-preview">
                                        <div class="me-3">
                                            <i class="bi bi-file-earmark-${fileIconClass}" style="font-size: 3rem;"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1">${currentFile.nama_file}</h6>
                                            <p class="mb-0 text-muted">
                                                <span class="badge bg-info text-dark me-2">${extension.toUpperCase()}</span>
                                                Ukuran: ${formatFileSize(currentFile.size_kb)} | 
                                                Versi: ${currentFile.version} | 
                                                Diupload: ${formatDateTime(currentFile.created_at)}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>`;

                        // Multiple versions
                        if (response.files.length > 1) {
                            html += `
                                <div class="card shadow-sm border-0 mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0" data-bs-toggle="collapse" href="#collapseVersions" role="button" aria-expanded="false" style="cursor: pointer;">
                                            <i class="bi bi-clock-history me-2"></i>Riwayat Versi <span class="badge bg-primary">${response.files.length - 1}</span>
                                            <i class="bi bi-chevron-down float-end"></i>
                                        </h5>
                                    </div>
                                    <div class="collapse" id="collapseVersions">
                                        <div class="card-body p-0">
                                            <table class="table table-sm mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Versi</th>
                                                        <th>Nama File</th>
                                                        <th>Ukuran</th>
                                                        <th>Tanggal Upload</th>
                                                    </tr>
                                                </thead>
                                                <tbody>`;

                            const oldVersions = response.files
                                .filter(f => !f.is_current)
                                .sort((a, b) => b.version - a.version);

                            oldVersions.forEach(file => {
                                html += `
                                    <tr>
                                        <td><span class="badge bg-secondary">v${file.version}</span></td>
                                        <td>${file.nama_file}</td>
                                        <td>${formatFileSize(file.size_kb)}</td>
                                        <td>${formatDateTime(file.created_at)}</td>
                                    </tr>`;
                            });

                            html += `
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>`;
                        }
                    }

                    // Footer
                    html += `</div>`;

                    $('#detailDokumenContent').html(html);
                    $('#btnDownloadDokumen').attr('data-id', id);
                    $('#btnEditFromDetail').attr('data-id', id);
                },
                error: function() {
                    $('#detailDokumenContent').html(`
                        <div class="text-center p-5">
                            <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 3rem;"></i>
                            <h4 class="mt-3">Gagal Memuat Detail</h4>
                            <p class="text-muted mb-4">Terjadi kesalahan saat memuat detail dokumen.</p>
                            <button class="btn btn-primary" onclick="showDetail(${id})">
                                <i class="bi bi-arrow-clockwise me-2"></i>Coba Lagi
                            </button>
                        </div>
                    `);
                }
            });
        }

        function editDokumen(id) {
            console.log('Edit dokumen ID:', id);

            // Show loading state in the modal
            $('#current_file_info').html(`
                <div class="d-flex align-items-center">
                    <div class="spinner-border spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div>Memuat informasi dokumen...</div>
                </div>
            `);

            $('#modalEditDokumen').modal('show');

            $.ajax({
                url: `/dokumen/${id}/edit`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Edit data loaded:', response);

                    $('#edit_dokumen_id').val(response.id);

                    // Fill form fields
                    $('#edit_judul').val(response.judul);
                    $('#edit_nomor_surat').val(response.nomor_surat || '');

                    let tanggal = response.tanggal_dokumen ? response.tanggal_dokumen.substring(0, 10) : '';
                    $('#edit_tanggal_dokumen').val(tanggal);

                    $('#edit_deskripsi').val(response.deskripsi || '');

                    // Set status radio buttons
                    $(`input[name="status"][value="${response.status}"]`).prop('checked', true);

                    // Show current file info
                    if (response.files && response.files.length > 0) {
                        let currentFile = response.files.find(f => f.is_current) || response.files[0];
                        const extension = currentFile.extension || '';

                        let iconClass = 'text';
                        let iconColor = '#6c757d';
                        if (extension === 'pdf') {
                            iconClass = 'pdf-fill';
                            iconColor = '#dc3545';
                        } else if (['doc', 'docx'].includes(extension)) {
                            iconClass = 'word-fill';
                            iconColor = '#0d6efd';
                        } else if (['xls', 'xlsx'].includes(extension)) {
                            iconClass = 'excel-fill';
                            iconColor = '#198754';
                        }

                        $('#current_file_info').html(`
                            <div class="d-flex align-items-center">
                                <i class="bi bi-file-earmark-${iconClass} me-3" style="font-size: 2rem; color: ${iconColor};"></i>
                                <div>
                                    <strong>${currentFile.nama_file}</strong><br>
                                    <small>
                                        <span class="badge bg-info text-dark me-1">${extension.toUpperCase()}</span>
                                        Ukuran: ${formatFileSize(currentFile.size_kb)} | 
                                        Versi: ${currentFile.version} | 
                                        Diupload: ${formatDateTime(currentFile.created_at)}
                                    </small>
                                </div>
                            </div>
                        `);
                    } else {
                        $('#current_file_info').html('<small class="text-muted">Tidak ada file</small>');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading edit data:', xhr);

                    $('#modalEditDokumen').modal('hide');

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal memuat data dokumen untuk edit'
                    });
                }
            });
        }

        function deleteDokumen(id) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: "Dokumen akan dihapus permanen beserta semua file dan metadata-nya. Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash me-1"></i>Ya, Hapus!',
                cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Batal',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/dokumen/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: 'Dokumen berhasil dihapus.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                // Reload page to refresh document list
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Gagal menghapus dokumen'
                            });
                        }
                    });
                }
            });
        }

        // ============================================
        // SUBFOLDER FUNCTIONS
        // ============================================

        function loadSubfolders() {
            const folderId = '{{ $folder->id }}';

            // Show skeleton and section
            $('#subfoldersSection').show();
            $('#subfoldersSkeleton').show();
            $('#subfoldersGrid').hide();

            $.ajax({
                url: `{{ route('terminaldata.foldersData.children', ':folderId') }}`.replace(':folderId',
                    folderId),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('Subfolders loaded:', response);

                    // Hide skeleton
                    $('#subfoldersSkeleton').hide();

                    subfoldersAll = response && Array.isArray(response) ? response : [];
                    populateOrangFilterOptions();
                    applyFiltersAndSort();
                },
                error: function(xhr, status, error) {
                    console.error('Error loading subfolders:', error, xhr);

                    // Hide skeleton on error
                    $('#subfoldersSkeleton').hide();
                    subfoldersAll = [];
                    $('#subfoldersSection').hide();
                }
            });
        }

        function renderSubfoldersGrid(folders) {
            if (!folders || folders.length === 0) {
                $('#subfoldersSection').hide();
                $('#subfoldersGrid').hide();
                return;
            }

            $('#subfoldersSection').show();
            $('#subfoldersGrid').show();
            let html = '';

            folders.forEach(folder => {
                const subfolderCount = 0; // Could be calculated if needed

                html += `
                <div class="td-grid-item">
                    <div class="gdrive-folder-card" onclick="navigateToFolder('${folder.id}')">
                        <div class="gdrive-folder-content">
                            <div class="gdrive-folder-icon">
                                <i class="bi bi-folder-fill"></i>
                            </div>
                            <div class="gdrive-folder-info">
                                <div class="gdrive-folder-title" title="${folder.name}">
                                    ${folder.name}
                                </div>
                                <div class="gdrive-folder-meta">
                                    <small class="text-muted">${folder.total_files || 0} file${(folder.total_files || 0) !== 1 ? 's' : ''}</small>
                                </div>
                            </div>
                            <div class="gdrive-folder-menu" onclick="event.stopPropagation();">
                                <button class="btn btn-sm btn-link p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" onclick="navigateToFolder('${folder.id}'); return false;">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>Buka
                                    </a></li>
                                    ${folder.permissions?.rename ? `
                                                                                        <li><a class="dropdown-item" href="#" onclick="renameFolder('${folder.id}', '${folder.name}'); return false;">
                                                                                            <i class="bi bi-pencil-square me-2"></i>Ganti Nama
                                                                                        </a></li>` : ''}
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" onclick="copyFolderLink('${folder.id}'); return false;">
                                        <i class="bi bi-link-45deg me-2"></i>Salin Link
                                    </a></li>
                                    <li><a class="dropdown-item" href="#" onclick="event.stopPropagation(); openInfoPanel('folder', '${folder.id}'); return false;">
                                        <i class="bi bi-info-circle me-2"></i>Informasi Folder
                                    </a></li>
                                    ${folder.permissions?.delete ? `
                                                                                        <li><hr class="dropdown-divider"></li>
                                                                                        <li><a class="dropdown-item text-danger" href="#" onclick="event.stopPropagation(); deleteFolder('${folder.id}', '${folder.name}'); return false;">
                                                                                            <i class="bi bi-trash me-2"></i>Pindahkan ke Sampah
                                                                                        </a></li>` : ''}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                `;
            });

            $('#subfoldersGrid').html(html);
        }

        // ============================================
        // FILES FUNCTIONS
        // ============================================

        function loadFiles() {
            // Show skeleton
            $('#filesSkeleton').show();
            $('#dokumenGrid').hide();
            $('#filesEmptyState').hide();

            // Use documents data that's already loaded from server
            setTimeout(() => {
                $('#filesSkeleton').hide();
                populateOrangFilterOptions();
                applyFiltersAndSort();
            }, 300);
        }

        // ============================================
        // FILTER & SORT (Jenis / Orang / Di Modifikasi + Urutkan)
        // ============================================

        function getJenisGroup(extension) {
            const ext = (extension || '').toLowerCase();
            if (ext === 'pdf') return 'pdf';
            if (['doc', 'docx'].includes(ext)) return 'word';
            if (['xls', 'xlsx'].includes(ext)) return 'excel';
            if (['ppt', 'pptx'].includes(ext)) return 'powerpoint';
            if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'].includes(ext)) return 'image';
            return 'other';
        }

        function modifiedWithinRange(dateString, range) {
            if (!range || !dateString) return true;
            const date = new Date(dateString);
            const now = new Date();
            if (range === 'today') return date.toDateString() === now.toDateString();
            if (range === '7days') return (now - date) <= 7 * 24 * 60 * 60 * 1000;
            if (range === '30days') return (now - date) <= 30 * 24 * 60 * 60 * 1000;
            if (range === 'year') return date.getFullYear() === now.getFullYear();
            return true;
        }

        function sortComparator(a, b) {
            const sizeA = a.size ?? a.total_size ?? 0;
            const sizeB = b.size ?? b.total_size ?? 0;
            switch (currentSort) {
                case 'name_desc':
                    return (b.name || '').localeCompare(a.name || '');
                case 'updated_desc':
                    return new Date(b.updated_at || 0) - new Date(a.updated_at || 0);
                case 'updated_asc':
                    return new Date(a.updated_at || 0) - new Date(b.updated_at || 0);
                case 'size_desc':
                    return sizeB - sizeA;
                case 'size_asc':
                    return sizeA - sizeB;
                case 'name_asc':
                default:
                    return (a.name || '').localeCompare(b.name || '');
            }
        }

        // Daftar "Orang" dibangun dari data yang sudah ada di client (creator file & folder
        // yang sedang tampil), bukan dari daftar user aplikasi — supaya opsinya relevan dengan
        // isi folder ini saja.
        function populateOrangFilterOptions() {
            const selectedBefore = $('.filter-orang:checked').map(function() {
                return $(this).val();
            }).get();

            const names = [...new Set(
                documentsAll.concat(subfoldersAll)
                .map(item => item.creator?.nama)
                .filter(Boolean)
            )].sort();

            if (names.length === 0) {
                $('#filterOrangList').html('<span class="text-muted small">Tidak ada data</span>');
                return;
            }

            let html = '';
            names.forEach((nama, index) => {
                const checked = selectedBefore.includes(nama) ? 'checked' : '';
                html += `
                    <div class="form-check">
                        <input class="form-check-input filter-orang" type="checkbox" value="${nama}"
                            id="filterOrang${index}" ${checked}>
                        <label class="form-check-label small" for="filterOrang${index}">${nama}</label>
                    </div>`;
            });
            $('#filterOrangList').html(html);
        }

        function applyFiltersAndSort() {
            const selectedJenis = $('.filter-jenis:checked').map(function() {
                return $(this).val();
            }).get();
            const selectedOrang = $('.filter-orang:checked').map(function() {
                return $(this).val();
            }).get();
            const modifikasi = $('#filterModifikasi').val();

            documents = documentsAll.filter(doc => {
                if (selectedJenis.length && !selectedJenis.includes(getJenisGroup(doc.extension))) return false;
                if (selectedOrang.length && !selectedOrang.includes(doc.creator?.nama)) return false;
                if (!modifiedWithinRange(doc.updated_at, modifikasi)) return false;
                return true;
            }).sort(sortComparator);

            // Folder tidak punya "jenis file", jadi filter Jenis tidak berlaku untuknya.
            subfolders = subfoldersAll.filter(folder => {
                if (selectedOrang.length && !selectedOrang.includes(folder.creator?.nama)) return false;
                if (!modifiedWithinRange(folder.updated_at, modifikasi)) return false;
                return true;
            }).sort(sortComparator);

            renderSubfoldersGrid(subfolders);
            $('#subfolderCount').text(subfolders.length);

            renderFilesGrid(documents);
            $('#documentCount').text(documents.length);

            if ($('#tableView').is(':visible')) {
                populateCombinedTable();
            }
        }

        function renderFilesGrid(files) {
            if (!files || files.length === 0) {
                $('#dokumenGrid').hide();
                $('#filesEmptyState').show();
                return;
            }

            $('#dokumenGrid').show();
            $('#filesEmptyState').hide();

            let html = '';

            files.forEach(file => {
                const extension = file.extension || 'pdf';
                const fileName = file.name || file.original_name || 'Unknown';
                const fileSize = formatFileSize(file.size || 0);
                const isImage = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(extension.toLowerCase());

                // Determine file icon and type
                let fileIcon = 'bi-file-earmark-text-fill';
                let dataType = extension.toLowerCase();

                if (extension === 'pdf') {
                    fileIcon = 'bi-file-earmark-pdf-fill';
                } else if (['doc', 'docx'].includes(extension)) {
                    fileIcon = 'bi-file-earmark-word-fill';
                } else if (['xls', 'xlsx'].includes(extension)) {
                    fileIcon = 'bi-file-earmark-excel-fill';
                } else if (['ppt', 'pptx'].includes(extension)) {
                    fileIcon = 'bi-file-earmark-slides-fill';
                } else if (isImage) {
                    fileIcon = 'bi-file-earmark-image-fill';
                } else if (['zip', 'rar', '7z', 'tar', 'gz'].includes(extension)) {
                    fileIcon = 'bi-file-earmark-zip-fill';
                }

                const serveUrl = `{{ url('terminal-data/api/files') }}/${file.id}/serve`;
                const downloadUrl = `{{ url('terminal-data/api/files') }}/${file.id}/download`;
                const isPdf = extension.toLowerCase() === 'pdf';
                const isOfficeDoc = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'].includes(extension.toLowerCase());

                // Determine click action based on file type
                let onClickAction = `showFileDetail('${file.id}')`;
                if (isPdf) {
                    onClickAction = `showPdfPreview('${file.id}', '${fileName.replace(/'/g, "\\'")}')`;
                } else if (isImage) {
                    onClickAction = `showImagePreview('${file.id}', '${fileName.replace(/'/g, "\\'")}')`;
                } else if (isOfficeDoc) {
                    onClickAction =
                        `showOfficePreview('${file.id}', '${fileName.replace(/'/g, "\\'")}', '${extension}')`;
                }

                html += `
                <div class="td-grid-item">
                    <div class="file-card" ondblclick="${onClickAction}">
                        <div class="file-card-header">
                            <div class="file-card-title" title="${fileName}">
                                ${fileName}
                            </div>
                            <div class="file-card-menu" onclick="event.stopPropagation();">
                                <button class="btn btn-sm btn-link p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    ${file.permissions?.download !== false ? `<li><a class="dropdown-item" href="${downloadUrl}" onclick="event.stopPropagation()">
                                                                                            <i class="bi bi-download me-2"></i>Unduh
                                                                                        </a></li>` : ''}
                                    ${file.permissions?.update ? `<li><a class="dropdown-item" href="#" onclick="event.stopPropagation(); editFile('${file.id}', '${fileName.replace(/'/g, "\\'")}'); return false;">
                                                                                            <i class="bi bi-pencil-square me-2"></i>Ganti Nama
                                                                                        </a></li>` : ''}
                                    ${(file.permissions?.download !== false || file.permissions?.update) ? `<li><hr class="dropdown-divider"></li>` : ''}
                                    <li><a class="dropdown-item" href="#" onclick="event.stopPropagation(); showFileDetail('${file.id}'); return false;">
                                        <i class="bi bi-info-circle me-2"></i>Info File
                                    </a></li>
                                    ${file.permissions?.delete ? `
                                                                                        <li><hr class="dropdown-divider"></li>
                                                                                        <li><a class="dropdown-item text-danger" href="#" onclick="event.stopPropagation(); deleteFile('${file.id}', '${fileName.replace(/'/g, "\\'")}'); return false;">
                                                                                            <i class="bi bi-trash me-2"></i>Pindahkan ke Sampah
                                                                                        </a></li>` : ''}
                                </ul>
                            </div>
                        </div>
                        <div class="file-card-preview">
                            ${isImage ? `
                                                                                        <img src="${serveUrl}" alt="${fileName}" class="file-thumbnail-img" 
                                                                                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                                                        <div class="file-icon-preview" data-type="image" style="display: none;">
                                                                                            <i class="bi bi-image"></i>
                                                                                            <div class="file-extension">${extension.toUpperCase()}</div>
                                                                                        </div>
                                                                                    ` : `
                                                                                        <div class="file-icon-preview" data-type="${dataType}">
                                                                                            <i class="bi ${fileIcon}"></i>
                                                                                            <div class="file-extension">${extension.toUpperCase()}</div>
                                                                                        </div>
                                                                                    `}
                        </div>
                    </div>
                </div>
                `;
            });

            $('#dokumenGrid').html(html);
        }

        function navigateToFolder(folderId) {
            window.location.href = `{{ route('terminaldata.folder.detail', ':folderId') }}`.replace(':folderId', folderId);
        }

        function copyFolderLink(id) {
            const link = `{{ route('terminaldata.folder.detail', ':id') }}`.replace(':id', id);

            if (navigator.clipboard) {
                navigator.clipboard.writeText(link).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Link folder berhasil disalin',
                        timer: 1500,
                        showConfirmButton: false
                    });
                });
            } else {
                const textarea = document.createElement('textarea');
                textarea.value = link;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Link folder berhasil disalin',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        }

        function formatDateOnly(dateString) {
            if (!dateString) return '-';
            let date = new Date(dateString);
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            const day = date.getDate();
            const month = months[date.getMonth()];
            const year = date.getFullYear();
            return `${day} ${month} ${year}`;
        }

        // ============================================
        // UTILITY FUNCTIONS
        // ============================================

        function formatDate(dateString) {
            if (!dateString) return '-';
            let date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });
        }

        function formatDateTime(dateString) {
            if (!dateString) return '-';
            let date = new Date(dateString);
            return date.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // ============================================
        // FOLDER DELETE FUNCTION
        // ============================================

        function deleteFolder(folderId, folderName) {
            Swal.fire({
                title: 'Pindahkan Folder ke Sampah?',
                html: `Folder <strong>"${folderName}"</strong> akan dipindahkan ke sampah.<br><small class="text-muted">Catatan: Folder harus kosong (tidak ada subfolder dan dokumen)</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash me-1"></i>Pindahkan ke Sampah',
                cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Batal',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Memindahkan folder ke sampah',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Make AJAX request to delete folder
                    $.ajax({
                        url: `{{ route('terminaldata.foldersData.destroy', ':folderId') }}`.replace(
                            ':folderId', folderId),
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message ||
                                    'Folder berhasil dipindahkan ke sampah',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                // Reload subfolders or redirect to parent
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            let message = 'Gagal memindahkan folder ke sampah';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: message
                            });
                        }
                    });
                }
            });
        }

        // Initialize detail elements for already rendered items
        window.showDetail = showDetail;
        window.editDokumen = editDokumen;
        window.deleteDokumen = deleteDokumen;
        window.deleteFolder = deleteFolder;
        window.showFileDetail = showFileDetail;
        window.editFile = editFile;
        window.deleteFile = deleteFile;

        // ============================================
        // FILE OPERATIONS (for TdFile)
        // ============================================

        function showFileDetail(id) {
            openInfoPanel('file', id);
        }

        // ============================================
        // INFO PANEL (sidebar Detail + Aktivitas)
        // ============================================

        const infoPanelBaseUrl = {
            file: "{{ url('terminal-data/api/files') }}",
            folder: "{{ url('terminal-data/api/folders') }}",
        };

        const infoPanelActionLabel = {
            created: 'membuat folder ini',
            uploaded: 'mengunggah file',
            renamed: 'mengganti nama',
            moved: 'memindahkan',
            trashed: 'memindahkan ke sampah',
            restored: 'memulihkan dari sampah',
            force_deleted: 'menghapus permanen',
            downloaded: 'mengunduh',
            starred: 'menandai favorit',
            unstarred: 'membatalkan tanda favorit',
        };

        const infoPanelActionIcon = {
            created: 'bi-folder-plus',
            uploaded: 'bi-cloud-upload',
            renamed: 'bi-pencil-square',
            moved: 'bi-arrows-move',
            trashed: 'bi-trash',
            restored: 'bi-arrow-counterclockwise',
            force_deleted: 'bi-trash-fill',
            downloaded: 'bi-download',
            starred: 'bi-star-fill',
            unstarred: 'bi-star',
        };

        function infoPanelFileIcon(extension) {
            const ext = (extension || '').toLowerCase();
            if (ext === 'pdf') return 'bi-file-earmark-pdf-fill';
            if (['doc', 'docx'].includes(ext)) return 'bi-file-earmark-word-fill';
            if (['xls', 'xlsx'].includes(ext)) return 'bi-file-earmark-excel-fill';
            if (['ppt', 'pptx'].includes(ext)) return 'bi-file-earmark-slides-fill';
            if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'].includes(ext)) return 'bi-file-earmark-image-fill';
            return 'bi-file-earmark-text-fill';
        }

        // Panel pakai position:fixed (anchor ke viewport), sedangkan #footer bagian dari alur
        // dokumen biasa — CSS saja tidak bisa tahu tinggi footer (bisa berubah tergantung
        // konten/breakpoint) untuk menghentikan panel persis di atasnya. Dihitung dari posisi
        // asli #footer setiap panel dibuka & saat resize/scroll, bukan ditebak di CSS.
        function adjustInfoPanelBottom() {
            const footer = document.getElementById('footer');
            const panel = document.getElementById('infoPanel');
            if (!footer || !panel) return;

            const gap = 12;
            const footerTop = footer.getBoundingClientRect().top;
            const bottomOffset = Math.max(gap, window.innerHeight - footerTop + gap);
            panel.style.bottom = bottomOffset + 'px';
        }

        $(window).on('resize scroll', function() {
            if ($('#infoPanel').hasClass('show')) {
                adjustInfoPanelBottom();
            }
        });

        function openInfoPanel(type, id) {
            // Tooltip Bootstrap ("Informasi folder" dkk) nge-trigger lewat focus tombolnya dan
            // baru hilang saat blur — kalau tidak disembunyikan manual di sini, dia menggantung
            // di atas panel yang baru terbuka (z-index tooltip > z-index panel).
            $('[data-bs-toggle="tooltip"]').tooltip('hide');

            $('body').addClass('info-panel-open');
            $('#infoPanelOverlay').addClass('show');
            $('#infoPanel').addClass('show');
            adjustInfoPanelBottom();

            // Reset ke tab Detail setiap kali dibuka
            new bootstrap.Tab(document.querySelector('#infoPanel .nav-link[data-bs-target="#infoPanelTabDetail"]')).show();

            $('#infoPanelDetailContent, #infoPanelAktivitasList, #infoPanelAktivitasEmpty').hide();
            $('#infoPanelDetailLoading, #infoPanelAktivitasLoading').show();
            $('#infoPanelName').text('Memuat...');

            fetch(`${infoPanelBaseUrl[type]}/${id}/detail`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(res => {
                    if (!res.success) {
                        throw new Error(res.message || 'Gagal memuat detail');
                    }
                    renderInfoPanelDetail(type, res.data, res.breadcrumb || []);
                    renderInfoPanelActivities(res.activities || []);
                })
                .catch(() => {
                    closeInfoPanel();
                    Swal.fire('Gagal', 'Tidak dapat memuat informasi', 'error');
                });
        }

        function closeInfoPanel() {
            $('body').removeClass('info-panel-open');
            $('#infoPanelOverlay').removeClass('show');
            $('#infoPanel').removeClass('show');
        }

        const infoPanelImageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'];

        function renderInfoPanelDetail(type, data, breadcrumb) {
            $('#infoPanelName').text(type === 'file' ? (data.original_name || data.name) : data.name);

            const $icon = $('#infoPanelIcon');
            const $preview = $('#infoPanelPreview');

            if (type === 'file') {
                const ext = (data.extension || '').toLowerCase();
                const iconClass = infoPanelFileIcon(ext);
                $icon.attr('class', 'info-panel-icon').html(`<i class="bi ${iconClass}"></i>`);

                if (infoPanelImageExtensions.includes(ext)) {
                    $preview.html(
                        `<img class="file-thumbnail-img" src="${infoPanelBaseUrl.file}/${data.id}/serve" alt="${data.original_name || data.name}">`
                        );
                } else {
                    $preview.html(`
                        <div class="file-icon-preview" data-type="${ext}">
                            <i class="bi ${iconClass}"></i>
                            <span class="file-extension">${ext || 'file'}</span>
                        </div>`);
                }

                $('#infoPanelJenis').text((data.extension || '-').toUpperCase());
                $('#infoPanelUkuranRow').show();
                $('#infoPanelUkuran').text(data.size_human || '-');
                const lokasi = breadcrumb.length ?
                    breadcrumb.map(b => b.name).join(' / ') + (data.folder ? ' / ' + data.folder.name : '') :
                    (data.folder ? data.folder.name : '-');
                $('#infoPanelLokasi').text(lokasi);
            } else {
                $icon.attr('class', 'info-panel-icon').html('<i class="bi bi-folder-fill"></i>');
                $preview.html('<div class="info-panel-preview-folder"><i class="bi bi-folder-fill"></i></div>');
                $('#infoPanelJenis').text('Folder');
                $('#infoPanelUkuranRow').hide();
                const lokasi = breadcrumb.length ? breadcrumb.map(b => b.name).join(' / ') : 'Eviden Kinerja';
                $('#infoPanelLokasi').text(lokasi);
            }

            $('#infoPanelDibuatOleh').text(data.creator ? data.creator.nama : '-');
            $('#infoPanelDibuatPada').text(formatDateTime(data.created_at));
            $('#infoPanelDiubahPada').text(formatDateTime(data.updated_at));

            $('#infoPanelBidang').text(data.bidang ? data.bidang.nama : '-');
            if (data.sub_bidang) {
                $('#infoPanelSubBidangRow').show();
                $('#infoPanelSubBidang').text(data.sub_bidang.nama);
            } else {
                $('#infoPanelSubBidangRow').hide();
            }
            $('#infoPanelVisibilitas').text(data.is_public ? 'Publik' : 'Privat');
            $('#infoPanelPemilik').text(data.creator ? data.creator.nama : '-');

            $('#infoPanelDetailLoading').hide();
            $('#infoPanelDetailContent').show();
        }

        function renderInfoPanelActivities(activities) {
            $('#infoPanelAktivitasLoading').hide();

            if (!activities.length) {
                $('#infoPanelAktivitasEmpty').show();
                return;
            }

            let html = '';
            activities.forEach(activity => {
                const label = infoPanelActionLabel[activity.action] || activity.action;
                const icon = infoPanelActionIcon[activity.action] || 'bi-dot';
                const actor = activity.user_nama || 'Sistem';

                html += `
                <div class="info-panel-activity-item">
                    <div class="info-panel-activity-icon"><i class="bi ${icon}"></i></div>
                    <div class="flex-grow-1">
                        <div class="info-panel-activity-text"><strong>${actor}</strong> ${label}</div>
                        <div class="info-panel-activity-time">${formatDateTime(activity.created_at)}</div>
                    </div>
                </div>`;
            });

            $('#infoPanelAktivitasList').html(html).show();
        }

        function editFile(id, fileName = '') {
            // Set file ID
            $('#rename_file_id').val(id);

            // Get name without extension
            let currentName = fileName;

            // Try to get from documents array if available
            if (typeof documents !== 'undefined' && documents && documents.length > 0) {
                const file = documents.find(doc => doc.id == id);
                if (file) {
                    currentName = file.name || file.original_name || fileName;
                }
            }

            // Remove extension from filename if present
            if (currentName) {
                const lastDotIndex = currentName.lastIndexOf('.');
                if (lastDotIndex > 0) {
                    currentName = currentName.substring(0, lastDotIndex);
                }
            }

            $('#file_new_name').val(currentName);

            // Show modal
            $('#modalRenameFile').modal('show');

            // Focus on input after modal is shown
            setTimeout(() => {
                $('#file_new_name').focus().select();
            }, 500);
        }

        function renameFolder(folderId, folderName) {
            // Set folder ID and current name
            $('#rename_folder_id').val(folderId);
            $('#folder_new_name').val(folderName);

            // Show modal
            $('#modalRenameFolder').modal('show');

            // Focus on input
            setTimeout(() => {
                $('#folder_new_name').focus().select();
            }, 500);
        }

        function deleteFile(id, fileName = '') {
            Swal.fire({
                title: 'Pindahkan ke Sampah?',
                html: `File <strong>${fileName || 'ini'}</strong> akan dipindahkan ke sampah dan dapat dipulihkan kembali.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash me-1"></i>Pindahkan ke Sampah',
                cancelButtonText: '<i class="bi bi-x-circle me-1"></i>Batal',
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Memindahkan file ke sampah',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: `{{ url('terminal-data/api/files') }}/${id}`,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'File berhasil dipindahkan ke sampah.',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message ||
                                    'Gagal memindahkan file ke sampah'
                            });
                        }
                    });
                }
            });
        }

        /**
         * Show PDF preview in modal
         * @param {string} id - File ID
         * @param {string} fileName - File name
         */
        function showPdfPreview(id, fileName) {
            // Set file name in modal title
            $('#pdfFileName').text(fileName);

            // Show loading state
            $('#pdfPreviewLoading').show();
            $('#pdfPreviewFrame').hide();

            // Show modal
            $('#modalPdfPreview').modal('show');

            // Get serve URL
            const serveUrl = `{{ url('terminal-data/api/files') }}/${id}/serve`;
            const downloadUrl = `{{ url('terminal-data/api/files') }}/${id}/download`;

            // Set iframe source
            $('#pdfPreviewFrame').attr('src', serveUrl);

            // Set download button
            $('#btnDownloadPdf').attr('href', downloadUrl);
            $('#btnDownloadPdf').attr('download', fileName);

            // Hide loading and show iframe after a short delay
            setTimeout(() => {
                $('#pdfPreviewLoading').hide();
                $('#pdfPreviewFrame').show();
            }, 800);
        }

        // Reset PDF preview when modal is closed
        $('#modalPdfPreview').on('hidden.bs.modal', function() {
            $('#pdfPreviewFrame').attr('src', '');
            $('#pdfPreviewLoading').show();
            $('#pdfPreviewFrame').hide();
        });

        // Make showPdfPreview available globally
        window.showPdfPreview = showPdfPreview;

        /**
         * Show Image preview in modal
         * @param {string} id - File ID
         * @param {string} fileName - File name
         */
        function showImagePreview(id, fileName) {
            // Set file name in modal title
            $('#imageFileName').text(fileName);

            // Show loading state
            $('#imagePreviewLoading').show();
            $('#imagePreviewContainer').hide();

            // Show modal
            $('#modalImagePreview').modal('show');

            // Get serve URL
            const serveUrl = `{{ url('terminal-data/api/files') }}/${id}/serve`;
            const downloadUrl = `{{ url('terminal-data/api/files') }}/${id}/download`;

            // Set image source
            const imgElement = $('#imagePreviewImg');

            // Handle image load
            imgElement.off('load').on('load', function() {
                $('#imagePreviewLoading').hide();
                $('#imagePreviewContainer').show();
            });

            // Handle image error
            imgElement.off('error').on('error', function() {
                $('#imagePreviewLoading').hide();
                $('#imagePreviewContainer').html(`
                    <div class="text-center py-5">
                        <i class="bi bi-exclamation-triangle text-warning" style="font-size: 48px;"></i>
                        <p class="mt-3 text-muted">Gagal memuat gambar</p>
                    </div>
                `).show();
            });

            imgElement.attr('src', serveUrl);

            // Set download button
            $('#btnDownloadImage').attr('href', downloadUrl);
            $('#btnDownloadImage').attr('download', fileName);
        }

        // Reset Image preview when modal is closed
        $('#modalImagePreview').on('hidden.bs.modal', function() {
            $('#imagePreviewImg').attr('src', '');
            $('#imagePreviewLoading').show();
            $('#imagePreviewContainer').hide();
        });

        // Make showImagePreview available globally
        window.showImagePreview = showImagePreview;

        /**
         * Download Office Document directly (Word, Excel, PowerPoint)
         * Since preview requires publicly accessible URL, we'll download the file instead
         * @param {string} id - File ID
         * @param {string} fileName - File name
         * @param {string} extension - File extension
         */
        function showOfficePreview(id, fileName, extension) {
            // Get download URL
            const downloadUrl = `{{ url('terminal-data/api/files') }}/${id}/download`;

            // Show notification
            let fileType = 'Dokumen Office';
            if (['doc', 'docx'].includes(extension.toLowerCase())) {
                fileType = 'Dokumen Word';
            } else if (['xls', 'xlsx'].includes(extension.toLowerCase())) {
                fileType = 'Dokumen Excel';
            } else if (['ppt', 'pptx'].includes(extension.toLowerCase())) {
                fileType = 'Dokumen PowerPoint';
            }

            // Show toast notification
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: `Mengunduh ${fileType}`,
                text: `File "${fileName}" akan segera diunduh`,
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });

            // Trigger download
            window.location.href = downloadUrl;
        }

        // Make showOfficePreview available globally
        window.showOfficePreview = showOfficePreview;
    </script>
@endpush
