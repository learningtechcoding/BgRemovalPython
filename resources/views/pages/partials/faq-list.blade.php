@php
    $hours = config('bgremover.retention_hours');
    $faqs = [
        ['Is it free?', 'Yes. Right now the tool is free to use within fair-use rate limits.'],
        ['Which formats can I upload?', 'JPG, PNG and WebP, up to '.round(config('bgremover.limits.max_file_kb') / 1024).' MB and '.config('bgremover.limits.max_dimension').' pixels per side.'],
        ['What do I download?', 'A PNG with a real transparent (alpha) channel. If you choose a solid background color, the PNG is flattened onto that color.'],
        ['What happens to my images?', "Images are processed on our own server and automatically deleted about $hours hour(s) after upload. You can also delete an image immediately by using \"Upload another image\"."],
        ['Is the result always perfect?', 'No. AI cutouts can struggle with fine hair, glass, shadows and subjects that match the background. Try a photo with clearer contrast if results are poor.'],
        ['Do I need an account?', 'No. Accounts, history and bulk processing may come later.'],
    ];
    if (isset($limit)) { $faqs = array_slice($faqs, 0, $limit); }
@endphp
<div class="space-y-1 border-b border-ink/40">
    @foreach ($faqs as [$q, $a])
        <details class="border-t border-ink/40 py-4">
            <summary class="cursor-pointer font-display text-lg font-bold min-h-11">{{ $q }}</summary>
            <p class="mt-2 text-muted">{{ $a }}</p>
        </details>
    @endforeach
</div>
