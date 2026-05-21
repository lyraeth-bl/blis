<?php

namespace App\Filament\Support\Widgets;

use App\Models\Attendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

abstract class AverageAttendanceTimeOverview extends BaseWidget
{
    protected ?string $pollingInterval = null;

    /**
     * @return class-string<Model>
     */
    abstract protected function getAttendableType(): string;

    abstract protected function getAttendanceSubjectLabel(): string;

    protected function getStats(): array
    {
        $query = Attendance::query()
            ->where('attendable_type', $this->getAttendableType())
            ->whereDate('date', today());

        $user = Auth::user();

        if ($user !== null && ! $user->isAdmin()) {
            $query->whereHasMorph(
                'attendable',
                [$this->getAttendableType()],
                fn (Builder $query): Builder => $query->whereIn('unit_id', $user->accessibleUnitIds()),
            );
        }

        $averages = $query
            ->selectRaw('
                AVG(TIME_TO_SEC(check_in)) as average_check_in_seconds,
                AVG(TIME_TO_SEC(check_out)) as average_check_out_seconds,
                COUNT(check_in) as check_in_count,
                COUNT(check_out) as check_out_count
            ')
            ->first();

        return [
            Stat::make('Rata-rata Jam Masuk', $this->formatAverageTime($averages?->average_check_in_seconds))
                ->description($this->formatDescription((int) ($averages?->check_in_count ?? 0), 'check-in'))
                ->color('info'),

            Stat::make('Rata-rata Jam Pulang', $this->formatAverageTime($averages?->average_check_out_seconds))
                ->description($this->formatDescription((int) ($averages?->check_out_count ?? 0), 'check-out'))
                ->color('success'),
        ];
    }

    protected function formatAverageTime(mixed $seconds): string
    {
        if ($seconds === null) {
            return '-';
        }

        $roundedSeconds = (int) round((float) $seconds);
        $hours = intdiv($roundedSeconds, 3600);
        $minutes = intdiv($roundedSeconds % 3600, 60);

        return sprintf('%02d:%02d', $hours, $minutes);
    }

    protected function formatDescription(int $count, string $timeType): string
    {
        return sprintf(
            'Berdasarkan %d data %s %s hari ini',
            $count,
            $timeType,
            $this->getAttendanceSubjectLabel(),
        );
    }
}
