<?php

namespace App\Services\Providers;

interface BackgroundRemovalProvider
{
    public function name(): string;

    /**
     * Remove the background from the image at $path and return PNG bytes with alpha.
     *
     * @throws ProviderException
     */
    public function remove(string $path): string;
}
