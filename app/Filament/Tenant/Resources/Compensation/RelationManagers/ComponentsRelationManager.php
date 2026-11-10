<?php

namespace App\Filament\Tenant\Resources\Compensation\RelationManagers;

use App\Enums\CompensationPackageComponentBasedOn;
use App\Filament\Tenant\Resources\Compensation\CompensationResource;
use App\Filament\Tenant\Resources\CompensationComponents\CompensationComponentResource;
use App\Models\Tenant\CompensationComponent;
use App\Services\DatabaseService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ComponentsRelationManager extends RelationManager
{
    protected static string $relationship = 'compensationComponents';

    protected static ?string $relatedResource = CompensationComponentResource::class;
    // protected static ?string $relatedResource = CompensationResource::class;

    public function formPackageComponents(): Array
    {
        return [
                Grid::make([
                        'lg' => 2,
                    ])
                    ->columnSpan(2)
                    ->schema([
                    Select::make('recordId')
                        ->label('Component')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->visibleOn(['attach'])
                        // ->hiddenOn(['view'])
                        ->options(
                            CompensationComponent::query()
                                ->pluck('name', 'id')
                        ),

                    Select::make('calc_type')
                        ->live()
                        ->options(app(DatabaseService::class)->getEnumValues('compensation_package_components', 'calc_type')),

                    TextInput::make('value')
                        ->numeric(),

                    Textarea::make('formula')
                        ->visible(fn ($get) => $get('calc_type') == 'formula'),

                    Select::make('based_on')
                        ->required()
                        ->options(CompensationPackageComponentBasedOn::options()),

                ]),
            ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('type'),
                TextColumn::make('calc_type'),
                TextColumn::make('value'),
                TextColumn::make('based_on')
                    ->formatStateUsing(fn ($state) => CompensationPackageComponentBasedOn::from($state)->label()),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->headerActions([
                    // ->form(fn (AttachAction $action): array => [
                    //     $action->getRecordSelect()
                    //         ->preload()
                    //         ->createOptionForm([
                    //             TextInput::make('name')
                    //
                    //                 ->label('Component Name'),
                    //             Select::make('type')
                    //                 ->options(app(DatabaseService::class)->getEnumValues('compensation_components', 'type')),
                    //             Toggle::make('is_active')->default(true),
                    //         ]),

                    //     Select::make('calc_type')
                    //         ->options(app(DatabaseService::class)->getEnumValues('compensation_package_components', 'calc_type'))
                    //         ->required(),

                    //     TextInput::make('value')
                    //         ->numeric(),

                    //     Select::make('based_on')
                    //         ->options(CompensationPackageComponentBasedOn::class),

                    //     Textarea::make('formula'),
                    // ])
                // AttachAction::make(),
                // AttachAction::make()
                    // ->recordSelect(...)
                    // ->createOptionForm([
                    //     TextInput::make('name'),
                    //     Select::make('type'),
                    //     Toggle::make('is_active'),
                    // ])
                    // ->form([
                    //     Select::make('calc_type'),
                    //     TextInput::make('value'),
                    //     Select::make('based_on'),
                    //     Textarea::make('formula'),
                    //     TextInput::make('sort_order'),
                    // ])
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit Component'),

                DeleteAction::make(),
                // ViewAction::make()
                //     ->label('View Component'),
                ActionGroup::make([
                    Action::make('editAttachment')
                        ->icon('heroicon-o-pencil')
                        ->schema($this->formPackageComponents())
                        ->fillForm(function (CompensationComponent $record) {
                            return [
                                'calc_type' => $record->pivot->calc_type,
                                'value' => $record->pivot->value,
                                'formula' => $record->pivot->formula,
                                'based_on' => $record->pivot->based_on,
                            ];
                        })
                        ->action(function (CompensationComponent $record, array $data) {
                            $record->pivot->update($data);
                        }),
                    ViewAction::make()
                        ->icon('heroicon-o-eye')
                        ->label('View Attachment')
                        ->schema($this->formPackageComponents())

                        ->fillForm(function (CompensationComponent $record) {
                            return [
                                'calc_type' => $record->pivot->calc_type,
                                'value' => $record->pivot->value,
                                'formula' => $record->pivot->formula,
                                'based_on' => $record->pivot->based_on,
                            ];
                        }),
                    DetachAction::make(),
                ]),
                // ForceDeleteAction::make(),
                // RestoreAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DetachBulkAction::make(),
                //     DeleteBulkAction::make(),
                //     ForceDeleteBulkAction::make(),
                //     RestoreBulkAction::make(),
                // ]),


                CreateAction::make()
                    ->label('Create Component'),
                AttachAction::make()
                    // ->preloadRecordSelect()
                    ->schema(fn () => $this->formPackageComponents())
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes([
                    // SoftDeletingScope::class,
                ]));
    }
}
