<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard.
     *
     * Sengaja disederhanakan jadi info minimal untuk sementara — statistik
     * gabungan Penugasan/Terminal Data akan dirancang ulang & diaktifkan lagi
     * belakangan sesuai kebutuhan, bukan dihapus permanen (lihat
     * Modules\Penugasan\Http\Controllers\DashboardController::getDashboardData()
     * dan Modules\TerminalData\Http\Controllers\TerminalDataController::getDashboardStats()
     * yang tetap tersedia untuk dipakai lagi nanti).
     */
    public function index(Request $request): View
    {
        return view('dashboard', ['user' => $request->user()]);
    }
}
