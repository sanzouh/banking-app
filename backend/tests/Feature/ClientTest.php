<?php
namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helper : créer un user authentifié ───────────────────────────────────
    private function actingAsUser(): self
    {
        $user = User::factory()->create(['role' => 'User']);
        $token = $user->createToken('test_token')->plainTextToken;
        return $this->withHeader('Authorization', 'Bearer ' . $token);
    }

    // =========================================================================
    // INDEX
    // =========================================================================

    public function test_can_list_clients(): void
    {
        Client::factory()->count(5)->create();

        $response = $this->actingAsUser()->getJson('/api/clients');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'datas' => [
                         'data' => [['account_num', 'name', 'balance']],
                         'current_page',
                         'per_page',
                         'total',
                     ]
                 ])
                 ->assertJson(['status' => 1]);
    }

    public function test_clients_list_is_paginated(): void
    {
        // Crée plus que la limite de pagination (15)
        Client::factory()->count(20)->create();

        $response = $this->actingAsUser()->getJson('/api/clients');

        $response->assertStatus(200)
                 ->assertJsonPath('datas.per_page', 15)
                 ->assertJsonPath('datas.total', 20)
                 ->assertJsonCount(15, 'datas.data');
    }

    // =========================================================================
    // STORE
    // =========================================================================

    public function test_can_create_client(): void
    {
        $payload = [
            'account_num' => 12345,
            'name'        => 'Rakoto Andry',
            'balance'     => 1500.00,
        ];

        $response = $this->actingAsUser()->postJson('/api/clients', $payload);

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'message', 'data' => ['account_num', 'name', 'balance']])
                 ->assertJson([
                     'status' => 1,
                     'data'   => [
                         'account_num' => 12345,
                         'name'        => 'Rakoto Andry',
                         'balance'     => '1500.00',
                     ]
                 ]);

        $this->assertDatabaseHas('clients', ['account_num' => 12345]);
    }

    public function test_store_fails_with_duplicate_account_num(): void
    {
        Client::factory()->create(['account_num' => 12345]);

        $response = $this->actingAsUser()->postJson('/api/clients', [
            'account_num' => 12345,
            'name'        => 'Autre Client',
            'balance'     => 500.00,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['account_num']);
    }

    public function test_store_fails_when_account_num_missing(): void
    {
        $response = $this->actingAsUser()->postJson('/api/clients', [
            'name'    => 'Rakoto Andry',
            'balance' => 1500.00,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['account_num']);
    }

    public function test_store_fails_when_name_missing(): void
    {
        $response = $this->actingAsUser()->postJson('/api/clients', [
            'account_num' => 12346,
            'balance'     => 1500.00,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    public function test_store_fails_when_balance_is_negative(): void
    {
        $response = $this->actingAsUser()->postJson('/api/clients', [
            'account_num' => 12347,
            'name'        => 'Rakoto Andry',
            'balance'     => -100.00,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['balance']);
    }

    public function test_store_fails_when_name_exceeds_max_length(): void
    {
        $response = $this->actingAsUser()->postJson('/api/clients', [
            'account_num' => 12348,
            'name'        => str_repeat('A', 51), // max:50
            'balance'     => 500.00,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    public function test_can_show_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAsUser()->getJson("/api/clients/{$client->account_num}");

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 1,
                     'data'   => ['account_num' => $client->account_num]
                 ]);
    }

    public function test_show_returns_404_for_nonexistent_client(): void
    {
        $response = $this->actingAsUser()->getJson('/api/clients/99999');

        $response->assertStatus(404)
                 ->assertJson(['status' => 0]);
    }

    // =========================================================================
    // UPDATE
    // =========================================================================

    public function test_can_update_client_name(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAsUser()->putJson("/api/clients/{$client->account_num}", [
            'name' => 'Nouveau Nom',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'status' => 1,
                     'data'   => ['name' => 'Nouveau Nom'],
                 ]);

        $this->assertDatabaseHas('clients', [
            'account_num' => $client->account_num,
            'name'        => 'Nouveau Nom',
        ]);
    }

    public function test_can_update_client_balance(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAsUser()->putJson("/api/clients/{$client->account_num}", [
            'balance' => 9999.99,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => 1]);

        $this->assertDatabaseHas('clients', [
            'account_num' => $client->account_num,
            'balance'     => 9999.99,
        ]);
    }

    public function test_update_fails_when_balance_is_negative(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAsUser()->putJson("/api/clients/{$client->account_num}", [
            'balance' => -50.00,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['balance']);
    }

    public function test_update_fails_when_name_exceeds_max_length(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAsUser()->putJson("/api/clients/{$client->account_num}", [
            'name' => str_repeat('B', 51),
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }

    public function test_update_returns_404_for_nonexistent_client(): void
    {
        $response = $this->actingAsUser()->putJson('/api/clients/99999', [
            'name' => 'Fantôme',
        ]);

        $response->assertStatus(404);
    }

    // =========================================================================
    // DESTROY
    // =========================================================================

    public function test_can_delete_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->actingAsUser()->deleteJson("/api/clients/{$client->account_num}");

        $response->assertStatus(200)
                 ->assertJson([
                     'status'  => 1,
                     'message' => 'Client supprimé',
                     'data'    => null,
                 ]);

        $this->assertDatabaseMissing('clients', ['account_num' => $client->account_num]);
    }

    public function test_destroy_returns_404_for_nonexistent_client(): void
    {
        $response = $this->actingAsUser()->deleteJson('/api/clients/99999');

        $response->assertStatus(404)
                 ->assertJson(['status' => 0]);
    }
}