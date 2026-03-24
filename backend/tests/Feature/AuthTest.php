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
}
