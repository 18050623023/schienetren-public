<?php

namespace App\Filament\Tenant\Resources\AttendanceCorrections;

use App\Filament\Tenant\Resources\AttendanceCorrections\Pages\CreateAttendanceCorrection;
use App\Filament\Tenant\Resources\AttendanceCorrections\Pages\EditAttendanceCorrection;
use App\Filament\Tenant\Resources\AttendanceCorrections\Pages\ListAttendanceCorrections;
use App\Filament\Tenant\Resources\AttendanceCorrections\Schemas\AttendanceCorrectionForm;
use App\Filament\Tenant\Resources\AttendanceCorrections\Tables\AttendanceCorrectionsTable;
use App\Models\Tenant\AttendanceCorrection;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AttendanceCorrectionResource extends Resource
{
    protected static ?string $model = AttendanceCorrection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    // protected static ?string $recordTitleAttribute = 'Attendance Correction';
    protected static ?string $recordTitleAttribute = 'date';
    protected static string|UnitEnum|null $navigationGroup = 'Attendance';

    public static function form(Schema $schema): Schema
    {
        return AttendanceCorrectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttendanceCorrectionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAttendanceCorrections::route('/'),
            'create' => CreateAttendanceCorrection::route('/create'),
            'edit' => EditAttendanceCorrection::route('/{record}/edit'),
        ];
    }
}
