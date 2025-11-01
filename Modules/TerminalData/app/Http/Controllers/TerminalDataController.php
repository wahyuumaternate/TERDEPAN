<?php

namespace Modules\TerminalData\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\TerminalData\Models\TdFolder;

class TerminalDataController extends Controller
{
    public function index()
    {
        return view('terminaldata::index');
    }

    public function folderIndex(Request $request)
    {
        // Jika request AJAX, return JSON
        if ($request->wantsJson() || $request->ajax()) {
            $folders = TdFolder::with(['parent', 'bidang', 'creator'])
                ->orderBy('level')
                ->orderBy('name')
                ->get();

            // Debug: Log bidang data
            // Log::info('Folders with bidang:', $folders->toArray());

            return response()->json($folders);
        }

        // Jika bukan AJAX, return view
        $folders = TdFolder::with(['parent', 'bidang', 'creator'])
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('terminaldata::folder.index', compact('folders'));
    }

    public function evidenIndex()
    {
        //
    }

    public function sampahIndex()
    {
        //
    }
}
