<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PennantFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_can_be_rendered_for_authenticated_user(): void
    {
        $user = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Pennant Blade Directives Playground');
    }

    public function test_features_management_page_can_be_rendered_for_admin(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->get('/features');

        $response->assertStatus(200);
        $response->assertSee('Feature Flag Management Suite');
    }

    public function test_non_admin_cannot_access_features_management(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $response = $this->actingAs($user)->get('/features');

        $response->assertStatus(403);
    }

    public function test_admin_can_toggle_kill_switch(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->post('/features/kill-switch', [
            'feature_name' => 'new_reports',
        ]);

        $response->assertRedirect('/features');
        $this->assertDatabaseHas('feature_rollouts', [
            'feature_name' => 'new_reports',
            'is_killed' => true,
        ]);
        $this->assertDatabaseHas('feature_audits', [
            'feature' => 'new_reports',
        ]);
    }

    public function test_admin_can_set_rollout_percentage(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->post('/features/set-percentage', [
            'feature_name' => 'new_dashboard',
            'percentage' => 75,
        ]);

        $response->assertRedirect('/features');
        $this->assertDatabaseHas('feature_rollouts', [
            'feature_name' => 'new_dashboard',
            'percentage' => 75,
        ]);
    }

    public function test_admin_can_panic_kill_all(): void
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->post('/features/panic-kill-all');

        $response->assertRedirect('/features');
        $this->assertDatabaseHas('feature_rollouts', [
            'feature_name' => 'new_dashboard',
            'is_killed' => true,
        ]);
    }
}
