<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MasterPegawaiController extends Controller
{
    use AuthorizesRequests;

    private const PROFILE_FIELDS = [
        'nomor_identitas', 'tipe_identitas', 'jabatan_id', 'bidang_id', 'sub_bidang_id',
        'jenis_kelamin', 'status_kepegawaian', 'status_aktif', 'no_telepon', 'pangkat',
        'golongan', 'gelar_depan', 'gelar_belakang', 'tanggal_lahir', 'alamat',
        'tanggal_masuk', 'tanggal_keluar', 'atasan_langsung_id', 'foto_profile_path',
    ];

    public function index()
    {
        try {
            $this->authorize('viewAny', User::class);

            $data = User::with(['profile.jabatan', 'profile.bidang', 'profile.atasanLangsung'])->get();

            return view('master-data.index-pegawai', compact('data'));
        } catch (AuthorizationException $e) {
            abort(403, 'Unauthorized');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
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
                'jenis_kelamin' => 'required|in:L,P',
                'status_kepegawaian' => 'required|in:PNS,PPPK,Kontrak',
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
                'password' => 'required|min:6|confirmed',
                'foto_profile' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            ]);

            Log::info('Validated Data:', $data);

            if (! isset($data['status_aktif'])) {
                $data['status_aktif'] = 'Aktif';
            }

            // Handle photo upload before creating pegawai
            $fotoPath = null;
            if ($request->hasFile('foto_profile')) {
                $file = $request->file('foto_profile');
                $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                $fotoPath = 'uploads/pegawai/photos/'.$filename;

                $directory = public_path('uploads/pegawai/photos');
                if (! file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                $file->move($directory, $filename);
                Log::info('Photo uploaded to: '.$fotoPath);
            }

            if ($fotoPath) {
                $data['foto_profile_path'] = $fotoPath;
            }

            unset($data['foto_profile']);

            $profileData = array_intersect_key($data, array_flip(self::PROFILE_FIELDS));

            $pegawai = User::create([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
            ]);

            $pegawai->profile()->create($profileData);

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
            $pegawai = User::with(['profile.jabatan', 'profile.bidang', 'profile.atasanLangsung'])->findOrFail($id);

            return view('master-data.show-edit-pegawai', compact('pegawai'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Pegawai tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $pegawai = User::with(['profile.jabatan', 'profile.bidang', 'profile.atasanLangsung'])->findOrFail($id);

            return view('master-data.show-edit-pegawai', compact('pegawai'));
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
                'jenis_kelamin' => 'required|in:L,P',
                'status_kepegawaian' => 'required|in:PNS,PPPK,Kontrak',
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
                'password' => 'nullable|min:6|confirmed',
                'foto_profile' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            ]);

            $password = null;
            if (! empty($data['password'])) {
                $password = bcrypt($data['password']);
            }
            unset($data['password']);

            if ($request->hasFile('foto_profile')) {
                if ($pegawai->profile?->foto_profile_path && file_exists(public_path($pegawai->profile->foto_profile_path))) {
                    unlink(public_path($pegawai->profile->foto_profile_path));
                }

                $file = $request->file('foto_profile');
                $filename = time().'_'.$pegawai->id.'.'.$file->getClientOriginalExtension();
                $path = 'uploads/pegawai/photos/'.$filename;

                $directory = public_path('uploads/pegawai/photos');
                if (! file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                $file->move($directory, $filename);
                $data['foto_profile_path'] = $path;
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

            if ($pegawai->profile?->foto_profile_path && file_exists(public_path($pegawai->profile->foto_profile_path))) {
                unlink(public_path($pegawai->profile->foto_profile_path));
            }

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
}
