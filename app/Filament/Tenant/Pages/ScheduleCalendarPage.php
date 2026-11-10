<?php

namespace App\Filament\Tenant\Pages;

use App\Filament\Tenant\Widgets\ScheduleCalendarWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ScheduleCalendarPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::Calendar;

    protected string $view = 'filament.tenant.pages.schedule-calendar-page';

    protected function getHeaderWidgets(): array
    {
        return [
            ScheduleCalendarWidget::class,
        ];
    }

    // protected function mount() {}
    public static function canAccess(): bool
    {
        // ponytail: ScheduleCalendarWidget is currently a draft class not extending Filament\Widgets\Widget. Returning false prevents TypeError until widget is completed.
        return false;
    }
}
