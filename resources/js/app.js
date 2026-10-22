import Alpine from 'alpinejs';

const ALLOWED = ['image/jpeg', 'image/png', 'image/webp'];

Alpine.data('remover', (cfg) => ({
    // idle | uploading | processing | done | error
    state: 'idle',
    progress: 0,
    error: '',
    file: null,
    job: null,
    view: 'result', // result | original | split
    bg: 'transparent',
    customColor: '#ffffff',
    dragging: false,
    localUrl: '',
    compare: 50,
    pollTimer: null,
    xhr: null,

    get maxMb() { return Math.round(cfg.maxKb / 1024); },
    get busy() { return this.state === 'uploading' || this.state === 'processing'; },
    get bgStyle() {
        if (this.bg === 'transparent') return '';
        return `background:${this.bg === 'custom' ? this.customColor : this.bg}`;
    },
    get statusText() {
        if (this.state === 'uploading') return `Uploading… ${this.progress}%`;
        if (this.state === 'processing') return 'Removing background…';
        if (this.state === 'done') return 'Background removed.';
        if (this.state === 'error') return this.error;
        return '';
    },
    humanSize(b) { return b > 1048576 ? (b / 1048576).toFixed(1) + ' MB' : Math.round(b / 1024) + ' KB'; },

    onDrop(e) { this.dragging = false; this.pick(e.dataTransfer.files[0]); },
    onPaste(e) {
        const f = [...(e.clipboardData?.files ?? [])].find((x) => x.type.startsWith('image/'));
        if (f) { e.preventDefault(); this.pick(f); }
    },
    onPick(e) { this.pick(e.target.files[0]); e.target.value = ''; },

    pick(f) {
        if (!f || this.busy) return;
        if (!ALLOWED.includes(f.type)) return this.fail('Unsupported file type. Please upload a JPG, PNG or WebP image.');
        if (f.size > cfg.maxKb * 1024) return this.fail(`That file is too large. The maximum size is ${this.maxMb} MB.`);
        this.file = f;
        this.setLocal(URL.createObjectURL(f));
        this.upload();
    },

    setLocal(u) { if (this.localUrl) URL.revokeObjectURL(this.localUrl); this.localUrl = u; },

    fail(msg) { this.stopPolling(); this.state = 'error'; this.error = msg; },

    upload() {
        this.state = 'uploading'; this.progress = 0; this.error = ''; this.job = null; this.view = 'result'; this.bg = 'transparent';
        const fd = new FormData();
        fd.append('image', this.file);
        const xhr = (this.xhr = new XMLHttpRequest());
        xhr.open('POST', cfg.storeUrl);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', cfg.csrf);
        xhr.upload.onprogress = (e) => { if (e.lengthComputable) this.progress = Math.round((e.loaded / e.total) * 100); };
        xhr.onload = () => {
            let body = {};
            try { body = JSON.parse(xhr.responseText); } catch (_) {}
            if (xhr.status === 202) {
                this.job = body; this.state = 'processing'; this.poll();
            } else if (xhr.status === 429) {
                this.fail('You have reached the hourly upload limit. Please try again later.');
            } else if (xhr.status === 419) {
                this.fail('Your session expired. Please reload the page and try again.');
            } else {
                this.fail(body?.errors?.image?.[0] || body?.message || 'Upload failed. Please try again.');
            }
        };
        xhr.onerror = () => this.fail('Network error during upload. Check your connection and retry.');
        xhr.onabort = () => {};
        xhr.send(fd);
    },

    poll() {
        let waited = 0;
        const tick = async () => {
            if (this.state !== 'processing') return;
            try {
                const r = await fetch(cfg.showUrl.replace('__ID__', this.job.id), { headers: { Accept: 'application/json' } });
                if (r.status === 404 || r.status === 410) return this.fail('This image has expired. Please upload it again.');
                const j = await r.json();
                this.job = j;
                if (j.status === 'completed') { this.state = 'done'; return; }
                if (j.status === 'failed') return this.fail(j.error);
            } catch (_) { /* transient network error: keep polling */ }
            waited += 1;
            if (waited > 150) return this.fail('Processing is taking too long. Please retry.');
            this.pollTimer = setTimeout(tick, 1000);
        };
        this.pollTimer = setTimeout(tick, 600);
    },

    stopPolling() { clearTimeout(this.pollTimer); },

    retry() { if (this.file) this.upload(); },

    async reset(deleteJob = true) {
        this.stopPolling();
        if (this.xhr && this.state === 'uploading') this.xhr.abort();
        const id = this.job?.id;
        this.state = 'idle'; this.file = null; this.job = null; this.error = ''; this.setLocal(''); this.compare = 50;
        if (deleteJob && id) {
            fetch(cfg.deleteUrl.replace('__ID__', id), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': cfg.csrf, Accept: 'application/json' } }).catch(() => {});
        }
    },

    format: 'png',
    exporting: false,
    exportError: '',
    formats: [
        { id: 'png', label: 'PNG', note: 'Lossless, keeps transparency' },
        { id: 'webp', label: 'WebP', note: 'Small file, keeps transparency' },
        { id: 'jpg', label: 'JPG', note: 'No transparency: uses the background color (white if none)' },
        { id: 'gif', label: 'GIF', note: 'Transparency is on/off only (no soft edges)' },
        { id: 'svg', label: 'SVG', note: 'Image wrapped in an SVG file (not vectorized)' },
    ],
    get formatNote() { return this.formats.find((f) => f.id === this.format).note; },

    fillColor() {
        if (this.bg === 'transparent') return null;
        return this.bg === 'custom' ? this.customColor : this.bg;
    },

    async drawResult(flatten) {
        const img = new Image();
        img.src = this.job.result_url;
        await img.decode();
        const c = document.createElement('canvas');
        c.width = img.naturalWidth; c.height = img.naturalHeight;
        const ctx = c.getContext('2d', { willReadFrequently: true });
        const fill = this.fillColor() ?? (flatten ? '#ffffff' : null);
        if (fill) { ctx.fillStyle = fill; ctx.fillRect(0, 0, c.width, c.height); }
        ctx.drawImage(img, 0, 0);
        return c;
    },

    save(blob, ext) {
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = (this.bg === 'transparent' ? 'background-removed.' : 'background-replaced.') + ext;
        document.body.appendChild(a); a.click(); a.remove();
        setTimeout(() => URL.revokeObjectURL(a.href), 5000);
    },

    toBlob(c, type, q) { return new Promise((res) => c.toBlob(res, type, q)); },

    async download() {
        if (this.exporting) return;
        this.exporting = true; this.exportError = '';
        try {
            const f = this.format;
            if (f === 'png' && this.bg === 'transparent') { window.location.href = this.job.download_url; return; }

            if (f === 'png') this.save(await this.toBlob(await this.drawResult(false), 'image/png'), 'png');
            else if (f === 'webp') this.save(await this.toBlob(await this.drawResult(false), 'image/webp', 0.95), 'webp');
            else if (f === 'jpg') this.save(await this.toBlob(await this.drawResult(true), 'image/jpeg', 0.95), 'jpg');
            else if (f === 'svg') {
                const c = await this.drawResult(false);
                const png = c.toDataURL('image/png');
                const svg = `<?xml version="1.0" encoding="UTF-8"?>\n<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="${c.width}" height="${c.height}" viewBox="0 0 ${c.width} ${c.height}"><image width="${c.width}" height="${c.height}" xlink:href="${png}"/></svg>`;
                this.save(new Blob([svg], { type: 'image/svg+xml' }), 'svg');
            } else if (f === 'gif') {
                const { GIFEncoder, quantize, applyPalette } = await import('gifenc');
                const c = await this.drawResult(false);
                const { data, width, height } = c.getContext('2d').getImageData(0, 0, c.width, c.height);
                const fill = this.fillColor();
                const fmt = fill ? 'rgb565' : 'rgba4444';
                const palette = quantize(data, 256, fill ? { format: fmt } : { format: fmt, oneBitAlpha: true });
                const index = applyPalette(data, palette, fmt);
                const gif = GIFEncoder();
                const opts = { palette };
                if (!fill) {
                    const t = palette.findIndex((p) => p[3] === 0);
                    if (t >= 0) { opts.transparent = true; opts.transparentIndex = t; }
                }
                gif.writeFrame(index, width, height, opts);
                gif.finish();
                this.save(new Blob([gif.bytes()], { type: 'image/gif' }), 'gif');
            }
        } catch (e) {
            this.exportError = 'Could not create that file format in your browser. Try PNG instead.';
        } finally {
            this.exporting = false;
        }
    },
}));

// Scroll-reveal: <div x-reveal style="--d:120ms"> fades/slides in once when it enters the viewport.
document.documentElement.classList.add('js');
const io = 'IntersectionObserver' in window ? new IntersectionObserver((entries) => {
    entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
}, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' }) : null;
Alpine.directive('reveal', (el) => {
    el.classList.add('reveal');
    io ? io.observe(el) : el.classList.add('in');
});

window.Alpine = Alpine;
Alpine.start();
