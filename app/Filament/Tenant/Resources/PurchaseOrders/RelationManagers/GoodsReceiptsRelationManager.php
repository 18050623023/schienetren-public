<?php

namespace App\Filament\Tenant\Resources\PurchaseOrders\RelationManagers;

use App\Enums\TenantFeature;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class GoodsReceiptsRelationManager extends RelationManager
{
    protected static string $relationship = 'goodsReceipts';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return tenant()->hasFeatureCode(TenantFeature::PURCHASE_ORDER);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('code')
                    ->label('Goods Receipt Code')
                    ->columnSpan(2),

                Section::make('Informasi Penerimaan')
                    ->columns(3)
                    ->columnSpan(2)
                    ->schema([
                        Forms\Components\Select::make('received_by')
                            ->relationship('receivedBy', 'name')
                            ->label('Received By'),

                        Forms\Components\DatePicker::make('date')
                            ->label('Received Date'),

                        Forms\Components\TextArea::make('notes')
                            ->label('Catatan'),

                        Forms\Components\FileUpload::make('attachment')
                            ->label('Lampiran Surat Jalan')
                            // ->disk('public')
                            ->directory('purchases/goods-receipts')
                            ->openable()
                            ->downloadable()
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpan(3),
                    ]),

                Section::make('Items')
                    ->columnSpanFull()
                    ->description('Item product yang diterima.')
                    ->schema([

                        Forms\Components\Repeater::make('items')
                            ->label('Products')
                            ->relationship('items')
                            ->reorderableWithDragAndDrop(false)
                            ->schema([

                            Forms\Components\TextInput::make('product_name')
                                ->label('Product')
                                ->formatStateUsing(fn ($record) =>
                                    $record->purchaseOrderItem?->product?->name
                                ),

                            Forms\Components\TextInput::make('variant_name')
                                ->label('Variant')
                                ->formatStateUsing(fn ($record) =>
                                    $record->purchaseOrderItem?->productVariant?->name
                                    ?? $record->purchaseOrderItem?->productVariant?->sku
                                ),
                                Forms\Components\TextInput::make('qty_received')
                                    ->numeric(),
                            ])

                            ->table([
                                TableColumn::make('Product'),
                                TableColumn::make('Variant'),
                                TableColumn::make('Received Quantity')
                                    ->width('150px'),
                            ])
                            ->required(),
                    ]),

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('code')
            ->columns([
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('receivedBy.name'),
                TextColumn::make('created_at')
                    ->date(),
                TextColumn::make('updated_at')
                    ->date(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'confirmed', 'posted' => 'success',
                        'canceled' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('attachment')
                    ->label('Surat Jalan')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Ada' : '-')
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([
                //
            ])
            // ->headerActions([
            //     CreateAction::make(),
            //     AssociateAction::make(),
            // ])
            ->recordActions([
                // EditAction::make(),
                ViewAction::make(),
                // DissociateAction::make(),
                // DeleteAction::make(),
            ])
            // ->toolbarActions([
            //     BulkActionGroup::make([
            //         DissociateBulkAction::make(),
            //         DeleteBulkAction::make(),
            //     ]),
            // ])
            ;
    }
}
