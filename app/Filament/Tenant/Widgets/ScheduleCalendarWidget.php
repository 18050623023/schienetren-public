<?php

namespace App\Filament\Tenant\Widgets;

// use Filament\Widgets\Widget;

// class ScheduleCalendarWidget extends Widget
// {
//     protected string $view = 'filament.tenant.widgets.schedule-calendar-widget';
// }

use App\Models\Tenant\Employee;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Filament\Forms;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\Auth;
// use Saade\FilamentFullCalendar\Widgets\FullCalendarWidget;

class ScheduleCalendarWidget //extends FullCalendarWidget
{
    /**
     * FullCalendar will call this function whenever it needs new event data.
     * This is triggered when the user clicks prev/next or switches views on the calendar.
     */
    // public function fetchEvents(array $fetchInfo): array
    // {
    //     // You can use $fetchInfo to filter events by date.
    //     // This method should return an array of event-like objects. See: https://github.com/saade/filament-fullcalendar/blob/3.x/#returning-events
    //     // You can also return an array of EventData objects. See: https://github.com/saade/filament-fullcalendar/blob/3.x/#the-eventdata-class
    //     return [];
    // }

    public function config(): array
    {
        return [
            'firstDay' => 1,
            'headerToolbar' => [
                // 'left' => 'dayGridWeek,dayGridDay',
                'left' => '',
                'center' => 'title',
                'right' => 'prev,next today',
            ],
            'editable' => false,
            'selectable' => false,
            // 'addEventUrl' => route('tenant.schedule-overrides.create'),
        ];
    }

    // public function getFormSchema(): array
    // {
    //     return [
    //         Forms\Components\TextInput::make('name'),

    //         Grid::make()
    //             ->schema([
    //                 Forms\Components\DateTimePicker::make('starts_at'),

    //                 Forms\Components\DateTimePicker::make('ends_at'),
    //             ]),
    //     ];
    // }

    public function fetchEvents(array $fetchInfo): array
    {
        // $employee = Auth::user()->employee;

        // $data = $this->loadScheduleData(
        //     $employee,
        //     Carbon::parse($fetchInfo['start']),
        //     Carbon::parse($fetchInfo['end'])
        // );

        // return $this->buildEvents($data);
        return [];

    }

    protected function loadScheduleData(Employee $employee, Carbon $start, Carbon $end): array
    {
        $assignment = $employee?->assignments()
            ->with([
                'workSchedule.days.shift',
                'scheduleOverrides',
                'branch.holidays',
            ])
            ->first();

        return [
            'dates' => collect(CarbonPeriod::create($start, $end)),
            'schedule_days' => $assignment->workSchedule->days->keyBy('day_of_week'),
            'overrides' => $assignment->scheduleOverrides->keyBy('date'),
            'holidays' => $assignment->branch->holidays->keyBy('holiday_date'),
        ];
    }

    protected function buildEvents(array $data): array
    {
        return $data['dates']
            ->map(fn ($date) => $this->resolveEvent($date, $data))
            ->filter()
            ->values()
            ->toArray();
    }

    protected function resolveEvent(Carbon $date, array $data): ?array
    {
        $dateString = $date->toDateString();

        if ($override = $data['overrides']->get($dateString)) {
            return [
                'title' => 'Override',
                'start' => "{$dateString} {$override->start_time}",
                'end' => "{$dateString} {$override->end_time}",
            ];
        }

        if ($holiday = $data['holidays']->get($dateString)) {
            return [
                'title' => $holiday->name,
                'start' => $dateString,
                'allDay' => true,
            ];
        }

        $day = $data['schedule_days']->get($date->dayOfWeek);

        if ($day?->is_working_day) {
            return [
                'title' => $day->shift->name,
                'start' => "{$dateString} {$day->shift->start_time}",
                'end' => "{$dateString} {$day->shift->end_time}",
            ];
        }

        return null;
    }
}
