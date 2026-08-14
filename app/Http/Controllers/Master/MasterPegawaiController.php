<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterJabatan;
use App\Models\User;
use App\Services\PegawaiCsvImporter;
use App\Services\ProfilePhotoService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Modules\Penugasan\Services\AtasanMandiriEligibility;

class MasterPegawaiController extends Controller
{
    use AuthorizesRequests;

    private const PROFILE_FIELDS = [
        'nomor_identitas', 'tipe_identitas', 'jabatan_id', 'bidang_id', 'sub_bidang_id',
        'jenis_kelamin', 'status_kepegawaian', 'status_aktif', 'no_telepon', 'pangkat',
        'golongan', 'gelar_depan', 'gelar_belakang', 'tanggal_lahir', 'alamat',
        'tanggal_masuk', 'tanggal_keluar', 'atasan_langsung_id', 'foto_profile_path', 'disk',
        'tempat_lahir', 'agama', 'tmt_cpns', 'tmt_pns', 'tmt_golongan', 'eselon',
        'pendidikan_terakhir', 'jenjang_pendidikan', 'jabatan_asli',
    ];

    public function __construct(
        private readonly ProfilePhotoService $photoService,
        private readonly AtasanMandiriEligibility $atasanEligibility,
    ) {}

    public function index()
    {
        try {
            $this->authorize('viewAny', User::class);

            $query = User::with(['profile.jabatan', 'profile.bidang', 'profile.atasanLangsung'])
                ->leftJoin('user_profiles', 'user_profiles.user_id', '=', 'users.id')
                ->leftJoin('master_jabatan', 'master_jabatan.id', '=', 'user_profiles.jabatan_id')
                ->orderBy('master_jabatan.level', 'asc')
                ->orderBy('user_profiles.nomor_identitas', 'asc')
                ->select('users.*');

            // Admin Sistem cuma tampil di daftar untuk sesama Admin Sistem — pegawai/atasan
            // lain yang buka Master Data Pegawai tidak perlu (dan tidak semestinya) melihat
            // akun admin sistem di daftar ini.
            if (auth()->user()->profile?->jabatan?->kode !== 'ADMIN') {
                $query->where(function ($q) {
                    $q->whereNull('master_jabatan.kode')->orWhere('master_jabatan.kode', '!=', 'ADMIN');
                });
            }

            $data = $query->get();

            return view('master-data.index-pegawai', compact('data'));
        } catch (AuthorizationException $e) {
            abort(403, 'Unauthorized');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Kandidat Atasan Langsung untuk kombinasi jabatan+bidang yang SEDANG dipilih di form
     * (belum tentu tersimpan) — dipanggil lewat AJAX saat admin mengganti Jabatan/Bidang di
     * mode edit, supaya opsi Atasan Langsung ikut update tanpa perlu simpan dulu.
     */
    public function atasanCandidates(Request $request)
    {
        try {
            $this->authorize('viewAny', User::class);

            $jabatanId = $request->query('jabatan_id');
            $bidangId = $request->query('bidang_id');
            $excludeId = $request->query('exclude');

            $kodeJabatan = $jabatanId ? MasterJabatan::find($jabatanId)?->kode : null;

            $kandidat = $this->atasanEligibility->kandidatUntukKombinasi($kodeJabatan, $bidangId ? (int) $bidangId : null);

            if ($kandidat->isNotEmpty()) {
                $kandidat->load('profile.jabatan');
            }

            return response()->json(
                $kandidat->reject(fn ($user) => $excludeId && (int) $user->id === (int) $excludeId)
                    ->map(fn ($user) => [
                        'id' => $user->id,
                        'nama' => $user->nama,
                        'jabatan' => $user->profile?->jabatan?->nama,
                    ])
                    ->values()
            );
        } catch (AuthorizationException $e) {
            abort(403, 'Unauthorized');
        }
    }

    public function create()
    {
        return view('master-data.create-pegawai');
    }

    public function store(Request $request)
    {
        try {
            $this->authorize('create', User::class);

            Log::info('Store Pegawai Request Data:', $request->all());

            $data = $request->validate([
                'nomor_identitas' => 'required|unique:user_profiles,nomor_identitas',
                'tipe_identitas' => 'required|in:NIP,NIK',
                'nama' => 'required|string|max:100',
                'jabatan_id' => 'required|exists:master_jabatan,id',
                'bidang_id' => 'required|exists:master_bidang,id',
                'sub_bidang_id' => ['nullable', Rule::exists('master_sub', 'id')->where('bidang_id', $request->input('bidang_id'))],
                'jenis_kelamin' => 'required|in:L,P',
                'status_kepegawaian' => 'required|in:PNS,CPNS,PPPK,Kontrak',
                'status_aktif' => 'required|in:Aktif,Nonaktif,Cuti,Pensiun',
                'email' => 'required|email|unique:users,email',
                'no_telepon' => 'nullable|string|max:20',
                'pangkat' => 'nullable|string|max:50',
                'golongan' => 'nullable|string|max:10',
                'gelar_depan' => 'nullable|string|max:20',
                'gelar_belakang' => 'nullable|string|max:20',
                'tanggal_lahir' => 'nullable|date',
                'alamat' => 'nullable|string',
                'tanggal_masuk' => 'nullable|date',
                'tanggal_keluar' => 'nullable|date',
                'atasan_langsung_id' => 'nullable|exists:users,id',
                'tempat_lahir' => 'nullable|string|max:100',
                'agama' => 'nullable|string|max:20',
                'tmt_cpns' => 'nullable|date',
                'tmt_pns' => 'nullable|date',
                'tmt_golongan' => 'nullable|date',
                'eselon' => 'nullable|string|max:20',
                'pendidikan_terakhir' => 'nullable|string|max:100',
                'jenjang_pendidikan' => 'nullable|string|max:50',
                'jabatan_asli' => 'nullable|string|max:150',
                'password' => 'required|min:6|confirmed',
                'foto_profile' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            ]);

            Log::info('Validated Data:', $data);

            if (! isset($data['status_aktif'])) {
                $data['status_aktif'] = 'Aktif';
            }

            $fotoProfile = $request->file('foto_profile');
            unset($data['foto_profile']);

            $profileData = array_intersect_key($data, array_flip(self::PROFILE_FIELDS));

            // User + profile dibungkus 1 transaction — tanpa ini, kalau profile()->create()
            // gagal (mis. constraint tidak terduga), baris users sudah kepalang tersimpan dan
            // jadi pegawai "yatim" tanpa profile yang bikin halaman lain error null pointer.
            // Foto disimpan DI DALAM transaction ini (setelah User::create()) supaya nama
            // filenya bisa pakai id pegawai yang sebenarnya, bukan uniqid() sementara.
            $pegawai = DB::transaction(function () use ($data, $profileData, $fotoProfile) {
                $pegawai = User::create([
                    'nama' => $data['nama'],
                    'email' => $data['email'],
                    'password' => bcrypt($data['password']),
                    'must_change_password' => true,
                ]);

                if ($fotoProfile) {
                    $stored = $this->photoService->store($fotoProfile, $pegawai->id);
                    $profileData['foto_profile_path'] = $stored['path'];
                    $profileData['disk'] = $stored['disk'];
                }

                $pegawai->profile()->create($profileData);

                return $pegawai;
            });

            Log::info('Pegawai created with ID: '.$pegawai->id);

            return redirect()->route('master.pegawai.index')->with('success', 'Pegawai berhasil ditambah');
        } catch (AuthorizationException $e) {
            abort(403, 'Unauthorized');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Error:', $e->errors());

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Store Pegawai Error: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $pegawai = User::with(['profile.jabatan', 'profile.bidang', 'profile.subBidang', 'profile.atasanLangsung'])->findOrFail($id);
            $atasanCandidates = $this->atasanEligibility->kandidatUntuk($pegawai);
            if ($atasanCandidates->isNotEmpty()) {
                $atasanCandidates->load('profile.jabatan');
            }

            return view('master-data.show-edit-pegawai', compact('pegawai', 'atasanCandidates'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Pegawai tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $pegawai = User::with(['profile.jabatan', 'profile.bidang', 'profile.subBidang', 'profile.atasanLangsung'])->findOrFail($id);
            $atasanCandidates = $this->atasanEligibility->kandidatUntuk($pegawai);
            if ($atasanCandidates->isNotEmpty()) {
                $atasanCandidates->load('profile.jabatan');
            }

            return view('master-data.show-edit-pegawai', compact('pegawai', 'atasanCandidates'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Pegawai tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $pegawai = User::with('profile')->findOrFail($id);

            $this->authorize('update', $pegawai);

            $data = $request->validate([
                'nomor_identitas' => 'required|unique:user_profiles,nomor_identitas,'.$pegawai->profile?->id,
                'tipe_identitas' => 'required|in:NIP,NIK',
                'nama' => 'required|string|max:100',
                'jabatan_id' => 'required|exists:master_jabatan,id',
                'bidang_id' => 'required|exists:master_bidang,id',
                'sub_bidang_id' => ['nullable', Rule::exists('master_sub', 'id')->where('bidang_id', $request->input('bidang_id'))],
                'jenis_kelamin' => 'required|in:L,P',
                'status_kepegawaian' => 'required|in:PNS,CPNS,PPPK,Kontrak',
                'status_aktif' => 'required|in:Aktif,Nonaktif,Cuti,Pensiun',
                'email' => 'required|email|unique:users,email,'.$id,
                'no_telepon' => 'nullable|string|max:20',
                'pangkat' => 'nullable|string|max:50',
                'golongan' => 'nullable|string|max:10',
                'gelar_depan' => 'nullable|string|max:20',
                'gelar_belakang' => 'nullable|string|max:20',
                'tanggal_lahir' => 'nullable|date',
                'alamat' => 'nullable|string',
                'tanggal_masuk' => 'nullable|date',
                'tanggal_keluar' => 'nullable|date',
                'atasan_langsung_id' => 'nullable|exists:users,id',
                'tempat_lahir' => 'nullable|string|max:100',
                'agama' => 'nullable|string|max:20',
                'tmt_cpns' => 'nullable|date',
                'tmt_pns' => 'nullable|date',
                'tmt_golongan' => 'nullable|date',
                'eselon' => 'nullable|string|max:20',
                'pendidikan_terakhir' => 'nullable|string|max:100',
                'jenjang_pendidikan' => 'nullable|string|max:50',
                'jabatan_asli' => 'nullable|string|max:150',
                'password' => 'nullable|min:6|confirmed',
                'foto_profile' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            ]);

            $password = null;
            if (! empty($data['password'])) {
                $password = bcrypt($data['password']);
            }
            unset($data['password']);

            if ($request->hasFile('foto_profile')) {
                $this->photoService->delete($pegawai->profile?->foto_profile_path, $pegawai->profile?->disk);

                $stored = $this->photoService->store($request->file('foto_profile'), $pegawai->id);
                $data['foto_profile_path'] = $stored['path'];
                $data['disk'] = $stored['disk'];
            }
            unset($data['foto_profile']);

            $pegawai->update(array_filter([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'password' => $password,
            ], fn ($value) => $value !== null));

            $profileData = array_intersect_key($data, array_flip(self::PROFILE_FIELDS));
            $pegawai->profile()->updateOrCreate(['user_id' => $pegawai->id], $profileData);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data pegawai berhasil diperbarui',
                ]);
            }

            return redirect()->route('master.pegawai.show', $id)->with('success', 'Data pegawai berhasil diperbarui');
        } catch (AuthorizationException $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'Unauthorized');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Pegawai tidak ditemukan'], 404);
            }

            return redirect()->back()->with('error', 'Pegawai tidak ditemukan');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }

            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $pegawai = User::with('profile')->findOrFail($id);

            $this->authorize('delete', $pegawai);

            $this->photoService->delete($pegawai->profile?->foto_profile_path, $pegawai->profile?->disk);

            $pegawai->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pegawai berhasil dihapus',
                ]);
            }

            return redirect()->route('master.pegawai.index')->with('success', 'Pegawai berhasil dihapus');
        } catch (AuthorizationException $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'Unauthorized');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Pegawai tidak ditemukan'], 404);
            }

            return redirect()->back()->with('error', 'Pegawai tidak ditemukan');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Form upload CSV (docs/flow/02-alur-autentikasi.md §Kelola Data Pegawai poin 3).
     */
    public function import()
    {
        $this->authorize('create', User::class);

        return view('master-data.import-pegawai');
    }

    public function importStore(Request $request, PegawaiCsvImporter $importer)
    {
        try {
            $this->authorize('create', User::class);

            $request->validate([
                'file_csv' => 'required|mimes:csv,txt|max:5120',
            ]);

            $hasil = $importer->importFromFile($request->file('file_csv')->getRealPath());

            $jumlahBerhasil = count($hasil['berhasil']);
            $pesan = "{$jumlahBerhasil} pegawai berhasil diimpor.";
            if (! empty($hasil['dilewati'])) {
                $pesan .= ' '.count($hasil['dilewati']).' baris dilewati.';
            }

            return redirect()->route('master.pegawai.import')
                ->with('success', $pesan)
                ->with('import_dilewati', $hasil['dilewati']);
        } catch (AuthorizationException $e) {
            abort(403, 'Unauthorized');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error('Import Pegawai CSV Error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Gagal mengimpor file: '.$e->getMessage());
        }
    }

    /**
     * Kirim email berisi link untuk mengatur password (docs/flow/02-alur-autentikasi.md
     * §Login Awal - Kirim email login) ke satu atau banyak pegawai terpilih — reuse
     * mekanisme reset password bawaan Breeze (Password::sendResetLink), bukan sistem
     * token terpisah.
     */
    public function kirimEmailLogin(Request $request)
    {
        try {
            $this->authorize('create', User::class);

            $data = $request->validate([
                'user_ids' => 'required|array|min:1',
                'user_ids.*' => 'exists:users,id',
            ]);

            $pegawaiList = User::whereIn('id', $data['user_ids'])->get();

            $berhasil = 0;
            $gagal = [];

            foreach ($pegawaiList as $pegawai) {
                $status = Password::sendResetLink(['email' => $pegawai->email]);

                if ($status === Password::RESET_LINK_SENT) {
                    $berhasil++;
                } else {
                    $gagal[] = $pegawai->nama;
                }
            }

            $pesan = "Email login berhasil dikirim ke {$berhasil} pegawai.";
            if (! empty($gagal)) {
                $pesan .= ' Gagal untuk: '.implode(', ', $gagal).'.';
            }

            return redirect()->route('master.pegawai.index')->with('success', $pesan);
        } catch (AuthorizationException $e) {
            abort(403, 'Unauthorized');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }
    }
}
