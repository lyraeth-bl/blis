<?php

namespace App\Filament\Resources\EmployeeAttendances;

use App\Filament\Resources\EmployeeAttendances\Pages\CreateEmployeeAttendance;
use App\Filament\Resources\EmployeeAttendances\Pages\EditEmployeeAttendance;
use App\Filament\Resources\EmployeeAttendances\Pages\ListEmployeeAttendances;
use App\Filament\Resources\EmployeeAttendances\Pages\ViewEmployeeAttendance;
use App\Filament\Resources\EmployeeAttendances\Schemas\EmployeeAttendanceForm;
use App\Filament\Resources\EmployeeAttendances\Schemas\EmployeeAttendanceInfolist;
use App\Filament\Resources\EmployeeAttendances\Tables\EmployeeAttendancesTable;
use App\Models\Attendance;
use App\Models\Employee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class EmployeeAttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?int $navigationSort = 11;

    protected static ?string $modelLabel = 'Absensi Karyawan';

    protected static ?string $pluralModelLabel = 'Daftar Absensi Karyawan';

    protected static ?string $navigationLabel = 'Absensi Karyawan';

    protected static ?string $slug = 'employee-attendances';

    protected static UnitEnum|string|null $navigationGroup = 'ADMS';

    protected static bool $shouldRegisterNavigation = true;

    public static function getModelLabel(): string
    {
        return 'Absensi Karyawan';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->where('attendable_type', Employee::class);
        $user = Auth::user();

        if ($user === null || $user->isAdmin()) {
            return $query;
        }

        return $query->whereHasMorph(
            'attendable',
            [Employee::class],
            fn (Builder $query): Builder => $query->whereIn('unit_id', $user->accessibleUnitIds()),
        );
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->canManageEmployees() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return EmployeeAttendanceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EmployeeAttendanceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeAttendancesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployeeAttendances::route('/'),
            'create' => CreateEmployeeAttendance::route('/create'),
            'view' => ViewEmployeeAttendance::route('/{record}'),
            'edit' => EditEmployeeAttendance::route('/{record}/edit'),
        ];
    }
}
