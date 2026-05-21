<?php

namespace App\Filament\Resources\Units;

use App\Filament\Resources\Units\Pages\CreateUnit;
use App\Filament\Resources\Units\Pages\EditUnit;
use App\Filament\Resources\Units\Pages\ListUnits;
use App\Filament\Resources\Units\Pages\ViewUnit;
use App\Models\Unit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Unit';

    protected static ?string $modelLabel = 'Unit';

    protected static ?string $pluralModelLabel = 'Daftar Unit';

    protected static UnitEnum|string|null $navigationGroup = 'Pengaturan';

    protected static ?string $recordTitleAttribute = 'code';

    public static function form(Schema $schema): Schema
    {
        return Schemas\UnitForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return Schemas\UnitInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return Tables\UnitsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUnits::route('/'),
            'create' => CreateUnit::route('/create'),
            'view' => ViewUnit::route('/{record}'),
            'edit' => EditUnit::route('/{record}/edit'),
        ];
    }
}
