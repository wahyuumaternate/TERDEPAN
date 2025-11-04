@extends('terminaldata::components.layouts.master')

@php
    // Helper function to format file size
    function formatBytes($bytes, $precision = 2)
    {
        if ($bytes == 0) {
            return '0 Bytes';
        }

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), $precision) . ' ' . $sizes[$i];
    }
@endphp

@section('main')
    <div class="pagetitle">
        <h1><i class="bi bi-trash me-2"></i>Sampah</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('terminaldata.index') }}">Terminal Data</a></li>
                <li class="breadcrumb-item active">Sampah</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <!-- Header with actions -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-folder-x me-2 text-danger"></i>Item yang Dihapus
                                </h5>
                                <p class="text-muted small mb-0">
                                    Kelola folder dan file yang telah dihapus
                                </p>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-danger" id="btnEmptyTrash">
                                    <i class="bi bi-trash3 me-2"></i>Kosongkan Sampah
                                </button>
                            </div>
                        </div>

                        <!-- Info Alert -->
                        @if ($trashedFolders->count() + $trashedFiles->count() > 0)
                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="bi bi-info-circle me-2"></i>
                                <div>
                                    <strong>{{ $trashedFolders->count() + $trashedFiles->count() }}</strong> item dalam
                                    sampah.
                                    Item akan otomatis terhapus permanen setelah 30 hari.
                                </div>
                            </div>
                        @endif

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="trashTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40">
                                            <input type="checkbox" class="form-check-input" id="selectAll">
                                        </th>
                                        <th>Nama</th>
                                        <th width="150">Pemilik</th>
                                        <th width="150">Tanggal Dihapus</th>
                                        <th width="100">Ukuran</th>
                                        <th width="250">Lokasi Awal</th>
                                        <th width="120" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse(array_merge($trashedFolders->toArray(), $trashedFiles->toArray()) as $item)
                                        @php
                                            $isFolder = isset($item['total_files']);
                                            $itemType = $isFolder ? 'folder' : 'file';

                                            // Get item details
                                            $itemName = $isFolder
                                                ? $item['name']
                                                : $item['name'] ?? $item['original_name'];
                                            $itemOwner = $item['creator']['nama'] ?? 'Unknown';
                                            $itemDeletedAt = \Carbon\Carbon::parse($item['deleted_at'])
                                                ->timezone('Asia/Jayapura')
                                                ->format('d M Y, H:i');
                                            $itemSize = $isFolder
                                                ? formatBytes($item['total_size'] ?? 0)
                                                : formatBytes($item['size'] ?? 0);

                                            // Get original location
                                            if ($isFolder) {
                                                $originalLocation = $item['parent']['name'] ?? 'Root';
                                            } else {
                                                $originalLocation = $item['folder']['name'] ?? 'Unknown';
                                            }

                                            // Icon
                                            if ($isFolder) {
                                                $icon = 'bi-folder-fill text-warning';
                                            } else {
                                                $extension = strtolower(pathinfo($itemName, PATHINFO_EXTENSION));
                                                $icon = match ($extension) {
                                                    'pdf' => 'bi-file-pdf-fill text-danger',
                                                    'doc', 'docx' => 'bi-file-word-fill text-primary',
                                                    'xls', 'xlsx' => 'bi-file-excel-fill text-success',
                                                    'ppt', 'pptx' => 'bi-file-ppt-fill text-warning',
                                                    'jpg',
                                                    'jpeg',
                                                    'png',
                                                    'gif',
                                                    'bmp',
                                                    'webp'
                                                        => 'bi-file-image-fill text-info',
                                                    default => 'bi-file-earmark-fill text-secondary',
                                                };
                                            }
                                        @endphp
                                        <tr data-item-id="{{ $item['id'] }}" data-item-type="{{ $itemType }}">
                                            <td>
                                                <input type="checkbox" class="form-check-input item-checkbox"
                                                    value="{{ $item['id'] }}" data-type="{{ $itemType }}">
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="bi {{ $icon }} fs-4 me-2"></i>
                                                    <div>
                                                        <div class="fw-medium">{{ $itemName }}</div>
                                                        @if (!$isFolder && isset($item['original_name']) && $item['original_name'] !== $itemName)
                                                            <small class="text-muted">{{ $item['original_name'] }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $itemOwner }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $itemDeletedAt }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">{{ $itemSize }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center text-muted">
                                                    <i class="bi bi-folder me-1"></i>
                                                    <span>{{ $originalLocation }}</span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-sm btn-success"
                                                        onclick="restoreItem('{{ $item['id'] }}', '{{ $itemType }}', '{{ addslashes($itemName) }}')"
                                                        title="Pulihkan">
                                                        <i class="bi bi-arrow-counterclockwise"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger"
                                                        onclick="permanentDelete('{{ $item['id'] }}', '{{ $itemType }}', '{{ addslashes($itemName) }}')"
                                                        title="Hapus Permanen">
                                                        <i class="bi bi-trash3-fill"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="empty-state-row">
                                            <td colspan="7" class="text-center py-5">
                                                <div class="empty-state">
                                                    <i class="bi bi-trash3 text-muted" style="font-size: 4rem;"></i>
                                                    <h5 class="text-muted mt-3">Sampah Kosong</h5>
                                                    <p class="text-muted">Tidak ada folder atau file yang dihapus</p>
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
@endsection

