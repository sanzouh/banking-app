<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{

    use RefreshDatabase;
    
    public function test_user_can_register(): void
    {
        // Simuler une requête POST
        $response = $this->postJson('/api/register', [
            'name'     => 'John Doe',
            'email'    => 'john@test.com',
            'password' => 'password123'
        ]);
        // Vérifier la structure JSON retournée
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'status',
                     'message',
                     'data' => ['token', 'name', 'email']
                    ]);
    }

    public function test_register_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'john@test.com']);
        
        // Simuler une requête POST avec un email déjà existant
        $response = $this->postJson('/api/register', [
            'name'     => 'John Doe',
            'email'    => 'john@test.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(422);
    }

    // Login valide
    public function test_user_can_login(): void
    {
        User::factory()->create([
            'email'    => 'john@test.com',
            'password' => bcrypt('password123'),
            'role'     => 'User'
        ]);

        $response = $this->postJson('/api/login', [
            'email'    => 'john@test.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'status',
                     'data' => ['token', 'name', 'email', 'role']
                 ]);
    }

    // Login mauvais credentials
    public function test_login_fails_with_wrong_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email'    => 'nobody@test.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401);
    }

    // Logout normal
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create(['role' => 'User']);
        $token = $user->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                         ->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJson(['status' => 1]);
    }

    // Accès sans token
    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/clients');
        $response->assertStatus(401);
    }
}
