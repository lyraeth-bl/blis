<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Models\Unit;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Karyawan')->schema([
                    TextInput::make('nip')
                        ->label('NIP')
                        ->required()
                        ->unique(ignoreRecord: true),

                    TextInput::make('name')
                        ->label('Nama')
                        ->required(),

                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->endsWith('@budiluhur.sch.id')
                        ->hint('Optional')
                        ->unique(ignoreRecord: true),

                    TextInput::make('position')
                        ->label('Jabatan')
                        ->hint('Optional'),

                    Select::make('unit_id')
                        ->label('Unit utama')
                        ->options(fn (): array => Unit::query()
                            ->when(
                                ! Auth::user()?->isAdmin(),
                                fn ($query) => $query->whereIn('id', Auth::user()?->accessibleUnitIds() ?? []),
                            )
                            ->orderBy('campus')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (Unit $unit): array => [$unit->id => $unit->display_name])
                            ->all())
                        ->native(false)
                        ->searchable()
                        ->required(),

                    Select::make('units')
                        ->label('Unit akses')
                        ->multiple()
                        ->native(false)
                        ->helperText('Unit utama otomatis ikut. Tambahkan unit lain jika karyawan perlu akses lintas unit.')
                        ->relationship(
                            name: 'units',
                            titleAttribute: 'name',
                            modifyQueryUsing: fn (Builder $query): Builder => $query
                                ->when(
                                    ! Auth::user()?->isAdmin(),
                                    fn (Builder $query): Builder => $query->whereIn('id', Auth::user()?->accessibleUnitIds() ?? []),
                                )
                                ->orderBy('name')
                                ->orderBy('campus'),
                        )
                        ->getOptionLabelFromRecordUsing(fn (Unit $record): string => $record->display_name)
                        ->preload()
                        ->searchable()
                        ->saveRelationshipsUsing(function (Select $component): void {
                            $unitIds = collect($component->getState() ?? [])
                                ->when($component->getRecord()?->unit_id, fn ($unitIds, int $unitId) => $unitIds->push($unitId))
                                ->map(fn (int|string $unitId): int => (int) $unitId)
                                ->unique()
                                ->values();

                            if (! Auth::user()?->isAdmin()) {
                                $allowedUnitIds = Auth::user()?->accessibleUnitIds() ?? collect();

                                abort_unless($unitIds->diff($allowedUnitIds)->isEmpty(), 403);
                            }

                            $component->getRelationship()->sync($unitIds->all());
                        }),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(2),
            ]);
    }
}
