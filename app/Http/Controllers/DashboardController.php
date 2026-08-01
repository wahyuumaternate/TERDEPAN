<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Penugasan\Http\Controllers\DashboardController as PenugasanDashboardController;
use Modules\TerminalData\Http\Controllers\TerminalDataController;

class DashboardController extends Controller
{
    public function __construct(
        protected PenugasanDashboardController $penugasanDashboard,
        protected TerminalDataController $terminalDataController,
    ) {}

    /**
     * Menampilkan dashboard gabungan (statistik Penugasan + Terminal Data).
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $penugasanData = $this->penugasanDashboard->getDashboardData($user);
        $tdStats = $this->terminalDataController->getDashboardStats($user);

        return view('dashboard', array_merge($penugasanData, [
            'tdStats' => $tdStats,
        ]));
    }
}
