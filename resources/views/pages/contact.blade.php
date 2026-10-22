@extends('layouts.app')
@section('title', 'Contact')
@section('description', 'Contact support.')
@section('content')
<header class="border-b border-line bg-mint"><div class="mx-auto max-w-3xl px-4 py-14 sm:px-8"><h1 class="font-display text-4xl font-extrabold sm:text-5xl">Contact</h1></div></header>
<article x-reveal class="mx-auto max-w-3xl px-4 py-12 sm:px-8 space-y-4 text-ink leading-relaxed [&_h1]:text-4xl [&_h1]:font-extrabold [&_h1]:font-display [&_h1]: [&_h2]:text-2xl [&_h2]:font-bold [&_h2]:font-display [&_h2]: [&_h2]:pt-4 [&_ul]:list-disc [&_ul]:pl-6 [&_a]:text-accent [&_a]:font-semibold [&_a]:underline">
<p>Questions, bug reports or abuse reports? Email <a href="mailto:{{ config('bgremover.contact_email') }}">{{ config('bgremover.contact_email') }}</a>.</p>
</article>
@endsection
