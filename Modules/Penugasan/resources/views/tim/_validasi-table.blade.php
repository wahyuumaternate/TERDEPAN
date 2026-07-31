<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th style="width: 30%">Nama Tugas</th>
                <th>Pegawai</th>
                <th class="text-center">Submit</th>
                <th class="text-center">Deadline</th>
                <th class="text-center">Target</th>
                <th class="text-center">File</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tugas as $t)
                <tr>
                    <td>
                        <a href="javascript:void(0)" onclick="viewDetail({{ $t->id }}, '{{ $t->tipe }}')"
                            class="text-decoration-none fw-semibold text-dark">
                            {{ $t->nama_tugas }}
                        </a>
                        @if ($t->tipe === 'harian' && $t->tugasPokok)
                            <br><small class="text-muted">
                                <i class="bi bi-diagram-3"></i> {{ Str::limit($t->tugasPokok->nama_tugas, 30) }}
                            </small>
                        @endif
                        @if ($t->is_lintas_bidang)
                            <span class="badge bg-info">
                                <i class="bi bi-arrow-left-right"></i> Lintas Bidang
                            </span>
                        @endif
                    </td>
                    <td>
                        {{ $t->pegawai->nama ?? 'N/A' }}
                        @if ($t->pegawai && $t->pegawai->profile->jabatan)
                            <br><small class="text-muted">{{ $t->pegawai->profile->jabatan->nama_jabatan }}</small>
                        @endif
                    </td>
                    <td class="text-center">
                        <small
                            class="text-muted">{{ $t->submitted_at ? $t->submitted_at->format('d M Y') : '-' }}</small>
                        @if ($t->submitted_at)
                            <br><small class="text-muted">{{ $t->submitted_at->diffForHumans() }}</small>
                        @endif
                    </td>
                    <td class="text-center">
                        @php
                            $deadline = \Carbon\Carbon::parse($t->tanggal_selesai);
                            $daysLeft = now()->diffInDays($deadline, false);
                        @endphp
                        {{ $deadline->format('d M Y') }}
                        @if ($daysLeft < 0)
                            <br><span class="badge bg-danger">Terlambat</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <strong>{{ $t->target_value }}</strong>
                        <br><small class="text-muted">{{ $t->satuan }}</small>
                    </td>
                    <td class="text-center">
                        @if ($t->attachedFiles && $t->attachedFiles->count() > 0)
                            <span class="badge bg-success">
                                <i class="bi bi-paperclip"></i> {{ $t->attachedFiles->count() }} file
                            </span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                onclick="viewDetail({{ $t->id }}, '{{ $t->tipe }}')" title="Detail">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success"
                                onclick="showSetujuiModal({{ $t->id }}, '{{ $t->tipe }}')"
                                title="Setujui">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger"
                                onclick="showRevisiModal({{ $t->id }}, '{{ $t->tipe }}')" title="Revisi">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-3">Tidak ada tugas yang menunggu validasi</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
