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
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class StudentAttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentList;

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Absensi Siswa dan Siswi';

    protected static ?string $pluralModelLabel = 'Daftar Absensi Siswa dan Siswi';

    protected static ?string $navigationLabel = 'Siswa dan Siswi';

    protected static ?string $slug = 'student-attendances';

    protected static UnitEnum|string|null $navigationGroup = 'Absensi';

    protected static bool $shouldRegisterNavigation = true;

    public static function getModelLabel(): string
    {
        return 'Absensi Siswa dan Siswi';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('attendable_type', Student::class);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
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
