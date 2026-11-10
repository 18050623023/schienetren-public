<?php

namespace App\Filament\Tenant\Resources\Holidays\Pages;

use App\Filament\Tenant\Resources\Holidays\HolidayResource;
use Carbon\Carbon;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditHoliday extends EditRecord
{
    protected static string $resource = HolidayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    // protected function mutateFormDataBeforeFill(array $data): array
    // {
    //     $data['recurrence_type'] = $this->record->holidayTemplate->recurrence_type ?? 'one_time';
    //     $data['month'] = $this->record->holidayTemplate->month ?? null;
    //     $data['day'] = $this->record->holidayTemplate->day ?? null;

    //     return $data;
    // }

    // protected function handleRecordUpdate(Model $record, array $data): Model
    // {
    //     // dd($data);
    //     // one time -> langsung ke holidays
    //     $data['date'] = $data['recurrence_type'] === 'yearly' ? Carbon::parse($data['date'],$data['month']) : $data['date'];
    //     $record->update($data);
    //     if ($data['recurrence_type'] === 'one_time') {

    //         return $record;

    //     }

    //     $template = $record->holidayTemplate;

    //     if (! $template) {
    //         return $record;
    //     }

    //     $template->update([
    //         'name' => $data['name'],
    //         'recurrence_type' => $data['recurrence_type'],
    //         'month' => $data['month'] ?? null,
    //         'day' => $data['day'] ?? null,
    //         'type' => $data['type'],
    //         'branch_id' => $data['branch_id'] ?? null,
    //         'is_active' => $data['is_active'],
    //     ]);

    //     return $record;
    // }
}
