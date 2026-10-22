@extends('layouts.app')
@section('title', 'Pricing')
@section('description', 'Background Studio is free to use within fair-use limits.')
@section('content')
<header class="border-b border-line bg-mint"><div class="mx-auto max-w-4xl px-4 py-14 sm:px-8"><h1 class="font-display text-4xl font-extrabold sm:text-5xl">Pricing</h1><p class="mt-3 max-w-xl text-lg text-muted">Free while we learn what people need. Paid plans come later, once real usage and costs are understood.</p></div></header>
<div class="mx-auto grid max-w-4xl gap-6 px-4 py-14 sm:px-8 md:grid-cols-2">
    <section x-reveal class="rounded-3xl bg-accent p-8 text-white" aria-labelledby="p-free">
        <h2 id="p-free" class="font-display text-2xl font-bold">Free</h2>
        <p class="mt-1 font-display text-5xl font-extrabold">$0</p>
        <ul class="mt-6 space-y-2">
            <li>✓ Transparent PNG, WebP, JPG, GIF and SVG</li>
            <li>✓ Background color replacement</li>
            <li>✓ Up to {{ config('bgremover.limits.uploads_per_hour') }} uploads per hour</li>
            <li>✓ No account, no watermark</li>
        </ul>
        <a href="{{ route('home') }}#tool" class="tap mt-8 inline-flex min-h-12 items-center rounded-full bg-butter px-7 font-bold text-ink">Start removing</a>
    </section>
    <section x-reveal style="--d:120ms" class="rounded-3xl border-2 border-dashed border-ink/30 bg-butter p-8" aria-labelledby="p-pro">
        <h2 id="p-pro" class="font-display text-2xl font-bold">Pro <span class="ml-2 rounded-full bg-deep px-3 py-1 align-middle text-xs font-semibold text-white">Planned</span></h2>
        <p class="mt-1 font-display text-5xl font-extrabold">Soon</p>
        <ul class="mt-6 space-y-2">
            <li>• Higher-resolution output</li>
            <li>• Bulk processing and ZIP download</li>
            <li>• Saved history</li>
            <li>• API access</li>
        </ul>
        <p class="mt-8 text-sm text-ink/70">Not available yet. Nothing to buy today.</p>
    </section>
</div>
@endsection
