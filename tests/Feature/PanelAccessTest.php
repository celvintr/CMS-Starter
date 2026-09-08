<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads(): void
    {
        Page::create(['title' => 'Inicio', 'slug' => 'home', 'content' => [], 'is_published' => true]);

        $this->get('/')->assertOk();
    }

    public function test_admin_requires_login(): void
    {
        $this->get('/admin')->assertRedirect();
    }

    public function test_admin_without_two_factor_is_forced_to_security(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertRedirect(route('filament.admin.pages.seguridad'));
    }

    public function test_editor_without_two_factor_can_enter(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)->get('/admin')->assertOk();
    }
}
