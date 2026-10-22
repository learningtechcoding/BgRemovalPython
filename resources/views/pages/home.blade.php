@extends('layouts.app')

@section('content')
<div class="relative overflow-hidden bg-gradient-to-b from-mint via-mint to-paper">
    <div aria-hidden="true" class="checker pointer-events-none absolute -right-24 -top-24 h-72 w-72 rotate-12 rounded-[3rem] opacity-40"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -left-20 bottom-0 h-56 w-56 rounded-full bg-butter opacity-80 blur-2xl"></div>
    <div class="relative"><x-remover /></div>
</div>

{{-- Shared illustration used by the before/after demo --}}
<svg width="0" height="0" class="absolute" aria-hidden="true" focusable="false">
    <defs>
        <g id="demo-plant">
            <g transform="rotate(-38 200 205)"><ellipse cx="200" cy="140" rx="20" ry="62" fill="#0b7a63"/></g>
            <g transform="rotate(38 200 205)"><ellipse cx="200" cy="140" rx="20" ry="62" fill="#0e9378"/></g>
            <g transform="rotate(-14 200 205)"><ellipse cx="200" cy="128" rx="22" ry="74" fill="#0a6b57"/></g>
            <g transform="rotate(16 200 205)"><ellipse cx="200" cy="132" rx="19" ry="68" fill="#16a487"/></g>
            <path d="M200 205 V130" stroke="#075c4a" stroke-width="3" stroke-linecap="round"/>
            <rect x="143" y="200" width="114" height="18" rx="7" fill="#c9431d"/>
            <path d="M150 218 H250 L238 284 Q237 290 230 290 H170 Q163 290 162 284 Z" fill="#e4572e"/>
        </g>
        <pattern id="demo-stripes" width="26" height="26" patternUnits="userSpaceOnUse" patternTransform="rotate(35)">
            <rect width="26" height="26" fill="#f2b84b"/><rect width="13" height="26" fill="#eaa72f"/>
        </pattern>
    </defs>
</svg>

<section class="bg-paper" aria-labelledby="demo-h">
    <div class="mx-auto grid max-w-6xl items-center gap-10 px-4 py-16 sm:px-8 lg:px-12 lg:grid-cols-[1fr_1.1fr]">
        <div x-reveal>
            <h2 id="demo-h" class="text-3xl font-extrabold sm:text-5xl">Drag to see what disappears.</h2>
            <p class="mt-4 max-w-md text-lg text-muted">Busy backdrop on the left, clean cutout on the right. The same thing happens to your photo in a few seconds.</p>
            <a href="#tool" class="tap mt-7 inline-flex min-h-12 items-center rounded-full bg-ink px-7 font-semibold text-paper transition-colors hover:bg-accent-dark">Try it with your image</a>
        </div>

        <div x-reveal style="--d:120ms" x-data="{ pos: 50 }" class="relative aspect-[4/3] overflow-hidden rounded-3xl border-4 border-surface shadow-[0_30px_60px_-30px_rgba(10,61,51,.45)] checker">
            <svg viewBox="0 0 400 300" class="absolute inset-0 h-full w-full" role="img" aria-label="Potted plant on a transparent background"><use href="#demo-plant"/></svg>
            <div class="absolute inset-0" :style="`clip-path: inset(0 ${100 - pos}% 0 0)`">
                <svg viewBox="0 0 400 300" class="h-full w-full" aria-hidden="true">
                    <rect width="400" height="300" fill="url(#demo-stripes)"/>
                    <circle cx="70" cy="70" r="46" fill="#3b6fd8"/><circle cx="345" cy="240" r="58" fill="#c9a7e8"/><circle cx="330" cy="60" r="26" fill="#f4c6d0"/>
                    <rect x="0" y="262" width="400" height="38" fill="#1c2420" opacity=".85"/>
                    <use href="#demo-plant"/>
                </svg>
            </div>
            <div class="pointer-events-none absolute inset-y-0 w-0.5 bg-white shadow-[0_0_0_1px_rgba(0,0,0,.25)]" :style="`left:${pos}%`">
                <span class="absolute top-1/2 -ml-[17px] -mt-[17px] grid h-9 w-9 place-items-center rounded-full bg-white text-xs font-bold shadow-md" aria-hidden="true">⇆</span>
            </div>
            <span class="absolute left-3 top-3 rounded-full bg-ink px-3 py-1 text-xs font-semibold text-paper">Before</span>
            <span class="absolute right-3 top-3 rounded-full bg-surface px-3 py-1 text-xs font-semibold">After</span>
            <input type="range" min="0" max="100" x-model.number="pos" class="compare-range" aria-label="Before and after comparison">
        </div>
    </div>
</section>

