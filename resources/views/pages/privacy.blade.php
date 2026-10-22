@extends('layouts.app')
@section('title', 'Privacy Policy')
@section('description', 'How we handle your uploaded images.')
@section('content')
<header class="border-b border-line bg-mint"><div class="mx-auto max-w-3xl px-4 py-14 sm:px-8"><h1 class="font-display text-4xl font-extrabold sm:text-5xl">Privacy Policy</h1></div></header>
<article x-reveal class="mx-auto max-w-3xl px-4 py-12 sm:px-8 space-y-4 text-ink leading-relaxed [&_h1]:text-4xl [&_h1]:font-extrabold [&_h1]:font-display [&_h1]: [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:font-display [&_h2]: [&_h2]:pt-4 [&_ul]:list-disc [&_ul]:pl-6 [&_a]:text-accent [&_a]:font-semibold [&_a]:underline">
<h2>What we process</h2><p>Images you upload are processed on our own server by an AI model. No third-party processing provider is used by default.</p><h2>Retention</h2><p>Uploaded images and results are automatically deleted about {{ config('bgremover.retention_hours') }} hour(s) after upload. Deleting from the tool removes them immediately from our storage; backups and server logs are not used to retain image content.</p><h2>Training</h2><p>We do not use your images to train models.</p><h2>Data we keep</h2><p>Technical logs (such as IP address for rate limiting and error codes) are kept briefly. We use a session cookie so only you can access your own images. We use no advertising trackers.</p><h2>Contact</h2><p>See the <a href="{{ url('contact') }}">contact page</a>.</p><p><em>This is a template; have it reviewed against the privacy laws that apply to you before launch.</em></p>
</article>
@endsection
