<?php

namespace App\Services;

/**
 * Validates provider output: must be a decodable PNG that carries an alpha channel.
 */
class ResultValidator
{
    /** @return array{0:int,1:int}|null width,height when valid */
    public function validate(string $bytes, ?int $expectW = null, ?int $expectH = null): ?array
    {
        if (strlen($bytes) < 33 || ! str_starts_with($bytes, "\x89PNG\r\n\x1a\n")) {
            return null;
        }

        $colorType = ord($bytes[25]);
        $hasTrns = str_contains(substr($bytes, 33, 4096), 'tRNS');
        if (! in_array($colorType, [4, 6], true) && ! $hasTrns) {
            return null;
        }

        $info = @getimagesizefromstring($bytes);
        if (! $info || $info[0] < 1 || $info[1] < 1) {
            return null;
        }

        return [$info[0], $info[1]];
    }
}
