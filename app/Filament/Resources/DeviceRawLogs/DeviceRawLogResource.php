<?php

namespace App\Filament\Resources\DeviceRawLogs;

use App\Filament\Resources\DeviceRawLogs\Pages\ListDeviceRawLogs;
use App\Filament\Resources\DeviceRawLogs\Pages\ViewDeviceRawLog;
use App\Filament\Resources\DeviceRawLogs\Schemas\DeviceRawLogInfolist;
use App\Filament\Resources\DeviceRawLogs\Tables\DeviceRawLogsTable;
use App\Models\DeviceRawLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class DeviceRawLogResource extends Resource
{
    protected static ?string $model = DeviceRawLog::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Log ADMS';

    protected static ?string $modelLabel = 'Log ADMS';

    protected static ?string $pluralModelLabel = 'Log ADMS';

    protected static UnitEnum|string|null $navigationGroup = 'Logs';

    public static function infolist(Schema $schema): Schema
    {
        return DeviceRawLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeviceRawLogsTable::configure($table);
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
            'index' => ListDeviceRawLogs::route('/'),
            'view' => ViewDeviceRawLog::route('/{record}'),
        ];
    }
}
