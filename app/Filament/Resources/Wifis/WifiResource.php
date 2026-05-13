<?php

namespace App\Filament\Resources\Wifis;

use App\Filament\Resources\Wifis\Pages\CreateWifi;
use App\Filament\Resources\Wifis\Pages\EditWifi;
use App\Filament\Resources\Wifis\Pages\ListWifis;
use App\Filament\Resources\Wifis\Pages\ViewWifi;
use App\Filament\Resources\Wifis\Schemas\WifiForm;
use App\Filament\Resources\Wifis\Schemas\WifiInfolist;
use App\Filament\Resources\Wifis\Tables\WifisTable;
use App\Models\Wifi;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class WifiResource extends Resource
{
    protected static ?string $model = Wifi::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWifi;

    protected static ?string $modelLabel = 'Wifi';

    protected static ?string $navigationLabel = 'Wifi';

    protected static ?string $pluralModelLabel = 'Daftar Wifi';

    protected static UnitEnum|string|null $navigationGroup = 'Jaringan';

    protected static ?string $recordTitleAttribute = 'ssid';

    public static function form(Schema $schema): Schema
    {
        return WifiForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WifiInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WifisTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWifis::route('/'),
            'create' => CreateWifi::route('/create'),
            'view' => ViewWifi::route('/{record}'),
            'edit' => EditWifi::route('/{record}/edit'),
        ];
    }
}
