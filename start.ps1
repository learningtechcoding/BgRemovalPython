# Starts everything needed locally: inference service, queue worker, web server.
# Usage (from the app folder):  powershell -ExecutionPolicy Bypass -File .\start.ps1
$root = $PSScriptRoot
$env:BG_PYTHON_TOKEN = 'local-dev-token'
$env:BG_MODEL = 'birefnet-general-lite'

Start-Process -WindowStyle Minimized -WorkingDirectory "$root\inference" -FilePath "$root\inference\.venv\Scripts\python.exe" `
    -ArgumentList '-m', 'uvicorn', 'main:app', '--host', '127.0.0.1', '--port', '8001'
Start-Process -WindowStyle Minimized -WorkingDirectory $root -FilePath php -ArgumentList 'artisan', 'queue:work', '--tries=1', '--sleep=1'
Start-Process -WindowStyle Minimized -WorkingDirectory $root -FilePath php -ArgumentList 'artisan', 'schedule:work'
Write-Host 'Open http://127.0.0.1:8090 (web server runs below; Ctrl+C to stop it)'
php artisan serve --port=8090
