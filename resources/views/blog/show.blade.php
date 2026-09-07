@extends('layouts.app')

@section('title', ($post->meta_title ?: $post->title) . ' — ' . $settings->site_name)
@section('meta_description', $post->meta_description ?: $post->excerpt)
@section('og_type', 'article')
@if ($post->cover_image)
    @section('og_image', asset('storage/' . $post->cover_image))
@endif

@section('head')
    <script type="application/ld+json">{!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $post->title,
        'description' => $post->excerpt,
        'image' => $post->cover_image ? asset('storage/' . $post->cover_image) : null,
        'datePublished' => optional($post->published_at)->toAtomString(),
        'author' => ['@type' => 'Organization', 'name' => $settings->site_name],
    ]), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endsection

@section('content')
    <article class="max-w-3xl mx-auto px-4 py-14">
        <a href="{{ route('blog.index') }}" class="text-sm text-brand hover:underline">&larr; Volver al blog</a>
        <h1 class="mt-4 text-4xl font-extrabold" style="color: var(--brand-ink)">{{ $post->title }}</h1>
        <div class="mt-2 text-sm text-slate-400">{{ optional($post->published_at)->format('d/m/Y') }}</div>

        @if ($post->cover_image)
            <img src="{{ asset('storage/' . $post->cover_image) }}" alt="{{ $post->title }}"
                 class="mt-6 w-full rounded-2xl shadow-md object-cover">
        @endif

        <div class="mt-8 prose prose-slate max-w-none prose-a:text-brand">
            {!! $post->body !!}
        </div>
    </article>
@endsection
