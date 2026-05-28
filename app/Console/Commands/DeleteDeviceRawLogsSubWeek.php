<?php

namespace App\Console\Commands;

use App\Models\DeviceRawLog;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        $cutoff = now()->subDays($days);

        $query = DeviceRawLog::query()
            ->where('created_at', '<', $cutoff);

        if ($this->option('dry-run')) {
            $this->components->info("{$query->count()} device raw logs older than {$cutoff->toDateTimeString()} would be deleted.");

            return self::SUCCESS;
        }

        $deleted = 0;

        do {
            $ids = (clone $query)
                ->orderBy('id')
                ->limit($chunkSize)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            $deleted += DB::table((new DeviceRawLog)->getTable())
                ->whereIn('id', $ids)
                ->delete();
        } while ($ids->count() === $chunkSize);

        $this->components->info("Deleted {$deleted} device raw logs older than {$cutoff->toDateTimeString()}.");

        return self::SUCCESS;
    }
}
