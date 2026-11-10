<?php

namespace App\Filament\Tenant\Resources\FinanceAccounts\Pages;

use App\Filament\Tenant\Resources\FinanceAccounts\FinanceAccountResource;
use App\Models\Tenant\FinanceAccountCategory;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;

class ListFinanceAccounts extends ListRecords
{
    protected static string $resource = FinanceAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('manageCategories')
                ->label('Kelola Kategori')
                ->icon('heroicon-o-tag')
                ->color('gray')
                ->url(fn () => \App\Filament\Tenant\Resources\FinanceAccountCategories\FinanceAccountCategoryResource::getUrl('index')),
            CreateAction::make(),
        ];
    }
}
