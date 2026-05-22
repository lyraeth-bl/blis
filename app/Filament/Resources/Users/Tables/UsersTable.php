<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\UserRole;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('role')
                    ->label('Role')
                    ->formatStateUsing(fn (UserRole|string $state): string => $state instanceof UserRole ? $state->label() : UserRole::from($state)->label())
                    ->badge()
                    ->color(fn (UserRole|string $state): string => match ($state instanceof UserRole ? $state : UserRole::from($state)) {
                        UserRole::Admin => 'danger',
                        UserRole::Hrd => 'warning',
                        UserRole::Tu => 'info',
                        UserRole::Staff => 'info',
                    })
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('units.display_name')
                    ->label('Unit')
                    ->badge()
                    ->separator(', ')
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Role')
                    ->options(collect(UserRole::cases())
                        ->mapWithKeys(fn (UserRole $role): array => [$role->value => $role->label()])
                        ->all()),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        true => 'Aktif',
                        false => 'Nonaktif',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::Users)
            ->emptyStateHeading('User masih kosong');
    }
}
