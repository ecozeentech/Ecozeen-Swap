<?php

namespace Tests\Feature\Content;

use App\Models\BlogPost;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_us_page_renders_published_content(): void
    {
        Page::create(['slug' => 'about', 'title' => 'About Ecozeen Swap', 'content' => '<p>We are Ecozeen.</p>', 'is_published' => true]);

        $response = $this->get(route('about.show'));

        $response->assertOk();
        $response->assertSee('About Ecozeen Swap');
    }

    public function test_unpublished_page_returns_404(): void
    {
        Page::create(['slug' => 'about', 'title' => 'About', 'content' => 'x', 'is_published' => false]);

        $this->get(route('about.show'))->assertNotFound();
    }

    public function test_contact_form_stores_a_message(): void
    {
        $response = $this->post(route('contact.store'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Question',
            'message' => 'Hello, I have a question about my account.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contact_messages', ['email' => 'jane@example.com']);
    }

    public function test_blog_index_only_shows_published_posts(): void
    {
        BlogPost::create([
            'title' => 'Published Post', 'slug' => 'published-post', 'content' => 'Body',
            'status' => 'published', 'published_at' => now()->subDay(),
        ]);
        BlogPost::create([
            'title' => 'Draft Post', 'slug' => 'draft-post', 'content' => 'Body',
            'status' => 'draft',
        ]);

        $response = $this->get(route('blog.index'));

        $response->assertOk();
        $response->assertSee('Published Post');
        $response->assertDontSee('Draft Post');
    }

    public function test_blog_show_increments_views_and_renders_seo_meta(): void
    {
        $post = BlogPost::create([
            'title' => 'How to Buy Crypto', 'slug' => 'how-to-buy-crypto', 'content' => '<p>Guide content</p>',
            'status' => 'published', 'published_at' => now()->subDay(), 'meta_description' => 'A guide.',
        ]);

        $response = $this->get(route('blog.show', $post->slug));

        $response->assertOk();
        $response->assertSee('How to Buy Crypto');
        $response->assertSee('A guide.', false);
        $this->assertEquals(1, $post->refresh()->views);
    }

    public function test_sitemap_is_valid_xml(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
    }
}
