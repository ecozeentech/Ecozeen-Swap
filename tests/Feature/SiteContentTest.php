<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\SystemSetting;
use App\Models\User;
use App\Support\SiteContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render_the_built_in_copy_before_anything_is_saved(): void
    {
        $this->aboutPage();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('directly with us.')
            ->assertSee('Everything you need, one vendor you can trust')
            ->assertSee('The single trusted vendor for buying, selling, and swapping crypto.');

        $this->get(route('about.show'))
            ->assertOk()
            ->assertSee('Financial Inclusion')
            ->assertSee('Our origin story.')
            ->assertSee('Leadership Team')
            ->assertSee('RC 1835204');

        $this->get(route('contact.show'))
            ->assertOk()
            ->assertSee('Technical Support')
            ->assertSee('Send a Message');

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('Insights & Updates');
    }

    public function test_admin_can_open_the_editor_and_a_guest_cannot(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.site-content.edit'))
            ->assertOk()
            ->assertSee('Homepage')
            ->assertSee('directly with us.')
            ->assertSee('min-height:0', false);

        $this->actingAs($admin)
            ->get(route('admin.site-content.edit', 'missing'))
            ->assertNotFound();

        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.site-content.edit'))->assertForbidden();
        $this->actingAs($user)
            ->put(route('admin.site-content.update', 'homepage'), $this->payload('homepage'))
            ->assertForbidden();
    }

    public function test_saving_homepage_copy_shows_on_the_public_page_and_leaves_other_sections_intact(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->put(route('admin.site-content.update', 'homepage'), $this->payload('homepage', [
                'hero' => ['headline_highlight' => 'first highlight'],
                'features' => [
                    ['title' => 'Desk Trades', 'description' => 'Only with us.'],
                ],
            ]))
            ->assertRedirect(route('admin.site-content.edit', 'homepage'))
            ->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->put(route('admin.site-content.update', 'homepage'), [
                'present' => ['hero'],
                'sections' => [
                    'hero' => array_merge(SiteContent::data('homepage', 'hero'), [
                        'headline_highlight' => 'second highlight',
                    ]),
                ],
            ])
            ->assertSessionHasNoErrors();

        auth()->logout();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('second highlight')
            ->assertDontSee('first highlight')
            ->assertSee('Desk Trades')
            ->assertDontSee('Gift Cards & Invoices');
    }

    public function test_saving_one_page_does_not_clobber_another_and_reset_restores_defaults(): void
    {
        $admin = $this->admin();
        $this->aboutPage();

        $this->actingAs($admin)->put(
            route('admin.site-content.update', 'homepage'),
            $this->payload('homepage', ['hero' => ['headline_highlight' => 'with our desk.']])
        )->assertSessionHasNoErrors();

        $this->actingAs($admin)->put(
            route('admin.site-content.update', 'about'),
            $this->payload('about', ['hero' => ['title_highlight' => 'Open Markets']])
        )->assertSessionHasNoErrors();

        auth()->logout();

        $this->get(route('home'))->assertSee('with our desk.');
        $this->get(route('about.show'))->assertSee('Open Markets');

        $this->actingAs($admin)
            ->delete(route('admin.site-content.reset', 'homepage'))
            ->assertRedirect(route('admin.site-content.edit', 'homepage'));

        auth()->logout();

        $this->get(route('home'))
            ->assertSee('directly with us.')
            ->assertDontSee('with our desk.');

        $this->get(route('about.show'))->assertSee('Open Markets');
    }

    public function test_about_image_upload_and_removal_updates_the_public_page(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $this->aboutPage();

        $payload = $this->payload('about', [
            'story' => ['caption' => 'Lagos office'],
            'team' => [
                ['name' => 'Ada Lovelace', 'role' => 'Chief Technology Officer'],
            ],
        ]);
        $payload['images'] = [
            'story' => ['image' => UploadedFile::fake()->image('office.jpg', 640, 480)],
        ];

        $this->actingAs($admin)
            ->put(route('admin.site-content.update', 'about'), $payload)
            ->assertSessionHasNoErrors();

        $stored = SystemSetting::get('content.about.story');
        $this->assertIsArray($stored);
        $this->assertNotSame('', $stored['image']);
        Storage::disk('public')->assertExists($stored['image']);

        $this->get(route('about.show'))
            ->assertOk()
            ->assertSee('Lagos office')
            ->assertSee('Ada Lovelace')
            ->assertSee('Our origin story.')
            ->assertDontSee('Chief Executive Officer')
            ->assertSee('storage/site-content', false);

        $remove = $this->payload('about');
        $remove['remove'] = ['story' => ['image' => '1']];

        $this->actingAs($admin)
            ->put(route('admin.site-content.update', 'about'), $remove)
            ->assertSessionHasNoErrors();

        Storage::disk('public')->assertMissing($stored['image']);
        $this->get(route('about.show'))->assertDontSee('storage/site-content', false);
    }

    public function test_invalid_image_is_rejected_and_does_not_change_the_page(): void
    {
        Storage::fake('public');
        $admin = $this->admin();
        $this->aboutPage();

        $payload = $this->payload('about', ['story' => ['caption' => 'Should not save']]);
        $payload['images'] = [
            'story' => ['image' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf')],
        ];

        $this->actingAs($admin)
            ->put(route('admin.site-content.update', 'about'), $payload)
            ->assertSessionHasErrors('images.story.image');

        $this->get(route('about.show'))
            ->assertSee('Global Reach')
            ->assertDontSee('Should not save');
    }

    public function test_footer_navigation_blog_and_contact_labels_are_editable(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put(
            route('admin.site-content.update', 'footer'),
            $this->payload('footer', ['brand' => ['tagline' => 'Trade at the counter.']])
        )->assertSessionHasNoErrors();

        $this->actingAs($admin)->put(
            route('admin.site-content.update', 'navigation'),
            $this->payload('navigation', ['labels' => ['buy' => 'Purchase']])
        )->assertSessionHasNoErrors();

        $this->actingAs($admin)->put(
            route('admin.site-content.update', 'blog'),
            $this->payload('blog', ['header' => ['title' => 'Field Notes']])
        )->assertSessionHasNoErrors();

        $this->actingAs($admin)->put(
            route('admin.site-content.update', 'contact'),
            $this->payload('contact', [
                'labels' => ['form_heading' => 'Write to us'],
                'subjects' => [
                    ['label' => 'Billing Help'],
                ],
            ])
        )->assertSessionHasNoErrors();

        auth()->logout();

        $this->get(route('home'))
            ->assertSee('Trade at the counter.')
            ->assertSee('Purchase');

        $this->get(route('blog.index'))->assertSee('Field Notes');

        $this->get(route('contact.show'))
            ->assertSee('Write to us')
            ->assertSee('Billing Help')
            ->assertDontSee('Technical Support');
    }

    public function test_script_tags_in_copy_are_stripped(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put(
            route('admin.site-content.update', 'homepage'),
            $this->payload('homepage', [
                'hero' => ['headline_highlight' => '<script>alert(1)</script>'],
            ])
        )->assertSessionHasNoErrors();

        auth()->logout();

        $this->get(route('home'))
            ->assertDontSee('<script>', false)
            ->assertSee('alert(1)');
    }

    public function test_clearing_the_team_hides_that_block(): void
    {
        $admin = $this->admin();
        $this->aboutPage();

        $this->actingAs($admin)->put(
            route('admin.site-content.update', 'about'),
            $this->payload('about', [
                'team_intro' => ['title' => '', 'subtitle' => ''],
                'team' => [],
            ])
        )->assertSessionHasNoErrors();

        $this->get(route('about.show'))
            ->assertOk()
            ->assertDontSee('Leadership Team')
            ->assertDontSee('Executive Name')
            ->assertSee('Financial Inclusion');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array{present: array<int, string>, sections: array<string, mixed>}
     */
    protected function payload(string $group, array $overrides = []): array
    {
        $sections = [];
        $present = [];

        foreach (SiteContent::sections($group) as $key => $definition) {
            $present[] = $key;
            $current = SiteContent::data($group, $key);

            if (! array_key_exists($key, $overrides)) {
                $sections[$key] = $current;

                continue;
            }

            $sections[$key] = SiteContent::isRepeat($definition)
                ? $overrides[$key]
                : array_merge($current, $overrides[$key]);
        }

        return ['present' => $present, 'sections' => $sections];
    }

    protected function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        return $admin;
    }

    protected function aboutPage(): void
    {
        Page::query()->create([
            'slug' => 'about',
            'title' => 'About Ecozeen',
            'content' => '<p>Our origin story.</p>',
            'is_published' => true,
        ]);
    }
}
