<?php

namespace App\Filament\Tenant\Resources\LeaveRequests\Schemas;

use App\Models\Tenant\Employee;
use App\Models\Tenant\LeaveType;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class LeaveRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('employee_id')
                    ->relationship('employee', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->user->name)
                    ->default(Auth::user()->employees?->id)
                    ->required()
                    ->disabled(fn () => ! Auth::user()->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin'])))
                    ->dehydrated()
                    ->live(),
                Textarea::make('notes')
                    ->label('Reason Reject')
                    ->disabled(fn () => ! Auth::user()->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']))),
                Section::make('Informasi Cuti')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([

                        Select::make('leave_type_id')
                            ->relationship('leaveType', 'name')
                            ->required()
                            ->live(),
                        TextInput::make('total_days')
                            ->numeric()
                            ->required()
                            ->minValue(0.5)
                            ->disabled(fn (Get $get) => (! Auth::user()->hasRole(settingTenant('tenant_bypass_roles', ['super_admin', 'owner', 'admin']))) || (blank($get('employee_id')) || blank($get('leave_type_id'))))
                            ->dehydrated()
                            ->reactive()
                            ->afterStateUpdated(function ($state, $record, callable $set, callable $get) {
                                // dd($record);
                                // $set('qty', $record->total_days);
                                if (! $get('leave_type_id')) {
                                    return;
                                }
                                // $default_quota = LeaveType::find($get('leave_type_id'))->default_quota;
                                // dd($default_quota);

                                // if ($state > $get('qty')) {
                                //     $set('qty_received', $get('qty'));

                                //     Notification::make()
                                //         ->title('Tidak bole melebihi maksimal QTY Purchase Order')
                                //         ->success()
                                //         ->send();
                                // }
                            }),
                        DatePicker::make('start_date')
                            ->required()
                            ->live()
                            ->minDate(Carbon::today())
                            ->disabled(fn (Get $get) => blank($get('employee_id')) || blank($get('leave_type_id')))
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                if ($get('start_date') && $get('end_date')) {
                                    $days = Carbon::parse($get('start_date'))
                                        ->diffInDays(Carbon::parse($get('end_date'))) + 1;

                                    $set('total_days', $days);
                                }
                            }),
                        DatePicker::make('end_date')
                            ->required()
                            ->live()
                            ->disabled(fn (Get $get) => blank($get('employee_id')) || blank($get('leave_type_id')))
                            // ->native(false)
                            ->minDate(fn (Get $get) => Carbon::parse($get('start_date')))
                            ->maxDate(function (Get $get) {
                                $leaveTypeId = $get('leave_type_id');
                                $startDate = $get('start_date');

                                // 1. Validasi awal (Early Return) jika input penting belum diisi
                                if (!$leaveTypeId || !$startDate) {
                                    return null;
                                }

                                // 2. Ambil data dari database
                                $leaveType = LeaveType::find($leaveTypeId);

                                // 3. Pastikan data ditemukan untuk mencegah error
                                if (!$leaveType) {
                                    return null;
                                }

                                // 4. Tentukan nilai batas hari (Gunakan ternary operator agar lebih bersih)
                                $maxDays = $leaveType->quota_per_request > 0
                                    ? $leaveType->quota_per_request
                                    : $leaveType->default_quota;

                                // 5. Kembalikan hasil kalkulasi tanggal
                                return Carbon::parse($startDate)->addDays($maxDays - 1);
                            })
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                if ($get('start_date') && $get('end_date')) {
                                    $days = Carbon::parse($get('start_date'))
                                        ->diffInDays(Carbon::parse($get('end_date'))) + 1;

                                    $set('total_days', $days);
                                }
                            }),
                        Textarea::make('reason')
                            ->required()
                            ->columnSpan(2),
                    ]),
                FileUpload::make('attachments')
                    ->label('File Bukti')
                    ->multiple()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'application/pdf'])
                    ->maxSize(5120)
                    ->columnSpan(2)
                    ->storeFileNamesIn('original_names')
                    ->directory(function (Get $get) {
                        $user = Employee::find($get('employee_id'))->user;

                        return 'file/leave-attachments/'.$user->id.'-'.$user->name;
                    }) // folder penyimpanan
                    ->getUploadedFileNameForStorageUsing(
                        function (TemporaryUploadedFile $file, Get $get): string {
                            $leaveType = LeaveType::find($get('leave_type_id'));

                            return 'izin-'.
                                ($leaveType?->name ?? 'unknown').
                                '-'.
                                now()->timestamp.
                                '-'.
                                uniqid().
                                '.'.
                                $file->getClientOriginalExtension();
                        }
                    )
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
                // Select::make('status')
                //     ->options([
                //         'pending' => 'Pending',
                //         'approved' => 'Approved',
                //         'rejected' => 'Rejected',
                //     ]),
            ]);
    }
}
