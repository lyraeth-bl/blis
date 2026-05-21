<?php

namespace App\Filament\Resources\FingerprintDevices;

use App\Filament\Resources\FingerprintDevices\Pages\CreateFingerprintDevice;
use App\Filament\Resources\FingerprintDevices\Pages\EditFingerprintDevice;
use App\Filament\Resources\FingerprintDevices\Pages\ListFingerprintDevices;
use App\Filament\Resources\FingerprintDevices\Pages\ViewFingerprintDevice;
use App\Filament\Resources\FingerprintDevices\RelationManagers\EmployeesRelationManager;
use App\Filament\Resources\FingerprintDevices\RelationManagers\StudentsRelationManager;
use App\Filament\Resources\FingerprintDevices\Schemas\FingerprintDeviceForm;
use App\Filament\Resources\FingerprintDevices\Schemas\FingerprintDeviceInfolist;
use App\Filament\Resources\FingerprintDevices\Tables\FingerprintDevicesTable;
use App\Models\FingerprintDevice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class FingerprintDeviceResource extends Resource
{
    protected static ?string $model = FingerprintDevice::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Mesin Fingerprint';

    protected static ?string $modelLabel = 'Mesin Fingerprint';

    protected static ?string $pluralModelLabel = 'Daftar Mesin Fingerprint';

    protected static UnitEnum|string|null $navigationGroup = 'ADMS';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return FingerprintDeviceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return FingerprintDeviceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FingerprintDevicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            StudentsRelationManager::class,
            EmployeesRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFingerprintDevices::route('/'),
            'create' => CreateFingerprintDevice::route('/create'),
            'view' => ViewFingerprintDevice::route('/{record}'),
            'edit' => EditFingerprintDevice::route('/{record}/edit'),
        ];
    }
}
