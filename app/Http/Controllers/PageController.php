<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        $page = Page::findBySlug($slug);

        abort_if(! $page || ! $page->is_published, 404);

        return view('pages.show', ['page' => $page]);
    }

    public function about(): View
    {
        $page = Page::findBySlug('about-us');

        abort_if(! $page || ! $page->is_published, 404);

        return view('pages.about', ['page' => $page]);
    }
}
