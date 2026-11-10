<?php

namespace App\Filament\Tenant\Pages;

use App\Enums\TenantFeature;
use App\Filament\Tenant\Widgets\LeaveBalanceStatWidget;
use App\Models\Tenant\Employee;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class LeaveBalancePage extends Page implements HasForms
{

    use InteractsWithForms;

    protected string $view = 'filament.tenant.pages.leave-balance-page';

    // public ?int $employeeId = null;
    public ?array $data = [];

    // canAccess
    public static function canAccess(): bool
    {
        return tenant()->hasFeatureCode(TenantFeature::LEAVE)
            && !Auth::user()->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']));
    }

    public function mount(): void
    {
        if( ! Auth::user()->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']))) {

            $this->form->fill([
                'employee_id' => Auth::user()->employees()->first()?->id,
            ]);

        }
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->label('Select Employee')
                    ->options(Employee::all()->pluck('user.name', 'id'))
                    // ->default(Auth::user()->employees?->id)
                    ->live()
                    ->disabled(fn () => ! Auth::user()->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin'])))
                    // ->afterStateUpdated(function ($state) {
                    //     // $this->dispatch('employee-selected', employeeId: $state);
                    //     dd(Auth::user()->employees);

                    // })
                    ,
            ])
            ->statePath('data');
    }

    // public function getWidgetsSchemaComponents(): array
    // {
    //     return [
    //         LeaveBalanceStatWidget::class,
    //         // AccountWidget::class,
    //         // WelcomeTenantWidget::class,
    //     ];
    // }


}
