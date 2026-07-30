<?php

namespace App\Jobs;

use App\Services\AssetSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncAssetIndicatorsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;

    public function __construct(
        private readonly ?string $ticker = null,
        private readonly string $type = 'all',
    ) {}

    public function handle(AssetSyncService $syncService): void
    {
        if ($this->ticker) {
            $syncService->syncSingle($this->ticker);
        } else {
            $syncService->sync(
                type: $this->type,
                force: false,
                maxHoursSinceUpdate: 8,
            );
        }
    }
}
