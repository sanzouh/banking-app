<?php
namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WithdrawalAuditTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function actingAsUser(): array
    {
        $user  = User::factory()->create(['role' => 'User']);
        $token = $user->createToken('user_token')->plainTextToken;
        return ['user' => $user, 'token' => $token];
    }

    private function actingAsAdmin(): array
    {
        $user = User::factory()->admin()->create();
        $token = $user->createToken('admin_token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    private function authHeader(string $token): static
    {
        return $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ]);
    }

    private function resetAuthContext(): void
    {
        Auth::forgetGuards();
        $this->flushHeaders();
    }

    // Crée un withdrawal complet via l'API pour déclencher les triggers
    private function createWithdrawal(User $user, string $token, Client $client, array $override = []): \Illuminate\Testing\TestResponse
    {
        return $this->authHeader($token)->postJson('/api/withdrawals', array_merge([
            'withdraw_num' => fake()->unique()->numerify('#####'),
            'check_num'    => fake()->unique()->numerify('#####'),
            'account_num'  => $client->account_num,
            'amount'       => 1000.00,
        ], $override));
    }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function test_can_list_audits(): void
    {
        ['user' => $user, 'token' => $token] = $this->actingAsUser();
        $client = Client::factory()->create(['balance' => 50000]);

        $this->createWithdrawal($user, $token, $client);
        // reset explicite du contexte d’auth dans WithdrawalAuditTest.php
        $this->resetAuthContext();

        ['token' => $adminToken] = $this->actingAsAdmin();

        $response = $this->authHeader($adminToken)->getJson('/api/withdrawals-audit');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => [
                         'data' => [
                             '*' => ['action_type', 'withdraw_num', 'user']
                         ],
                         'current_page',
                         'per_page',
                         'total',
                     ]
                 ]);
    }

    public function test_audits_list_is_paginated(): void
    {
        ['user' => $user, 'token' => $token] = $this->actingAsUser();
        $client = Client::factory()->create(['balance' => 999999]);

        // 20 inserts → 20 audits
        for ($i = 0; $i < 20; $i++) {
            $response = $this->createWithdrawal($user, $token, $client, [
                'withdraw_num' => 10000 + $i,
                'check_num'    => 20000 + $i,
            ]);

            $response->assertStatus(201);
            $this->resetAuthContext();
        }

        ['token' => $adminToken] = $this->actingAsAdmin();

        $response = $this->authHeader($adminToken)->getJson('/api/withdrawals-audit');

        $response->assertStatus(200)
                 ->assertJsonPath('data.per_page', 15)
                 ->assertJsonPath('data.total', 20);
    }

    // =========================================================================
    // TRIGGER INSERT → audit
    // =========================================================================

    public function test_insert_trigger_creates_audit_entry(): void
    {
        ['user' => $user, 'token' => $token] = $this->actingAsUser();
        $client = Client::factory()->create(['balance' => 50000]);

        $response = $this->createWithdrawal($user, $token, $client, [
            'withdraw_num' => 11111,
            'check_num'    => 22222,
            'amount'       => 2000.00,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('withdrawals', [
            'withdraw_num' => 11111,
            'account_num'  => $client->account_num,
            'amount'       => 2000.00,
            'user_id'      => $user->id_user,
        ]);

        $this->assertDatabaseHas('withdrawals_audit', [
            'action_type'  => 'INSERT',
            'withdraw_num' => 11111,
            'account_num'  => $client->account_num,
            'client_name'  => $client->name,
            'old_amount'   => null,
            'new_amount'   => 2000.00,
            'user'         => $user->name,
        ]);
    }


    public function test_insert_trigger_decreases_client_balance(): void
    {
        ['user' => $user, 'token' => $token] = $this->actingAsUser();
        $client = Client::factory()->create(['balance' => 10000]);

        $response = $this->createWithdrawal($user, $token, $client, [
            'withdraw_num' => 11112,
            'check_num'    => 22223,
            'amount'       => 3000.00,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('clients', [
            'account_num' => $client->account_num,
            'balance'     => 7000.00,
        ]);
    }

    // =========================================================================
    // TRIGGER UPDATE → audit
    // =========================================================================

    public function test_update_trigger_creates_audit_entry(): void
    {
        ['user' => $user, 'token' => $token] = $this->actingAsUser();
        $client = Client::factory()->create(['balance' => 50000]);

        $createResponse = $this->createWithdrawal($user, $token, $client, [
            'withdraw_num' => 22222,
            'check_num'    => 32222,
            'amount'       => 1000.00,
        ]);
        $createResponse->assertStatus(201);

        $updateResponse = $this->authHeader($token)->putJson('/api/withdrawals/22222', [
            'amount' => 4000.00,
        ]);
        $updateResponse->assertStatus(200);

        $this->assertDatabaseHas('withdrawals_audit', [
            'action_type'  => 'UPDATE',
            'withdraw_num' => 22222,
            'old_amount'   => 1000.00,
            'new_amount'   => 4000.00,
            'user'         => $user->name,
        ]);
    }

    public function test_update_trigger_adjusts_client_balance(): void
    {
        ['user' => $user, 'token' => $token] = $this->actingAsUser();
        // balance initiale 10000, retrait 1000 → 9000, update à 4000 → 6000
        $client = Client::factory()->create(['balance' => 10000]);

        $createResponse = $this->createWithdrawal($user, $token, $client, [
            'withdraw_num' => 33333,
            'check_num'    => 43333,
            'amount'       => 1000.00,
        ]);
        $createResponse->assertStatus(201);

        $updateResponse = $this->authHeader($token)->putJson('/api/withdrawals/33333', [
            'amount' => 4000.00,
        ]);
        $updateResponse->assertStatus(200);

        $this->assertDatabaseHas('clients', [
            'account_num' => $client->account_num,
            'balance'     => 6000.00,
        ]);
    }

    // =========================================================================
    // TRIGGER DELETE → audit
    // =========================================================================

    public function test_delete_trigger_creates_audit_entry(): void
    {
        ['user' => $user, 'token' => $token] = $this->actingAsUser();
        $client = Client::factory()->create(['balance' => 50000]);

        $createResponse = $this->createWithdrawal($user, $token, $client, [
            'withdraw_num' => 44444,
            'check_num'    => 54444,
            'amount'       => 2000.00,
        ]);
        $createResponse->assertStatus(201);

        $deleteResponse = $this->authHeader($token)->deleteJson('/api/withdrawals/44444');
        $deleteResponse->assertStatus(200);

        $this->assertDatabaseHas('withdrawals_audit', [
            'action_type'  => 'DELETE',
            'withdraw_num' => 44444,
            'old_amount'   => 2000.00,
            'new_amount'   => null,
            'user'         => $user->name,
        ]);
    }

    public function test_delete_trigger_restores_client_balance(): void
    {
        ['user' => $user, 'token' => $token] = $this->actingAsUser();
        $client = Client::factory()->create(['balance' => 10000]);

        $createResponse = $this->createWithdrawal($user, $token, $client, [
            'withdraw_num' => 55555,
            'check_num'    => 65555,
            'amount'       => 3000.00,
        ]);
        $createResponse->assertStatus(201);

        // balance après insert : 7000
        $deleteResponse = $this->authHeader($token)->deleteJson('/api/withdrawals/55555');
        $deleteResponse->assertStatus(200);

        // balance après delete : 7000 + 3000 = 10000
        $this->assertDatabaseHas('clients', [
            'account_num' => $client->account_num,
            'balance'     => 10000.00,
        ]);
    }

    // =========================================================================
    // STATS
    // =========================================================================

    public function test_stats_returns_correct_counts(): void
    {
        ['user' => $user, 'token' => $token] = $this->actingAsUser();
        $client = Client::factory()->create(['balance' => 999999]);

        // User crée les withdrawals
        $firstResponse = $this->createWithdrawal($user, $token, $client, [
            'withdraw_num' => 30001,
            'check_num'    => 40001,
            'amount'       => 1000,
        ]);
        $firstResponse->assertStatus(201);

        $secondResponse = $this->createWithdrawal($user, $token, $client, [
            'withdraw_num' => 30002,
            'check_num'    => 40002,
            'amount'       => 2000,
        ]);
        $secondResponse->assertStatus(201);

        $this->assertDatabaseCount('withdrawals_audit', 2);

        $this->resetAuthContext();

        // ← Admin consulte les stats
        ['token' => $adminToken] = $this->actingAsAdmin();
        $response = $this->authHeader($adminToken)->getJson('/api/withdrawals-audit/stats');

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 1,
                     'data'   => [
                         'inserts' => 2,
                     ]
                 ]);
    }

    public function test_stats_returns_zeros_when_no_withdrawals(): void
    {
        ['token' => $adminToken] = $this->actingAsAdmin();
        $response = $this->authHeader($adminToken)->getJson('/api/withdrawals-audit/stats');

        $response->assertStatus(200)
                 ->assertJson([
                     'data' => ['inserts' => 0, 'updates' => 0, 'deletes' => 0]
                 ]);
    }
}
