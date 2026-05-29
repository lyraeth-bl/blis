<?php

namespace App\Services;

use App\Models\DeviceRawLog;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DeviceRawLogPruner
{
    public function cutoff(int $days = 7): CarbonInterface
    {
        return now()->subDays(max(1, $days));
    }

    public function count(int $days = 7): int
    {
        return $this->baseQuery($days)->count();
    }

    public function delete(int $days = 7, int $chunkSize = 1000): int
    {
        $query = $this->baseQuery($days);
        $chunkSize = max(1, $chunkSize);
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

        return $deleted;
    }

    private function baseQuery(int $days): Builder
    {
        return DeviceRawLog::query()
            ->where('created_at', '<', $this->cutoff($days));
    }
}
