<?php

namespace App\Services\Providers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class PythonModelProvider implements BackgroundRemovalProvider
{
    public function name(): string
    {
        return 'python';
    }

    public function remove(string $path): string
    {
        $cfg = config('bgremover.python');

        try {
            $response = Http::withHeaders(['X-Api-Token' => $cfg['token']])
                ->timeout($cfg['timeout'])
                ->attach('file', fopen($path, 'r'), 'image')
                ->post(rtrim($cfg['url'], '/').'/remove');
        } catch (ConnectionException) {
            throw new ProviderException('provider_unavailable', 'Inference service unreachable.', true);
        }

        if ($response->status() === 422) {
            throw new ProviderException('unprocessable_image', 'Image could not be processed.');
        }
        if ($response->failed()) {
            throw new ProviderException('provider_error', 'Inference service error '.$response->status(), $response->serverError());
        }

        return $response->body();
    }
}
