<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Page;
use App\Models\Post;

class SeoController extends Controller
{
    public function sitemap()
    {
        $urls = [];

        $urls[] = ['loc' => url('/')];

        foreach (Page::where('is_published', true)->where('slug', '!=', 'home')->get() as $page) {
            $urls[] = ['loc' => url('/' . $page->slug), 'lastmod' => optional($page->updated_at)->toAtomString()];
        }

        $urls[] = ['loc' => route('blog.index')];
        foreach (Post::where('is_published', true)->get() as $post) {
            $urls[] = ['loc' => route('blog.show', $post->slug), 'lastmod' => optional($post->updated_at)->toAtomString()];
        }

        foreach (Module::where('is_public', true)->get() as $module) {
            $urls[] = ['loc' => route('module.index', $module->slug)];
            foreach ($module->entries()->where('is_published', true)->get() as $entry) {
                $urls[] = ['loc' => route('module.show', [$module->slug, $entry->slug]), 'lastmod' => optional($entry->updated_at)->toAtomString()];
            }
        }

        return response()->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    public function robots()
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            '',
            'Sitemap: ' . url('/sitemap.xml'),
            '',
        ];

        return response(implode("\n", $lines), 200)->header('Content-Type', 'text/plain');
    }
}
