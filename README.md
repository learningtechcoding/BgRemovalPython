# Background Studio

Laravel 13 + Blade + Tailwind 4 + Alpine. AI removal runs in a local Python service (rembg / U²-Net) behind a provider adapter; a remove.bg adapter is included.

## Run locally
1. `composer install && npm install && npm run build`
2. `php artisan migrate`
3. Python service, first time only (skip if `inference\.venv` already exists; recreating it fails while the service is running): `python -m venv inference\.venv` then `inference\.venv\Scripts\python -m pip install "rembg[cpu]" fastapi uvicorn python-multipart pillow`
4. `powershell -ExecutionPolicy Bypass -File .\start.ps1` → http://127.0.0.1:8090

The first start downloads the ~176 MB model.

## Configuration (.env)
`BG_PROVIDER=python|removebg`, `BG_PYTHON_URL`, `BG_PYTHON_TOKEN` (must match the service), `REMOVEBG_API_KEY`,
`BG_MAX_FILE_KB`, `BG_MAX_DIMENSION`, `BG_MAX_PIXELS`, `BG_UPLOADS_PER_HOUR`, `BG_RETENTION_HOURS`, `BG_CONTACT_EMAIL`.
Change `BG_PYTHON_TOKEN` in production.

## Notes
- Anonymous jobs are tied to the browser session; files are stored privately and expire (`images:cleanup`, run by the scheduler every 5 min).
- Tests: `php artisan test`.
- Privacy/Terms pages are templates and need legal review. Phase 2/3 items (accounts, bulk, billing) are not built.
