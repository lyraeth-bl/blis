<?php

namespace App\Filament\Resources\PicketSchedules;

use App\Filament\Resources\PicketSchedules\Pages\CreatePicketSchedule;
use App\Filament\Resources\PicketSchedules\Pages\EditPicketSchedule;
use App\Filament\Resources\PicketSchedules\Pages\ListPicketSchedules;
use App\Filament\Resources\PicketSchedules\Pages\ViewPicketSchedule;
use App\Filament\Resources\PicketSchedules\Schemas\PicketScheduleForm;
use App\Filament\Resources\PicketSchedules\Schemas\PicketScheduleInfolist;
use App\Filament\Resources\PicketSchedules\Tables\PicketSchedulesTable;
use App\Models\PicketSchedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class PicketScheduleResource extends Resource
{
    protected static ?string $model = PicketSchedule::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Jadwal Piket';

    protected static ?string $modelLabel = 'Jadwal Piket';

    protected static ?string $pluralModelLabel = 'Jadwal Piket';

    protected static UnitEnum|string|null $navigationGroup = 'ADMS';

    protected static ?string $recordTitleAttribute = 'day_label';

    protected static bool $isGloballySearchable = false;

    public static function form(Schema $schema): Schema
    {
        return PicketScheduleForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PicketScheduleInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PicketSchedulesTable::configure($table);
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->canManagePicketSchedules() ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user === null || $user->isAdmin()) {
            return $query;
        }

        return $query->whereIn('unit_id', $user->accessibleUnitIds());
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPicketSchedules::route('/'),
            'create' => CreatePicketSchedule::route('/create'),
            'view' => ViewPicketSchedule::route('/{record}'),
            'edit' => EditPicketSchedule::route('/{record}/edit'),
        ];
    }
}
