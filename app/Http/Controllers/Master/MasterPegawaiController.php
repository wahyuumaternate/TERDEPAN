<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterPegawai;
use App\Models\MasterJabatan;
use App\Models\MasterBidang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MasterPegawaiController extends Controller
{
    public function index()
    {
        try {
            $data = MasterPegawai::with(['jabatan', 'bidang', 'atasanLangsung', 'ttdDigital'])->get();
            return view('master-data.index-pegawai', compact('data'));
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
            // Debug: Log request data
            Log::info('Store Pegawai Request Data:', $request->all());

            $data = $request->validate([
                'nomor_identitas' => 'required|unique:master_pegawai,nomor_identitas',
                'tipe_identitas' => 'required|in:NIP,NIK',
                'nama' => 'required|string|max:100',
                'jabatan_id' => 'required|exists:master_jabatan,id',
                'bidang_id' => 'required|exists:master_bidang,id',
                'jenis_kelamin' => 'required|in:L,P',
                'status_kepegawaian' => 'required|in:PNS,PPPK,Kontrak',
                'status_aktif' => 'required|in:Aktif,Nonaktif,Cuti,Pensiun',
                'email' => 'required|email|unique:master_pegawai,email',
                'no_telepon' => 'nullable|string|max:20',
                'pangkat' => 'nullable|string|max:50',
                'golongan' => 'nullable|string|max:10',
                'gelar_depan' => 'nullable|string|max:20',
                'gelar_belakang' => 'nullable|string|max:20',
                'tanggal_lahir' => 'nullable|date',
                'alamat' => 'nullable|string',
                'tanggal_masuk' => 'nullable|date',
                'tanggal_keluar' => 'nullable|date',
                'atasan_langsung_id' => 'nullable|exists:master_pegawai,id',
                'password' => 'required|min:6|confirmed',
                'foto_profile' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            ]);

            Log::info('Validated Data:', $data);

            // Hash password
            $data['password'] = bcrypt($data['password']);

            // Set default status_aktif if not provided
            if (!isset($data['status_aktif'])) {
                $data['status_aktif'] = 'Aktif';
            }

            // Handle photo upload before creating pegawai
            $fotoPath = null;
            if ($request->hasFile('foto_profile')) {
                $file = $request->file('foto_profile');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $fotoPath = 'uploads/pegawai/photos/' . $filename;

                // Create directory if not exists
                $directory = public_path('uploads/pegawai/photos');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                $file->move($directory, $filename);
                Log::info('Photo uploaded to: ' . $fotoPath);
            }

            // Add photo path to data
            if ($fotoPath) {
                $data['foto_profile_path'] = $fotoPath;
            }

            // Remove foto_profile from data (it's not a database field)
            unset($data['foto_profile']);

            Log::info('Final data before create:', $data);

            // Create pegawai
            $pegawai = MasterPegawai::create($data);

            Log::info('Pegawai created with ID: ' . $pegawai->id);

            return redirect()->route('master.pegawai.index')->with('success', 'Pegawai berhasil ditambah');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation Error:', $e->errors());
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Store Pegawai Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $pegawai = MasterPegawai::with(['jabatan', 'bidang', 'atasanLangsung', 'ttdDigital'])->findOrFail($id);
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
            $pegawai = MasterPegawai::findOrFail($id);
            return view('master.pegawai.edit', compact('pegawai'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Pegawai tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $pegawai = MasterPegawai::findOrFail($id);

            $data = $request->validate([
                'nomor_identitas' => 'required|unique:master_pegawai,nomor_identitas,' . $id,
                'tipe_identitas' => 'required|in:NIP,NIK',
                'nama' => 'required|string|max:100',
                'jabatan_id' => 'required|exists:master_jabatan,id',
                'bidang_id' => 'required|exists:master_bidang,id',
                'jenis_kelamin' => 'required|in:L,P',
                'status_kepegawaian' => 'required|in:PNS,PPPK,Kontrak',
                'status_aktif' => 'required|in:Aktif,Nonaktif,Cuti,Pensiun',
                'email' => 'required|email|unique:master_pegawai,email,' . $id,
                'no_telepon' => 'nullable|string|max:20',
                'pangkat' => 'nullable|string|max:50',
                'golongan' => 'nullable|string|max:10',
                'gelar_depan' => 'nullable|string|max:20',
                'gelar_belakang' => 'nullable|string|max:20',
                'tanggal_lahir' => 'nullable|date',
                'alamat' => 'nullable|string',
                'tanggal_masuk' => 'nullable|date',
                'tanggal_keluar' => 'nullable|date',
                'atasan_langsung_id' => 'nullable|exists:master_pegawai,id',
                'password' => 'nullable|min:6|confirmed',
                'foto_profile' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            ]);

            // Handle password update
            if (!empty($data['password'])) {
                $data['password'] = bcrypt($data['password']);
            } else {
                unset($data['password']);
            }

            // Handle photo upload
            if ($request->hasFile('foto_profile')) {
                // Delete old photo if exists
                if ($pegawai->foto_profile_path && file_exists(public_path($pegawai->foto_profile_path))) {
                    unlink(public_path($pegawai->foto_profile_path));
                }

                // Store new photo
                $file = $request->file('foto_profile');
                $filename = time() . '_' . $pegawai->id . '.' . $file->getClientOriginalExtension();
                $path = 'uploads/pegawai/photos/' . $filename;

                // Create directory if not exists
                $directory = public_path('uploads/pegawai/photos');
                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                $file->move($directory, $filename);
                $data['foto_profile_path'] = $path;
            }

            // Update pegawai data
            $pegawai->update($data);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data pegawai berhasil diperbarui'
                ]);
            }

            return redirect()->route('master.pegawai.show', $id)->with('success', 'Data pegawai berhasil diperbarui');
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
            $pegawai = MasterPegawai::findOrFail($id);

            // Delete photo file if exists
            if ($pegawai->foto_profile_path && file_exists(public_path($pegawai->foto_profile_path))) {
                unlink(public_path($pegawai->foto_profile_path));
            }

            $pegawai->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Pegawai berhasil dihapus'
                ]);
            }

            return redirect()->route('master.pegawai.index')->with('success', 'Pegawai berhasil dihapus');
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
