<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Entry;
use App\Models\Module;
use App\Models\Page;
use App\Models\Post;
use App\Models\SiteSetting;

class ContentApiController extends Controller
{
    public function settings()
    {
        $s = SiteSetting::current();

        return response()->json([
            'site_name' => $s->site_name,
            'tagline' => $s->tagline,
            'logo' => $s->logo_path ? asset('storage/' . $s->logo_path) : null,
            'primary_color' => $s->primary_color,
            'secondary_color' => $s->secondary_color,
            'whatsapp' => $s->whatsapp,
            'phone' => $s->phone,
            'email' => $s->email,
            'address' => $s->address,
            'social' => [
                'facebook' => $s->facebook,
                'instagram' => $s->instagram,
                'tiktok' => $s->tiktok,
            ],
            'seo' => [
                'meta_title' => $s->meta_title,
                'meta_description' => $s->meta_description,
            ],
        ]);
    }

    public function pages()
    {
        $pages = Page::where('is_published', true)
            ->orderBy('sort_order')
            ->get(['title', 'slug', 'meta_title', 'meta_description', 'updated_at']);

        return response()->json(['data' => $pages]);
    }

    public function page(string $slug)
    {
        $page = Page::where('slug', $slug)->where('is_published', true)->firstOrFail();

        return response()->json([
            'title' => $page->title,
            'slug' => $page->slug,
            'content' => $page->content ?? [],
            'seo' => [
                'meta_title' => $page->meta_title,
                'meta_description' => $page->meta_description,
            ],
            'updated_at' => $page->updated_at,
        ]);
    }

    public function posts()
    {
        $posts = Post::where('is_published', true)
            ->orderByDesc('published_at')
            ->paginate(12, ['title', 'slug', 'excerpt', 'cover_image', 'published_at']);

        $posts->getCollection()->transform(fn (Post $p) => [
            'title' => $p->title,
            'slug' => $p->slug,
            'excerpt' => $p->excerpt,
            'cover_image' => $p->cover_image ? asset('storage/' . $p->cover_image) : null,
            'published_at' => $p->published_at,
        ]);

        return response()->json($posts);
    }

    public function post(string $slug)
    {
        $post = Post::where('slug', $slug)->where('is_published', true)->firstOrFail();

        return response()->json([
            'title' => $post->title,
            'slug' => $post->slug,
            'excerpt' => $post->excerpt,
            'body' => $post->body,
            'cover_image' => $post->cover_image ? asset('storage/' . $post->cover_image) : null,
            'published_at' => $post->published_at,
            'seo' => [
                'meta_title' => $post->meta_title,
                'meta_description' => $post->meta_description,
            ],
        ]);
    }

    public function modules()
    {
        $modules = Module::orderBy('sort_order')->orderBy('name')->get()
            ->map(fn (Module $m) => [
                'name' => $m->name,
                'slug' => $m->slug,
                'type' => $m->type,
                'is_public' => $m->is_public,
                'fields' => $m->fieldList()->values(),
            ]);

        return response()->json(['data' => $modules]);
    }

    public function module(string $slug)
    {
        $module = Module::where('slug', $slug)->firstOrFail();

        return response()->json([
            'name' => $module->name,
            'slug' => $module->slug,
            'type' => $module->type,
            'is_public' => $module->is_public,
            'fields' => $module->fieldList()->values(),
        ]);
    }

    public function entries(string $slug)
    {
        $module = Module::where('slug', $slug)->firstOrFail();

        $entries = $module->entries()
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(20);

        $entries->getCollection()->transform(fn (Entry $e) => $this->transformEntry($e, $module));

        return response()->json($entries);
    }

    public function entry(string $slug, string $entry)
    {
        $module = Module::where('slug', $slug)->firstOrFail();

        $record = $module->entries()
            ->where('slug', $entry)
            ->where('is_published', true)
            ->firstOrFail();

        return response()->json($this->transformEntry($record, $module));
    }

    /**
     * Convierte los campos de imagen/galería a URLs absolutas.
     */
    protected function transformEntry(Entry $entry, Module $module): array
    {
        $data = $entry->data ?? [];

        foreach ($module->fieldList() as $field) {
            $key = $field['key'];
            if (empty($data[$key])) {
                continue;
            }

            if (($field['type'] ?? '') === 'image') {
                $data[$key] = asset('storage/' . $data[$key]);
            } elseif (($field['type'] ?? '') === 'gallery') {
                $data[$key] = array_map(fn ($p) => asset('storage/' . $p), (array) $data[$key]);
            } elseif (($field['type'] ?? '') === 'relation') {
                $rel = Entry::find($data[$key]);
                $data[$key] = $rel ? ['id' => $rel->id, 'title' => $rel->title, 'slug' => $rel->slug] : null;
            }
        }

        return [
            'id' => $entry->id,
            'title' => $entry->title,
            'slug' => $entry->slug,
            'data' => $data,
            'created_at' => $entry->created_at,
        ];
    }
}
