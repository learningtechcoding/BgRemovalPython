<?php

namespace App\Jobs;

use App\Models\ImageJob;
use App\Services\Providers\BackgroundRemovalProvider;
use App\Services\Providers\ProviderException;
use App\Services\ResultValidator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ProcessImageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 240;

    public function __construct(public int $imageJobId)
    {
    }

    public function handle(BackgroundRemovalProvider $provider, ResultValidator $validator): void
    {
        $job = ImageJob::find($this->imageJobId);
        if (! $job || $job->status !== ImageJob::QUEUED) {
            return;
        }

        $job->update([
            'status' => ImageJob::PROCESSING,
            'processing_started_at' => now(),
            'provider_name' => $provider->name(),
        ]);

        $disk = ImageJob::disk();
        $attempts = 2; // one retry, only for transient failures

        try {
            for ($i = 1; ; $i++) {
                try {
                    $png = $provider->remove($disk->path($job->original_storage_key));
                    break;
                } catch (ProviderException $e) {
                    if (! $e->transient || $i >= $attempts) {
                        throw $e;
                    }
                    sleep(2);
                }
            }

            $dims = $validator->validate($png);
            if (! $dims) {
                throw new ProviderException('invalid_output', 'Provider output is not a transparent PNG.');
            }

            $key = 'results/'.Str::random(40).'.png';
            $disk->put($key, $png);

            $job->update([
                'status' => ImageJob::COMPLETED,
                'result_storage_key' => $key,
                'result_width' => $dims[0],
                'result_height' => $dims[1],
                'processing_completed_at' => now(),
            ]);
        } catch (ProviderException $e) {
            $this->markFailed($job, $e->errorCode, $e->getMessage());
        } catch (Throwable $e) {
            $this->markFailed($job, 'internal_error', $e->getMessage());
        }
    }

    private function markFailed(ImageJob $job, string $code, string $detail): void
    {
        Log::warning('Image job failed', ['job' => $job->public_id, 'code' => $code, 'detail' => $detail]);
        $job->update([
            'status' => ImageJob::FAILED,
            'error_code' => $code,
            'processing_completed_at' => now(),
        ]);
    }
}

