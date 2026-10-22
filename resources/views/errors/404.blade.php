@extends('layouts.app')
@section('title', 'Page not found')
@section('content')
<div class="mx-auto max-w-xl px-4 py-24 text-center">
    <h1 class="font-display text-8xl font-extrabold text-coral">404</h1>
    <p class="mt-3 text-muted">We couldn't find that page.</p>
    <a href="{{ route('home') }}" class="mt-6 inline-block rounded-full bg-accent px-8 py-3.5 font-semibold text-white">Back to home</a>
</div>
@endsection
