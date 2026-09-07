@extends('layouts.app')

@section('title', 'Blog — ' . $settings->site_name)

@section('content')
    <section class="max-w-6xl mx-auto px-4 pt-16 pb-24">
        <div class="mb-12">
            <span class="eyebrow">Novedades</span>
            <h1 class="section-title mt-4">Blog</h1>
        </div>

        @if ($posts->isEmpty())
            <p class="text-slate-400">Todavía no hay entradas publicadas.</p>
        @else
            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($posts as $post)
                    <a href="{{ route('blog.show', $post) }}" class="card overflow-hidden group block">
                        <div class="overflow-hidden">
                            <img src="{{ $post->cover_image ? asset('storage/' . $post->cover_image) : 'https://placehold.co/600x400/f1f5f9/94a3b8?text=+' }}"
                                 alt="{{ $post->title }}" class="w-full h-48 object-cover transition-transform duration-500 group-hover:scale-105">
                        </div>
                        <div class="p-6">
                            <div class="text-xs uppercase tracking-wider text-slate-400">{{ optional($post->published_at)->format('d M, Y') }}</div>
                            <h2 class="mt-2 font-display text-xl font-bold tracking-tight text-brandink group-hover:text-brand transition-colors">{{ $post->title }}</h2>
                            @if ($post->excerpt)
                                <p class="mt-2 text-sm text-slate-500 leading-relaxed line-clamp-3">{{ $post->excerpt }}</p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-12">{{ $posts->links() }}</div>
        @endif
    </section>
@endsection