{{-- Audience tabs --}}
<section class="bg-butter" aria-labelledby="who-h"
    x-data="{ tab: 'sellers', tabs: {
        sellers: ['Online sellers', 'Put every product on the same clean background.', ['White or brand-color backgrounds in one click', 'Download as PNG, WebP or JPG for any marketplace', 'Consistent catalogs without a studio']],
        people: ['Individuals', 'Profile pictures, CVs and passport-style photos.', ['Cut yourself out in seconds', 'Swap in a solid color that fits the form', 'Your photo is deleted within the hour']],
        creators: ['Creators & marketers', 'Cutouts that drop straight into your designs.', ['Transparent PNG for thumbnails, posters and slides', 'Paste straight from your clipboard', 'No watermark, no sign-up']],
        photographers: ['Photographers', 'Skip the pen tool for the first 90% of the job.', ['Full-resolution output up to 8000 px', 'Use the cutout as a starting point for retouching', 'Works on JPG, PNG and WebP']],
        developers: ['Developers', 'The engine is swappable, and an API is on the roadmap.', ['Runs on your own server with an open model', 'Provider adapter for third-party APIs', 'Queue-based processing for larger jobs']],
    } }">
    <div class="mx-auto max-w-6xl px-4 py-16 sm:px-8 lg:px-12">
        <h2 id="who-h" x-reveal class="max-w-2xl text-3xl font-extrabold sm:text-4xl">One tool. A lot of different jobs.</h2>

        <div role="tablist" aria-label="Who it's for" class="mt-8 flex flex-wrap gap-2">
            <template x-for="(v, k) in tabs" :key="k">
                <button type="button" role="tab" :id="'tab-' + k" :aria-selected="tab === k" :aria-controls="'panel-' + k" @click="tab = k"
                        :class="tab === k ? 'bg-deep text-white' : 'border border-ink/25 hover:bg-white/60'" class="tap min-h-11 rounded-full px-5 text-sm font-semibold transition-colors" x-text="v[0]"></button>
            </template>
        </div>

        <div class="mt-8 min-h-[190px]" role="tabpanel" :aria-labelledby="'tab-' + tab" :id="'panel-' + tab">
            <template x-for="(v, k) in tabs" :key="k">
                <div x-show="tab === k" x-transition.opacity.duration.300ms class="grid gap-6 lg:grid-cols-[1fr_1.2fr]">
                    <p class="font-display text-2xl font-bold leading-snug sm:text-3xl" x-text="v[1]"></p>
                    <ul class="space-y-3">
                        <template x-for="line in v[2]" :key="line">
                            <li class="flex gap-3 border-t border-ink/20 pt-3"><span aria-hidden="true" class="font-bold text-accent-dark">✓</span><span x-text="line"></span></li>
                        </template>
                    </ul>
                </div>
            </template>
        </div>
    </div>
</section>

{{-- How it works --}}
<section class="bg-surface" aria-labelledby="how"><div class="mx-auto max-w-6xl px-4 py-16 sm:px-8 lg:px-12">
    <div class="grid gap-10 lg:grid-cols-[1fr_2fr]">
        <h2 id="how" x-reveal class="text-3xl font-extrabold sm:text-4xl">Three steps, about ten seconds.</h2>
        <ol class="grid gap-8 sm:grid-cols-3">
            @foreach ([['Upload', 'Drop in a JPG, PNG or WebP. Pasting from your clipboard works too.'], ['We cut it out', 'An AI model separates the subject from the background, edges included.'], ['Download', 'Keep it transparent or put it on any color, then save it in the format you need.']] as $i => [$t, $d])
                <li x-reveal style="--d:{{ $i * 120 }}ms">
                    <span class="font-display text-6xl font-extrabold text-coral" aria-hidden="true">{{ $i + 1 }}</span>
                    <h3 class="mt-1 text-xl font-bold">{{ $t }}</h3>
                    <p class="mt-2 text-muted">{{ $d }}</p>
                </li>
            @endforeach
        </ol>
    </div>
    </div>
</section>

{{-- Features --}}
<section class="bg-deep text-paper" aria-labelledby="feat-h">
    <div class="mx-auto max-w-6xl px-4 py-16 sm:px-8 lg:px-12">
        <h2 id="feat-h" x-reveal class="max-w-2xl text-3xl font-extrabold sm:text-4xl">Small tool. No fine print.</h2>
        <dl class="mt-10 grid gap-x-10 gap-y-8 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['Free to use', 'No account, no watermark. Fair-use limits keep it fast for everyone.'],
                ['Five export formats', 'PNG, WebP, JPG, GIF and SVG, straight from your browser.'],
                ['Any background', 'Stay transparent or pick from presets and a custom color.'],
                ['Private by default', 'Processed on our own server and deleted within '.config('bgremover.retention_hours').' hour. Never used for training.'],
                ['Before / after slider', 'Check the edges against your original before you download.'],
                ['Works on your phone', 'Large touch targets, paste and drag-and-drop where supported.'],
            ] as $i => [$t, $d])
                <div x-reveal style="--d:{{ ($i % 3) * 100 }}ms" class="border-t border-butter/40 pt-4">
                    <dt class="font-display text-xl font-bold text-butter">{{ $t }}</dt>
                    <dd class="mt-1 text-paper/80">{{ $d }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>

<section class="bg-mint" aria-labelledby="faq-h">
    <div class="mx-auto grid max-w-6xl gap-10 px-4 py-16 sm:px-8 lg:grid-cols-[1fr_2fr] lg:px-12">
        <div x-reveal>
            <h2 id="faq-h" class="text-3xl font-extrabold sm:text-4xl">Questions, answered.</h2>
            <p class="mt-3 text-muted">The short version. The rest is on the <a class="font-semibold text-accent-dark underline" href="{{ url('faq') }}">FAQ page</a>.</p>
        </div>
        <div>@include('pages.partials.faq-list', ['limit' => 4])</div>
    </div>
</section>

{{-- Closing CTA --}}
<section class="bg-paper">
    <div class="mx-auto max-w-6xl px-4 py-16 sm:px-8 lg:px-12">
        <div x-reveal class="relative overflow-hidden rounded-[2rem] bg-accent px-8 py-14 text-white sm:px-14">
            <div aria-hidden="true" class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-butter/90"></div>
            <div class="relative flex flex-col items-start justify-between gap-6 sm:flex-row sm:items-center">
                <h2 class="max-w-md text-3xl font-extrabold sm:text-5xl">Got a photo? Let's lose the background.</h2>
                <a href="#tool" class="tap inline-flex min-h-14 items-center rounded-full bg-butter px-9 text-lg font-bold text-ink transition-colors hover:bg-white">Upload image</a>
            </div>
        </div>
    </div>
</section>
@endsection
