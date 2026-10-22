<?php

namespace App\Services\Providers;

use RuntimeException;

class ProviderException extends RuntimeException
{
    public function __construct(public readonly string $errorCode, string $message = '', public readonly bool $transient = false)
    {
        parent::__construct($message ?: $errorCode);
    }
}
