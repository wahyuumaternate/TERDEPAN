<?php

namespace Tests\Feature\TerminalData;

use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FolderIndexPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        MasterJabatan::insert([
            ['id' => 1, 'kode' => 'ADMIN', 'nama' => 'Administrator', 'level' => 0],
        ]);

        MasterBidang::create(['kode' => 'APPS', 'nama' => 'Bidang Aplikasi dan Statistik']);
    }

    public function test_folder_index_page_renders_with_stats_cards(): void
    {
        $user = $this->createUserWithJabatan('ADMIN');

        $response = $this->actingAs($user)->get(route('terminaldata.folders.index'));

        $response->assertOk();
        $response->assertSee('id="totalFolder"', false);
        $response->assertSee('id="totalRootFolder"', false);
        $response->assertSee('id="totalAutoFolder"', false);
        $response->assertSee('id="totalFiles"', false);
    }

    protected function createUserWithJabatan(string $kodeJabatan): User
    {
        $jabatan = MasterJabatan::where('kode', $kodeJabatan)->firstOrFail();
        $nip = '1990'.str_pad((string) rand(1, 999999), 6, '0', STR_PAD_LEFT).'001';

        $user = User::create([
            'nama' => 'User '.$kodeJabatan,
            'email' => strtolower($kodeJabatan).rand(1, 9999).'@test.com',
            'password' => bcrypt('password'),
            'must_change_password' => false,
        ]);

        $user->profile()->create([
            'nomor_identitas' => $nip,
            'tipe_identitas' => 'NIP',
            'jenis_kelamin' => 'L',
            'status_kepegawaian' => 'PNS',
            'status_aktif' => 'Aktif',
            'jabatan_id' => $jabatan->id,
            'bidang_id' => MasterBidang::first()->id,
        ]);

        return $user->fresh('profile');
    }
}
