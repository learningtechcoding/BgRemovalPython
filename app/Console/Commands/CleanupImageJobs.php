<?php

namespace App\Console\Commands;

use App\Models\ImageJob;
use Illuminate\Console\Command;

class CleanupImageJobs extends Command
{
    protected $signature = 'images:cleanup';
    protected $description = 'Delete expired uploads/results and old job metadata';

    public function handle(): int
    {
        $count = 0;
        ImageJob::where('expires_at', '<', now())->where('status', '!=', ImageJob::EXPIRED)
            ->chunkById(100, function ($jobs) use (&$count) {
                foreach ($jobs as $job) {
                    $job->deleteFiles();
                    $job->update(['status' => ImageJob::EXPIRED, 'original_storage_key' => null, 'result_storage_key' => null]);
                    $count++;
                }
            });

        // Drop metadata a day after expiry.
        ImageJob::where('status', ImageJob::EXPIRED)->where('expires_at', '<', now()->subDay())->delete();

        $this->info("Expired {$count} job(s).");

        return self::SUCCESS;
    }
}
