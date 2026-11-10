<?php

namespace App\Filament\Admin\Resources\Addons\RelationManagers;

use App\Models\Feature;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AddonFeaturesRelationManager extends RelationManager
{
    protected static string $relationship = 'addonFeatures';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('feature_id')
                    ->required()
                    ->maxLength(255),
                TextInput::make('value')
                    ->required()
                    ->numeric()
                    ->suffix(function(Get $get){
                        if(!$get('feature_id')) return '';
                        $feature = Feature::find($get('feature_id'));
                        return $feature->type == 'limit' ? $feature->unit : '';
                    })
                    ->maxLength(255),
                
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('feature_id')
            ->columns([
                TextColumn::make('feature_id')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
