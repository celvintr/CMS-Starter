@php
    $url = $data['url'] ?? '';
    $embed = null;
    if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/)([\w-]{11})~', $url, $m)) {
        $embed = 'https://www.youtube.com/embed/' . $m[1];
    } elseif (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
        $embed = 'https://player.vimeo.com/video/' . $m[1];
    }
@endphp

<section class="max-w-4xl mx-auto px-4 py-20 md:py-24">
    @if (!empty($data['heading']))
        <div class="text-center max-w-2xl mx-auto mb-10">
            <span class="eyebrow justify-center">Video</span>
            <h2 class="section-title mt-4">{{ $data['heading'] }}</h2>
        </div>
    @endif

    @if ($embed)
        <div class="rounded-2xl overflow-hidden shadow-lg" style="position:relative;padding-bottom:56.25%;height:0">
            <iframe src="{{ $embed }}" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0"
                    loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen title="Video"></iframe>
        </div>
    @elseif ($url)
        <p class="text-center text-slate-400 text-sm">Pega una URL válida de YouTube o Vimeo.</p>
    @endif
</section>
