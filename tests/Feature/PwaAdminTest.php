<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PwaAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_route_returns_default_values(): void
    {
        $response = $this->get(route('pwa.manifest'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/manifest+json');
        $response->assertJsonPath('theme_color', '#2563EB');
        $response->assertJsonPath('background_color', '#F8FAFC');
        $response->assertJsonStructure(['name', 'short_name', 'icons']);
    }

    public function test_admin_can_upload_a_pwa_icon_and_manifest_reflects_it(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $icon = UploadedFile::fake()->image('icon.png', 512, 512);

        $response = $this->actingAs($admin)->put(route('admin.pwa.update'), [
            'pwa_icon_512' => $icon,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $manifest = $this->get(route('pwa.manifest'))->json();
        $this->assertStringContainsString('/storage/pwa/', $manifest['icons'][1]['src']);
    }

    public function test_admin_can_update_app_name_and_colors(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $response = $this->actingAs($admin)->put(route('admin.pwa.update'), [
            'pwa_app_name' => 'Test App',
            'pwa_short_name' => 'TestApp',
            'pwa_theme_color' => '#112233',
            'pwa_background_color' => '#445566',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $manifest = $this->get(route('pwa.manifest'))->json();
        $this->assertSame('Test App', $manifest['name']);
        $this->assertSame('TestApp', $manifest['short_name']);
        $this->assertSame('#112233', $manifest['theme_color']);
        $this->assertSame('#445566', $manifest['background_color']);
    }

    public function test_uploading_an_icon_does_not_clobber_previously_set_app_details(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin)->put(route('admin.pwa.update'), [
            'pwa_app_name' => 'Persisted Name',
            'pwa_theme_color' => '#abcdef',
        ]);

        $icon = UploadedFile::fake()->image('icon.png', 192, 192);
        $this->actingAs($admin)->put(route('admin.pwa.update'), [
            'pwa_icon_192' => $icon,
        ]);

        $manifest = $this->get(route('pwa.manifest'))->json();
        $this->assertSame('Persisted Name', $manifest['name']);
        $this->assertSame('#abcdef', $manifest['theme_color']);
    }

    public function test_setting_app_details_does_not_clobber_a_previously_uploaded_icon(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $icon = UploadedFile::fake()->image('icon.png', 512, 512);
        $this->actingAs($admin)->put(route('admin.pwa.update'), [
            'pwa_icon_512' => $icon,
        ]);

        $manifestBefore = $this->get(route('pwa.manifest'))->json();
        $uploadedIconUrl = $manifestBefore['icons'][1]['src'];

        $this->actingAs($admin)->put(route('admin.pwa.update'), [
            'pwa_app_name' => 'Another Name',
        ]);

        $manifestAfter = $this->get(route('pwa.manifest'))->json();
        $this->assertSame($uploadedIconUrl, $manifestAfter['icons'][1]['src']);
    }

    public function test_admin_can_reset_a_pwa_icon_to_default(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $icon = UploadedFile::fake()->image('icon.png', 512, 512);
        $this->actingAs($admin)->put(route('admin.pwa.update'), ['pwa_icon_512' => $icon]);

        $response = $this->actingAs($admin)->delete(route('admin.pwa.reset', 'pwa_icon_512'));
        $response->assertRedirect();

        $manifest = $this->get(route('pwa.manifest'))->json();
        $this->assertStringContainsString('images/icons/icon-512.png', $manifest['icons'][1]['src']);
    }

    public function test_maskable_icon_is_omitted_from_manifest_when_not_set(): void
    {
        $manifest = $this->get(route('pwa.manifest'))->json();

        $this->assertCount(2, $manifest['icons']);
    }

    public function test_maskable_icon_appears_in_manifest_once_uploaded(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $icon = UploadedFile::fake()->image('maskable.png', 512, 512);
        $this->actingAs($admin)->put(route('admin.pwa.update'), ['pwa_maskable_icon' => $icon]);

        $manifest = $this->get(route('pwa.manifest'))->json();
        $this->assertCount(3, $manifest['icons']);
        $this->assertSame('maskable', $manifest['icons'][2]['purpose']);
    }

    public function test_non_admin_cannot_access_pwa_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.pwa.edit'))->assertForbidden();
    }
}
