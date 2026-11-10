<?php

namespace App\Filament\Tenant\Resources\DeviceTypes\RelationManagers;

use App\Models\Tenant\ChecklistTemplate;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChecklistsRelationManager extends RelationManager
{
    protected static string $relationship = 'checklists';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live()
                    ->unique(
                        ignoreRecord: true,
                    )
                    ->afterStateUpdated(function ($state, $set) {
                        $exists = ChecklistTemplate::where('name', $state)->exists();

                        if ($exists) {
                            Notification::make()
                                ->title('Nama sudah digunakan')
                                ->danger()
                                ->send();

                            // Kosongkan field
                            $set('name', '');
                        }
                    }),
                Textarea::make('description')
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('description')
            ])
            ->filters([
                //
            ])
            ->headerActions([
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
                CreateAction::make(),
                AssociateAction::make(),
            ]);
    }
}
