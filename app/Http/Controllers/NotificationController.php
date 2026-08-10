<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Penugasan\Services\NotifikasiService;

class NotificationController extends Controller
{
    /**
     * Menampilkan seluruh notifikasi milik pengguna login. Notifikasi dihitung
     * langsung dari kondisi data terkini (lihat NotifikasiService) — bukan
     * catatan tersimpan, jadi tidak ada status "sudah dibaca" untuk saat ini.
     */
    public function index(Request $request, NotifikasiService $notifikasiService): View
    {
        $notifikasi = $notifikasiService->untuk($request->user());

        return view('notifications.index', compact('notifikasi'));
    }
}
