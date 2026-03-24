<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase; // This trait will refresh the database, relaunching the migrations before each test.
    
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_not_found_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(404);
    }
}
