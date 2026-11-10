<?php

namespace App\Filament\Tenant\Resources\EmployeeAssignments\RelationManagers;

use App\Filament\Tenant\Resources\Compensation\CompensationResource;
use App\Filament\Tenant\Resources\EmployeeAssignments\Pages\EditEmployeeAssignment;
use App\Models\Tenant\CompensationPackage;
use App\Models\Tenant\EmployeeAssignment;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use App\Enums\TenantFeature;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CompensationPackagesRelationManager extends RelationManager
{
    protected static string $relationship = 'compensationPackages';

    protected static ?string $relatedResource = CompensationResource::class;

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return tenant()->hasFeatureCode(TenantFeature::PAYROLL);
    }

    public function formPackageAssignments(): array
    {
        return [
            Select::make('recordId')
                ->label('Compensation Package')
                ->searchable()
                ->preload()
                ->required()
                ->disabledOn(['editAttachment'])
                ->visibleOn(['attach', 'editAttachment'])
                ->options(
                    CompensationPackage::query()
                        ->pluck('name', 'id')
                ),

            DatePicker::make('start_date')
                ->required(),

            DatePicker::make('end_date')
                ->helperText('Kosongkan jika karyawan tetap.'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('compensationComponents.name')
                    ->searchable()
                    ->badge()
                    ->label('Components')
                    ,
                TextColumn::make('start_date'),
                TextColumn::make('end_date')
                    ->placeholder('Masih Aktif')
                    ->color(fn($state) => $state ? 'gray' : 'success'),
            ])
            ->headerActions([
                // CreateAction::make(),
                AttachAction::make()
                    ->schema($this->formPackageAssignments())
                    ->hidden(function () {
                        return $this->getOwnerRecord()
                            ->compensationPackages()
                            ->where(function ($query) {
                                $query->whereNull('end_date')
                                    ->orWhereDate('end_date', '>=', today());
                            })
                            ->exists();
                        // dd();
                    })
            ])
            ->recordActions([
                // EditAction::make()
                //     ->schema($this->formPackageAssignments()),
                Action::make('editAttachment')
                    ->icon('heroicon-o-pencil')
                    ->visible(fn () => $this->pageClass === EditEmployeeAssignment::class)
                    ->schema($this->formPackageAssignments())
                    ->fillForm(function (CompensationPackage $record) {
                        // dd($record);
                        return [
                            'recordId' => $record->id,
                            'start_date' => $record->start_date,
                            'end_date' => $record->end_date
                        ];
                    })
                    ->action(function (array $data, CompensationPackage $record) {
                        if ($record->pivot) {
                            $record->pivot->update([
                                'start_date' => $data['start_date'],
                                'end_date' => $data['end_date'],
                            ]);
                        }
                    })
                    ,
                DetachAction::make(),
                // DeleteAction::make(),
            ]);
    }
}
