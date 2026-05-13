<?php

namespace App\Filament\Support\Widgets;

use App\Models\Attendance;
use Carbon\CarbonInterface;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class AttendanceStatusLineChart extends ChartWidget
{
    protected int|string|array $columnSpan = 'full';

    protected ?string $pollingInterval = null;

    protected ?string $maxHeight = '320px';

    /**
     * @return class-string<Model>
     */
    abstract protected function getAttendableType(): string;

    protected function getData(): array
    {
        $startDate = today()->subDays(13);
        $endDate = today();
        $dates = Collection::times(14, fn (int $day): CarbonInterface => $startDate->copy()->addDays($day - 1));
        $statuses = [
            'present' => [
                'label' => 'Hadir',
                'borderColor' => '#22c55e',
                'backgroundColor' => 'rgba(34, 197, 94, 0.16)',
            ],
            'late' => [
                'label' => 'Terlambat',
                'borderColor' => '#f59e0b',
                'backgroundColor' => 'rgba(245, 158, 11, 0.16)',
            ],
            'absent' => [
                'label' => 'Tidak Masuk',
                'borderColor' => '#ef4444',
                'backgroundColor' => 'rgba(239, 68, 68, 0.16)',
            ],
        ];

        $counts = Attendance::query()
            ->where('attendable_type', $this->getAttendableType())
            ->whereBetween('date', [$startDate, $endDate])
            ->whereIn('status', array_keys($statuses))
            ->selectRaw('date, status, COUNT(*) as total')
            ->groupBy('date', 'status')
            ->get()
            ->groupBy(fn (Attendance $attendance): string => $attendance->date->format('Y-m-d'));

        return [
            'datasets' => collect($statuses)
                ->map(fn (array $config, string $status): array => [
                    'label' => $config['label'],
                    'data' => $this->getStatusData($dates, $counts, $status),
                    'borderColor' => $config['borderColor'],
                    'backgroundColor' => $config['backgroundColor'],
                    'pointBackgroundColor' => $config['borderColor'],
                    'pointBorderColor' => $config['borderColor'],
                    'tension' => 0.3,
                ])
                ->values()
                ->all(),
            'labels' => $dates
                ->map(fn (CarbonInterface $date): string => $date->format('d/m'))
                ->all(),
        ];
    }

    protected function getStatusData(Collection $dates, Collection $counts, string $status): array
    {
        return $dates
            ->map(function (CarbonInterface $date) use ($counts, $status): int {
                $attendance = $counts
                    ->get($date->format('Y-m-d'), collect())
                    ->firstWhere('status', $status);

                return (int) ($attendance?->total ?? 0);
            })
            ->all();
    }

    protected function getType(): string
    {
        return 'line';
    }
}
