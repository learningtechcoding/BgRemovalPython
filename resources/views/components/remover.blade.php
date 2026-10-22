{{-- Hero + live tool. State machine lives in resources/js/app.js (Alpine "remover"). --}}
@php $idle = "state === 'idle' || (state === 'error' && !file)"; @endphp
<section id="tool" aria-labelledby="tool-title"
    x-data="remover({
        maxKb: {{ config('bgremover.limits.max_file_kb') }},
        csrf: @js(csrf_token()),
        storeUrl: @js(route('image-jobs.store')),
        showUrl: @js(route('image-jobs.show', '__ID__')),
        deleteUrl: @js(route('image-jobs.destroy', '__ID__')),
    })"
    @paste.window="onPaste($event)"
    @dragover.window.prevent="dragging = true" @dragleave.window.prevent="if (!$event.relatedTarget) dragging = false" @drop.window.prevent="onDrop($event)"
    class="mx-auto max-w-6xl px-4 sm:px-8 lg:px-12">

    <div class="grid gap-10 lg:gap-14 py-10 sm:py-16 lg:grid-cols-[1.05fr_1fr] lg:items-center" :class="!({{ $idle }}) && 'lg:!grid-cols-1 !py-8'">

        {{-- Headline (hidden once an image is in play so the work gets the space) --}}
        <div x-show="{{ $idle }}" class="rise">
            <h1 id="tool-title" class="text-4xl sm:text-6xl font-extrabold leading-[1.02]">Cut out the subject. <span class="text-accent">Lose the background.</span></h1>
            <p class="mt-5 text-lg text-muted max-w-xl">Upload a photo and get a clean transparent PNG in seconds. No sign-up, no watermark, and your files are deleted within {{ config('bgremover.retention_hours') }} hour.</p>
            <ul class="mt-7 space-y-2 text-[15px]">
                <li class="flex gap-3"><span aria-hidden="true" class="text-accent font-bold">✓</span> Handles people, products, pets and logos</li>
                <li class="flex gap-3"><span aria-hidden="true" class="text-accent font-bold">✓</span> Preview on a checkerboard or swap in any color</li>
                <li class="flex gap-3"><span aria-hidden="true" class="text-accent font-bold">✓</span> Processed on our own server, never used for training</li>
            </ul>
        </div>

        <div>
            {{-- 1. Upload --}}
            <div x-show="{{ $idle }}"
                 :class="dragging ? 'border-accent bg-accent-soft' : 'border-line bg-surface'"
                 style="--d:150ms" class="rise rounded-3xl border-2 border-dashed p-8 sm:p-12 text-center shadow-[0_20px_50px_-30px_rgba(28,36,32,.35)] transition-colors">
                <p class="font-display text-2xl font-bold">Drop an image here</p>
                <label class="tap lift mt-5 inline-flex min-h-12 cursor-pointer items-center justify-center rounded-full bg-accent px-9 py-3.5 text-lg font-semibold text-white transition-colors hover:bg-accent-dark focus-within:outline focus-within:outline-3 focus-within:outline-offset-2 focus-within:outline-accent">
                    Upload image
                    <input type="file" class="sr-only" accept="image/jpeg,image/png,image/webp" @change="onPick($event)" data-testid="file-input">
                </label>
                <p class="mt-4 text-sm text-muted">or drag a file anywhere on this page, or press <kbd class="rounded border border-line bg-paper px-1.5 py-0.5 text-xs">Ctrl</kbd> + <kbd class="rounded border border-line bg-paper px-1.5 py-0.5 text-xs">V</kbd> to paste</p>
                <p class="mt-6 text-xs text-muted">JPG, PNG or WebP · up to {{ round(config('bgremover.limits.max_file_kb') / 1024) }} MB · {{ config('bgremover.limits.max_dimension') }}px max per side</p>
            </div>

            {{-- Screen-reader status --}}
            <div role="status" aria-live="polite" class="sr-only" x-text="statusText"></div>

            {{-- Error --}}
            <div x-show="state === 'error'" x-cloak role="alert" class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-coral/50 bg-[#fdeee8] p-4 text-[#7a2a12]">
                <p><strong>Something went wrong:</strong> <span x-text="error"></span></p>
                <div class="flex gap-2">
                    <button type="button" x-show="file" @click="retry()" class="min-h-11 rounded-full bg-coral px-5 text-sm font-semibold text-white">Try again</button>
                    <button type="button" @click="reset()" class="min-h-11 rounded-full border border-[#7a2a12]/40 px-5 text-sm font-semibold">Start over</button>
                </div>
            </div>

            {{-- 2. Live processing: the user's own image with a scanner sweeping over it --}}
            <div x-show="busy" x-cloak class="mx-auto max-w-3xl">
                <div class="relative overflow-hidden rounded-3xl border border-line bg-surface">
                    <img :src="localUrl" alt="" class="mx-auto max-h-[60vh] w-auto max-w-full select-none object-contain opacity-80">
                    <div x-show="state === 'processing'" class="scanline" aria-hidden="true"></div>
                </div>
                <div class="mt-5 text-center">
                    <p class="font-display text-xl font-bold" x-text="state === 'uploading' ? 'Uploading… ' + progress + '%' : 'Removing the background…'"></p>
                    <div class="mx-auto mt-3 h-2 max-w-sm overflow-hidden rounded-full bg-line" role="progressbar" aria-label="Progress" aria-valuemin="0" aria-valuemax="100" :aria-valuenow="state === 'uploading' ? progress : null">
                        <div class="h-full rounded-full bg-accent transition-[width] duration-300" :class="state === 'processing' && 'animate-pulse'" :style="`width:${state === 'uploading' ? progress : 100}%`"></div>
                    </div>
                    <button type="button" @click="reset()" class="mt-4 min-h-11 px-4 text-sm text-muted underline">Cancel</button>
                </div>
            </div>

            {{-- 3. Result: preview + download sidebar --}}
            <div x-show="state === 'done'" x-cloak class="grid gap-6 lg:grid-cols-[1fr_320px]">
                <div>
                    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                        <p class="truncate text-sm text-muted"><span class="font-semibold text-ink" x-text="file?.name"></span> · <span x-text="job ? job.width + '×' + job.height : ''"></span></p>
                        <div role="tablist" aria-label="Preview mode" class="inline-flex overflow-hidden rounded-full border border-line bg-surface text-sm">
                            <template x-for="t in [['result','Result'],['original','Original'],['compare','Before / after']]" :key="t[0]">
                                <button type="button" role="tab" :aria-selected="view === t[0]" @click="view = t[0]"
                                        :class="view === t[0] ? 'bg-ink text-paper' : 'hover:bg-paper'" class="min-h-11 px-4 font-medium transition-colors" x-text="t[1]"></button>
                            </template>
                        </div>
                    </div>

                    <div class="relative overflow-hidden rounded-3xl border border-line" :class="view !== 'original' && bg === 'transparent' ? 'checker' : 'bg-surface'" :style="view !== 'original' ? bgStyle : ''">
                        {{-- sizing image keeps the box at the natural aspect ratio --}}
                        <img :src="job?.original_url" alt="" class="invisible mx-auto max-h-[68vh] w-auto max-w-full object-contain" aria-hidden="true">

                        <img x-show="view === 'original'" :src="job?.original_url" alt="Original image" class="absolute inset-0 h-full w-full object-contain">
                        <img x-show="view !== 'original'" :src="job?.result_url" alt="Image with background removed" data-testid="result-img" class="absolute inset-0 h-full w-full object-contain">

                        {{-- before/after: the original is revealed on the left of the handle --}}
                        <template x-if="view === 'compare'">
                            <div>
                                <div class="absolute inset-0" :style="`clip-path: inset(0 ${100 - compare}% 0 0)`">
                                    <div class="absolute inset-0 bg-surface"></div>
                                    <img :src="job?.original_url" alt="" class="absolute inset-0 h-full w-full object-contain">
                                </div>
                                <div class="pointer-events-none absolute inset-y-0 w-0.5 bg-white shadow-[0_0_0_1px_rgba(0,0,0,.25)]" :style="`left:${compare}%`">
                                    <span class="absolute top-1/2 -ml-[17px] -mt-[17px] grid h-9 w-9 place-items-center rounded-full bg-white text-xs font-bold text-ink shadow-md" aria-hidden="true">⇆</span>
                                </div>
                                <input type="range" min="0" max="100" x-model.number="compare" class="compare-range" aria-label="Before and after comparison">
                            </div>
                        </template>
                    </div>
                </div>

                <aside class="space-y-6 self-start rounded-3xl border border-line bg-surface p-6 lg:sticky lg:top-6">
                    <div>
                        <label for="fmt" class="mb-1.5 block font-display text-base font-bold">Format</label>
                        <select id="fmt" x-model="format" class="mb-3 min-h-12 w-full rounded-xl border border-line bg-paper px-3 font-medium">
                            <template x-for="f in formats" :key="f.id"><option :value="f.id" x-text="f.label"></option></template>
                        </select>
                        <button type="button" @click="download()" :disabled="exporting" data-testid="download" class="tap flex min-h-14 w-full items-center justify-center rounded-full bg-accent px-6 text-lg font-semibold text-white transition-colors hover:bg-accent-dark disabled:opacity-60">
                            <span x-text="exporting ? 'Preparing…' : 'Download ' + format.toUpperCase()"></span>
                        </button>
                        <p class="mt-2 text-center text-xs text-muted" x-text="formatNote + ' · ' + (job?.width ?? '') + '×' + (job?.height ?? '')"></p>
                        <p x-show="exportError" x-cloak role="alert" class="mt-2 text-center text-sm font-medium text-coral" x-text="exportError"></p>
                    </div>

                    <fieldset>
                        <legend class="mb-3 font-display text-base font-bold">Background</legend>
                        <div class="flex flex-wrap gap-2.5">
                            <button type="button" @click="bg = 'transparent'" :aria-pressed="bg === 'transparent'" aria-label="Transparent" class="checker h-11 w-11 rounded-full border-2" :class="bg === 'transparent' ? 'border-accent ring-2 ring-accent/30' : 'border-line'"></button>
                            <template x-for="c in ['#ffffff','#1c2420','#e4572e','#f2b84b','#0b7a63','#3b6fd8','#c9a7e8','#f4c6d0']" :key="c">
                                <button type="button" @click="bg = c" :aria-pressed="bg === c" :aria-label="'Background color ' + c" :style="`background:${c}`" class="h-11 w-11 rounded-full border-2" :class="bg === c ? 'border-accent ring-2 ring-accent/30' : 'border-line'"></button>
                            </template>
                            <label class="relative grid h-11 w-11 cursor-pointer place-items-center overflow-hidden rounded-full border-2 border-dashed border-line text-lg text-muted" :class="bg === 'custom' && '!border-accent'">
                                <span aria-hidden="true">+</span><span class="sr-only">Custom color</span>
                                <input type="color" x-model="customColor" @input="bg = 'custom'" class="absolute inset-0 h-full w-full cursor-pointer opacity-0">
                            </label>
                        </div>
                    </fieldset>

                    <div class="space-y-2 border-t border-line pt-5">
                        <button type="button" @click="reset()" class="min-h-12 w-full rounded-full border border-line font-semibold transition-colors hover:bg-paper">Upload another image</button>
                        <p class="text-xs leading-relaxed text-muted">AI cutouts can struggle with fine hair, glass and subjects that match the background. Files are deleted automatically within {{ config('bgremover.retention_hours') }} hour.</p>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    {{-- Full-page drop hint --}}
    <div x-show="dragging && !busy" x-cloak class="pointer-events-none fixed inset-0 z-50 grid place-items-center bg-accent/20 backdrop-blur-sm">
        <p class="rounded-full bg-ink px-8 py-4 font-display text-xl font-bold text-paper">Drop to remove background</p>
    </div>
</section>
