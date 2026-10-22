<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Free Background Remover – Make Image Backgrounds Transparent') · {{ config('app.name') }}</title>
    <meta name="description" content="@yield('description', 'Remove the background from any photo in seconds and download a transparent PNG. No account needed. Uploads are deleted automatically.')">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Free Background Remover')">
    <meta property="og:description" content="@yield('description', 'Remove image backgrounds and download a transparent PNG.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="theme-color" content="#f7f5ef">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col antialiased">
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-surface focus:p-3">Skip to content</a>

<header class="rise border-b border-line bg-surface">
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-8 lg:px-12" x-data="{ open: false }">
        <a href="{{ route('home') }}" class="font-display text-xl font-extrabold tracking-tight">
            {{ config('app.name') }}<span class="text-accent">.</span>
        </a>
        <button type="button" class="grid h-11 w-11 place-items-center rounded-full border border-line md:hidden" @click="open = !open" :aria-expanded="open" aria-controls="nav">
            <span class="sr-only">Toggle menu</span>
            <svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        </button>
        <nav id="nav" aria-label="Main" class="hidden items-center gap-7 text-[15px] font-medium md:flex"
             :class="{ '!flex absolute left-0 right-0 top-[73px] z-40 flex-col items-start gap-1 border-b border-line bg-paper px-4 pb-4': open }">
            <a class="underline-grow py-2 hover:text-accent" href="{{ url('how-it-works') }}">How it works</a>
            <a class="underline-grow py-2 hover:text-accent" href="{{ url('use-cases') }}">Use cases</a>
            <a class="underline-grow py-2 hover:text-accent" href="{{ url('pricing') }}">Pricing</a>
            <a class="underline-grow py-2 hover:text-accent" href="{{ url('faq') }}">FAQ</a>
            <a class="rounded-full bg-accent px-5 py-2.5 text-white hover:bg-accent-dark" href="{{ route('home') }}#tool">Remove background</a>
        </nav>
    </div>
</header>

<main id="main" class="flex-1">
    @yield('content')
</main>

<footer class="bg-deep text-paper">
    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 text-sm sm:px-8 lg:px-12 md:grid-cols-[1.4fr_1fr_1fr_1fr]">
        <div>
            <p class="font-display text-xl font-extrabold">{{ config('app.name') }}<span class="text-butter">.</span></p>
            <p class="mt-3 max-w-xs text-paper/70">Remove image backgrounds in seconds. Files are deleted automatically after {{ config('bgremover.retention_hours') }} hour(s).</p>
        </div>
        <nav aria-label="Product">
            <p class="font-display text-base font-bold text-butter">Product</p>
            <ul class="mt-3 space-y-2 text-paper/70">
                <li><a class="hover:text-butter hover:underline" href="{{ route('home') }}#tool">Remove background</a></li>
                <li><a class="hover:text-butter hover:underline" href="{{ url('how-it-works') }}">How it works</a></li>
                <li><a class="hover:text-butter hover:underline" href="{{ url('use-cases') }}">Use cases</a></li>
                <li><a class="hover:text-butter hover:underline" href="{{ url('pricing') }}">Pricing</a></li>
            </ul>
        </nav>
        <nav aria-label="Support">
            <p class="font-display text-base font-bold text-butter">Support</p>
            <ul class="mt-3 space-y-2 text-paper/70">
                <li><a class="hover:text-butter hover:underline" href="{{ url('faq') }}">FAQ</a></li>
                <li><a class="hover:text-butter hover:underline" href="{{ url('contact') }}">Contact</a></li>
            </ul>
        </nav>
        <nav aria-label="Company">
            <p class="font-display text-base font-bold text-butter">Company</p>
            <ul class="mt-3 space-y-2 text-paper/70">
                <li><a class="hover:text-butter hover:underline" href="{{ url('about') }}">About</a></li>
                <li><a class="hover:text-butter hover:underline" href="{{ url('privacy') }}">Privacy</a></li>
                <li><a class="hover:text-butter hover:underline" href="{{ url('terms') }}">Terms</a></li>
            </ul>
        </nav>
    </div>
    <p class="border-t border-paper/15 px-4 py-5 text-center text-xs text-paper/60">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
</footer>
</body>
</html>
