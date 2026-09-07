<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    public function home()
    {
        $page = Page::where('slug', 'home')->where('is_published', true)->first();

        abort_if(! $page, 404);

        return view('page', compact('page'));
    }

    public function show(Page $page)
    {
        abort_if(! $page->is_published, 404);

        return view('page', compact('page'));
    }
}
