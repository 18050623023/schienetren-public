<?php

namespace App\Filament\Tenant\Resources\StockOpnames\Tables;
use App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Actions\ActionStatus;
use App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Actions\StockOpnameAction;
use App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\AuditStockOpname;
use App\Filament\Tenant\Resources\StockOpnames\Pages\Audit\Support\ModalDescriptions;
use App\Filament\Tenant\Resources\StockOpnames\StockOpnameResource;
use App\Models\Tenant\Branch;
use App\Models\Tenant\StockOpname;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\View;
use Filament\Tables\Table;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\HtmlString;

class StockOpnamesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Created By')
                    ->searchable(),

                Tables\Columns\TextColumn::make('postedBy.name')
                    ->label('Posted By')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('cenceledBy.name')
                    ->label('Cenceled By')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Cabang')
                    ->searchable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Items')
                    ->counts('items'),

                // status
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        // 'draft' => 'gray',
                        'posted' => 'success',
                        'confirmed' => 'warning',
                        default => 'gray',
                    }),
                // Tables\Columns\TextColumn::make('created_at')
                //     ->label('Created')
                //     ->since(),
            ])
            ->filters([
                TrashedFilter::make(),
                Tables\Filters\Filter::make('branch')->form([
                    Select::make('branch_id')->options(Branch::all()->pluck('name','id')->toArray())
                ])
            ])
            ->recordActions([
                \Filament\Actions\ActionGroup::make([
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                    Action::make('post')
                        ->icon('heroicon-s-arrow-up-on-square-stack')
                        ->color('success')
                        ->label(fn (StockOpname $record) =>
                            $record->type === 'initial'
                                ? 'Post Initial'
                                : 'Post Audit'
                        )
                        ->visible(function (StockOpname $record) {
                            if ($record->type === 'regular' && ! tenant()->hasFeatureCode(\App\Enums\TenantFeature::STOCK_OPNAME)) {
                                return false;
                            }
                            return $record->status === 'draft';
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Confirm Posting Stock')
                        ->modalDescription(ModalDescriptions::stockAuditPost())
                        ->action(fn (StockOpname $record) =>
                            ActionStatus::initialStockPost($record)
                        ),

                    Action::make('edit')
                        ->icon('heroicon-s-pencil-square')
                        ->label('Edit')
                        ->visible(fn ($record) => in_array($record->status, ['draft', 'in_progress']) && $record->type === 'regular' && tenant()->hasFeatureCode(\App\Enums\TenantFeature::STOCK_OPNAME))
                        ->requiresConfirmation()
                        ->url(fn (StockOpname $record) =>
                            StockOpnameResource::getUrl('audit', [
                                'token' => Crypt::encryptString(
                                    $record->id . '|edit'
                                ),
                            ])
                        ),

                    Action::make('cancel')
                        ->icon('heroicon-s-x-mark')
                        ->label('Cancel')
                        ->color('danger')
                        ->visible(function (StockOpname $record) {
                            if ($record->type === 'regular' && ! tenant()->hasFeatureCode(\App\Enums\TenantFeature::STOCK_OPNAME)) {
                                return false;
                            }
                            return in_array($record->status, ['draft', 'in_progress']);
                        })
                        ->requiresConfirmation()
                        // ->modalHeading('Confirm Posting Stock')
                        ->modalDescription(fn (StockOpname $record) =>
                            $record->type === 'initial'
                                ? ModalDescriptions::initialStockCancel()
                                : ModalDescriptions::stockAuditCancel()
                        )
                        ->action(fn (StockOpname $record) =>
                            $record->type === 'initial'
                                ? ActionStatus::initialStockCancel($record)
                                : ActionStatus::stockAuditCancel($record)
                        )
                        ->modalHeading(fn (StockOpname $record) =>
                            $record->type === 'initial'
                                ? 'Initial Stock Detail'
                                : 'Stock Audit Detail'
                        ),

                    ViewAction::make()
                        ->icon('heroicon-s-eye')
                        ->label('View')
                        ->visible(function (StockOpname $record) {
                            if ($record->type === 'regular' && ! tenant()->hasFeatureCode(\App\Enums\TenantFeature::STOCK_OPNAME)) {
                                return false;
                            }
                            return true;
                        }),
                ]),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
