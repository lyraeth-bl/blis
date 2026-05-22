<?php

namespace App\Filament\Resources\SpoPostLogs;

use App\Filament\Resources\SpoPostLogs\Pages\ListSpoPostLogs;
use App\Filament\Resources\SpoPostLogs\Pages\ViewSpoPostLog;
use App\Filament\Resources\SpoPostLogs\Schemas\SpoPostLogInfolist;
use App\Filament\Resources\SpoPostLogs\Tables\SpoPostLogsTable;
use App\Models\SpoPostLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class SpoPostLogResource extends Resource
{
    protected static ?string $model = SpoPostLog::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Log Post SPO';

    protected static ?string $modelLabel = 'Log Post SPO';

    protected static ?string $pluralModelLabel = 'Log Post SPO';

    protected static UnitEnum|string|null $navigationGroup = 'Logs';

    public static function infolist(Schema $schema): Schema
    {
        return SpoPostLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SpoPostLogsTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->isAdmin() ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpoPostLogs::route('/'),
            'view' => ViewSpoPostLog::route('/{record}'),
        ];
    }
}
