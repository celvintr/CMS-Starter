@extends('layouts.app')

@section('title', ($page->meta_title ?: $page->t('title')) . ' — ' . $settings->t('site_name'))
@section('meta_description', $page->meta_description ?: $settings->t('meta_description'))

@section('content')
    @forelse (($page->t('content') ?? []) as $block)
        @includeIf('blocks.' . $block['type'], ['data' => $block['data'] ?? []])
    @empty
        <div class="max-w-3xl mx-auto px-4 py-24 text-center text-slate-400">
            Esta página aún no tiene contenido. Agrégalo desde el panel de administración.
        </div>
    @endforelse
@endsection
