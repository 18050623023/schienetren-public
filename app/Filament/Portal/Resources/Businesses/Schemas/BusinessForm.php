<?php

namespace App\Filament\Portal\Resources\Businesses\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class BusinessForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Toggle::make('is_active')
                    ->label('Aktifkan Bisnis')
                    ->onColor('success')
                    ->offColor('danger')
                    ->default(true)
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('business_name')
                    ->required()
                    ->maxLength(255)
                    ->unique('tenants', 'business_name', ignoreRecord: true)
                    ->label('Nama Bisnis')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, $state, Set $set, Get $get) {
                        if ($operation === 'create' && empty($get('domain.domain'))) {
                            $slug = Str::slug($state);
                            $mainDomain = config('tenancy.main_domain')
                                ?? collect(config('tenancy.central_domains', []))->first(fn($d) => !in_array($d, ['127.0.0.1', 'localhost']))
                                ?? 'schienetren.test';
                            $set('domain.domain', $slug . '.' . $mainDomain);
                        }
                    }),

                Fieldset::make('Domain Bisnis')
                    ->columnSpan(2)
                    ->relationship('domain')
                    ->schema([
                        TextInput::make('domain')
                            ->label('Domain / Subdomain')
                            ->unique('domains', 'domain', ignoreRecord: true)
                            ->placeholder('Contoh: tokosaya.' . (config('tenancy.main_domain') ?? collect(config('tenancy.central_domains', []))->first(fn($d) => !in_array($d, ['127.0.0.1', 'localhost'])) ?? 'schienetren.test'))
                            ->regex('/^[a-zA-Z0-9.\-]+$/')
                            ->validationMessages([
                                'regex' => 'Format domain tidak valid. Hanya boleh menggunakan huruf, angka, titik, dan strip (-). Tanpa spasi.',
                            ])
                            ->required()
                    ]),

                

                Section::make('Addon Aktif')
                    ->columnSpanFull()
                    ->description('Daftar fitur tambahan yang sedang aktif pada bisnis ini.')
                    ->schema([
                        RepeatableEntry::make('subscriptionAddons') // Pastikan ada relasi subscriptionAddons() di model Tenant
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('addon.name')
                                    ->label('Nama Addon')
                                    ->weight('bold'),
                                TextEntry::make('ends_at')
                                    ->label('Berlaku Sampai')
                                    ->date('d F Y')
                                    ->badge()
                                    ->color(fn ($state) => $state->isPast() ? 'danger' : 'success'),
                            ])
                            ->columns(2)
                    ])

            ]);
    }
}
