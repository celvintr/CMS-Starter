<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Page;
use App\Models\Post;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $results = collect();

        if (mb_strlen($q) >= 2) {
            $like = '%' . $q . '%';

            foreach (Page::published()->where('title', 'like', $like)->limit(10)->get() as $page) {
                $results->push([
                    'type' => 'Página',
                    'title' => $page->title,
                    'url' => url('/' . $page->slug),
                    'snippet' => $page->meta_description,
                ]);
            }

            foreach (Post::published()
                ->where(fn ($w) => $w->where('title', 'like', $like)->orWhere('excerpt', 'like', $like)->orWhere('body', 'like', $like))
                ->limit(10)->get() as $post) {
                $results->push([
                    'type' => 'Blog',
                    'title' => $post->title,
                    'url' => route('blog.show', $post->slug),
                    'snippet' => $post->excerpt,
                ]);
            }

            foreach (Entry::with('module')->published()->where('title', 'like', $like)->limit(15)->get() as $entry) {
                if ($entry->module && $entry->module->is_public) {
                    $results->push([
                        'type' => $entry->module->singularLabel(),
                        'title' => $entry->title,
                        'url' => route('module.show', [$entry->module->slug, $entry->slug]),
                        'snippet' => null,
                    ]);
                }
            }
        }

        return view('buscar', ['q' => $q, 'results' => $results]);
    }
}
