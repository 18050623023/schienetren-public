<?php

namespace App\Filament\Tenant\Resources\Holidays\Pages;

use App\Filament\Tenant\Resources\Holidays\Actions\HolidayService;
use App\Filament\Tenant\Resources\Holidays\HolidayResource;
use App\Models\Tenant\Holiday;
use App\Models\Tenant\HolidayTemplate;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateHoliday extends CreateRecord
{
    protected static string $resource = HolidayResource::class;

    // protected function handleRecordCreation(array $data): Model
    // {
    //     $holiday = new Holiday;
    //     DB::transaction(function () use ($data, &$holiday) {

    //         // one time -> langsung ke holidays
    //         if ($data['recurrence_type'] === 'one_time') {
    //             $holiday = Holiday::create($data);

    //             return;
    //         }

    //         // repeat -> simpan template
    //         $template = HolidayTemplate::create([
    //             'name' => $data['name'],
    //             'recurrence_type' => $data['recurrence_type'],
    //             'month' => $data['month'] ?? null,
    //             'day' => $data['day'] ?? null,
    //             'type' => $data['type'],
    //             'branch_id' => $data['branch_id'] ?? null,
    //             'is_active' => $data['is_active'],
    //         ]);

    //         // generate ke holidays
    //         $holiday = HolidayService::generateFromTemplate($template, $data);
    //     });

    //     return $holiday;
    // }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
