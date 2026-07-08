<?php

namespace Tests\Feature;

use App\Models\Club;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_users_are_redirected_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_home(): void
    {
        $user = User::factory()->create();
        $club = Club::create(['name' => 'Test Club', 'slug' => 'test-club']);
        $club->users()->attach($user->id, ['role' => 'member']);

        $response = $this->actingAs($user)->get('/');

        // With only one club, redirects to the club league page
        $response->assertRedirect(route('club.league', $club));
    }
}
