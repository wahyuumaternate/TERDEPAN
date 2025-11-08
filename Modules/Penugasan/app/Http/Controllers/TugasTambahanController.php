<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Penugasan\Models\TugasTambahan;

class TugasTambahanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TugasTambahan::with(['pegawai', 'pemberiTugas', 'validator', 'attachedFiles'])
            ->orderBy('tanggal_mulai', 'desc');

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan hasil validasi
        if ($request->filled('hasil_validasi')) {
            $query->where('hasil_validasi', $request->hasil_validasi);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_tugas', 'like', "%{$request->search}%")
                    ->orWhere('deskripsi', 'like', "%{$request->search}%");
            });
        }

        $tugasTambahan = $query->paginate($request->get('per_page', 15));

        return view('penugasan::tugas-tambahan.index', compact('tugasTambahan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $tugasTambahan = TugasTambahan::with(['pegawai', 'pemberiTugas', 'validator', 'attachedFiles'])
                ->findOrFail($id);

            // Return JSON response for AJAX request
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json($tugasTambahan);
            }

            return view('penugasan::tugas-tambahan.edit', compact('tugasTambahan'));
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tugas tambahan tidak ditemukan'
                ], 404);
            }
            return redirect()->back()->with('error', 'Tugas tambahan tidak ditemukan');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nama_tugas' => 'required|string|max:500',
                'deskripsi' => 'nullable|string',
                'alasan_penugasan' => 'nullable|string',
                'tanggal_mulai' => 'required|date',
                'deadline' => 'required|date|after_or_equal:tanggal_mulai',
                'target_penilaian' => 'nullable|numeric|min:0|max:100',
            ]);

            $tugasTambahan = TugasTambahan::findOrFail($id);
            $tugasTambahan->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Tugas tambahan berhasil diperbarui'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $tugasTambahan = TugasTambahan::findOrFail($id);

            // Check if user has permission to delete
            $currentUserId = Auth::id();
            if ($tugasTambahan->pemberi_tugas_id !== $currentUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menghapus tugas ini'
                ], 403);
            }

            $tugasTambahan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tugas tambahan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update status tugas tambahan
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,dikerjakan,validasi,revisi,selesai'
        ]);

        $tugasTambahan = TugasTambahan::findOrFail($id);
        $tugasTambahan->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Status tugas tambahan berhasil diperbarui'
        ]);
    }

    /**
     * Update progress tugas tambahan
     */
    public function updateProgress(Request $request, $id)
    {
        $validated = $request->validate([
            'progress_persen' => 'required|numeric|min:0|max:100',
            'deskripsi_kegiatan' => 'required|string',
            'kendala' => 'nullable|string',
        ]);

        $tugasTambahan = TugasTambahan::findOrFail($id);

        // Create progress record dengan polymorphic
        \Modules\Penugasan\Models\Progress::create([
            'tipe_progress' => TugasTambahan::class,
            'tipe_progress_id' => $tugasTambahan->id,
            'pegawai_id' => $tugasTambahan->pegawai_id,
            'tanggal' => now(),
            'progress_persen' => $validated['progress_persen'],
            'deskripsi_kegiatan' => $validated['deskripsi_kegiatan'],
            'kendala' => $validated['kendala'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Progress tugas berhasil diperbarui'
        ]);
    }
}
