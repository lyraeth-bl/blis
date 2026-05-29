<?php

namespace App\Console\Commands;

use App\Services\DeviceRawLogPruner;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('adms:prune-device-raw-logs {--days=7 : Delete logs older than this many days} {--chunk=1000 : Number of rows to delete per query} {--dry-run : Count matching logs without deleting them}')]
#[Description('Delete old ADMS device raw logs.')]
class DeleteDeviceRawLogsSubWeek extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $chunkSize = max(1, (int) $this->option('chunk'));
        $pruner = app(DeviceRawLogPruner::class);
        $cutoff = $pruner->cutoff($days);

        if ($this->option('dry-run')) {
            $this->components->info("{$pruner->count($days)} device raw logs older than {$cutoff->toDateTimeString()} would be deleted.");

            return self::SUCCESS;
        }

        $deleted = $pruner->delete($days, $chunkSize);

        $this->components->info("Deleted {$deleted} device raw logs older than {$cutoff->toDateTimeString()}.");

        return self::SUCCESS;
    }
}
