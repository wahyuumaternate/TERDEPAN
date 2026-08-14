<?php

namespace App\Http\Controllers\Testing;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

/**
 * Halaman internal untuk uji coba pengiriman WhatsApp lewat Twilio sebelum fitur
 * notifikasi WhatsApp sungguhan dibangun. Hanya ADMIN yang bisa akses (lihat guard
 * di method index()) karena tiap pengiriman lewat Twilio berbayar.
 */
class WhatsappTestController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->profile?->jabatan?->kode === 'ADMIN', 403);

        $pegawai = User::whereHas('profile', function ($query) {
            $query->whereNotNull('no_telepon');
        })->with('profile')->orderBy('nama')->get();

        return view('testing.whatsapp', compact('pegawai'));
    }

    public function send(Request $request): RedirectResponse
    {
        abort_unless($request->user()->profile?->jabatan?->kode === 'ADMIN', 403);

        $validated = $request->validate([
            'nomor_tujuan' => 'required|string|max:15|min:9',
            'pesan' => 'required|string|max:1000',
        ]);

        try {
            $sid = config('services.twilio.sid');
            $authToken = config('services.twilio.auth_token');
            $from = config('services.twilio.whatsapp_from');

            if (! $sid || ! $authToken || ! $from) {
                throw new \RuntimeException('Konfigurasi Twilio (TWILIO_SID/TWILIO_AUTH_TOKEN/TWILIO_WHATSAPP_FROM) belum diisi di .env');
            }

            $client = new Client($sid, $authToken);

            $client->messages->create(
                'whatsapp:'.$this->normalisasiNomor($validated['nomor_tujuan']),
                [
                    'from' => 'whatsapp:'.$from,
                    'body' => $validated['pesan'],
                ]
            );

            return redirect()->back()->with('success', 'Pesan WhatsApp berhasil dikirim');
        } catch (TwilioException|\RuntimeException $e) {
            return redirect()->back()->with('error', 'Gagal mengirim: '.$e->getMessage())->withInput();
        }
    }

    private function normalisasiNomor(string $nomor): string
    {
        $nomor = preg_replace('/[^0-9+]/', '', $nomor);

        if (str_starts_with($nomor, '0')) {
            $nomor = '+62'.substr($nomor, 1);
        } elseif (! str_starts_with($nomor, '+')) {
            $nomor = '+'.$nomor;
        }

        return $nomor;
    }
}
