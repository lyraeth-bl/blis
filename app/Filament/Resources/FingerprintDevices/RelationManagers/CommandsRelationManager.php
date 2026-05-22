<?php

namespace App\Filament\Resources\FingerprintDevices\RelationManagers;

use App\Models\FingerprintDevice;
use App\Models\FingerprintDeviceCommand;
use App\Services\AdmsCommandService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CommandsRelationManager extends RelationManager
{
    protected static string $relationship = 'commands';

    protected static ?string $title = 'Command ADMS';

    public function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('command_id')
                    ->label('Command ID')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        FingerprintDeviceCommand::ACTION_UPDATE_USER => 'Update User',
                        FingerprintDeviceCommand::ACTION_DELETE_USER => 'Delete User',
                        FingerprintDeviceCommand::ACTION_QUERY_USER => 'Query User',
                        FingerprintDeviceCommand::ACTION_UPDATE_FINGERPRINT_TEMPLATE => 'Upload Finger',
                        FingerprintDeviceCommand::ACTION_QUERY_FINGERPRINT_TEMPLATE => 'Query Finger',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        FingerprintDeviceCommand::ACTION_UPDATE_USER => 'success',
                        FingerprintDeviceCommand::ACTION_DELETE_USER => 'danger',
                        FingerprintDeviceCommand::ACTION_QUERY_USER => 'gray',
                        FingerprintDeviceCommand::ACTION_UPDATE_FINGERPRINT_TEMPLATE => 'info',
                        FingerprintDeviceCommand::ACTION_QUERY_FINGERPRINT_TEMPLATE => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('attendable.name')
                    ->label('User')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        FingerprintDeviceCommand::STATUS_PENDING => 'Pending',
                        FingerprintDeviceCommand::STATUS_SENT => 'Terkirim',
                        FingerprintDeviceCommand::STATUS_SUCCEEDED => 'Berhasil',
                        FingerprintDeviceCommand::STATUS_FAILED => 'Gagal',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        FingerprintDeviceCommand::STATUS_PENDING => 'warning',
                        FingerprintDeviceCommand::STATUS_SENT => 'info',
                        FingerprintDeviceCommand::STATUS_SUCCEEDED => 'success',
                        FingerprintDeviceCommand::STATUS_FAILED => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('return_code')
                    ->label('Return')
                    ->placeholder('-'),

                TextColumn::make('comparison_status')
                    ->label('Cek Data')
                    ->badge()
                    ->placeholder('-')
                    ->formatStateUsing(fn (?string $state, FingerprintDeviceCommand $record): string => match ($state) {
                        FingerprintDeviceCommand::COMPARISON_SYNCED => $record->action === FingerprintDeviceCommand::ACTION_QUERY_FINGERPRINT_TEMPLATE
                            ? 'Ditemukan'
                            : 'Sama',
                        FingerprintDeviceCommand::COMPARISON_DIFFERENT => 'Beda',
                        FingerprintDeviceCommand::COMPARISON_MISSING => 'Tidak Ada',
                        FingerprintDeviceCommand::COMPARISON_UNKNOWN => 'Belum Terbaca',
                        default => '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        FingerprintDeviceCommand::COMPARISON_SYNCED => 'success',
                        FingerprintDeviceCommand::COMPARISON_DIFFERENT => 'warning',
                        FingerprintDeviceCommand::COMPARISON_MISSING => 'danger',
                        FingerprintDeviceCommand::COMPARISON_UNKNOWN => 'gray',
                        default => 'gray',
                    }),

                TextColumn::make('comparison_details')
                    ->label('Perbedaan')
                    ->state(fn (FingerprintDeviceCommand $record): string => $this->formatComparisonDetails($record))
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('fingerprint_template_summary')
                    ->label('Template Finger')
                    ->state(fn (FingerprintDeviceCommand $record): string => $this->formatFingerprintTemplateSummary($record))
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('fingerprint_template_tmp')
                    ->label('TMP')
                    ->state(fn (FingerprintDeviceCommand $record): string => $this->formatFingerprintTemplateTmp($record))
                    ->limit(80)
                    ->copyable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('sent_at')
                    ->label('Dikirim')
                    ->dateTime()
                    ->placeholder('-'),

                TextColumn::make('replied_at')
                    ->label('Dibalas')
                    ->dateTime()
                    ->placeholder('-'),

                TextColumn::make('raw_reply')
                    ->label('Raw Reply')
                    ->limit(80)
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('command')
                    ->label('Command')
                    ->limit(80)
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('upload_fingerprint_templates')
                    ->label('Upload TMP')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('info')
                    ->visible(fn (FingerprintDeviceCommand $record): bool => $record->action === FingerprintDeviceCommand::ACTION_QUERY_FINGERPRINT_TEMPLATE
                        && ! empty($this->fingerprintTemplatesFrom($record)))
                    ->form(fn (FingerprintDeviceCommand $record): array => [
                        Select::make('target_device_id')
                            ->label('Device Tujuan')
                            ->options(fn (): array => $this->targetDeviceOptionsFor($record))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (FingerprintDeviceCommand $record, array $data): void {
                        $targetDevice = FingerprintDevice::query()->find($data['target_device_id']);
                        $attendable = $record->attendable;

                        if (! $targetDevice instanceof FingerprintDevice || ! $attendable instanceof Model) {
                            Notification::make()
                                ->title('Target device atau user tidak valid.')
                                ->danger()
                                ->send();

                            return;
                        }

                        $createdCount = 0;

                        try {
                            foreach ($this->fingerprintTemplatesFrom($record) as $template) {
                                app(AdmsCommandService::class)->queueUpdateFingerprintTemplate(
                                    device: $targetDevice,
                                    attendable: $attendable,
                                    fingerId: (int) $template['FID'],
                                    template: (string) $template['TMP'],
                                    valid: (string) ($template['Valid'] ?? '1') === '1',
                                    requestedBy: Auth::user(),
                                );

                                $createdCount++;
                            }

                            Notification::make()
                                ->title("{$createdCount} command upload TMP dibuat")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error: '.$e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->emptyStateIcon(Heroicon::CommandLine)
            ->emptyStateHeading('Belum ada command ADMS')
            ->emptyStateDescription('Command push, hapus, dan query user akan muncul di sini.');
    }

    private function formatComparisonDetails(FingerprintDeviceCommand $record): string
    {
        $details = $record->comparison_details;

        if (empty($details)) {
            return '-';
        }

        return collect($details)
            ->map(fn (array $values, string $field): string => "{$field}: web={$values['web']}; device={$values['device']}")
            ->join(' | ');
    }

    private function formatFingerprintTemplateSummary(FingerprintDeviceCommand $record): string
    {
        $templates = $this->fingerprintTemplatesFrom($record);

        if (empty($templates)) {
            return '-';
        }

        return collect($templates)
            ->map(fn (array $template): string => 'FID '.($template['FID'] ?? '-')
                .' | Size '.($template['Size'] ?? '-')
                .' | Valid '.($template['Valid'] ?? '-'))
            ->join(' ; ');
    }

    private function formatFingerprintTemplateTmp(FingerprintDeviceCommand $record): string
    {
        $templates = $this->fingerprintTemplatesFrom($record);

        if (empty($templates)) {
            return '-';
        }

        return collect($templates)
            ->map(fn (array $template): string => 'FID='.($template['FID'] ?? '-').' TMP='.($template['TMP'] ?? ''))
            ->join("\n");
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function fingerprintTemplatesFrom(FingerprintDeviceCommand $record): array
    {
        if ($record->action !== FingerprintDeviceCommand::ACTION_QUERY_FINGERPRINT_TEMPLATE) {
            return [];
        }

        $payload = $record->reply_payload ?? [];

        if (isset($payload['templates']) && is_array($payload['templates'])) {
            return collect($payload['templates'])
                ->filter(fn (mixed $template): bool => is_array($template) && filled($template['TMP'] ?? null))
                ->values()
                ->all();
        }

        if (filled($payload['TMP'] ?? null)) {
            return [$payload];
        }

        return [];
    }

    /**
     * @return array<int, string>
     */
    private function targetDeviceOptionsFor(FingerprintDeviceCommand $record): array
    {
        $attendable = $record->attendable;

        if (! $attendable instanceof Model) {
            return [];
        }

        return FingerprintDevice::query()
            ->whereKeyNot($record->fingerprint_device_id)
            ->where('type', $record->fingerprintDevice->type)
            ->whereHas('units', fn ($query) => $query->whereKey($attendable->getAttribute('unit_id')))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
