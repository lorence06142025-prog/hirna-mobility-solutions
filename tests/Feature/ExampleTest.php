<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Unauthenticated access to protected '/' route correctly redirects to '/login' (302)
        $response = $this->get('/');
        $response->assertStatus(302);

        // Public '/login' page returns 200 OK
        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
    }
}
