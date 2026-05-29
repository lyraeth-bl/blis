<?php

namespace App\Filament\Resources\Employees;

use App\Filament\Resources\Employees\Pages\CreateEmployee;
use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Filament\Resources\Employees\Pages\ViewEmployee;
use App\Filament\Resources\Employees\RelationManagers\FingerprintDevicesRelationManager;
use App\Filament\Resources\Employees\Schemas\EmployeeForm;
use App\Filament\Resources\Employees\Schemas\EmployeeInfolist;
use App\Filament\Resources\Employees\Tables\EmployeesTable;
use App\Models\Employee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Karyawan';

    protected static ?string $modelLabel = 'Karyawan';

    protected static ?string $pluralModelLabel = 'Daftar Karyawan';

    protected static UnitEnum|string|null $navigationGroup = 'ADMS';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmployeeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeesTable::configure($table);
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->canManageEmployees() ?? false;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'nip'];
    }

    /**
     * @return array<string, string>
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        if (! $record instanceof Employee) {
            return [];
        }

        return [
            'NIP' => $record->nip ?: '-',
            'Jabatan' => $record->position ?: '-',
            'Unit' => $record->accessibleUnitsLabel(),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()
            ->with(['unitModel:id,name,campus', 'units:id,name,campus']);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user === null || $user->isAdmin()) {
            return $query;
        }

        $accessibleUnitIds = $user->accessibleUnitIds();

        return $query->where(function (Builder $query) use ($accessibleUnitIds): void {
            $query->whereIn('unit_id', $accessibleUnitIds)
                ->orWhereHas('units', fn (Builder $query): Builder => $query->whereKey($accessibleUnitIds));
        });
    }

    public static function getRelations(): array
    {
        return [
            FingerprintDevicesRelationManager::class,
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'view' => ViewEmployee::route('/{record}'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }
}
