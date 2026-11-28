<?php

namespace App\Jobs;

use App\Facades\Storage;
use Exception;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DeleteBlob implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $manifest,
    ) {}

    public int $uniqueFor = 900;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 5;

    /**
     * The number of seconds to wait before retrying an exception.
     */
    public int $backoff = 10;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return md5($this->manifest);
    }

    /**
     * Execute the job.
     *
     * @throws Exception
     */
    public function handle(): void
    {
        if (trim($this->manifest) === '') {
            return;
        }

        Storage::delete($this->manifest);
    }
}
