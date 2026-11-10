<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ColorPicker;
use App\Models\Setting;
use Spatie\Permission\Models\Role;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;
use App\Filament\Forms\Components\GeofenceMap;
use UnitEnum;

class SettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;
    protected static string|null $navigationLabel = 'Pengaturan Umum';
    protected static string|UnitEnum|null $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 4;

    protected string $view = 'filament.admin.pages.settings-page';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'central_bypass_roles' => settingCentral('central_bypass_roles', ['super_admin']),
            'central_technical_roles' => settingCentral('central_technical_roles', ['technician']),
            'color_primary' => settingCentral('color_primary', '#007bff'),
            'brand_name' => settingCentral('brand_name', 'Schienetren Central'),
            'global_admin_fee' => settingCentral('global_admin_fee', '0'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                TextInput::make('brand_name')
                    ->label('Brand Name')
                    ->placeholder('Schienetren Central'),

                ColorPicker::make('color_primary')
                    ->label('Warna Utama Website')
                    ->nullable(),

                TextInput::make('global_admin_fee')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('Rp'),

                Select::make('central_bypass_roles')
                    ->label('Role Pengecualian (Unlimited Access)')
                    ->multiple()
                    ->options(Role::pluck('name', 'name'))
                    ->helperText('Role yang memiliki akses penuh untuk admin sentral.'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        Notification::make()
            ->title('Pengaturan berhasil disimpan')
            ->success()
            ->send();

        redirect(request()->header('Referer') ?: static::getUrl());
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole(settingCentral('central_bypass_roles', ['super_admin', 'owner', 'admin'])) ?? false;
    }
}
