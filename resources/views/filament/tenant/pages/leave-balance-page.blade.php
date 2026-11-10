<x-filament-panels::page>
    {{-- Page content --}}

    {{ $this->form }}

    @livewire(
        \App\Filament\Tenant\Widgets\LeaveBalanceStatWidget::class,
        [
            'employeeId' => $this->data['employee_id'] ?? null,
        ],
        key('leave-balance-widget-' . ($this->data['employee_id'] ?? 'all'))
    )

    @livewire(
        \App\Filament\Tenant\Widgets\RecentLeaveRequestsWidget::class,
        [
            'employeeId' => $this->data['employee_id'] ?? null,
        ],
        key('leave-balance-table-' . ($this->data['employee_id'] ?? 'all'))
    )


    {{-- @livewire(
        \App\Filament\Tenant\Resources\LeaveRequests\LeaveRequestResource::getTableColumns(),
        // \App\Filament\Widgets\LeaveBalanceTable::class,
        ['employeeId' => $this->employeeId]
    ) --}}
</x-filament-panels::page>
