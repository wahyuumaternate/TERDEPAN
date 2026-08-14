<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;
use Webklex\PHPIMAP\ClientManager;

/**
 * SMTP relay tidak otomatis menulis salinan email yang dikirim ke folder "Sent" mailbox —
 * itu kenapa email yang sukses terkirim (mis. lewat MasterPegawaiController::kirimEmailLogin())
 * tidak pernah muncul kalau mailbox dicek lewat webmail. Listener ini menambahkan salinan
 * mentah pesan yang baru dikirim ke folder Sent lewat IMAP APPEND, supaya konsisten dengan
 * yang terlihat di webmail. Didaftarkan global (bukan cuma di alur kirim-email-login) karena
 * saat ini email itu satu-satunya email yang dikirim aplikasi — otomatis ikut mencakup email
 * lain kalau ditambahkan nanti.
 */
class SimpanEmailTerkirimKeSentFolder
{
    public function handle(MessageSent $event): void
    {
        if (! config('imap.accounts.default.host') || ! config('imap.accounts.default.username')) {
            return;
        }

        try {
            $client = app(ClientManager::class)->account('default');
            $client->connect();

            $folder = $client->getFolder(config('imap.sent_folder', 'Sent'));

            if (! $folder) {
                Log::warning('Folder Sent IMAP tidak ditemukan, salinan email tidak disimpan.');

                return;
            }

            $folder->appendMessage($event->message->toString(), ['\\Seen']);
        } catch (\Throwable $e) {
            // Kegagalan simpan ke folder Sent tidak boleh menggagalkan proses pengiriman email
            // yang sudah sukses lewat SMTP — cukup dicatat di log.
            Log::warning('Gagal menyimpan salinan email ke folder Sent IMAP: '.$e->getMessage());
        }
    }
}
