<?php

namespace App\Filament\Resources\Websites\Schemas;

use App\Models\Unit;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WebsiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Website')->schema([
                    TextInput::make('name')
                        ->label('Nama Website')
                        ->required(),

                    TextInput::make('url')
                        ->label('URL/Domain')
                        ->required()
                        ->url(),

                    // TextInput::make('username')
                    //     ->label('Username/Email'),

                    // TextInput::make('password')
                    //     ->label('Password')
                    //     ->password()
                    //     ->revealable()
                    //     ->nullable(),

                    Select::make('category')
                        ->label('Kategori')
                        ->native(false)
                        ->nullable()
                        ->options([
                            'Absensi & Kehadiran' => 'Absensi & Kehadiran',
                            'Akademik' => 'Akademik',
                            'Administrasi' => 'Administrasi',
                            'Jaringan & Infrastruktur' => 'Jaringan & Infrastruktur',
                            'Keuangan' => 'Keuangan',
                            'Kepegawaian' => 'Kepegawaian',
                            'Kesiswaan' => 'Kesiswaan',
                            'Layanan Google' => 'Layanan Google',
                            'Media Sosial & Komunikasi' => 'Media Sosial & Komunikasi',
                            'Pemerintahan & Dinas' => 'Pemerintahan & Dinas',
                            'Perangkat & IoT' => 'Perangkat & IoT',
                            'Perpustakaan' => 'Perpustakaan',
                            'Sistem Internal' => 'Sistem Internal',
                            'Lainnya' => 'Lainnya',
                        ]),

                    Select::make('unit_id')
                        ->label('Unit')
                        ->native(false)
                        ->nullable()
                        ->helperText('Kosongkan jika website berlaku untuk semua unit.')
                        ->options(fn (): array => Unit::query()
                            ->orderBy('name')
                            ->orderBy('campus')
                            ->get()
                            ->mapWithKeys(fn (Unit $unit): array => [$unit->id => $unit->display_name])
                            ->all()),
                ]),

                Section::make('Lainnya')->schema([
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3),

                    Toggle::make('is_private')
                        ->label('Private')
                        ->helperText('Jika aktif, website ini tidak akan ditampilkan di halaman publik.')
                        ->default(false),
                ]),
            ]);
    }
}
