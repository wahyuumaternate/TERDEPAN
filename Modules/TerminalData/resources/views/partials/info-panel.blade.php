{{-- Panel informasi file/folder (sidebar kanan, tab Detail + Aktivitas). Dipakai dari
     folder/detail.blade.php lewat openInfoPanel('file'|'folder', id) --}}
<div id="infoPanelOverlay" class="info-panel-overlay" onclick="closeInfoPanel()"></div>

<div id="infoPanel" class="info-panel">
    <div class="info-panel-header">
        <div id="infoPanelIcon" class="info-panel-icon">
            <i class="bi bi-file-earmark-text-fill"></i>
        </div>
        <div class="info-panel-title flex-grow-1">
            <div id="infoPanelName" class="fw-semibold text-truncate"></div>
        </div>
        <button type="button" class="btn-close" aria-label="Tutup" onclick="closeInfoPanel()"></button>
    </div>

    <ul class="nav nav-tabs info-panel-tabs px-3 justify-content-evenly" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#infoPanelTabDetail" type="button" role="tab">
                Detail
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#infoPanelTabAktivitas" type="button" role="tab">
                Aktivitas
            </button>
        </li>
    </ul>

    <div class="info-panel-body tab-content">
        <div class="tab-pane fade show active" id="infoPanelTabDetail" role="tabpanel">
            <div id="infoPanelDetailLoading" class="info-panel-loading">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                <span class="ms-2 text-muted">Memuat...</span>
            </div>
            <div id="infoPanelDetailContent" style="display:none;">
                <div id="infoPanelPreview" class="info-panel-preview"></div>

                <div class="info-panel-section">
                    <div class="info-panel-row">
                        <span class="info-panel-label">Jenis</span>
                        <span id="infoPanelJenis" class="info-panel-value"></span>
                    </div>
                    <div id="infoPanelUkuranRow" class="info-panel-row">
                        <span class="info-panel-label">Ukuran</span>
                        <span id="infoPanelUkuran" class="info-panel-value"></span>
                    </div>
                    <div class="info-panel-row">
                        <span class="info-panel-label">Lokasi</span>
                        <span id="infoPanelLokasi" class="info-panel-value"></span>
                    </div>
                    <div class="info-panel-row">
                        <span class="info-panel-label">Dibuat oleh</span>
                        <span id="infoPanelDibuatOleh" class="info-panel-value"></span>
                    </div>
                    <div class="info-panel-row">
                        <span class="info-panel-label">Dibuat pada</span>
                        <span id="infoPanelDibuatPada" class="info-panel-value"></span>
                    </div>
                    <div class="info-panel-row">
                        <span class="info-panel-label">Diubah terakhir</span>
                        <span id="infoPanelDiubahPada" class="info-panel-value"></span>
                    </div>
                </div>

                <div class="info-panel-section">
                    <div class="info-panel-section-title">
                        <i class="bi bi-shield-lock me-1"></i>Akses
                    </div>
                    <div class="info-panel-row">
                        <span class="info-panel-label">Bidang</span>
                        <span id="infoPanelBidang" class="info-panel-value"></span>
                    </div>
                    <div id="infoPanelSubBidangRow" class="info-panel-row">
                        <span class="info-panel-label">Sub Bidang</span>
                        <span id="infoPanelSubBidang" class="info-panel-value"></span>
                    </div>
                    <div class="info-panel-row">
                        <span class="info-panel-label">Visibilitas</span>
                        <span id="infoPanelVisibilitas" class="info-panel-value"></span>
                    </div>
                    <div class="info-panel-row">
                        <span class="info-panel-label">Pemilik</span>
                        <span id="infoPanelPemilik" class="info-panel-value"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="infoPanelTabAktivitas" role="tabpanel">
            <div id="infoPanelAktivitasLoading" class="info-panel-loading">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                <span class="ms-2 text-muted">Memuat...</span>
            </div>
            <div id="infoPanelAktivitasList" style="display:none;"></div>
            <div id="infoPanelAktivitasEmpty" class="info-panel-empty" style="display:none;">
                <i class="bi bi-clock-history fs-2 text-muted d-block mb-2"></i>
                <span class="text-muted">Belum ada aktivitas</span>
            </div>
        </div>
    </div>
</div>
