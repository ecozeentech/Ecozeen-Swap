<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        $page = Page::findBySlug($slug);

        abort_if(! $page || ! $page->is_published, 404);

        [$content, $tableOfContents] = $this->addHeadingAnchors((string) $page->content);

        return view('pages.show', ['page' => $page, 'content' => $content, 'tableOfContents' => $tableOfContents]);
    }

    /**
     * Injects an id="..." into every <h2> in the page content and returns
     * a matching table-of-contents list, powering the policy-page sidebar
     * navigation shown on Privacy Policy / Terms & Conditions.
     */
    protected function addHeadingAnchors(string $html): array
    {
        $toc = [];

        $content = preg_replace_callback('/<h2>(.*?)<\/h2>/i', function ($matches) use (&$toc) {
            $text = trim(strip_tags($matches[1]));
            $slug = Str::slug($text) ?: 'section-'.(count($toc) + 1);
            $toc[] = ['id' => $slug, 'title' => $text];

            return '<h2 id="'.$slug.'">'.$matches[1].'</h2>';
        }, $html);

        return [$content ?? $html, $toc];
    }

    public function about(): View
    {
        $page = Page::findBySlug('about');

        abort_if(! $page || ! $page->is_published, 404);

        return view('pages.about', ['page' => $page]);
    }
}
