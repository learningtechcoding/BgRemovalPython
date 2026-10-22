@extends('layouts.app')
@section('title', 'How It Works')
@section('description', 'Learn how the background remover turns your photo into a transparent PNG.')
@section('content')
<header class="border-b border-line bg-mint"><div class="mx-auto max-w-3xl px-4 py-14 sm:px-8"><h1 class="font-display text-4xl font-extrabold sm:text-5xl">How It Works</h1></div></header>
<article x-reveal class="mx-auto max-w-3xl px-4 py-12 sm:px-8 space-y-4 text-ink leading-relaxed [&_h1]:text-4xl [&_h1]:font-extrabold [&_h1]:font-display [&_h1]: [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:font-display [&_h2]: [&_h2]:pt-4 [&_ul]:list-disc [&_ul]:pl-6 [&_a]:text-accent [&_a]:font-semibold [&_a]:underline">
<p>1. Upload a JPG, PNG or WebP image. We check the file type, size and dimensions.</p><p>2. The image is queued and processed by an AI segmentation model that separates the subject from the background.</p><p>3. We verify the output is a PNG with a real alpha channel, then show it on a checkerboard so you can see the transparency.</p><p>4. Download the transparent PNG, or pick a solid color to replace the background. Replacing the background happens in your browser and never re-runs the AI.</p><p><a href="{{ route('home') }}#tool">Try it now</a></p>
</article>
@endsection
