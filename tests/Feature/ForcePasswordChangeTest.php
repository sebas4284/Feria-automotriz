<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_must_change_password_is_redirected_to_profile(): void
    {
        $user = User::factory()->create(['rol' => 'admin', 'must_change_password' => true]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('profile.edit'));
    }

    public function test_user_without_must_change_password_is_not_redirected(): void
    {
        $user = User::factory()->create(['rol' => 'admin', 'must_change_password' => false]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_user_forced_to_change_password_can_still_reach_profile_and_logout(): void
    {
        $user = User::factory()->create(['rol' => 'admin', 'must_change_password' => true]);

        $this->actingAs($user)->get('/profile')->assertOk();
        $this->actingAs($user)->post('/logout')->assertRedirect('/');
    }

    public function test_updating_password_clears_the_must_change_password_flag(): void
    {
        $user = User::factory()->create(['rol' => 'admin', 'must_change_password' => true, 'password' => bcrypt('old-password-123')]);

        $this->actingAs($user)->put('/password', [
            'current_password' => 'old-password-123',
            'password' => 'nueva-password-123',
            'password_confirmation' => 'nueva-password-123',
        ])->assertRedirect();

        $this->assertFalse($user->fresh()->must_change_password);
    }

    public function test_after_changing_password_user_can_navigate_normally(): void
    {
        $user = User::factory()->create(['rol' => 'admin', 'must_change_password' => true, 'password' => bcrypt('old-password-123')]);

        $this->actingAs($user)->put('/password', [
            'current_password' => 'old-password-123',
            'password' => 'nueva-password-123',
            'password_confirmation' => 'nueva-password-123',
        ]);

        $this->actingAs($user->fresh())
            ->get('/dashboard')
            ->assertOk();
    }
}
