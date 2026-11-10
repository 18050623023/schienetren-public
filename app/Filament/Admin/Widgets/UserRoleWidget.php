<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

class UserRoleWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -2;

    protected int | string | array $columnSpan = 1;

    protected function getStats(): array
    {
        $user = Auth::user();
        $roles = $user->getRoleNames()->implode(', ');

        return [
            Stat::make('Role Anda', $user->getRoleNames()->implode(', '))
                // ->description('Role user yang sedang login')
                ->color('primary')
            // Stat::make(
            //     '', 
            //     new HtmlString("<div class='flex items-center gap-2'><span class='text-sm text-gray-500 font-medium'>Role Anda:</span> <span class='text-lg font-bold text-primary-600'>{$roles}</span></div>")
            // )
        ];
    }

    public static function canView(): bool
    {
        return false; // Mengembalikan false akan menyembunyikan widget ini
    }
}
