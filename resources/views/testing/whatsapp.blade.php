@extends('layouts.main')

@section('main')
    <div class="pagetitle">
        <h1>Testing WhatsApp (Twilio)</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('e-kinerja.index') }}">E-Kinerja</a></li>
                <li class="breadcrumb-item active">Testing WhatsApp</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-8">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Halaman internal untuk uji coba pengiriman WhatsApp lewat Twilio. Setiap pengiriman
                    dari halaman ini adalah pesan WhatsApp sungguhan dan berbayar sesuai tarif Twilio.
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form action="{{ route('testing.whatsapp.send') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="pegawai_id" class="form-label">Pilih Pegawai (opsional)</label>
                                <select class="form-select" id="pegawai_id">
                                    <option value="">-- Isi manual di bawah --</option>
                                    @foreach ($pegawai as $item)
                                        <option value="{{ $item->profile->no_telepon }}">
                                            {{ $item->nama }} ({{ $item->profile->no_telepon }})
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Memilih pegawai otomatis mengisi nomor tujuan di
                                    bawah, tetap bisa diedit manual.</small>
                            </div>

                            <div class="mb-3">
                                <label for="nomor_tujuan" class="form-label">Nomor Tujuan</label>
                                <input type="text" class="form-control @error('nomor_tujuan') is-invalid @enderror"
                                    id="nomor_tujuan" name="nomor_tujuan" value="{{ old('nomor_tujuan') }}"
                                    placeholder="08xxxxxxxxxx atau +62xxxxxxxxxx" required>
                                @error('nomor_tujuan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="pesan" class="form-label">Pesan</label>
                                <textarea class="form-control @error('pesan') is-invalid @enderror" id="pesan"
                                    name="pesan" rows="4" required>{{ old('pesan', 'Ini adalah pesan uji coba dari aplikasi TERDEPAN.') }}</textarea>
                                @error('pesan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-whatsapp me-1"></i> Kirim Pesan Uji
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            document.getElementById('pegawai_id').addEventListener('change', function () {
                if (this.value) {
                    document.getElementById('nomor_tujuan').value = this.value;
                }
            });
        </script>
    @endpush
@endsection
