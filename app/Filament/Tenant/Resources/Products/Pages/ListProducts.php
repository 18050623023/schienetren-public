<?php

namespace App\Filament\Tenant\Resources\Products\Pages;

use App\Filament\Tenant\Resources\Categories\CategoryResource;
use App\Filament\Tenant\Resources\Products\ProductResource;
use App\Filament\Tenant\Resources\Units\UnitResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('categories')
                ->label('Manage Categories')
                ->icon('heroicon-o-chart-bar')
                ->url(CategoryResource::getUrl()),

            Action::make('units')
                ->label('Manage Units')
                ->icon('heroicon-o-chart-bar')
                ->url(UnitResource::getUrl()),
            CreateAction::make(),
        ];
    }
}
