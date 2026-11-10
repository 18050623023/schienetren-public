<?php

namespace App\Filament\Tenant\Resources\CustomerReceivables;

use App\Filament\Tenant\Resources\CustomerReceivables\Pages\ListCustomerReceivables;
use App\Filament\Tenant\Resources\CustomerReceivables\Tables\CustomerReceivablesTable;
use App\Models\Tenant\RepairInvoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CustomerReceivableResource extends Resource
{
    protected static ?string $model = RepairInvoice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCurrencyDollar;

    protected static ?string $recordTitleAttribute = 'invoice_number';

    protected static string|UnitEnum|null $navigationGroup = 'Finance';

    protected static ?string $navigationLabel = 'Piutang Pelanggan';

    protected static ?string $modelLabel = 'Piutang Pelanggan';

    protected static ?string $pluralModelLabel = 'Piutang Pelanggan';

    protected static ?string $slug = 'customer-receivables';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $query = RepairInvoice::where('remaining_amount', '>', 0)
            ->where('status', '!=', 'refunded');

        // $count = RepairInvoice::where('remaining_amount', '>', 0)
            // ->where('status', '!=', 'refunded')
            // ->count();
        if ($branchId = session('current_branch_id')) {
            $query->whereHas('repair', fn ($q) => $q->where('branch_id', $branchId));
        }

        $count = $query->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('remaining_amount', '>', 0)
            ->where('status', '!=', 'refunded')
            ->when(session('current_branch_id'), function (Builder $query, $branchId) {
                $query->whereHas('repair', fn ($q) => $q->where('branch_id', $branchId));
            })
            ->with(['repair.client', 'repair.branch', 'repairPayments.paymentMethod']);
    }

    public static function table(Table $table): Table
    {
        return CustomerReceivablesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomerReceivables::route('/'),
        ];
    }
}
