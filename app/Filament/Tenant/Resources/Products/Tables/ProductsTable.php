<?php

namespace App\Filament\Tenant\Resources\Products\Tables;

use App\Filament\Tenant\Resources\Products\ProductResource;
use App\Filament\Tenant\Resources\Stocks\StockResource;
use App\Models\Tenant\ProductSerial;
use App\Models\Tenant\Stock;
use Dom\Text;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Container\Attributes\DB;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                ImageColumn::make('image')
                    ->label('Gambar'),
                TextColumn::make('brand.name')
                    ->searchable(),
                TextColumn::make('categories.name')
                    ->label('Categories')
                    ->badge()
                    ->separator(', ')
                    ->grow()
                    ->searchable(),
                // ToggleColumn::make('has_sn'),
                TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->label('View Details'),
                    Action::make('ViewStock')
                        ->label('View Stock')
                        ->icon('heroicon-o-clipboard-document-check')
                        ->modalWidth('4xl')
                        ->modalHeading('Product Stock')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Close')
                        ->infolist(
                            ProductStockDetail::make()
                        ),
                    EditAction::make(),
                    DeleteAction::make(),

                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
