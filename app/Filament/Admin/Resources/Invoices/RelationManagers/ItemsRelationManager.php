<?php

namespace App\Filament\Admin\Resources\Invoices\RelationManagers;

use Filament\Forms;
// use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Item Tagihan';
    protected static ?string $recordTitleAttribute = 'item_type';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('item_type')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('item_id')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('qty')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('price')
                    ->required()
                    ->numeric(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('item_type')
            ->columns([
                Tables\Columns\TextColumn::make('item_type')
                    ->label('Tipe Item'),
                Tables\Columns\TextColumn::make('item_id')
                    ->label('ID Item'),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Kuantitas')
                    ->numeric(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('idr'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Admin biasanya tidak membuat item manual di sini
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
