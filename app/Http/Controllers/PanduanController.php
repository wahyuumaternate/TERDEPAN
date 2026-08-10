<?php

namespace App\Http\Controllers;

use App\Http\Requests\Panduan\StorePanduanRequest;
use App\Http\Requests\Panduan\UpdatePanduanRequest;
use App\Models\Panduan;
use App\Services\PanduanService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PanduanController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly PanduanService $panduanService) {}

    public function index(): View
    {
        $this->authorize('viewAny', Panduan::class);

        $panduans = Panduan::orderBy('judul')->get();

        return view('panduan.index', [
            'panduans' => $panduans,
            'isAdmin' => auth()->user()->can('create', Panduan::class),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Panduan::class);

        return view('panduan.create');
    }

    public function store(StorePanduanRequest $request): RedirectResponse
    {
        $this->authorize('create', Panduan::class);

        try {
            $stored = $this->panduanService->store($request->file('file'));

            Panduan::create([
                'judul' => $request->validated('judul'),
                'deskripsi' => $request->validated('deskripsi'),
                'disk' => $stored['disk'],
                'path' => $stored['path'],
                'nama_file' => $stored['nama_file'],
                'mime_type' => $stored['mime_type'],
                'size' => $stored['size'],
                'diunggah_oleh_id' => auth()->id(),
            ]);

            return redirect()->route('panduan.index')->with('success', 'Panduan berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function edit(Panduan $panduan): View
    {
        $this->authorize('update', $panduan);

        return view('panduan.edit', compact('panduan'));
    }

    public function update(UpdatePanduanRequest $request, Panduan $panduan): RedirectResponse
    {
        $this->authorize('update', $panduan);

        try {
            $data = [
                'judul' => $request->validated('judul'),
                'deskripsi' => $request->validated('deskripsi'),
            ];

            if ($request->hasFile('file')) {
                $this->panduanService->deletePhysical($panduan->path, $panduan->disk);
                $stored = $this->panduanService->store($request->file('file'));
                $data = [...$data, ...$stored];
            }

            $panduan->update($data);

            return redirect()->route('panduan.index')->with('success', 'Panduan berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(Panduan $panduan): RedirectResponse
    {
        $this->authorize('delete', $panduan);

        try {
            $panduan->delete();

            return redirect()->route('panduan.index')->with('success', 'Panduan berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function preview(Panduan $panduan): StreamedResponse
    {
        $this->authorize('view', $panduan);

        return $this->panduanService->serveInline($panduan);
    }

    public function download(Panduan $panduan): StreamedResponse
    {
        $this->authorize('view', $panduan);

        return $this->panduanService->download($panduan);
    }
}
