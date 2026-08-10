<?php

namespace Modules\Penugasan\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_bisa_subscribe(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('penugasan.api.push-subscription.store'), [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
            'keys' => ['p256dh' => 'public-key', 'auth' => 'auth-token'],
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('push_subscriptions', [
            'subscribable_id' => $user->id,
            'subscribable_type' => $user->getMorphClass(),
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
        ]);
    }

    public function test_user_bisa_unsubscribe(): void
    {
        $user = User::factory()->create();
        $user->updatePushSubscription('https://fcm.googleapis.com/fcm/send/abc123', 'public-key', 'auth-token');

        $response = $this->actingAs($user)->delete(route('penugasan.api.push-subscription.destroy'), [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseMissing('push_subscriptions', [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
        ]);
    }

    public function test_subscribe_butuh_login(): void
    {
        $response = $this->post(route('penugasan.api.push-subscription.store'), [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/abc123',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_endpoint_lain_tidak_ikut_terhapus(): void
    {
        $user = User::factory()->create();
        $user->updatePushSubscription('https://fcm.googleapis.com/fcm/send/milik-sendiri', 'k', 'a');
        $user->updatePushSubscription('https://fcm.googleapis.com/fcm/send/lainnya', 'k', 'a');

        $this->actingAs($user)->delete(route('penugasan.api.push-subscription.destroy'), [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/milik-sendiri',
        ])->assertSuccessful();

        $this->assertDatabaseMissing('push_subscriptions', ['endpoint' => 'https://fcm.googleapis.com/fcm/send/milik-sendiri']);
        $this->assertDatabaseHas('push_subscriptions', ['endpoint' => 'https://fcm.googleapis.com/fcm/send/lainnya']);
    }
}
