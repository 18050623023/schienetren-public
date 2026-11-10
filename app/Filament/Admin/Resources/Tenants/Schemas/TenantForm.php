<?php

namespace App\Filament\Admin\Resources\Tenants\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\DB;

class TenantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('business_name')
                    ->required()
                    ->maxLength(255)
                    ->unique('tenants', 'business_name', ignoreRecord: true)
                    ->label('Business Name')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, $state, Set $set, Get $get) {
                        // Hanya otomatis generate pada saat create dan jika domain masih kosong
                        if ($operation === 'create' && empty($get('domain.domain'))) {
                            $slug = \Illuminate\Support\Str::slug($state);
                            $mainDomain = config('tenancy.main_domain')
                                ?? collect(config('tenancy.central_domains', []))->first(fn($d) => !in_array($d, ['127.0.0.1', 'localhost']))
                                ?? 'schienetren.test';
                            $set('domain.domain', $slug . '.' . $mainDomain);
                        }
                    }),

                Fieldset::make('Domains')
                    ->relationship('domain')
                    ->schema([
                        TextInput::make('domain')
                            ->label('Domain/Subdomain')
                            ->unique('domains', 'domain', ignoreRecord: true)
                            ->placeholder('Contoh: tokosaya.' . (config('tenancy.main_domain') ?? collect(config('tenancy.central_domains', []))->first(fn($d) => !in_array($d, ['127.0.0.1', 'localhost'])) ?? 'schienetren.test'))
                            ->regex('/^[a-zA-Z0-9.\-]+$/')
                            ->validationMessages([
                                'regex' => 'Format domain tidak valid. Hanya boleh menggunakan huruf, angka, titik, dan strip (-). Tanpa spasi.',
                            ])
                            ->required()
                    ]),
                Select::make('user_id')
                    ->label('User Owner Tenant')
                    ->relationship('user', 'name')
                    ->visible(fn () => Auth::user()->hasRole('super_admin'))
                    ->preload()
                    ->searchable(),
                // TextInput::make('domain')
                //     ->label('Domain/Subdomain')
                //     ->unique('domains', 'domain')
                //     ->placeholder('contoh: mylaundry.test')
                //     ->helperText('Gunakan subdomain tenant, misal: tenant1.saaslaundry.test'),


                Select::make('subscription_id')
                    ->label('Active Subscription')
                    ->relationship(
                                'subscription',
                                'id',
                                modifyQueryUsing: function (Builder $query, Get $get) {
                                    $query->with(['user', 'plan']);
                                },
                            )
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->user?->name} - {$record->plan?->name}")
                    ->searchable()
                    ->preload(),
                
                Toggle::make('is_active')
                    ->required(),
                Section::make('Addon dan Feature Aktif')
                    ->columnSpanFull()
                    ->description('Daftar fitur tambahan dan limit fitur yang sedang aktif pada bisnis ini.')
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
                            ->columns(2),


                        RepeatableEntry::make('tenantFeatureLimits')
                            ->hiddenLabel()
                            ->state(function ($record) {
                                if (!$record) return [];
                                
                                try {
                                    return $record->run(function () {
                                        if (!\Illuminate\Support\Facades\Schema::hasTable('tenant_feature_limits')) {
                                            return [];
                                        }
                                        // Ambil data dan ubah menjadi array of array agar bisa dibaca oleh Infolist
                                        return DB::table('tenant_feature_limits')
                                            ->get()
                                            ->map(fn($item) => (array) $item)
                                            ->toArray();
                                    });
                                } catch (\Exception $e) {
                                    return [];
                                }
                            })
                            ->schema([
                                TextEntry::make('feature_code')
                                    ->label('Feature Code')
                                    ->weight('bold'),
                                TextEntry::make('feature_type')
                                    ->label('Type'),
                                TextEntry::make('max_value')
                                    ->label('Max Value')
                                    ->badge()
                                    ->color('primary'),
                                TextEntry::make('is_expired')
                                    ->label('Expired?')
                                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                                    ->badge()
                                    ->color(fn ($state) => $state ? 'danger' : 'success'),
                            ])
                            ->columns(4)
                    ]),
            ]);
    }
}
