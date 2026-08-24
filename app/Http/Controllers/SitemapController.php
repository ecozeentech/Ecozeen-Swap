<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Page;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = collect([
            ['loc' => route('home'), 'priority' => '1.0'],
            ['loc' => route('about.show'), 'priority' => '0.6'],
            ['loc' => route('contact.show'), 'priority' => '0.6'],
            ['loc' => route('blog.index'), 'priority' => '0.8'],
        ]);

        foreach (Page::query()->where('is_published', true)->get() as $page) {
            $urls->push(['loc' => url('/'.$page->slug), 'priority' => '0.5']);
        }

        foreach (BlogPost::query()->published()->get() as $post) {
            $urls->push([
                'loc' => route('blog.show', $post->slug),
                'priority' => '0.7',
                'lastmod' => $post->updated_at->toAtomString(),
            ]);
        }

        $xml = view('sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
