<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterJabatan;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class MasterJabatanController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        try {
            $this->authorize('viewAny', MasterJabatan::class);

            $data = MasterJabatan::orderBy('level', 'ASC')->with(['pegawai'])->get();

            return view('master-data.index-jabatan', compact('data'));
        } catch (AuthorizationException $e) {
            abort(403, 'Unauthorized');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function create()
    {
        return view('master-data.create-jabatan');
    }

    public function store(Request $request)
    {
        try {
            $this->authorize('create', MasterJabatan::class);

            $data = $request->validate([
                'kode' => 'required|unique:master_jabatan,kode|max:10',
                'nama' => 'required|string|max:100',
                'level' => 'required|integer|min:1|max:6',
                'is_struktural' => 'boolean',
                'bebas_nilai_kinerja' => 'boolean',
                'is_active' => 'boolean',
            ]);

            // Set default values if not provided
            if (! isset($data['is_struktural'])) {
                $data['is_struktural'] = false;
            }
            if (! isset($data['bebas_nilai_kinerja'])) {
                $data['bebas_nilai_kinerja'] = false;
            }
            if (! isset($data['is_active'])) {
                $data['is_active'] = true;
            }

            $jabatan = MasterJabatan::create($data);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Jabatan berhasil ditambah',
                    'data' => $jabatan,
                ]);
            }

            return redirect()->route('master.jabatan.index')->with('success', 'Jabatan berhasil ditambah');
        } catch (AuthorizationException $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'Unauthorized');
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

    public function show($id)
    {
        try {
            $jabatan = MasterJabatan::with(['pegawai'])->findOrFail($id);

            return view('master-data.show-edit-jabatan', compact('jabatan'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Jabatan tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $jabatan = MasterJabatan::with(['pegawai'])->findOrFail($id);

            return view('master-data.show-edit-jabatan', compact('jabatan'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Jabatan tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $jabatan = MasterJabatan::findOrFail($id);

            $this->authorize('update', $jabatan);

            $data = $request->validate([
                'kode' => 'required|unique:master_jabatan,kode,'.$id.'|max:10',
                'nama' => 'required|string|max:100',
                'level' => 'required|integer|min:1|max:6',
                'is_struktural' => 'boolean',
                'bebas_nilai_kinerja' => 'boolean',
                'is_active' => 'boolean',
            ]);

            $jabatan->update($data);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Jabatan berhasil diperbarui',
                    'data' => $jabatan,
                ]);
            }

            return redirect()->route('master.jabatan.show', $id)->with('success', 'Jabatan berhasil diperbarui');
        } catch (AuthorizationException $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'Unauthorized');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Jabatan tidak ditemukan'], 404);
            }

            return redirect()->back()->with('error', 'Jabatan tidak ditemukan');
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
            $jabatan = MasterJabatan::findOrFail($id);

            $this->authorize('delete', $jabatan);

            // Check if jabatan has pegawai
            if ($jabatan->pegawai()->count() > 0) {
                $message = 'Jabatan tidak dapat dihapus karena masih memiliki pegawai';
                if ($request->ajax()) {
                    return response()->json(['error' => $message], 422);
                }

                return redirect()->back()->with('error', $message);
            }

            $jabatan->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Jabatan berhasil dihapus',
                ]);
            }

            return redirect()->route('master.jabatan.index')->with('success', 'Jabatan berhasil dihapus');
        } catch (AuthorizationException $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'Unauthorized');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Jabatan tidak ditemukan'], 404);
            }

            return redirect()->back()->with('error', 'Jabatan tidak ditemukan');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