@push('styles')
    <style>
        .table> :not(caption)>tr>th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .btn-group .btn {
            padding: 0.25rem 0.5rem;
        }

        .empty-state {
            padding: 3rem 0;
        }

        .card {
            border-radius: 0.5rem;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
        }
    </style>
@endpush

@push('scripts')
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        let dataTable = null;

        $(document).ready(function() {
            // Initialize DataTable only if there are data rows (not empty state)
            const hasEmptyState = $('#trashTable tbody tr.empty-state-row').length > 0;
            const hasDataRows = $('#trashTable tbody tr:not(.empty-state-row)').length > 0;
            
            if (hasDataRows && !hasEmptyState) {
                dataTable = $('#trashTable').DataTable({
                    responsive: true,
                    pageLength: 25,
                    order: [
                        [3, 'desc']
                    ], // Sort by deleted date descending
                    language: {
                        search: "Cari:",
                        lengthMenu: "Tampilkan _MENU_ data",
                        info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                        infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                        infoFiltered: "(disaring dari _MAX_ total data)",
                        paginate: {
                            first: "Pertama",
                            last: "Terakhir",
                            next: "Selanjutnya",
                            previous: "Sebelumnya"
                        },
                        emptyTable: "Tidak ada data tersedia"
                    },
                    columnDefs: [{
                        targets: [0, 6], // Checkbox and Actions columns
                        orderable: false,
                        searchable: false
                    }]
                });
            }

            // Select all checkbox
            $('#selectAll').change(function() {
                $('.item-checkbox').prop('checked', $(this).prop('checked'));
            });

            // Update select all when individual checkbox changes
            $('.item-checkbox').change(function() {
                if (!$(this).prop('checked')) {
                    $('#selectAll').prop('checked', false);
                }
            });

            // Empty trash button
            $('#btnEmptyTrash').click(function() {
                emptyTrash();
            });
        });

        function restoreItem(id, type, name) {
            Swal.fire({
                title: 'Pulihkan Item?',
                html: `Anda akan memulihkan <strong>${name}</strong> ke lokasi asalnya.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-arrow-counterclockwise me-2"></i>Ya, Pulihkan',
                cancelButtonText: 'Batal',
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Memulihkan...',
                        html: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const endpoint = type === 'folder' ?
                        `/terminal-data/api/folders/${id}/restore` :
                        `/terminal-data/api/files/${id}/restore`;

                    $.ajax({
                        url: endpoint,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: response.message || 'Item berhasil dipulihkan',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Gagal memulihkan item'
                            });
                        }
                    });
                }
            });
        }

        function permanentDelete(id, type, name) {
            Swal.fire({
                title: 'Hapus Permanen?',
                html: `<p class="mb-2">Anda akan menghapus <strong>${name}</strong> secara permanen.</p>
                       <p class="text-danger mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Tindakan ini tidak dapat dibatalkan!</p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash3-fill me-2"></i>Ya, Hapus Permanen',
                cancelButtonText: 'Batal',
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Menghapus...',
                        html: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const endpoint = type === 'folder' ?
                        `/terminal-data/api/folders/${id}/force-delete` :
                        `/terminal-data/api/files/${id}/force-delete`;

                    $.ajax({
                        url: endpoint,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: response.message || 'Item berhasil dihapus permanen',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Gagal menghapus item'
                            });
                        }
                    });
                }
            });
        }

        function emptyTrash() {
            const totalItems = $('.item-checkbox').length;
            const checkedItems = $('.item-checkbox:checked');

            // Jika tidak ada yang dipilih, konfirmasi untuk hapus semua
            if (checkedItems.length === 0) {
                if (totalItems === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Sampah Kosong',
                        text: 'Tidak ada item untuk dihapus'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Kosongkan Semua Sampah?',
                    html: `<p class="mb-2">Anda akan menghapus <strong>semua ${totalItems} item</strong> secara permanen.</p>
                           <p class="text-danger mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Tindakan ini tidak dapat dibatalkan!</p>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-trash3-fill me-2"></i>Ya, Hapus Semua',
                    cancelButtonText: 'Batal',
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Select all items and delete
                        $('.item-checkbox').prop('checked', true);
                        performDelete($('.item-checkbox:checked'));
                    }
                });
                return;
            }

            // Jika ada yang dipilih, hapus yang dipilih saja
            Swal.fire({
                title: 'Hapus Item Terpilih?',
                html: `<p class="mb-2">Anda akan menghapus <strong>${checkedItems.length} item</strong> secara permanen.</p>
                       <p class="text-danger mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Tindakan ini tidak dapat dibatalkan!</p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="bi bi-trash3-fill me-2"></i>Ya, Hapus',
                cancelButtonText: 'Batal',
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    performDelete(checkedItems);
                }
            });
        }

        function performDelete(checkedItems) {
            // Show loading
            Swal.fire({
                title: 'Menghapus...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Collect items to delete
            const items = [];
            checkedItems.each(function() {
                items.push({
                    id: $(this).val(),
                    type: $(this).data('type')
                });
            });

            $.ajax({
                url: '/terminal-data/api/trash/empty',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    items: items
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message || 'Sampah berhasil dikosongkan',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: xhr.responseJSON?.message || 'Gagal mengosongkan sampah'
                    });
                }
            });
        }
    </script>
@endpush
