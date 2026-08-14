<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Mockery;
use Tests\TestCase;
use Webklex\PHPIMAP\Client;
use Webklex\PHPIMAP\ClientManager;
use Webklex\PHPIMAP\Folder;

class SimpanEmailTerkirimKeSentFolderTest extends TestCase
{
    public function test_tidak_menghubungi_imap_kalau_belum_dikonfigurasi(): void
    {
        Config::set('imap.accounts.default.host', null);

        $manager = Mockery::mock(ClientManager::class);
        $manager->shouldNotReceive('account');
        $this->app->instance(ClientManager::class, $manager);

        Mail::raw('Isi email uji', function ($message) {
            $message->to('pegawai@example.com')->subject('Uji Coba');
        });
    }

    public function test_menyimpan_salinan_email_ke_folder_sent_saat_imap_dikonfigurasi(): void
    {
        Config::set('imap.accounts.default.host', 'imap.example.com');
        Config::set('imap.accounts.default.username', 'terdepan@example.com');
        Config::set('imap.sent_folder', 'Sent');

        $folder = Mockery::mock(Folder::class);
        $folder->shouldReceive('appendMessage')
            ->once()
            ->with(Mockery::on(fn ($raw) => str_contains($raw, 'Uji Coba')), ['\\Seen']);

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('connect')->once()->andReturnSelf();
        $client->shouldReceive('getFolder')->once()->with('Sent')->andReturn($folder);

        $manager = Mockery::mock(ClientManager::class);
        $manager->shouldReceive('account')->once()->with('default')->andReturn($client);
        $this->app->instance(ClientManager::class, $manager);

        Mail::raw('Isi email uji', function ($message) {
            $message->to('pegawai@example.com')->subject('Uji Coba');
        });
    }

    public function test_kegagalan_imap_tidak_melempar_exception(): void
    {
        Config::set('imap.accounts.default.host', 'imap.example.com');
        Config::set('imap.accounts.default.username', 'terdepan@example.com');

        $manager = Mockery::mock(ClientManager::class);
        $manager->shouldReceive('account')->once()->andThrow(new \RuntimeException('Koneksi IMAP gagal'));
        $this->app->instance(ClientManager::class, $manager);

        Mail::raw('Isi email uji', function ($message) {
            $message->to('pegawai@example.com')->subject('Uji Coba');
        });

        $this->assertTrue(true);
    }
}
