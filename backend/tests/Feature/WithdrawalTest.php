<?php
namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WithdrawalTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function actingAsUser(): array
    {
        $user  = User::factory()->create(['role' => 'User']);
        $token = $user->createToken('test_token')->plainTextToken;
        return ['user' => $user, 'token' => $token];
    }

    private function authHeader(string $token): self
    {
        return $this->withHeader('Authorization', 'Bearer ' . $token);
    }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function test_can_list_withdrawals(): void
    {
        ['token' => $token] = $this->actingAsUser();
        Withdrawal::factory()->count(3)->create();

        $response = $this->authHeader($token)->getJson('/api/withdrawals');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [['withdraw_num', 'check_num', 'account_num', 'amount', 'user_id']]
                 ])
                 ->assertJson(['status' => 1]);
    }

    // =========================================================================
    // STORE
    // =========================================================================

    public function test_can_create_withdrawal(): void
    {
        ['user' => $user, 'token' => $token] = $this->actingAsUser();
        $client = Client::factory()->create();

        $payload = [
            'withdraw_num' => 11111,
            'check_num'    => 22222,
            'account_num'  => $client->account_num,
            'amount'       => 5000.00,
        ];

        $response = $this->authHeader($token)->postJson('/api/withdrawals', $payload);

        $response->assertStatus(201)
                 ->assertJson([
                     'status' => 1,
                     'data'   => [
                         'withdraw_num' => 11111,
                         'check_num'    => 22222,
                         'account_num'  => $client->account_num,
                         'user_id'      => $user->id_user,
                     ]
                 ]);

        $this->assertDatabaseHas('withdrawals', ['withdraw_num' => 11111]);
    }

    public function test_store_fails_with_duplicate_withdraw_num(): void
    {
        ['token' => $token] = $this->actingAsUser();
        $existing = Withdrawal::factory()->create();
        $client   = Client::factory()->create();

        $response = $this->authHeader($token)->postJson('/api/withdrawals', [
            'withdraw_num' => $existing->withdraw_num,
            'check_num'    => 33333,
            'account_num'  => $client->account_num,
            'amount'       => 1000,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['withdraw_num']);
    }

    public function test_store_fails_with_duplicate_check_num(): void
    {
        ['token' => $token] = $this->actingAsUser();
        $existing = Withdrawal::factory()->create();
        $client   = Client::factory()->create();

        $response = $this->authHeader($token)->postJson('/api/withdrawals', [
            'withdraw_num' => 44444,
            'check_num'    => $existing->check_num,
            'account_num'  => $client->account_num,
            'amount'       => 1000,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['check_num']);
    }

    public function test_store_fails_with_nonexistent_account_num(): void
    {
        ['token' => $token] = $this->actingAsUser();

        $response = $this->authHeader($token)->postJson('/api/withdrawals', [
            'withdraw_num' => 55555,
            'check_num'    => 66666,
            'account_num'  => 99999, // n'existe pas
            'amount'       => 1000,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['account_num']);
    }

    public function test_store_fails_when_amount_is_negative(): void
    {
        ['token' => $token] = $this->actingAsUser();
        $client = Client::factory()->create();

        $response = $this->authHeader($token)->postJson('/api/withdrawals', [
            'withdraw_num' => 77777,
            'check_num'    => 88888,
            'account_num'  => $client->account_num,
            'amount'       => -500,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['amount']);
    }

    public function test_store_fails_when_required_fields_missing(): void
    {
        ['token' => $token] = $this->actingAsUser();

        $response = $this->authHeader($token)->postJson('/api/withdrawals', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['withdraw_num', 'check_num', 'account_num', 'amount']);
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    public function test_can_show_withdrawal(): void
    {
        ['token' => $token] = $this->actingAsUser();
        $withdrawal = Withdrawal::factory()->create();

        $response = $this->authHeader($token)->getJson("/api/withdrawals/{$withdrawal->withdraw_num}");

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 1,
                     'data'   => ['withdraw_num' => $withdrawal->withdraw_num],
                 ]);
    }

    public function test_show_returns_404_for_nonexistent_withdrawal(): void
    {
        ['token' => $token] = $this->actingAsUser();

        $response = $this->authHeader($token)->getJson('/api/withdrawals/99999');

        $response->assertStatus(404)
                 ->assertJson(['status' => 0]);
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function test_can_update_withdrawal_amount(): void
    {
        ['token' => $token] = $this->actingAsUser();
        $withdrawal = Withdrawal::factory()->create();

        $response = $this->authHeader($token)->putJson("/api/withdrawals/{$withdrawal->withdraw_num}", [
            'amount' => 9999.99,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => 1]);

        $this->assertDatabaseHas('withdrawals', [
            'withdraw_num' => $withdrawal->withdraw_num,
            'amount'       => 9999.99,
        ]);
    }

    public function test_can_update_withdrawal_check_num(): void
    {
        ['token' => $token] = $this->actingAsUser();
        $withdrawal = Withdrawal::factory()->create();

        $response = $this->authHeader($token)->putJson("/api/withdrawals/{$withdrawal->withdraw_num}", [
            'check_num' => 12121,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => 1]);

        $this->assertDatabaseHas('withdrawals', ['check_num' => 12121]);
    }

    public function test_update_fails_with_duplicate_check_num(): void
    {
        ['token' => $token] = $this->actingAsUser();
        $w1 = Withdrawal::factory()->create();
        $w2 = Withdrawal::factory()->create();

        // Tente de mettre le check_num de w1 sur w2
        $response = $this->authHeader($token)->putJson("/api/withdrawals/{$w2->withdraw_num}", [
            'check_num' => $w1->check_num,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['check_num']);
    }

    public function test_update_fails_when_amount_is_negative(): void
    {
        ['token' => $token] = $this->actingAsUser();
        $withdrawal = Withdrawal::factory()->create();

        $response = $this->authHeader($token)->putJson("/api/withdrawals/{$withdrawal->withdraw_num}", [
            'amount' => -100,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['amount']);
    }

    public function test_update_returns_404_for_nonexistent_withdrawal(): void
    {
        ['token' => $token] = $this->actingAsUser();

        $response = $this->authHeader($token)->putJson('/api/withdrawals/99999', [
            'amount' => 5000,
        ]);

        $response->assertStatus(404);
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function test_can_delete_withdrawal(): void
    {
        ['token' => $token] = $this->actingAsUser();
        $withdrawal = Withdrawal::factory()->create();

        $response = $this->authHeader($token)->deleteJson("/api/withdrawals/{$withdrawal->withdraw_num}");

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => 1,
                     'message' => 'Retrait supprimé',
                     'data'    => null,
                 ]);

        $this->assertDatabaseMissing('withdrawals', ['withdraw_num' => $withdrawal->withdraw_num]);
    }

    public function test_destroy_returns_404_for_nonexistent_withdrawal(): void
    {
        ['token' => $token] = $this->actingAsUser();

        $response = $this->authHeader($token)->deleteJson('/api/withdrawals/99999');

        $response->assertStatus(404)
                 ->assertJson(['status' => 0]);
    }
}