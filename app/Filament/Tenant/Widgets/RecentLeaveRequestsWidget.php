<?php

namespace App\Filament\Tenant\Widgets;

use Filament\Actions\BulkActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\TenantFeature;
use App\Models\Tenant\LeaveRequest;
use App\Models\Tenant\LeaveType;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class RecentLeaveRequestsWidget extends TableWidget
{
    public int|string|null $employeeId = null;

    public static function canView(): bool
    {
        return tenant()->hasFeatureCode(TenantFeature::LEAVE);
    }

    public function mount( $employeeId = null): void
    {
        $this->employeeId = $employeeId
            ? (int) $employeeId
            : null;
        // dd($this->employeeId);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(function (Builder $query): Builder {

                if (blank($this->employeeId)) {
                    return LeaveRequest::query()->whereRaw('1 = 0');
                }

                return LeaveRequest::query()
                    ->where('status', 'approved')
                    ->when(
                        $this->employeeId,
                        fn ($query) => $query->where('employee_id', $this->employeeId)
                    )
                    ->latest();

            })
            ->columns([
                TextColumn::make('leaveType.name')
                    ->searchable(),
                TextColumn::make('total_days')
                    ->searchable(),
                TextColumn::make('start_date')
                    ->searchable(),
                TextColumn::make('end_date')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->form([
                        Select::make('employee_id')
                            ->relationship('employee', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name)
                            ->default(Auth::user()->employees?->id),
                        Textarea::make('notes')
                            ->label('Reason Reject'),
                        Section::make('Informasi Cuti')
                            ->columnSpan(2)
                            ->columns(2)
                            ->schema([

                                Select::make('leave_type_id')
                                    ->relationship('leaveType', 'name'),
                                TextInput::make('total_days')
                                    ->numeric()
                                    ->minValue(0.5),
                                DatePicker::make('start_date'),
                                DatePicker::make('end_date'),
                                Textarea::make('reason')
                                    ->required()
                                    ->columnSpan(2),
                            ]),
                        FileUpload::make('attachments')
                            ->label('File Bukti')
                            ->multiple()
                            ->columnSpan(2)
                            ->afterStateHydrated(function ($component, $record, $state) {
                                // dd($record->attachments);
                                if ($record) {
                                    $component->state(
                                        $record->attachments
                                            ->pluck('file_path')
                                            ->toArray()
                                    );
                                }
                            }),
                    ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
