<?php

namespace App\Filament\Tenant\Resources\Products\RelationManagers;

use App\Filament\Tenant\Resources\Products\ProductResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Form;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $relatedResource = ProductResource::class;
    protected static ?string $recordTitleAttribute = 'variant_name';

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
            TextInput::make('variant_name')
                ->label('Variant Name')
                ->required(),

            TextInput::make('sku')
                ->label('SKU')
                ->unique(ignoreRecord: true),

            TextInput::make('price')
                ->numeric()
                ->prefix('Rp')
                ->required(),

            TextInput::make('stock')
                ->numeric()
                ->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        // return $table
        //     ->headerActions([
        //         CreateAction::make(),
        //     ]);
         return $table->columns([
            TextColumn::make('variant_name')->label('Variant'),
            TextColumn::make('sku'),
            TextColumn::make('price')->money('IDR'),
            TextColumn::make('stock'),
        ])
        ->headerActions([
            CreateAction::make(),
        ])
        ->actions([
            EditAction::make(),
            DeleteAction::make(),
        ]);
    }
}
