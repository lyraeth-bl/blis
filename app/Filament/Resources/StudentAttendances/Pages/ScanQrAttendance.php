<?php

namespace App\Filament\Resources\StudentAttendances\Pages;

use App\Filament\Resources\StudentAttendances\StudentAttendanceResource;
use App\Services\QrScanService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Livewire\Attributes\On;

class ScanQrAttendance extends Page
{
    protected static string $resource = StudentAttendanceResource::class;

    protected string $view = 'filament.resources.student-attendances.pages.scan-qr-attendance';

    protected static ?string $title = 'Scan QR Absensi';

    public string $token = '';

    /** Data siswa terakhir yang berhasil di-scan */
    public ?array $lastScan = null;

    /** Pesan error scan terakhir */
    public ?string $lastError = null;

    public function mount(): void
    {
        $this->token = '';
    }

    #[On('qr-scanned')]
    public function processToken(string $token): void
    {
        $this->token = $token;
        $this->lastError = null;
        $this->lastScan = null;

        if (blank($token)) {
            return;
        }

        try {
            $result = app(QrScanService::class)->process($token);

            $this->lastScan = [
                'nis' => $result['student']->nis,
                'nama' => $result['student']->name,
                'unit' => $result['student']->unit,
                'kelas' => $result['student']->class,
                'tipe' => $result['tipe'] === 'checkIn' ? 'Check In' : 'Check Out',
                'waktu' => now()->format('H:i:s'),
                'status' => match ($result['status']) {
                    'present' => 'Hadir',
                    'late' => 'Terlambat',
                    default => $result['status'],
                },
            ];

            Notification::make()
                ->title('Absensi Berhasil')
                ->body("{$result['student']->name} — {$this->lastScan['tipe']}")
                ->success()
                ->send();

        } catch (\RuntimeException $e) {
            $this->lastError = $e->getMessage();

            Notification::make()
                ->title('Scan Gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }

        // Reset token input setelah diproses
        $this->token = '';
        $this->dispatch('reset-input');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali ke Daftar')
                ->icon('heroicon-o-arrow-left')
                ->url(StudentAttendanceResource::getUrl('index'))
                ->color('gray'),
        ];
    }
}
