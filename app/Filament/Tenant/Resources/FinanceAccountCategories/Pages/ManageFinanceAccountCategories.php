<?php

namespace App\Filament\Tenant\Resources\FinanceAccountCategories\Pages;

use App\Filament\Tenant\Resources\FinanceAccountCategories\FinanceAccountCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageFinanceAccountCategories extends ManageRecords
{
    protected static string $resource = FinanceAccountCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali ke Akun')
                ->url(fn () => \App\Filament\Tenant\Resources\FinanceAccounts\FinanceAccountResource::getUrl('index'))
                ->color('gray')
                ->icon('heroicon-o-arrow-left'),
            Actions\CreateAction::make(),
        ];
    }
}
