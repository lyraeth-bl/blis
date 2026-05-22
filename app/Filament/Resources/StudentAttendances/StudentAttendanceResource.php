<?php

namespace App\Filament\Resources\StudentAttendances;

use App\Filament\Resources\StudentAttendances\Pages\CreateStudentAttendance;
use App\Filament\Resources\StudentAttendances\Pages\EditStudentAttendance;
use App\Filament\Resources\StudentAttendances\Pages\ListStudentAttendances;
use App\Filament\Resources\StudentAttendances\Pages\ViewStudentAttendance;
use App\Filament\Resources\StudentAttendances\Schemas\StudentAttendanceForm;
use App\Filament\Resources\StudentAttendances\Schemas\StudentAttendanceInfolist;
use App\Filament\Resources\StudentAttendances\Tables\StudentAttendancesTable;
use App\Models\Attendance;
use App\Models\Student;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class StudentAttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static ?int $navigationSort = 12;

    protected static ?string $modelLabel = 'Absensi Siswa dan Siswi';

    protected static ?string $pluralModelLabel = 'Daftar Absensi Siswa dan Siswi';

    protected static ?string $navigationLabel = 'Absensi Siswa/i';

    protected static ?string $slug = 'student-attendances';

    protected static UnitEnum|string|null $navigationGroup = 'ADMS';

    protected static bool $shouldRegisterNavigation = true;

    public static function getModelLabel(): string
    {
        return 'Absensi Siswa dan Siswi';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->where('attendable_type', Student::class);
        $user = Auth::user();

        if ($user === null || $user->isAdmin()) {
            return $query;
        }

        return $query->whereHasMorph(
            'attendable',
            [Student::class],
            fn (Builder $query): Builder => $query->whereIn('unit_id', $user->accessibleUnitIds()),
        );
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->canManageStudents() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return StudentAttendanceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StudentAttendanceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StudentAttendancesTable::configure($table);
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
            'index' => ListStudentAttendances::route('/'),
            'create' => CreateStudentAttendance::route('/create'),
            'view' => ViewStudentAttendance::route('/{record}'),
            'edit' => EditStudentAttendance::route('/{record}/edit'),
        ];
    }
}
