<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreSubBidangRequest;
use App\Http\Requests\Master\UpdateSubBidangRequest;
use App\Models\MasterBidang;
use App\Models\MasterSubBidang;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MasterSubBidangController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): View|JsonResponse
    {
        try {
            $this->authorize('viewAny', MasterSubBidang::class);

            $data = MasterSubBidang::with('bidang')->get();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json($data);
            }

            return view('master-data.index-sub-bidang', compact('data'));
        } catch (AuthorizationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'Unauthorized');
        }
    }

    public function create(): View
    {
        $bidangList = MasterBidang::orderBy('nama')->get();

        return view('master-data.create-sub-bidang', compact('bidangList'));
    }

    public function store(StoreSubBidangRequest $request): RedirectResponse
    {
        $this->authorize('create', MasterSubBidang::class);

        $subBidang = MasterSubBidang::create($request->validated());

        return redirect()->route('master.sub-bidang.index')->with('success', 'Sub bidang berhasil ditambah');
    }

    public function show(int $id): View|RedirectResponse
    {
        try {
            $subBidang = MasterSubBidang::with('bidang')->findOrFail($id);
            $bidangList = MasterBidang::orderBy('nama')->get();

            return view('master-data.show-edit-sub-bidang', compact('subBidang', 'bidangList'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('master.sub-bidang.index')->with('error', 'Sub bidang tidak ditemukan');
        }
    }

    public function edit(int $id): View|RedirectResponse
    {
        return $this->show($id);
    }

    public function update(UpdateSubBidangRequest $request, int $id): RedirectResponse
    {
        try {
            $subBidang = MasterSubBidang::findOrFail($id);

            $this->authorize('update', $subBidang);

            $subBidang->update($request->validated());

            return redirect()->route('master.sub-bidang.show', $subBidang->id)->with('success', 'Sub bidang berhasil diperbarui');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('master.sub-bidang.index')->with('error', 'Sub bidang tidak ditemukan');
        }
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        try {
            $subBidang = MasterSubBidang::findOrFail($id);

            $this->authorize('delete', $subBidang);

            $subBidang->delete();

            return redirect()->route('master.sub-bidang.index')->with('success', 'Sub bidang berhasil dihapus');
        } catch (AuthorizationException $e) {
            abort(403, 'Unauthorized');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('master.sub-bidang.index')->with('error', 'Sub bidang tidak ditemukan');
        }
    }
}
