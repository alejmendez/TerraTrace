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
        // The home route redirects to /login for guests (Breeze). The
        // default Laravel example test expects a 200, which assumes a
        // public landing page — TerraTrace doesn't have one.
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
