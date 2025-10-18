<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterBidang;
use Illuminate\Http\Request;

class MasterBidangController extends Controller
{
    public function index()
    {
        try {
            $data = MasterBidang::all();
            return view('master-data.index-bidang', compact('data'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function create()
    {
        return view('master-data.create-bidang');
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'kode' => 'required|unique:master_bidang,kode|max:20',
                'nama' => 'required|string|max:100',
                'deskripsi' => 'nullable|string',
                'warna' => 'nullable|string|max:7',
                'is_active' => 'boolean',
            ]);

            // Set default is_active if not provided
            if (!isset($data['is_active'])) {
                $data['is_active'] = true;
            }

            $bidang = MasterBidang::create($data);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bidang berhasil ditambah',
                    'data' => $bidang
                ]);
            }

            return redirect()->route('master.bidang.index')->with('success', 'Bidang berhasil ditambah');
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
            $bidang = MasterBidang::with(['pegawai'])->findOrFail($id);
            return view('master-data.show-edit-bidang', compact('bidang'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Bidang tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $bidang = MasterBidang::findOrFail($id);
            return view('master-data.edit-bidang', compact('bidang'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Bidang tidak ditemukan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $bidang = MasterBidang::findOrFail($id);

            $data = $request->validate([
                'kode' => 'required|unique:master_bidang,kode,' . $id . '|max:20',
                'nama' => 'required|string|max:100',
                'deskripsi' => 'nullable|string',
                'warna' => 'nullable|string|max:7',
                'is_active' => 'boolean',
            ]);

            $bidang->update($data);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bidang berhasil diperbarui',
                    'data' => $bidang
                ]);
            }

            return redirect()->route('master.bidang.show', $id)->with('success', 'Bidang berhasil diperbarui');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Bidang tidak ditemukan'], 404);
            }
            return redirect()->back()->with('error', 'Bidang tidak ditemukan');
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
            $bidang = MasterBidang::findOrFail($id);

            // Check if bidang has pegawai
            if ($bidang->pegawai()->count() > 0) {
                $message = 'Bidang tidak dapat dihapus karena masih memiliki pegawai';
                if ($request->ajax()) {
                    return response()->json(['error' => $message], 422);
                }
                return redirect()->back()->with('error', $message);
            }

            $bidang->delete();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bidang berhasil dihapus'
                ]);
            }

            return redirect()->route('master.bidang.index')->with('success', 'Bidang berhasil dihapus');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Bidang tidak ditemukan'], 404);
            }
            return redirect()->back()->with('error', 'Bidang tidak ditemukan');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
