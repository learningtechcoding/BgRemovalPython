<?php

namespace App\Services\Providers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class RemoveBgProvider implements BackgroundRemovalProvider
{
    public function name(): string
    {
        return 'removebg';
    }

    public function remove(string $path): string
    {
        $cfg = config('bgremover.removebg');
        if (! $cfg['key']) {
            throw new ProviderException('provider_not_configured', 'REMOVEBG_API_KEY is not set.');
        }

        try {
            $response = Http::withHeaders(['X-Api-Key' => $cfg['key']])
                ->timeout($cfg['timeout'])
                ->attach('image_file', fopen($path, 'r'), 'image')
                ->post('https://api.remove.bg/v1.0/removebg', ['size' => 'auto', 'format' => 'png']);
        } catch (ConnectionException) {
            throw new ProviderException('provider_unavailable', 'remove.bg unreachable.', true);
        }

        if ($response->status() === 402 || $response->status() === 429) {
            throw new ProviderException('provider_quota', 'Provider quota/rate limit.', $response->status() === 429);
        }
        if ($response->failed()) {
            throw new ProviderException('provider_error', 'remove.bg error '.$response->status(), $response->serverError());
        }

        return $response->body();
    }
}
