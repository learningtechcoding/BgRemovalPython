@extends('layouts.app')
@section('title', 'Use Cases')
@section('description', 'Remove backgrounds for product photos, portraits and social media.')
@section('content')
<header class="border-b border-line bg-mint"><div class="mx-auto max-w-3xl px-4 py-14 sm:px-8"><h1 class="font-display text-4xl font-extrabold sm:text-5xl">Use Cases</h1></div></header>
<article x-reveal class="mx-auto max-w-3xl px-4 py-12 sm:px-8 space-y-4 text-ink leading-relaxed [&_h1]:text-4xl [&_h1]:font-extrabold [&_h1]:font-display [&_h1]: [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:font-display [&_h2]: [&_h2]:pt-4 [&_ul]:list-disc [&_ul]:pl-6 [&_a]:text-accent [&_a]:font-semibold [&_a]:underline">
<h2>Product photos</h2><p>Create clean, consistent images for online stores and marketplaces. Download a transparent PNG or place the product on white.</p><h2>Portraits and profile pictures</h2><p>Cut out a person and place them on a plain color for profiles and applications.</p><h2>Social media and design</h2><p>Get cutouts to drop into thumbnails, posters and presentations.</p><p><a href="{{ route('home') }}#tool">Open the tool</a></p>
</article>
@endsection
