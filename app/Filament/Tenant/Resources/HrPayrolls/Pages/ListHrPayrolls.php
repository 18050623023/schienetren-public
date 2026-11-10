<?php

namespace App\Filament\Tenant\Resources\HrPayrolls\Pages;

use App\Filament\Tenant\Resources\HrPayrolls\HrPayrollResource;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Employee;
use App\Services\PayrollCalculationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;

class ListHrPayrolls extends ListRecords
{
    protected static string $resource = HrPayrollResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generatePayroll')
                ->label('Generate Gaji Bulanan')
                ->icon(Heroicon::OutlinedCalculator)
                ->color('primary')
                ->schema([
                    Grid::make(2)
                        ->schema([
                                
                            Select::make('branch_id')
                                ->label('Cabang')
                                ->options(Branch::pluck('name', 'id'))
                                ->default(fn () => session('current_branch_id'))
                                ->required()
                                ->searchable(),

                            TextInput::make('period')
                                ->label('Periode Bulan (Format: YYYY-MM)')
                                ->default(now()->format('Y-m'))
                                ->placeholder('Contoh: 2026-10')
                                ->required(),
                        ]),

                    Select::make('employee_id')
                        ->label('Target Karyawan (Opsional)')
                        ->helperText('Kosongkan untuk memproses seluruh karyawan aktif di cabang terpilih.')
                        ->options(Employee::with('user')->get()->mapWithKeys(fn($employee) => [$employee->id => $employee->user->name]))
                        ->searchable()
                        ->preload(),
                ])
                ->action(function (array $data) {
                    $service = app(PayrollCalculationService::class);
                    $payrolls = $service->generatePeriodPayroll(
                        (int) $data['branch_id'],
                        $data['period'],
                        !empty($data['employee_id']) ? (int) $data['employee_id'] : null
                    );

                    Notification::make()
                        ->title('Generate Gaji Selesai')
                        ->body("Berhasil menghitung dan memperbarui {$payrolls->count()} slip gaji karyawan.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
