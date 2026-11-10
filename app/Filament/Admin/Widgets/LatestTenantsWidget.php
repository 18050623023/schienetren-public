<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Tenant;
use Filament\Actions\Action;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class LatestTenantsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    // protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Tenant::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('business_name')
                    ->label('Business Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Owner Name'),
                Tables\Columns\TextColumn::make('subscription.plan.name')
                    ->label('Plan')
                    ->badge(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->headerActions([
                Action::make('view_all')
                    ->label('Lihat Semua')
                    ->url(fn(): string => route('filament.admin.resources.tenants.index'))
                    ->icon('heroicon-m-arrow-right')
            ])
            ->paginated(false);
    }
}
